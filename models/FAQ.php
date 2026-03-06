<?php
// models/FAQ.php
require_once 'BaseModel.php';

class FAQ extends BaseModel {
    protected $table = 'faqs';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (question, answer, category, sort_order, is_active)
                  VALUES (:question, :answer, :category, :sort_order, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':question' => $data['question'],
            ':answer' => $data['answer'],
            ':category' => $data['category'] ?? '',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  question = :question,
                  answer = :answer,
                  category = :category,
                  sort_order = :sort_order,
                  is_active = :is_active
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':question' => $data['question'],
            ':answer' => $data['answer'],
            ':category' => $data['category'] ?? '',
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