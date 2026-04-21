<?php
// models/Client.php
require_once __DIR__ . '/../config/database.php';
require_once 'BaseModel.php';

class Client extends BaseModel {
    protected $table = 'clients';
    protected $primaryKey = 'id';

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (bride_name, groom_name, email, bride_email, bride_phone, bride_facebook, bride_instagram,
                   groom_email, groom_phone, groom_facebook, groom_instagram, phone, alternate_phone, 
                   address, city, state, zip_code, country, created_at)
                  VALUES (:bride_name, :groom_name, :email, :bride_email, :bride_phone, :bride_facebook, :bride_instagram,
                          :groom_email, :groom_phone, :groom_facebook, :groom_instagram, :phone, :alternate_phone, 
                          :address, :city, :state, :zip_code, :country, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            ':bride_name' => $data['bride_name'],
            ':groom_name' => $data['groom_name'],
            ':email' => $data['email'] ?? $data['bride_email'] ?? '',
            ':bride_email' => $data['bride_email'] ?? null,
            ':bride_phone' => $data['bride_phone'] ?? null,
            ':bride_facebook' => $data['bride_facebook'] ?? null,
            ':bride_instagram' => $data['bride_instagram'] ?? null,
            ':groom_email' => $data['groom_email'] ?? null,
            ':groom_phone' => $data['groom_phone'] ?? null,
            ':groom_facebook' => $data['groom_facebook'] ?? null,
            ':groom_instagram' => $data['groom_instagram'] ?? null,
            ':phone' => $data['phone'] ?? '',
            ':alternate_phone' => $data['alternate_phone'] ?? null,
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null,
            ':zip_code' => $data['zip_code'] ?? null,
            ':country' => $data['country'] ?? 'Bangladesh'
        ]);
        
        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  bride_name = :bride_name,
                  groom_name = :groom_name,
                  email = :email,
                  bride_email = :bride_email,
                  bride_phone = :bride_phone,
                  bride_facebook = :bride_facebook,
                  bride_instagram = :bride_instagram,
                  groom_email = :groom_email,
                  groom_phone = :groom_phone,
                  groom_facebook = :groom_facebook,
                  groom_instagram = :groom_instagram,
                  phone = :phone,
                  alternate_phone = :alternate_phone,
                  address = :address,
                  city = :city,
                  state = :state,
                  zip_code = :zip_code,
                  country = :country,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':bride_name' => $data['bride_name'],
            ':groom_name' => $data['groom_name'],
            ':email' => $data['email'] ?? $data['bride_email'] ?? '',
            ':bride_email' => $data['bride_email'] ?? null,
            ':bride_phone' => $data['bride_phone'] ?? null,
            ':bride_facebook' => $data['bride_facebook'] ?? null,
            ':bride_instagram' => $data['bride_instagram'] ?? null,
            ':groom_email' => $data['groom_email'] ?? null,
            ':groom_phone' => $data['groom_phone'] ?? null,
            ':groom_facebook' => $data['groom_facebook'] ?? null,
            ':groom_instagram' => $data['groom_instagram'] ?? null,
            ':phone' => $data['phone'] ?? '',
            ':alternate_phone' => $data['alternate_phone'] ?? null,
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null,
            ':zip_code' => $data['zip_code'] ?? null,
            ':country' => $data['country'] ?? 'Bangladesh',
            ':id' => $id
        ]);
    }

    public function getByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ? OR bride_email = ? OR groom_email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email, $email, $email]);
        return $stmt->fetch();
    }

    public function getByPhone($phone) {
        $query = "SELECT * FROM " . $this->table . " WHERE phone = ? OR bride_phone = ? OR groom_phone = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$phone, $phone, $phone]);
        return $stmt->fetch();
    }

    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE bride_name LIKE :keyword 
                  OR groom_name LIKE :keyword 
                  OR email LIKE :keyword 
                  OR bride_email LIKE :keyword
                  OR groom_email LIKE :keyword
                  OR phone LIKE :keyword 
                  OR bride_phone LIKE :keyword
                  OR groom_phone LIKE :keyword
                  ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':keyword' => '%' . $keyword . '%']);
        return $stmt->fetchAll();
    }
}
?>