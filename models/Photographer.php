<?php
// models/Photographer.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class Photographer extends BaseModel {
    protected $table = 'photographers';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (name, bio, photo, sort_order, is_active, created_at)
                  VALUES (:name, :bio, :photo, :sort_order, :is_active, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':bio' => $data['bio'] ?? '',
            ':photo' => $data['photo'] ?? '',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  name = :name,
                  bio = :bio,
                  photo = :photo,
                  sort_order = :sort_order,
                  is_active = :is_active,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':bio' => $data['bio'] ?? '',
            ':photo' => $data['photo'] ?? '',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':id' => $id
        ]);
    }

    public function getActive() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getWithImageCount() {
        $query = "SELECT p.*, 
                  (SELECT COUNT(*) FROM gallery_images WHERE photographer_id = p.id AND is_active = 1) as image_count 
                  FROM " . $this->table . " p 
                  WHERE p.is_active = 1 
                  ORDER BY p.sort_order ASC, p.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function delete($id) {
        // Check if photographer has images
        $checkQuery = "SELECT COUNT(*) as count FROM gallery_images WHERE photographer_id = ?";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            // Set photographer_id to NULL for all images
            $updateQuery = "UPDATE gallery_images SET photographer_id = NULL WHERE photographer_id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([$id]);
        }
        
        // Delete photographer
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>