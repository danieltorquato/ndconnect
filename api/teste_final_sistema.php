<?php
// Teste final para verificar se tudo está funcionando
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Teste Final - Sistema de PDF</h2>";

// Testar TCPDF
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
}

if (!class_exists("TCPDF") && file_exists("tcpdf_simple.php")) {
    require_once "tcpdf_simple.php";
}

if (class_exists("TCPDF")) {
    echo "✅ TCPDF carregado<br>";
    
    try {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
        $pdf->SetCreator("N.D Connect");
        $pdf->SetTitle("Teste Final");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
        
        $pdf->SetFont("helvetica", "B", 16);
        $pdf->Cell(0, 10, "TESTE FINAL - SISTEMA CORRIGIDO", 0, 1, "C");
        
        $pdf->Ln(10);
        
        $pdf->SetFont("helvetica", "", 12);
        $pdf->Cell(0, 8, "✅ TCPDF funcionando", 0, 1, "L");
        $pdf->Cell(0, 8, "✅ PDF sendo gerado corretamente", 0, 1, "L");
        $pdf->Cell(0, 8, "✅ Sistema pronto para uso", 0, 1, "L");
        
        $pdfContent = $pdf->Output("teste_final.pdf", "S");
        
        if (strlen($pdfContent) > 0) {
            echo "✅ PDF gerado com sucesso (" . strlen($pdfContent) . " bytes)<br>";
            
            // Verificar assinatura
            $signature = substr($pdfContent, 0, 4);
            if ($signature === "%PDF") {
                echo "✅ PDF válido<br>";
            } else {
                echo "❌ PDF inválido (assinatura: " . bin2hex($signature) . ")<br>";
            }
        } else {
            echo "❌ PDF vazio<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ TCPDF não carregado<br>";
}

echo "<h3>Links de Teste:</h3>";
echo "• <a href=\"pdf_real.php?id=1\" target=\"_blank\">Teste pdf_real.php?id=1</a><br>";
echo "• <a href=\"simple_pdf.php?id=1\" target=\"_blank\">Teste simple_pdf.php?id=1</a><br>";

echo "<h3>Próximos Passos:</h3>";
echo "1. Teste os links acima<br>";
echo "2. Se funcionarem, teste no frontend<br>";
echo "3. Se houver problemas, verifique os logs de erro<br>";
?>