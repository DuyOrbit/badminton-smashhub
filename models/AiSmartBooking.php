<?php
/**
 * SmashHub - AI Smart Booking Recommendation Engine
 *
 * Capabilities:
 * 1. Vietnamese Natural Language Processing (NLP Intent & Entity Parser)
 * 2. Geo-spatial GPS Haversine calculation
 * 3. Real-time court availability verification
 * 4. Multi-criteria AI Relevance Scoring & 1-Click Booking Generation
 */

class AiSmartBooking {
    private $conn;
    private $districtsMap = [
        'bình thạnh' => ['lat' => 10.8038, 'lng' => 106.7025, 'name' => 'Bình Thạnh'],
        'binh thanh' => ['lat' => 10.8038, 'lng' => 106.7025, 'name' => 'Bình Thạnh'],
        'gò vấp'     => ['lat' => 10.8388, 'lng' => 106.6653, 'name' => 'Gò Vấp'],
        'go vap'     => ['lat' => 10.8388, 'lng' => 106.6653, 'name' => 'Gò Vấp'],
        'quận 10'    => ['lat' => 10.7745, 'lng' => 106.6677, 'name' => 'Quận 10'],
        'quan 10'    => ['lat' => 10.7745, 'lng' => 106.6677, 'name' => 'Quận 10'],
        'tân bình'   => ['lat' => 10.8015, 'lng' => 106.6547, 'name' => 'Tân Bình'],
        'tan binh'   => ['lat' => 10.8015, 'lng' => 106.6547, 'name' => 'Tân Bình'],
        'quận 1'     => ['lat' => 10.7756, 'lng' => 106.7004, 'name' => 'Quận 1'],
        'quan 1'     => ['lat' => 10.7756, 'lng' => 106.7004, 'name' => 'Quận 1'],
        'quận 3'     => ['lat' => 10.7844, 'lng' => 106.6844, 'name' => 'Quận 3'],
        'quan 3'     => ['lat' => 10.7844, 'lng' => 106.6844, 'name' => 'Quận 3'],
        'thủ đức'    => ['lat' => 10.8494, 'lng' => 106.7537, 'name' => 'Thủ Đức'],
        'thu duc'    => ['lat' => 10.8494, 'lng' => 106.7537, 'name' => 'Thủ Đức'],
        'phú nhuận'  => ['lat' => 10.7992, 'lng' => 106.6803, 'name' => 'Phú Nhuận'],
        'phu nhuan'  => ['lat' => 10.7992, 'lng' => 106.6803, 'name' => 'Phú Nhuận'],
        'quận 7'     => ['lat' => 10.7340, 'lng' => 106.7218, 'name' => 'Quận 7'],
        'quan 7'     => ['lat' => 10.7340, 'lng' => 106.7218, 'name' => 'Quận 7']
    ];

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Bóc tách ý định và thực thể từ câu ngôn ngữ tự nhiên của người dùng
     */
    public function parseNaturalLanguage($prompt) {
        $promptRaw = trim((string)$prompt);
        $promptLower = mb_strtolower($promptRaw, 'UTF-8');

        // 1. Phân tích ngày thi đấu (date)
        $date = date('Y-m-d');
        $dateLabel = 'hôm nay';

        if (mb_strpos($promptLower, 'ngày mai', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'sáng mai', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'tối mai', 0, 'UTF-8') !== false || preg_match('/\bmai\b/u', $promptLower)) {
            $date = date('Y-m-d', strtotime('+1 day'));
            $dateLabel = 'ngày mai (' . date('d/m', strtotime('+1 day')) . ')';
        } elseif (mb_strpos($promptLower, 'ngày mốt', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'hôm kia', 0, 'UTF-8') !== false) {
            $date = date('Y-m-d', strtotime('+2 days'));
            $dateLabel = 'ngày mốt (' . date('d/m', strtotime('+2 days')) . ')';
        } elseif (mb_strpos($promptLower, 'cuối tuần', 0, 'UTF-8') !== false) {
            $daysToSat = (6 - (int)date('w') + 7) % 7;
            if ($daysToSat === 0) $daysToSat = 7;
            $date = date('Y-m-d', strtotime("+{$daysToSat} days"));
            $dateLabel = 'cuối tuần Thứ 7 (' . date('d/m', strtotime("+{$daysToSat} days")) . ')';
        } elseif (preg_match('/(\d{1,2})[\/\-](\d{1,2})/', $promptLower, $mDate)) {
            $day = str_pad($mDate[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($mDate[2], 2, '0', STR_PAD_LEFT);
            $year = date('Y');
            $date = "{$year}-{$month}-{$day}";
            $dateLabel = "ngày {$day}/{$month}";
        }

        // 2. Phân tích khung giờ (start_time & end_time)
        $startHour = null;
        $startMin = '00';
        $durationHours = 1;

        if (preg_match('/(\d{1,2})\s*-\s*(\d{1,2})\s*h/u', $promptLower, $mRange)) {
            $startHour = (int)$mRange[1];
            $endH = (int)$mRange[2];
            if ($endH > $startHour) {
                $durationHours = min(3, max(1, $endH - $startHour));
            }
        } elseif (preg_match('/(\d{1,2})\s*(?:h|giờ|:00)/u', $promptLower, $mHour)) {
            $startHour = (int)$mHour[1];
            // Neu nguoi dung noi 7h tối, 8h tối
            if (mb_strpos($promptLower, 'tối', 0, 'UTF-8') !== false && $startHour >= 1 && $startHour <= 11) {
                $startHour += 12;
            } elseif (mb_strpos($promptLower, 'chiều', 0, 'UTF-8') !== false && $startHour >= 1 && $startHour <= 5) {
                $startHour += 12;
            }
        } elseif (preg_match('/(\d{1,2})h(\d{2})/u', $promptLower, $mTimeExact)) {
            $startHour = (int)$mTimeExact[1];
            $startMin = $mTimeExact[2];
        }

        // Neu khong ghi gio cu the, doan theo buoi trong ngay
        if ($startHour === null) {
            if (mb_strpos($promptLower, 'chiều', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'sau giờ làm', 0, 'UTF-8') !== false) {
                $startHour = 17;
            } elseif (mb_strpos($promptLower, 'tối', 0, 'UTF-8') !== false) {
                $startHour = 19;
            } elseif (mb_strpos($promptLower, 'sáng', 0, 'UTF-8') !== false) {
                $startHour = 8;
            } elseif (mb_strpos($promptLower, 'trưa', 0, 'UTF-8') !== false) {
                $startHour = 12;
            } else {
                // Mac dinh lay gio tiep theo cua ngay hom nay
                $currentH = (int)date('H');
                if ($date === date('Y-m-d') && $currentH >= 6 && $currentH <= 20) {
                    $startHour = min(21, $currentH + 1);
                } else {
                    $startHour = 18; // Default 18:00
                }
            }
        }

        // Gioi han trong khung hoat dong cua san 06:00 - 22:00
        $startHour = max(6, min(21, $startHour));
        $endHour = min(22, $startHour + $durationHours);

        $startTimeStr = str_pad($startHour, 2, '0', STR_PAD_LEFT) . ':' . $startMin . ':00';
        $endTimeStr = str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':' . $startMin . ':00';

        // 3. Phân tích quận / địa điểm (district)
        $detectedDistrict = null;
        $inferredLat = null;
        $inferredLng = null;

        foreach ($this->districtsMap as $kw => $info) {
            if (mb_strpos($promptLower, $kw, 0, 'UTF-8') !== false) {
                $detectedDistrict = $info['name'];
                $inferredLat = $info['lat'];
                $inferredLng = $info['lng'];
                break;
            }
        }

        // 4. Phân tích ngân sách giá tối đa (max_price)
        $maxPrice = null;
        if (preg_match('/(?:dưới|<|tầm|khoảng|max)\s*(\d{2,3})\s*k/u', $promptLower, $mPriceK)) {
            $maxPrice = (float)$mPriceK[1] * 1000;
        } elseif (preg_match('/(?:dưới|<|tầm|khoảng|max)\s*(\d{1,3}(?:\.\d{3})+)/u', $promptLower, $mPriceFull)) {
            $maxPrice = (float)str_replace('.', '', $mPriceFull[1]);
        } elseif (mb_strpos($promptLower, 'sinh viên', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'giá rẻ', 0, 'UTF-8') !== false) {
            $maxPrice = 90000;
        }

        // 5. Phân tích tiện ích mong muốn (amenities)
        $preferredAmenities = [];
        if (mb_strpos($promptLower, 'thảm', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'bwf', 0, 'UTF-8') !== false) {
            $preferredAmenities[] = 'bwf';
        }
        if (mb_strpos($promptLower, 'bãi xe', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'ô tô', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'oto', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'xe hơi', 0, 'UTF-8') !== false) {
            $preferredAmenities[] = 'parking';
        }
        if (mb_strpos($promptLower, 'máy lạnh', 0, 'UTF-8') !== false || mb_strpos($promptLower, 'cooling', 0, 'UTF-8') !== false) {
            $preferredAmenities[] = 'cooling';
        }

        return [
            'raw_prompt'         => $promptRaw,
            'date'               => $date,
            'date_label'         => $dateLabel,
            'start_time'         => $startTimeStr,
            'end_time'           => $endTimeStr,
            'start_hour_display' => substr($startTimeStr, 0, 5),
            'end_hour_display'   => substr($endTimeStr, 0, 5),
            'district'           => $detectedDistrict,
            'inferred_lat'       => $inferredLat,
            'inferred_lng'       => $inferredLng,
            'max_price'          => $maxPrice,
            'amenities'          => $preferredAmenities
        ];
    }

    /**
     * Thuật toán tính khoảng cách Haversine giữa 2 tọa độ GPS (km)
     */
    public static function calculateDistanceKm($lat1, $lon1, $lat2, $lon2) {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }
        $earthRadius = 6371; // km
        $dLat = deg2rad((float)$lat2 - (float)$lat1);
        $dLon = deg2rad((float)$lon2 - (float)$lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    /**
     * Tìm kiếm và chấm điểm xếp hạng AI (AI Relevance Scoring Engine)
     */
    public function searchAndRankPitches($parsed, $userLat = null, $userLng = null, $limit = 4) {
        require_once __DIR__ . '/Booking.php';
        $bookingModel = new Booking($this->conn);

        // Uu tien lay toa do GPS thuc te tu client, neu khong thi dung toa do suy dien tu quan
        $targetLat = ($userLat !== null && $userLat !== '') ? (float)$userLat : $parsed['inferred_lat'];
        $targetLng = ($userLng !== null && $userLng !== '') ? (float)$userLng : $parsed['inferred_lng'];

        // Truy van danh sach tat ca cac cum san dang hoat dong
        $sql = "SELECT id, name, location, district, city, latitude, longitude, type, quantity, price_per_hour, image_url, amenities, description
                FROM pitches 
                WHERE status = 'active'";
        $params = [];

        // Neu co xac dinh quan va khong co GPS thi filter theo quan
        if ($parsed['district'] && ($userLat === null || $userLat === '')) {
            $sql .= " AND (district LIKE ? OR location LIKE ?)";
            $params[] = '%' . $parsed['district'] . '%';
            $params[] = '%' . $parsed['district'] . '%';
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $allPitches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Neu filter theo quan khong co ket qua, fallback lay toan bo san de tim kiem rong
        if (empty($allPitches) && $parsed['district']) {
            $fallbackStmt = $this->conn->query("SELECT id, name, location, district, city, latitude, longitude, type, quantity, price_per_hour, image_url, amenities, description FROM pitches WHERE status = 'active'");
            $allPitches = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $results = [];

        foreach ($allPitches as $p) {
            $pitchId = (int)$p['id'];
            $totalCourts = (int)$p['quantity'];
            $price = (float)$p['price_per_hour'];

            // 1. Kiem tra tinh trang san trong thuc te (Real-time Availability)
            $occupied = $bookingModel->getConfirmedOverlappingCount(
                $pitchId, 
                $parsed['date'], 
                $parsed['start_time'], 
                $parsed['end_time']
            );
            $available = max(0, $totalCourts - $occupied);

            // Tinh khoang cach GPS Haversine
            $distKm = null;
            if ($targetLat !== null && $targetLng !== null && !empty($p['latitude']) && !empty($p['longitude'])) {
                $distKm = self::calculateDistanceKm($targetLat, $targetLng, (float)$p['latitude'], (float)$p['longitude']);
            }

            // 2. Tinh diem phu hop AI (Relevance Score)
            // Diem Khoang cach: Cang gan diem cang cao (100d neu <= 1.5km, giam dan)
            $distScore = 50;
            if ($distKm !== null) {
                $distScore = max(10, min(100, round(100 - ($distKm * 8))));
            }

            // Diem Gia tien: Neu trong ngan sach duoc 100d, vuot ngan sach bi tru diem
            $priceScore = 70;
            if ($parsed['max_price'] && $parsed['max_price'] > 0) {
                if ($price <= $parsed['max_price']) {
                    $priceScore = 100;
                } else {
                    $diffPercent = (($price - $parsed['max_price']) / $parsed['max_price']) * 100;
                    $priceScore = max(10, 100 - ($diffPercent * 1.5));
                }
            }

            // Diem San trong: Co san trong thuong 100d, het san phat nang
            $slotScore = ($available > 0) ? (70 + min(30, $available * 10)) : 0;

            // Diem Tien ich: Khop tien ich yeu cau
            $amenityScore = 50;
            $pitchAmenities = strtolower((string)$p['amenities']);
            if (!empty($parsed['amenities'])) {
                $matchedCount = 0;
                foreach ($parsed['amenities'] as $am) {
                    if (strpos($pitchAmenities, $am) !== false) {
                        $matchedCount++;
                    }
                }
                $amenityScore = ($matchedCount / count($parsed['amenities'])) * 100;
            }

            // Tong diem trong so da muc tieu
            $totalScore = ($distScore * 0.35) + ($priceScore * 0.25) + ($slotScore * 0.30) + ($amenityScore * 0.10);

            // Neu het san hoan toan thi tru diem tong de uu tien san con cho
            if ($available <= 0) {
                $totalScore -= 40;
            }

            $bookingUrl = "index.php?action=book&id={$pitchId}&date={$parsed['date']}&start=" . substr($parsed['start_time'], 0, 5) . "&end=" . substr($parsed['end_time'], 0, 5);

            $results[] = [
                'id'                 => $pitchId,
                'name'               => $p['name'],
                'location'           => $p['location'],
                'district'           => $p['district'],
                'price_per_hour'     => $price,
                'price_formatted'    => number_format($price, 0, ',', '.') . 'đ/h',
                'image_url'          => $p['image_url'] ?: 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?w=600',
                'type'               => $p['type'],
                'amenities'          => $p['amenities'],
                'total_courts'       => $totalCourts,
                'occupied_courts'    => $occupied,
                'available_courts'   => $available,
                'has_available_slot' => ($available > 0),
                'distance_km'        => $distKm,
                'distance_display'   => $distKm !== null ? "{$distKm} km" : "Gần bạn",
                'suggested_time'     => substr($parsed['start_time'], 0, 5) . ' - ' . substr($parsed['end_time'], 0, 5),
                'booking_url'        => $bookingUrl,
                'ai_score'           => round($totalScore, 1)
            ];
        }

        // Sap xep giam dan theo AI Relevance Score
        usort($results, function($a, $b) {
            return $b['ai_score'] <=> $a['ai_score'];
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Sinh cau thoai phan hoi tu nhien than thien cho nguoi dung
     */
    public function generateAssistantMessage($parsed, $rankedPitches) {
        $count = count($rankedPitches);
        $timeStr = "{$parsed['start_hour_display']} - {$parsed['end_hour_display']}";
        $dateStr = $parsed['date_label'];

        if ($count === 0) {
            return "Trợ lý AI đã tìm kiếm vào lúc {$timeStr} {$dateStr} nhưng hiện tại chưa tìm thấy cụm sân nào phù hợp. Bạn thử mở rộng khoảng cách hoặc chuyển sang khung giờ lân cận nhé!";
        }

        $availableCount = 0;
        foreach ($rankedPitches as $p) {
            if ($p['has_available_slot']) {
                $availableCount++;
            }
        }

        $districtText = $parsed['district'] ? "tại khu vực {$parsed['district']}" : "quanh vị trí của bạn";
        $priceText = $parsed['max_price'] ? "với ngân sách dưới " . number_format($parsed['max_price'], 0, ',', '.') . "đ" : "";

        $msg = "AI đã quét dữ liệu sân thực tế vào lúc **{$timeStr} {$dateStr}** {$districtText} {$priceText}.\n";

        if ($availableCount > 0) {
            $msg .= "Tìm thấy **{$availableCount} cụm sân tối ưu còn sân con trống**. Bạn có thể bấm **Đặt Ngay** để chốt lịch tức thì:";
        } else {
            $msg .= "Các sân trong khung giờ này đang kín chỗ, tuy nhiên mình gợi ý các cụm sân chất lượng tốt nhất gần bạn để bạn tham khảo hoặc đổi giờ:";
        }

        return $msg;
    }
}
?>

