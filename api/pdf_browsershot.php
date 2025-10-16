<?php
// Gerador de PDF usando Browsershot
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se o Browsershot está disponível
if (!class_exists('Spatie\Browsershot\Browsershot')) {
    // Tentar carregar via autoload do Composer
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    } else {
        die('Browsershot não encontrado. Execute: composer install');
    }
}

use Spatie\Browsershot\Browsershot;

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

try {
    // Verificar se o ID foi fornecido
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die('ID do orçamento não fornecido');
    }

    $orcamentoId = (int)$_GET['id'];
    $orcamento = getOrcamentoData($orcamentoId);

    if (!$orcamento) {
        die('Orçamento não encontrado');
    }

    // URL do template HTML
    $baseUrl = 'https://ndconnect.torquatoit.com.br/api/';
    $templateUrl = $baseUrl . 'orcamento_template.html?id=' . $orcamentoId;

    // Configurar Browsershot
    $browsershot = Browsershot::url($templateUrl)
        ->waitUntilNetworkIdle()
        ->dismissDialogs()
        ->format('A4')
        ->margins(10, 10, 10, 10)
        ->showBackground()
        ->timeout(60);

    // Gerar PDF
    $pdfContent = $browsershot->pdf();

    // Definir nome do arquivo
    $filename = 'orcamento_' . strtolower(explode(' ', $orcamento['cliente_nome'])[0]) . '_' . $orcamentoId . '.pdf';

    // Headers para download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Enviar conteúdo do PDF
    echo $pdfContent;

} catch (Exception $e) {
    error_log('Erro no PDF Browsershot: ' . $e->getMessage());
    header('Content-Type: text/html');
    die('Erro ao gerar PDF: ' . $e->getMessage());
}
?>
