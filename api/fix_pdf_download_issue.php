<?php
// Script para corrigir o problema de redirecionamento do download PDF
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Corrigindo Problema de Redirecionamento do Download PDF</h2>";

// 1. Verificar o problema atual
echo "<h3>1. Identificando o Problema</h3>";
echo "O problema é que o pdf_real.php está usando Output('D') que pode causar redirecionamentos.<br>";
echo "Vamos criar uma versão que funcione melhor com o sistema de rotas.<br>";

// 2. Criar versão corrigida do pdf_real.php
echo "<h3>2. Criando Versão Corrigida</h3>";

$pdfRealContent = file_get_contents('pdf_real.php');

// Substituir a linha problemática
$correcao = '// Gerar PDF
// Usar "S" para retornar como string e depois enviar com headers corretos
$pdfContent = $pdf->Output("orcamento_" . strtolower(explode(" ", $orcamento["cliente_nome"])[0]) . "_" . $orcamentoId . ".pdf", "S");

// Headers corretos para PDF
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"orcamento_" . strtolower(explode(" ", $orcamento["cliente_nome"])[0]) . "_" . $orcamentoId . ".pdf\"");
header("Content-Length: " . strlen($pdfContent));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Enviar conteúdo do PDF
echo $pdfContent;';

$pdfRealContent = str_replace(
    '// Gerar PDF
$pdf->Output(\'orcamento_\' . strtolower(explode(\' \', $orcamento[\'cliente_nome\'])[0]) . \'_\' . $orcamentoId . \'.pdf\', \'D\');',
    $correcao,
    $pdfRealContent
);

// Salvar versão corrigida
file_put_contents('pdf_real_fixed_download.php', $pdfRealContent);
echo "✅ pdf_real_fixed_download.php criado<br>";

// 3. Criar arquivo de teste específico
echo "<h3>3. Criando Arquivo de Teste</h3>";

$testContent = '<?php
// Teste específico para download PDF
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Teste de Download PDF</h2>";

// Simular dados de orçamento
$orcamentoTeste = [
    "id" => 1,
    "numero_orcamento" => 1,
    "cliente_nome" => "Cliente Teste",
    "email" => "teste@exemplo.com",
    "telefone" => "11999999999",
    "total" => 1000.00,
    "data_validade" => date("Y-m-d", strtotime("+10 days")),
    "itens" => [
        [
            "produto_nome" => "Produto Teste",
            "quantidade" => 1,
            "preco_unitario" => 1000.00,
            "subtotal" => 1000.00,
            "unidade" => "un"
        ]
    ],
    "subtotal" => 1000.00,
    "desconto" => 0.00,
    "observacoes" => "Orçamento de teste"
];

echo "<h3>Testando Download PDF:</h3>";

// Testar se TCPDF funciona
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
}

if (!class_exists("TCPDF") && file_exists("tcpdf_simple.php")) {
    require_once "tcpdf_simple.php";
}

if (class_exists("TCPDF")) {
    echo "✅ TCPDF disponível<br>";

    try {
        // Criar PDF de teste
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
        $pdf->SetCreator("N.D Connect");
        $pdf->SetTitle("Teste Download PDF");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();

        $pdf->SetFont("helvetica", "B", 16);
        $pdf->Cell(0, 10, "TESTE DE DOWNLOAD PDF", 0, 1, "C");

        $pdf->SetFont("helvetica", "", 12);
        $pdf->Cell(0, 8, "Cliente: " . $orcamentoTeste["cliente_nome"], 0, 1, "L");
        $pdf->Cell(0, 8, "Total: R$ " . number_format($orcamentoTeste["total"], 2, ",", "."), 0, 1, "L");

        // Usar método correto para download
        $pdfContent = $pdf->Output("teste_download.pdf", "S");

        // Headers corretos
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"teste_download.pdf\"");
        header("Content-Length: " . strlen($pdfContent));
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Enviar PDF
        echo $pdfContent;

    } catch (Exception $e) {
        echo "❌ Erro ao criar PDF: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ TCPDF não disponível<br>";
}
?>';

file_put_contents('test_download_pdf.php', $testContent);
echo "✅ test_download_pdf.php criado<br>";

// 4. Criar versão alternativa do pdf_real.php que funciona melhor
echo "<h3>4. Criando Versão Alternativa</h3>";

$pdfRealAlternative = '<?php
// Versão alternativa do pdf_real.php para resolver problema de redirecionamento
error_reporting(E_ALL);
ini_set("display_errors", 0); // Desabilitar display de erros para PDF
ini_set("log_errors", 1);

// Headers corretos para PDF
header("Content-Type: application/pdf");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once "Config/Database.php";

// Incluir TCPDF
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
    if (!class_exists("TCPDF")) {
        require_once "tcpdf_simple.php";
    }
} else {
    require_once "tcpdf_simple.php";
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
    $stmt->bindParam(":id", $id);
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
    $stmtItens->bindParam(":orcamento_id", $id);
    $stmtItens->execute();

    $orcamento["itens"] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    return $orcamento;
}

// Verificar se o ID foi fornecido
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Content-Type: text/html");
    die("ID do orçamento não fornecido");
}

$orcamentoId = (int)$_GET["id"];
$orcamento = getOrcamentoData($orcamentoId);

if (!$orcamento) {
    header("Content-Type: text/html");
    die("Orçamento não encontrado");
}

try {
    // Criar novo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);

    // Configurar informações do documento
    $pdf->SetCreator("N.D Connect");
    $pdf->SetAuthor("N.D Connect");
    $pdf->SetTitle("Orçamento N.D Connect - " . $orcamentoId);
    $pdf->SetSubject("Orçamento de Equipamentos para Eventos");
    $pdf->SetKeywords("orçamento, eventos, equipamentos, N.D Connect");

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

    // Header com logo
    $logoPath = __DIR__ . "/../src/assets/img/logo.jpeg";

    if (file_exists($logoPath) && is_readable($logoPath)) {
        try {
            $pdf->Image($logoPath, 75, 0, 60, 0, "JPEG", "", "C", false, 300, "C", false, false, 0, false, false, false);
            $pdf->Ln(40);
        } catch (Exception $e) {
            // Se falhar, usar texto
            $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont("helvetica", "B", 24);
            $pdf->Cell(0, 20, "N.D CONNECT", 0, 1, "C", true);
        }
    } else {
        // Fallback para texto se logo não encontrada
        $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont("helvetica", "B", 24);
        $pdf->Cell(0, 20, "N.D CONNECT", 0, 1, "C", true);
    }

    // Resto do conteúdo do PDF (simplificado para teste)
    $pdf->SetFont("helvetica", "B", 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, "ORÇAMENTO Nº " . str_pad($orcamento["numero_orcamento"], 6, "0", STR_PAD_LEFT), 0, 1, "C");

    $pdf->Ln(10);

    $pdf->SetFont("helvetica", "", 12);
    $pdf->Cell(0, 8, "Cliente: " . $orcamento["cliente_nome"], 0, 1, "L");
    $pdf->Cell(0, 8, "Total: R$ " . number_format($orcamento["total"], 2, ",", "."), 0, 1, "L");

    // Gerar PDF como string
    $pdfContent = $pdf->Output("orcamento_" . $orcamentoId . ".pdf", "S");

    // Definir nome do arquivo
    $filename = "orcamento_" . strtolower(explode(" ", $orcamento["cliente_nome"])[0]) . "_" . $orcamentoId . ".pdf";

    // Headers finais
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    header("Content-Length: " . strlen($pdfContent));

    // Enviar conteúdo do PDF
    echo $pdfContent;

} catch (Exception $e) {
    error_log("Erro no PDF: " . $e->getMessage());
    header("Content-Type: text/html");
    die("Erro ao gerar PDF: " . $e->getMessage());
}
?>';

file_put_contents('pdf_real_alternative.php', $pdfRealAlternative);
echo "✅ pdf_real_alternative.php criado<br>";

// 5. Atualizar o Routes/api.php para incluir a versão alternativa
echo "<h3>5. Atualizando Rotas</h3>";

$routesContent = file_get_contents('Routes/api.php');

// Adicionar nova rota para a versão alternativa
$novaRota = '
        case \'pdf_real_alternative.php\':
            // Incluir e executar o arquivo pdf_real_alternative.php diretamente
            require_once \'pdf_real_alternative.php\';
            exit; // Parar a execução aqui para não processar como JSON

        case \'test_download_pdf.php\':
            // Incluir e executar o arquivo test_download_pdf.php diretamente
            require_once \'test_download_pdf.php\';
            exit; // Parar a execução aqui para não processar como JSON';

// Inserir antes do default
$routesContent = str_replace(
    '        default:',
    $novaRota . '
        default:',
    $routesContent
);

file_put_contents('Routes/api_updated.php', $routesContent);
echo "✅ Routes/api_updated.php criado<br>";

echo "<h3>6. Resumo das Correções</h3>";
echo "✅ Identificado problema: Output('D') causando redirecionamento<br>";
echo "✅ Criada versão que usa Output('S') + headers corretos<br>";
echo "✅ Criado arquivo de teste para validação<br>";
echo "✅ Criada versão alternativa do pdf_real.php<br>";
echo "✅ Atualizado sistema de rotas<br>";

echo "<h3>7. Arquivos de Teste Criados</h3>";
echo "• <a href='test_download_pdf.php'>test_download_pdf.php</a> - Teste de download direto<br>";
echo "• <a href='pdf_real_alternative.php?id=1'>pdf_real_alternative.php?id=1</a> - Versão alternativa<br>";
echo "• <a href='pdf_real_fixed_download.php?id=1'>pdf_real_fixed_download.php?id=1</a> - Versão corrigida<br>";

echo "<h3>8. Próximos Passos</h3>";
echo "1. Teste primeiro: <a href='test_download_pdf.php'>test_download_pdf.php</a><br>";
echo "2. Se funcionar, teste: <a href='pdf_real_alternative.php?id=1'>pdf_real_alternative.php?id=1</a><br>";
echo "3. Se funcionar, substitua o pdf_real.php original pela versão corrigida<br>";
echo "4. Atualize o Routes/api.php com a versão atualizada<br>";
?>
