<?php
class PartialPayment {
    private $conn;
    private $table_name = "partial_payments";
    private $movement_table_name = "inventory_movements";

    public $id;
    public $product_id;
    public $total_amount;
    public $paid_amount;
    public $remaining_amount;
    public $client_name;
    public $client_contact;
    public $date_created;
    public $date_updated;
    public $is_completed;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET product_id=:product_id, total_amount=:total_amount, paid_amount=:paid_amount, remaining_amount=:remaining_amount, client_name=:client_name, client_contact=:client_contact";
        $stmt = $this->conn->prepare($query);

        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->paid_amount = htmlspecialchars(strip_tags($this->paid_amount));
        $this->remaining_amount = htmlspecialchars(strip_tags($this->remaining_amount));
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->client_contact = htmlspecialchars(strip_tags($this->client_contact));

        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":paid_amount", $this->paid_amount);
        $stmt->bindParam(":remaining_amount", $this->remaining_amount);
        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":client_contact", $this->client_contact);

        if($stmt->execute()) {
            // Deduct stock immediately when partial payment is registered
            $this->deductStock();
            return true;
        }
        return false;
    }

    function read() {
        $query = "SELECT pp.id, pp.product_id, p.name as product_name, pp.total_amount, pp.paid_amount, pp.remaining_amount, pp.client_name, pp.client_contact, pp.date_created, pp.date_updated, pp.is_completed FROM " . $this->table_name . " pp JOIN products p ON pp.product_id = p.id ORDER BY pp.date_created DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne() {
        $query = "SELECT pp.id, pp.product_id, p.name as product_name, pp.total_amount, pp.paid_amount, pp.remaining_amount, pp.client_name, pp.client_contact, pp.date_created, pp.date_updated, pp.is_completed FROM " . $this->table_name . " pp JOIN products p ON pp.product_id = p.id WHERE pp.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row["id"];
            $this->product_id = $row["product_id"];
            $this->total_amount = $row["total_amount"];
            $this->paid_amount = $row["paid_amount"];
            $this->remaining_amount = $row["remaining_amount"];
            $this->client_name = $row["client_name"];
            $this->client_contact = $row["client_contact"];
            $this->date_created = $row["date_created"];
            $this->date_updated = $row["date_updated"];
            $this->is_completed = $row["is_completed"];
            return true;
        }
        return false;
    }

    function addPayment($amount) {
        $this->paid_amount += $amount;
        $this->remaining_amount -= $amount;

        if ($this->remaining_amount <= 0) {
            $this->remaining_amount = 0;
            $this->is_completed = true;
        }

        $query = "UPDATE " . $this->table_name . " SET paid_amount=:paid_amount, remaining_amount=:remaining_amount, is_completed=:is_completed, date_updated=CURRENT_TIMESTAMP WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->paid_amount = htmlspecialchars(strip_tags($this->paid_amount));
        $this->remaining_amount = htmlspecialchars(strip_tags($this->remaining_amount));
        $this->is_completed = htmlspecialchars(strip_tags($this->is_completed));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":paid_amount", $this->paid_amount);
        $stmt->bindParam(":remaining_amount", $this->remaining_amount);
        $stmt->bindParam(":is_completed", $this->is_completed);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            if ($this->is_completed) {
                $this->createCompletedExitMovement();
                $this->createSaleRecord();
            }
            return true;
        }
        return false;
    }

    private function deductStock() {
        // Directly update product stock
        $query = "UPDATE products SET quantity = quantity - 1 WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $this->product_id);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    private function createCompletedExitMovement() {
        // Use Movement class to create the exit movement without updating stock (already deducted)
        include_once 'movement.php';
        $movement = new Movement($this->conn);

        $movement->product_id = $this->product_id;
        $movement->type = "exit";
        $movement->quantity = 1; // Assuming one product is sold on credit
        $movement->reason = "Venta a crédito (pago por partes completado)";
        $movement->client_name = $this->client_name;
        $movement->client_contact = $this->client_contact;
        $movement->skip_stock_update = true; // Don't deduct stock again

        if($movement->create()) {
            return true;
        }
        return false;
    }

    private function createSaleRecord() {
        // Use Sale class to create the sale record
        include_once 'sale.php';
        $sale = new Sale($this->conn);

        $sale->product_id = $this->product_id;
        $sale->quantity_sold = 1;
        $sale->sale_price = $this->total_amount;
        $sale->sale_type = 'partial'; // Special type for partial payments
        $sale->payment_type = 'credit';
        $sale->payment_status = 'paid';
        $sale->remaining_balance = 0;

        if($sale->create()) {
            return true;
        }
        return false;
    }

    private function createCreditExitMovement() {
        // Use Movement class to create the exit movement and update inventory
        include_once 'movement.php';
        $movement = new Movement($this->conn);

        $movement->product_id = $this->product_id;
        $movement->type = "exit";
        $movement->quantity = 1; // Assuming one product is sold on credit
        $movement->reason = "Venta a crédito (pago por partes completado)";
        $movement->client_name = $this->client_name;
        $movement->client_contact = $this->client_contact;

        if($movement->create()) {
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
}
?>
