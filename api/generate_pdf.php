<?php
require_once 'tcpdf/tcpdf.php';

class OrcamentoPDF extends TCPDF {

    public function Header() {
        // Logo da N.D Connect
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(30, 58, 138); // Azul escuro da N.D Connect

        // Círculo com N.D
        $this->SetFillColor(30, 58, 138);
        $this->Circle(25, 15, 8, 0, 360, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(20, 12);
        $this->Cell(10, 6, 'N.D', 0, 0, 'C');

        // Tag CONNECT
        $this->SetFillColor(30, 58, 138);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(40, 10);
        $this->Cell(30, 10, 'CONNECT', 0, 0, 'C', true);

        // Linha separadora
        $this->SetDrawColor(249, 115, 22); // Laranja da N.D Connect
        $this->Line(15, 25, 195, 25);

        $this->Ln(15);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'C');
    }
}

function gerarPDFOrcamento($orcamento) {
    $pdf = new OrcamentoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurações do documento
    $pdf->SetCreator('N.D Connect');
    $pdf->SetAuthor('N.D Connect');
    $pdf->SetTitle('Orçamento - ' . $orcamento['numero_orcamento']);
    $pdf->SetSubject('Orçamento de Equipamentos para Eventos');

    // Configurações da página
    $pdf->SetMargins(15, 30, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 25);

    $pdf->AddPage();

    // Título do orçamento
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 10, 'ORÇAMENTO', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(249, 115, 22);
    $pdf->Cell(0, 8, 'Nº ' . $orcamento['numero_orcamento'], 0, 1, 'C');

    $pdf->Ln(10);

    // Dados do cliente
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 8, 'DADOS DO CLIENTE', 0, 1);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, 'Nome: ' . $orcamento['cliente_nome'], 0, 1);

    if (!empty($orcamento['email'])) {
        $pdf->Cell(0, 6, 'Email: ' . $orcamento['email'], 0, 1);
    }

    if (!empty($orcamento['telefone'])) {
        $pdf->Cell(0, 6, 'Telefone: ' . $orcamento['telefone'], 0, 1);
    }

    if (!empty($orcamento['endereco'])) {
        $pdf->Cell(0, 6, 'Endereço: ' . $orcamento['endereco'], 0, 1);
    }

    if (!empty($orcamento['cpf_cnpj'])) {
        $pdf->Cell(0, 6, 'CPF/CNPJ: ' . $orcamento['cpf_cnpj'], 0, 1);
    }

    $pdf->Ln(5);

    // Data do orçamento
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Data do Orçamento: ' . date('d/m/Y', strtotime($orcamento['data_orcamento'])), 0, 1);
    $pdf->Cell(0, 6, 'Válido até: ' . date('d/m/Y', strtotime($orcamento['data_validade'])), 0, 1);

    $pdf->Ln(10);

    // Tabela de itens
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 8, 'ITENS DO ORÇAMENTO', 0, 1);

    // Cabeçalho da tabela
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(249, 115, 22);
    $pdf->SetTextColor(255, 255, 255);

    $pdf->Cell(80, 8, 'PRODUTO', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'QTD', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'PREÇO UNIT.', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'SUBTOTAL', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'UNID.', 1, 1, 'C', true);

    // Itens da tabela
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(248, 250, 252);

    $fill = false;
    foreach ($orcamento['itens'] as $item) {
        $pdf->Cell(80, 8, $item['produto_nome'], 1, 0, 'L', $fill);
        $pdf->Cell(20, 8, $item['quantidade'], 1, 0, 'C', $fill);
        $pdf->Cell(25, 8, 'R$ ' . number_format($item['preco_unitario'], 2, ',', '.'), 1, 0, 'R', $fill);
        $pdf->Cell(25, 8, 'R$ ' . number_format($item['subtotal'], 2, ',', '.'), 1, 0, 'R', $fill);
        $pdf->Cell(20, 8, $item['unidade'], 1, 1, 'C', $fill);
        $fill = !$fill;
    }

    $pdf->Ln(5);

    // Totais
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Cell(130, 8, 'SUBTOTAL:', 0, 0, 'R');
    $pdf->Cell(30, 8, 'R$ ' . number_format($orcamento['subtotal'], 2, ',', '.'), 0, 1, 'R');

    if ($orcamento['desconto'] > 0) {
        $pdf->Cell(130, 8, 'DESCONTO:', 0, 0, 'R');
        $pdf->Cell(30, 8, '- R$ ' . number_format($orcamento['desconto'], 2, ',', '.'), 0, 1, 'R');
    }

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(130, 10, 'TOTAL:', 0, 0, 'R');
    $pdf->Cell(30, 10, 'R$ ' . number_format($orcamento['total'], 2, ',', '.'), 0, 1, 'R');

    $pdf->Ln(10);

    // Observações
    if (!empty($orcamento['observacoes'])) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(30, 58, 138);
        $pdf->Cell(0, 8, 'OBSERVAÇÕES:', 0, 1);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 6, $orcamento['observacoes'], 0, 'L');
    }

    $pdf->Ln(15);

    // Rodapé com informações da empresa
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 8, 'N.D CONNECT - EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, 'Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED', 0, 1, 'C');
    $pdf->Cell(0, 6, 'Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br', 0, 1, 'C');

    return $pdf;
}

// Endpoint para gerar PDF
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    require_once 'Controllers/OrcamentoController.php';

    $orcamentoController = new OrcamentoController();
    $orcamento = $orcamentoController->getById($_GET['id']);

    if ($orcamento) {
        $pdf = gerarPDFOrcamento($orcamento);

        // Nome do arquivo
        $filename = 'orcamento_' . $orcamento['numero_orcamento'] . '.pdf';

        // Headers para download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Output do PDF
        $pdf->Output($filename, 'D');
    } else {
        http_response_code(404);
        echo 'Orçamento não encontrado';
    }
} else {
    http_response_code(400);
    echo 'ID do orçamento não fornecido';
}
?>
