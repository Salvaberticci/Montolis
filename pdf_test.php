<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/libs/TCPDF-main/tcpdf.php');

try {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Test');
    $pdf->SetTitle('TCPDF Test');
    $pdf->SetSubject('Test');

    // Add a page
    $pdf->AddPage();

    // Set some content to print
    $html = '<h1>Hello World!</h1><p>This is a test of TCPDF on the server.</p>';

    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    // Close and output PDF document
    ob_end_clean();
    $pdf->Output('test.pdf', 'I');

} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
