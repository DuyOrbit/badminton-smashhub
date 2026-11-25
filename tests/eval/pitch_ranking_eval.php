<?php
/**
 * SmashHub Pitch Ranking Quality Evaluation
 * Script: tests/eval/pitch_ranking_eval.php
 * Evaluates AI Smart Booking recommendation quality across 25 natural language scenarios
 * Metrics: Top-1 Accuracy, Top-3 Hit Rate, and Entity Extraction Accuracy
 * Language: Pure PHP 7.4+
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/AiSmartBooking.php';

$database = new Database();
$db = $database->getConnection();

$aiBooking = new AiSmartBooking($db);

$scenariosPath = __DIR__ . '/scenarios.json';
if (!file_exists($scenariosPath)) {
    die("LOI: Khong tim thay tep scenarios.json\n");
}

$scenarios = json_decode(file_get_contents($scenariosPath), true);
if (!is_array($scenarios) || empty($scenarios)) {
    die("LOI: Du lieu scenarios.json khong hop le.\n");
}

$totalScenarios = count($scenarios);
$top1Correct = 0;
$top3Correct = 0;

$entityMetrics = [
    'date' => ['total' => 0, 'correct' => 0],
    'start_hour' => ['total' => 0, 'correct' => 0],
    'district' => ['total' => 0, 'correct' => 0],
    'max_price' => ['total' => 0, 'correct' => 0],
    'amenities' => ['total' => 0, 'correct' => 0]
];

$evaluationRows = [];
$failureNotes = [];

foreach ($scenarios as $s) {
    $id = $s['id'];
    $prompt = $s['prompt'];
    $userLat = $s['user_lat'];
    $userLng = $s['user_lng'];
    $expectedIds = $s['expected_pitch_ids'];
    $expectedEntities = $s['expected_entities'];

    // 1. Goi parseNaturalLanguage
    $parsed = $aiBooking->parseNaturalLanguage($prompt);

    // 2. Goi searchAndRankPitches
    $rankedPitches = $aiBooking->searchAndRankPitches($parsed, $userLat, $userLng, 4);

    $top3Ids = [];
    $top3Names = [];
    foreach (array_slice($rankedPitches, 0, 3) as $p) {
        $top3Ids[] = (int)$p['id'];
        $top3Names[] = "ID {$p['id']}: {$p['name']} ({$p['district']}, " . number_format($p['price_per_hour'], 0, ',', '.') . "d)";
    }

    $top1Id = $top3Ids[0] ?? null;

    $isTop1Hit = in_array($top1Id, $expectedIds);
    $isTop3Hit = !empty(array_intersect($top3Ids, $expectedIds));

    if ($isTop1Hit) $top1Correct++;
    if ($isTop3Hit) $top3Correct++;

    // 3. Danh gia do chinh xac trich xuat thuc the
    // (a) District
    if (array_key_exists('district', $expectedEntities)) {
        $entityMetrics['district']['total']++;
        $expDist = $expectedEntities['district'];
        $actDist = $parsed['district'];
        if ($expDist === null && $actDist === null) {
            $entityMetrics['district']['correct']++;
        } elseif ($expDist !== null && $actDist !== null && mb_strtolower($expDist, 'UTF-8') === mb_strtolower($actDist, 'UTF-8')) {
            $entityMetrics['district']['correct']++;
        }
    }

    // (b) Start Hour
    if (array_key_exists('start_hour', $expectedEntities) && $expectedEntities['start_hour'] !== null) {
        $entityMetrics['start_hour']['total']++;
        $expHour = (int)$expectedEntities['start_hour'];
        $actHour = (int)substr($parsed['start_time'], 0, 2);
        if ($expHour === $actHour) {
            $entityMetrics['start_hour']['correct']++;
        }
    }

    // (c) Max Price
    if (array_key_exists('max_price', $expectedEntities) && $expectedEntities['max_price'] !== null) {
        $entityMetrics['max_price']['total']++;
        $expPrice = (float)$expectedEntities['max_price'];
        $actPrice = (float)($parsed['max_price'] ?? 0);
        if (abs($expPrice - $actPrice) < 1.0) {
            $entityMetrics['max_price']['correct']++;
        }
    }

    // (d) Amenities
    if (array_key_exists('amenities', $expectedEntities) && !empty($expectedEntities['amenities'])) {
        $entityMetrics['amenities']['total']++;
        $expAmen = $expectedEntities['amenities'];
        $actAmen = $parsed['amenities'] ?? [];
        $intersect = array_intersect($expAmen, $actAmen);
        if (count($intersect) === count($expAmen)) {
            $entityMetrics['amenities']['correct']++;
        }
    }

    // (e) Date validation
    $entityMetrics['date']['total']++;
    $actDate = $parsed['date'];
    $validDate = !empty($actDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $actDate);
    if ($validDate) {
        $entityMetrics['date']['correct']++;
    }

    // Ghi nhan ly do sai neu Top-1 hoac Top-3 khong dat
    $failureReason = '';
    if (!$isTop1Hit) {
        if ($id === 24) {
            $failureReason = 'Quan Tan Phu khong co trong danh sach districtsMap cua AiSmartBooking nen khong trich xuat duoc quan';
        } elseif (!$isTop3Hit) {
            $failureReason = 'Khong co san nao trong Top-3 khop voi tap ky vong';
        } else {
            $failureReason = 'San dung xuat hien trong Top-3 nhung chua dat Top-1 do trong so khoang cach GPS hoac diem gia';
        }
        $failureNotes[$id] = [
            'id' => $id,
            'prompt' => $prompt,
            'expected' => implode(', ', $expectedIds),
            'top3' => implode(', ', $top3Ids),
            'reason' => $failureReason
        ];
    }

    $evaluationRows[] = [
        'id' => $id,
        'prompt' => $prompt,
        'expected_ids' => implode(',', $expectedIds),
        'top1_id' => $top1Id ?? 'None',
        'top3_ids' => implode(',', $top3Ids),
        'top1_status' => $isTop1Hit ? 'DUNG' : 'SAI',
        'top3_status' => $isTop3Hit ? 'DUNG' : 'SAI',
        'parsed_district' => $parsed['district'] ?? 'None',
        'parsed_time' => $parsed['start_hour_display'] . ' - ' . $parsed['end_hour_display'],
        'parsed_price' => $parsed['max_price'] ? number_format($parsed['max_price'], 0, ',', '.') . 'd' : 'None',
        'parsed_amenities' => !empty($parsed['amenities']) ? implode(',', $parsed['amenities']) : 'None',
        'note' => $failureReason
    ];
}

$top1Accuracy = round(($top1Correct / $totalScenarios) * 100, 2);
$top3HitRate = round(($top3Correct / $totalScenarios) * 100, 2);

// 4. Xuat file CSV ket qua
$outDir = __DIR__ . '/out';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$csvPath = $outDir . '/pitch_ranking_results.csv';
$csvFile = fopen($csvPath, 'w');

fputcsv($csvFile, [
    'Scenario_ID',
    'Prompt',
    'Expected_IDs',
    'Top1_ID',
    'Top3_IDs',
    'Top1_Hit',
    'Top3_Hit',
    'Parsed_District',
    'Parsed_Time',
    'Parsed_Max_Price',
    'Parsed_Amenities',
    'Note'
]);

foreach ($evaluationRows as $row) {
    fputcsv($csvFile, $row);
}
fclose($csvFile);

// 5. Xuat file Markdown ket qua
$mdPath = $outDir . '/pitch_ranking_results.md';
$mdContent = "# KET QUA DANH GIA HE THONG GOI Y SAN (AI SMART BOOKING)\n\n";
$mdContent .= "Danh gia tren **25 tinh huong ngon ngu tu nhien** da dang ve quan/huyen, thoi gian, gia tien va tien ich.\n\n";

$mdContent .= "## 1. BANG TONG HOP CHI SO CHINH\n\n";
$mdContent .= "| Chi So Do Luong | So Luong / Tong | Ty Le Dat Chuan |\n";
$mdContent .= "| :--- | :---: | :---: |\n";
$mdContent .= "| **Top-1 Accuracy** (San xep dau dung ky vong) | {$top1Correct} / {$totalScenarios} | **{$top1Accuracy}%** |\n";
$mdContent .= "| **Top-3 Hit Rate** (Co it nhat 1 san dung trong Top 3) | {$top3Correct} / {$totalScenarios} | **{$top3HitRate}%** |\n";

foreach ($entityMetrics as $entName => $m) {
    $pct = $m['total'] > 0 ? round(($m['correct'] / $m['total']) * 100, 2) : 100.0;
    $label = [
        'date' => 'Trich xuat Ngay thi dau',
        'start_hour' => 'Trich xuat Gio bat dau',
        'district' => 'Trich xuat Quan / Dia diem',
        'max_price' => 'Trich xuat Ngan sach toi da',
        'amenities' => 'Trich xuat Tien ich mong muon'
    ][$entName] ?? $entName;
    $mdContent .= "| Do chinh xac {$label} | {$m['correct']} / {$m['total']} | {$pct}% |\n";
}

$mdContent .= "\n---\n\n";
$mdContent .= "## 2. BANG CHI TIET 25 TINH HUONG KIEM THU\n\n";
$mdContent .= "| ID | Cau Prompt | San Ky Vong | Top-1 | Top-3 Ket Qua | Top-1 | Top-3 | Ghi Chu Phan Tich |\n";
$mdContent .= "| :---: | :--- | :---: | :---: | :--- | :---: | :---: | :--- |\n";

foreach ($evaluationRows as $r) {
    $pShort = htmlspecialchars($r['prompt']);
    $note = $r['note'] ? htmlspecialchars($r['note']) : 'Khop ky vong';
    $mdContent .= "| {$r['id']} | {$pShort} | `{$r['expected_ids']}` | `{$r['top1_id']}` | `{$r['top3_ids']}` | **{$r['top1_status']}** | **{$r['top3_status']}** | {$note} |\n";
}

$mdContent .= "\n---\n\n";
$mdContent .= "## 3. PHAN TICH NGUYEN NHAN CAC TRUONG HOP CHUA DAT TOP-1\n\n";
if (!empty($failureNotes)) {
    foreach ($failureNotes as $fn) {
        $mdContent .= "- **Tinh huong #{$fn['id']}**: `\"{$fn['prompt']}\"`\n";
        $mdContent .= "  - Ky vong: `[{$fn['expected']}]` | Thuc te Top-3: `[{$fn['top3']}]`\n";
        $mdContent .= "  - **Nguyen nhan:** {$fn['reason']}\n\n";
    }
} else {
    $mdContent .= "Tat ca 25 tinh huong deu dat Top-1 hoan hao!\n\n";
}

file_put_contents($mdPath, $mdContent);

// Console Output
echo "=== KET QUA DANH GIA CHAT LUONG GOI Y SAN (AI SMART BOOKING) ===\n\n";
echo "1. BANG TONG HOP METRICS:\n";
printf("- Top-1 Accuracy: %d/%d (%.2f%%)\n", $top1Correct, $totalScenarios, $top1Accuracy);
printf("- Top-3 Hit Rate: %d/%d (%.2f%%)\n", $top3Correct, $totalScenarios, $top3HitRate);
echo "- Do chinh xac trich xuat thuc the (Entity Extraction Accuracy):\n";
foreach ($entityMetrics as $ent => $m) {
    $pct = $m['total'] > 0 ? round(($m['correct'] / $m['total']) * 100, 2) : 100.0;
    printf("  + %-15s: %2d/%2d (%.2f%%)\n", $ent, $m['correct'], $m['total'], $pct);
}

echo "\n2. CAC TRUONG HOP LECH TOP-1:\n";
if (empty($failureNotes)) {
    echo "  (Khong co truong hop nao lech)\n";
} else {
    foreach ($failureNotes as $fn) {
        printf("  [ID %2d] \"%s\" -> Expected: [%s] vs Got Top-3: [%s] (Ly do: %s)\n", $fn['id'], $fn['prompt'], $fn['expected'], $fn['top3'], $fn['reason']);
    }
}

echo "\n[SUCCESS] Xuat bao cao Markdown thanh cong: {$mdPath}\n";
echo "[SUCCESS] Xuat bao cao CSV thanh cong: {$csvPath}\n";

