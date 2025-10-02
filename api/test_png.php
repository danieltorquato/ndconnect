<?php
// Teste específico para PNG no TCPDF

echo "<h2>Teste PNG no TCPDF</h2>";

// Verificar se GD está instalado
if (extension_loaded('gd')) {
    echo "✅ GD está instalado<br>";
    $info = gd_info();
    echo "PNG Support: " . ($info['PNG Support'] ? "✅ SIM" : "❌ NÃO") . "<br>";
} else {
    echo "❌ GD não está instalado<br>";
}

// Verificar se Imagick está instalado
if (extension_loaded('imagick')) {
    echo "✅ Imagick está instalado<br>";
} else {
    echo "❌ Imagick não está instalado<br>";
}

// Testar conversão da imagem
$logoPath = __DIR__ . '/../src/assets/img/logo.png';
echo "<br><strong>Testando logo PNG:</strong><br>";
echo "Caminho: " . $logoPath . "<br>";
echo "Existe: " . (file_exists($logoPath) ? "✅ SIM" : "❌ NÃO") . "<br>";

if (file_exists($logoPath)) {
    $imageData = file_get_contents($logoPath);
    $base64 = 'data:image/png;base64,' . base64_encode($imageData);
    echo "Base64 gerado: " . (strlen($base64) > 100 ? "✅ SIM (" . strlen($base64) . " chars)" : "❌ NÃO") . "<br>";

    // Testar se é uma imagem PNG válida
    $imageInfo = getimagesize($logoPath);
    if ($imageInfo) {
        echo "Tipo de imagem: " . $imageInfo['mime'] . "<br>";
        echo "Dimensões: " . $imageInfo[0] . "x" . $imageInfo[1] . "<br>";
    } else {
        echo "❌ Não é uma imagem válida<br>";
    }

    // Mostrar a imagem
    echo "<br><img src='$base64' style='max-width: 200px; border: 1px solid #ccc;'>";
}
?>
