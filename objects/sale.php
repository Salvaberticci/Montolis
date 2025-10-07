<?php
class Sale {
    private $conn;
    private $table_name = "sales";

    public $id;
    public $product_id;
    public $quantity_sold;
    public $sale_price;
    public $sale_type;
    public $sale_date;
    public $payment_type;
    public $payment_status;
    public $remaining_balance;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        // Set default payment values if not provided
        if (!isset($this->payment_type)) $this->payment_type = 'cash';
        if (!isset($this->payment_status)) {
            $this->payment_status = ($this->payment_type == 'cash') ? 'paid' : 'pending';
        }
        if (!isset($this->remaining_balance)) {
            $total_amount = $this->sale_price * $this->quantity_sold;
            $this->remaining_balance = ($this->payment_type == 'cash') ? 0 : $total_amount;
        }

        // Create sale
        $query = "INSERT INTO " . $this->table_name . " SET product_id=:product_id, quantity_sold=:quantity_sold, sale_price=:sale_price, sale_type=:sale_type, payment_type=:payment_type, payment_status=:payment_status, remaining_balance=:remaining_balance";
        $stmt = $this->conn->prepare($query);

        $this->product_id=htmlspecialchars(strip_tags($this->product_id));
        $this->quantity_sold=htmlspecialchars(strip_tags($this->quantity_sold));
        $this->sale_price=htmlspecialchars(strip_tags($this->sale_price));
        $this->sale_type=htmlspecialchars(strip_tags($this->sale_type));
        $this->payment_type=htmlspecialchars(strip_tags($this->payment_type));
        $this->payment_status=htmlspecialchars(strip_tags($this->payment_status));
        $this->remaining_balance=htmlspecialchars(strip_tags($this->remaining_balance));

        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity_sold", $this->quantity_sold);
        $stmt->bindParam(":sale_price", $this->sale_price);
        $stmt->bindParam(":sale_type", $this->sale_type);
        $stmt->bindParam(":payment_type", $this->payment_type);
        $stmt->bindParam(":payment_status", $this->payment_status);
        $stmt->bindParam(":remaining_balance", $this->remaining_balance);

        if(!$stmt->execute()) {
            return false;
        }

        // Update product stock
        $query = "UPDATE products SET quantity = quantity - :quantity_sold WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":quantity_sold", $this->quantity_sold);
        $stmt->bindParam(":product_id", $this->product_id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    function read() {
        $query = "SELECT s.id, p.name as product_name, s.quantity_sold, s.sale_price, s.sale_type, s.sale_date, s.payment_type, s.payment_status, s.remaining_balance FROM " . $this->table_name . " s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.sale_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
