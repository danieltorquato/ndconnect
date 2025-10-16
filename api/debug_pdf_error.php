<?php
// Debug específico para identificar erros no PDF
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug de Erro no PDF</h2>";

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "❌ ID não fornecido. Use: ?id=1<br>";
    exit;
}

$orcamentoId = (int)$_GET['id'];
echo "✅ Testando com ID: $orcamentoId<br>";

// 1. Verificar se o TCPDF está funcionando
echo "<h3>1. Verificando TCPDF</h3>";

if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

if (!class_exists('TCPDF') && file_exists('tcpdf_simple.php')) {
    require_once 'tcpdf_simple.php';
}

if (class_exists('TCPDF')) {
    echo "✅ TCPDF disponível<br>";
} else {
    echo "❌ TCPDF não disponível<br>";
    exit;
}

// 2. Verificar dados do orçamento
echo "<h3>2. Verificando Dados do Orçamento</h3>";

require_once 'Config/Database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        echo "❌ Erro na conexão com banco<br>";
        exit;
    }
    
    echo "✅ Conexão com banco OK<br>";
    
    // Buscar orçamento
    $query = "SELECT o.*, c.nome as cliente_nome, c.email, c.telefone, c.endereco, c.cpf_cnpj
              FROM orcamentos o
              LEFT JOIN clientes c ON o.cliente_id = c.id
              WHERE o.id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $orcamentoId);
    $stmt->execute();
    
    $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$orcamento) {
        echo "❌ Orçamento não encontrado<br>";
        exit;
    }
    
    echo "✅ Orçamento encontrado: " . $orcamento['cliente_nome'] . "<br>";
    echo "Número: " . $orcamento['numero_orcamento'] . "<br>";
    echo "Total: R$ " . number_format($orcamento['total'], 2, ',', '.') . "<br>";
    
    // Buscar itens
    $queryItens = "SELECT oi.*, p.nome as produto_nome, p.descricao, p.unidade
                   FROM orcamento_itens oi
                   LEFT JOIN produtos p ON oi.produto_id = p.id
                   WHERE oi.orcamento_id = :orcamento_id";
    
    $stmtItens = $conn->prepare($queryItens);
    $stmtItens->bindParam(':orcamento_id', $orcamentoId);
    $stmtItens->execute();
    
    $orcamento['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Itens encontrados: " . count($orcamento['itens']) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao buscar dados: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Testar geração de PDF passo a passo
echo "<h3>3. Testando Geração de PDF</h3>";

try {
    // Criar PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    echo "✅ PDF criado<br>";
    
    // Configurar informações
    $pdf->SetCreator('N.D Connect');
    $pdf->SetAuthor('N.D Connect');
    $pdf->SetTitle('Orçamento N.D Connect - ' . $orcamentoId);
    echo "✅ Informações configuradas<br>";
    
    // Desabilitar cabeçalho e rodapé
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    echo "✅ Cabeçalho/rodapé desabilitados<br>";
    
    // Configurar margens
    $pdf->SetMargins(15, 0, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(TRUE, 25);
    echo "✅ Margens configuradas<br>";
    
    // Adicionar página
    $pdf->AddPage();
    echo "✅ Página adicionada<br>";
    
    // Testar logo
    $logoPath = '../src/assets/img/logo.jpeg';
    if (file_exists($logoPath)) {
        try {
            $pdf->Image($logoPath, 75, 0, 60, 0, 'JPEG', '', 'C', false, 300, 'C', false, false, 0, false, false, false);
            $pdf->Ln(40);
            echo "✅ Logo adicionado<br>";
        } catch (Exception $e) {
            echo "⚠️ Erro no logo: " . $e->getMessage() . "<br>";
            // Usar texto como fallback
            $pdf->SetFillColor(12, 43, 89);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 24);
            $pdf->Cell(0, 20, 'N.D CONNECT', 0, 1, 'C', true);
            echo "✅ Fallback de texto aplicado<br>";
        }
    } else {
        echo "⚠️ Logo não encontrado, usando texto<br>";
        $pdf->SetFillColor(12, 43, 89);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->Cell(0, 20, 'N.D CONNECT', 0, 1, 'C', true);
    }
    
    // Adicionar conteúdo básico
    $pdf->SetFillColor(12, 43, 89);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', '', 14);
    $pdf->Cell(0, 8, 'EQUIPAMENTOS PARA EVENTOS', 0, 1, 'C', true);
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(12, 43, 89);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 12, 'ORÇAMENTO Nº ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT), 0, 1, 'C', true);
    
    $pdf->Ln(10);
    
    // Dados do cliente
    $pdf->SetFillColor(12, 43, 89);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'DADOS DO CLIENTE', 0, 1, 'L', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(8);
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(25, 6, 'NOME', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(80, 6, $orcamento['cliente_nome'], 0, 0, 'L');
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(20, 6, 'E-MAIL', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 6, $orcamento['email'] ?? '', 0, 1, 'L');
    
    $pdf->Ln(5);
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(25, 6, 'TELEFONE', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(80, 6, $orcamento['telefone'] ?? '', 0, 0, 'L');
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(20, 6, 'CPF/CNPJ', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 6, $orcamento['cpf_cnpj'] ?? '', 0, 1, 'L');
    
    $pdf->Ln(10);
    
    // Itens do orçamento
    $pdf->SetFillColor(232, 98, 45);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'ITENS DO ORÇAMENTO', 0, 1, 'L', true);
    
    $pdf->Ln(5);
    
    // Cabeçalho da tabela
    $pdf->SetFillColor(232, 98, 45);
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
        
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(12, 43, 89);
        $pdf->Cell(80, 8, $item['produto_nome'], 1, 0, 'L', true);
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(20, 8, $item['quantidade'], 1, 0, 'C', true);
        
        $pdf->SetTextColor(5, 150, 105);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 8, 'R$ ' . number_format($item['preco_unitario'], 2, ',', '.'), 1, 0, 'C', true);
        
        $pdf->Cell(30, 8, 'R$ ' . number_format($item['subtotal'], 2, ',', '.'), 1, 0, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(20, 8, $item['unidade'], 1, 1, 'C', true);
    }
    
    $pdf->Ln(10);
    
    // Totais
    $pdf->SetFillColor(248, 250, 252);
    $pdf->Rect(15, $pdf->GetY(), 180, 40, 'F');
    
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell(120, 8, 'SUBTOTAL:', 0, 0, 'R');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 8, 'R$ ' . number_format($orcamento['subtotal'], 2, ',', '.'), 0, 1, 'R');
    
    if ($orcamento['desconto'] > 0) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(120, 8, 'DESCONTO:', 0, 0, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, 8, '- R$ ' . number_format($orcamento['desconto'], 2, ',', '.'), 0, 1, 'R');
    }
    
    $pdf->SetDrawColor(232, 98, 45);
    $pdf->Line(15, $pdf->GetY() + 2, 195, $pdf->GetY() + 2);
    
    $pdf->Ln(5);
    
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(232, 98, 45);
    $pdf->Cell(120, 10, 'TOTAL:', 0, 0, 'R');
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(60, 10, 'R$ ' . number_format($orcamento['total'], 2, ',', '.'), 0, 1, 'R');
    
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
    
    echo "✅ Conteúdo do PDF adicionado<br>";
    
    // Gerar PDF como string
    $pdfContent = $pdf->Output('orcamento_debug.pdf', 'S');
    echo "✅ PDF gerado como string<br>";
    echo "Tamanho: " . strlen($pdfContent) . " bytes<br>";
    
    // Verificar se é um PDF válido
    $pdfSignature = substr($pdfContent, 0, 4);
    if ($pdfSignature === '%PDF') {
        echo "✅ PDF válido (assinatura correta)<br>";
    } else {
        echo "❌ PDF inválido (assinatura incorreta): " . bin2hex($pdfSignature) . "<br>";
    }
    
    // Salvar para análise
    file_put_contents('debug_pdf_output.pdf', $pdfContent);
    echo "✅ PDF salvo como debug_pdf_output.pdf para análise<br>";
    
    // Testar download
    echo "<h3>4. Testando Download</h3>";
    
    // Headers corretos
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="orcamento_debug_' . $orcamentoId . '.pdf"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Enviar PDF
    echo $pdfContent;
    
} catch (Exception $e) {
    echo "❌ Erro na geração do PDF: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
