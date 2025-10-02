<?php
// Script para instalar mPDF via Composer
echo "Instalando mPDF para conversão real de PDF...\n";

// Verificar se o Composer está disponível
$composerPath = 'composer';
if (PHP_OS_FAMILY === 'Windows') {
    $composerPath = 'composer.bat';
}

// Comando para instalar mPDF
$command = "$composerPath require mpdf/mpdf";

echo "Executando: $command\n";

// Executar comando
$output = [];
$returnCode = 0;
exec($command . ' 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ mPDF instalado com sucesso!\n";
    echo "Saída:\n" . implode("\n", $output) . "\n";
} else {
    echo "❌ Erro ao instalar mPDF:\n";
    echo implode("\n", $output) . "\n";
    echo "\nTentando instalação manual...\n";

    // Criar composer.json se não existir
    if (!file_exists('composer.json')) {
        $composerJson = [
            "require" => [
                "mpdf/mpdf" => "^8.0"
            ]
        ];
        file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
        echo "✅ composer.json criado\n";
    }

    // Tentar instalar novamente
    exec("$composerPath install 2>&1", $output2, $returnCode2);
    if ($returnCode2 === 0) {
        echo "✅ mPDF instalado com sucesso na segunda tentativa!\n";
    } else {
        echo "❌ Falha na instalação. Instale manualmente:\n";
        echo "1. Execute: composer require mpdf/mpdf\n";
        echo "2. Ou baixe mPDF manualmente\n";
    }
}

echo "\nVerificando instalação...\n";
if (file_exists('vendor/autoload.php')) {
    echo "✅ Autoloader encontrado\n";
    require_once 'vendor/autoload.php';

    if (class_exists('Mpdf\Mpdf')) {
        echo "✅ Classe mPDF disponível\n";
    } else {
        echo "❌ Classe mPDF não encontrada\n";
    }
} else {
    echo "❌ Autoloader não encontrado\n";
}
?>
