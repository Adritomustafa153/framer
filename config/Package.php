<?php
// models/Package.php
class Package {
    private $conn;
    private $table = "packages";

    public $id;
    public $package_name;
    public $price;
    public $description;
    public $features;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all packages
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_active = 1 ORDER BY sort_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single package
    public function getById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Create package
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  SET package_name = :name,
                      price = :price,
                      description = :description,
                      features = :features";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->package_name);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":features", $this->features);

        return $stmt->execute();
    }

    // Update package
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET package_name = :name,
                      price = :price,
                      description = :description,
                      features = :features
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->package_name);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":features", $this->features);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete package
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>