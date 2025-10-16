<?php
// Script para debugar erros nos PDFs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste de Debug - PDF Real</h2>";

// Testar se o arquivo pdf_real.php existe
if (file_exists('pdf_real.php')) {
    echo "✅ pdf_real.php encontrado<br>";

    // Testar se o TCPDF está disponível
    if (file_exists('vendor/autoload.php')) {
        echo "✅ vendor/autoload.php encontrado<br>";
        require_once 'vendor/autoload.php';

        if (class_exists('TCPDF')) {
            echo "✅ Classe TCPDF disponível<br>";
        } else {
            echo "❌ Classe TCPDF não encontrada<br>";
        }
    } else {
        echo "❌ vendor/autoload.php não encontrado<br>";
    }

    // Testar se o arquivo tcpdf_simple.php existe
    if (file_exists('tcpdf_simple.php')) {
        echo "✅ tcpdf_simple.php encontrado<br>";
    } else {
        echo "❌ tcpdf_simple.php não encontrado<br>";
    }

    // Testar se o logo existe
    $logoPath = '../src/assets/img/logo.jpeg';
    if (file_exists($logoPath)) {
        echo "✅ Logo encontrado: $logoPath<br>";
        echo "Tamanho: " . filesize($logoPath) . " bytes<br>";
    } else {
        echo "❌ Logo não encontrado: $logoPath<br>";

        // Verificar se existe logo.png
        $logoPathPng = '../src/assets/img/logo.png';
        if (file_exists($logoPathPng)) {
            echo "✅ Logo PNG encontrado: $logoPathPng<br>";
            echo "Tamanho: " . filesize($logoPathPng) . " bytes<br>";
        } else {
            echo "❌ Logo PNG também não encontrado: $logoPathPng<br>";
        }
    }

    // Testar conexão com banco
    try {
        require_once 'Config/Database.php';
        $database = new Database();
        $conn = $database->connect();
        if ($conn) {
            echo "✅ Conexão com banco OK<br>";
        } else {
            echo "❌ Erro na conexão com banco<br>";
        }
    } catch (Exception $e) {
        echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    }

} else {
    echo "❌ pdf_real.php não encontrado<br>";
}

echo "<h2>Teste de Debug - Simple PDF</h2>";

// Testar simple_pdf.php
if (file_exists('simple_pdf.php')) {
    echo "✅ simple_pdf.php encontrado<br>";

    // Testar se o OrcamentoController existe
    if (file_exists('Controllers/OrcamentoController.php')) {
        echo "✅ OrcamentoController.php encontrado<br>";
    } else {
        echo "❌ OrcamentoController.php não encontrado<br>";
    }
} else {
    echo "❌ simple_pdf.php não encontrado<br>";
}

echo "<h2>Teste de Debug - Download PDF</h2>";

// Testar download_pdf.php
if (file_exists('download_pdf.php')) {
    echo "✅ download_pdf.php encontrado<br>";
} else {
    echo "❌ download_pdf.php não encontrado<br>";
}

echo "<h2>Teste de Erro Simulado</h2>";

// Simular um teste com ID de orçamento
if (isset($_GET['test_id'])) {
    $testId = $_GET['test_id'];
    echo "Testando com ID: $testId<br>";

    try {
        // Testar pdf_real.php
        echo "<h3>Testando pdf_real.php</h3>";
        ob_start();
        $_GET['id'] = $testId;
        include 'pdf_real.php';
        $output = ob_get_clean();

        if (strpos($output, 'PDF') !== false || strpos($output, 'TCPDF') !== false) {
            echo "✅ pdf_real.php executou sem erros fatais<br>";
        } else {
            echo "❌ pdf_real.php retornou: " . substr($output, 0, 200) . "...<br>";
        }

    } catch (Exception $e) {
        echo "❌ Erro em pdf_real.php: " . $e->getMessage() . "<br>";
    }

    try {
        // Testar simple_pdf.php
        echo "<h3>Testando simple_pdf.php</h3>";
        ob_start();
        $_GET['id'] = $testId;
        include 'simple_pdf.php';
        $output = ob_get_clean();

        if (strpos($output, 'html') !== false || strpos($output, 'DOCTYPE') !== false) {
            echo "✅ simple_pdf.php executou sem erros fatais<br>";
        } else {
            echo "❌ simple_pdf.php retornou: " . substr($output, 0, 200) . "...<br>";
        }

    } catch (Exception $e) {
        echo "❌ Erro em simple_pdf.php: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Para testar com um ID específico, use: ?test_id=1<br>";
}

echo "<h2>Verificação de Logs de Erro</h2>";

// Verificar se há logs de erro do PHP
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo "Log de erro encontrado: $errorLog<br>";
    $logContent = file_get_contents($errorLog);
    $recentErrors = array_slice(explode("\n", $logContent), -10);
    echo "Últimos 10 erros:<br>";
    foreach ($recentErrors as $error) {
        if (trim($error)) {
            echo "• " . htmlspecialchars($error) . "<br>";
        }
    }
} else {
    echo "Nenhum log de erro encontrado<br>";
}
?>
