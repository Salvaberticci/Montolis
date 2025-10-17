<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once 'config/database.php';
include_once 'objects/product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$product->id = $_GET['id'];

if($product->delete()){
    header("Location: dashboard.php");
}
else{
    echo "<div>Unable to delete product.</div>";
}
?>
