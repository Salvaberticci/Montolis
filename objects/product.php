<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $description;
    public $quantity;
    public $image;
    public $product_cost;
    public $sale_price;
    public $third_party_sale_price;
    public $third_party_seller_percentage;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, name, description, quantity, product_cost, sale_price, third_party_sale_price, third_party_seller_percentage, image FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, description=:description, quantity=:quantity, product_cost=:product_cost, sale_price=:sale_price, third_party_sale_price=:third_party_sale_price, third_party_seller_percentage=:third_party_seller_percentage, image=:image";
        $stmt = $this->conn->prepare($query);

        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->quantity=htmlspecialchars(strip_tags($this->quantity));
        $this->product_cost=htmlspecialchars(strip_tags($this->product_cost));
        $this->sale_price=htmlspecialchars(strip_tags($this->sale_price));
        $this->third_party_sale_price=htmlspecialchars(strip_tags($this->third_party_sale_price));
        $this->third_party_seller_percentage=htmlspecialchars(strip_tags($this->third_party_seller_percentage));
        $this->image=htmlspecialchars(strip_tags($this->image));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":product_cost", $this->product_cost);
        $stmt->bindParam(":sale_price", $this->sale_price);
        $stmt->bindParam(":third_party_sale_price", $this->third_party_sale_price);
        $stmt->bindParam(":third_party_seller_percentage", $this->third_party_seller_percentage);
        $stmt->bindParam(":image", $this->image);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function readOne(){
        $query = "SELECT name, description, quantity, product_cost, sale_price, third_party_sale_price, third_party_seller_percentage, image FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->quantity = $row['quantity'];
        $this->product_cost = $row['product_cost'];
        $this->sale_price = $row['sale_price'];
        $this->third_party_sale_price = $row['third_party_sale_price'];
        $this->third_party_seller_percentage = $row['third_party_seller_percentage'];
        $this->image = $row['image'];
    }

    function update(){
        // Add `image = :image` to the query
        $query = "UPDATE " . $this->table_name . " SET name = :name, description = :description, quantity = :quantity, product_cost = :product_cost, sale_price = :sale_price, third_party_sale_price = :third_party_sale_price, third_party_seller_percentage = :third_party_seller_percentage, image = :image WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->quantity=htmlspecialchars(strip_tags($this->quantity));
        $this->product_cost=htmlspecialchars(strip_tags($this->product_cost));
        $this->sale_price=htmlspecialchars(strip_tags($this->sale_price));
        $this->third_party_sale_price=htmlspecialchars(strip_tags($this->third_party_sale_price));
        $this->third_party_seller_percentage=htmlspecialchars(strip_tags($this->third_party_seller_percentage));
        $this->image=htmlspecialchars(strip_tags($this->image)); // Sanitize image
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':product_cost', $this->product_cost);
        $stmt->bindParam(':sale_price', $this->sale_price);
        $stmt->bindParam(':third_party_sale_price', $this->third_party_sale_price);
        $stmt->bindParam(':third_party_seller_percentage', $this->third_party_seller_percentage);
        $stmt->bindParam(':image', $this->image); // Bind image
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    function search($keywords){
        $query = "SELECT id, name, description, quantity, product_cost, sale_price, third_party_sale_price, third_party_seller_percentage, image FROM " . $this->table_name . " WHERE name LIKE ? OR description LIKE ? ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);

        $keywords=htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);

        $stmt->execute();
        return $stmt;
    }
}
?>