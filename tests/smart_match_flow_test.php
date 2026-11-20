<?php
if (PHP_OS_FAMILY === 'Windows' && function_exists('sapi_windows_cp_set')) {
    @sapi_windows_cp_set(65001);
}

/**
 * SMASHHUB - Smart Match Flow Integration Test
 *
 * Flow:
 * 1. Create two compatible tickets
 * 2. autoMatch
 * 3. Both users confirmMatch
 * 4. Verify booking is created and has a valid court_number
 * 5. Report match result as host_won
 * 6. Verify Ranking score changes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/MatchTicket.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Matchmaking.php';
require_once __DIR__ . '/../models/Ranking.php';

echo "=== SMASHHUB: SMART MATCH FULL FLOW TEST ===\n\n";

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        die("[FAIL] Database connection failed.\n");
    }
} catch (Exception $e) {
    die("[FAIL] DB Exception: " . $e->getMessage() . "\n");
}

$ticketModel = new MatchTicket($db);
$bookingModel = new Booking($db);
$matchModel = new Matchmaking($db);
$rankingModel = new Ranking($db);

$user1Id = 7;
$user2Id = 10;
$pitchId = 1;

$pitchStmt = $db->prepare("SELECT id, name, owner_id FROM pitches WHERE id = ?");
$pitchStmt->execute([$pitchId]);
$pitch = $pitchStmt->fetch(PDO::FETCH_ASSOC);
if (!$pitch) {
    die("[FAIL] Pitch ID {$pitchId} not found.\n");
}
$ownerId = (int)$pitch['owner_id'];

$testDate = date('Y-m-d', strtotime('+5 days'));
$startTime = '20:00:00';
$endTime = '21:00:00';

$createdTicketIds = [];
$createdBookingId = null;
$createdMatchId = null;

try {
    // 1. Create 2 compatible tickets
    echo "1. Creating 2 compatible tickets...\n";
    $t1Res = $ticketModel->createTicket([
        'user_id' => $user1Id,
        'play_date' => $testDate,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'format' => 'Đánh Đơn',
        'district' => 'Bình Thạnh',
        'specified_pitch_id' => $pitchId,
        'has_pitch' => 1,
        'note' => 'Test ticket 1'
    ]);
    if (!$t1Res['success']) {
        throw new Exception("Failed to create ticket 1: " . ($t1Res['message'] ?? 'unknown'));
    }
    $t1Id = (int)$t1Res['ticket_id'];
    $createdTicketIds[] = $t1Id;
    echo "   - Ticket 1 created: ID {$t1Id}\n";

    $t2Res = $ticketModel->createTicket([
        'user_id' => $user2Id,
        'play_date' => $testDate,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'format' => 'Đánh Đơn',
        'district' => 'Bình Thạnh',
        'specified_pitch_id' => $pitchId,
        'has_pitch' => 1,
        'note' => 'Test ticket 2'
    ]);
    if (!$t2Res['success']) {
        throw new Exception("Failed to create ticket 2: " . ($t2Res['message'] ?? 'unknown'));
    }
    $t2Id = (int)$t2Res['ticket_id'];
    $createdTicketIds[] = $t2Id;
    echo "   - Ticket 2 created: ID {$t2Id}\n";

    // 2. autoMatch verification
    echo "2. Checking autoMatch status...\n";
    $ticket1 = $ticketModel->getTicketById($t1Id);
    $ticket2 = $ticketModel->getTicketById($t2Id);

    if ($ticket1['status'] !== 'matched' || $ticket2['status'] !== 'matched') {
        // Trigger autoMatch manually if not auto-matched during creation
        $matched = $ticketModel->autoMatch($t1Id);
        $ticket1 = $ticketModel->getTicketById($t1Id);
        $ticket2 = $ticketModel->getTicketById($t2Id);
    }

    if ($ticket1['status'] !== 'matched' || $ticket2['status'] !== 'matched') {
        throw new Exception("Tickets were not matched. Ticket 1: {$ticket1['status']}, Ticket 2: {$ticket2['status']}");
    }
    echo "   [PASS] Both tickets matched successfully.\n";

    // 3. Both confirmMatch
    echo "3. Confirming match from both players...\n";
    $conf1 = $ticketModel->confirmMatch($t1Id, $user1Id, 'vietqr', 'paid');
    if (!$conf1['success'] || $conf1['status'] !== 'waiting_opponent') {
        throw new Exception("Ticket 1 confirmMatch failed: " . json_encode($conf1));
    }
    echo "   - Ticket 1 confirmed (waiting opponent): OK\n";

    $conf2 = $ticketModel->confirmMatch($t2Id, $user2Id, 'vietqr', 'paid');
    if (!$conf2['success'] || $conf2['status'] !== 'fully_confirmed') {
        throw new Exception("Ticket 2 confirmMatch failed: " . json_encode($conf2));
    }
    $createdBookingId = (int)$conf2['booking_id'];
    echo "   - Ticket 2 confirmed (fully_confirmed): OK\n";

    // 4. Verify booking is created and has court_number
    echo "4. Verifying created booking and court_number...\n";
    $bookingData = $bookingModel->readOneWithPitchInfo($createdBookingId);
    if (!$bookingData) {
        throw new Exception("Booking {$createdBookingId} not found in database.");
    }

    $courtNumber = (int)$bookingData['court_number'];
    if ($courtNumber <= 0) {
        throw new Exception("Invalid court_number: {$courtNumber}");
    }
    echo "   [PASS] Booking #{$createdBookingId} created with court_number #{$courtNumber}.\n";

    // 5. Find matchmaking record and report match result as host_won
    echo "5. Reporting match result as host_won...\n";
    $mStmt = $db->prepare("SELECT id, user_id, opponent_id, match_result FROM matchmaking WHERE booking_id = ?");
    $mStmt->execute([$createdBookingId]);
    $matchRow = $mStmt->fetch(PDO::FETCH_ASSOC);
    if (!$matchRow) {
        throw new Exception("Matchmaking row for booking {$createdBookingId} not found.");
    }
    $createdMatchId = (int)$matchRow['id'];
    $hostUserId = (int)$matchRow['user_id'];
    $guestUserId = (int)$matchRow['opponent_id'];

    $season = Ranking::getCurrentSeason();
    $scoreBefore = (int)$rankingModel->getSeasonPoints($hostUserId, $season);

    $reportSuccess = $matchModel->reportResult($createdMatchId, 'host_won', $ownerId);
    if (!$reportSuccess) {
        throw new Exception("reportResult('host_won') returned false.");
    }
    echo "   [PASS] reportResult('host_won') succeeded.\n";

    // Ensure it cannot be reported again
    $reportAgain = $matchModel->reportResult($createdMatchId, 'host_won', $ownerId);
    if ($reportAgain) {
        throw new Exception("reportResult allowed reporting a second time!");
    }
    echo "   [PASS] Duplicate reportResult correctly rejected.\n";

    // 6. Verify Ranking score changes
    echo "6. Checking Ranking score update...\n";
    $scoreAfter = (int)$rankingModel->getSeasonPoints($hostUserId, $season);
    echo "   - Host (User #{$hostUserId}) Elo: {$scoreBefore} -> {$scoreAfter} (Diff: " . ($scoreAfter - $scoreBefore) . ")\n";

    if ($scoreAfter <= $scoreBefore) {
        throw new Exception("Host Ranking points did not increase after win! Before: {$scoreBefore}, After: {$scoreAfter}");
    }
    echo "   [PASS] Host Ranking points successfully increased.\n";

    echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "\n[FAIL] Test encountered error: " . $e->getMessage() . "\n";
} finally {
    // Cleanup test artifacts
    echo "\nCleaning up test artifacts from database...\n";
    if ($createdMatchId) {
        $db->prepare("DELETE FROM matchmaking WHERE id = ?")->execute([$createdMatchId]);
    }
    if ($createdBookingId) {
        $db->prepare("DELETE FROM bookings WHERE id = ?")->execute([$createdBookingId]);
    }
    if (!empty($createdTicketIds)) {
        $in = implode(',', array_map('intval', $createdTicketIds));
        $db->exec("DELETE FROM match_tickets WHERE id IN ({$in})");
    }
    echo "Cleanup finished.\n";
}
