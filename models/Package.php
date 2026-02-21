<?php
// models/Package.php
require_once 'BaseModel.php';

class Package extends BaseModel {
    protected $table = 'packages';

    public function create($data) {
        // Check if package code already exists
        if ($this->codeExists($data['package_code'])) {
            // Generate a unique code by adding a timestamp or random number
            $data['package_code'] = $this->generateUniqueCode($data['package_code']);
        }
        
        $query = "INSERT INTO " . $this->table . "
                  (package_name, package_code, price, currency, duration, description, features, is_featured, sort_order)
                  VALUES (:name, :code, :price, :currency, :duration, :description, :features, :is_featured, :sort_order)";
        
        $stmt = $this->conn->prepare($query);
        
        // Convert features array to JSON if it's an array
        $features = is_array($data['features']) ? json_encode($data['features']) : $data['features'];
        
        return $stmt->execute([
            ':name' => $data['package_name'],
            ':code' => $data['package_code'],
            ':price' => $data['price'],
            ':currency' => $data['currency'],
            ':duration' => $data['duration'],
            ':description' => $data['description'],
            ':features' => $features,
            ':is_featured' => $data['is_featured'] ?? 0,
            ':sort_order' => $data['sort_order'] ?? 0
        ]);
    }

    public function update($id, $data) {
        // Check if package code already exists for a DIFFERENT package
        if ($this->codeExistsForDifferentId($data['package_code'], $id)) {
            // Generate a unique code
            $data['package_code'] = $this->generateUniqueCode($data['package_code']);
        }
        
        $query = "UPDATE " . $this->table . " SET
                  package_name = :name,
                  package_code = :code,
                  price = :price,
                  currency = :currency,
                  duration = :duration,
                  description = :description,
                  features = :features,
                  is_featured = :is_featured,
                  is_active = :is_active,
                  sort_order = :sort_order
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Convert features array to JSON if it's an array
        $features = is_array($data['features']) ? json_encode($data['features']) : $data['features'];
        
        return $stmt->execute([
            ':name' => $data['package_name'],
            ':code' => $data['package_code'],
            ':price' => $data['price'],
            ':currency' => $data['currency'],
            ':duration' => $data['duration'],
            ':description' => $data['description'],
            ':features' => $features,
            ':is_featured' => $data['is_featured'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':id' => $id
        ]);
    }

    public function codeExists($code) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE package_code = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$code]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function codeExistsForDifferentId($code, $id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE package_code = ? AND id != ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$code, $id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function generateUniqueCode($baseCode) {
        $counter = 1;
        $newCode = $baseCode;
        
        while ($this->codeExists($newCode)) {
            $newCode = $baseCode . '-' . $counter;
            $counter++;
        }
        
        return $newCode;
    }

    public function getFeatured() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_featured = 1 AND is_active = 1 ORDER BY sort_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>