<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once 'config/database.php';
include_once 'objects/product.php';
include_once 'objects/sale.php';

// Include TCPDF
require_once('libs/TCPDF-main/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Montoli\'s Inventory System');
$pdf->SetAuthor('Montoli\'s');
$pdf->SetTitle('Estadísticas y Reporte');
$pdf->SetSubject('Reporte de Estadísticas de Inventario');

// Set default header data
$pdf->SetHeaderData('', 0, 'Montoli\'s - Reporte de Estadísticas', 'Generado el ' . date('d/m/Y H:i'));

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Database connection
$database = new Database();
$db = $database->getConnection();
$sale = new Sale($db);

// Get statistics data
$query_movements = "SELECT
                    SUM(CASE WHEN type = 'entry' THEN quantity ELSE 0 END) as total_entries,
                    SUM(CASE WHEN type = 'exit' THEN quantity ELSE 0 END) as total_exits,
                    COUNT(CASE WHEN type = 'entry' THEN 1 END) as entry_count,
                    COUNT(CASE WHEN type = 'exit' THEN 1 END) as exit_count
                   FROM inventory_movements";
$stmt_movements = $db->prepare($query_movements);
$stmt_movements->execute();
$movement_stats = $stmt_movements->fetch(PDO::FETCH_ASSOC) ?: ['total_entries' => 0, 'total_exits' => 0, 'entry_count' => 0, 'exit_count' => 0];

$query_total_products = "SELECT COUNT(*) as total_products, SUM(quantity) as total_stock FROM products";
$stmt_total_products = $db->prepare($query_total_products);
$stmt_total_products->execute();
$total_products = $stmt_total_products->fetch(PDO::FETCH_ASSOC) ?: ['total_products' => 0, 'total_stock' => 0];

// Calculate total stock value
$query_stock_value = "SELECT SUM(sale_price * quantity) as total_stock_value FROM products";
$stmt_stock_value = $db->prepare($query_stock_value);
$stmt_stock_value->execute();
$stock_data = $stmt_stock_value->fetch(PDO::FETCH_ASSOC) ?: ['total_stock_value' => 0];

// Calculate total investment (cost of all products in inventory)
$query_total_investment = "SELECT SUM(product_cost * quantity) as total_investment FROM products";
$stmt_total_investment = $db->prepare($query_total_investment);
$stmt_total_investment->execute();
$investment_data = $stmt_total_investment->fetch(PDO::FETCH_ASSOC) ?: ['total_investment' => 0];

// Calculate profits from exits (sales) - using product sale_price vs product_cost
$query_profits = "SELECT
                    SUM((p.sale_price - p.product_cost) * m.quantity) as total_profits,
                    SUM(p.product_cost * m.quantity) as total_cost_sold,
                    SUM(p.sale_price * m.quantity) as total_sales_value
                  FROM inventory_movements m
                  LEFT JOIN products p ON m.product_id = p.id
                  WHERE m.type = 'exit'";
$stmt_profits = $db->prepare($query_profits);
$stmt_profits->execute();
$profit_data = $stmt_profits->fetch(PDO::FETCH_ASSOC) ?: ['total_profits' => 0, 'total_cost_sold' => 0, 'total_sales_value' => 0];

// Get most moved products
$query_most_moved = "SELECT p.name,
                    SUM(CASE WHEN m.type = 'entry' THEN m.quantity ELSE 0 END) as total_entries,
                    SUM(CASE WHEN m.type = 'exit' THEN m.quantity ELSE 0 END) as total_exits
                   FROM inventory_movements m
                   LEFT JOIN products p ON m.product_id = p.id
                   GROUP BY m.product_id
                   ORDER BY (SUM(CASE WHEN m.type = 'entry' THEN m.quantity ELSE 0 END) + SUM(CASE WHEN m.type = 'exit' THEN m.quantity ELSE 0 END)) DESC
                   LIMIT 10";
$stmt_most_moved = $db->prepare($query_most_moved);
$stmt_most_moved->execute();
$most_moved_products = $stmt_most_moved->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get movements by month (last 12 months)
$query_movements_by_month = "SELECT DATE_FORMAT(date, '%Y-%m') as month,
                           SUM(CASE WHEN type = 'entry' THEN quantity ELSE 0 END) as entries,
                           SUM(CASE WHEN type = 'exit' THEN quantity ELSE 0 END) as exits
                          FROM inventory_movements
                          WHERE date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                          GROUP BY DATE_FORMAT(date, '%Y-%m')
                          ORDER BY month";
$stmt_movements_by_month = $db->prepare($query_movements_by_month);
$stmt_movements_by_month->execute();
$movements_by_month = $stmt_movements_by_month->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get movement types breakdown
$query_movement_types = "SELECT type, COUNT(*) as count, SUM(quantity) as total_quantity
                        FROM inventory_movements
                        GROUP BY type";
$stmt_movement_types = $db->prepare($query_movement_types);
$stmt_movement_types->execute();
$movement_types_data = $stmt_movement_types->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE ESTADÍSTICAS DE INVENTARIO', 0, 1, 'C');
$pdf->Ln(10);

// Financial Summary section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'RESUMEN FINANCIERO', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

$pdf->Cell(100, 8, 'Dinero Invertido:', 0, 0);
$pdf->Cell(0, 8, '$' . number_format($investment_data['total_investment'] ?? 0, 2), 0, 1);

$pdf->Cell(100, 8, 'Ganancias Totales:', 0, 0);
$pdf->Cell(0, 8, '$' . number_format($profit_data['total_profits'] ?? 0, 2), 0, 1);

$pdf->Cell(100, 8, 'Valor de Ventas:', 0, 0);
$pdf->Cell(0, 8, '$' . number_format($profit_data['total_sales_value'] ?? 0, 2), 0, 1);

$pdf->Cell(100, 8, 'Valor del Stock:', 0, 0);
$pdf->Cell(0, 8, '$' . number_format($stock_data['total_stock_value'] ?? 0, 2), 0, 1);

$pdf->Cell(100, 8, 'Costo de Productos Vendidos:', 0, 0);
$pdf->Cell(0, 8, '$' . number_format($profit_data['total_cost_sold'] ?? 0, 2), 0, 1);

$margin = ($profit_data['total_sales_value'] ?? 0) > 0 ?
    (($profit_data['total_profits'] ?? 0) / ($profit_data['total_sales_value'] ?? 1)) * 100 : 0;
$pdf->Cell(100, 8, 'Margen de Ganancia:', 0, 0);
$pdf->Cell(0, 8, number_format($margin, 1) . '%', 0, 1);

$pdf->Ln(10);

// Movement Summary section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'RESUMEN DE MOVIMIENTOS', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

$pdf->Cell(100, 8, 'Total Movimientos:', 0, 0);
$pdf->Cell(0, 8, number_format(($movement_stats['entry_count'] ?? 0) + ($movement_stats['exit_count'] ?? 0)), 0, 1);

$pdf->Cell(100, 8, 'Total Entradas:', 0, 0);
$pdf->Cell(0, 8, number_format($movement_stats['total_entries'] ?? 0) . ' unidades', 0, 1);

$pdf->Cell(100, 8, 'Total Salidas:', 0, 0);
$pdf->Cell(0, 8, number_format($movement_stats['total_exits'] ?? 0) . ' unidades', 0, 1);

$pdf->Cell(100, 8, 'Productos en Inventario:', 0, 0);
$pdf->Cell(0, 8, number_format($total_products['total_products'] ?? 0), 0, 1);

$pdf->Cell(100, 8, 'Stock Total:', 0, 0);
$pdf->Cell(0, 8, number_format($total_products['total_stock'] ?? 0), 0, 1);

$pdf->Ln(10);

// Movement types section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'TIPOS DE MOVIMIENTO', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

foreach ($movement_types_data as $type) {
    $type_label = '';
    switch ($type['type']) {
        case 'entry': $type_label = 'Entradas'; break;
        case 'exit': $type_label = 'Salidas'; break;
        default: $type_label = $type['type'];
    }

    $pdf->Cell(60, 8, $type_label . ':', 0, 0);
    $pdf->Cell(30, 8, $type['count'] . ' movimientos', 0, 0);
    $pdf->Cell(0, 8, 'Total: ' . number_format($type['total_quantity']) . ' unidades', 0, 1);
}

$pdf->Ln(10);

// Most moved products section
if (!empty($most_moved_products)) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'PRODUCTOS MÁS MOVIDOS', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);

    // Table header
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(60, 8, 'Producto', 1, 0, 'L', true);
    $pdf->Cell(25, 8, 'Entradas', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Salidas', 1, 1, 'C', true);

    // Table data
    foreach ($most_moved_products as $product) {
        $pdf->Cell(60, 8, substr($product['name'], 0, 25) . (strlen($product['name']) > 25 ? '...' : ''), 1, 0, 'L');
        $pdf->Cell(25, 8, number_format($product['total_entries']), 1, 0, 'C');
        $pdf->Cell(25, 8, number_format($product['total_exits']), 1, 1, 'C');
    }

    $pdf->Ln(10);
}

// Movements by month section
if (!empty($movements_by_month)) {
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'MOVIMIENTOS POR MES (ÚLTIMOS 12 MESES)', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);

    // Table header
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(30, 8, 'Mes', 1, 0, 'L', true);
    $pdf->Cell(25, 8, 'Entradas', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Salidas', 1, 1, 'C', true);

    // Table data
    foreach ($movements_by_month as $month_data) {
        $pdf->Cell(30, 8, $month_data['month'], 1, 0, 'L');
        $pdf->Cell(25, 8, number_format($month_data['entries']), 1, 0, 'C');
        $pdf->Cell(25, 8, number_format($month_data['exits']), 1, 1, 'C');
    }
}

// Footer with generation info
$pdf->Ln(20);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Reporte generado por el Sistema de Inventario Montoli\'s el ' . date('d/m/Y H:i:s'), 0, 1, 'C');
$pdf->Cell(0, 10, 'Usuario: ' . (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Usuario'), 0, 1, 'C');

// Output the PDF
$pdf->Output('reporte_estadisticas_' . date('Y-m-d_H-i-s') . '.pdf', 'D');
?>