<?php
// Script para verificar se GD está instalado

echo "<h2>Verificação de extensões PHP</h2>";

if (extension_loaded('gd')) {
    echo "✅ <strong>GD está instalado e ativo!</strong><br>";
    $info = gd_info();
    echo "<pre>";
    print_r($info);
    echo "</pre>";
} else {
    echo "❌ <strong>GD NÃO está instalado</strong><br><br>";
    echo "<strong>Para ativar GD:</strong><br>";
    echo "1. Abra o arquivo php.ini<br>";
    echo "2. Procure por: ;extension=gd<br>";
    echo "3. Remova o ponto e vírgula: extension=gd<br>";
    echo "4. Reinicie o servidor PHP<br>";
}

echo "<hr>";

if (extension_loaded('imagick')) {
    echo "✅ <strong>Imagick está instalado e ativo!</strong><br>";
} else {
    echo "❌ <strong>Imagick NÃO está instalado</strong><br>";
}

echo "<hr>";
echo "<strong>Localização do php.ini:</strong> " . php_ini_loaded_file();
?>

