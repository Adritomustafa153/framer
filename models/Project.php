<?php
// models/Project.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class Project extends BaseModel {
    protected $table = 'projects';
    protected $primaryKey = 'id';

    public function generateProjectCode() {
        $prefix = 'PRJ';
        $year = date('Y');
        $month = date('m');
        
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE project_code LIKE '$prefix$year$month%'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . $year . $month . $sequence;
    }

    public function create($data) {
        $project_code = $this->generateProjectCode();
        
        $query = "INSERT INTO " . $this->table . "
                  (project_code, project_name, event_location, bride_name, groom_name, 
                   bride_email, bride_phone, bride_facebook, bride_instagram,
                   groom_email, groom_phone, groom_facebook, groom_instagram,
                   email, phone, alternate_phone, address, city, state, zip_code,
                   event_date, event_time, venue_name, venue_address, special_notes,
                   package_id, package_name, package_price, total_amount, status, created_by, created_at)
                  VALUES (:project_code, :project_name, :event_location, :bride_name, :groom_name,
                          :bride_email, :bride_phone, :bride_facebook, :bride_instagram,
                          :groom_email, :groom_phone, :groom_facebook, :groom_instagram,
                          :email, :phone, :alternate_phone, :address, :city, :state, :zip_code,
                          :event_date, :event_time, :venue_name, :venue_address, :special_notes,
                          :package_id, :package_name, :package_price, :total_amount, :status, :created_by, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            ':project_code' => $project_code,
            ':project_name' => $data['project_name'],
            ':event_location' => $data['event_location'] ?? null,
            ':bride_name' => $data['bride_name'],
            ':groom_name' => $data['groom_name'],
            ':bride_email' => $data['bride_email'] ?? null,
            ':bride_phone' => $data['bride_phone'] ?? null,
            ':bride_facebook' => $data['bride_facebook'] ?? null,
            ':bride_instagram' => $data['bride_instagram'] ?? null,
            ':groom_email' => $data['groom_email'] ?? null,
            ':groom_phone' => $data['groom_phone'] ?? null,
            ':groom_facebook' => $data['groom_facebook'] ?? null,
            ':groom_instagram' => $data['groom_instagram'] ?? null,
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':alternate_phone' => $data['alternate_phone'] ?? null,
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null,
            ':zip_code' => $data['zip_code'] ?? null,
            ':event_date' => $data['event_date'],
            ':event_time' => $data['event_time'] ?? null,
            ':venue_name' => $data['venue_name'] ?? null,
            ':venue_address' => $data['venue_address'] ?? null,
            ':special_notes' => $data['special_notes'] ?? null,
            ':package_id' => $data['package_id'] ?? null,
            ':package_name' => $data['package_name'],
            ':package_price' => $data['package_price'],
            ':total_amount' => $data['total_amount'],
            ':status' => $data['status'] ?? 'draft',
            ':created_by' => $data['created_by'] ?? null
        ]);
        
        if ($result) {
            return ['id' => $this->conn->lastInsertId(), 'project_code' => $project_code];
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  project_name = :project_name,
                  event_location = :event_location,
                  bride_name = :bride_name,
                  groom_name = :groom_name,
                  bride_email = :bride_email,
                  bride_phone = :bride_phone,
                  bride_facebook = :bride_facebook,
                  bride_instagram = :bride_instagram,
                  groom_email = :groom_email,
                  groom_phone = :groom_phone,
                  groom_facebook = :groom_facebook,
                  groom_instagram = :groom_instagram,
                  email = :email,
                  phone = :phone,
                  alternate_phone = :alternate_phone,
                  address = :address,
                  city = :city,
                  state = :state,
                  zip_code = :zip_code,
                  event_date = :event_date,
                  event_time = :event_time,
                  venue_name = :venue_name,
                  venue_address = :venue_address,
                  special_notes = :special_notes,
                  package_id = :package_id,
                  package_name = :package_name,
                  package_price = :package_price,
                  total_amount = :total_amount,
                  status = :status,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':project_name' => $data['project_name'],
            ':event_location' => $data['event_location'] ?? null,
            ':bride_name' => $data['bride_name'],
            ':groom_name' => $data['groom_name'],
            ':bride_email' => $data['bride_email'] ?? null,
            ':bride_phone' => $data['bride_phone'] ?? null,
            ':bride_facebook' => $data['bride_facebook'] ?? null,
            ':bride_instagram' => $data['bride_instagram'] ?? null,
            ':groom_email' => $data['groom_email'] ?? null,
            ':groom_phone' => $data['groom_phone'] ?? null,
            ':groom_facebook' => $data['groom_facebook'] ?? null,
            ':groom_instagram' => $data['groom_instagram'] ?? null,
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':alternate_phone' => $data['alternate_phone'] ?? null,
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null,
            ':zip_code' => $data['zip_code'] ?? null,
            ':event_date' => $data['event_date'],
            ':event_time' => $data['event_time'] ?? null,
            ':venue_name' => $data['venue_name'] ?? null,
            ':venue_address' => $data['venue_address'] ?? null,
            ':special_notes' => $data['special_notes'] ?? null,
            ':package_id' => $data['package_id'] ?? null,
            ':package_name' => $data['package_name'],
            ':package_price' => $data['package_price'],
            ':total_amount' => $data['total_amount'],
            ':status' => $data['status'] ?? 'draft',
            ':id' => $id
        ]);
    }

    public function getWithDetails($id) {
        $query = "SELECT p.*, 
                  (SELECT SUM(amount) FROM project_payments WHERE project_id = p.id) as paid_amount,
                  (SELECT COUNT(*) FROM project_team WHERE project_id = p.id) as team_count
                  FROM " . $this->table . " p
                  WHERE p.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllWithDetails($status = null, $limit = null, $offset = 0) {
        $query = "SELECT p.*, 
                  (SELECT SUM(amount) FROM project_payments WHERE project_id = p.id) as paid_amount,
                  (SELECT COUNT(*) FROM project_team WHERE project_id = p.id) as team_count,
                  u.username as created_by_name
                  FROM " . $this->table . " p
                  LEFT JOIN users u ON p.created_by = u.id";
        
        if ($status) {
            $query .= " WHERE p.status = :status";
        }
        
        $query .= " ORDER BY p.id DESC";
        
        if ($limit) {
            $query .= " LIMIT $offset, $limit";
        }
        
        $stmt = $this->conn->prepare($query);
        if ($status) {
            $stmt->execute([':status' => $status]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function updateInvoiceSent($id, $sent = 1) {
        $query = "UPDATE " . $this->table . " SET invoice_sent = :sent, invoice_sent_date = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':sent' => $sent, ':id' => $id]);
    }
}
?>