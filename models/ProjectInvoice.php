<?php
// models/ProjectInvoice.php
require_once __DIR__ . '/BaseModel.php';

class ProjectInvoice extends BaseModel {
    protected $table = 'project_invoices';
    protected $primaryKey = 'id';
    
    public function __construct($db) {
        parent::__construct($db);
    }
    
    /**
     * Create a new invoice
     * @return array|false Returns array with id and invoice_number, or false on failure
     */
    public function create($data) {
        try {
            // Generate invoice number
            $invoice_number = $this->generateInvoiceNumber();
            
            $query = "INSERT INTO " . $this->table . " 
                      (invoice_number, project_id, total_amount, paid_amount, due_amount, 
                       invoice_date, due_date, status, created_at) 
                      VALUES (:invoice_number, :project_id, :total_amount, :paid_amount, :due_amount,
                              :invoice_date, :due_date, :status, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            $result = $stmt->execute([
                ':invoice_number' => $invoice_number,
                ':project_id' => $data['project_id'],
                ':total_amount' => $data['total_amount'],
                ':paid_amount' => $data['paid_amount'],
                ':due_amount' => $data['due_amount'],
                ':invoice_date' => $data['invoice_date'],
                ':due_date' => $data['due_date'] ?? null,
                ':status' => $data['status']
            ]);
            
            if ($result) {
                $invoice_id = $this->conn->lastInsertId();
                return [
                    'id' => $invoice_id,
                    'invoice_number' => $invoice_number
                ];
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("ProjectInvoice Create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber() {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $query = "SELECT invoice_number FROM " . $this->table . " 
                  WHERE invoice_number LIKE :pattern 
                  ORDER BY id DESC LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':pattern' => $pattern]);
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($last) {
            // Extract the sequence number
            $last_number = (int)substr($last['invoice_number'], -4);
            $sequence = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return $prefix . $year . $month . $sequence;
    }
    
    /**
     * Update invoice
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      paid_amount = :paid_amount,
                      due_amount = :due_amount,
                      status = :status,
                      pdf_path = :pdf_path,
                      updated_at = NOW()
                      WHERE " . $this->primaryKey . " = :id";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                ':paid_amount' => $data['paid_amount'],
                ':due_amount' => $data['due_amount'],
                ':status' => $data['status'],
                ':pdf_path' => $data['pdf_path'] ?? null,
                ':id' => $id
            ]);
            
        } catch (PDOException $e) {
            error_log("ProjectInvoice Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get invoice by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectInvoice GetById Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get invoices by project ID
     */
    public function getByProject($project_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE project_id = :project_id 
                      ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectInvoice GetByProject Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark invoice email as sent
     */
    public function markEmailSent($id) {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      email_sent = 1, 
                      email_sent_at = NOW(),
                      updated_at = NOW()
                      WHERE " . $this->primaryKey . " = :id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("ProjectInvoice MarkEmailSent Error: " . $e->getMessage());
            return false;
        }
    }
}
?>