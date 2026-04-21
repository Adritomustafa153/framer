<?php
// models/AddonService.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class AddonService extends BaseModel {
    protected $table = 'addon_services';
    protected $primaryKey = 'id';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (service_name, description, price, price_type, sort_order, is_active, created_at)
                  VALUES (:service_name, :description, :price, :price_type, :sort_order, :is_active, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':service_name' => $data['service_name'],
            ':description' => $data['description'] ?? '',
            ':price' => $data['price'],
            ':price_type' => $data['price_type'] ?? 'fixed',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  service_name = :service_name,
                  description = :description,
                  price = :price,
                  price_type = :price_type,
                  sort_order = :sort_order,
                  is_active = :is_active,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':service_name' => $data['service_name'],
            ':description' => $data['description'] ?? '',
            ':price' => $data['price'],
            ':price_type' => $data['price_type'] ?? 'fixed',
            ':sort_order' => $data['sort_order'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
            ':id' => $id
        ]);
    }

    public function getActive() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY sort_order ASC, service_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>