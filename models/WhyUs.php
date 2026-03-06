<?php
// models/WhyUs.php
require_once 'BaseModel.php';

class WhyUs extends BaseModel {
    protected $table = 'why_us';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (title, description, icon, sort_order, is_active)
                  VALUES (:title, :description, :icon, :sort_order, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':icon' => $data['icon'] ?? null,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  title = :title,
                  description = :description,
                  icon = :icon,
                  sort_order = :sort_order,
                  is_active = :is_active
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':icon' => $data['icon'] ?? null,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':id' => $id
        ]);
    }

    public function getActive() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>