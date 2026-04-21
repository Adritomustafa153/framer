<?php
// models/ProjectAddon.php
require_once __DIR__ . '/BaseModel.php';

class ProjectAddon extends BaseModel {
    protected $table = 'project_addons';
    protected $primaryKey = 'id';
    
    public function __construct($db) {
        parent::__construct($db);
    }
    
    /**
     * Create addon for project
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (project_id, addon_id, service_name, description, quantity, unit_price, total_price, created_at) 
                      VALUES (:project_id, :addon_id, :service_name, :description, :quantity, :unit_price, :total_price, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                ':project_id' => $data['project_id'],
                ':addon_id' => $data['addon_id'] ?? null,
                ':service_name' => $data['service_name'],
                ':description' => $data['description'] ?? '',
                ':quantity' => $data['quantity'],
                ':unit_price' => $data['unit_price'],
                ':total_price' => $data['total_price'] ?? ($data['quantity'] * $data['unit_price'])
            ]);
        } catch (PDOException $e) {
            error_log("ProjectAddon Create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get addons by project ID
     */
    public function getByProject($project_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE project_id = :project_id 
                      ORDER BY created_at ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectAddon GetByProject Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete all addons for a project
     */
    public function deleteByProject($project_id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':project_id' => $project_id]);
        } catch (PDOException $e) {
            error_log("ProjectAddon DeleteByProject Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a specific addon
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("ProjectAddon Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get total addons amount for a project
     */
    public function getTotalByProject($project_id) {
        try {
            $query = "SELECT SUM(total_price) as total FROM " . $this->table . " 
                      WHERE project_id = :project_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return floatval($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("ProjectAddon GetTotalByProject Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Update addon quantity or price
     */
    public function update($id, $data) {
        try {
            $total_price = ($data['quantity'] ?? 1) * ($data['unit_price'] ?? 0);
            
            $query = "UPDATE " . $this->table . " SET 
                      quantity = :quantity,
                      unit_price = :unit_price,
                      total_price = :total_price,
                      description = :description
                      WHERE " . $this->primaryKey . " = :id";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                ':quantity' => $data['quantity'],
                ':unit_price' => $data['unit_price'],
                ':total_price' => $total_price,
                ':description' => $data['description'] ?? '',
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("ProjectAddon Update Error: " . $e->getMessage());
            return false;
        }
    }
}
?>