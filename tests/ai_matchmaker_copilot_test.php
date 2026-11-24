<?php
/**
 * Test Suite: AI Active Matchmaking Copilot
 * Tests Natural Language Opponent Search, 1-Click Ticket Recommendation, and Direct Invite Integration
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../ai_engine/Predictor.php';
require_once __DIR__ . '/../models/MatchTicket.php';

$database = new Database();
$db = $database->getConnection();

echo "=== BAT DAU KIEM THU AI COPILOT CHU DONG TIM TRAN & TIM DOI ===\n\n";

$predictor = new Predictor($db);

// 1. Kiem thu tim kiem doi thu khi he thong chua co ve cho
$testQuery1 = "Tìm đối thủ ở Bình Thạnh 18h";
$res1 = $predictor->processMessage($testQuery1, 10.8038, 106.7025, 10);

assert($res1['intent'] === 'radar_matchmaking', "Intent phai la radar_matchmaking, nhan duoc: " . $res1['intent']);
assert(!empty($res1['entities']['district']), "Entity district phai duoc trich xuat");
assert($res1['entities']['district'] === 'Bình Thạnh', "District phai la Binh Thanh");
assert($res1['quick_ticket'] !== null, "Phai co quick_ticket de tao ve 1-click");

echo "[PASS] 1. AI xu ly cau hoi tim doi: '{$testQuery1}' thanh cong!\n";
echo "   - Intent: [{$res1['intent']}] (Do tin cay: " . round($res1['confidence'] * 100, 1) . "%)\n";
echo "   - Quan: {$res1['entities']['district']} | Gio: {$res1['entities']['start_time']}\n";
echo "   - Quick Ticket de xuat 1-Click: " . json_encode($res1['quick_ticket'], JSON_UNESCAPED_UNICODE) . "\n\n";

// 2. Tao du lieu mau: 1 nguoi choi (User 7) mo lenh tim doi tai Binh Thanh 18:00
$ticketModel = new MatchTicket($db);
$createdTicket = $ticketModel->createTicket([
    'user_id' => 7,
    'play_date' => date('Y-m-d'),
    'start_time' => '18:00:00',
    'end_time' => '20:00:00',
    'format' => 'Đánh Đôi',
    'district' => 'Bình Thạnh',
    'user_lat' => 10.8038,
    'user_lng' => 106.7025,
    'radius_km' => 10.0,
    'has_pitch' => 0,
    'specified_pitch_id' => null,
    'note' => 'Test AI Copilot Opponent Discovery'
], true);

assert($createdTicket['success'] === true, "Tao ve cho User 7 that bai: " . ($createdTicket['message'] ?? ''));
$ticketId = $createdTicket['ticket_id'] ?? null;
if (!$ticketId) {
    $tCheck = $db->query("SELECT id FROM match_tickets WHERE user_id = 7 AND status = 'waiting' ORDER BY id DESC LIMIT 1");
    $ticketId = (int)$tCheck->fetchColumn();
}
echo "[PASS] 2. Da khoi tao ve cho User 7 tai Binh Thanh 18h (Ticket ID #{$ticketId})\n\n";

// 3. User 10 hoi AI: 'Tìm người đánh cùng ở Bình Thạnh tối nay'
$testQuery2 = "Tìm người đánh cùng ở Bình Thạnh tối nay";
$res2 = $predictor->processMessage($testQuery2, 10.8038, 106.7025, 10);

assert($res2['intent'] === 'radar_matchmaking', "Intent phai la radar_matchmaking");
assert(!empty($res2['opponents']), "Phai tim thay doi thu dang cho!");
$firstOpp = $res2['opponents'][0];
assert((int)$firstOpp['ticket_id'] === (int)$ticketId, "Doi thu phu hop nhat phai la Ticket #{$ticketId}");
assert(!empty($firstOpp['elo']), "Doi thu phai co diem Elo");
assert(!empty($firstOpp['rank_name']), "Doi thu phai co Rank Name");

echo "[PASS] 3. AI Copilot chu dong quet thay doi thu dang cho:\n";
echo "   - Query: '{$testQuery2}'\n";
echo "   - Tim thay: " . count($res2['opponents']) . " doi thu phu hop\n";
echo "   - Doi thu #1: {$firstOpp['full_name']} (Ticket #{$firstOpp['ticket_id']}) - Rank: {$firstOpp['rank_name']} ({$firstOpp['elo']} Elo)\n";
echo "   - Gio dau: {$firstOpp['time_slot']} | Quan: {$firstOpp['district']} | Khoang cach: {$firstOpp['distance_display']}\n\n";

// 4. Kiem tra cau hoi ly thuyet / giai thich radar: khong tra ve the doi thu
$testQuery3 = "Giải thích thuật toán ghép trận và tính điểm Elo hoạt động thế nào";
$res3 = $predictor->processMessage($testQuery3, 10.8038, 106.7025, 10);
assert(empty($res3['opponents']), "Cau hoi ly thuyet khong duoc tra ve the doi thu");
assert(strpos($res3['reply'], 'Radar') !== false, "Phan hoi phai chua noi dung giai thich");

echo "[PASS] 4. AI phan biet chinh xac cau hoi ly thuyet vs hanh dong tim doi thuc te:\n";
echo "   - Query: '{$testQuery3}'\n";
echo "   - So luong doi thu: " . count($res3['opponents']) . " (Dung quy chuan ly thuyet)\n\n";

// 5. Don dep du lieu test
$db->exec("DELETE FROM radar_invites WHERE sender_ticket_id = {$ticketId} OR target_ticket_id = {$ticketId}");
$db->exec("DELETE FROM match_tickets WHERE id = {$ticketId}");
echo "[PASS] 5. Don dep du lieu kiem thu thanh cong.\n\n";

echo "=== TAT CA KIEM THU AI COPILOT CHU DONG TIM TRAN & TIM DOI: 100% SUCCESS ===\n";
