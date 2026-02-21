<?php
// models/ActivityLog.php
require_once 'BaseModel.php';

class ActivityLog extends BaseModel {
    protected $table = 'activity_log';

    public function log($user_id, $action, $table_name = null, $record_id = null, $old_data = null, $new_data = null) {
        $query = "INSERT INTO " . $this->table . "
                  (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent)
                  VALUES (:user_id, :action, :table_name, :record_id, :old_data, :new_data, :ip_address, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':action' => $action,
            ':table_name' => $table_name,
            ':record_id' => $record_id,
            ':old_data' => $old_data ? json_encode($old_data) : null,
            ':new_data' => $new_data ? json_encode($new_data) : null,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    public function getRecent($limit = 50) {
        $query = "SELECT l.*, u.username 
                  FROM " . $this->table . " l
                  LEFT JOIN users u ON l.user_id = u.id
                  ORDER BY l.created_at DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>