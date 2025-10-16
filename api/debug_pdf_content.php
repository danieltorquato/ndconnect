<?php
// Script para debugar o conteúdo do PDF gerado
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug do PDF - Conteúdo Gerado</h2>";

// Simular dados de orçamento para teste
$orcamento = [
    'id' => 1,
    'numero_orcamento' => 1,
    'cliente_nome' => 'Daniel Teste',
    'email' => 'daniel@teste.com',
    'telefone' => '(11) 99999-9999',
    'cpf_cnpj' => '123.456.789-00',
    'endereco' => 'Rua Teste, 123',
    'data_orcamento' => '2025-01-16',
    'data_validade' => '2025-02-16',
    'subtotal' => 1000.00,
    'desconto' => 0.00,
    'total' => 1000.00,
    'observacoes' => 'Teste de orçamento',
    'itens' => [
        [
            'produto_nome' => 'Produto Teste',
            'quantidade' => 1,
            'preco_unitario' => 1000.00,
            'subtotal' => 1000.00,
            'unidade' => 'UN'
        ]
    ]
];

echo "<h3>1. Testando TCPDF Simple Fixed</h3>";

try {
    require_once 'tcpdf_simple_fixed.php';
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('N.D Connect');
    $pdf->SetAuthor('N.D Connect');
    $pdf->SetTitle('Teste PDF');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 0, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage();
    
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'TESTE DE PDF - N.D CONNECT', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Cliente: ' . $orcamento['cliente_nome'], 0, 1, 'L');
    $pdf->Cell(0, 8, 'Total: R$ ' . number_format($orcamento['total'], 2, ',', '.'), 0, 1, 'L');
    
    $pdfContent = $pdf->Output('teste.pdf', 'S');
    
    echo "<p><strong>Tamanho do PDF gerado:</strong> " . strlen($pdfContent) . " bytes</p>";
    echo "<p><strong>Primeiros 200 caracteres:</strong></p>";
    echo "<pre>" . htmlspecialchars(substr($pdfContent, 0, 200)) . "</pre>";
    
    echo "<p><strong>Últimos 200 caracteres:</strong></p>";
    echo "<pre>" . htmlspecialchars(substr($pdfContent, -200)) . "</pre>";
    
    // Verificar se é um PDF válido
    if (strpos($pdfContent, '%PDF') === 0) {
        echo "<p style='color: green;'>✅ PDF válido - começa com %PDF</p>";
    } else {
        echo "<p style='color: red;'>❌ PDF inválido - não começa com %PDF</p>";
    }
    
    if (strpos($pdfContent, '%%EOF') !== false) {
        echo "<p style='color: green;'>✅ PDF válido - termina com %%EOF</p>";
    } else {
        echo "<p style='color: red;'>❌ PDF inválido - não termina com %%EOF</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao gerar PDF: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Testando com TCPDF Real (se disponível)</h3>";

// Verificar se existe o TCPDF real
if (file_exists('vendor/tecnickcom/tcpdf/tcpdf.php')) {
    try {
        require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('N.D Connect');
        $pdf->SetAuthor('N.D Connect');
        $pdf->SetTitle('Teste PDF Real');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'TESTE DE PDF REAL - N.D CONNECT', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Cliente: ' . $orcamento['cliente_nome'], 0, 1, 'L');
        $pdf->Cell(0, 8, 'Total: R$ ' . number_format($orcamento['total'], 2, ',', '.'), 0, 1, 'L');
        
        $pdfContent = $pdf->Output('teste_real.pdf', 'S');
        
        echo "<p><strong>Tamanho do PDF real gerado:</strong> " . strlen($pdfContent) . " bytes</p>";
        echo "<p><strong>Primeiros 200 caracteres:</strong></p>";
        echo "<pre>" . htmlspecialchars(substr($pdfContent, 0, 200)) . "</pre>";
        
        if (strpos($pdfContent, '%PDF') === 0) {
            echo "<p style='color: green;'>✅ PDF real válido - começa com %PDF</p>";
        } else {
            echo "<p style='color: red;'>❌ PDF real inválido - não começa com %PDF</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao gerar PDF real: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ TCPDF real não encontrado em vendor/tecnickcom/tcpdf/tcpdf.php</p>";
}

echo "<h3>3. Verificando arquivos disponíveis</h3>";
echo "<p>Arquivos na pasta api:</p>";
$files = glob('*.php');
foreach ($files as $file) {
    if (strpos($file, 'tcpdf') !== false || strpos($file, 'pdf') !== false) {
        echo "<p>- " . $file . " (" . filesize($file) . " bytes)</p>";
    }
}

echo "<h3>4. Teste de Download Direto</h3>";
echo "<p><a href='pdf_real.php?id=1' target='_blank'>Testar Download PDF Real</a></p>";
echo "<p><a href='teste_pdf_fixed.php' target='_blank'>Testar PDF Fixed</a></p>";
?>
