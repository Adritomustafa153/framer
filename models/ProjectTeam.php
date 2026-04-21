<?php
// models/ProjectTeam.php
require_once __DIR__ . '/BaseModel.php';

class ProjectTeam extends BaseModel {
    protected $table = 'project_team';
    protected $primaryKey = 'id';
    
    public function __construct($db) {
        parent::__construct($db);
    }
    
    /**
     * Assign a team member to a project
     */
    public function assign($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (project_id, user_id, role, assigned_by, assigned_at) 
                      VALUES (:project_id, :user_id, :role, :assigned_by, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                ':project_id' => $data['project_id'],
                ':user_id' => $data['user_id'],
                ':role' => $data['role'],
                ':assigned_by' => $data['assigned_by']
            ]);
        } catch (PDOException $e) {
            error_log("ProjectTeam Assign Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get team members by project ID
     */
    public function getByProject($project_id) {
        try {
            $query = "SELECT pt.*, u.username, u.full_name, u.email 
                      FROM " . $this->table . " pt
                      LEFT JOIN users u ON pt.user_id = u.id
                      WHERE pt.project_id = :project_id 
                      ORDER BY pt.assigned_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectTeam GetByProject Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete all team members for a project
     */
    public function deleteByProject($project_id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':project_id' => $project_id]);
        } catch (PDOException $e) {
            error_log("ProjectTeam DeleteByProject Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a specific team member assignment
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("ProjectTeam Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update team member role
     */
    public function updateRole($id, $role) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET role = :role 
                      WHERE " . $this->primaryKey . " = :id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':role' => $role,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("ProjectTeam UpdateRole Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get team members by role
     */
    public function getByRole($project_id, $role) {
        try {
            $query = "SELECT pt.*, u.username, u.full_name, u.email 
                      FROM " . $this->table . " pt
                      LEFT JOIN users u ON pt.user_id = u.id
                      WHERE pt.project_id = :project_id AND pt.role = :role";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':project_id' => $project_id,
                ':role' => $role
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectTeam GetByRole Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user is assigned to project
     */
    public function isAssigned($project_id, $user_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                      WHERE project_id = :project_id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':project_id' => $project_id,
                ':user_id' => $user_id
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("ProjectTeam IsAssigned Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get projects assigned to a user
     */
    public function getUserProjects($user_id, $status = null) {
        try {
            $query = "SELECT pt.*, p.project_name, p.project_code, p.event_date, p.status 
                      FROM " . $this->table . " pt
                      LEFT JOIN projects p ON pt.project_id = p.id
                      WHERE pt.user_id = :user_id";
            
            if ($status) {
                $query .= " AND p.status = :status";
            }
            
            $query .= " ORDER BY p.event_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $params = [':user_id' => $user_id];
            
            if ($status) {
                $params[':status'] = $status;
            }
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectTeam GetUserProjects Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get team statistics for a project
     */
    public function getTeamStats($project_id) {
        try {
            $query = "SELECT 
                        role,
                        COUNT(*) as count
                      FROM " . $this->table . "
                      WHERE project_id = :project_id
                      GROUP BY role";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectTeam GetTeamStats Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bulk assign team members
     */
    public function bulkAssign($project_id, $team_members) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($team_members as $member) {
                $query = "INSERT INTO " . $this->table . " 
                          (project_id, user_id, role, assigned_by, assigned_at) 
                          VALUES (:project_id, :user_id, :role, :assigned_by, NOW())";
                
                $stmt = $this->conn->prepare($query);
                $result = $stmt->execute([
                    ':project_id' => $project_id,
                    ':user_id' => $member['user_id'],
                    ':role' => $member['role'],
                    ':assigned_by' => $member['assigned_by']
                ]);
                
                if (!$result) {
                    throw new Exception("Failed to assign team member");
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("ProjectTeam BulkAssign Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get available users for team assignment (not already assigned to project)
     */
    public function getAvailableUsers($project_id) {
        try {
            $query = "SELECT u.id, u.username, u.full_name, u.email, u.role 
                      FROM users u
                      WHERE u.is_active = 1 
                      AND u.id NOT IN (
                          SELECT user_id FROM " . $this->table . " 
                          WHERE project_id = :project_id
                      )
                      ORDER BY u.role, u.username";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ProjectTeam GetAvailableUsers Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get team member count by project
     */
    public function getTeamCount($project_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':project_id' => $project_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("ProjectTeam GetTeamCount Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Remove all team members and reassign (for updates)
     */
    public function reassignAll($project_id, $new_team_members, $assigned_by) {
        try {
            $this->conn->beginTransaction();
            
            // Delete all existing assignments
            $this->deleteByProject($project_id);
            
            // Add new assignments
            foreach ($new_team_members as $member) {
                $this->assign([
                    'project_id' => $project_id,
                    'user_id' => $member['user_id'],
                    'role' => $member['role'],
                    'assigned_by' => $assigned_by
                ]);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("ProjectTeam ReassignAll Error: " . $e->getMessage());
            return false;
        }
    }
}
?>