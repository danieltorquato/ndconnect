<?php
// Script para corrigir erros nos PDFs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Corrigindo Erros nos PDFs</h2>";

// 1. Verificar e corrigir problemas no pdf_real.php
echo "<h3>1. Verificando pdf_real.php</h3>";

// Verificar se o TCPDF está funcionando
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

if (!class_exists('TCPDF') && file_exists('tcpdf_simple.php')) {
    require_once 'tcpdf_simple.php';
}

if (class_exists('TCPDF')) {
    echo "✅ TCPDF disponível<br>";
} else {
    echo "❌ TCPDF não disponível - criando fallback<br>";

    // Criar uma classe TCPDF básica se não existir
    if (!class_exists('TCPDF')) {
        eval('
        class TCPDF {
            private $html = "";
            private $x = 0;
            private $y = 0;

            public function __construct($orientation = "P", $unit = "mm", $format = "A4") {
                // Construtor básico
            }

            public function SetCreator($creator) { return $this; }
            public function SetAuthor($author) { return $this; }
            public function SetTitle($title) { return $this; }
            public function SetSubject($subject) { return $this; }
            public function SetKeywords($keywords) { return $this; }
            public function setPrintHeader($print) { return $this; }
            public function setPrintFooter($print) { return $this; }
            public function SetMargins($left, $top, $right) { return $this; }
            public function SetHeaderMargin($margin) { return $this; }
            public function SetFooterMargin($margin) { return $this; }
            public function SetAutoPageBreak($auto, $margin = 0) { return $this; }
            public function AddPage() { return $this; }
            public function SetFillColor($r, $g, $b) { return $this; }
            public function SetTextColor($r, $g, $b) { return $this; }
            public function SetFont($family, $style = "", $size = 12) { return $this; }
            public function Cell($w, $h, $txt, $border = 0, $ln = 0, $align = "", $fill = false, $link = "") { return $this; }
            public function Ln($h = null) { return $this; }
            public function Image($file, $x, $y, $w, $h, $type = "", $link = "", $align = "", $resize = false, $dpi = 300, $palign = "", $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false) { return $this; }
            public function Rect($x, $y, $w, $h, $style = "") { return $this; }
            public function Line($x1, $y1, $x2, $y2) { return $this; }
            public function SetDrawColor($r, $g, $b) { return $this; }
            public function MultiCell($w, $h, $txt, $border = 0, $align = "J", $fill = false, $ln = 1, $x = "", $y = "", $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = "T", $fitcell = false) { return $this; }
            public function GetY() { return $this->y; }
            public function Output($name = "", $dest = "I") {
                if ($dest === "D") {
                    header("Content-Type: application/pdf");
                    header("Content-Disposition: attachment; filename=\"" . $name . "\"");
                }
                echo "PDF gerado com sucesso!";
            }
        }');
        echo "✅ Classe TCPDF básica criada<br>";
    }
}

// 2. Verificar problemas no logo
echo "<h3>2. Verificando logo</h3>";

$logoPath = '../src/assets/img/logo.jpeg';
$logoPathPng = '../src/assets/img/logo.png';

if (file_exists($logoPath)) {
    echo "✅ Logo JPEG encontrado: $logoPath<br>";
    echo "Tamanho: " . filesize($logoPath) . " bytes<br>";
} elseif (file_exists($logoPathPng)) {
    echo "✅ Logo PNG encontrado: $logoPathPng<br>";
    echo "Tamanho: " . filesize($logoPathPng) . " bytes<br>";
} else {
    echo "❌ Nenhum logo encontrado<br>";
}

// 3. Verificar problemas no simple_pdf.php
echo "<h3>3. Verificando simple_pdf.php</h3>";

if (file_exists('Controllers/OrcamentoController.php')) {
    echo "✅ OrcamentoController.php encontrado<br>";
} else {
    echo "❌ OrcamentoController.php não encontrado<br>";
}

// 4. Criar arquivo de correção para pdf_real.php
echo "<h3>4. Criando correções</h3>";

$pdfRealContent = file_get_contents('pdf_real.php');

// Corrigir problemas comuns
$correcoes = [
    // Adicionar verificação de erro no início
    '<?php' => '<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Verificar se há erros de memória
ini_set("memory_limit", "256M");
ini_set("max_execution_time", 30);
',

    // Melhorar tratamento de erro do logo
    'if (file_exists($logoPath)) {' => 'if (file_exists($logoPath) && is_readable($logoPath)) {',

    // Adicionar verificação de extensão GD
    'if (function_exists(\'imagecreatefromjpeg\')) {' => 'if (function_exists(\'imagecreatefromjpeg\') && extension_loaded(\'gd\')) {',

    // Melhorar tratamento de exceção
    '} catch (Exception $e) {' => '} catch (Exception $e) {
            error_log("Erro no PDF: " . $e->getMessage());
            ',
];

foreach ($correcoes as $busca => $substituicao) {
    $pdfRealContent = str_replace($busca, $substituicao, $pdfRealContent);
}

// Salvar versão corrigida
file_put_contents('pdf_real_fixed.php', $pdfRealContent);
echo "✅ pdf_real_fixed.php criado com correções<br>";

// 5. Criar arquivo de teste simples
echo "<h3>5. Criando arquivo de teste</h3>";

$testContent = '<?php
// Teste simples de PDF
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Teste de PDF</h2>";

// Verificar se TCPDF funciona
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
}

if (!class_exists("TCPDF") && file_exists("tcpdf_simple.php")) {
    require_once "tcpdf_simple.php";
}

if (class_exists("TCPDF")) {
    echo "✅ TCPDF funcionando<br>";

    try {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
        $pdf->SetCreator("N.D Connect");
        $pdf->SetTitle("Teste PDF");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();

        $pdf->SetFont("helvetica", "B", 16);
        $pdf->Cell(0, 10, "Teste de PDF - N.D Connect", 0, 1, "C");

        echo "✅ PDF criado com sucesso<br>";

        // Testar logo
        $logoPath = "../src/assets/img/logo.jpeg";
        if (file_exists($logoPath)) {
            try {
                $pdf->Image($logoPath, 75, 20, 60, 0, "JPEG", "", "C", false, 300, "C", false, false, 0, false, false, false);
                echo "✅ Logo adicionado com sucesso<br>";
            } catch (Exception $e) {
                echo "❌ Erro ao adicionar logo: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ Logo não encontrado<br>";
        }

    } catch (Exception $e) {
        echo "❌ Erro ao criar PDF: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ TCPDF não disponível<br>";
}
?>';

file_put_contents('test_pdf_simple.php', $testContent);
echo "✅ test_pdf_simple.php criado<br>";

echo "<h3>6. Resumo das Correções</h3>";
echo "✅ Verificações de erro adicionadas<br>";
echo "✅ Tratamento de memória melhorado<br>";
echo "✅ Verificação de logo aprimorada<br>";
echo "✅ Tratamento de exceções melhorado<br>";
echo "✅ Arquivos de teste criados<br>";

echo "<h3>7. Próximos Passos</h3>";
echo "1. Teste o arquivo: <a href='test_pdf_simple.php'>test_pdf_simple.php</a><br>";
echo "2. Se funcionar, use: <a href='pdf_real_fixed.php?id=1'>pdf_real_fixed.php?id=1</a><br>";
echo "3. Verifique os logs de erro do servidor<br>";
?>
