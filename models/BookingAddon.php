<?php
// models/BookingAddon.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class BookingAddon extends BaseModel {
    protected $table = 'booking_addons';
    protected $primaryKey = 'id';

    public function create($data) {
        $total_price = $data['unit_price'] * ($data['quantity'] ?? 1);
        
        $query = "INSERT INTO " . $this->table . "
                  (booking_id, addon_id, service_name, description, quantity, unit_price, total_price, created_at)
                  VALUES (:booking_id, :addon_id, :service_name, :description, :quantity, :unit_price, :total_price, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':booking_id' => $data['booking_id'],
            ':addon_id' => $data['addon_id'] ?? null,
            ':service_name' => $data['service_name'],
            ':description' => $data['description'] ?? '',
            ':quantity' => $data['quantity'] ?? 1,
            ':unit_price' => $data['unit_price'],
            ':total_price' => $total_price
        ]);
    }

    public function getByBooking($booking_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE booking_id = ? ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$booking_id]);
        return $stmt->fetchAll();
    }

    public function deleteByBooking($booking_id) {
        $query = "DELETE FROM " . $this->table . " WHERE booking_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$booking_id]);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function getTotalByBooking($booking_id) {
        $query = "SELECT COALESCE(SUM(total_price), 0) as total FROM " . $this->table . " WHERE booking_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$booking_id]);
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>