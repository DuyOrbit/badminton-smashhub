<?php
class Service {
    private $conn;
    private $table_name = "services";

    public $id;
    public $pitch_id;
    public $name;
    public $category;
    public $price;
    public $unit;
    public $stock;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readByPitch($pitch_id = null) {
        $query = "SELECT * FROM " . $this->table_name;
        if ($pitch_id) {
            $query .= " WHERE pitch_id = :pitch_id OR pitch_id IS NULL";
        }
        $query .= " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        if ($pitch_id) {
            $stmt->bindParam(':pitch_id', $pitch_id);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (pitch_id, name, category, price, unit, stock) 
                  VALUES (:pitch_id, :name, :category, :price, :unit, :stock)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            'pitch_id' => $data['pitch_id'] ?? null,
            'name' => htmlspecialchars(strip_tags($data['name'])),
            'category' => $data['category'] ?? 'beverage',
            'price' => $data['price'],
            'unit' => $data['unit'] ?? 'Chai',
            'stock' => $data['stock'] ?? 100
        ]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
