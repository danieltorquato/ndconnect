<?php
// Teste do layout completo do PDF com cores e formatação da N.D Connect
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'tcpdf_real.php';

// Dados de exemplo do orçamento
$orcamento = array(
    'numero_orcamento' => 12345,
    'cliente_nome' => 'João Silva Santos',
    'email' => 'joao.silva@email.com',
    'telefone' => '(11) 99999-9999',
    'cpf_cnpj' => '123.456.789-00',
    'endereco' => 'Rua das Flores, 123 - Centro - São Paulo/SP',
    'data_orcamento' => '2024-01-15',
    'data_validade' => '2024-02-15',
    'subtotal' => 2500.00,
    'desconto' => 250.00,
    'total' => 2250.00,
    'observacoes' => 'Orçamento válido por 30 dias. Pagamento à vista com 10% de desconto. Entrega em até 7 dias úteis.',
    'itens' => array(
        array(
            'produto_nome' => 'Palco 3x3m Profissional',
            'quantidade' => 2,
            'preco_unitario' => 800.00,
            'subtotal' => 1600.00,
            'unidade' => 'un'
        ),
        array(
            'produto_nome' => 'Gerador 5kVA Silencioso',
            'quantidade' => 1,
            'preco_unitario' => 500.00,
            'subtotal' => 500.00,
            'unidade' => 'un'
        ),
        array(
            'produto_nome' => 'Sistema de Som 2.1',
            'quantidade' => 1,
            'preco_unitario' => 400.00,
            'subtotal' => 400.00,
            'unidade' => 'un'
        )
    )
);

try {
    // Criar novo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurar informações do documento
    $pdf->SetCreator('N.D Connect');
    $pdf->SetAuthor('N.D Connect');
    $pdf->SetTitle('Orçamento N.D Connect - ' . $orcamento['numero_orcamento']);
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
    $verde = array(5, 150, 105);         // #059669

    // ===== HEADER COM LOGO =====
    // Retângulo azul marinho no topo
    $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->Rect(0, 0, 210, 50, 'F');

    // Logo (se existir) ou texto N.D CONNECT
    $logoPath = '../src/assets/img/logo.jpeg';
    if (file_exists($logoPath) && is_readable($logoPath)) {
        try {
            // Tentar adicionar logo
            $pdf->Image($logoPath, 75, 5, 60, 0, 'JPEG', '', 'C', false, 300, 'C', false, false, 0, false, false, false);
        } catch (Exception $e) {
            // Fallback para texto se logo falhar
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 28);
            $pdf->Cell(0, 20, 'N.D CONNECT', 0, 1, 'C');
        }
    } else {
        // Texto N.D CONNECT se logo não existir
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 28);
        $pdf->Cell(0, 20, 'N.D CONNECT', 0, 1, 'C');
    }

    // Subtítulo "EQUIPAMENTOS PARA EVENTOS"
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', '', 16);
    $pdf->Cell(0, 8, 'EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C');

    // Posicionar para próxima seção
    $pdf->SetY(55);

    // ===== NÚMERO DO ORÇAMENTO =====
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 15, 'ORÇAMENTO Nº ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT), 0, 1, 'C', true);

    $pdf->Ln(5);

    // ===== DADOS DO CLIENTE =====
    // Cabeçalho da seção
    $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 12, 'DADOS DO CLIENTE', 0, 1, 'L', true);

    // Conteúdo dos dados do cliente
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(8);

    // Grid de dados do cliente
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(30, 7, 'NOME:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(80, 7, $orcamento['cliente_nome'], 0, 0, 'L');

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(25, 7, 'E-MAIL:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 7, $orcamento['email'], 0, 1, 'L');

    $pdf->Ln(3);

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(30, 7, 'TELEFONE:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(80, 7, $orcamento['telefone'], 0, 0, 'L');

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(25, 7, 'CPF/CNPJ:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 7, $orcamento['cpf_cnpj'], 0, 1, 'L');

    if (!empty($orcamento['endereco'])) {
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
        $pdf->Cell(30, 7, 'ENDEREÇO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 7, $orcamento['endereco'], 0, 1, 'L');
    }

    $pdf->Ln(10);

    // ===== SEÇÃO DE DATAS =====
    $pdf->SetFillColor($cinzaClaro[0], $cinzaClaro[1], $cinzaClaro[2]);
    $pdf->Rect(15, $pdf->GetY(), 180, 18, 'F');

    $dataOrcamento = date('d/m/Y', strtotime($orcamento['data_orcamento']));
    $dataValidade = date('d/m/Y', strtotime($orcamento['data_validade']));

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(60, 10, 'DATA DO ORÇAMENTO', 0, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->Cell(60, 10, $dataOrcamento, 0, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(30, 10, 'VÁLIDO ATÉ', 0, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->Cell(0, 10, $dataValidade, 0, 1, 'C');

    $pdf->Ln(20);

    // ===== TÍTULO DA SEÇÃO DE ITENS =====
    $pdf->SetFillColor($laranja[0], $laranja[1], $laranja[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 12, 'ITENS DO ORÇAMENTO', 0, 1, 'L', true);

    $pdf->Ln(8);

    // ===== CABEÇALHO DA TABELA =====
    $pdf->SetFillColor($laranja[0], $laranja[1], $laranja[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 11);

    $pdf->Cell(80, 10, 'PRODUTO', 1, 0, 'L', true);
    $pdf->Cell(25, 10, 'QTD', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'PREÇO UNIT.', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'SUBTOTAL', 1, 0, 'C', true);
    $pdf->Cell(15, 10, 'UNID.', 1, 1, 'C', true);

    // ===== ITENS DA TABELA =====
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10);

    foreach ($orcamento['itens'] as $index => $item) {
        // Cor de fundo alternada
        $bgColor = ($index % 2 == 0) ? array(255, 255, 255) : array(248, 250, 252);
        $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);

        // Nome do produto
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
        $pdf->Cell(80, 10, $item['produto_nome'], 1, 0, 'L', true);

        // Quantidade
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(25, 10, $item['quantidade'], 1, 0, 'C', true);

        // Preço unitário (verde)
        $pdf->SetTextColor($verde[0], $verde[1], $verde[2]);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(35, 10, 'R$ ' . number_format($item['preco_unitario'], 2, ',', '.'), 1, 0, 'C', true);

        // Subtotal
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(35, 10, 'R$ ' . number_format($item['subtotal'], 2, ',', '.'), 1, 0, 'C', true);

        // Unidade
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(15, 10, $item['unidade'], 1, 1, 'C', true);
    }

    $pdf->Ln(15);

    // ===== SEÇÃO DE TOTAIS =====
    $pdf->SetFillColor($cinzaClaro[0], $cinzaClaro[1], $cinzaClaro[2]);
    $pdf->Rect(15, $pdf->GetY(), 180, 50, 'F');

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
    $pdf->Cell(120, 10, 'SUBTOTAL:', 0, 0, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(60, 10, 'R$ ' . number_format($orcamento['subtotal'], 2, ',', '.'), 0, 1, 'R');

    if ($orcamento['desconto'] > 0) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor($cinzaEscuro[0], $cinzaEscuro[1], $cinzaEscuro[2]);
        $pdf->Cell(120, 10, 'DESCONTO:', 0, 0, 'R');
        $pdf->SetTextColor(220, 38, 38); // Vermelho para desconto
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(60, 10, '- R$ ' . number_format($orcamento['desconto'], 2, ',', '.'), 0, 1, 'R');
    }

    // Linha separadora
    $pdf->SetDrawColor($laranja[0], $laranja[1], $laranja[2]);
    $pdf->Line(15, $pdf->GetY() + 5, 195, $pdf->GetY() + 5);

    $pdf->Ln(8);

    // Total final
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor($laranja[0], $laranja[1], $laranja[2]);
    $pdf->Cell(120, 12, 'TOTAL:', 0, 0, 'R');
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(60, 12, 'R$ ' . number_format($orcamento['total'], 2, ',', '.'), 0, 1, 'R');

    $pdf->Ln(20);

    // ===== OBSERVAÇÕES =====
    if (!empty($orcamento['observacoes'])) {
        $pdf->SetFillColor(254, 243, 199); // Amarelo claro
        $pdf->SetDrawColor($amarelo[0], $amarelo[1], $amarelo[2]);
        $pdf->Rect(15, $pdf->GetY(), 180, 30, 'FD');

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
        $pdf->Cell(0, 8, 'OBSERVAÇÕES', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'I', 11);
        $pdf->SetTextColor(146, 64, 14); // Marrom escuro
        $pdf->MultiCell(170, 6, $orcamento['observacoes'], 0, 'L');

        $pdf->Ln(15);
    }

    // ===== FOOTER =====
    $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'N.D CONNECT - EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 6, 'Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED', 0, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(200, 200, 200);
    $pdf->Cell(0, 5, 'Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br | Site: www.ndconnect.com.br', 0, 1, 'C', true);

    // Gerar PDF como string
    $pdfContent = $pdf->Output('orcamento_ndconnect_completo.pdf', 'S');

    // Headers finais
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="orcamento_ndconnect_completo.pdf"');
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
