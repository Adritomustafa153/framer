<?php
// models/Gallery.php
require_once 'BaseModel.php';

class Gallery extends BaseModel {
    protected $table = 'gallery_images';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (title, description, image_url, thumbnail_url, category, tags, sort_order, is_featured, uploaded_by)
                  VALUES (:title, :description, :image_url, :thumbnail_url, :category, :tags, :sort_order, :is_featured, :uploaded_by)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':image_url' => $data['image_url'],
            ':thumbnail_url' => $data['thumbnail_url'] ?? $data['image_url'],
            ':category' => $data['category'],
            ':tags' => json_encode($data['tags'] ?? []),
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_featured' => $data['is_featured'] ?? 0,
            ':uploaded_by' => $data['uploaded_by']
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  title = :title,
                  description = :description,
                  image_url = :image_url,
                  thumbnail_url = :thumbnail_url,
                  category = :category,
                  tags = :tags,
                  sort_order = :sort_order,
                  is_featured = :is_featured,
                  is_active = :is_active
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':image_url' => $data['image_url'],
            ':thumbnail_url' => $data['thumbnail_url'] ?? $data['image_url'],
            ':category' => $data['category'],
            ':tags' => json_encode($data['tags'] ?? []),
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_featured' => $data['is_featured'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':id' => $id
        ]);
    }

    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table . " WHERE category = ? AND is_active = 1 ORDER BY sort_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
}
?>