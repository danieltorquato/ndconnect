<?php
require_once 'Config/Database.php';

// Incluir TCPDF
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    die('TCPDF não está instalado. Execute: composer require tecnickcom/tcpdf');
}

// Função para obter dados do orçamento
function getOrcamentoData($id) {
    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT o.*, c.nome as cliente_nome, c.email, c.telefone, c.endereco, c.cpf_cnpj
              FROM orcamentos o
              LEFT JOIN clientes c ON o.cliente_id = c.id
              WHERE o.id = :id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orcamento) {
        return null;
    }

    // Buscar itens do orçamento
    $queryItens = "SELECT oi.*, p.nome as produto_nome, p.descricao, p.unidade
                   FROM orcamento_itens oi
                   LEFT JOIN produtos p ON oi.produto_id = p.id
                   WHERE oi.orcamento_id = :orcamento_id";

    $stmtItens = $conn->prepare($queryItens);
    $stmtItens->bindParam(':orcamento_id', $id);
    $stmtItens->execute();

    $orcamento['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    return $orcamento;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID do orçamento não fornecido');
}

$orcamentoId = (int)$_GET['id'];
$orcamento = getOrcamentoData($orcamentoId);

if (!$orcamento) {
    die('Orçamento não encontrado');
}

// Criar novo documento PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar informações do documento
$pdf->SetCreator('N.D Connect');
$pdf->SetAuthor('N.D Connect');
$pdf->SetTitle('Orçamento N.D Connect - ' . $orcamentoId);
$pdf->SetSubject('Orçamento de Equipamentos para Eventos');
$pdf->SetKeywords('orçamento, eventos, equipamentos, N.D Connect');

// Configurar margens
$pdf->SetMargins(15, 20, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Configurar quebras de página automáticas
$pdf->SetAutoPageBreak(TRUE, 25);

// Adicionar uma página
$pdf->AddPage();

// Definir fonte
$pdf->SetFont('helvetica', '', 10);

// Cores personalizadas
$pdf->SetTextColor(12, 43, 89); // Azul marinho
$corLaranja = array(232, 98, 45); // Laranja

// Header do orçamento
$pdf->SetFillColor(12, 43, 89);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 15, 'N.D CONNECT', 0, 1, 'C', true);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'ORÇAMENTO N° ' . str_pad($orcamentoId, 6, '0', STR_PAD_LEFT), 0, 1, 'C', true);

// Voltar para cor normal
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(10);

// Dados do cliente
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(12, 43, 89);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 8, 'DADOS DO CLIENTE', 0, 1, 'L', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Ln(2);

// Informações do cliente em duas colunas
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 5, 'Nome:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(70, 5, $orcamento['cliente_nome'], 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(20, 5, 'Email:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $orcamento['email'], 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 5, 'Telefone:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(70, 5, $orcamento['telefone'], 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(20, 5, 'CPF/CNPJ:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $orcamento['cpf_cnpj'], 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 5, 'Endereço:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $orcamento['endereco'], 0, 1, 'L');

$pdf->Ln(5);

// Datas
$dataOrcamento = date('d/m/Y', strtotime($orcamento['data_orcamento']));
$dataValidade = date('d/m/Y', strtotime($orcamento['data_validade']));

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 5, 'Data Orçamento:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(40, 5, $dataOrcamento, 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(25, 5, 'Válido até:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $dataValidade, 0, 1, 'L');

$pdf->Ln(10);

// Itens do orçamento
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(232, 98, 45);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 8, 'ITENS DO ORÇAMENTO', 0, 1, 'L', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(2);

// Cabeçalho da tabela
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetFillColor(232, 98, 45);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(80, 6, 'PRODUTO', 1, 0, 'C', true);
$pdf->Cell(15, 6, 'QTD', 1, 0, 'C', true);
$pdf->Cell(25, 6, 'PREÇO UNIT.', 1, 0, 'C', true);
$pdf->Cell(25, 6, 'SUBTOTAL', 1, 0, 'C', true);
$pdf->Cell(20, 6, 'UNID.', 1, 1, 'C', true);

// Itens
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 8);

foreach ($orcamento['itens'] as $item) {
    $precoUnitario = number_format($item['preco_unitario'], 2, ',', '.');
    $subtotal = number_format($item['subtotal'], 2, ',', '.');

    $pdf->Cell(80, 5, $item['produto_nome'], 1, 0, 'L');
    $pdf->Cell(15, 5, $item['quantidade'], 1, 0, 'C');
    $pdf->Cell(25, 5, 'R$ ' . $precoUnitario, 1, 0, 'R');
    $pdf->Cell(25, 5, 'R$ ' . $subtotal, 1, 0, 'R');
    $pdf->Cell(20, 5, $item['unidade'], 1, 1, 'C');
}

$pdf->Ln(5);

// Totais
$pdf->SetFont('helvetica', 'B', 10);
$subtotal = number_format($orcamento['subtotal'], 2, ',', '.');
$desconto = number_format($orcamento['desconto'], 2, ',', '.');
$total = number_format($orcamento['total'], 2, ',', '.');

$pdf->Cell(120, 6, 'SUBTOTAL:', 0, 0, 'R');
$pdf->Cell(30, 6, 'R$ ' . $subtotal, 0, 1, 'R');

if ($orcamento['desconto'] > 0) {
    $pdf->Cell(120, 6, 'DESCONTO:', 0, 0, 'R');
    $pdf->Cell(30, 6, '- R$ ' . $desconto, 0, 1, 'R');
}

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(12, 43, 89);
$pdf->Cell(120, 8, 'TOTAL:', 0, 0, 'R');
$pdf->Cell(30, 8, 'R$ ' . $total, 0, 1, 'R');

// Observações
if (!empty($orcamento['observacoes'])) {
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, 'OBSERVAÇÕES:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(0, 5, $orcamento['observacoes'], 0, 'L');
}

// Footer
$pdf->SetY(-30);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(12, 43, 89);
$pdf->Cell(0, 5, 'N.D CONNECT - EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 4, 'Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED', 0, 1, 'C');
$pdf->Cell(0, 4, 'Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br', 0, 1, 'C');

// Gerar PDF
$pdf->Output('orcamento_' . $orcamentoId . '.pdf', 'D');
?>
