<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/libs/TCPDF-main/tcpdf.php');
include_once __DIR__ . '/config/database.php';
include_once __DIR__ . '/objects/product.php';

class CatalogPDF extends TCPDF {
    private $catalogType;

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false, $type='customer') {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->catalogType = $type;
    }

    public function Header() {
        // Dark background for the entire page
        $this->SetFillColor(1, 22, 39);
        $this->Rect(0, 0, $this->getPageWidth(), $this->getPageHeight(), 'F');
        
        // Logo
        $this->Image(__DIR__ . '/images/logo.png', 15, 10, 30, 0, 'PNG', '', 'T', false, 300, '', false, false, 0);
        
        // Title
        $this->SetFont('helvetica', 'B', 28);
        $this->SetTextColor(255, 255, 255);
        $title = 'Catálogo de Productos';
        if ($this->catalogType == 'owner') {
            $title = 'Reporte de Inventario';
        } elseif ($this->catalogType == 'seller') {
            $title = 'Catálogo para Vendedores';
        }
        $this->Cell(0, 38, $title, 0, 1, 'R', 0, '', 0, false, 'M', 'B');
        
        // Floating Column Titles
        $this->SetY(50);
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(200, 200, 200);
        
        $this->Cell(50, 8, 'Nombre', 0, 0, 'L');
        $this->Cell(70, 8, 'Descripción', 0, 0, 'L');

        switch ($this->catalogType) {
            case 'owner':
                $this->Cell(20, 8, 'Costo', 0, 0, 'C');
                $this->Cell(20, 8, 'Venta', 0, 0, 'C');
                $this->Cell(20, 8, 'Stock', 0, 0, 'C');
                break;
            case 'seller':
                $this->Cell(30, 8, 'Precio Venta', 0, 0, 'C');
                $this->Cell(30, 8, 'Comisión', 0, 0, 'C');
                break;
            case 'customer':
            default:
                $this->Cell(60, 8, 'Precio', 0, 0, 'C');
                break;
        }
        $this->Ln();
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(156, 163, 175);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, 0, 'L');
        $this->Cell(0, 10, "Montoli's E-commerce", 0, 0, 'R');
    }
}

$type = isset($_GET['type']) ? $_GET['type'] : 'customer';

try {
    $pdf = new CatalogPDF('P', 'mm', 'A4', true, 'UTF-8', false, false, $type);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Montoli\'s');
    $pdf->SetTitle('Catálogo de Productos');
    $pdf->SetMargins(15, 60, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(15);
    $pdf->SetAutoPageBreak(TRUE, 25);

    $pdf->AddPage();

    $database = new Database();
    $db = $database->getConnection();
    $product = new Product($db);
    $stmt = $product->read();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf->setCellPaddings(2, 2, 2, 2);

    foreach ($products as $p) {
        // Calculate height needed for the row
        $row_height = $pdf->getStringHeight(70, $p['description']);
        $name_height = $pdf->getStringHeight(50, $p['name']);
        if ($name_height > $row_height) {
            $row_height = $name_height;
        }
        $row_height += 6; // Add padding

        // Check for page break
        if ($pdf->GetY() + $row_height > $pdf->getPageHeight() - $pdf->getBreakMargin()) {
            $pdf->AddPage();
            $pdf->SetY(60); // Reset Y position after header
        }
        
        $current_y = $pdf->GetY();

        // White card background
        $pdf->SetFillColor(255, 255, 255);
        $pdf->RoundedRect(15, $current_y, 180, $row_height, 2, '1111', 'F');

        // Set text color to black for the content
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 9);

        // Draw cells on top of the white card
        $pdf->MultiCell(50, $row_height, $p['name'], 0, 'L', false, 0, 15, $current_y, true, 0, false, true, $row_height, 'M');
        $pdf->MultiCell(70, $row_height, $p['description'], 0, 'L', false, 0, 65, $current_y, true, 0, false, true, $row_height, 'M');

        switch ($type) {
            case 'owner':
                $pdf->MultiCell(20, $row_height, '$' . $p['product_cost'], 0, 'C', false, 0, 135, $current_y, true, 0, false, true, $row_height, 'M');
                $pdf->MultiCell(20, $row_height, '$' . $p['third_party_sale_price'], 0, 'C', false, 0, 155, $current_y, true, 0, false, true, $row_height, 'M');
                $pdf->MultiCell(20, $row_height, $p['quantity'], 0, 'C', false, 1, 175, $current_y, true, 0, false, true, $row_height, 'M');
                break;
            case 'seller':
                $pdf->MultiCell(30, $row_height, '$' . $p['third_party_sale_price'], 0, 'C', false, 0, 135, $current_y, true, 0, false, true, $row_height, 'M');
                $pdf->MultiCell(30, $row_height, $p['third_party_seller_percentage'] . '%', 0, 'C', false, 1, 165, $current_y, true, 0, false, true, $row_height, 'M');
                break;
            case 'customer':
            default:
                $pdf->MultiCell(60, $row_height, '$' . $p['sale_price'], 0, 'C', false, 1, 135, $current_y, true, 0, false, true, $row_height, 'M');
                break;
        }
        $pdf->Ln(2); // Add a small gap between cards
    }

    @ob_end_clean();

    $pdf->Output('catalogo_montolis.pdf', 'I');

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
