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
        } catch (Exception $e) {}
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET pitch_id=:pitch_id, user_id=:user_id, customer_name=:customer_name, customer_phone=:customer_phone, 
                      booking_date=:booking_date, start_time=:start_time, end_time=:end_time, total_price=:total_price,
                      payment_method=:payment_method, payment_status=:payment_status, services_note=:services_note, 
                      checkin_code=:checkin_code, booking_type=:booking_type, created_by_user_id=:created_by_user_id";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->customer_phone = htmlspecialchars(strip_tags($this->customer_phone));
        if (empty($this->checkin_code)) {
            $this->checkin_code = 'CKIN' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        }

        // bind values
        $stmt->bindParam(":pitch_id", $this->pitch_id);
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
        $query = "SELECT b.*, p.name as pitch_name, p.quantity 
                  FROM " . $this->table_name . " b
                  INNER JOIN pitches p ON b.pitch_id = p.id
                  WHERE p.owner_id = :owner_id AND b.booking_date = :booking_date
                  AND b.status IN ('confirmed', 'pending')
                  ORDER BY b.start_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindParam(':booking_date', $date);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                  WHERE (b.checkin_code = :keyword OR b.customer_phone = :keyword OR b.id = :keyword)";
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
        $query = "SELECT b.*, p.name as pitch_name, p.type as pitch_type
                  FROM " . $this->table_name . " b
                  LEFT JOIN pitches p ON b.pitch_id = p.id
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
            $query .= " AND (b.checkin_code LIKE :kw OR b.customer_phone LIKE :kw OR b.customer_name LIKE :kw)";
            $params[':kw'] = "%$keyword%";
        }

        $query .= " ORDER BY b.booking_date DESC, b.start_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
