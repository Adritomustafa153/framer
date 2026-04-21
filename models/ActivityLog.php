<?php
// models/ActivityLog.php

class ActivityLog {
    private $conn;
    private $table = 'activity_log';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Log an activity
     * @param int $userId User ID who performed the action
     * @param string $action Action type (create, update, delete, etc.)
     * @param string $tableName Name of the affected table
     * @param int $recordId ID of the affected record
     * @param mixed $oldData Old data (array or null)
     * @param mixed $newData New data (array or null)
     * @param string $description Optional description (will be stored in new_data if provided)
     * @return bool Success status
     */
    public function log($userId, $action, $tableName, $recordId, $oldData = null, $newData = null, $description = null) {
        try {
            // Prepare the query without description field
            $query = "INSERT INTO " . $this->table . " 
                      (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent) 
                      VALUES (:user_id, :action, :table_name, :record_id, :old_data, :new_data, :ip_address, :user_agent)";
            
            $stmt = $this->conn->prepare($query);
            
            // Convert arrays to JSON if needed
            $oldDataJson = null;
            if ($oldData !== null) {
                $oldDataJson = is_array($oldData) ? json_encode($oldData) : $oldData;
            }
            
            $newDataJson = null;
            if ($newData !== null) {
                // If description is provided, include it in the new_data JSON
                if ($description !== null && is_array($newData)) {
                    $newDataWithDescription = $newData;
                    $newDataWithDescription['_description'] = $description;
                    $newDataJson = json_encode($newDataWithDescription);
                } elseif ($description !== null && !is_array($newData)) {
                    // If newData is not an array but description is provided
                    $newDataJson = json_encode(['data' => $newData, '_description' => $description]);
                } else {
                    $newDataJson = is_array($newData) ? json_encode($newData) : $newData;
                }
            } elseif ($description !== null) {
                // If only description is provided (no new_data)
                $newDataJson = json_encode(['_description' => $description]);
            }
            
            // Get IP address
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            if ($ipAddress === '::1' || $ipAddress === '127.0.0.1') {
                $ipAddress = 'localhost';
            }
            
            // Get user agent
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Bind parameters
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->bindParam(':record_id', $recordId);
            $stmt->bindParam(':old_data', $oldDataJson);
            $stmt->bindParam(':new_data', $newDataJson);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("ActivityLog Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get activity logs with optional filters
     * @param array $filters Optional filters (user_id, action, table_name, date_from, date_to)
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Activity logs
     */
    public function getLogs($filters = [], $limit = 100, $offset = 0) {
        $query = "SELECT al.*, u.username, u.full_name 
                  FROM " . $this->table . " al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $query .= " AND al.action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $query .= " AND al.table_name = :table_name";
            $params[':table_name'] = $filters['table_name'];
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(al.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(al.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON data
        foreach ($logs as &$log) {
            if ($log['old_data']) {
                $log['old_data'] = json_decode($log['old_data'], true);
            }
            if ($log['new_data']) {
                $log['new_data'] = json_decode($log['new_data'], true);
            }
        }
        
        return $logs;
    }
    
    /**
     * Get total count of logs with filters
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function getLogsCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $query .= " AND action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $query .= " AND table_name = :table_name";
            $params[':table_name'] = $filters['table_name'];
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'];
    }
    
    /**
     * Clean old logs (older than specified days)
     * @param int $days Days to keep (default: 90)
     * @return bool Success status
     */
    public function cleanOldLogs($days = 90) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Get activity summary for dashboard
     * @param int $days Number of days to summarize (default: 7)
     * @return array Summary data
     */
    public function getSummary($days = 7) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    action,
                    COUNT(*) as count
                  FROM " . $this->table . "
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at), action
                  ORDER BY date DESC, action";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent activities
     * @param int $limit Number of records to return
     * @return array Recent activities
     */
    public function getRecent($limit = 10) {
        return $this->getLogs([], $limit, 0);
    }
    
    /**
     * Get activities by user
     * @param int $userId User ID
     * @param int $limit Number of records
     * @return array User activities
     */
    public function getUserActivities($userId, $limit = 20) {
        return $this->getLogs(['user_id' => $userId], $limit, 0);
    }
    
    /**
     * Get activities by table
     * @param string $tableName Table name
     * @param int $limit Number of records
     * @return array Table activities
     */
    public function getTableActivities($tableName, $limit = 50) {
        return $this->getLogs(['table_name' => $tableName], $limit, 0);
    }
}
?>