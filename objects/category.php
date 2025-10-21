<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $description;
    public $color;
    public $icon;
    public $is_active;
    public $sort_order;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, name, description, color, icon, is_active, sort_order, created_at FROM " . $this->table_name . " ORDER BY sort_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readActive() {
        $query = "SELECT id, name, description, color, icon, is_active, sort_order, created_at FROM " . $this->table_name . " WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, description=:description, color=:color, icon=:icon, is_active=:is_active, sort_order=:sort_order";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->icon = htmlspecialchars(strip_tags($this->icon));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->sort_order = htmlspecialchars(strip_tags($this->sort_order));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":icon", $this->icon);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":sort_order", $this->sort_order);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function readOne() {
        $query = "SELECT name, description, color, icon, is_active, sort_order FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->color = $row['color'];
            $this->icon = $row['icon'];
            $this->is_active = $row['is_active'];
            $this->sort_order = $row['sort_order'];
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET name=:name, description=:description, color=:color, icon=:icon, is_active=:is_active, sort_order=:sort_order WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->icon = htmlspecialchars(strip_tags($this->icon));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->sort_order = htmlspecialchars(strip_tags($this->sort_order));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":icon", $this->icon);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        // Check if category is being used by products
        $query_check = "SELECT COUNT(*) as count FROM products WHERE category = ?";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(1, $this->name);
        $stmt_check->execute();
        $row = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if($row['count'] > 0) {
            return false; // Cannot delete category that's being used
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function getProductCount($category_name) {
        $query = "SELECT COUNT(*) as count FROM products WHERE category = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_name);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    function updateSortOrder() {
        $query = "UPDATE " . $this->table_name . " SET sort_order = :sort_order WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sort_order", $this->sort_order);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
?>