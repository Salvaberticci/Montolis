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

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        // Create sale
        $query = "INSERT INTO " . $this->table_name . " SET product_id=:product_id, quantity_sold=:quantity_sold, sale_price=:sale_price, sale_type=:sale_type";
        $stmt = $this->conn->prepare($query);

        $this->product_id=htmlspecialchars(strip_tags($this->product_id));
        $this->quantity_sold=htmlspecialchars(strip_tags($this->quantity_sold));
        $this->sale_price=htmlspecialchars(strip_tags($this->sale_price));
        $this->sale_type=htmlspecialchars(strip_tags($this->sale_type));

        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity_sold", $this->quantity_sold);
        $stmt->bindParam(":sale_price", $this->sale_price);
        $stmt->bindParam(":sale_type", $this->sale_type);

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
        $query = "SELECT s.id, p.name as product_name, s.quantity_sold, s.sale_price, s.sale_type, s.sale_date FROM " . $this->table_name . " s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.sale_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
