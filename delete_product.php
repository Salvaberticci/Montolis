<?php
include_once 'config/database.php';
include_once 'objects/product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$product->id = $_GET['id'];

if($product->delete()){
    header("Location: index.php");
}
else{
    echo "<div>Unable to delete product.</div>";
}
?>
