<?php
/**
 * SmashHub Elo Convergence Simulation
 * Script: tests/eval/elo_simulation.php
 * Evaluates the convergence of estimated Elo ratings towards true player skill
 * under (a) Standard Pure Elo K=32 and (b) SmashHub Production Rules in Ranking::processMatchResult
 * Language: Pure PHP 7.4+
 */

require_once __DIR__ . '/../../models/Ranking.php';

// Set fixed random seed for 100% reproducibility
mt_srand(42);

$trueSkills = [
    1 => 100,
    2 => 250,
    3 => 400,
    4 => 550,
    5 => 700,
    6 => 850,
    7 => 1000,
    8 => 1150
];

$playerIds = array_keys($trueSkills);
$numPlayers = count($playerIds);
$defaultElo = Ranking::DEFAULT_ELO; // 100

// Initialize ratings
$ratingsPure = array_fill_keys($playerIds, (float)$defaultElo);
$ratingsCode = array_fill_keys($playerIds, (int)$defaultElo);

$totalMatches = 400;
$checkpoints = [0, 25, 50, 100, 200, 400];

// Storage for trajectories
$trajectoryPure = [0 => $ratingsPure];
$trajectoryCode = [0 => $ratingsCode];
$totalEloPure = [0 => array_sum($ratingsPure)];
$totalEloCode = [0 => array_sum($ratingsCode)];

// Helper: Spearman Rank Correlation
function calculateSpearman($actual, $expected) {
    $n = count($actual);
    if ($n <= 1) return 1.0;

    $getRanks = function($arr) use ($n) {
        $items = [];
        foreach ($arr as $k => $v) {
            $items[] = ['k' => $k, 'v' => (float)$v];
        }
        usort($items, function($a, $b) {
            if ($a['v'] == $b['v']) return 0;
            return ($a['v'] < $b['v']) ? -1 : 1;
        });

        $ranks = [];
        $i = 0;
        while ($i < $n) {
            $j = $i;
            while ($j < $n - 1 && $items[$j + 1]['v'] == $items[$j]['v']) {
                $j++;
            }
            $avgRank = ($i + 1 + $j + 1) / 2.0;
            for ($k = $i; $k <= $j; $k++) {
                $ranks[$items[$k]['k']] = $avgRank;
            }
            $i = $j + 1;
        }
        return $ranks;
    };

    $ranksActual = $getRanks($actual);
    $ranksExpected = $getRanks($expected);

    $sumD2 = 0.0;
    foreach ($actual as $k => $val) {
        $diff = $ranksActual[$k] - $ranksExpected[$k];
        $sumD2 += $diff * $diff;
    }

    return 1.0 - (6.0 * $sumD2) / ($n * ($n * $n - 1));
}

// Helper: Normalized MAE
function calculateNormalizedMAE($estimated, $groundTruth) {
    $n = count($estimated);
    $minTruth = min($groundTruth);
    $maxTruth = max($groundTruth);
    $rangeTruth = ($maxTruth - $minTruth) > 0 ? ($maxTruth - $minTruth) : 1.0;

    $minEst = min($estimated);
    $maxEst = max($estimated);
    $rangeEst = ($maxEst - $minEst) > 0 ? ($maxEst - $minEst) : 1.0;

    $sumAbsDiff = 0.0;
    foreach ($groundTruth as $k => $trueVal) {
        $normTrue = ($trueVal - $minTruth) / $rangeTruth;
        $normEst = ($estimated[$k] - $minEst) / $rangeEst;
        $sumAbsDiff += abs($normTrue - $normEst);
    }

    return $sumAbsDiff / $n;
}

// Checkpoint data collector
$checkpointResults = [];

// Record checkpoint 0
$checkpointResults[0] = [
    'match' => 0,
    'pure_spearman' => calculateSpearman($ratingsPure, $trueSkills),
    'pure_nmae' => calculateNormalizedMAE($ratingsPure, $trueSkills),
    'pure_total_elo' => array_sum($ratingsPure),
    'code_spearman' => calculateSpearman($ratingsCode, $trueSkills),
    'code_nmae' => calculateNormalizedMAE($ratingsCode, $trueSkills),
    'code_total_elo' => array_sum($ratingsCode),
];

// Pre-generate random pairings and outcomes from true skills
$matches = [];
for ($m = 1; $m <= $totalMatches; $m++) {
    // Pick 2 distinct players
    $p1 = mt_rand(1, $numPlayers);
    do {
        $p2 = mt_rand(1, $numPlayers);
    } while ($p2 === $p1);

    $trueA = $trueSkills[$p1];
    $trueB = $trueSkills[$p2];

    // Win probability of A based on true skill
    $probA = 1.0 / (1.0 + pow(10.0, ($trueB - $trueA) / 400.0));
    $randVal = mt_rand(1, 1000000) / 1000000.0;
    $winner = ($randVal < $probA) ? $p1 : $p2;

    $matches[] = [
        'match_id' => $m,
        'p1' => $p1,
        'p2' => $p2,
        'winner' => $winner
    ];
}

// Run simulation
foreach ($matches as $match) {
    $m = $match['match_id'];
    $p1 = $match['p1'];
    $p2 = $match['p2'];
    $winner = $match['winner'];

    // --- (a) Pure Elo Standard Formula ---
    $rA = $ratingsPure[$p1];
    $rB = $ratingsPure[$p2];
    $expA = 1.0 / (1.0 + pow(10.0, ($rB - $rA) / 400.0));
    $expB = 1.0 / (1.0 + pow(10.0, ($rA - $rB) / 400.0));
    $actA = ($winner === $p1) ? 1.0 : 0.0;
    $actB = ($winner === $p2) ? 1.0 : 0.0;
    $K = 32.0;

    $ratingsPure[$p1] = $rA + $K * ($actA - $expA);
    $ratingsPure[$p2] = $rB + $K * ($actB - $expB);

    // --- (b) Production Code Rules (Ranking::processMatchResult, lines 37-60 and lines 92-94) ---
    // Source: models/Ranking.php
    // Expected probability calculation
    // Point calculation with (int)round(K * (actual - expected))
    // Floor constraint: max(0, current + delta)
    $hostRating = $ratingsCode[$p1];
    $guestRating = $ratingsCode[$p2];

    $expectedHost = 1.0 / (1.0 + pow(10.0, ($guestRating - $hostRating) / 400.0));
    $expectedGuest = 1.0 / (1.0 + pow(10.0, ($hostRating - $guestRating) / 400.0));

    $actualHost = ($winner === $p1) ? 1.0 : 0.0;
    $actualGuest = ($winner === $p2) ? 1.0 : 0.0;

    $host_points = (int)round($K * ($actualHost - $expectedHost));
    $guest_points = (int)round($K * ($actualGuest - $expectedGuest));

    $ratingsCode[$p1] = max(0, $hostRating + $host_points);
    $ratingsCode[$p2] = max(0, $guestRating + $guest_points);

    // Record trajectory
    $trajectoryPure[$m] = $ratingsPure;
    $trajectoryCode[$m] = $ratingsCode;
    $totalEloPure[$m] = array_sum($ratingsPure);
    $totalEloCode[$m] = array_sum($ratingsCode);

    if (in_array($m, $checkpoints)) {
        $checkpointResults[$m] = [
            'match' => $m,
            'pure_spearman' => round(calculateSpearman($ratingsPure, $trueSkills), 4),
            'pure_nmae' => round(calculateNormalizedMAE($ratingsPure, $trueSkills), 4),
            'pure_total_elo' => round(array_sum($ratingsPure), 2),
            'code_spearman' => round(calculateSpearman($ratingsCode, $trueSkills), 4),
            'code_nmae' => round(calculateNormalizedMAE($ratingsCode, $trueSkills), 4),
            'code_total_elo' => array_sum($ratingsCode),
        ];
    }
}

// 1. Output CSV Report
$outDir = __DIR__ . '/out';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$csvPath = $outDir . '/elo_convergence.csv';
$csvFile = fopen($csvPath, 'w');

// Write Checkpoints Summary Table
fputcsv($csvFile, ['--- ELO CONVERGENCE CHECKPOINTS SUMMARY ---']);
fputcsv($csvFile, [
    'Match_Count',
    'Pure_Spearman_Rs',
    'Pure_Normalized_MAE',
    'Pure_Total_Elo',
    'Code_Spearman_Rs',
    'Code_Normalized_MAE',
    'Code_Total_Elo'
]);

foreach ($checkpointResults as $row) {
    fputcsv($csvFile, $row);
}

fputcsv($csvFile, []);
fputcsv($csvFile, ['--- FINAL RATINGS AT MATCH 400 ---']);
fputcsv($csvFile, ['Player_ID', 'True_Skill', 'Pure_Elo_Estimated', 'Code_Elo_Estimated', 'Code_Deviation_From_True']);
foreach ($trueSkills as $pId => $tSkill) {
    $pElo = round($ratingsPure[$pId], 2);
    $cElo = $ratingsCode[$pId];
    $dev = $cElo - $tSkill;
    fputcsv($csvFile, [$pId, $tSkill, $pElo, $cElo, $dev]);
}

fputcsv($csvFile, []);
fputcsv($csvFile, ['--- TRAJECTORY DATA (EVERY 10 MATCHES) ---']);
$headerTraj = ['Match', 'Total_Elo_Pure', 'Total_Elo_Code'];
for ($p = 1; $p <= $numPlayers; $p++) {
    $headerTraj[] = "P{$p}_Pure";
    $headerTraj[] = "P{$p}_Code";
}
fputcsv($csvFile, $headerTraj);

for ($m = 0; $m <= $totalMatches; $m += 10) {
    $row = [
        $m,
        round($totalEloPure[$m], 2),
        round($totalEloCode[$m], 2)
    ];
    for ($p = 1; $p <= $numPlayers; $p++) {
        $row[] = round($trajectoryPure[$m][$p], 2);
        $row[] = round($trajectoryCode[$m][$p], 2);
    }
    fputcsv($csvFile, $row);
}

fclose($csvFile);

// 2. Output HTML Chart using Chart.js
$labelsJson = json_encode(range(0, $totalMatches, 10));

// Build datasets for Chart 1: Player Elo over matches (Code vs True Skill)
$chart1Datasets = [];
$colors = [
    1 => '#ef4444', // red
    2 => '#f97316', // orange
    3 => '#f59e0b', // amber
    4 => '#eab308', // yellow
    5 => '#10b981', // emerald
    6 => '#06b6d4', // cyan
    7 => '#3b82f6', // blue
    8 => '#8b5cf6'  // violet
];

for ($p = 1; $p <= $numPlayers; $p++) {
    $pData = [];
    for ($m = 0; $m <= $totalMatches; $m += 10) {
        $pData[] = $trajectoryCode[$m][$p];
    }
    $chart1Datasets[] = [
        'label' => "Nguoi choi {$p} (Trinh do that {$trueSkills[$p]})",
        'data' => $pData,
        'borderColor' => $colors[$p],
        'backgroundColor' => $colors[$p],
        'borderWidth' => 2,
        'tension' => 0.1,
        'pointRadius' => 1
    ];
}

// Chart 2: Group Total Elo over matches (Pure vs Code)
$totalPureData = [];
$totalCodeData = [];
for ($m = 0; $m <= $totalMatches; $m += 10) {
    $totalPureData[] = round($totalEloPure[$m], 2);
    $totalCodeData[] = round($totalEloCode[$m], 2);
}

$chart2Datasets = [
    [
        'label' => 'Tong Elo Cong Thuc Thuan (K=32 Bao Toan)',
        'data' => $totalPureData,
        'borderColor' => '#38bdf8',
        'backgroundColor' => 'rgba(56, 189, 248, 0.1)',
        'borderWidth' => 3,
        'tension' => 0.1,
        'fill' => false
    ],
    [
        'label' => 'Tong Elo Quy Tac Code (Sau Khi Bo Muc Cong Toi Thieu +15, Bao Toan Diem)',
        'data' => $totalCodeData,
        'borderColor' => '#10b981',
        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
        'borderWidth' => 3,
        'tension' => 0.1,
        'fill' => false
    ]
];

$chart1Json = json_encode($chart1Datasets);
$chart2Json = json_encode($chart2Datasets);

$htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmashHub - Bao Cao Mo Phong Hoi Tu Elo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0b1116;
            color: #e2e8f0;
            padding: 24px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1, h2 {
            color: #d8ff3e;
            margin-bottom: 8px;
        }
        .subtitle {
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .card {
            background-color: #121b21;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 28px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 12px;
        }
        th, td {
            padding: 10px 14px;
            text-align: right;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        th:first-child, td:first-child {
            text-align: left;
        }
        th {
            background-color: rgba(255, 255, 255, 0.04);
            color: #d8ff3e;
            font-weight: 600;
        }
        .badge-pure {
            color: #38bdf8;
            font-weight: bold;
        }
        .badge-code {
            color: #f43f5e;
            font-weight: bold;
        }
        .chart-box {
            position: relative;
            height: 420px;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SMASHHUB - KET QUA MO PHONG HOI TU ELO (400 TRAN)</h1>
        <div class="subtitle">Danh gia he thong Elo (models/Ranking.php, K=32, DEFAULT_ELO=100) tren 8 nguoi choi co trinh do that [100 - 1150]. Seed: 42.</div>

        <div class="card">
            <h2>1. Bang Thong Ke Cac Moc Kiem Tra (Checkpoints)</h2>
            <table>
                <thead>
                    <tr>
                        <th>So Tran</th>
                        <th>Spearman Rs (Thuan)</th>
                        <th>Normalized MAE (Thuan)</th>
                        <th>Tong Elo (Thuan)</th>
                        <th>Spearman Rs (Code)</th>
                        <th>Normalized MAE (Code)</th>
                        <th>Tong Elo (Code)</th>
                    </tr>
                </thead>
                <tbody>
HTML;

foreach ($checkpointResults as $row) {
    $htmlContent .= "<tr>
        <td><strong>{$row['match']} tran</strong></td>
        <td class=\"badge-pure\">{$row['pure_spearman']}</td>
        <td>{$row['pure_nmae']}</td>
        <td>{$row['pure_total_elo']}</td>
        <td class=\"badge-code\">{$row['code_spearman']}</td>
        <td>{$row['code_nmae']}</td>
        <td class=\"badge-code\">{$row['code_total_elo']}</td>
    </tr>";
}

$htmlContent .= <<<HTML
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>2. Bang So Sanh Diem Elo Uoc Luong Sau 400 Tran</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nguoi Choi</th>
                        <th>Trinh Do That</th>
                        <th>Elo Uoc Luong (Thuan)</th>
                        <th>Elo Uoc Luong (Code)</th>
                        <th>Do Lech Code vs That</th>
                    </tr>
                </thead>
                <tbody>
HTML;

foreach ($trueSkills as $pId => $tSkill) {
    $pElo = round($ratingsPure[$pId], 2);
    $cElo = $ratingsCode[$pId];
    $diff = $cElo - $tSkill;
    $sign = $diff >= 0 ? "+{$diff}" : "{$diff}";
    $htmlContent .= "<tr>
        <td>Nguoi choi {$pId}</td>
        <td><strong>{$tSkill}</strong></td>
        <td class=\"badge-pure\">{$pElo}</td>
        <td class=\"badge-code\">{$cElo}</td>
        <td>{$sign}</td>
    </tr>";
}

$htmlContent .= <<<HTML
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>3. Bieu Do 1: Duong Hoi Tu Elo Cua 8 Nguoi Choi (Quy Tac Code)</h2>
            <div class="subtitle">Quan sat su phan hoa va tien gan ve thu tu xep hang trinh do that sau 400 tran.</div>
            <div class="chart-box">
                <canvas id="chart1"></canvas>
            </div>
        </div>

        <div class="card">
            <h2>4. Bieu Do 2: Bao Toan Tong Diem Elo (Total Group Elo)</h2>
            <div class="subtitle">So sanh tong diem Elo giua cong thuc thuan va quy tac code Ranking::processMatchResult (sau khi bo muc cong toi thieu +15).</div>
            <div class="chart-box">
                <canvas id="chart2"></canvas>
            </div>
        </div>
    </div>

    <script>
        const labels = {$labelsJson};

        // Render Chart 1
        new Chart(document.getElementById('chart1'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: {$chart1Json}
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: '#e2e8f0', font: { size: 11 } } }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'So Tran Dau (Matches)', color: '#94a3b8' },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    y: {
                        title: { display: true, text: 'Diem Elo Uoc Luong', color: '#94a3b8' },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });

        // Render Chart 2
        new Chart(document.getElementById('chart2'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: {$chart2Json}
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { color: '#e2e8f0', font: { size: 12 } } }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'So Tran Dau (Matches)', color: '#94a3b8' },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    y: {
                        title: { display: true, text: 'Tong Diem Elo Cua 8 Nguoi Choi', color: '#94a3b8' },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });
    </script>
</body>
</html>
HTML;

file_put_contents($outDir . '/elo_convergence.html', $htmlContent);

// Console Output
echo "=== KET QUA MO PHONG HOI TU ELO (400 TRAN, SEED: 42) ===\n\n";
echo "1. BANG TIEN TRINH THEO CAC MOC TRAN (CHECKPOINTS):\n";
printf("%-8s | %-16s | %-16s | %-16s | %-16s\n", "Match", "Rs (Pure)", "Rs (Code)", "Total Elo (Pure)", "Total Elo (Code)");
echo "---------+------------------+------------------+------------------+------------------\n";
foreach ($checkpointResults as $m => $r) {
    printf("%-8d | %-16.4f | %-16.4f | %-16.2f | %-16d\n", $m, $r['pure_spearman'], $r['code_spearman'], $r['pure_total_elo'], $r['code_total_elo']);
}

echo "\n2. BANG DIEM ELO SAU 400 TRAN:\n";
printf("%-8s | %-12s | %-16s | %-16s | %-12s\n", "Player", "True Skill", "Pure Elo Est", "Code Elo Est", "Deviation");
echo "---------+--------------+------------------+------------------+--------------\n";
foreach ($trueSkills as $pId => $tSkill) {
    $pElo = round($ratingsPure[$pId], 2);
    $cElo = $ratingsCode[$pId];
    $diff = $cElo - $tSkill;
    printf("P%-7d | %-12d | %-16.2f | %-16d | %+d\n", $pId, $tSkill, $pElo, $cElo, $diff);
}

echo "\n[SUCCESS] Xuat bao cao CSV thanh cong: {$csvPath}\n";
echo "[SUCCESS] Xuat bieu do HTML Chart.js thanh cong: {$outDir}/elo_convergence.html\n";

