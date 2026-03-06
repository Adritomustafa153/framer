<?php
// models/GallerySettings.php
require_once 'BaseModel.php';

class GallerySettings extends BaseModel {
    protected $table = 'gallery_settings';
    protected $primaryKey = 'id';

    public function get($key) {
        $query = "SELECT setting_value FROM " . $this->table . " WHERE setting_key = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }

    public function set($key, $value, $description = null) {
        // Check if exists
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE setting_key = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$key]);
        
        if ($stmt->fetch()['count'] > 0) {
            $query = "UPDATE " . $this->table . " SET setting_value = ?, description = ? WHERE setting_key = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$value, $description, $key]);
        } else {
            $query = "INSERT INTO " . $this->table . " (setting_key, setting_value, description) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$key, $value, $description]);
        }
    }

    // FIXED: Added $orderBy parameter with default value to match parent class
    public function getAll($orderBy = 'setting_key ASC') {
        $query = "SELECT * FROM " . $this->table . " ORDER BY " . $orderBy;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Get settings as key-value pairs
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
}
?>