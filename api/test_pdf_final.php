<?php
// Teste final do PDF com TCPDF real
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'tcpdf_real.php';

try {
    // Criar novo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurar informações do documento
    $pdf->SetCreator('N.D Connect');
    $pdf->SetAuthor('N.D Connect');
    $pdf->SetTitle('Teste PDF Real');
    $pdf->SetSubject('Teste');
    $pdf->SetKeywords('teste');

    // Desabilitar cabeçalho e rodapé
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Configurar margens
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);

    // Adicionar uma página
    $pdf->AddPage();

    // Adicionar texto simples
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'TESTE PDF REAL FUNCIONANDO', 0, 1, 'C');

    $pdf->Ln(10);

    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Este é um teste do TCPDF real', 0, 1, 'L');
    $pdf->Cell(0, 10, 'Se você está vendo este PDF, o TCPDF está funcionando!', 0, 1, 'L');

    $pdf->Ln(10);

    // Teste de cores
    $pdf->SetFillColor(255, 0, 0); // Vermelho
    $pdf->SetTextColor(255, 255, 255); // Branco
    $pdf->Cell(50, 10, 'TEXTO VERMELHO', 1, 1, 'C', true);

    $pdf->SetFillColor(0, 255, 0); // Verde
    $pdf->SetTextColor(0, 0, 0); // Preto
    $pdf->Cell(50, 10, 'TEXTO VERDE', 1, 1, 'C', true);

    // Gerar PDF como string
    $pdfContent = $pdf->Output('teste_real.pdf', 'S');

    // Headers finais
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="teste_real.pdf"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Enviar conteúdo do PDF
    echo $pdfContent;

} catch (Exception $e) {
    error_log('Erro no PDF: ' . $e->getMessage());
    header('Content-Type: text/html');
    die('Erro ao gerar PDF: ' . $e->getMessage());
}
?>
