<?php
// Versão corrigida do pdf_real.php que gera PDFs válidos
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desabilitar display de erros para PDF
ini_set('log_errors', 1);

// Headers corretos para PDF
header('Content-Type: application/pdf');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once 'Config/Database.php';

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
    header('Content-Type: text/html');
    die('ID do orçamento não fornecido');
}

$orcamentoId = (int)$_GET['id'];
$orcamento = getOrcamentoData($orcamentoId);

if (!$orcamento) {
    header('Content-Type: text/html');
    die('Orçamento não encontrado');
}

try {
    // Gerar PDF usando uma implementação simples mas funcional
    $pdfContent = generateSimplePDF($orcamento, $orcamentoId);

    // Definir nome do arquivo
    $filename = 'orcamento_' . strtolower(explode(' ', $orcamento['cliente_nome'])[0]) . '_' . $orcamentoId . '.pdf';

    // Headers finais
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdfContent));

    // Enviar conteúdo do PDF
    echo $pdfContent;

} catch (Exception $e) {
    error_log('Erro no PDF: ' . $e->getMessage());
    header('Content-Type: text/html');
    die('Erro ao gerar PDF: ' . $e->getMessage());
}

function generateSimplePDF($orcamento, $orcamentoId) {
    // Gerar PDF básico mas funcional
    $pdf = "%PDF-1.4\n";
    $pdf .= "1 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Type /Catalog\n";
    $pdf .= "/Pages 2 0 R\n";
    $pdf .= ">>\n";
    $pdf .= "endobj\n";

    $pdf .= "2 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Type /Pages\n";
    $pdf .= "/Kids [3 0 R]\n";
    $pdf .= "/Count 1\n";
    $pdf .= ">>\n";
    $pdf .= "endobj\n";

    $pdf .= "3 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Type /Page\n";
    $pdf .= "/Parent 2 0 R\n";
    $pdf .= "/MediaBox [0 0 595 842]\n";
    $pdf .= "/Contents 4 0 R\n";
    $pdf .= "/Resources <<\n";
    $pdf .= "/Font <<\n";
    $pdf .= "/F1 5 0 R\n";
    $pdf .= ">>\n";
    $pdf .= ">>\n";
    $pdf .= ">>\n";
    $pdf .= "endobj\n";

    // Fonte
    $pdf .= "5 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Type /Font\n";
    $pdf .= "/Subtype /Type1\n";
    $pdf .= "/BaseFont /Helvetica\n";
    $pdf .= ">>\n";
    $pdf .= "endobj\n";

    // Conteúdo da página
    $content = "BT\n";
    $content .= "/F1 16 Tf\n";
    $content .= "50 800 Td\n";
    $content .= "(N.D CONNECT - EQUIPAMENTOS PARA EVENTOS) Tj\n";
    $content .= "0 -30 Td\n";
    $content .= "/F1 12 Tf\n";
    $content .= "(ORCAMENTO Nº " . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . ") Tj\n";
    $content .= "0 -20 Td\n";
    $content .= "(Cliente: " . $orcamento['cliente_nome'] . ") Tj\n";
    $content .= "0 -15 Td\n";
    $content .= "(Email: " . ($orcamento['email'] ?? '') . ") Tj\n";
    $content .= "0 -15 Td\n";
    $content .= "(Telefone: " . ($orcamento['telefone'] ?? '') . ") Tj\n";
    $content .= "0 -15 Td\n";
    $content .= "(Data: " . date('d/m/Y', strtotime($orcamento['data_orcamento'])) . ") Tj\n";
    $content .= "0 -15 Td\n";
    $content .= "(Valido ate: " . date('d/m/Y', strtotime($orcamento['data_validade'])) . ") Tj\n";
    $content .= "0 -30 Td\n";
    $content .= "/F1 14 Tf\n";
    $content .= "(ITENS DO ORCAMENTO) Tj\n";
    $content .= "0 -20 Td\n";
    $content .= "/F1 10 Tf\n";

    // Itens
    foreach ($orcamento['itens'] as $item) {
        $content .= "(" . $item['produto_nome'] . " - Qtd: " . $item['quantidade'] . " - R$ " . number_format($item['preco_unitario'], 2, ',', '.') . ") Tj\n";
        $content .= "0 -12 Td\n";
    }

    $content .= "0 -20 Td\n";
    $content .= "/F1 12 Tf\n";
    $content .= "(SUBTOTAL: R$ " . number_format($orcamento['subtotal'], 2, ',', '.') . ") Tj\n";

    if ($orcamento['desconto'] > 0) {
        $content .= "0 -15 Td\n";
        $content .= "(DESCONTO: R$ " . number_format($orcamento['desconto'], 2, ',', '.') . ") Tj\n";
    }

    $content .= "0 -15 Td\n";
    $content .= "/F1 16 Tf\n";
    $content .= "(TOTAL: R$ " . number_format($orcamento['total'], 2, ',', '.') . ") Tj\n";

    if (!empty($orcamento['observacoes'])) {
        $content .= "0 -20 Td\n";
        $content .= "/F1 10 Tf\n";
        $content .= "(OBSERVACOES: " . $orcamento['observacoes'] . ") Tj\n";
    }

    $content .= "ET\n";

    $pdf .= "4 0 obj\n";
    $pdf .= "<<\n";
    $pdf .= "/Length " . strlen($content) . "\n";
    $pdf .= ">>\n";
    $pdf .= "stream\n";
    $pdf .= $content;
    $pdf .= "endstream\n";
    $pdf .= "endobj\n";

    $pdf .= "xref\n";
    $pdf .= "0 6\n";
    $pdf .= "0000000000 65535 f \n";
    $pdf .= "0000000009 00000 n \n";
    $pdf .= "0000000058 00000 n \n";
    $pdf .= "0000000115 00000 n \n";
    $pdf .= "0000000204 00000 n \n";
    $pdf .= "0000000300 00000 n \n";
    $pdf .= "trailer\n";
    $pdf .= "<<\n";
    $pdf .= "/Size 6\n";
    $pdf .= "/Root 1 0 R\n";
    $pdf .= ">>\n";
    $pdf .= "startxref\n";
    $pdf .= "400\n";
    $pdf .= "%%EOF\n";

    return $pdf;
}
?>
