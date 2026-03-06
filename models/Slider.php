<?php
// models/Slider.php
require_once 'BaseModel.php';

class Slider extends BaseModel {
    protected $table = 'slider_images';

    public function create($data) {
        // Clean up image URL
        if (isset($data['image_url']) && !empty($data['image_url'])) {
            // Remove any leading ../ if present
            $data['image_url'] = ltrim($data['image_url'], '/');
            $data['image_url'] = str_replace('../', '', $data['image_url']);
        }
        
        $query = "INSERT INTO " . $this->table . "
                  (title, description, image_url, link, sort_order, is_active)
                  VALUES (:title, :description, :image_url, :link, :sort_order, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':description' => $data['description'] ?? '',
            ':image_url' => $data['image_url'] ?? '',
            ':link' => $data['link'] ?? '',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        // Clean up image URL if provided
        if (isset($data['image_url']) && !empty($data['image_url'])) {
            $data['image_url'] = ltrim($data['image_url'], '/');
            $data['image_url'] = str_replace('../', '', $data['image_url']);
        }
        
        $query = "UPDATE " . $this->table . " SET
                  title = :title,
                  description = :description,
                  image_url = :image_url,
                  link = :link,
                  sort_order = :sort_order,
                  is_active = :is_active
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':description' => $data['description'] ?? '',
            ':image_url' => $data['image_url'] ?? '',
            ':link' => $data['link'] ?? '',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':id' => $id
        ]);
    }

    public function getActive() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY sort_order ASC";
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
        // Get image path before deleting
        $slide = $this->getById($id);
        if ($slide && !empty($slide['image_url'])) {
            // Try to delete local file if it exists
            $filePath = '../' . ltrim($slide['image_url'], '/');
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>