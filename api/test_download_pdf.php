<?php
// Teste específico para download PDF
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Teste de Download PDF</h2>";

// Simular dados de orçamento
$orcamentoTeste = [
    "id" => 1,
    "numero_orcamento" => 1,
    "cliente_nome" => "Cliente Teste",
    "email" => "teste@exemplo.com",
    "telefone" => "11999999999",
    "total" => 1000.00,
    "data_validade" => date("Y-m-d", strtotime("+10 days")),
    "itens" => [
        [
            "produto_nome" => "Produto Teste",
            "quantidade" => 1,
            "preco_unitario" => 1000.00,
            "subtotal" => 1000.00,
            "unidade" => "un"
        ]
    ],
    "subtotal" => 1000.00,
    "desconto" => 0.00,
    "observacoes" => "Orçamento de teste"
];

echo "<h3>Testando Download PDF:</h3>";

// Testar se TCPDF funciona
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
}

if (!class_exists("TCPDF") && file_exists("tcpdf_simple.php")) {
    require_once "tcpdf_simple.php";
}

if (class_exists("TCPDF")) {
    echo "✅ TCPDF disponível<br>";
    
    try {
        // Criar PDF de teste
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
        $pdf->SetCreator("N.D Connect");
        $pdf->SetTitle("Teste Download PDF");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
        
        $pdf->SetFont("helvetica", "B", 16);
        $pdf->Cell(0, 10, "TESTE DE DOWNLOAD PDF", 0, 1, "C");
        
        $pdf->SetFont("helvetica", "", 12);
        $pdf->Cell(0, 8, "Cliente: " . $orcamentoTeste["cliente_nome"], 0, 1, "L");
        $pdf->Cell(0, 8, "Total: R$ " . number_format($orcamentoTeste["total"], 2, ",", "."), 0, 1, "L");
        
        // Usar método correto para download
        $pdfContent = $pdf->Output("teste_download.pdf", "S");
        
        // Headers corretos
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"teste_download.pdf\"");
        header("Content-Length: " . strlen($pdfContent));
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        // Enviar PDF
        echo $pdfContent;
        
    } catch (Exception $e) {
        echo "❌ Erro ao criar PDF: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ TCPDF não disponível<br>";
}
?>