<?php
require_once 'api/Config/Database.php';

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

// Desabilitar cabeçalho e rodapé
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar margens
$pdf->SetMargins(15, 0, 15);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

// Configurar quebras de página automáticas
$pdf->SetAutoPageBreak(TRUE, 25);

// Adicionar uma página
$pdf->AddPage();

// Cores personalizadas N.D Connect
$azulMarinho = array(12, 43, 89);    // #0C2B59
$laranja = array(232, 98, 45);       // #E8622D
$amarelo = array(247, 166, 76);      // #F7A64C
$cinzaClaro = array(248, 250, 252);  // #f8fafc
$cinzaEscuro = array(100, 116, 139); // #64748b

// Header com logo pequeno no topo - APENAS JPEG
$logoPath = __DIR__ . '/src/assets/img/logo.jpeg';

if (file_exists($logoPath)) {
    // Tentar carregar como JPEG
    $image = null;
    if (function_exists('imagecreatefromjpeg')) {
        $image = @imagecreatefromjpeg($logoPath);
    }

    if ($image) {
        // Se conseguiu carregar com GD, processar
        $width = imagesx($image);
        $height = imagesy($image);

        // Salvar como arquivo temporário JPEG
        $tempJpeg = tempnam(sys_get_temp_dir(), 'logo_') . '.jpg';
        imagejpeg($image, $tempJpeg, 90);

        // Limpar memória
        imagedestroy($image);

        // Adicionar logo pequeno (60mm de largura) usando arquivo direto
        $pdf->Image($tempJpeg, 75, 0, 60, 0, 'JPEG', '', 'C', false, 300, 'C', false, false, 0, false, false, false);
        $pdf->Ln(40);

        // Limpar arquivo temporário
        unlink($tempJpeg);
    } else {
        // Se GD não funcionar, tentar arquivo direto
        try {
            $pdf->Image($logoPath, 75, 0, 60, 0, 'JPEG', '', 'C', false, 300, 'C', false, false, 0, false, false, false);
            $pdf->Ln(40);
        } catch (Exception $e) {
            // Se falhar, usar texto
            $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 24);
            $pdf->Cell(0, 20, 'N.D CONNECT', 0, 1, 'C', true);
        }
    }
} else {
    // Fallback para texto se logo não encontrada
    $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->Cell(0, 20, 'N.D CONNECT', 0, 1, 'C', true);
}

// Adicionar espaço antes da faixa azul
$pdf->Ln(10);

$pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', '', 14);
$pdf->Cell(0, 8, 'EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C', true);

// Número do orçamento com destaque
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 12, 'ORÇAMENTO Nº ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT), 0, 1, 'C', true);

$pdf->Ln(0);

// Dados do cliente
$pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'DADOS DO CLIENTE', 0, 1, 'L', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(8);

// Grid de dados do cliente
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
$pdf->Cell(25, 6, 'NOME', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(80, 6, $orcamento['cliente_nome'], 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
$pdf->Cell(20, 6, 'E-MAIL', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, $orcamento['email'] ?? '', 0, 1, 'L');

$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
$pdf->Cell(25, 6, 'TELEFONE', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(80, 6, $orcamento['telefone'] ?? '', 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
$pdf->Cell(20, 6, 'CPF/CNPJ', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, $orcamento['cpf_cnpj'] ?? '', 0, 1, 'L');

if (!empty($orcamento['endereco'])) {
    $pdf->Ln(3);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(25, 6, 'ENDEREÇO', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 6, $orcamento['endereco'], 0, 1, 'L');
}

$pdf->Ln(10);

// Seção de datas
$pdf->SetFillColor($cinzaClaro[0], $cinzaClaro[1], $cinzaClaro[2]);
$pdf->Rect(15, $pdf->GetY(), 180, 15, 'F');

$dataOrcamento = date('d/m/Y', strtotime($orcamento['data_orcamento']));
$dataValidade = date('d/m/Y', strtotime($orcamento['data_validade']));

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
$pdf->Cell(60, 8, 'DATA DO ORÇAMENTO', 0, 0, 'C');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
$pdf->Cell(60, 8, $dataOrcamento, 0, 0, 'C');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
$pdf->Cell(30, 8, 'VÁLIDO ATÉ', 0, 0, 'C');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
$pdf->Cell(0, 8, $dataValidade, 0, 1, 'C');

$pdf->Ln(15);

// Título da seção de itens
$pdf->SetFillColor($laranja[0], $laranja[1], $laranja[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'ITENS DO ORÇAMENTO', 0, 1, 'L', true);

$pdf->Ln(5);

// Cabeçalho da tabela
$pdf->SetFillColor($laranja[0], $laranja[1], $laranja[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 10);

$pdf->Cell(80, 8, 'PRODUTO', 1, 0, 'L', true);
$pdf->Cell(20, 8, 'QTD', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'PREÇO UNIT.', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'SUBTOTAL', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'UNID.', 1, 1, 'C', true);

// Itens da tabela
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 9);

foreach ($orcamento['itens'] as $index => $item) {
    $bgColor = ($index % 2 == 0) ? array(255, 255, 255) : array(248, 250, 252);
    $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);

    // Nome do produto
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->Cell(80, 8, $item['produto_nome'], 1, 0, 'L', true);

    // Quantidade
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(20, 8, $item['quantidade'], 1, 0, 'C', true);

    // Preço unitário
    $pdf->SetTextColor(5, 150, 105);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(30, 8, 'R$ ' . number_format($item['preco_unitario'], 2, ',', '.'), 1, 0, 'C', true);

    // Subtotal
    $pdf->Cell(30, 8, 'R$ ' . number_format($item['subtotal'], 2, ',', '.'), 1, 0, 'C', true);

    // Unidade
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(20, 8, $item['unidade'], 1, 1, 'C', true);
}

$pdf->Ln(10);

// Seção de totais
$pdf->SetFillColor($cinzaClaro[0], $cinzaClaro[1], $cinzaClaro[2]);
$pdf->Rect(15, $pdf->GetY(), 180, 40, 'F');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
$pdf->Cell(120, 8, 'SUBTOTAL:', 0, 0, 'R');
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(60, 8, 'R$ ' . number_format($orcamento['subtotal'], 2, ',', '.'), 0, 1, 'R');

if ($orcamento['desconto'] > 0) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(120, 8, 'DESCONTO:', 0, 0, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 8, '- R$ ' . number_format($orcamento['desconto'], 2, ',', '.'), 0, 1, 'R');
}

// Linha separadora
$pdf->SetDrawColor($laranja[0], $laranja[1], $laranja[2]);
$pdf->Line(15, $pdf->GetY() + 2, 195, $pdf->GetY() + 2);

$pdf->Ln(5);

// Total final
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor($laranja[0], $laranja[1], $laranja[2]);
$pdf->Cell(120, 10, 'TOTAL:', 0, 0, 'R');
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(60, 10, 'R$ ' . number_format($orcamento['total'], 2, ',', '.'), 0, 1, 'R');

$pdf->Ln(15);

// Observações (se houver)
if (!empty($orcamento['observacoes'])) {
    $pdf->SetFillColor(254, 243, 199); // Amarelo claro
    $pdf->SetDrawColor($amarelo[0], $amarelo[1], $amarelo[2]);
    $pdf->Rect(15, $pdf->GetY(), 180, 25, 'FD');

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->Cell(0, 6, 'OBSERVAÇÕES', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(146, 64, 14); // Marrom escuro
    $pdf->MultiCell(170, 5, $orcamento['observacoes'], 0, 'L');

    $pdf->Ln(10);
}

// Footer
$pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'N.D CONNECT - EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C', true);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED', 0, 1, 'C', true);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(200, 200, 200);
$pdf->Cell(0, 4, 'Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br', 0, 1, 'C', true);

// Gerar PDF
$pdf->Output('orcamento_' . strtolower(explode(' ', $orcamento['cliente_nome'])[0]) . '_' . $orcamentoId . '.pdf', 'D');
?>
