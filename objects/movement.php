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
    public $client_name;
    public $client_contact;
    public $total_price;
    public $skip_stock_update = false; // Flag to skip stock update for partial payments

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET product_id=:product_id, type=:type, quantity=:quantity, reason=:reason, client_name=:client_name, client_contact=:client_contact, total_price=:total_price";
        $stmt = $this->conn->prepare($query);

        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->client_contact = htmlspecialchars(strip_tags($this->client_contact));
        $this->total_price = htmlspecialchars(strip_tags($this->total_price));

        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":reason", $this->reason);
        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":client_contact", $this->client_contact);
        $stmt->bindParam(":total_price", $this->total_price);

        if($stmt->execute()) {
            // Update product quantity only if not skipped
            if (!$this->skip_stock_update) {
                $this->updateProductQuantity();
            }
            error_log("Movement created successfully for product_id: " . $this->product_id . ", type: " . $this->type . ", quantity: " . $this->quantity);
            return true;
        } else {
            error_log("Failed to create movement: " . print_r($stmt->errorInfo(), true));
            return false;
        }
    }

    function read($filters = []) {
        $query = "SELECT m.id, m.product_id, p.name as product_name, m.type, m.quantity, m.reason, m.date, m.client_name, m.client_contact, m.total_price FROM " . $this->table_name . " m JOIN products p ON m.product_id = p.id WHERE 1=1";

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

    function readOne() {
        $query = "SELECT m.id, m.product_id, p.name as product_name, m.type, m.quantity, m.reason, m.client_name, m.client_contact, m.date, m.total_price FROM " . $this->table_name . " m JOIN products p ON m.product_id = p.id WHERE m.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id = $row['id'];
        $this->product_id = $row['product_id'];
        $this->type = $row['type'];
        $this->quantity = $row['quantity'];
        $this->reason = $row['reason'];
        $this->client_name = $row['client_name'];
        $this->client_contact = $row['client_contact'];
        $this->total_price = $row['total_price'];
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET product_id=:product_id, type=:type, quantity=:quantity, reason=:reason, client_name=:client_name, client_contact=:client_contact, total_price=:total_price WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->client_contact = htmlspecialchars(strip_tags($this->client_contact));
        $this->total_price = htmlspecialchars(strip_tags($this->total_price));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":reason", $this->reason);
        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":client_contact", $this->client_contact);
        $stmt->bindParam(":total_price", $this->total_price);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    private function updateProductQuantity() {
        $query = "UPDATE products SET quantity = quantity " . ($this->type == 'entry' ? '+' : '-') . " :quantity WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":product_id", $this->product_id);
        if($stmt->execute()) {
            error_log("Product quantity updated for product_id: " . $this->product_id . ", type: " . $this->type . ", quantity: " . $this->quantity);
        } else {
            error_log("Failed to update product quantity: " . print_r($stmt->errorInfo(), true));
        }
    }
}
?>