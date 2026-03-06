<?php
// models/Gallery.php
require_once 'BaseModel.php';

class Gallery extends BaseModel {
    protected $table = 'gallery_images';
    protected $primaryKey = 'id';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (title, description, image_url, thumbnail_url, category, sort_order, is_active, is_featured, created_at)
                  VALUES (:title, :description, :image_url, :thumbnail_url, :category, :sort_order, :is_active, :is_featured, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':description' => $data['description'] ?? '',
            ':image_url' => $data['image_url'],
            ':thumbnail_url' => $data['thumbnail_url'] ?? $data['image_url'],
            ':category' => $data['category'] ?? '',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':is_featured' => $data['is_featured'] ?? 0
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  title = :title,
                  description = :description,
                  image_url = :image_url,
                  thumbnail_url = :thumbnail_url,
                  category = :category,
                  sort_order = :sort_order,
                  is_active = :is_active,
                  is_featured = :is_featured,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':description' => $data['description'] ?? '',
            ':image_url' => $data['image_url'],
            ':thumbnail_url' => $data['thumbnail_url'] ?? $data['image_url'],
            ':category' => $data['category'] ?? '',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':is_featured' => $data['is_featured'] ?? 0,
            ':id' => $id
        ]);
    }

    public function getActive() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY sort_order ASC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFeatured() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 AND category = ? ORDER BY sort_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }

    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table . " WHERE is_active = 1 AND category != '' AND category IS NOT NULL ORDER BY category ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>