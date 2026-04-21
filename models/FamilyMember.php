<?php
// models/FamilyMember.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class FamilyMember extends BaseModel {
    protected $table = 'family_members';
    protected $primaryKey = 'id';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (client_id, name, relationship, side, phone, notes, created_at)
                  VALUES (:client_id, :name, :relationship, :side, :phone, :notes, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':client_id' => $data['client_id'],
            ':name' => $data['name'],
            ':relationship' => $data['relationship'],
            ':side' => $data['side'] ?? 'bride',
            ':phone' => $data['phone'] ?? null,
            ':notes' => $data['notes'] ?? null
        ]);
    }

    public function getByClient($client_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE client_id = ? ORDER BY side, FIELD(relationship, 'father','mother','brother','sister','other')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$client_id]);
        return $stmt->fetchAll();
    }

    public function deleteByClient($client_id) {
        $query = "DELETE FROM " . $this->table . " WHERE client_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$client_id]);
    }
}
?>