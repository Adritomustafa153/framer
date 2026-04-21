<?php
// models/Invoice.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class Invoice extends BaseModel {
    protected $table = 'invoices';
    protected $primaryKey = 'id';

    public function generateInvoiceNumber() {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE invoice_number LIKE '$prefix$year$month%'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . $year . $month . $sequence;
    }

    public function create($data) {
        $invoice_number = $this->generateInvoiceNumber();
        
        $query = "INSERT INTO " . $this->table . "
                  (invoice_number, booking_id, total_amount, paid_amount, due_amount, invoice_date, due_date, status, created_at)
                  VALUES (:invoice_number, :booking_id, :total_amount, :paid_amount, :due_amount, :invoice_date, :due_date, :status, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            ':invoice_number' => $invoice_number,
            ':booking_id' => $data['booking_id'],
            ':total_amount' => $data['total_amount'],
            ':paid_amount' => $data['paid_amount'] ?? 0,
            ':due_amount' => $data['due_amount'],
            ':invoice_date' => $data['invoice_date'],
            ':due_date' => $data['due_date'] ?? null,
            ':status' => $data['status'] ?? 'unpaid'
        ]);
        
        if ($result) {
            return ['id' => $this->conn->lastInsertId(), 'invoice_number' => $invoice_number];
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  paid_amount = :paid_amount,
                  due_amount = :due_amount,
                  status = :status,
                  pdf_path = :pdf_path,
                  sent_at = :sent_at,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':paid_amount' => $data['paid_amount'],
            ':due_amount' => $data['due_amount'],
            ':status' => $data['status'],
            ':pdf_path' => $data['pdf_path'] ?? null,
            ':sent_at' => $data['sent_at'] ?? null,
            ':id' => $id
        ]);
    }

    public function getByBooking($booking_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE booking_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$booking_id]);
        return $stmt->fetch();
    }

    public function getAllWithDetails($limit = null, $offset = 0) {
        $query = "SELECT i.*, b.booking_number, c.bride_name, c.groom_name, c.email
                  FROM " . $this->table . " i
                  LEFT JOIN bookings b ON i.booking_id = b.id
                  LEFT JOIN clients c ON b.client_id = c.id
                  ORDER BY i.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT $offset, $limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPendingInvoices() {
        $query = "SELECT i.*, b.booking_number, c.bride_name, c.groom_name, c.email
                  FROM " . $this->table . " i
                  LEFT JOIN bookings b ON i.booking_id = b.id
                  LEFT JOIN clients c ON b.client_id = c.id
                  WHERE i.status IN ('unpaid', 'partial')
                  ORDER BY i.due_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateAfterPayment($booking_id, $paid_amount, $total_amount) {
        $due_amount = $total_amount - $paid_amount;
        $status = $due_amount <= 0 ? 'paid' : ($paid_amount > 0 ? 'partial' : 'unpaid');
        
        $query = "UPDATE " . $this->table . " SET
                  paid_amount = :paid_amount,
                  due_amount = :due_amount,
                  status = :status,
                  updated_at = NOW()
                  WHERE booking_id = :booking_id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':paid_amount' => $paid_amount,
            ':due_amount' => $due_amount,
            ':status' => $status,
            ':booking_id' => $booking_id
        ]);
    }
}
?>