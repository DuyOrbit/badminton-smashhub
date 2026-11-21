<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Pitch.php';
require_once __DIR__ . '/../models/MatchTicket.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Ranking.php';

echo "=== BAT DAU KIEM THU CHUC NANG RADAR BAT CAP BAN DAU LAN CAN ===\n";

$database = new Database();
$db = $database->getConnection();
$matchTicket = new MatchTicket($db);
$notifModel = new Notification($db);

// 1. Tim 2 user test hoac tao test users
$stmt = $db->query("SELECT id, name FROM users WHERE role = 'user' LIMIT 2");
$testUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($testUsers) < 2) {
    die("LOI: Can it nhat 2 user trong database de thuc hien test.\n");
}

$userA = $testUsers[0];
$userB = $testUsers[1];
echo "User A (Nguoi cho): ID {$userA['id']} - {$userA['name']}\n";
echo "User B (Nguoi tim): ID {$userB['id']} - {$userB['name']}\n";

// Cap nhat GPS gia lap: User A o Binh Thanh (10.8018, 106.6985), User B o Phu Nhuan (10.7992, 106.6800)
$db->prepare("UPDATE users SET latitude = 10.8018, longitude = 106.6985, district = 'Bình Thạnh' WHERE id = ?")->execute([$userA['id']]);
$db->prepare("UPDATE users SET latitude = 10.7992, longitude = 106.6800, district = 'Phú Nhuận' WHERE id = ?")->execute([$userB['id']]);

// 2. Tao ve cho (waiting ticket) cho User A
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$ticketDataA = [
    'user_id' => $userA['id'],
    'play_date' => $tomorrow,
    'start_time' => '17:00:00',
    'end_time' => '19:00:00',
    'format' => 'Đánh Đôi',
    'district' => 'Bình Thạnh',
    'user_lat' => 10.8018,
    'user_lng' => 106.6985,
    'radius_km' => 10.00,
    'has_pitch' => 0,
    'specified_pitch_id' => null,
    'note' => 'Test Radar Proximity'
];

$resA = $matchTicket->createTicket($ticketDataA);
if (!$resA['success'] || empty($resA['ticket_id'])) {
    die("LOI: Khong the tao ticket A test: " . ($resA['message'] ?? '') . "\n");
}
$ticketAId = $resA['ticket_id'];
echo "[PASS] 1. Da tao ticket A (waiting) thanh cong: Ticket ID #{$ticketAId}\n";

// 3. User B quet radar tim kiem nguoi choi lân can
$nearby = $matchTicket->getNearbyWaitingTickets($userB['id'], 10.7992, 106.6800, 15, 5);
echo "So luong nguoi choi lan can quet duoc: " . count($nearby) . "\n";

$foundA = false;
$distA = 0;
foreach ($nearby as $cand) {
    if ($cand['ticket_id'] == $ticketAId) {
        $foundA = true;
        $distA = $cand['distance_km'];
        break;
    }
}

if (!$foundA) {
    die("LOI: Radar khong quet thay ticket A cua User A trong ban kinh hop le.\n");
}
echo "[PASS] 2. Radar quet thay User A thanh cong! Khoang cach tinh toan: {$distA} km (Duoi 5km giua Binh Thanh va Phu Nhuan).\n";

// 4. User B gui loi moi bat cap truc tiep toi Ticket A
$inviteRes = $matchTicket->sendDirectNearbyInvite($userB['id'], $ticketAId);
if (!$inviteRes['success']) {
    die("LOI: Gui loi moi bat cap that bai: " . ($inviteRes['message'] ?? '') . "\n");
}
$inviteId = $inviteRes['invite_id'];
echo "[PASS] 3. User B da gui loi moi bat cap thanh cong: Invite ID #{$inviteId}\n";

// Kiem tra notification duoc gui cho User A
$notifCheck = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND title LIKE '%RADAR%' ORDER BY id DESC LIMIT 1");
$notifCheck->execute([$userA['id']]);
$notif = $notifCheck->fetch(PDO::FETCH_ASSOC);
if (!$notif) {
    die("LOI: Khong tim thay thong bao notification duoc gui cho User A.\n");
}
echo "[PASS] 4. User A da nhan duoc notification: {$notif['title']} - {$notif['content']}\n";

// 5. User A nhan dien loi moi den tren Radar (Incoming Invite)
$incoming = $matchTicket->getPendingIncomingInvite($userA['id']);
if (!$incoming || $incoming['invite_id'] != $inviteId) {
    die("LOI: User A khong nhan dien duoc incoming invite tren Radar.\n");
}
echo "[PASS] 5. User A phat hien loi moi den tren Radar tu {$incoming['sender_name']} (cach ~{$incoming['distance_km']} km).\n";

// 6. User A chap nhan loi moi bat cap (Accept Invite)
$respondRes = $matchTicket->respondNearbyInvite($inviteId, $userA['id'], 'accept');
if (!$respondRes['success']) {
    die("LOI: Chap nhan loi moi that bai: " . ($respondRes['message'] ?? '') . "\n");
}
echo "[PASS] 6. User A da dong y bat cap! Phan hoi: {$respondRes['message']}\n";

// Kiem tra ca 2 ticket da duoc chuyen sang 'matched' va co matched_ticket_id
$checkT1 = $matchTicket->getTicketById($ticketAId);
$senderTicketId = $incoming['sender_ticket_id'];
$checkT2 = $matchTicket->getTicketById($senderTicketId);

if ($checkT1['status'] !== 'matched' || $checkT2['status'] !== 'matched') {
    die("LOI: Trang thai ticket chua chuyen sang 'matched'. T1: {$checkT1['status']}, T2: {$checkT2['status']}\n");
}
if ($checkT1['matched_ticket_id'] != $senderTicketId || $checkT2['matched_ticket_id'] != $ticketAId) {
    die("LOI: Hai ticket chua duoc cross-link matched_ticket_id chinh xac.\n");
}
echo "[PASS] 7. Ca hai ticket da duoc ket noi vao phong ghep keo 50/50 tai san: {$checkT1['pitch_name']}\n";

// Clean up test data
$db->prepare("DELETE FROM radar_invites WHERE id = ?")->execute([$inviteId]);
$db->prepare("DELETE FROM match_tickets WHERE id IN (?, ?)")->execute([$ticketAId, $senderTicketId]);
$db->prepare("DELETE FROM notifications WHERE id = ?")->execute([$notif['id']]);
echo "[PASS] 8. Don dep du lieu kiem thu thanh cong.\n";

echo "=== TAT CA CAC BIEU MAU KIEM THU RADAR LAN CAN HOAN TAT: 100% SUCCESS ===\n";
