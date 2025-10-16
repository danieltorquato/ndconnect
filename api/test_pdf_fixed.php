<?php
// Teste usando TCPDF corrigido
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste com TCPDF Corrigido</h2>";

// Usar a versão corrigida
require_once 'tcpdf_simple_fixed.php';

if (class_exists('TCPDF')) {
    echo "✅ TCPDF corrigido carregado<br>";
    
    try {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        echo "✅ PDF criado<br>";
        
        $pdf->SetCreator('N.D Connect');
        $pdf->SetTitle('Teste PDF Corrigido');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
        
        echo "✅ Configurações aplicadas<br>";
        
        // Adicionar conteúdo
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'TESTE DE PDF CORRIGIDO', 0, 1, 'C');
        
        $pdf->Ln(10);
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Este PDF foi gerado com TCPDF corrigido.', 0, 1, 'L');
        $pdf->Cell(0, 8, 'Se você conseguir ver este PDF, a correção funcionou!', 0, 1, 'L');
        
        $pdf->Ln(10);
        
        // Testar cores
        $pdf->SetFillColor(12, 43, 89);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'N.D CONNECT - EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C', true);
        
        $pdf->Ln(5);
        
        $pdf->SetFillColor(232, 98, 45);
        $pdf->Cell(0, 8, 'ORÇAMENTO Nº 000001', 0, 1, 'C', true);
        
        $pdf->Ln(10);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'DADOS DO CLIENTE', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 6, 'Nome: Cliente Teste', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Email: teste@exemplo.com', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Telefone: (11) 99999-9999', 0, 1, 'L');
        
        $pdf->Ln(10);
        
        // Tabela de itens
        $pdf->SetFillColor(232, 98, 45);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'ITENS DO ORÇAMENTO', 0, 1, 'C', true);
        
        $pdf->Ln(5);
        
        // Cabeçalho da tabela
        $pdf->SetFillColor(12, 43, 89);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        
        $pdf->Cell(80, 8, 'PRODUTO', 1, 0, 'L', true);
        $pdf->Cell(30, 8, 'QTD', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'PREÇO', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'TOTAL', 1, 1, 'C', true);
        
        // Linhas da tabela
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        
        $produtos = [
            ['Produto Teste 1', 2, 100.00, 200.00],
            ['Produto Teste 2', 1, 150.00, 150.00]
        ];
        
        foreach ($produtos as $index => $produto) {
            $bgColor = ($index % 2 == 0) ? array(255, 255, 255) : array(248, 250, 252);
            $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
            
            $pdf->Cell(80, 8, $produto[0], 1, 0, 'L', true);
            $pdf->Cell(30, 8, $produto[1], 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'R$ ' . number_format($produto[2], 2, ',', '.'), 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'R$ ' . number_format($produto[3], 2, ',', '.'), 1, 1, 'C', true);
        }
        
        $pdf->Ln(10);
        
        // Totais
        $pdf->SetFillColor(248, 250, 252);
        $pdf->Rect(15, $pdf->GetY(), 180, 30, 'F');
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(120, 8, 'SUBTOTAL:', 0, 0, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 8, 'R$ 350,00', 0, 1, 'R');
        
        $pdf->SetDrawColor(232, 98, 45);
        $pdf->Line(15, $pdf->GetY() + 2, 195, $pdf->GetY() + 2);
        
        $pdf->Ln(5);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(232, 98, 45);
        $pdf->Cell(120, 10, 'TOTAL:', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(60, 10, 'R$ 350,00', 0, 1, 'R');
        
        $pdf->Ln(15);
        
        // Footer
        $pdf->SetFillColor(12, 43, 89);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'N.D CONNECT - EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C', true);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED', 0, 1, 'C', true);
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(200, 200, 200);
        $pdf->Cell(0, 4, 'Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br', 0, 1, 'C', true);
        
        echo "✅ Conteúdo adicionado<br>";
        
        // Gerar PDF
        $pdfContent = $pdf->Output('teste_pdf_corrigido.pdf', 'S');
        echo "✅ PDF gerado<br>";
        echo "Tamanho: " . strlen($pdfContent) . " bytes<br>";
        
        // Verificar assinatura
        $pdfSignature = substr($pdfContent, 0, 4);
        if ($pdfSignature === '%PDF') {
            echo "✅ PDF válido (assinatura: %PDF)<br>";
        } else {
            echo "❌ PDF inválido (assinatura: " . bin2hex($pdfSignature) . ")<br>";
        }
        
        // Salvar para análise
        file_put_contents('teste_pdf_corrigido_output.pdf', $pdfContent);
        echo "✅ PDF salvo como teste_pdf_corrigido_output.pdf<br>";
        
        // Testar download
        echo "<h3>Testando Download</h3>";
        
        // Headers corretos
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="teste_pdf_corrigido.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Enviar PDF
        echo $pdfContent;
        
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "<br>";
        echo "Arquivo: " . $e->getFile() . "<br>";
        echo "Linha: " . $e->getLine() . "<br>";
    }
} else {
    echo "❌ TCPDF não carregado<br>";
}
?>
