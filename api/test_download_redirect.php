<?php
// Teste para verificar redirecionamento do download PDF
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste de Redirecionamento - Download PDF</h2>";

// Verificar se estamos recebendo o ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    echo "✅ ID recebido: $id<br>";
} else {
    echo "❌ ID não recebido<br>";
    echo "URL atual: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "Query string: " . $_SERVER['QUERY_STRING'] . "<br>";
}

// Verificar headers
echo "<h3>Headers da Requisição:</h3>";
echo "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Não definido') . "<br>";
echo "Accept: " . ($_SERVER['HTTP_ACCEPT'] ?? 'Não definido') . "<br>";
echo "Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'Não definido') . "<br>";

// Verificar se há redirecionamentos
echo "<h3>Verificando Redirecionamentos:</h3>";

// Simular uma requisição para pdf_real.php
$pdfUrl = "pdf_real.php?id=" . ($id ?? 1);
echo "Tentando acessar: $pdfUrl<br>";

if (file_exists('pdf_real.php')) {
    echo "✅ pdf_real.php existe<br>";

    // Verificar se há redirecionamento no .htaccess
    if (file_exists('.htaccess')) {
        echo "✅ .htaccess existe<br>";
        $htaccess = file_get_contents('.htaccess');
        echo "Conteúdo do .htaccess:<br>";
        echo "<pre>" . htmlspecialchars($htaccess) . "</pre>";
    } else {
        echo "❌ .htaccess não existe<br>";
    }

    // Testar se conseguimos incluir o arquivo
    try {
        ob_start();
        $_GET['id'] = $id ?? 1;
        include 'pdf_real.php';
        $output = ob_get_clean();

        if (strpos($output, 'PDF') !== false || strpos($output, 'TCPDF') !== false) {
            echo "✅ pdf_real.php executou sem redirecionamento<br>";
        } else {
            echo "❌ pdf_real.php retornou conteúdo inesperado:<br>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
        }

    } catch (Exception $e) {
        echo "❌ Erro ao executar pdf_real.php: " . $e->getMessage() . "<br>";
    }

} else {
    echo "❌ pdf_real.php não existe<br>";
}

// Verificar configuração do servidor
echo "<h3>Configuração do Servidor:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Não definido') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Não definido') . "<br>";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Não definido') . "<br>";

// Verificar se há redirecionamentos no index.php
if (file_exists('index.php')) {
    echo "<h3>Verificando index.php:</h3>";
    $indexContent = file_get_contents('index.php');
    if (strpos($indexContent, 'header(') !== false) {
        echo "⚠️ index.php contém redirecionamentos (header)<br>";
    } else {
        echo "✅ index.php não contém redirecionamentos óbvios<br>";
    }
}

// Teste de download direto
echo "<h3>Teste de Download Direto:</h3>";
echo "<a href='pdf_real.php?id=" . ($id ?? 1) . "' target='_blank'>Testar Download Direto</a><br>";

// Verificar se há JavaScript que pode estar causando redirecionamento
echo "<h3>Teste JavaScript:</h3>";
?>

<script>
console.log("Testando redirecionamento...");

// Simular o que o frontend faz
function testarDownload() {
    const id = <?php echo $id ?? 1; ?>;
    const pdfUrl = `pdf_real.php?id=${id}`;

    console.log("Tentando baixar:", pdfUrl);

    // Criar link temporário
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.download = `orcamento_teste_${id}.pdf`;
    link.target = '_blank';

    console.log("Link criado:", link.href);
    console.log("Download attribute:", link.download);

    // Adicionar ao DOM e clicar
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    console.log("Clique executado");
}

// Executar teste automaticamente
document.addEventListener("DOMContentLoaded", function() {
    console.log("Página carregada, executando teste...");
    testarDownload();
});
</script>

<button onclick="testarDownload()" style="padding: 10px 20px; background: #0C2B59; color: white; border: none; border-radius: 5px; cursor: pointer;">
    🔄 Testar Download Novamente
</button>

<?php
// Verificar logs de erro
echo "<h3>Logs de Erro Recentes:</h3>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $logContent = file_get_contents($errorLog);
    $recentErrors = array_slice(explode("\n", $logContent), -20);
    echo "Últimos 20 erros:<br>";
    foreach ($recentErrors as $error) {
        if (trim($error) && strpos($error, 'pdf') !== false) {
            echo "• " . htmlspecialchars($error) . "<br>";
        }
    }
} else {
    echo "Nenhum log de erro encontrado<br>";
}
?>
