<?php
/**
 * SmashHub NLP Intent Classification Evaluation & Confusion Matrix
 * Script: tests/eval/nlp_eval.php
 * Evaluates in-house TF-IDF + Naive Bayes Classifier via Stratified 5-Fold Cross-Validation
 * Outputs: 10x10 Confusion Matrix, Precision/Recall/F1, Error Analysis, and Confidence Guardrail Testing
 * Language: Pure PHP 7.4+
 */

require_once __DIR__ . '/../../ai_engine/Tokenizer.php';
require_once __DIR__ . '/../../ai_engine/TfIdfVectorizer.php';
require_once __DIR__ . '/../../ai_engine/NaiveBayesClassifier.php';
require_once __DIR__ . '/../../ai_engine/Predictor.php';

// Set fixed random seed for reproducible splits
mt_srand(42);

$datasetPath = __DIR__ . '/../../ai_engine/dataset.json';
if (!file_exists($datasetPath)) {
    die("LOI: Khong tim thay tap du lieu dataset.json\n");
}

$rawJson = file_get_contents($datasetPath);
$dataset = json_decode($rawJson, true);
if (!is_array($dataset) || empty($dataset)) {
    die("LOI: Du lieu dataset.json khong hop le.\n");
}

$totalSamples = count($dataset);

// Bucket samples by intent
$classBuckets = [];
foreach ($dataset as $s) {
    $c = $s['intent'];
    $classBuckets[$c][] = $s;
}

$intents = array_keys($classBuckets);
sort($intents);
$numClasses = count($intents);

// 1. Stratified 5-Fold Split
$K = 5;
$folds = array_fill(0, $K, []);

foreach ($classBuckets as $intent => $samples) {
    shuffle($samples);
    foreach ($samples as $idx => $sample) {
        $foldIdx = $idx % $K;
        $folds[$foldIdx][] = $sample;
    }
}

// Initialize Confusion Matrix: matrix[actual][predicted]
$confusionMatrix = [];
foreach ($intents as $trueC) {
    $confusionMatrix[$trueC] = [];
    foreach ($intents as $predC) {
        $confusionMatrix[$trueC][$predC] = 0;
    }
}

$alpha = 0.1;
$tp = array_fill_keys($intents, 0);
$fp = array_fill_keys($intents, 0);
$fn = array_fill_keys($intents, 0);

$totalEvaluated = 0;
$totalCorrect = 0;
$misclassifiedSamples = [];
$confusedPairs = [];

for ($k = 0; $k < $K; $k++) {
    $testSet = $folds[$k];
    $trainSet = [];
    for ($j = 0; $j < $K; $j++) {
        if ($j !== $k) {
            $trainSet = array_merge($trainSet, $folds[$j]);
        }
    }

    $nb = new NaiveBayesClassifier($alpha);
    $nb->train($trainSet);

    foreach ($testSet as $sample) {
        $totalEvaluated++;
        $text = $sample['text'];
        $trueIntent = $sample['intent'];

        $pred = $nb->predict($text);
        $predIntent = $pred['intent'];
        $conf = $pred['confidence'];

        if (isset($confusionMatrix[$trueIntent][$predIntent])) {
            $confusionMatrix[$trueIntent][$predIntent]++;
        } else {
            $confusionMatrix[$trueIntent][$predIntent] = 1;
        }

        if ($predIntent === $trueIntent) {
            $totalCorrect++;
            $tp[$trueIntent]++;
        } else {
            $fp[$predIntent] = ($fp[$predIntent] ?? 0) + 1;
            $fn[$trueIntent]++;

            $pairKey = "{$trueIntent} -> {$predIntent}";
            $confusedPairs[$pairKey] = ($confusedPairs[$pairKey] ?? 0) + 1;

            $misclassifiedSamples[] = [
                'text' => $text,
                'true_intent' => $trueIntent,
                'pred_intent' => $predIntent,
                'confidence' => $conf
            ];
        }
    }
}

$accuracy = ($totalCorrect / $totalEvaluated) * 100;

// Calculate Precision, Recall, F1
$metrics = [];
$sumF1 = 0.0;
$sumPrec = 0.0;
$sumRec = 0.0;

foreach ($intents as $in) {
    $p = ($tp[$in] + $fp[$in] > 0) ? ($tp[$in] / ($tp[$in] + $fp[$in])) : 0.0;
    $r = ($tp[$in] + $fn[$in] > 0) ? ($tp[$in] / ($tp[$in] + $fn[$in])) : 0.0;
    $f1 = ($p + $r > 0) ? (2.0 * $p * $r / ($p + $r)) : 0.0;

    $sumPrec += $p;
    $sumRec += $r;
    $sumF1 += $f1;

    $metrics[$in] = [
        'samples' => count($classBuckets[$in]),
        'precision' => round($p * 100, 2),
        'recall' => round($r * 100, 2),
        'f1' => round($f1 * 100, 2)
    ];
}

$macroPrecision = round(($sumPrec / $numClasses) * 100, 2);
$macroRecall = round(($sumRec / $numClasses) * 100, 2);
$macroF1 = round(($sumF1 / $numClasses) * 100, 2);

// Sort Confused Pairs desc
arsort($confusedPairs);
$top3ConfusedPairs = array_slice($confusedPairs, 0, 3, true);

// 2. Confidence Guardrail Evaluation
$predictor = new Predictor();

$outOfScopeQueries = [
    "Thủ đô của nước Pháp là gì",
    "Cách nấu món bò kho miền Nam chuẩn vị ngon",
    "Giải phương trình bậc hai 2x^2 + 5x - 3 = 0",
    "Tổng thống Mỹ nhiệm kỳ đầu tiên là ai",
    "Công thức tính diện tích hình tròn bán kính R",
    "Tại sao lá cây lại có màu xanh lục vào mùa hè",
    "Cách làm bánh mì hoa cúc tại nhà không cần máy",
    "Ý nghĩa triết học của câu nói Tôi tư duy nên tôi tồn tại",
    "Giá vàng miếng SJC hôm nay tăng hay giảm bao nhiêu",
    "Mẹo trị mụn trứng cá và chăm sóc da dầu hiệu quả"
];

$inScopeQueries = [
    "Chiều nay 18h ở Bình Thạnh có sân nào còn trống không",
    "Bảng giá thuê sân cầu lông một giờ khoảng bao nhiêu",
    "Người mới tập chơi cổ tay yếu nên mua loại vợt gì",
    "Nếu đối thủ bùng kèo không đến thì bị trừ bao nhiêu điểm uy tín",
    "Quy định luật phát cầu dưới 1m15 mới nhất của BWF",
    "Làm sao để tìm bạn đấu cầu lông ở gần tôi bằng radar",
    "Ngày mai là thứ mấy vậy bạn",
    "Hôm nay thời tiết đẹp trời nhỉ có nên đi đánh cầu không",
    "Đánh cầu lông có giúp đốt mỡ và tăng chiều cao không",
    "Xin chào trợ lý AI SmashHub"
];

$guardrailOutCorrect = 0;
$guardrailOutResults = [];
foreach ($outOfScopeQueries as $q) {
    $res = $predictor->processMessage($q);
    $isBlocked = ($res['intent'] === 'out_of_scope');
    if ($isBlocked) $guardrailOutCorrect++;
    $guardrailOutResults[] = [
        'query' => $q,
        'intent' => $res['intent'],
        'confidence' => round($res['confidence'] * 100, 1),
        'blocked' => $isBlocked ? 'CHẶN ĐÚNG' : 'BỎ SÓT'
    ];
}

$guardrailInCorrect = 0;
$guardrailInResults = [];
foreach ($inScopeQueries as $q) {
    $res = $predictor->processMessage($q);
    $isAllowed = ($res['intent'] !== 'out_of_scope');
    if ($isAllowed) $guardrailInCorrect++;
    $guardrailInResults[] = [
        'query' => $q,
        'intent' => $res['intent'],
        'confidence' => round($res['confidence'] * 100, 1),
        'allowed' => $isAllowed ? 'CHO PHÉP' : 'CHẶN NHẦM'
    ];
}

$outRejectionRate = round(($guardrailOutCorrect / count($outOfScopeQueries)) * 100, 1);
$inPassRate = round(($guardrailInCorrect / count($inScopeQueries)) * 100, 1);
$falseRejectionRate = round(((count($inScopeQueries) - $guardrailInCorrect) / count($inScopeQueries)) * 100, 1);

// 3. Export Confusion Matrix to CSV
$outDir = __DIR__ . '/out';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$cmCsvPath = $outDir . '/nlp_confusion_matrix.csv';
$fpCm = fopen($cmCsvPath, 'w');

// Header row
$cmHeader = array_merge(['Actual_Intent \ Predicted'], $intents);
fputcsv($fpCm, $cmHeader);

foreach ($intents as $actual) {
    $row = [$actual];
    foreach ($intents as $pred) {
        $row[] = $confusionMatrix[$actual][$pred] ?? 0;
    }
    fputcsv($fpCm, $row);
}
fclose($fpCm);

// 4. Export Evaluation Report to Markdown
$mdPath = $outDir . '/nlp_eval_results.md';
$md = "# BAO CAO DANH GIA MO HINH NLP & MA TRAN NHAM LAN (CONFUSION MATRIX)\n\n";
$md .= "Phan he xu ly ngon ngu tu nhien (ai_engine/) cua SmashHub su dung ket hop **Tokenizer N-Gram tieng Viet**, ma tran **TF-IDF chuan hoa L2** va bo phan loai **Multinomial Naive Bayes (Laplace alpha = 0.1, Softmax)**.\n\n";

$md .= "## 1. BANG METRICS TONG THE (5-FOLD CROSS-VALIDATION)\n\n";
$md .= "| Chi So (Metric) | Gia Tri |\n";
$md .= "| :--- | :---: |\n";
$md .= "| Tong so mau huan luyen (Dataset) | **{$totalSamples} mau** |\n";
$md .= "| So luong lop y dinh (Intents) | **{$numClasses} intents** |\n";
$md .= "| So fold kiem dinh cheo (K-Fold) | **5 Folds (Stratified)** |\n";
$md .= "| **Do chinh xac tong the (Accuracy)** | **" . round($accuracy, 2) . "%** |\n";
$md .= "| **Macro Precision** | **{$macroPrecision}%** |\n";
$md .= "| **Macro Recall** | **{$macroRecall}%** |\n";
$md .= "| **Diem chuan Macro F1-Score** | **{$macroF1}%** |\n\n";

$md .= "## 2. BANG CHI TIET PRECISION, RECALL, F1 TUNG INTENT\n\n";
$md .= "| Y Dinh (Intent) | So Mau | Precision | Recall | F1-Score |\n";
$md .= "| :--- | :---: | :---: | :---: | :---: |\n";
foreach ($intents as $in) {
    $m = $metrics[$in];
    $md .= "| `{$in}` | {$m['samples']} | {$m['precision']}% | {$m['recall']}% | **{$m['f1']}%** |\n";
}
$md .= "| **TRUNG BINH (MACRO AVG)** | **{$totalSamples}** | **{$macroPrecision}%** | **{$macroRecall}%** | **{$macroF1}%** |\n\n";

$md .= "## 3. MA TRAN NHAM LAN (CONFUSION MATRIX 10x10)\n\n";
$md .= "> Hang doc: Nhan thuc te (Ground Truth). Cot ngang: Nhan mo hinh du doan (Prediction).\n\n";

// Build markdown confusion matrix table
$md .= "| Thuc te \\ Du doan | " . implode(' | ', array_map(function($x) { return substr($x, 0, 7); }, $intents)) . " |\n";
$md .= "| :--- | " . str_repeat(':---: | ', count($intents)) . "\n";
foreach ($intents as $act) {
    $line = "| `{$act}` | ";
    foreach ($intents as $prd) {
        $val = $confusionMatrix[$act][$prd];
        $display = ($val > 0) ? (($act === $prd) ? "**{$val}**" : "{$val}") : ".";
        $line .= "{$display} | ";
    }
    $md .= $line . "\n";
}

$md .= "\n## 4. TOP 3 CAP INTENT BI NHAM LAN NHIEU NHAT\n\n";
$rank = 1;
foreach ($top3ConfusedPairs as $pair => $cnt) {
    $md .= "{$rank}. **`{$pair}`**: {$cnt} lan nham lan\n";
    $rank++;
}

$md .= "\n## 5. VI DU CAC MAU CAU BI PHAN LOAI SAI\n\n";
$md .= "| STT | Cau Mau (Text) | Intent Thuc Te | Intent Du Doan | Do Tin Cay |\n";
$md .= "| :---: | :--- | :---: | :---: | :---: |\n";
foreach (array_slice($misclassifiedSamples, 0, 5) as $i => $ms) {
    $num = $i + 1;
    $textEsc = htmlspecialchars($ms['text']);
    $confPct = round($ms['confidence'] * 100, 1);
    $md .= "| {$num} | \"{$textEsc}\" | `{$ms['true_intent']}` | `{$ms['pred_intent']}` | {$confPct}% |\n";
}

$md .= "\n## 6. DANH GIA BO LOC NGUONG TIN CAY (CONFIDENCE GUARDRAIL >= 50%)\n\n";
$md .= "- **Ty le chan cau hoi ngoai pham vi the thao (True Rejection Rate):** **{$outRejectionRate}%** ({$guardrailOutCorrect}/" . count($outOfScopeQueries) . ")\n";
$md .= "- **Ty le cho phep cau hoi the thao hop le (In-Scope Pass Rate):** **{$inPassRate}%** ({$guardrailInCorrect}/" . count($inScopeQueries) . ")\n";
$md .= "- **Ty le chan nham cau the thao hop le (False Rejection Rate):** **{$falseRejectionRate}%**\n\n";

$md .= "### Chi tiet kiem tra 10 cau ngoai pham vi (Out-of-Scope):\n\n";
$md .= "| STT | Cau Hoi | Intent Nhan Dien | Do Tin Cay | Ket Qua |\n";
$md .= "| :---: | :--- | :---: | :---: | :---: |\n";
foreach ($guardrailOutResults as $i => $gr) {
    $num = $i + 1;
    $md .= "| {$num} | \"{$gr['query']}\" | `{$gr['intent']}` | {$gr['confidence']}% | **{$gr['blocked']}** |\n";
}

$md .= "\n### Chi tiet kiem tra 10 cau trong pham vi (In-Scope):\n\n";
$md .= "| STT | Cau Hoi | Intent Nhan Dien | Do Tin Cay | Ket Qua |\n";
$md .= "| :---: | :--- | :---: | :---: | :---: |\n";
foreach ($guardrailInResults as $i => $gr) {
    $num = $i + 1;
    $md .= "| {$num} | \"{$gr['query']}\" | `{$gr['intent']}` | {$gr['confidence']}% | **{$gr['allowed']}** |\n";
}

file_put_contents($mdPath, $md);

// Console Output
echo "=== KET QUA DANH GIA NLP & MA TRAN NHAM LAN (STRATIFIED 5-FOLD CV) ===\n\n";
printf("- Tong so mau: %d | Accuracy: %.2f%% | Macro F1: %.2f%%\n", $totalEvaluated, $accuracy, $macroF1);
echo "\nTop 3 cap intent nham lan nhieu nhat:\n";
foreach ($top3ConfusedPairs as $pair => $cnt) {
    echo "  + {$pair}: {$cnt} lan\n";
}

echo "\nDanh gia Confidence Guardrail:\n";
echo "  + Out-of-Scope Rejection Rate: {$outRejectionRate}%\n";
echo "  + In-Scope Pass Rate: {$inPassRate}%\n";
echo "  + False Rejection Rate: {$falseRejectionRate}%\n";

echo "\n[SUCCESS] Xuat Confusion Matrix CSV: {$cmCsvPath}\n";
echo "[SUCCESS] Xuat Bao cao NLP Markdown: {$mdPath}\n";

