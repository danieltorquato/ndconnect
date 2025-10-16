<?php
// Teste simples de PDF
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Teste de PDF</h2>";

// Verificar se TCPDF funciona
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
}

if (!class_exists("TCPDF") && file_exists("tcpdf_simple.php")) {
    require_once "tcpdf_simple.php";
}

if (class_exists("TCPDF")) {
    echo "✅ TCPDF funcionando<br>";
    
    try {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
        $pdf->SetCreator("N.D Connect");
        $pdf->SetTitle("Teste PDF");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
        
        $pdf->SetFont("helvetica", "B", 16);
        $pdf->Cell(0, 10, "Teste de PDF - N.D Connect", 0, 1, "C");
        
        echo "✅ PDF criado com sucesso<br>";
        
        // Testar logo
        $logoPath = "../src/assets/img/logo.jpeg";
        if (file_exists($logoPath)) {
            try {
                $pdf->Image($logoPath, 75, 20, 60, 0, "JPEG", "", "C", false, 300, "C", false, false, 0, false, false, false);
                echo "✅ Logo adicionado com sucesso<br>";
            } catch (Exception $e) {
                echo "❌ Erro ao adicionar logo: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ Logo não encontrado<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao criar PDF: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ TCPDF não disponível<br>";
}
?>