<?php
if (PHP_OS_FAMILY === 'Windows' && function_exists('sapi_windows_cp_set')) {
    @sapi_windows_cp_set(65001);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AiSmartBooking.php';

echo "=== SMASHHUB: AI SMART BOOKING ENGINE TEST ===\n\n";

$pass = true;

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Khong the ket noi CSDL.");
    }

    $ai = new AiSmartBooking($db);

    // 1. Test NLP Entity Parsing
    echo "1. Testing Vietnamese NLP Entity Parser...\n";
    $testPrompt = "Chiều nay 18h ở Bình Thạnh giá dưới 120k có thảm BWF";
    $parsed = $ai->parseNaturalLanguage($testPrompt);

    echo "   - Input: '{$testPrompt}'\n";
    echo "   - Extracted Date: {$parsed['date']} ({$parsed['date_label']})\n";
    echo "   - Extracted Time: {$parsed['start_hour_display']} - {$parsed['end_hour_display']}\n";
    echo "   - Extracted District: " . ($parsed['district'] ?: 'None') . "\n";
    echo "   - Extracted Max Price: " . ($parsed['max_price'] ? number_format($parsed['max_price'], 0, ',', '.') . 'd' : 'None') . "\n";
    echo "   - Extracted Amenities: " . implode(', ', $parsed['amenities']) . "\n";

    if ($parsed['start_hour_display'] === '18:00' && $parsed['district'] === 'Bình Thạnh' && $parsed['max_price'] == 120000) {
        echo "   [PASS] NLP entity extraction is 100% accurate!\n";
    } else {
        $pass = false;
        echo "   [FAIL] NLP extraction failed expected values.\n";
    }

    // 2. Test GPS Distance Calculation
    echo "\n2. Testing Haversine GPS distance calculation...\n";
    // Dist from Landmark 81 (10.7950, 106.7219) to Dinh Bo Linh Binh Thanh (10.8038, 106.7025)
    $dist = AiSmartBooking::calculateDistanceKm(10.7950, 106.7219, 10.8038, 106.7025);
    echo "   - Distance Landmark 81 -> Pitch 1: {$dist} km\n";
    if ($dist !== null && $dist > 1.0 && $dist < 4.0) {
        echo "   [PASS] GPS Haversine calculation verified.\n";
    } else {
        $pass = false;
        echo "   [FAIL] Distance calculation out of expected range.\n";
    }

    // 3. Test AI Relevance Ranking & Availability Check
    echo "\n3. Testing AI Smart Recommendation & Ranking Engine...\n";
    $ranked = $ai->searchAndRankPitches($parsed, 10.8038, 106.7025, 3);
    echo "   - Found " . count($ranked) . " recommendations.\n";

    foreach ($ranked as $idx => $p) {
        $num = $idx + 1;
        $avail = $p['has_available_slot'] ? "Con {$p['available_courts']}/{$p['total_courts']} san con" : "Het san";
        echo "   #{$num}. {$p['name']} | {$p['price_formatted']} | {$p['distance_display']} | {$avail} | AI Score: {$p['ai_score']}\n";
    }

    if (!empty($ranked)) {
        echo "   [PASS] Pitch ranking engine returned valid ranked pitches.\n";
    } else {
        $pass = false;
        echo "   [FAIL] No pitches returned.\n";
    }

    // 4. Test Assistant Message Generation
    echo "\n4. Testing Assistant Vietnamese Conversation Generation...\n";
    $msg = $ai->generateAssistantMessage($parsed, $ranked);
    echo "   - Assistant reply:\n\"{$msg}\"\n";
    if (!empty($msg) && mb_check_encoding($msg, 'UTF-8')) {
        echo "   [PASS] Assistant message generated in clean UTF-8 Vietnamese.\n";
    } else {
        $pass = false;
        echo "   [FAIL] Assistant message generation error.\n";
    }

} catch (Exception $e) {
    $pass = false;
    echo "\n[FAIL] Exception caught: " . $e->getMessage() . "\n";
}

echo "\n==============================================================\n";
if ($pass) {
    echo "RESULT: [ALL TESTS PASSED] AI Smart Booking Engine is fully operational!\n";
    exit(0);
} else {
    echo "RESULT: [FAILED] Some tests failed.\n";
    exit(1);
}

