<?php
// models/User.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    /**
     * Get all users - must match BaseModel::getAll($orderBy = 'id DESC')
     */
    public function getAll($orderBy = 'id DESC') {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY " . $orderBy;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("User GetAll Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("User GetById Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        try {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Sanitize and prepare data
            $username = trim($data['username']);
            $email = trim($data['email']);
            $full_name = trim($data['full_name'] ?? '');
            $role = $data['role'] ?? 'editor';
            $salary = isset($data['salary']) && is_numeric($data['salary']) ? floatval($data['salary']) : 0;
            $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            
            $query = "INSERT INTO " . $this->table . "
                      (username, email, password_hash, full_name, role, salary, is_active, created_at)
                      VALUES (:username, :email, :password_hash, :full_name, :role, :salary, :is_active, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $hashed_password,
                ':full_name' => $full_name,
                ':role' => $role,
                ':salary' => $salary,
                ':is_active' => $is_active
            ]);
            
            if ($result) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("User Create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        try {
            // Sanitize and prepare data
            $username = trim($data['username']);
            $email = trim($data['email']);
            $full_name = trim($data['full_name'] ?? '');
            $role = trim($data['role'] ?? 'editor');
            $salary = isset($data['salary']) && is_numeric($data['salary']) ? floatval($data['salary']) : 0;
            $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            
            // Verify role is valid
            $validRoles = ['admin', 'manager', 'photographer', 'editor', 'accounts'];
            if (!in_array($role, $validRoles)) {
                error_log("Invalid role: $role. Using 'editor' as default.");
                $role = 'editor';
            }
            
            $query = "UPDATE " . $this->table . " SET
                      username = :username,
                      email = :email,
                      full_name = :full_name,
                      role = :role,
                      salary = :salary,
                      is_active = :is_active,
                      updated_at = NOW()
                      WHERE " . $this->primaryKey . " = :id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':full_name' => $full_name,
                ':role' => $role,
                ':salary' => $salary,
                ':is_active' => $is_active,
                ':id' => $id
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("User Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        try {
            // First check if user exists
            $user = $this->getById($id);
            if (!$user) {
                error_log("Delete failed: User with ID $id not found");
                return false;
            }
            
            $query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $id]);
            
            if ($result) {
                error_log("User deleted successfully: ID $id, Username: " . $user['username']);
            } else {
                error_log("User delete failed: ID $id");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("User Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user password
     */
    public function updatePassword($id, $password) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE " . $this->table . " 
                      SET password_hash = :password, updated_at = NOW() 
                      WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':password' => $hashed_password, 
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Password Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE (username = :username OR email = :username) AND is_active = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':username' => $username]);
            
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $user['password_hash'])) {
                    $this->updateLastLogin($user['id']);
                    unset($user['password_hash']);
                    return $user;
                }
            }
            return false;
        } catch (PDOException $e) {
            error_log("Authentication Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin($id) {
        try {
            $query = "UPDATE " . $this->table . " SET last_login = NOW() WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Last Login Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check user permission
     */
    public function hasPermission($user_id, $permission) {
        $permissions = [
            'admin' => [
                'view_projects', 'create_projects', 'edit_projects', 'delete_projects',
                'view_users', 'create_users', 'edit_users', 'delete_users',
                'view_packages', 'create_packages', 'edit_packages', 'delete_packages',
                'view_addons', 'create_addons', 'edit_addons', 'delete_addons',
                'view_blog', 'create_blog', 'edit_blog', 'delete_blog',
                'view_gallery', 'upload_gallery', 'delete_gallery',
                'view_slider', 'create_slider', 'edit_slider', 'delete_slider',
                'view_messages', 'reply_messages',
                'view_orders', 'create_orders', 'edit_orders', 'delete_orders',
                'view_reports', 'export_reports',
                'manage_settings', 'manage_team', 'view_activity_log'
            ],
            'manager' => [
                'view_projects', 'create_projects', 'edit_projects',
                'view_packages', 'create_packages', 'edit_packages',
                'view_addons', 'create_addons', 'edit_addons',
                'view_blog', 'create_blog', 'edit_blog',
                'view_gallery', 'upload_gallery',
                'view_messages', 'reply_messages',
                'view_orders', 'create_orders', 'edit_orders',
                'view_reports'
            ],
            'photographer' => [
                'view_projects', 'view_packages', 'view_gallery', 'view_blog'
            ],
            'editor' => [
                'view_projects', 'edit_projects', 'view_packages',
                'view_blog', 'create_blog', 'edit_blog',
                'view_gallery', 'upload_gallery'
            ],
            'accounts' => [
                'view_projects', 'view_orders', 'create_orders', 'edit_orders',
                'view_reports', 'export_reports'
            ]
        ];
        
        $user = $this->getById($user_id);
        if ($user && isset($permissions[$user['role']])) {
            return in_array($permission, $permissions[$user['role']]);
        }
        return false;
    }
    
    /**
     * Get users by role
     */
    public function getByRole($role) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE role = :role AND is_active = 1 
                      ORDER BY username";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':role' => $role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get By Role Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active users
     */
    public function getActive() {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE is_active = 1 
                      ORDER BY role, username";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Active Users Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user with salary information
     */
    public function getWithSalary($id) {
        try {
            $query = "SELECT id, username, email, full_name, role, salary, is_active, last_login, created_at 
                      FROM " . $this->table . " 
                      WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get With Salary Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE username = :username";
            $params = [':username' => $username];
            
            if ($excludeId) {
                $query .= " AND " . $this->primaryKey . " != :id";
                $params[':id'] = $excludeId;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Username Exists Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE email = :email";
            $params = [':email' => $email];
            
            if ($excludeId) {
                $query .= " AND " . $this->primaryKey . " != :id";
                $params[':id'] = $excludeId;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Email Exists Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user statistics
     */
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN role = 'manager' THEN 1 ELSE 0 END) as managers,
                        SUM(CASE WHEN role = 'photographer' THEN 1 ELSE 0 END) as photographers,
                        SUM(CASE WHEN role = 'editor' THEN 1 ELSE 0 END) as editors,
                        SUM(CASE WHEN role = 'accounts' THEN 1 ELSE 0 END) as accounts
                      FROM " . $this->table;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Stats Error: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'admins' => 0,
                'managers' => 0,
                'photographers' => 0,
                'editors' => 0,
                'accounts' => 0
            ];
        }
    }
    
    /**
     * Search users
     */
    public function search($keyword) {
        try {
            $searchTerm = "%{$keyword}%";
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE username LIKE :keyword 
                      OR email LIKE :keyword 
                      OR full_name LIKE :keyword 
                      ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':keyword' => $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update user role (dedicated method)
     */
    public function updateRole($id, $role) {
        try {
            $validRoles = ['admin', 'manager', 'photographer', 'editor', 'accounts'];
            if (!in_array($role, $validRoles)) {
                error_log("Invalid role for updateRole: $role");
                return false;
            }
            
            $query = "UPDATE " . $this->table . " 
                      SET role = :role, updated_at = NOW() 
                      WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':role' => $role,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Update Role Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user status
     */
    public function updateStatus($id, $is_active) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET is_active = :is_active, updated_at = NOW() 
                      WHERE " . $this->primaryKey . " = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':is_active' => (int)$is_active,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Update Status Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by email
     */
    public function getByEmail($email) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get By Email Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by username
     */
    public function getByUsername($username) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':username' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get By Username Error: " . $e->getMessage());
            return null;
        }
    }
}
?>