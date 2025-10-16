<?php
// Teste do TCPDF real
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste do TCPDF Real</h2>";

try {
    // Verificar se o arquivo existe
    if (!file_exists('vendor/tecnickcom/tcpdf/tcpdf.php')) {
        throw new Exception('TCPDF não encontrado em vendor/tecnickcom/tcpdf/tcpdf.php');
    }

    require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

    echo "<p>✅ TCPDF carregado com sucesso!</p>";

    // Criar PDF de teste
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurar informações do documento
    $pdf->SetCreator('N.D Connect');
    $pdf->SetAuthor('N.D Connect');
    $pdf->SetTitle('Teste PDF Real');
    $pdf->SetSubject('Teste de Geração de PDF');

    // Desabilitar cabeçalho e rodapé
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Configurar margens
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);

    // Configurar quebras de página automáticas
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Adicionar uma página
    $pdf->AddPage();

    // Adicionar conteúdo
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'TESTE DE PDF REAL - N.D CONNECT', 0, 1, 'C');

    $pdf->Ln(10);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Este é um teste do TCPDF real.', 0, 1, 'L');
    $pdf->Cell(0, 8, 'Data: ' . date('d/m/Y H:i:s'), 0, 1, 'L');
    $pdf->Cell(0, 8, 'Cliente: Daniel Teste', 0, 1, 'L');
    $pdf->Cell(0, 8, 'Total: R$ 1.000,00', 0, 1, 'L');

    // Gerar PDF como string
    $pdfContent = $pdf->Output('teste_real.pdf', 'S');

    echo "<p><strong>Tamanho do PDF:</strong> " . strlen($pdfContent) . " bytes</p>";

    // Verificar assinatura
    if (strpos($pdfContent, '%PDF') === 0) {
        echo "<p style='color: green;'>✅ PDF válido - começa com %PDF</p>";
    } else {
        echo "<p style='color: red;'>❌ PDF inválido - não começa com %PDF</p>";
        echo "<p>Primeiros 50 caracteres: " . htmlspecialchars(substr($pdfContent, 0, 50)) . "</p>";
    }

    if (strpos($pdfContent, '%%EOF') !== false) {
        echo "<p style='color: green;'>✅ PDF válido - termina com %%EOF</p>";
    } else {
        echo "<p style='color: red;'>❌ PDF inválido - não termina com %%EOF</p>";
    }

    // Salvar arquivo de teste
    file_put_contents('teste_pdf_real.pdf', $pdfContent);
    echo "<p><a href='teste_pdf_real.pdf' target='_blank'>Baixar PDF de Teste</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";

    // Listar arquivos na pasta vendor
    echo "<h3>Arquivos em vendor/tecnickcom/tcpdf/</h3>";
    if (is_dir('vendor/tecnickcom/tcpdf/')) {
        $files = scandir('vendor/tecnickcom/tcpdf/');
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "<p>- " . $file . "</p>";
            }
        }
    } else {
        echo "<p>Pasta vendor/tecnickcom/tcpdf/ não encontrada</p>";
    }
}
?>
