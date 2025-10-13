<?php
// Teste simples do TCPDF
require_once 'tcpdf_simple.php';

echo "Testando TCPDF Simple...\n";

// Criar novo documento PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar informações do documento
$pdf->SetCreator('N.D Connect');
$pdf->SetAuthor('N.D Connect');
$pdf->SetTitle('Teste TCPDF Simple');
$pdf->SetSubject('Teste de Funcionalidade');
$pdf->SetKeywords('teste, tcpdf, pdf');

// Desabilitar cabeçalho e rodapé
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Adicionar uma página
$pdf->AddPage();

// Configurar fonte
$pdf->SetFont('helvetica', '', 12);

// Adicionar conteúdo
$pdf->Cell(0, 10, 'Teste do TCPDF Simple', 0, 1, 'C');
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Este é um teste de funcionalidade do TCPDF simplificado.', 0, 1);
$pdf->Cell(0, 10, 'Data: ' . date('d/m/Y H:i:s'), 0, 1);
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Se você está vendo este PDF, o TCPDF Simple está funcionando!', 0, 1);

// Gerar PDF
echo "Gerando PDF...\n";
$pdf->Output('teste_tcpdf.pdf', 'D'); // 'D' para download
echo "PDF gerado com sucesso!\n";
?>
