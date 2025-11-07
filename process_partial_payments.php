<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once 'config/database.php';
include_once 'objects/product.php';
include_once 'objects/partial_payment.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$partial_payment = new PartialPayment($db);

$notification = '';
$notification_type = '';

if($_POST) {
    // Debug: Log POST data
    error_log("POST data: " . print_r($_POST, true));

    $movement_type = $_POST['type'];
    $reason = $_POST['reason'] ?? '';
    $client_name = $_POST['client_name'] ?? '';
    $client_contact = $_POST['client_contact'] ?? '';

    if ($movement_type == 'partial_payment') {
        $pp_products = $_POST['pp_products'] ?? [];
        error_log("Partial payment products: " . print_r($pp_products, true));
        if(empty($pp_products)) {
            $notification = 'Por favor agregue al menos un producto para el pago por partes.';
            $notification_type = 'error';
        } else {
            $success_count = 0;
            $error_count = 0;

            foreach($pp_products as $product_data) {
                if(!empty($product_data['product_id'])) {
                    $partial_payment->product_id = $product_data['product_id'];

                    // Get product price for total amount
                    $product->id = $product_data['product_id'];
                    $product->readOne();
                    $partial_payment->total_amount = $product->sale_price;
                    $partial_payment->paid_amount = 0;
                    $partial_payment->remaining_amount = $product->sale_price;
                    $partial_payment->client_name = $_POST['pp_client_name'];
                    $partial_payment->client_contact = $_POST['pp_client_contact'];

                    if ($partial_payment->create()) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }

            if($success_count > 0) {
                $notification = "Se registraron {$success_count} pagos por partes exitosamente.";
                if($error_count > 0) {
                    $notification .= " {$error_count} pagos por partes fallaron.";
                }
                $notification_type = $error_count > 0 ? 'warning' : 'success';
            } else {
                $notification = 'Error al registrar los pagos por partes.';
                $notification_type = 'error';
            }
        }
    }
}

// Redirect back to inventory_movements.php with notification
$_SESSION['notification'] = $notification;
$_SESSION['notification_type'] = $notification_type;
header("Location: inventory_movements.php");
exit();
?>