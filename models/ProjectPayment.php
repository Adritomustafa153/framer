<?php
// models/ProjectPayment.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class ProjectPayment extends BaseModel {
    protected $table = 'project_payments';
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
                  (project_id, payment_number, amount, payment_method, transaction_id, payment_date, notes, received_by, created_at)
                  VALUES (:project_id, :payment_number, :amount, :payment_method, :transaction_id, :payment_date, :notes, :received_by, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':project_id' => $data['project_id'],
            ':payment_number' => $payment_number,
            ':amount' => $data['amount'],
            ':payment_method' => $data['payment_method'],
            ':transaction_id' => $data['transaction_id'] ?? null,
            ':payment_date' => $data['payment_date'],
            ':notes' => $data['notes'] ?? null,
            ':received_by' => $data['received_by'] ?? null
        ]);
    }

    public function getByProject($project_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE project_id = ? ORDER BY payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$project_id]);
        return $stmt->fetchAll();
    }

    public function getTotalPaidByProject($project_id) {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM " . $this->table . " WHERE project_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$project_id]);
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>