<?php
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "CREATE TABLE IF NOT EXISTS partial_payments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    paid_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    remaining_amount DECIMAL(10, 2) NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_contact VARCHAR(255) NOT NULL,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_completed BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);";

$stmt = $db->prepare($query);

if ($stmt->execute()) {
    echo "<p>Tabla partial_payments creada exitosamente.</p>";
} else {
    echo "<p>Error al crear la tabla partial_payments: " . json_encode($stmt->errorInfo()) . "</p>";
}
?>
