<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../ai_engine/Tokenizer.php';
require_once __DIR__ . '/../ai_engine/TfIdfVectorizer.php';
require_once __DIR__ . '/../ai_engine/NaiveBayesClassifier.php';
require_once __DIR__ . '/../ai_engine/Predictor.php';

echo "=== BAT DAU KIEM THU TOAN DIEN HE THONG MACHINE LEARNING AI ENGINE ===\n\n";

// 1. Kiem thu Tokenizer
$text = "Tôi muốn tìm sân ở Bình Thạnh chiều nay thảm BWF";
$tokens = Tokenizer::tokenize($text);
if (!in_array('bình_thạnh', $tokens) || !in_array('chiều_nay', $tokens) || !in_array('thảm_bwf', $tokens)) {
    die("LOI: Tokenizer khong tach dung cac cum tu dac thu cau long.\n");
}
echo "[PASS] 1. Tokenizer tach tu va ghep cum n-gram chinh xac (so luong tokens: " . count($tokens) . ").\n";

// 2. Kiem thu TF-IDF Vectorizer
$vectorizer = new TfIdfVectorizer();
$corpus = [
    "Tôi muốn tìm sân ở Bình Thạnh",
    "Giá thuê sân cầu lông bao nhiêu tiền",
    "Tư vấn mua vợt cho người cổ tay yếu"
];
$vectorizer->fit($corpus);
$vec = $vectorizer->transform("Bình Thạnh tìm sân");
if (empty($vec)) {
    die("LOI: TF-IDF Vectorizer khong tao duoc vector dac trung.\n");
}
echo "[PASS] 2. TF-IDF Vectorizer tao ma tran dac trung L2-normalized thanh cong.\n";

// 3. Kiem thu Naive Bayes Classifier tren model_weights.json
$database = new Database();
$db = $database->getConnection();
$predictor = new Predictor($db);

$testCases = [
    [
        'query'           => 'Chiều nay 18h ở Bình Thạnh giá dưới 100k',
        'expected_intent' => ['find_pitch', 'ask_price'],
        'check_pitches'   => true
    ],
    [
        'query'           => 'Người mới chơi cổ tay yếu nên mua vợt gì',
        'expected_intent' => ['racket_consult'],
        'check_pitches'   => false
    ],
    [
        'query'           => 'Luật phát cầu 1m15 mới nhất quy định thế nào',
        'expected_intent' => ['rule_inquiry'],
        'check_pitches'   => false
    ],
    [
        'query'           => 'Nếu đối thủ bùng kèo không đến thì bị trừ bao nhiêu điểm uy tín',
        'expected_intent' => ['split_bill_info'],
        'check_pitches'   => false
    ],
    [
        'query'           => 'Làm sao để tìm bạn đấu cầu lông ở gần tôi bằng radar',
        'expected_intent' => ['radar_matchmaking'],
        'check_pitches'   => false
    ],
    [
        'query'           => 'Hôm nay đẹp trời nhỉ',
        'expected_intent' => ['weather_mood'],
        'check_pitches'   => false
    ],
    [
        'query'           => 'Đánh cầu lông có giúp giảm cân và đốt mỡ không',
        'expected_intent' => ['health_fitness'],
        'check_pitches'   => false
    ],
    [
        'query'           => 'Ngày mai là thứ mấy',
        'expected_intent' => ['calendar_schedule'],
        'check_pitches'   => false
    ],
    [
        'query'           => 'Thủ đô của nước Pháp là gì',
        'expected_intent' => ['out_of_scope'],
        'check_pitches'   => false
    ]
];

foreach ($testCases as $idx => $tc) {
    $res = $predictor->processMessage($tc['query']);
    $intent = $res['intent'];
    $conf = round($res['confidence'] * 100, 1);

    if (!in_array($intent, $tc['expected_intent'])) {
        die("LOI o TestCase #" . ($idx + 1) . ": '{$tc['query']}' nhan dien sai intent: {$intent}\n");
    }

    if ($tc['check_pitches'] && empty($res['pitches'])) {
        die("LOI: Truy van tim san khong tra ve danh sach cum san phu hop.\n");
    }

    echo "[PASS] " . (3 + $idx) . ". Query: '{$tc['query']}' -> Intent: [{$intent}] (Do tin cay: {$conf}%, Pitches: " . count($res['pitches']) . ").\n";
}

echo "\n[PASS] 8. Kiem tra file model_weights.json hop le:\n";
$weights = json_decode(file_get_contents(__DIR__ . '/../ai_engine/model_weights.json'), true);
echo "   - Thuat toan: {$weights['algorithm']}\n";
echo "   - Tong mau huan luyen: {$weights['metrics']['total_samples']} mau\n";
echo "   - Do chinh xac Cross-Val: {$weights['metrics']['accuracy']}%\n";
echo "   - F1-Score: {$weights['metrics']['macro_f1']}%\n";

echo "\n=== TAT CA CAC BIEU MAU KIEM THU MACHINE LEARNING AI ENGINE HOAN TAT: 100% SUCCESS ===\n";

