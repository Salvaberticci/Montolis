<?php
class Movement {
    private $conn;
    private $table_name = "inventory_movements";

    public $id;
    public $product_id;
    public $type;
    public $quantity;
    public $reason;
    public $date;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET product_id=:product_id, type=:type, quantity=:quantity, reason=:reason";
        $stmt = $this->conn->prepare($query);

        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->reason = htmlspecialchars(strip_tags($this->reason));

        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":reason", $this->reason);

        if($stmt->execute()) {
            // Update product quantity
            $this->updateProductQuantity();
            return true;
        }
        return false;
    }

    function read($filters = []) {
        $query = "SELECT m.id, m.product_id, p.name as product_name, m.type, m.quantity, m.reason, m.date FROM " . $this->table_name . " m JOIN products p ON m.product_id = p.id WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE :search OR m.reason LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['type'])) {
            $query .= " AND m.type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['product_id'])) {
            $query .= " AND m.product_id = :product_id";
            $params[':product_id'] = $filters['product_id'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(m.date) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(m.date) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $query .= " ORDER BY m.date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    private function updateProductQuantity() {
        $query = "UPDATE products SET quantity = quantity " . ($this->type == 'entry' ? '+' : '-') . " :quantity WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->execute();
    }
}
?>