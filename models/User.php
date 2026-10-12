<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $role;
    public $name;
    public $phone;
    public $owner_id = null;
    public $assigned_pitch_id = null;

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureSchema();
    }

    private function ensureSchema() {
        if (!$this->conn) return;
        try {
            $cols = $this->conn->query("SHOW COLUMNS FROM " . $this->table_name)->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('owner_id', $cols)) {
                $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN owner_id INT NULL COMMENT 'ID của Chủ sân quản lý'");
            }
            if (!in_array('assigned_pitch_id', $cols)) {
                $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN assigned_pitch_id INT NULL COMMENT 'ID của Cụm sân phụ trách'");
            }
        } catch (Exception $e) {}
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password=:password, role=:role, name=:name, phone=:phone, 
                      owner_id=:owner_id, assigned_pitch_id=:assigned_pitch_id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->role = !empty($this->role) ? $this->role : 'user'; // assign role if specified, else user
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":owner_id", $this->owner_id);
        $stmt->bindParam(":assigned_pitch_id", $this->assigned_pitch_id);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function login() {
        $query = "SELECT id, username, password, role, name, phone, owner_id, assigned_pitch_id 
                  FROM " . $this->table_name . " 
                  WHERE username = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($this->password, $row['password'])) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->role = $row['role'];
            $this->name = $row['name'];
            $this->phone = $row['phone'];
            $this->owner_id = $row['owner_id'];
            $this->assigned_pitch_id = $row['assigned_pitch_id'];
            return true;
        }
        return false;
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readAll() {
        $query = "SELECT u.*, p.name as pitch_name, o.name as owner_name 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN pitches p ON u.assigned_pitch_id = p.id 
                  LEFT JOIN " . $this->table_name . " o ON u.owner_id = o.id 
                  ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function updateProfile($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, phone = :phone";
        
        if (isset($data['avatar_url'])) {
            $query .= ", avatar_url = :avatar_url";
        }
        if (isset($data['password'])) {
            $query .= ", password = :password";
        }
        if (isset($data['skill_level'])) {
            $query .= ", skill_level = :skill_level";
        }
        if (isset($data['preferred_format'])) {
            $query .= ", preferred_format = :preferred_format";
        }
        if (isset($data['district'])) {
            $query .= ", district = :district";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($data['name']));
        $this->phone = htmlspecialchars(strip_tags($data['phone']));
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':id', $id);
        
        if (isset($data['avatar_url'])) {
            $avatar_url = htmlspecialchars(strip_tags($data['avatar_url']));
            $stmt->bindParam(':avatar_url', $avatar_url);
        }
        if (isset($data['password'])) {
            $stmt->bindParam(':password', $data['password']);
        }
        if (isset($data['skill_level'])) {
            $stmt->bindParam(':skill_level', $data['skill_level']);
        }
        if (isset($data['preferred_format'])) {
            $stmt->bindParam(':preferred_format', $data['preferred_format']);
        }
        if (isset($data['district'])) {
            $stmt->bindParam(':district', $data['district']);
        }
        
        return $stmt->execute();
    }

    public function updateRole($id, $role) {
        $allowed = ['user', 'staff', 'owner', 'admin'];
        if (!in_array($role, $allowed)) {
            return false;
        }
        $query = "UPDATE " . $this->table_name . " SET role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function readStaffByOwner($owner_id) {
        $query = "SELECT u.*, p.name as pitch_name 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN pitches p ON u.assigned_pitch_id = p.id 
                  WHERE u.role = 'staff' AND (u.owner_id = :owner_id OR p.owner_id = :owner_id)
                  ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignStaffToPitch($staff_id, $owner_id, $pitch_id = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET role = 'staff', owner_id = :owner_id, assigned_pitch_id = :pitch_id 
                  WHERE id = :staff_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $owner_id);
        $stmt->bindParam(':pitch_id', $pitch_id);
        $stmt->bindParam(':staff_id', $staff_id);
        return $stmt->execute();
    }

    public function removeStaffAssignment($staff_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET role = 'user', owner_id = NULL, assigned_pitch_id = NULL 
                  WHERE id = :staff_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $staff_id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
