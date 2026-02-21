<?php
// models/BaseModel.php
abstract class BaseModel {
    protected $conn;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($orderBy = 'created_at DESC') {
        $query = "SELECT * FROM " . $this->table . " ORDER BY " . $orderBy;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
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
}
?>