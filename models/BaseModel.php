<?php
// models/BaseModel.php
abstract class BaseModel {
    protected $conn;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct($db) {
        $this->conn = $db;
    }

    // FIXED: Changed default to 'id DESC' since not all tables have created_at
    public function getAll($orderBy = 'id DESC') {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY " . $orderBy;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            // If column doesn't exist, fallback to primary key
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $query = "SELECT * FROM " . $this->table . " ORDER BY " . $this->primaryKey . " DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                return $stmt;
            }
            throw $e;
        }
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE " . $this->primaryKey . " = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
    
    public function getPaginated($limit, $offset, $orderBy = 'id DESC') {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY " . $orderBy . " LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$limit, $offset]);
            return $stmt;
        } catch (PDOException $e) {
            // Fallback to primary key
            $query = "SELECT * FROM " . $this->table . " ORDER BY " . $this->primaryKey . " DESC LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$limit, $offset]);
            return $stmt;
        }
    }
}
?>