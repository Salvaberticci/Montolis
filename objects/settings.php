<?php
class Settings {
    private $conn;
    private $table_name = "catalog_settings";

    public $id;
    public $setting_key;
    public $setting_value;
    public $setting_description;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function get($key) {
        $query = "SELECT setting_value FROM " . $this->table_name . " WHERE setting_key = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $key);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : null;
    }

    function set($key, $value) {
        // Check if setting exists
        $query_check = "SELECT id FROM " . $this->table_name . " WHERE setting_key = ?";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(1, $key);
        $stmt_check->execute();

        if($stmt_check->rowCount() > 0) {
            // Update existing setting
            $query = "UPDATE " . $this->table_name . " SET setting_value = :value WHERE setting_key = :key";
        } else {
            // Insert new setting
            $query = "INSERT INTO " . $this->table_name . " SET setting_key = :key, setting_value = :value";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":value", $value);

        return $stmt->execute();
    }

    function readAll() {
        $query = "SELECT id, setting_key, setting_value, setting_description, updated_at FROM " . $this->table_name . " ORDER BY setting_key ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET setting_value = :value, setting_description = :description WHERE setting_key = :key";
        $stmt = $this->conn->prepare($query);

        $this->setting_key = htmlspecialchars(strip_tags($this->setting_key));
        $this->setting_value = htmlspecialchars(strip_tags($this->setting_value));
        $this->setting_description = htmlspecialchars(strip_tags($this->setting_description));

        $stmt->bindParam(":key", $this->setting_key);
        $stmt->bindParam(":value", $this->setting_value);
        $stmt->bindParam(":description", $this->setting_description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Helper methods for common settings
    function getShowOutOfStock() {
        return $this->get('show_out_of_stock') === '1';
    }

    function getWholesaleMinimum() {
        return (int) $this->get('wholesale_minimum');
    }

    function getCatalogTitle() {
        return $this->get('catalog_title') ?: 'Catálogo de Productos';
    }

    function getCatalogDescription() {
        return $this->get('catalog_description') ?: 'Descubre nuestros productos';
    }


    function getProductsPerPage() {
        return (int) $this->get('products_per_page') ?: 12;
    }

    function getEnableProductSearch() {
        return $this->get('enable_product_search') === '1';
    }

    function getEnableCategoryFilter() {
        return $this->get('enable_category_filter') === '1';
    }
}
?>