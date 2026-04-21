<?php
// models/Gallery.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class Gallery extends BaseModel {
    protected $table = 'gallery_images';
    protected $primaryKey = 'id';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (title, description, image_url, thumbnail_url, category, photographer_id, sort_order, is_active, is_featured, created_at)
                  VALUES (:title, :description, :image_url, :thumbnail_url, :category, :photographer_id, :sort_order, :is_active, :is_featured, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':description' => $data['description'] ?? '',
            ':image_url' => $data['image_url'],
            ':thumbnail_url' => $data['thumbnail_url'] ?? $data['image_url'],
            ':category' => $data['category'] ?? '',
            ':photographer_id' => $data['photographer_id'] ?? null,
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
                  photographer_id = :photographer_id,
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
            ':photographer_id' => $data['photographer_id'] ?? null,
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

    public function getByPhotographer($photographer_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE is_active = 1 AND photographer_id = ? 
                  ORDER BY sort_order ASC, id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$photographer_id]);
        return $stmt->fetchAll();
    }

    public function incrementDownload($id) {
        $query = "UPDATE " . $this->table . " SET download_count = download_count + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table . " WHERE is_active = 1 AND category != '' AND category IS NOT NULL ORDER BY category ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>