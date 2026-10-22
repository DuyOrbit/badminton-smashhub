<?php
class MatchmakingController {
    public function index() {
        $database = new Database();
        $db = $database->getConnection();

        $currentUser = isset($_SESSION['user_id']) ? (new User($db))->readOne($_SESSION['user_id']) : null;
        $matchModel = new Matchmaking($db);
        $matches = $matchModel->readAllOpen($currentUser);

        $page_title = "Bảng Tin Ghép Sân Cầu Lông";
        $content = 'matchmaking/index.php';
        require_once 'views/layout.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            // 1. Kiểm tra Sân coi còn trống không (Tránh bị ngâm lịch)
            $pitch = new Pitch($db);
            $pitchData = $pitch->readOne($_POST['pitch_id']);
            
            $booking = new Booking($db);
            $overlapping = $booking->getConfirmedOverlappingCount($_POST['pitch_id'], $_POST['play_date'], $_POST['start_time'], $_POST['end_time']);
            
            if ($overlapping >= $pitchData['quantity']) {
                header("Location: index.php?action=matchmaking&error=fully_booked");
                exit;
            }

            // Fix lỗi người dùng nhập giờ kết thúc trước giờ bắt đầu
            $start_test = strtotime($_POST['start_time']);
            $end_test = strtotime($_POST['end_time']);
            if ($end_test <= $start_test) {
                header("Location: index.php?action=matchmaking&error=invalid_time");
                exit;
            }

            // 2. Tự động mượn thông tin User để Đặt Sân (Auto Booking)
            $userModel = new User($db);
            $currentUser = $userModel->readOne($_SESSION['user_id']);
            
            $booking->pitch_id = $_POST['pitch_id'];
            $booking->customer_name = $currentUser['name'];
            $booking->customer_phone = $currentUser['phone'] ?? '0000000000';
            $booking->booking_date = $_POST['play_date'];
            $booking->start_time = $_POST['start_time'];
            $booking->end_time = $_POST['end_time'];
            $booking->user_id = $_SESSION['user_id'];
            
            // Tạm tính giá Full lúc đăng bài (Giá này chỉ để cho Chủ sân theo dõi Booking)
            $start = strtotime($_POST['start_time']);
            $end = strtotime($_POST['end_time']);
            $hours = ($end - $start) / 3600;
            $base_price = $hours * $pitchData['price_per_hour'];
            
            $ranking = new Ranking($db);
            $discount = $ranking->getActiveDiscount($_SESSION['user_id']);
            
            // Tạm tính giá (Host đã giảm giá + Khách mặc định 100%). Khi Khách nhận, giá sẽ Update lại chuẩn xác.
            $host_half = ($base_price / 2) * ((100 - $discount) / 100);
            $guest_half = ($base_price / 2); // Khách tạm tính 0%
            $booking->total_price = $host_half + $guest_half;
            
            if (!$booking->create()) {
                header("Location: index.php?action=matchmaking&error=booking_failed");
                exit;
            }

            // 3. Đăng bài lên Bảng Cáp Kèo
            $match = new Matchmaking($db);
            $match->user_id = $_SESSION['user_id'];
            $match->pitch_id = $_POST['pitch_id'];
            $match->play_date = $_POST['play_date'];
            $match->start_time = $_POST['start_time'];
            $match->end_time = $_POST['end_time'];
            $match->format = $_POST['format'] ?? 'Đánh Đôi';
            $match->skill_level = intval($_POST['skill_level'] ?? 3);
            $match->district = $_POST['district'] ?? ($pitchData['district'] ?? '');
            $match->message = $_POST['message'] ?? '';

            if ($match->create()) {
                $host_final_price = ($base_price / 2) * ((100 - $discount) / 100);
                $_SESSION['last_booking'] = [
                    'name' => $currentUser['name'],
                    'pitch_name' => $pitchData['name'] . " (Nửa Tiền Chủ Kèo)",
                    'date' => $_POST['play_date'],
                    'time' => $_POST['start_time'] . ' - ' . $_POST['end_time'],
                    'total_price' => $host_final_price
                ];
                header("Location: index.php?action=success");
            } else {
                header("Location: index.php?action=matchmaking&error=failed");
            }
            exit;
        }
    }

    public function closeMatch() {
        if (isset($_GET['id'])) {
            $database = new Database();
            $db = $database->getConnection();
            
            $match = new Matchmaking($db);
            if ($match->completeMatch($_GET['id'], $_SESSION['user_id'])) {
                header("Location: index.php?action=matchmaking&msg=closed");
            }
            exit;
        }
    }

    public function acceptMatch() {
        if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
            $database = new Database();
            $db = $database->getConnection();
            
            $matchModel = new Matchmaking($db);

            $query = "SELECT m.*, p.name as pitch_name, p.price_per_hour FROM matchmaking m INNER JOIN pitches p ON m.pitch_id = p.id WHERE m.id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id']]);
            $mData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($mData && $mData['user_id'] == $_SESSION['user_id']) {
                header("Location: index.php?action=matchmaking&error=cannot_accept_own_match");
                exit;
            }
            
            if ($matchModel->acceptMatch($_GET['id'], $_SESSION['user_id'])) {
                $start = strtotime($mData['start_time']);
                $end = strtotime($mData['end_time']);
                $hours = ($end - $start) / 3600;
                $half_base = ($hours * $mData['price_per_hour']) / 2;
                
                require_once 'models/Ranking.php';
                $ranking = new Ranking($db);
                $discount = $ranking->getActiveDiscount($_SESSION['user_id']);
                $guest_final_price = $half_base * ((100 - $discount) / 100);

                $userModel = new User($db);
                $currentUser = $userModel->readOne($_SESSION['user_id']);

                // Update the true total price in bookings table for Owner Dashboard accuracy!
                $host_discount = $ranking->getActiveDiscount($mData['user_id']);
                $host_final_price = $half_base * ((100 - $host_discount) / 100);
                $true_revenue = $host_final_price + $guest_final_price;
                
                $upd_query = "UPDATE bookings SET total_price = ? WHERE user_id = ? AND pitch_id = ? AND booking_date = ? AND start_time = ?";
                $stmt_upd = $db->prepare($upd_query);
                $stmt_upd->execute([$true_revenue, $mData['user_id'], $mData['pitch_id'], $mData['play_date'], $mData['start_time']]);

                $_SESSION['last_booking'] = [
                    'name' => $currentUser['name'],
                    'pitch_name' => $mData['pitch_name'] . " (Nửa Tiền Đội Khách)",
                    'date' => $mData['play_date'],
                    'time' => $mData['start_time'] . ' - ' . $mData['end_time'],
                    'total_price' => $guest_final_price
                ];
                header("Location: index.php?action=success");
            } else {
                header("Location: index.php?action=matchmaking&error=accept_failed");
            }
            exit;
        }
    }

    public function cancelMatch() {
        if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT m.*, p.price_per_hour FROM matchmaking m INNER JOIN pitches p ON m.pitch_id = p.id WHERE m.id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id']]);
            $mData = $stmt->fetch(PDO::FETCH_ASSOC);

            $match = new Matchmaking($db);
            if ($match->cancelMatchByUser($_GET['id'], $_SESSION['user_id'])) {
                if ($mData) {
                    $start = strtotime($mData['start_time']);
                    $end = strtotime($mData['end_time']);
                    $hours = ($end - $start) / 3600;
                    $base_price = $hours * $mData['price_per_hour'];
                    
                    require_once 'models/Ranking.php';
                    $ranking = new Ranking($db);
                    $host_discount = $ranking->getActiveDiscount($mData['user_id']);
                    $host_half = ($base_price / 2) * ((100 - $host_discount) / 100);
                    $guest_half = ($base_price / 2);
                    $original_total = $host_half + $guest_half;

                    $upd = "UPDATE bookings SET total_price = ? WHERE user_id = ? AND pitch_id = ? AND booking_date = ? AND start_time = ?";
                    $sUpd = $db->prepare($upd);
                    $sUpd->execute([$original_total, $mData['user_id'], $mData['pitch_id'], $mData['play_date'], $mData['start_time']]);
                }
                header("Location: index.php?action=my_matches&msg=cancelled");
            } else {
                header("Location: index.php?action=my_matches&error=cancel_failed");
            }
            exit;
        }
    }

    public function myMatches() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        $database = new Database();
        $db = $database->getConnection();
        
        $matchModel = new Matchmaking($db);
        $stmt = $matchModel->readMyMatches($_SESSION['user_id']);
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'models/Ranking.php';
        $ranking = new Ranking($db);
        $myDiscount = $ranking->getActiveDiscount($_SESSION['user_id']);

        $page_title = "Kèo Thi Đấu Của Tôi";
        $content = 'matchmaking/my_matches.php';
        require_once 'views/layout.php';
    }

    // --- SMART ASYNCHRONOUS MATCHMAKING (LẬP LỆNH TÌM TRẬN THEO ELO) ---
    public function smartMatch() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        // Thông tin Rank & Elo hiện tại của User
        require_once 'models/Ranking.php';
        require_once 'models/User.php';
        require_once 'models/MatchTicket.php';
        require_once 'models/Reputation.php';

        $ranking = new Ranking($db);
        $userModel = new User($db);
        $ticketModel = new MatchTicket($db);
        $repModel = new Reputation($db);

        $currentUser = $userModel->readOne($_SESSION['user_id']);
        if (!$currentUser) {
            unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
            header("Location: index.php?action=login");
            exit;
        }
        $season = Ranking::getCurrentSeason();
        $currentElo = $ranking->getSeasonPoints($_SESSION['user_id'], $season);
        if ($currentElo <= 0) $currentElo = 100;
        $rankName = $ranking->getRankName($currentElo);
        $discount = $ranking->getActiveDiscount($_SESSION['user_id']);

        // Điểm Uy Tín Fair-Play & Lịch sử biến động
        $userReputation = $repModel->getUserReputation($_SESSION['user_id']);
        $reputationLogs = $repModel->getHistory($_SESSION['user_id'], 10);

        // Danh sách các Ticket của User
        $myTickets = $ticketModel->getUserTickets($_SESSION['user_id']);

        // Danh sách toàn bộ sân để người dùng chọn khi lập lệnh "Đã có sân"
        require_once 'models/Pitch.php';
        $pitchModel = new Pitch($db);
        $allPitches = $pitchModel->readAll()->fetchAll(PDO::FETCH_ASSOC);
        $districtCounts = $pitchModel->getActiveDistrictsWithCount();

        $page_title = "Ghép Trận Thông Minh & Điểm Uy Tín";
        $content = 'matchmaking/smart_match.php';
        require_once 'views/layout.php';
    }

    public function storeTicket() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            require_once 'models/MatchTicket.php';
            $ticketModel = new MatchTicket($db);

            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];

            if (strtotime($end_time) <= strtotime($start_time)) {
                header("Location: index.php?action=smart_match&error=" . urlencode("Giờ kết thúc phải sau giờ bắt đầu!"));
                exit;
            }

            $has_pitch = (isset($_POST['pitch_mode']) && $_POST['pitch_mode'] === 'specified') ? 1 : 0;
            $specified_pitch_id = ($has_pitch && !empty($_POST['specified_pitch_id'])) ? (int)$_POST['specified_pitch_id'] : null;

            // Nếu người dùng chọn sẵn sân, tự động cập nhật district theo sân được chọn
            $district = $_POST['district'] ?? 'Bình Thạnh';
            if ($has_pitch && $specified_pitch_id) {
                require_once 'models/Pitch.php';
                $pData = (new Pitch($db))->readOne($specified_pitch_id);
                if ($pData && !empty($pData['district'])) {
                    $district = $pData['district'];
                }
            }

            $user_lat = !empty($_POST['user_lat']) ? floatval($_POST['user_lat']) : null;
            $user_lng = !empty($_POST['user_lng']) ? floatval($_POST['user_lng']) : null;
            $radius_km = !empty($_POST['radius_km']) ? floatval($_POST['radius_km']) : 10.0;
            $location_name = $_POST['location_name'] ?? '';

            $data = [
                'user_id' => $_SESSION['user_id'],
                'play_date' => $_POST['play_date'],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'format' => $_POST['format'] ?? 'Đánh Đôi',
                'district' => $district,
                'user_lat' => $user_lat,
                'user_lng' => $user_lng,
                'radius_km' => $radius_km,
                'location_name' => $location_name,
                'has_pitch' => $has_pitch,
                'specified_pitch_id' => $specified_pitch_id,
                'note' => $_POST['note'] ?? ''
            ];

            $res = $ticketModel->createTicket($data);

            if ($res['success']) {
                $msg = $res['matched'] ? 'matched_instant' : 'ticket_queued';
                header("Location: index.php?action=smart_match&msg=" . $msg);
            } else {
                $err = !empty($res['message']) ? urlencode($res['message']) : 'create_failed';
                header("Location: index.php?action=smart_match&error=" . $err);
            }
            exit;
        }
    }

    public function switchPitch() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['ticket_id']) || !isset($_POST['pitch_id'])) {
            header("Location: index.php?action=smart_match");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        require_once 'models/MatchTicket.php';
        $ticketModel = new MatchTicket($db);

        $res = $ticketModel->switchMatchPitch($_POST['ticket_id'], $_POST['pitch_id'], $_SESSION['user_id']);

        if ($res['success']) {
            header("Location: index.php?action=smart_match&msg=pitch_switched");
        } else {
            header("Location: index.php?action=smart_match&error=" . urlencode($res['message']));
        }
        exit;
    }

    public function confirmTicket() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header("Location: index.php?action=smart_match");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        require_once 'models/MatchTicket.php';
        $ticketModel = new MatchTicket($db);

        $res = $ticketModel->confirmMatch($_GET['id'], $_SESSION['user_id']);

        if ($res['success']) {
            if ($res['status'] == 'fully_confirmed') {
                header("Location: index.php?action=smart_match&msg=fully_confirmed");
            } else {
                header("Location: index.php?action=smart_match&msg=waiting_opponent");
            }
        } else {
            header("Location: index.php?action=smart_match&error=" . urlencode($res['message'] ?? 'failed'));
        }
        exit;
    }

    public function cancelTicket() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header("Location: index.php?action=smart_match");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        require_once 'models/MatchTicket.php';
        $ticketModel = new MatchTicket($db);

        $ticketModel->cancelTicket($_GET['id'], $_SESSION['user_id']);
        header("Location: index.php?action=smart_match&msg=cancelled");
        exit;
    }

    public function apiCheckTicket() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        require_once 'models/MatchTicket.php';
        $ticketModel = new MatchTicket($db);

        // Kích hoạt quét tìm trận nếu ticket vẫn đang 'waiting'
        $ticket = $ticketModel->getTicketById($_GET['id']);
        if ($ticket && $ticket['status'] == 'waiting') {
            $ticketModel->autoMatch($ticket['id']);
            $ticket = $ticketModel->getTicketById($_GET['id']); // lấy lại sau khi quét
        }

        echo json_encode($ticket ?: ['status' => 'not_found']);
        exit;
    }

    public function reportNoShow() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $booking_id = $_GET['booking_id'] ?? $_POST['booking_id'] ?? null;
        $violator_id = $_GET['violator_id'] ?? $_POST['violator_id'] ?? null;
        $detail = $_POST['detail'] ?? 'Bỏ trận không đến sân theo lịch hẹn.';

        if (!$booking_id || !$violator_id) {
            header("Location: index.php?action=my_bookings&error=missing_params");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        require_once 'models/Reputation.php';
        $repModel = new Reputation($db);
        $res = $repModel->reportNoShow($_SESSION['user_id'], $violator_id, $booking_id, $detail);

        if ($res['success']) {
            header("Location: index.php?action=smart_match&msg=noshow_reported");
        } else {
            header("Location: index.php?action=smart_match&error=" . urlencode($res['message']));
        }
        exit;
    }
}
?>
