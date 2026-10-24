<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $pitch_id;
    public $user_id;
    public $customer_name;
    public $customer_phone;
    public $booking_date;
    public $start_time;
    public $end_time;
    public $total_price;
    public $status;
    public $payment_method = 'cash';
    public $payment_status = 'unpaid';
    public $services_note = '';
    public $checkin_code = '';
    public $booking_type = 'online_app';
    public $created_by_user_id = null;
    public $court_number = 1;

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureSchema();
    }

    private function ensureSchema() {
        if (!$this->conn) return;
        try {
            $cols = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name)->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('booking_type', $cols)) {
                $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN booking_type ENUM('online_app','walk_in','fixed_monthly') DEFAULT 'online_app'");
            }
            if (!in_array('created_by_user_id', $cols)) {
                $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN created_by_user_id INT NULL");
            }
            if (!in_array('court_number', $cols)) {
                $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN court_number INT DEFAULT 1 AFTER pitch_id");
            }
        } catch (Exception $e) {}
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET pitch_id=:pitch_id, court_number=:court_number, user_id=:user_id, customer_name=:customer_name, customer_phone=:customer_phone, 
                      booking_date=:booking_date, start_time=:start_time, end_time=:end_time, total_price=:total_price,
                      payment_method=:payment_method, payment_status=:payment_status, services_note=:services_note, 
                      checkin_code=:checkin_code, booking_type=:booking_type, created_by_user_id=:created_by_user_id";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->customer_phone = htmlspecialchars(strip_tags($this->customer_phone));
        $this->court_number = max(1, (int)$this->court_number);
        if (empty($this->checkin_code)) {
            $this->checkin_code = 'CKIN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        }

        // bind values
        $stmt->bindParam(":pitch_id", $this->pitch_id);
        $stmt->bindParam(":court_number", $this->court_number);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":customer_phone", $this->customer_phone);
        $stmt->bindParam(":booking_date", $this->booking_date);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->bindParam(":total_price", $this->total_price);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":payment_status", $this->payment_status);
        $stmt->bindParam(":services_note", $this->services_note);
        $stmt->bindParam(":checkin_code", $this->checkin_code);
        $stmt->bindParam(":booking_type", $this->booking_type);
        $stmt->bindParam(":created_by_user_id", $this->created_by_user_id);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT b.*, p.name as pitch_name 
                  FROM " . $this->table_name . " b
                  LEFT JOIN pitches p ON b.pitch_id = p.id
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByOwner($owner_id) {
        $query = "SELECT b.*, p.name as pitch_name 
                  FROM " . $this->table_name . " b
                  INNER JOIN pitches p ON b.pitch_id = p.id
                  WHERE p.owner_id = ?
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $owner_id);
        $stmt->execute();
        return $stmt;
    }

    public function readScheduleByOwner($owner_id, $date) {
        $query = "SELECT b.*, p.name as pitch_name, p.quantity,
                         m.id as match_id, m.user_id as match_host_id, m.opponent_id as match_guest_id,
                         u_host.name as match_host_name, u_host.phone as match_host_phone,
                         u_guest.name as match_guest_name, u_guest.phone as match_guest_phone,
                         m.host_paid, m.guest_paid, m.host_checkin, m.guest_checkin,
                         m.host_checkin_time, m.guest_checkin_time,
                         m.noshow_status as match_noshow_status, m.noshow_reported_by as match_noshow_reported_by,
                         m.noshow_target_user_id as match_noshow_target_user_id, m.noshow_reason as match_noshow_reason
                  FROM " . $this->table_name . " b
                  INNER JOIN pitches p ON b.pitch_id = p.id
                  LEFT JOIN matchmaking m ON b.pitch_id = m.pitch_id AND b.booking_date = m.play_date AND b.start_time = m.start_time
                  LEFT JOIN users u_host ON m.user_id = u_host.id
                  LEFT JOIN users u_guest ON m.opponent_id = u_guest.id
                  WHERE p.owner_id = :owner_id AND b.booking_date = :booking_date
                  AND b.status IN ('confirmed', 'pending')
                  ORDER BY b.start_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindParam(':booking_date', $date);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bookings as &$b) {
            $b['host_checkin_code'] = '';
            $b['guest_checkin_code'] = '';
            $b['is_match'] = (!empty($b['match_id']) || strpos($b['services_note'] ?? '', 'Kèo Ghép') !== false || strpos($b['services_note'] ?? '', 'Smart Match') !== false);
            if ($b['is_match']) {
                $tStmt = $this->conn->prepare("SELECT user_id, checkin_code, is_checked_in, payment_status, split_price FROM match_tickets WHERE booking_id = ?");
                $tStmt->execute([$b['id']]);
                $tRows = $tStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tRows as $tr) {
                    if ($tr['user_id'] == ($b['match_host_id'] ?? 0)) {
                        $b['host_checkin_code'] = $tr['checkin_code'];
                    } elseif ($tr['user_id'] == ($b['match_guest_id'] ?? 0)) {
                        $b['guest_checkin_code'] = $tr['checkin_code'];
                    }
                }
            }
        }
        return $bookings;
    }

    public function readByUser($user_id) {
        $query = "SELECT b.*, p.name as pitch_name 
                  FROM " . $this->table_name . " b
                  LEFT JOIN pitches p ON b.pitch_id = p.id
                  WHERE b.user_id = ?
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getConfirmedOverlappingCount($pitch_id, $booking_date, $start_time, $end_time, $include_pending = true) {
        $status_condition = $include_pending ? "status IN ('confirmed', 'pending')" : "status = 'confirmed'";
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE pitch_id = :pitch_id 
                  AND booking_date = :booking_date 
                  AND {$status_condition} 
                  AND (start_time < :end_time AND end_time > :start_time)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pitch_id", $pitch_id);
        $stmt->bindParam(":booking_date", $booking_date);
        $stmt->bindParam(":start_time", $start_time);
        $stmt->bindParam(":end_time", $end_time);
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function getOccupiedCourts($pitch_id, $booking_date, $start_time, $end_time, $total_courts) {
        $query = "SELECT court_number FROM " . $this->table_name . "
                  WHERE pitch_id = :pitch_id
                    AND booking_date = :booking_date
                    AND status IN ('confirmed', 'pending', 'completed')
                    AND (start_time < :end_time AND end_time > :start_time)
                  ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pitch_id", $pitch_id);
        $stmt->bindParam(":booking_date", $booking_date);
        $stmt->bindParam(":start_time", $start_time);
        $stmt->bindParam(":end_time", $end_time);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $occupied = [];
        foreach ($rows as $cNum) {
            $c = (int)$cNum;
            if ($c < 1 || $c > $total_courts || in_array($c, $occupied)) {
                // Auto-assign to next available court number if legacy booking had default court_number = 1
                for ($try = 1; $try <= $total_courts; $try++) {
                    if (!in_array($try, $occupied)) {
                        $c = $try;
                        break;
                    }
                }
            }
            if ($c >= 1 && $c <= $total_courts && !in_array($c, $occupied)) {
                $occupied[] = $c;
            }
        }
        sort($occupied);
        return $occupied;
    }

    public function getDailyHourlyMatrix($pitch_id, $booking_date, $total_courts) {
        $slots = [];
        for ($h = 6; $h < 22; $h++) {
            $start = sprintf('%02d:00', $h);
            $end = sprintf('%02d:00', $h + 1);
            $occupiedCourts = $this->getOccupiedCourts($pitch_id, $booking_date, $start . ':00', $end . ':00', $total_courts);
            $bookedCount = count($occupiedCourts);
            $availableCount = max(0, $total_courts - $bookedCount);
            $slots[] = [
                'hour' => $h,
                'start' => $start,
                'end' => $end,
                'label' => "{$start} - {$end}",
                'booked' => $bookedCount,
                'available' => $availableCount,
                'total' => (int)$total_courts,
                'is_peak' => ($h >= 17 && $h < 21),
                'occupied_courts' => $occupiedCourts
            ];
        }
        return $slots;
    }

    public function readOneWithPitchInfo($id) {
        $query = "SELECT b.*, p.name as pitch_name, p.location as pitch_location, p.type as pitch_type, p.owner_id
                  FROM " . $this->table_name . " b
                  LEFT JOIN pitches p ON b.pitch_id = p.id
                  WHERE b.id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByCheckinCode($keyword, $assigned_pitch_id = null, $owner_id = null) {
        $keyword = trim($keyword);
        $query = "SELECT b.*, p.name as pitch_name, p.location as pitch_location, p.type as pitch_type, p.owner_id 
                  FROM " . $this->table_name . " b
                  LEFT JOIN pitches p ON b.pitch_id = p.id
                  WHERE (b.checkin_code = :keyword OR b.customer_phone = :keyword OR b.id = :keyword
                         OR b.id IN (SELECT booking_id FROM match_tickets WHERE checkin_code = :keyword))";
        $params = [':keyword' => $keyword];

        if (!empty($assigned_pitch_id)) {
            $query .= " AND b.pitch_id = :assigned_pitch_id";
            $params[':assigned_pitch_id'] = $assigned_pitch_id;
        } elseif (!empty($owner_id)) {
            $query .= " AND p.owner_id = :owner_id";
            $params[':owner_id'] = $owner_id;
        }

        $query .= " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkin($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'completed', payment_status = 'paid' 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $res = $stmt->execute();

        if ($res) {
            // Đồng bộ trạng thái vé Smart Match nếu có
            try {
                $tStmt = $this->conn->prepare("SELECT * FROM match_tickets WHERE booking_id = ?");
                $tStmt->execute([$id]);
                $tickets = $tStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($tickets)) {
                    $this->conn->prepare("UPDATE match_tickets SET is_checked_in = 1, checked_in_at = NOW(), payment_status = 'paid' WHERE booking_id = ?")->execute([$id]);
                    $this->conn->prepare("UPDATE matchmaking SET host_checkin = 1, guest_checkin = 1, host_paid = 1, guest_paid = 1 WHERE pitch_id = ? AND play_date = ? AND start_time = ?")
                               ->execute([$tickets[0]['matched_pitch_id'], $tickets[0]['play_date'], $tickets[0]['start_time']]);
                }
            } catch (\Exception $e) {}

            // Thưởng +2 điểm uy tín Fair-Play vì đã đến sân đúng hẹn
            $q = "SELECT user_id FROM " . $this->table_name . " WHERE id = ?";
            $s = $this->conn->prepare($q);
            $s->execute([$id]);
            if ($row = $s->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($row['user_id'])) {
                    require_once __DIR__ . '/Reputation.php';
                    $rep = new Reputation($this->conn);
                    $rep->changeScore($row['user_id'], 2, "Hoàn tất thi đấu đúng hẹn tại sân", $id);
                }
            }
        }
        return $res;
    }

    public function readAllForStaff($date = null, $keyword = null, $assigned_pitch_id = null, $owner_id = null) {
        $query = "SELECT b.*, p.name as pitch_name, p.type as pitch_type,
                         m.id as match_id, m.user_id as match_host_id, m.opponent_id as match_guest_id,
                         u_host.name as match_host_name, u_host.phone as match_host_phone,
                         u_guest.name as match_guest_name, u_guest.phone as match_guest_phone,
                         m.host_paid, m.guest_paid, m.host_checkin, m.guest_checkin,
                         m.host_checkin_time, m.guest_checkin_time,
                         m.noshow_status as match_noshow_status, m.noshow_reason as match_noshow_reason
                  FROM " . $this->table_name . " b
                  LEFT JOIN pitches p ON b.pitch_id = p.id
                  LEFT JOIN matchmaking m ON b.pitch_id = m.pitch_id AND b.booking_date = m.play_date AND b.start_time = m.start_time
                  LEFT JOIN users u_host ON m.user_id = u_host.id
                  LEFT JOIN users u_guest ON m.opponent_id = u_guest.id
                  WHERE 1=1";
        $params = [];

        if (!empty($assigned_pitch_id)) {
            $query .= " AND b.pitch_id = :assigned_pitch_id";
            $params[':assigned_pitch_id'] = $assigned_pitch_id;
        } elseif (!empty($owner_id)) {
            $query .= " AND p.owner_id = :owner_id";
            $params[':owner_id'] = $owner_id;
        }

        if (!empty($date)) {
            $query .= " AND b.booking_date = :bdate";
            $params[':bdate'] = $date;
        }

        if (!empty($keyword)) {
            $query .= " AND (b.checkin_code LIKE :kw OR b.customer_phone LIKE :kw OR b.customer_name LIKE :kw 
                             OR b.id IN (SELECT booking_id FROM match_tickets WHERE checkin_code LIKE :kw_t))";
            $params[':kw'] = "%$keyword%";
            $params[':kw_t'] = "%$keyword%";
        }

        $query .= " ORDER BY b.booking_date DESC, b.start_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bookings as &$b) {
            $b['is_match'] = !empty($b['match_id']);
            $b['host_checkin_code'] = '';
            $b['guest_checkin_code'] = '';
            $b['host_ticket_id'] = null;
            $b['guest_ticket_id'] = null;
            if ($b['is_match']) {
                $tStmt = $this->conn->prepare("SELECT id, user_id, checkin_code, is_checked_in, payment_status FROM match_tickets WHERE play_date = ? AND start_time = ? AND matched_pitch_id = ?");
                $tStmt->execute([$b['booking_date'], $b['start_time'], $b['pitch_id']]);
                $tickets = $tStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tickets as $t) {
                    if ($t['user_id'] == $b['match_host_id']) {
                        $b['host_checkin_code'] = $t['checkin_code'] ?? '';
                        $b['host_ticket_id'] = $t['id'];
                        if (!empty($t['is_checked_in'])) $b['host_checkin'] = 1;
                        if (($t['payment_status'] ?? '') === 'paid') $b['host_paid'] = 1;
                    } elseif ($t['user_id'] == $b['match_guest_id']) {
                        $b['guest_checkin_code'] = $t['checkin_code'] ?? '';
                        $b['guest_ticket_id'] = $t['id'];
                        if (!empty($t['is_checked_in'])) $b['guest_checkin'] = 1;
                        if (($t['payment_status'] ?? '') === 'paid') $b['guest_paid'] = 1;
                    }
                }
            }
        }
        return $bookings;
    }

    public function updatePaymentStatus($id, $payment_status, $status = null) {
        $query = "UPDATE " . $this->table_name . " SET payment_status = :payment_status";
        $params = [':payment_status' => $payment_status, ':id' => $id];
        if ($status !== null) {
            $query .= ", status = :status";
            $params[':status'] = $status;
        }
        $query .= " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function getWeeklyRevenueStats($owner_id) {
        $labels = [];
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $days[$d] = 0;
            $labels[] = date('d/m', strtotime("-$i days"));
        }

        $query = "SELECT b.booking_date, SUM(b.total_price) as daily_total
                  FROM " . $this->table_name . " b
                  INNER JOIN pitches p ON b.pitch_id = p.id
                  WHERE p.owner_id = :owner_id 
                    AND b.status IN ('confirmed', 'completed')
                    AND b.booking_date >= :start_date
                  GROUP BY b.booking_date";
        $stmt = $this->conn->prepare($query);
        $start_date = date('Y-m-d', strtotime("-6 days"));
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            if (isset($days[$r['booking_date']])) {
                $days[$r['booking_date']] = (float)$r['daily_total'];
            }
        }

        return [
            'labels' => $labels,
            'data' => array_values($days)
        ];
    }

    public function getHourlyOccupancyStats($owner_id) {
        $query = "SELECT b.start_time
                  FROM " . $this->table_name . " b
                  INNER JOIN pitches p ON b.pitch_id = p.id
                  WHERE p.owner_id = :owner_id AND b.status IN ('confirmed', 'completed', 'pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $brackets = [
            'Sáng (06h - 09h)' => 0,
            'Trưa (09h - 15h)' => 0,
            'Chiều (15h - 18h)' => 0,
            'Giờ Vàng (18h - 22h)' => 0
        ];

        foreach ($rows as $r) {
            $hour = (int)substr($r['start_time'], 0, 2);
            if ($hour >= 6 && $hour < 9) {
                $brackets['Sáng (06h - 09h)']++;
            } elseif ($hour >= 9 && $hour < 15) {
                $brackets['Trưa (09h - 15h)']++;
            } elseif ($hour >= 15 && $hour < 18) {
                $brackets['Chiều (15h - 18h)']++;
            } else {
                $brackets['Giờ Vàng (18h - 22h)']++;
            }
        }

        return [
            'labels' => array_keys($brackets),
            'data' => array_values($brackets)
        ];
    }
}
?>
