<?php
// models/ProjectFamilyMember.php
require_once __DIR__ . '/BaseModel.php';

class ProjectFamilyMember extends BaseModel {
    protected $table = 'project_family_members';
    protected $primaryKey = 'id';
    
    public function __construct($db) {
        parent::__construct($db);
    }
    
    /**
     * Create family member
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (project_id, name, relationship, side, phone, notes, created_at) 
                      VALUES (:project_id, :name, :relationship, :side, :phone, :notes, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                ':project_id' => $data['project_id'],
                ':name' => $data['name'],
                ':relationship' => $data['relationship'],
                ':side' => $data['side'],
                ':phone' => $data['phone'] ?? null,
                ':notes' => $data['notes'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("ProjectFamilyMember Create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get family members by project ID
     */
    public function getByProject($project_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE project_id = :project_id 
                      ORDER BY side, relationship, name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectFamilyMember GetByProject Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete all family members for a project
     */
    public function deleteByProject($project_id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':project_id' => $project_id]);
        } catch (PDOException $e) {
            error_log("ProjectFamilyMember DeleteByProject Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a specific family member
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("ProjectFamilyMember Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update family member
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      name = :name,
                      relationship = :relationship,
                      side = :side,
                      phone = :phone,
                      notes = :notes
                      WHERE " . $this->primaryKey . " = :id";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                ':name' => $data['name'],
                ':relationship' => $data['relationship'],
                ':side' => $data['side'],
                ':phone' => $data['phone'] ?? null,
                ':notes' => $data['notes'] ?? null,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("ProjectFamilyMember Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get family members by side
     */
    public function getBySide($project_id, $side) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE project_id = :project_id AND side = :side 
                      ORDER BY relationship, name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':project_id' => $project_id,
                ':side' => $side
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectFamilyMember GetBySide Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get family member count
     */
    public function getCount($project_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("ProjectFamilyMember GetCount Error: " . $e->getMessage());
            return 0;
        }
    }
}
?>