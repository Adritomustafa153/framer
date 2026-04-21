<?php
// models/ClientGallery.php
require_once __DIR__ . '/BaseModel.php';

class ClientGallery extends BaseModel {
    protected $table = 'client_galleries';
    protected $primaryKey = 'id';

    // Generate unique gallery code
    public function generateUniqueCode() {
        $code = 'GAL' . strtoupper(uniqid());
        while ($this->codeExists($code)) {
            $code = 'GAL' . strtoupper(uniqid());
        }
        return $code;
    }

    private function codeExists($code) {
        $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE gallery_code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch() !== false;
    }

    // Create new gallery
    public function create($data) {
        $data['gallery_code'] = $this->generateUniqueCode();
        if (empty($data['share_date'])) $data['share_date'] = date('Y-m-d');
        if (empty($data['cover_orientation'])) $data['cover_orientation'] = 'landscape';
        $query = "INSERT INTO {$this->table} 
                  (gallery_code, project_id, client_id, title, headline, story, 
                   cover_image_id, is_active, password, cover_orientation, expiry_date, logo_path, share_date)
                  VALUES (:code, :project_id, :client_id, :title, :headline, :story, 
                          :cover_image_id, :is_active, :password, :cover_orientation, :expiry_date, :logo_path, :share_date)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':code' => $data['gallery_code'],
            ':project_id' => $data['project_id'] ?? null,
            ':client_id' => $data['client_id'] ?? null,
            ':title' => $data['title'],
            ':headline' => $data['headline'] ?? '',
            ':story' => $data['story'] ?? '',
            ':cover_image_id' => $data['cover_image_id'] ?? null,
            ':is_active' => $data['is_active'] ?? 1,
            ':password' => $data['password'] ?? null,
            ':cover_orientation' => $data['cover_orientation'],
            ':expiry_date' => $data['expiry_date'] ?? null,
            ':logo_path' => $data['logo_path'] ?? null,
            ':share_date' => $data['share_date']
        ]);
    }

    // Update gallery
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        $allowed = ['title', 'headline', 'story', 'cover_image_id', 'is_active', 'password', 
                    'cover_orientation', 'expiry_date', 'logo_path', 'share_date'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    // Get gallery by code (used in public view)
    public function getByCode($code) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE gallery_code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get gallery with related project/client details
    public function getWithDetails($id) {
        $stmt = $this->conn->prepare("SELECT g.*, p.project_name, c.bride_name, c.groom_name, c.email
                                      FROM {$this->table} g
                                      LEFT JOIN projects p ON g.project_id = p.id
                                      LEFT JOIN clients c ON g.client_id = c.id
                                      WHERE g.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if gallery is expired
    public function isExpired($gallery) {
        if (empty($gallery['expiry_date'])) return false;
        return strtotime($gallery['expiry_date']) < time();
    }

    // Set expiry date based on duration string
    public function setExpiryDate($duration) {
        $now = new DateTime();
        switch ($duration) {
            case '7days':   $now->modify('+7 days'); break;
            case '1month':  $now->modify('+1 month'); break;
            case '6months': $now->modify('+6 months'); break;
            case '1year':   $now->modify('+1 year'); break;
            default: return null;
        }
        return $now->format('Y-m-d H:i:s');
    }
}
?>