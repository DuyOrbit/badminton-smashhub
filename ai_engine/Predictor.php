<?php
/**
 * SmashHub AI Engine - Real-Time Inference & Conversational Dialog Predictor
 * Language: Pure PHP 7.4+
 */

require_once __DIR__ . '/Tokenizer.php';
require_once __DIR__ . '/TfIdfVectorizer.php';
require_once __DIR__ . '/NaiveBayesClassifier.php';

class Predictor {
    private $classifier;
    private $modelLoaded = false;
    private $db;

    public function __construct($db = null) {
        $this->db = $db;
        $this->loadModel();
    }

    private function loadModel() {
        $weightsPath = __DIR__ . '/model_weights.json';
        if (!file_exists($weightsPath)) {
            // Neu chua train, chay train ngay
            require_once __DIR__ . '/train.php';
        }

        if (file_exists($weightsPath)) {
            $raw = file_get_contents($weightsPath);
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $this->classifier = new NaiveBayesClassifier($data['metrics']['alpha'] ?? 0.1);
                $this->classifier->loadModel($data);
                $this->modelLoaded = true;
            }
        }
    }

    /**
     * Xu ly tin nhan cua nguoi dung, tra ve phan hoi hoi thoai kem du lieu san truc tiep
     */
    public function processMessage($message, $userLat = null, $userLng = null) {
        $msg = trim((string)$message);
        if ($msg === '') {
            return [
                'reply'      => 'Bạn cần tôi hỗ trợ tìm sân, tư vấn vợt hay giải đáp luật thi đấu cầu lông?',
                'intent'     => 'greeting',
                'confidence' => 1.0,
                'pitches'    => []
            ];
        }

        // 1. Du doan y dinh (Intent Classification qua mo hinh Machine Learning da train)
        $classification = $this->classifier ? $this->classifier->predict($msg) : ['intent' => 'find_pitch', 'confidence' => 0.7];
        $intent = $classification['intent'];
        $confidence = $classification['confidence'];

        // 2. Trich xuat thuc the (Entity Extraction)
        $entities = $this->extractEntities($msg);

        // 3. Xu ly logic nghiep vu & Sinh cau tra loi tu nhien
        $pitches = [];
        $reply = '';

        switch ($intent) {
            case 'find_pitch':
            case 'ask_price':
                $pitchResult = $this->handlePitchSearch($entities, $userLat, $userLng);
                $reply = $pitchResult['reply'];
                $pitches = $pitchResult['pitches'];
                break;

            case 'racket_consult':
                $reply = $this->handleRacketConsultation($msg, $entities);
                break;

            case 'split_bill_info':
                $reply = $this->handleSplitBillInfo($msg);
                break;

            case 'rule_inquiry':
                $reply = $this->handleRuleInquiry($msg);
                break;

            case 'radar_matchmaking':
                $reply = $this->handleRadarMatchmakingInfo($msg);
                break;

            case 'greeting':
            default:
                $reply = "Xin chào! Tôi là Trợ Lý AI SmashHub được huấn luyện chuyên sâu cho bộ môn cầu lông. Tôi có thể hỗ trợ bạn:\n\n"
                       . "1. Đặt sân thông minh theo vị trí GPS, khung giờ và ngân sách.\n"
                       . "2. Tư vấn chọn vợt, căng cước phù hợp lực cổ tay và lối đánh.\n"
                       . "3. Hướng dẫn cơ chế chia đôi tiền sân 50/50 và chống bùng kèo.\n"
                       . "4. Giải đáp luật thi đấu cầu lông chuẩn BWF.\n\n"
                       . "Bạn muốn trải nghiệm tính năng nào trước?";
                break;
        }

        return [
            'reply'      => $reply,
            'intent'     => $intent,
            'confidence' => $confidence,
            'entities'   => $entities,
            'pitches'    => $pitches
        ];
    }

    /**
     * Trich xuat cac thuc the tu van ban: quan, gio, ngay, gia, tien ich
     */
    private function extractEntities($text) {
        $lower = mb_strtolower($text, 'UTF-8');

        // Quan / Dia diem
        $district = null;
        $districtMap = [
            'bình thạnh' => 'Bình Thạnh',
            'binh thanh' => 'Bình Thạnh',
            'gò vấp'     => 'Gò Vấp',
            'go vap'     => 'Gò Vấp',
            'quận 10'    => 'Quận 10',
            'quan 10'    => 'Quận 10',
            'tân bình'   => 'Tân Bình',
            'tan binh'   => 'Tân Bình',
            'quận 1'     => 'Quận 1',
            'quận 3'     => 'Quận 3',
            'quận 7'     => 'Quận 7',
            'thủ đức'    => 'Thủ Đức',
            'phú nhuận'  => 'Phú Nhuận'
        ];
        $districtCoords = [
            'Bình Thạnh' => ['lat' => 10.8038, 'lng' => 106.7025],
            'Gò Vấp'     => ['lat' => 10.8388, 'lng' => 106.6653],
            'Quận 10'    => ['lat' => 10.7745, 'lng' => 106.6677],
            'Tân Bình'   => ['lat' => 10.8015, 'lng' => 106.6547],
            'Quận 1'     => ['lat' => 10.7756, 'lng' => 106.7004],
            'Quận 3'     => ['lat' => 10.7844, 'lng' => 106.6844],
            'Quận 7'     => ['lat' => 10.7340, 'lng' => 106.7218],
            'Thủ Đức'    => ['lat' => 10.8494, 'lng' => 106.7537],
            'Phú Nhuận'  => ['lat' => 10.7992, 'lng' => 106.6803]
        ];

        foreach ($districtMap as $kw => $name) {
            if (mb_strpos($lower, $kw, 0, 'UTF-8') !== false) {
                $district = $name;
                break;
            }
        }

        // Ngay
        $date = date('Y-m-d');
        $dateLabel = 'hôm nay';
        if (mb_strpos($lower, 'ngày mai', 0, 'UTF-8') !== false || mb_strpos($lower, 'sáng mai', 0, 'UTF-8') !== false || mb_strpos($lower, 'tối mai', 0, 'UTF-8') !== false || preg_match('/\bmai\b/u', $lower)) {
            $date = date('Y-m-d', strtotime('+1 day'));
            $dateLabel = 'ngày mai (' . date('d/m', strtotime('+1 day')) . ')';
        } elseif (mb_strpos($lower, 'ngày mốt', 0, 'UTF-8') !== false) {
            $date = date('Y-m-d', strtotime('+2 days'));
            $dateLabel = 'ngày mốt (' . date('d/m', strtotime('+2 days')) . ')';
        } elseif (mb_strpos($lower, 'cuối tuần', 0, 'UTF-8') !== false) {
            $daysToSat = (6 - (int)date('w') + 7) % 7;
            if ($daysToSat === 0) $daysToSat = 7;
            $date = date('Y-m-d', strtotime("+{$daysToSat} days"));
            $dateLabel = 'cuối tuần Thứ 7 (' . date('d/m', strtotime("+{$daysToSat} days")) . ')';
        }

        // Gio
        $startHour = null;
        if (preg_match('/(\d{1,2})\s*(?:h|giờ)/u', $lower, $mH)) {
            $startHour = (int)$mH[1];
            if (mb_strpos($lower, 'tối', 0, 'UTF-8') !== false && $startHour >= 1 && $startHour <= 11) {
                $startHour += 12;
            } elseif (mb_strpos($lower, 'chiều', 0, 'UTF-8') !== false && $startHour >= 1 && $startHour <= 5) {
                $startHour += 12;
            }
        } elseif (mb_strpos($lower, 'chiều', 0, 'UTF-8') !== false) {
            $startHour = 17;
        } elseif (mb_strpos($lower, 'tối', 0, 'UTF-8') !== false) {
            $startHour = 19;
        } elseif (mb_strpos($lower, 'sáng', 0, 'UTF-8') !== false) {
            $startHour = 8;
        }

        if ($startHour === null) {
            $currentH = (int)date('H');
            $startHour = ($currentH < 20) ? $currentH + 1 : 18;
        }
        $startHour = max(6, min(21, $startHour));
        $endHour = min(22, $startHour + 1);

        // Gia toi da
        $maxPrice = null;
        if (preg_match('/(?:dưới|tầm|khoảng|<)\s*(\d+)\s*(?:k|ngàn|nghìn|đ)/u', $lower, $mP)) {
            $val = (int)$mP[1];
            $maxPrice = ($val < 1000) ? $val * 1000 : $val;
        }

        // Tien ich
        $amenities = [];
        if (mb_strpos($lower, 'bwf', 0, 'UTF-8') !== false || mb_strpos($lower, 'thảm xịn', 0, 'UTF-8') !== false) {
            $amenities[] = 'bwf';
        }
        if (mb_strpos($lower, 'ô tô', 0, 'UTF-8') !== false || mb_strpos($lower, 'bãi xe', 0, 'UTF-8') !== false) {
            $amenities[] = 'parking';
        }

        $inferredLat = ($district && isset($districtCoords[$district])) ? $districtCoords[$district]['lat'] : null;
        $inferredLng = ($district && isset($districtCoords[$district])) ? $districtCoords[$district]['lng'] : null;

        return [
            'district'     => $district,
            'inferred_lat' => $inferredLat,
            'inferred_lng' => $inferredLng,
            'date'         => $date,
            'date_label'   => $dateLabel,
            'start_time'   => sprintf('%02d:00:00', $startHour),
            'end_time'     => sprintf('%02d:00:00', $endHour),
            'start_str'    => sprintf('%02d:00', $startHour),
            'end_str'      => sprintf('%02d:00', $endHour),
            'start_hour_display' => sprintf('%02d:00', $startHour),
            'end_hour_display'   => sprintf('%02d:00', $endHour),
            'max_price'    => $maxPrice,
            'amenities'    => $amenities
        ];
    }

    private function handlePitchSearch($entities, $userLat = null, $userLng = null) {
        if (!$this->db) {
            return [
                'reply'   => 'Đã ghi nhận yêu cầu tìm sân của bạn.',
                'pitches' => []
            ];
        }

        require_once __DIR__ . '/../models/AiSmartBooking.php';
        $aiEngine = new AiSmartBooking($this->db);
        $ranked = $aiEngine->searchAndRankPitches($entities, $userLat, $userLng, 3);

        $timeStr = "{$entities['start_str']} - {$entities['end_str']}";
        $dateStr = $entities['date_label'];
        $distStr = $entities['district'] ? "tại khu vực {$entities['district']}" : "quanh vị trí của bạn";
        $priceStr = $entities['max_price'] ? "với mức giá dưới " . number_format($entities['max_price'], 0, ',', '.') . "đ" : "";

        if (empty($ranked)) {
            $reply = "AI đã quét dữ liệu lúc **{$timeStr} {$dateStr}** {$distStr} nhưng hiện tại chưa tìm thấy cụm sân phù hợp. Bạn thử mở rộng khoảng cách hoặc chuyển sang khung giờ lân cận nhé!";
        } else {
            $availableCount = 0;
            foreach ($ranked as $p) {
                if (!empty($p['has_available_slot'])) {
                    $availableCount++;
                }
            }
            $reply = "Mô hình AI đã phân tích dữ liệu thực tế lúc **{$timeStr} {$dateStr}** {$distStr} {$priceStr}.\n\n";
            if ($availableCount > 0) {
                $reply .= "Tìm thấy **{$availableCount} cụm sân tối ưu còn sân con trống** (đã xếp hạng theo khoảng cách GPS và mức giá). Bạn có thể bấm nút **Đặt Ngay** bên dưới:";
            } else {
                $reply .= "Các sân trong khung giờ này đang kín lịch, tuy nhiên hệ thống gợi ý các cụm sân chất lượng tốt nhất gần bạn để bạn tham khảo hoặc đổi khung giờ:";
            }
        }

        return [
            'reply'   => $reply,
            'pitches' => $ranked
        ];
    }

    private function handleRacketConsultation($msg, $entities) {
        $lower = mb_strtolower($msg, 'UTF-8');

        if (mb_strpos($lower, 'cổ tay yếu', 0, 'UTF-8') !== false || mb_strpos($lower, 'nữ', 0, 'UTF-8') !== false || mb_strpos($lower, 'mới chơi', 0, 'UTF-8') !== false) {
            return "**Tư vấn chuyên môn từ AI SmashHub cho người mới chơi / cổ tay yếu:**\n\n"
                 . "1. **Trọng lượng vợt:** Bạn nên chọn thông số **4U (80-84g)** hoặc **5U (75-79g)**. Thân vợt có độ dẻo vừa (Medium Flex) để hỗ trợ trợ lực khi vung tay.\n"
                 . "2. **Điểm cân bằng:** Chọn vợt **Cân bằng (Even Balance: 285-295mm)** hoặc **Nhẹ đầu (Head Light)** để dễ điều cầu và thủ phản tạt, tránh bị mỏi cổ tay.\n"
                 . "3. **Mức căng cước:** Khuyên dùng từ **9.5 kg đến 10.5 kg (21 - 23 lbs)**. Dây cước căng vừa phải sẽ cho độ nảy trợ lực cao và mặt lưới êm ái.\n"
                 . "4. **Gợi ý mẫu vợt quốc dân:** Yonex Astrox 77 Play, Lining Windstorm 72, Lining Axforce 20 hoặc Yonex Nanoflare 001.";
        }

        if (mb_strpos($lower, 'smash', 0, 'UTF-8') !== false || mb_strpos($lower, 'tấn công', 0, 'UTF-8') !== false || mb_strpos($lower, 'đập cầu', 0, 'UTF-8') !== false) {
            return "**Tư vấn chuyên môn cho lối đánh Tấn Công / Smash uy lực:**\n\n"
                 . "1. **Trọng lượng & Điểm cân bằng:** Bạn nên chọn dòng vợt **Nặng đầu (Head Heavy: > 300mm)** trọng lượng **4U hoặc 3U**, thân đũa từ trung bình đến cứng (Stiff) để dồn toàn bộ lực vung vào quả cầu.\n"
                 . "2. **Mức căng dây:** Từ **10.5 kg - 11.5 kg (23 - 25 lbs)** với tay đánh có lực tốt. Cước gợi ý: **Yonex BG66 Ultimax** (nảy cực tốt, tiếng nổ đanh) hoặc **Yonex BG80 Power** (bám cầu, uy lực).\n"
                 . "3. **Gợi ý mẫu vợt:** Yonex Astrox 88D Game/Pro, Yonex Astrox 99 Play, Lining Halbertec 8000 hoặc Victor Thruster K.";
        }

        return "**Tư vấn chuyên môn chọn vợt cầu lông toàn diện:**\n\n"
             . "- **Lối đánh Công Thủ Toàn Diện:** Khuyên dùng vợt điểm cân bằng 290-295mm, trọng lượng 4U, thân dẻo vừa. Dễ xoay chuyển giữa đập cầu và thủ lưới.\n"
             . "- **Chọn dây cước:** Người mới nên căng **Yonex BG65** (bền bỉ, kinh tế) hoặc **BG65 Titanium**. Người có kinh nghiệm thích tiếng nổ và lực nảy nên căng **BG66 Ultimax** ở mức 10.5 kg.\n"
             . "- **Giày cầu lông:** Luôn chọn giày chuyên dụng có đế cao su non (Gum Rubber) và đệm giảm chấn (Power Cushion) để bảo vệ khớp gối và chống lật cổ chân khi bật nhảy.";
    }

    private function handleSplitBillInfo($msg) {
        return "**Cơ chế Chia Đôi Tiền Sân (Split Bill 50/50) & Chống Bùng Kèo:**\n\n"
             . "1. **Thanh toán chia đôi:** Khi được ghép trận thành công, hệ thống tự động chia hóa đơn làm 2 nửa bằng nhau (mỗi bên thanh toán 50% qua mã VietQR Sandbox hoặc tiền mặt tại quầy).\n"
             . "2. **Điểm Uy Tín Fair-Play:** Mỗi người chơi khởi đầu với **100 Điểm Uy Tín**. Đánh xong trận bình thường được **+2 điểm**.\n"
             . "3. **Chế tài chống bùng kèo (No-Show):**\n"
             . "   - Hủy trước 2 tiếng: trừ 5 điểm uy tín.\n"
             . "   - Hủy sát giờ (dưới 2 tiếng): trừ 15 điểm uy tín.\n"
             . "   - Bùng kèo không đến: bị Chủ sân bấm xác nhận trừ **-30 Điểm Uy Tín, -50 Điểm Elo** và **Khóa tính năng tìm trận 3 ngày**.\n"
             . "   - Nếu điểm uy tín dưới **50 điểm**, tài khoản sẽ bị hệ thống tự động khóa tính năng ghép kèo.";
    }

    private function handleRuleInquiry($msg) {
        $lower = mb_strtolower($msg, 'UTF-8');

        if (mb_strpos($lower, 'phát cầu', 0, 'UTF-8') !== false || mb_strpos($lower, '1m15', 0, 'UTF-8') !== false || mb_strpos($lower, 'giao cầu', 0, 'UTF-8') !== false) {
            return "**Quy định Luật Phát Cầu Lông mới nhất của BWF:**\n\n"
                 . "1. **Độ cao phát cầu cố định:** Toàn bộ quả cầu phải nằm ở độ cao **dưới 1.15 mét** tính từ mặt sân tại thời điểm vợt chạm vào cầu (thay thế cho luật cũ quy định dưới thắt lưng).\n"
                 . "2. **Vị trí chân:** Khi phát cầu, cả hai chân của người phát cầu và người nhận cầu phải đứng cố định trong ô chéo sân tương ứng, không được chạm vạch biên.\n"
                 . "3. **Cầu chạm lưới:** Khi giao cầu, nếu quả cầu chạm mép trên của lưới nhưng vẫn rơi vào đúng ô nhận cầu của đối phương thì pha giao cầu vẫn hợp lệ và trận đấu tiếp tục (không phải giao lại).";
        }

        if (mb_strpos($lower, 'chạm lưới', 0, 'UTF-8') !== false) {
            return "**Luật Chạm Lưới trong Cầu Lông:**\n\n"
                 . "- Trong khi quả cầu còn đang trong cuộc, nếu người chơi để vợt, thân thể hoặc trang phục **chạm vào lưới hoặc cột lưới** thì bị tính là phạm quy (Lỗi - Fault) và đối phương được cộng 1 điểm.\n"
                 . "- Chỉ khi quả cầu đã chạm đất (ngoài cuộc) thì việc vô tình chạm lưới mới không bị xử phạt.";
        }

        return "**Luật Thi Đấu Cầu Lông Cơ Bản BWF (Hệ thống tính điểm 21):**\n\n"
             . "1. **Thể thức trận đấu:** Thi đấu theo thể thức 3 ván thắng 2 (Best of 3). Mỗi ván đánh đến **21 điểm**, bên nào ghi điểm sẽ được quyền giao cầu ở lượt tiếp theo.\n"
             . "2. **Tình huống hòa 20 - 20 (Deuce):** Bên nào dẫn trước cách biệt 2 điểm trước sẽ thắng ván đó (ví dụ 22-20, 24-22). Điểm số tối đa của ván là **30 điểm** (nếu hòa 29-29 thì bên nào chạm 30 trước sẽ thắng).\n"
             . "3. **Đổi sân:** Hai bên đổi sân sau hiệp 1, hiệp 2 (nếu có hiệp 3) và đổi sân khi chạm mốc **11 điểm** ở hiệp thi đấu quyết định thứ 3.";
    }

    private function handleRadarMatchmakingInfo($msg) {
        return "**Tính Năng Radar Quét Bạn Đấu Lân Cận (Nearby Proximity Matchmaking):**\n\n"
             . "1. **Quét theo GPS thực tế:** Radar tự động tính khoảng cách Haversine km giữa bạn và các cơ thủ xung quanh đang mở lệnh tìm trận (`waiting`).\n"
             . "2. **Cân bằng trình độ (Elo Scoring):** Hệ thống lọc những người chơi có mức chênh lệch Elo nhỏ, đảm bảo trận đấu kịch tính và cân trình.\n"
             . "3. **Bắt cặp 1-Click:** Khi thấy bạn đấu phù hợp, bấm **`[Rủ Bắt Cặp Ngay]`**. Đối phương sẽ nhận thông báo trên màn hình để bấm **`[Đồng Ý]`**, hai bên sẽ lập tức được xếp vào phòng chung để chia đôi hóa đơn 50/50!\n\n"
             . "Bạn có thể vào trang **Ghép Trận Thông Minh** ở menu trên để mở phòng ghép ngay nhé!";
    }
}
