<?php
/**
 * SmashHub AI Engine - Machine Learning Training Script
 * Algorithm: Multinomial Naive Bayes with TF-IDF Vectorization
 * Language: Pure PHP 7.4+
 */

require_once __DIR__ . '/Tokenizer.php';
require_once __DIR__ . '/TfIdfVectorizer.php';
require_once __DIR__ . '/NaiveBayesClassifier.php';

echo "====================================================================\n";
echo "   SMASHHUB AI ENGINE: MACHINE LEARNING MODEL TRAINING PIPELINE     \n";
echo "====================================================================\n";

$datasetPath = __DIR__ . '/dataset.json';
if (!file_exists($datasetPath)) {
    die("LOI: Khong tim thay tap du lieu dataset.json\n");
}

$rawJson = file_get_contents($datasetPath);
$dataset = json_decode($rawJson, true);

if (!is_array($dataset) || empty($dataset)) {
    die("LOI: Du lieu dataset.json khong hop le.\n");
}

$totalSamples = count($dataset);
echo "[INFO] Tong so mau du lieu huan luyen: {$totalSamples} mau\n";

// Phan bo mau theo nhan
$classBuckets = [];
foreach ($dataset as $s) {
    $c = $s['intent'];
    $classBuckets[$c][] = $s;
}

echo "[INFO] Phan bo cac lop y dinh (Intents):\n";
foreach ($classBuckets as $intent => $samples) {
    printf("   - %-20s: %2d mau\n", $intent, count($samples));
}

// 1. K-Fold Cross-Validation (K = 5)
$K = 5;
mt_srand(42);
$folds = array_fill(0, $K, []);

// Stratified K-fold split
foreach ($classBuckets as $intent => $samples) {
    shuffle($samples);
    foreach ($samples as $idx => $sample) {
        $foldIdx = $idx % $K;
        $folds[$foldIdx][] = $sample;
    }
}

$alpha = 0.1; // Optimized Laplace smoothing
$intents = array_keys($classBuckets);
$tp = []; $fp = []; $fn = [];
foreach ($intents as $in) {
    $tp[$in] = 0; $fp[$in] = 0; $fn[$in] = 0;
}

$totalEvaluated = 0;
$totalCorrect = 0;

$startTime = microtime(true);

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
        $pred = $nb->predict($sample['text']);
        $predIntent = $pred['intent'];
        $trueIntent = $sample['intent'];

        if ($predIntent === $trueIntent) {
            $totalCorrect++;
            $tp[$trueIntent]++;
        } else {
            $fp[$predIntent] = ($fp[$predIntent] ?? 0) + 1;
            $fn[$trueIntent]++;
        }
    }
}

$trainingTime = round((microtime(true) - $startTime) * 1000, 2);
$accuracy = ($totalCorrect / $totalEvaluated) * 100;

echo "\n--------------------------------------------------------------------\n";
echo "       KET QUA DANH GIA STRATIFIED 5-FOLD CROSS-VALIDATION          \n";
echo "--------------------------------------------------------------------\n";
printf("%-20s | %-10s | %-10s | %-10s\n", "Y Dinh (Intent)", "Precision", "Recall", "F1-Score");
echo "---------------------+------------+------------+------------\n";

$sumF1 = 0;
foreach ($intents as $in) {
    $p = ($tp[$in] + $fp[$in] > 0) ? ($tp[$in] / ($tp[$in] + $fp[$in])) : 1.0;
    $r = ($tp[$in] + $fn[$in] > 0) ? ($tp[$in] / ($tp[$in] + $fn[$in])) : 1.0;
    $f1 = ($p + $r > 0) ? (2 * $p * $r / ($p + $r)) : 0.0;
    $sumF1 += $f1;

    printf("%-20s | %9.1f%% | %9.1f%% | %9.1f%%\n", $in, $p * 100, $r * 100, $f1 * 100);
}

$macroF1 = ($sumF1 / count($intents)) * 100;
echo "---------------------+------------+------------+------------\n";
printf("DO CHINH XAC CROSS-VAL (ACCURACY): %5.1f%%\n", $accuracy);
printf("DIEM CHUAN MACRO F1-SCORE        : %5.1f%%\n", $macroF1);
printf("THOI GIAN HUAN LUYEN & DANH GIA  : %5.2f ms\n", $trainingTime);

// 2. Huan luyen mo hinh tong the 100% dataset de xuat model_weights.json
$finalClassifier = new NaiveBayesClassifier($alpha);
$finalClassifier->train($dataset);

$modelData = $finalClassifier->exportModel();
$modelData['metrics'] = [
    'total_samples'  => $totalSamples,
    'cross_val_k'    => $K,
    'accuracy'       => round($accuracy, 2),
    'macro_f1'       => round($macroF1, 2),
    'alpha'          => $alpha,
    'training_ms'    => $trainingTime
];

$outputPath = __DIR__ . '/model_weights.json';
file_put_contents($outputPath, json_encode($modelData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n[SUCCESS] Mo hinh da duoc xuat thanh cong ra: {$outputPath}\n";
echo "====================================================================\n";
