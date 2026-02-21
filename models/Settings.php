<?php
// models/Settings.php
require_once 'BaseModel.php';

class Settings extends BaseModel {
    protected $table = 'settings';
    protected $primaryKey = 'setting_key';

    public function get($key) {
        $query = "SELECT setting_value FROM " . $this->table . " WHERE setting_key = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }

    public function set($key, $value, $type = 'text', $description = null) {
        // Check if exists
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE setting_key = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$key]);
        
        if ($stmt->fetch()['count'] > 0) {
            $query = "UPDATE " . $this->table . " SET setting_value = ?, setting_type = ?, description = ? WHERE setting_key = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$value, $type, $description, $key]);
        } else {
            $query = "INSERT INTO " . $this->table . " (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$key, $value, $type, $description]);
        }
    }

    // Fix: Add the $orderBy parameter with default value to match parent signature
    public function getAll($orderBy = 'setting_key ASC') {
        $query = "SELECT * FROM " . $this->table . " ORDER BY " . $orderBy;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Get settings as key-value pairs for easy access
    public function getAllAsArray() {
        $settings = [];
        $query = "SELECT setting_key, setting_value FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    // Get settings by group/type
    public function getByType($type) {
        $query = "SELECT * FROM " . $this->table . " WHERE setting_type = ? ORDER BY setting_key ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }
    
    // Delete a setting
    public function delete($key) {
        $query = "DELETE FROM " . $this->table . " WHERE setting_key = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$key]);
    }
}
?>