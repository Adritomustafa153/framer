<?php
// models/Message.php
require_once 'BaseModel.php';

class Message extends BaseModel {
    protected $table = 'contact_messages';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (name, email, phone, subject, message)
                  VALUES (:name, :email, :phone, :subject, :message)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':subject' => $data['subject'] ?? null,
            ':message' => $data['message']
        ]);
    }

    public function markAsRead($id) {
        $query = "UPDATE " . $this->table . " SET is_read = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function markAsReplied($id, $replied_by) {
        $query = "UPDATE " . $this->table . " SET is_replied = 1, replied_by = ?, replied_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$replied_by, $id]);
    }

    public function getUnreadCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch()['count'];
    }
}
?>