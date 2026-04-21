<?php
// models/Booking.php
require_once __DIR__ . '/BaseModel.php';

class Booking extends BaseModel {
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    
    public function __construct($db) {
        parent::__construct($db);
    }
    
    /**
     * Get all bookings with client details
     */
    public function getAllWithDetails($limit = null, $offset = null) {
        try {
            $query = "SELECT b.*, 
                             c.bride_name, c.groom_name, c.email as client_email, 
                             c.phone as client_phone, c.address as client_address,
                             c.bride_phone, c.groom_phone, c.bride_email, c.groom_email,
                             c.bride_facebook, c.bride_instagram, c.groom_facebook, c.groom_instagram,
                             u.username as created_by_name
                      FROM " . $this->table . " b
                      LEFT JOIN clients c ON b.client_id = c.id
                      LEFT JOIN users u ON b.created_by = u.id
                      ORDER BY b.created_at DESC";
            
            if ($limit !== null) {
                $query .= " LIMIT :limit";
                if ($offset !== null) {
                    $query .= " OFFSET :offset";
                }
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($limit !== null) {
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetAllWithDetails Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create a new booking
     * @return int|false The ID of the new booking or false on failure
     */
    public function create($data) {
        try {
            // Check if booking_number already exists
            if ($this->bookingNumberExists($data['booking_number'])) {
                // Generate a new unique booking number
                $data['booking_number'] = $this->generateUniqueBookingNumber();
            }
            
            $query = "INSERT INTO " . $this->table . " 
                      (booking_number, client_id, package_id, package_name, package_price, 
                       event_type, event_date, event_time, venue_name, venue_address, 
                       special_notes, status, booking_date, created_by, created_at) 
                      VALUES (:booking_number, :client_id, :package_id, :package_name, :package_price,
                              :event_type, :event_date, :event_time, :venue_name, :venue_address,
                              :special_notes, :status, :booking_date, :created_by, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            $result = $stmt->execute([
                ':booking_number' => $data['booking_number'],
                ':client_id' => $data['client_id'],
                ':package_id' => isset($data['package_id']) && !empty($data['package_id']) ? (int)$data['package_id'] : null,
                ':package_name' => $data['package_name'],
                ':package_price' => floatval($data['package_price']),
                ':event_type' => $data['event_type'] ?? 'Wedding',
                ':event_date' => $data['event_date'],
                ':event_time' => $data['event_time'] ?? null,
                ':venue_name' => $data['venue_name'] ?? null,
                ':venue_address' => $data['venue_address'] ?? null,
                ':special_notes' => $data['special_notes'] ?? null,
                ':status' => $data['status'] ?? 'pending',
                ':booking_date' => $data['booking_date'],
                ':created_by' => $data['created_by'] ?? null
            ]);
            
            if ($result) {
                $booking_id = $this->conn->lastInsertId();
                error_log("Booking created successfully with ID: " . $booking_id);
                return (int)$booking_id;
            }
            
            error_log("Booking creation failed - execute returned false");
            return false;
            
        } catch (PDOException $e) {
            error_log("Booking Create Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if booking number exists
     */
    public function bookingNumberExists($booking_number) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE booking_number = :booking_number";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':booking_number' => $booking_number]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Booking Number Exists Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate unique booking number
     */
    public function generateUniqueBookingNumber() {
        $prefix = 'BK';
        $year = date('Y');
        $month = date('m');
        
        // Get the highest booking number for this month
        $query = "SELECT booking_number FROM " . $this->table . " 
                  WHERE booking_number LIKE :pattern 
                  ORDER BY id DESC LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':pattern' => $pattern]);
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($last) {
            // Extract the sequence number
            $last_number = (int)substr($last['booking_number'], -4);
            $sequence = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return $prefix . $year . $month . $sequence;
    }
    
    /**
     * Get booking by ID with all details
     */
    public function getWithDetails($id) {
        try {
            $query = "SELECT b.*, 
                             c.bride_name, c.groom_name, c.email as client_email, 
                             c.phone as client_phone, c.address as client_address,
                             c.bride_phone, c.groom_phone, c.bride_email, c.groom_email,
                             c.bride_facebook, c.bride_instagram, c.groom_facebook, c.groom_instagram,
                             u.username as created_by_name
                      FROM " . $this->table . " b
                      LEFT JOIN clients c ON b.client_id = c.id
                      LEFT JOIN users u ON b.created_by = u.id
                      WHERE b.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetWithDetails Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get booking by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetById Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get booking by number
     */
    public function getByNumber($booking_number) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE booking_number = :booking_number";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':booking_number' => $booking_number]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetByNumber Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update booking
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET 
                      event_type = :event_type,
                      event_date = :event_date,
                      event_time = :event_time,
                      venue_name = :venue_name,
                      venue_address = :venue_address,
                      special_notes = :special_notes,
                      status = :status,
                      updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute([
                ':event_type' => $data['event_type'] ?? 'Wedding',
                ':event_date' => $data['event_date'],
                ':event_time' => $data['event_time'] ?? null,
                ':venue_name' => $data['venue_name'] ?? null,
                ':venue_address' => $data['venue_address'] ?? null,
                ':special_notes' => $data['special_notes'] ?? null,
                ':status' => $data['status'] ?? 'pending',
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Booking Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete booking
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Booking Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get bookings by client
     */
    public function getByClient($client_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE client_id = :client_id 
                      ORDER BY event_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':client_id' => $client_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetByClient Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get bookings by status
     */
    public function getByStatus($status) {
        try {
            $query = "SELECT b.*, c.bride_name, c.groom_name, c.email 
                      FROM " . $this->table . " b
                      LEFT JOIN clients c ON b.client_id = c.id
                      WHERE b.status = :status 
                      ORDER BY b.event_date ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':status' => $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetByStatus Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming bookings
     */
    public function getUpcoming($limit = 10) {
        try {
            $query = "SELECT b.*, c.bride_name, c.groom_name, c.email 
                      FROM " . $this->table . " b
                      LEFT JOIN clients c ON b.client_id = c.id
                      WHERE b.event_date >= CURDATE() AND b.status != 'cancelled'
                      ORDER BY b.event_date ASC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetUpcoming Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get booking count by status
     */
    public function getCountByStatus() {
        try {
            $query = "SELECT status, COUNT(*) as count FROM " . $this->table . " GROUP BY status";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $counts = [
                'pending' => 0,
                'confirmed' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
            
            foreach ($results as $row) {
                $counts[$row['status']] = $row['count'];
            }
            
            return $counts;
        } catch (PDOException $e) {
            error_log("Booking GetCountByStatus Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search bookings
     */
    public function search($keyword) {
        try {
            $searchTerm = "%{$keyword}%";
            $query = "SELECT b.*, c.bride_name, c.groom_name, c.email 
                      FROM " . $this->table . " b
                      LEFT JOIN clients c ON b.client_id = c.id
                      WHERE b.booking_number LIKE :keyword 
                      OR b.package_name LIKE :keyword
                      OR c.bride_name LIKE :keyword 
                      OR c.groom_name LIKE :keyword
                      OR c.email LIKE :keyword
                      ORDER BY b.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':keyword' => $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking Search Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get bookings for dashboard
     */
    public function getDashboardStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_bookings,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN event_date >= CURDATE() AND status = 'confirmed' THEN 1 ELSE 0 END) as upcoming
                      FROM " . $this->table;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetDashboardStats Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get monthly booking count
     */
    public function getMonthlyStats($year = null) {
        try {
            if (!$year) {
                $year = date('Y');
            }
            
            $query = "SELECT 
                        MONTH(event_date) as month,
                        COUNT(*) as count,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                      FROM " . $this->table . "
                      WHERE YEAR(event_date) = :year
                      GROUP BY MONTH(event_date)
                      ORDER BY month ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':year' => $year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Booking GetMonthlyStats Error: " . $e->getMessage());
            return [];
        }
    }
}
?>