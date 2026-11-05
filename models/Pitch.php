<?php
class Pitch {
    private $conn;
    private $table_name = "pitches";

    public $id;
    public $owner_id;
    public $name;
    public $type;
    public $quantity;
    public $price_per_hour;
    public $image_url;
    public $amenities;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT p.*, u.name as owner_name FROM " . $this->table_name . " p LEFT JOIN users u ON p.owner_id = u.id ORDER BY p.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByOwner($owner_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE owner_id = ? ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $owner_id);
        $stmt->execute();
        return $stmt;
    }

    public function search($keyword = '', $max_price = 0, $type = '', $district = '', $amenity = '', $min_price = 0, $sort = '') {
        // If first argument is an associative array, unpack it
        if (is_array($keyword)) {
            $filters = $keyword;
            $keyword = $filters['keyword'] ?? ($filters['q'] ?? '');
            $max_price = $filters['max_price'] ?? 0;
            $type = $filters['type'] ?? '';
            $district = $filters['district'] ?? '';
            $amenity = $filters['amenity'] ?? '';
            $min_price = $filters['min_price'] ?? 0;
            $sort = $filters['sort'] ?? '';
        }

        $query = "SELECT p.*, u.name as owner_name FROM " . $this->table_name . " p 
                  LEFT JOIN users u ON p.owner_id = u.id 
                  WHERE (p.status = 'active' OR p.status IS NULL)";
        $params = [];
        
        // Build conditions dynamically
        if (!empty($keyword)) {
            $query .= " AND (p.name LIKE :keyword OR p.location LIKE :keyword OR p.district LIKE :keyword OR p.description LIKE :keyword)";
            $params[':keyword'] = "%" . htmlspecialchars(strip_tags($keyword)) . "%";
        }
        
        if (!empty($district) && $district !== 'all' && $district !== 'Tất cả') {
            $query .= " AND (p.district = :district OR p.location LIKE :district_loc)";
            $params[':district'] = $district;
            $params[':district_loc'] = "%" . $district . "%";
        }
        
        if (!empty($type) && $type !== 'all' && $type !== 'Tất cả') {
            $query .= " AND p.type = :type";
            $params[':type'] = $type;
        }

        if (!empty($min_price) && (float)$min_price > 0) {
            $query .= " AND p.price_per_hour >= :min_price";
            $params[':min_price'] = (float)$min_price;
        }
        
        if (!empty($max_price) && (float)$max_price > 0) {
            $query .= " AND p.price_per_hour <= :max_price";
            $params[':max_price'] = (float)$max_price;
        }
        
        if (!empty($amenity) && $amenity !== 'all') {
            $amenitiesList = is_array($amenity) ? $amenity : explode(',', $amenity);
            foreach ($amenitiesList as $idx => $am) {
                $am = trim($am);
                if (!empty($am)) {
                    $paramKey = ":amenity_" . $idx;
                    $query .= " AND (p.amenities LIKE {$paramKey} OR p.description LIKE {$paramKey})";
                    $params[$paramKey] = "%" . $am . "%";
                }
            }
        }
        
        // Sorting
        switch ($sort) {
            case 'price_asc':
                $query .= " ORDER BY p.price_per_hour ASC, p.id DESC";
                break;
            case 'price_desc':
                $query .= " ORDER BY p.price_per_hour DESC, p.id DESC";
                break;
            case 'quantity_desc':
                $query .= " ORDER BY p.quantity DESC, p.id DESC";
                break;
            case 'name_asc':
                $query .= " ORDER BY p.name ASC";
                break;
            default:
                $query .= " ORDER BY p.id DESC";
                break;
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt;
    }

    // THUẬT TOÁN TÍNH KHOẢNG CÁCH HAVERSINE TÌM SÂN THEO BÁN KÍNH (KM)
    public function getPitchesWithinRadius($userLat, $userLng, $radiusKm = 10, $maxPrice = 0, $type = '') {
        $userLat = (float)$userLat;
        $userLng = (float)$userLng;
        $radiusKm = (float)$radiusKm;

        $query = "SELECT p.*, u.name as owner_name,
                         (6371 * acos(
                             LEAST(1.0, GREATEST(-1.0,
                                 cos(radians(:user_lat)) 
                                 * cos(radians(p.latitude)) 
                                 * cos(radians(p.longitude) - radians(:user_lng)) 
                                 + sin(radians(:user_lat)) 
                                 * sin(radians(p.latitude))
                             ))
                         )) AS distance
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.owner_id = u.id
                  WHERE p.latitude IS NOT NULL AND p.longitude IS NOT NULL";

        if (!empty($maxPrice) && $maxPrice > 0) {
            $query .= " AND p.price_per_hour <= :max_price";
        }

        if (!empty($type)) {
            $query .= " AND p.type = :type";
        }

        $query .= " HAVING distance <= :radius ORDER BY distance ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_lat', $userLat);
        $stmt->bindParam(':user_lng', $userLng);
        $stmt->bindParam(':radius', $radiusKm);

        if (!empty($maxPrice) && $maxPrice > 0) {
            $stmt->bindParam(':max_price', $maxPrice);
        }
        if (!empty($type)) {
            $stmt->bindParam(':type', $type);
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
        $query = "INSERT INTO " . $this->table_name . " 
                (owner_id, name, location, district, city, type, quantity, price_per_hour, image_url, amenities, description, latitude, longitude) 
                VALUES (:owner_id, :name, :location, :district, :city, :type, :quantity, :price_per_hour, :image_url, :amenities, :description, :latitude, :longitude)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->owner_id = htmlspecialchars(strip_tags($data['owner_id']));
        $this->name = htmlspecialchars(strip_tags($data['name']));
        $this->location = htmlspecialchars(strip_tags($data['location']));
        $this->district = htmlspecialchars(strip_tags($data['district'] ?? ''));
        $this->city = htmlspecialchars(strip_tags($data['city'] ?? ''));
        $this->type = htmlspecialchars(strip_tags($data['type']));
        $this->quantity = htmlspecialchars(strip_tags($data['quantity'] ?? 1));
        $this->price_per_hour = htmlspecialchars(strip_tags($data['price_per_hour']));
        $this->image_url = htmlspecialchars(strip_tags($data['image_url']));
        $this->amenities = htmlspecialchars(strip_tags($data['amenities'] ?? 'parking,lighting,cooling,shower'));
        $this->description = htmlspecialchars(strip_tags($data['description']));
        $lat = !empty($data['latitude']) ? floatval($data['latitude']) : null;
        $lng = !empty($data['longitude']) ? floatval($data['longitude']) : null;

        // Bind data
        $stmt->bindParam(':owner_id', $this->owner_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':district', $this->district);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':price_per_hour', $this->price_per_hour);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':amenities', $this->amenities);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':latitude', $lat);
        $stmt->bindParam(':longitude', $lng);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($data, $id) {
        $query = "UPDATE " . $this->table_name . " 
                SET name = :name, location = :location, district = :district, city = :city, type = :type, quantity = :quantity, price_per_hour = :price_per_hour, amenities = :amenities, description = :description, latitude = :latitude, longitude = :longitude";
        
        // Only update image_url if provided
        if (isset($data['image_url']) && !empty($data['image_url'])) {
            $query .= ", image_url = :image_url";
        }
        
        $query .= " WHERE id = :id AND owner_id = :owner_id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($data['name']));
        $this->location = htmlspecialchars(strip_tags($data['location']));
        $this->district = htmlspecialchars(strip_tags($data['district'] ?? ''));
        $this->city = htmlspecialchars(strip_tags($data['city'] ?? 'TP.HCM'));
        $this->type = htmlspecialchars(strip_tags($data['type']));
        $this->quantity = htmlspecialchars(strip_tags($data['quantity'] ?? 1));
        $this->price_per_hour = htmlspecialchars(strip_tags($data['price_per_hour']));
        $this->amenities = htmlspecialchars(strip_tags($data['amenities'] ?? 'parking,lighting,cooling,shower'));
        $this->description = htmlspecialchars(strip_tags($data['description']));
        $this->id = htmlspecialchars(strip_tags($id));
        $this->owner_id = htmlspecialchars(strip_tags($data['owner_id']));
        $lat = !empty($data['latitude']) ? floatval($data['latitude']) : null;
        $lng = !empty($data['longitude']) ? floatval($data['longitude']) : null;

        // Bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindParam(':district', $this->district);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':price_per_hour', $this->price_per_hour);
        $stmt->bindParam(':amenities', $this->amenities);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':owner_id', $this->owner_id);
        $stmt->bindParam(':latitude', $lat);
        $stmt->bindParam(':longitude', $lng);

        if (isset($data['image_url']) && !empty($data['image_url'])) {
            $this->image_url = htmlspecialchars(strip_tags($data['image_url']));
            $stmt->bindParam(':image_url', $this->image_url);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id, $owner_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND owner_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([(int)$id, (int)$owner_id]);
    }

    public function getActiveDistrictsWithCount() {
        $query = "SELECT district, COUNT(*) as pitch_count FROM " . $this->table_name . " WHERE status = 'active' AND district IS NOT NULL AND district != '' GROUP BY district ORDER BY pitch_count DESC, district ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $map = [];
        foreach ($results as $row) {
            $map[trim($row['district'])] = (int)$row['pitch_count'];
        }
        return $map;
    }
}
?>
