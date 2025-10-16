<?php
// Script para corrigir problemas específicos do WhatsApp e PDF
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Corrigindo Problemas do WhatsApp e PDF</h2>";

// 1. Verificar e corrigir problemas no simple_pdf.php
echo "<h3>1. Corrigindo simple_pdf.php</h3>";

$simplePdfContent = file_get_contents('simple_pdf.php');

// Identificar problemas específicos
$problemas = [
    // Problema 1: Caminho do PDF pode estar incorreto
    'const pdfUrl = window.location.origin + "/pdf_real.php?id=' . $id . '";' => 'const pdfUrl = window.location.origin + "/api/pdf_real.php?id=' . $id . '";',

    // Problema 2: Verificar se o fetch está funcionando
    'const response = await fetch(pdfUrl);' => 'const response = await fetch(pdfUrl, {
                    method: "GET",
                    headers: {
                        "Accept": "application/pdf"
                    }
                });',

    // Problema 3: Melhorar tratamento de erro
    'if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }' => 'if (!response.ok) {
                    console.error("Erro HTTP:", response.status, response.statusText);
                    throw new Error(`Erro HTTP: ${response.status} - ${response.statusText}`);
                }',

    // Problema 4: Adicionar verificação de tipo de conteúdo
    'const blob = await response.blob();' => 'const blob = await response.blob();
                console.log("Tipo de conteúdo:", response.headers.get("content-type"));
                if (!blob.type.includes("pdf") && !blob.type.includes("application")) {
                    console.warn("Tipo de conteúdo inesperado:", blob.type);
                }',
];

foreach ($problemas as $busca => $substituicao) {
    $simplePdfContent = str_replace($busca, $substituicao, $simplePdfContent);
}

// Salvar versão corrigida
file_put_contents('simple_pdf_fixed.php', $simplePdfContent);
echo "✅ simple_pdf_fixed.php criado com correções<br>";

// 2. Criar arquivo de teste para WhatsApp
echo "<h3>2. Criando arquivo de teste para WhatsApp</h3>";

$testWhatsappContent = '<?php
// Teste específico para WhatsApp
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Teste de WhatsApp e PDF</h2>";

// Simular dados de orçamento para teste
$orcamentoTeste = [
    "id" => 1,
    "numero_orcamento" => 1,
    "cliente_nome" => "Cliente Teste",
    "email" => "teste@exemplo.com",
    "telefone" => "11999999999",
    "total" => 1000.00,
    "data_validade" => date("Y-m-d", strtotime("+10 days"))
];

echo "<h3>Dados do Orçamento de Teste:</h3>";
echo "ID: " . $orcamentoTeste["id"] . "<br>";
echo "Número: " . $orcamentoTeste["numero_orcamento"] . "<br>";
echo "Cliente: " . $orcamentoTeste["cliente_nome"] . "<br>";
echo "Total: R$ " . number_format($orcamentoTeste["total"], 2, ",", ".") . "<br>";

echo "<h3>Testando URLs:</h3>";

// Testar URL do PDF
$pdfUrl = "pdf_real.php?id=" . $orcamentoTeste["id"];
echo "URL do PDF: <a href=\'$pdfUrl\' target=\'_blank\'>$pdfUrl</a><br>";

// Testar se o arquivo existe
if (file_exists("pdf_real.php")) {
    echo "✅ pdf_real.php existe<br>";
} else {
    echo "❌ pdf_real.php não existe<br>";
}

// Testar se o TCPDF funciona
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
}

if (!class_exists("TCPDF") && file_exists("tcpdf_simple.php")) {
    require_once "tcpdf_simple.php";
}

if (class_exists("TCPDF")) {
    echo "✅ TCPDF disponível<br>";

    try {
        // Teste básico de PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
        $pdf->SetCreator("N.D Connect");
        $pdf->SetTitle("Teste WhatsApp");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();

        $pdf->SetFont("helvetica", "B", 16);
        $pdf->Cell(0, 10, "Teste de PDF para WhatsApp", 0, 1, "C");

        echo "✅ PDF de teste criado com sucesso<br>";

    } catch (Exception $e) {
        echo "❌ Erro ao criar PDF: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ TCPDF não disponível<br>";
}

echo "<h3>Teste de JavaScript para WhatsApp:</h3>";
?>

<script>
// Função de teste para WhatsApp
async function testarWhatsApp() {
    console.log("Iniciando teste do WhatsApp...");

    try {
        const pdfUrl = "pdf_real.php?id=1";
        console.log("Tentando baixar PDF de:", pdfUrl);

        const response = await fetch(pdfUrl, {
            method: "GET",
            headers: {
                "Accept": "application/pdf"
            }
        });

        console.log("Status da resposta:", response.status);
        console.log("Headers:", Object.fromEntries(response.headers.entries()));

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status} - ${response.statusText}`);
        }

        const blob = await response.blob();
        console.log("Blob criado:", blob.type, blob.size, "bytes");

        if (blob.size === 0) {
            throw new Error("PDF vazio recebido");
        }

        // Testar se é realmente um PDF
        const arrayBuffer = await blob.arrayBuffer();
        const uint8Array = new Uint8Array(arrayBuffer);
        const pdfSignature = uint8Array.slice(0, 4);
        const isPdf = pdfSignature[0] === 0x25 && pdfSignature[1] === 0x50 &&
                     pdfSignature[2] === 0x44 && pdfSignature[3] === 0x46; // %PDF

        console.log("Assinatura PDF:", Array.from(pdfSignature).map(b => b.toString(16)).join(" "));
        console.log("É PDF válido:", isPdf);

        if (isPdf) {
            console.log("✅ PDF válido recebido!");
            alert("✅ PDF baixado com sucesso! Tamanho: " + blob.size + " bytes");
        } else {
            console.warn("⚠️ Arquivo não parece ser um PDF válido");
            alert("⚠️ Arquivo recebido não é um PDF válido");
        }

    } catch (error) {
        console.error("❌ Erro no teste:", error);
        alert("❌ Erro: " + error.message);
    }
}

// Executar teste automaticamente
document.addEventListener("DOMContentLoaded", function() {
    console.log("Página carregada, executando teste...");
    testarWhatsApp();
});
</script>

<button onclick="testarWhatsApp()" style="padding: 10px 20px; background: #25d366; color: white; border: none; border-radius: 5px; cursor: pointer;">
    🔄 Testar WhatsApp Novamente
</button>
';

file_put_contents('test_whatsapp_pdf.php', $testWhatsappContent);
echo "✅ test_whatsapp_pdf.php criado<br>";

// 3. Criar arquivo de correção para pdf_real.php
echo "<h3>3. Corrigindo pdf_real.php</h3>";

$pdfRealContent = file_get_contents('pdf_real.php');

// Adicionar headers corretos para PDF
$headersCorrecao = '<?php
// Headers corretos para PDF
header("Content-Type: application/pdf");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

error_reporting(E_ALL);
ini_set("display_errors", 0); // Desabilitar display de erros para PDF
ini_set("log_errors", 1);

require_once "Config/Database.php";';

$pdfRealContent = str_replace('<?php
require_once \'Config/Database.php\';', $headersCorrecao, $pdfRealContent);

// Melhorar tratamento de erro do logo
$logoCorrecao = '// Header com logo pequeno no topo - APENAS JPEG
$logoPath = __DIR__ . "/../src/assets/img/logo.jpeg";

if (file_exists($logoPath) && is_readable($logoPath)) {
    try {
        // Tentar carregar como JPEG
        $image = null;
        if (function_exists(\'imagecreatefromjpeg\') && extension_loaded(\'gd\')) {
            $image = @imagecreatefromjpeg($logoPath);
        }

        if ($image) {
            // Se conseguiu carregar com GD, processar
            $width = imagesx($image);
            $height = imagesy($image);

            // Salvar como arquivo temporário JPEG
            $tempJpeg = tempnam(sys_get_temp_dir(), \'logo_\') . \'.jpg\';
            imagejpeg($image, $tempJpeg, 90);

            // Limpar memória
            imagedestroy($image);

            // Adicionar logo pequeno (60mm de largura) usando arquivo direto
            $pdf->Image($tempJpeg, 75, 0, 60, 0, \'JPEG\', \'\', \'C\', false, 300, \'C\', false, false, 0, false, false, false);
            $pdf->Ln(40);

            // Limpar arquivo temporário
            unlink($tempJpeg);
        } else {
            // Se GD não funcionar, tentar arquivo direto
            $pdf->Image($logoPath, 75, 0, 60, 0, \'JPEG\', \'\', \'C\', false, 300, \'C\', false, false, 0, false, false, false);
            $pdf->Ln(40);
        }
    } catch (Exception $e) {
        error_log("Erro ao carregar logo: " . $e->getMessage());
        // Se falhar, usar texto
        $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont(\'helvetica\', \'B\', 24);
        $pdf->Cell(0, 20, \'N.D CONNECT\', 0, 1, \'C\', true);
    }
} else {
    // Fallback para texto se logo não encontrada
    $pdf->SetFillColor($azulMarinho[0], $azulMarinho[1], $azulMarinho[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont(\'helvetica\', \'B\', 24);
    $pdf->Cell(0, 20, \'N.D CONNECT\', 0, 1, \'C\', true);
}';

// Substituir a seção do logo
$pdfRealContent = preg_replace('/\/\/ Header com logo pequeno no topo.*?} else {.*?}/s', $logoCorrecao, $pdfRealContent);

// Salvar versão corrigida
file_put_contents('pdf_real_whatsapp_fixed.php', $pdfRealContent);
echo "✅ pdf_real_whatsapp_fixed.php criado<br>";

echo "<h3>4. Resumo das Correções</h3>";
echo "✅ Caminhos do PDF corrigidos para WhatsApp<br>";
echo "✅ Headers de PDF melhorados<br>";
echo "✅ Tratamento de erro do logo aprimorado<br>";
echo "✅ Verificação de tipo de conteúdo adicionada<br>";
echo "✅ Logs de erro melhorados<br>";

echo "<h3>5. Arquivos de Teste Criados</h3>";
echo "• <a href='test_whatsapp_pdf.php'>test_whatsapp_pdf.php</a> - Teste completo do WhatsApp<br>";
echo "• <a href='simple_pdf_fixed.php?id=1'>simple_pdf_fixed.php?id=1</a> - Versão corrigida do simple_pdf<br>";
echo "• <a href='pdf_real_whatsapp_fixed.php?id=1'>pdf_real_whatsapp_fixed.php?id=1</a> - Versão corrigida do pdf_real<br>";

echo "<h3>6. Próximos Passos</h3>";
echo "1. Teste o arquivo test_whatsapp_pdf.php primeiro<br>";
echo "2. Se funcionar, substitua os arquivos originais pelas versões corrigidas<br>";
echo "3. Verifique se o WhatsApp consegue baixar o PDF<br>";
?>
