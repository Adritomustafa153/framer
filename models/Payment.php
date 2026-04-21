<?php
// models/Payment.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class Payment extends BaseModel {
    protected $table = 'payments';
    protected $primaryKey = 'id';

    public function generatePaymentNumber() {
        $prefix = 'PAY';
        $year = date('Y');
        $month = date('m');
        
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE payment_number LIKE '$prefix$year$month%'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . $year . $month . $sequence;
    }

    public function create($data) {
        $payment_number = $this->generatePaymentNumber();
        
        $query = "INSERT INTO " . $this->table . "
                  (booking_id, payment_number, amount, payment_method, transaction_id, payment_date, notes, received_by, created_at)
                  VALUES (:booking_id, :payment_number, :amount, :payment_method, :transaction_id, :payment_date, :notes, :received_by, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            ':booking_id' => $data['booking_id'],
            ':payment_number' => $payment_number,
            ':amount' => $data['amount'],
            ':payment_method' => $data['payment_method'],
            ':transaction_id' => $data['transaction_id'] ?? null,
            ':payment_date' => $data['payment_date'],
            ':notes' => $data['notes'] ?? null,
            ':received_by' => $data['received_by'] ?? null
        ]);
        
        if ($result) {
            return ['id' => $this->conn->lastInsertId(), 'payment_number' => $payment_number];
        }
        return false;
    }

    public function getByBooking($booking_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE booking_id = ? ORDER BY payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$booking_id]);
        return $stmt->fetchAll();
    }

    public function getTotalPaidByBooking($booking_id) {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM " . $this->table . " WHERE booking_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$booking_id]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function getPaymentsByDateRange($start_date, $end_date) {
        $query = "SELECT p.*, b.booking_number, c.bride_name, c.groom_name
                  FROM " . $this->table . " p
                  LEFT JOIN bookings b ON p.booking_id = b.id
                  LEFT JOIN clients c ON b.client_id = c.id
                  WHERE DATE(p.payment_date) BETWEEN ? AND ?
                  ORDER BY p.payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll();
    }
}
?>