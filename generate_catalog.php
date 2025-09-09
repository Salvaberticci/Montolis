<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('libs/TCPDF-main/tcpdf.php');
include_once 'config/database.php';
include_once 'objects/product.php';

class CatalogPDF extends TCPDF {
    private $catalogType;

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false, $type='customer') {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->catalogType = $type;
        $this->SetFont('helvetica', '', 10);
    }

    public function Header() {
        $this->LinearGradient(0, 0, $this->getPageWidth(), $this->getPageHeight(), [4, 40, 50], [10, 60, 70]);
        
        $this->Image('images/logo.png', 15, 10, 30, 0, 'PNG', '', 'T', false, 300, '', false, false, 0);
        
        $this->SetFont('helvetica', 'B', 28);
        $this->SetTextColor(255, 255, 255);
        
        $title = 'Catálogo de Productos';
        if ($this->catalogType == 'owner') {
            $title = 'Reporte de Inventario';
        } elseif ($this->catalogType == 'seller') {
            $title = 'Catálogo para Vendedores';
        }
        
        $this->Cell(0, 38, $title, 0, 1, 'R', 0, '', 0, false, 'M', 'B');
        $this->Ln(5);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(156, 163, 175);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, 0, 'L');
        $this->Cell(0, 10, "Montoli's E-commerce", 0, 0, 'R');
    }

    public function ProductCard($product, $y) {
        $image_path = 'uploads/' . $product['image'];
        if (!file_exists($image_path) || empty($product['image'])) {
            $image_path = 'images/placeholder.png';
        }

        // Card background
        $this->SetFillColor(255, 255, 255);
        $this->RoundedRect(15, $y, 180, 80, 5, '1111', 'F');

        // Image with shadow
        $this->Image($image_path, 20, $y + 5, 70, 70, '', '', 'T', true, 300, '', false, false, 0, false, false, false);
        
        // Product Name
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(17, 24, 39);
        $this->SetXY(100, $y + 10);
        $this->Cell(90, 10, $product['name'], 0, 1, 'L');

        // Description
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(107, 114, 128);
        $this->SetXY(100, $y + 22);
        $this->MultiCell(90, 15, $product['description'], 0, 'L');

        // Data section
        $this->SetXY(100, $y + 55);
        $this->SetFont('helvetica', '', 12);

        switch ($this->catalogType) {
            case 'owner':
                $this->SetTextColor(219, 39, 119);
                $this->Cell(15, 10, 'Costo:', 0, 0, 'L');
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(25, 10, '$' . $product['product_cost'], 0, 0, 'L');
                
                $this->SetFont('helvetica', '', 12);
                $this->SetTextColor(22, 163, 74);
                $this->Cell(15, 10, 'Venta:', 0, 0, 'L');
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(25, 10, '$' . $product['third_party_sale_price'], 0, 1, 'L');

                $this->SetXY(100, $y + 65);
                $this->SetFont('helvetica', '', 12);
                $this->SetTextColor(59, 130, 246);
                $this->Cell(15, 10, 'Stock:', 0, 0, 'L');
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(25, 10, $product['quantity'], 0, 1, 'L');
                break;
            case 'seller':
                $this->SetTextColor(22, 163, 74);
                $this->Cell(45, 10, 'Precio Venta:', 0, 0, 'L');
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(45, 10, '$' . $product['third_party_sale_price'], 0, 1, 'L');
                
                $this->SetXY(100, $y + 65);
                $this->SetFont('helvetica', '', 12);
                $this->SetTextColor(190, 24, 93);
                $this->Cell(45, 10, 'Comisión:', 0, 0, 'L');
                $this->SetFont('helvetica', 'B', 12);
                $this->Cell(45, 10, $product['third_party_seller_percentage'] . '%', 0, 1, 'L');
                break;
            case 'customer':
            default:
                $this->SetTextColor(22, 163, 74);
                $this->SetFont('helvetica', 'B', 18);
                $this->Cell(90, 10, '$' . $product['sale_price'], 0, 1, 'L');
                break;
        }
    }
}

$type = isset($_GET['type']) ? $_GET['type'] : 'customer';

$pdf = new CatalogPDF('P', 'mm', 'A4', true, 'UTF-8', false, false, $type);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Montoli\'s');
$pdf->SetTitle('Catálogo de Productos');
$pdf->SetMargins(15, 45, 15);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(FALSE, 25);

$pdf->AddPage();

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);
$stmt = $product->read();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$y_position = 45;
foreach ($products as $p) {
    if ($y_position > 200) { // Check if there is enough space for the next card
        $pdf->AddPage();
        $y_position = 45;
    }
    $pdf->ProductCard($p, $y_position);
    $y_position += 90;
}

$pdf->Output('catalogo_montolis.pdf', 'I');
?>
