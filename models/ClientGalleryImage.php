<?php
require_once __DIR__ . '/BaseModel.php';

class ClientGalleryImage extends BaseModel {
    protected $table = 'client_gallery_images';
    protected $primaryKey = 'id';

    public function create($data) {
        $query = "INSERT INTO {$this->table} (gallery_id, category, filename, original_name, file_path, thumbnail_path, size)
                  VALUES (:gallery_id, :category, :filename, :original_name, :file_path, :thumbnail_path, :size)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':gallery_id' => $data['gallery_id'],
            ':category' => $data['category'],
            ':filename' => $data['filename'],
            ':original_name' => $data['original_name'],
            ':file_path' => $data['file_path'],
            ':thumbnail_path' => $data['thumbnail_path'] ?? null,
            ':size' => $data['size'] ?? null
        ]);
    }

    public function getByGalleryAndCategory($gallery_id, $category) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE gallery_id = ? AND category = ? ORDER BY created_at ASC");
        $stmt->execute([$gallery_id, $category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCover($gallery_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE gallery_id = ? AND category = 'cover' LIMIT 1");
        $stmt->execute([$gallery_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteById($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateCategory($id, $category) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET category = ? WHERE id = ?");
        return $stmt->execute([$category, $id]);
    }
}
?>