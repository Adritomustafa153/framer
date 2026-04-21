<?php
// models/EmailLog.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class EmailLog extends BaseModel {
    protected $table = 'email_logs';
    protected $primaryKey = 'id';

    public function log($data) {
        $query = "INSERT INTO " . $this->table . "
                  (booking_id, invoice_id, recipient_email, subject, message, status, error_message, created_at)
                  VALUES (:booking_id, :invoice_id, :recipient_email, :subject, :message, :status, :error_message, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':booking_id' => $data['booking_id'] ?? null,
            ':invoice_id' => $data['invoice_id'] ?? null,
            ':recipient_email' => $data['recipient_email'],
            ':subject' => $data['subject'],
            ':message' => $data['message'] ?? null,
            ':status' => $data['status'] ?? 'sent',
            ':error_message' => $data['error_message'] ?? null
        ]);
    }

    public function getByBooking($booking_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE booking_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$booking_id]);
        return $stmt->fetchAll();
    }
}
?>