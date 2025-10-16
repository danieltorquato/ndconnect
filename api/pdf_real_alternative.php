<?php
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
?>