<?php
class Club {
    private $conn;
    private $table_name = "clubs";

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureSchema();
    }

    private function ensureSchema() {
        if (!$this->conn) return;
        try {
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS clubs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(150) NOT NULL,
                    description TEXT NULL,
                    leader_id INT NOT NULL,
                    pitch_id INT NULL,
                    schedule_info VARCHAR(255) NULL,
                    district VARCHAR(100) NULL,
                    level_requirement VARCHAR(100) DEFAULT 'Mọi trình độ (Giao lưu học hỏi)',
                    avatar_url VARCHAR(255) NULL,
                    banner_url VARCHAR(255) NULL,
                    member_count INT DEFAULT 1,
                    is_recruiting TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS club_members (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    club_id INT NOT NULL,
                    user_id INT NOT NULL,
                    role ENUM('leader', 'admin', 'member') DEFAULT 'member',
                    status ENUM('approved', 'pending', 'rejected') DEFAULT 'approved',
                    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_club_user (club_id, user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS club_posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    club_id INT NOT NULL,
                    user_id INT NOT NULL,
                    post_type ENUM('general', 'recruitment', 'announcement') DEFAULT 'general',
                    title VARCHAR(255) NULL,
                    content TEXT NOT NULL,
                    slot_needed INT DEFAULT 0,
                    fee_per_slot INT DEFAULT 0,
                    likes_count INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (Exception $e) {}
    }

    public function readAll($keyword = '', $district = '', $is_recruiting = null) {
        $query = "SELECT c.*, u.name as leader_name, u.username as leader_username, p.name as pitch_name 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.leader_id = u.id 
                  LEFT JOIN pitches p ON c.pitch_id = p.id 
                  WHERE 1=1";
        $params = [];

        if (!empty($keyword)) {
            $query .= " AND (c.name LIKE :kw OR c.description LIKE :kw OR c.schedule_info LIKE :kw)";
            $params[':kw'] = "%$keyword%";
        }

        if (!empty($district) && $district !== 'Tất cả') {
            $query .= " AND c.district = :district";
            $params[':district'] = $district;
        }

        if ($is_recruiting !== null && $is_recruiting !== '') {
            $query .= " AND c.is_recruiting = :recruiting";
            $params[':recruiting'] = (int)$is_recruiting;
        }

        $query .= " ORDER BY c.is_recruiting DESC, c.member_count DESC, c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne($id) {
        $query = "SELECT c.*, u.name as leader_name, u.username as leader_username, u.phone as leader_phone, 
                         p.name as pitch_name, p.location as pitch_location, p.price_per_hour
                  FROM " . $this->table_name . " c 
                  LEFT JOIN users u ON c.leader_id = u.id 
                  LEFT JOIN pitches p ON c.pitch_id = p.id 
                  WHERE c.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, description=:description, leader_id=:leader_id, pitch_id=:pitch_id, 
                      schedule_info=:schedule_info, district=:district, level_requirement=:level_requirement, 
                      member_count=1, is_recruiting=:is_recruiting";
        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags($data['name']));
        $description = htmlspecialchars(strip_tags($data['description'] ?? ''));
        $leader_id = (int)$data['leader_id'];
        $pitch_id = !empty($data['pitch_id']) ? (int)$data['pitch_id'] : null;
        $schedule_info = htmlspecialchars(strip_tags($data['schedule_info'] ?? ''));
        $district = htmlspecialchars(strip_tags($data['district'] ?? ''));
        $level_requirement = htmlspecialchars(strip_tags($data['level_requirement'] ?? 'Mọi trình độ'));
        $is_recruiting = isset($data['is_recruiting']) ? (int)$data['is_recruiting'] : 1;

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':leader_id', $leader_id);
        $stmt->bindParam(':pitch_id', $pitch_id);
        $stmt->bindParam(':schedule_info', $schedule_info);
        $stmt->bindParam(':district', $district);
        $stmt->bindParam(':level_requirement', $level_requirement);
        $stmt->bindParam(':is_recruiting', $is_recruiting);

        if ($stmt->execute()) {
            $club_id = $this->conn->lastInsertId();
            // Tự động gán leader vào danh sách thành viên
            $m_stmt = $this->conn->prepare("INSERT INTO club_members (club_id, user_id, role, status) VALUES (?, ?, 'leader', 'approved')");
            $m_stmt->execute([$club_id, $leader_id]);
            return $club_id;
        }
        return false;
    }

    public function getMembers($club_id) {
        $query = "SELECT cm.*, u.name, u.username, u.phone 
                  FROM club_members cm 
                  JOIN users u ON cm.user_id = u.id 
                  WHERE cm.club_id = :club_id AND cm.status = 'approved' 
                  ORDER BY (cm.role = 'leader') DESC, cm.joined_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':club_id', $club_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserMembership($club_id, $user_id) {
        if (!$user_id) return null;
        $query = "SELECT * FROM club_members WHERE club_id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$club_id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function joinClub($club_id, $user_id) {
        $existing = $this->getUserMembership($club_id, $user_id);
        if ($existing) return false;

        $query = "INSERT INTO club_members (club_id, user_id, role, status) VALUES (?, ?, 'member', 'approved')";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([$club_id, $user_id])) {
            $this->syncMemberCount($club_id);
            return true;
        }
        return false;
    }

    public function leaveClub($club_id, $user_id) {
        $membership = $this->getUserMembership($club_id, $user_id);
        if (!$membership || $membership['role'] === 'leader') {
            return false; // Leader cannot leave directly
        }

        $query = "DELETE FROM club_members WHERE club_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([$club_id, $user_id])) {
            $this->syncMemberCount($club_id);
            return true;
        }
        return false;
    }

    public function syncMemberCount($club_id) {
        $q = "UPDATE clubs SET member_count = (SELECT COUNT(*) FROM club_members WHERE club_id = :cid AND status = 'approved') WHERE id = :id";
        $stmt = $this->conn->prepare($q);
        $stmt->execute([':cid' => $club_id, ':id' => $club_id]);
    }

    public function toggleRecruitment($club_id, $leader_id) {
        $query = "UPDATE clubs SET is_recruiting = 1 - is_recruiting WHERE id = :id AND leader_id = :leader_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $club_id, ':leader_id' => $leader_id]);
    }

    public function getPosts($club_id) {
        $query = "SELECT cp.*, u.name as author_name, u.username as author_username 
                  FROM club_posts cp 
                  JOIN users u ON cp.user_id = u.id 
                  WHERE cp.club_id = :club_id 
                  ORDER BY cp.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':club_id', $club_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPost($club_id, $user_id, $title, $content, $post_type = 'general', $slot_needed = 0, $fee_per_slot = 0) {
        $query = "INSERT INTO club_posts (club_id, user_id, title, content, post_type, slot_needed, fee_per_slot) 
                  VALUES (:club_id, :user_id, :title, :content, :post_type, :slot_needed, :fee_per_slot)";
        $stmt = $this->conn->prepare($query);

        $title = htmlspecialchars(strip_tags($title));
        $content = htmlspecialchars(strip_tags($content));
        $slot_needed = max(0, (int)$slot_needed);
        $fee_per_slot = max(0, (int)$fee_per_slot);

        return $stmt->execute([
            ':club_id' => $club_id,
            ':user_id' => $user_id,
            ':title' => $title,
            ':content' => $content,
            ':post_type' => $post_type,
            ':slot_needed' => $slot_needed,
            ':fee_per_slot' => $fee_per_slot
        ]);
    }

    public function getUserClubs($user_id) {
        $query = "SELECT c.*, cm.role as member_role 
                  FROM clubs c 
                  JOIN club_members cm ON c.id = cm.club_id 
                  WHERE cm.user_id = ? AND cm.status = 'approved' 
                  ORDER BY cm.joined_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
