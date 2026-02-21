<?php
// models/User.php
require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    
    public function authenticate($username, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $username]);
        
        if ($user = $stmt->fetch()) {
            if (password_verify($password, $user['password_hash'])) {
                // Update last login
                $update = "UPDATE " . $this->table . " SET last_login = NOW() WHERE id = ?";
                $stmt = $this->conn->prepare($update);
                $stmt->execute([$user['id']]);
                
                unset($user['password_hash']);
                return $user;
            }
        }
        return false;
    }

    public function create($data) {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO " . $this->table . "
                  (username, email, password_hash, full_name, role)
                  VALUES (:username, :email, :password_hash, :full_name, :role)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':full_name' => $data['full_name'],
            ':role' => $data['role'] ?? 'editor'
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET 
                  full_name = :full_name,
                  email = :email,
                  role = :role
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':role' => $data['role'],
            ':id' => $id
        ]);
    }
}
?>