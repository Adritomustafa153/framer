<?php
// models/Package.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class Package extends BaseModel {
    protected $table = 'packages';

    public function create($data) {
        // Check if package code already exists
        if ($this->codeExists($data['package_code'])) {
            $data['package_code'] = $this->generateUniqueCode($data['package_code']);
        }
        
        $query = "INSERT INTO " . $this->table . "
                  (package_name, category, package_code, price, currency, duration, description, features, image_url, is_featured, sort_order, created_at)
                  VALUES (:name, :category, :code, :price, :currency, :duration, :description, :features, :image_url, :is_featured, :sort_order, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        $features = is_array($data['features']) ? json_encode($data['features']) : $data['features'];
        
        return $stmt->execute([
            ':name' => $data['package_name'],
            ':category' => $data['category'] ?? 'Other',
            ':code' => $data['package_code'],
            ':price' => $data['price'],
            ':currency' => $data['currency'],
            ':duration' => $data['duration'],
            ':description' => $data['description'] ?? '',
            ':features' => $features,
            ':image_url' => $data['image_url'] ?? '',
            ':is_featured' => $data['is_featured'] ?? 0,
            ':sort_order' => $data['sort_order'] ?? 0
        ]);
    }

    public function update($id, $data) {
        // Check if package code already exists for a DIFFERENT package
        if ($this->codeExistsForDifferentId($data['package_code'], $id)) {
            $data['package_code'] = $this->generateUniqueCode($data['package_code']);
        }
        
        $query = "UPDATE " . $this->table . " SET
                  package_name = :name,
                  category = :category,
                  package_code = :code,
                  price = :price,
                  currency = :currency,
                  duration = :duration,
                  description = :description,
                  features = :features,
                  image_url = :image_url,
                  is_featured = :is_featured,
                  is_active = :is_active,
                  sort_order = :sort_order,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $features = is_array($data['features']) ? json_encode($data['features']) : $data['features'];
        
        return $stmt->execute([
            ':name' => $data['package_name'],
            ':category' => $data['category'] ?? 'Other',
            ':code' => $data['package_code'],
            ':price' => $data['price'],
            ':currency' => $data['currency'],
            ':duration' => $data['duration'],
            ':description' => $data['description'] ?? '',
            ':features' => $features,
            ':image_url' => $data['image_url'] ?? '',
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
    
    public function getActive() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY is_featured DESC, sort_order ASC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 AND category = ? ORDER BY is_featured DESC, sort_order ASC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    public function getCategories() {
        $query = "SELECT DISTINCT category, COUNT(*) as count FROM " . $this->table . " WHERE is_active = 1 AND category IS NOT NULL AND category != '' GROUP BY category ORDER BY category ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function incrementDownload($id) {
        $query = "UPDATE " . $this->table . " SET download_count = download_count + 1, popularity = popularity + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
    
    public function getPopular() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY popularity DESC, download_count DESC LIMIT 6";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Method to get package by ID with all fields
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        // If features is stored as JSON, decode it
        if ($result && isset($result['features']) && is_string($result['features'])) {
            $result['features'] = json_decode($result['features'], true);
        }
        
        return $result;
    }
}
?>