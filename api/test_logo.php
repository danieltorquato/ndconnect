<?php
// Teste para verificar se a logo está sendo encontrada

$logoPath = __DIR__ . '/../src/assets/img/logo.png';

echo "<h2>Teste da Logo PNG</h2>";
echo "<strong>Caminho:</strong> " . $logoPath . "<br>";
echo "<strong>Existe:</strong> " . (file_exists($logoPath) ? "✅ SIM" : "❌ NÃO") . "<br>";

if (file_exists($logoPath)) {
    echo "<strong>Tamanho:</strong> " . filesize($logoPath) . " bytes<br>";
    echo "<strong>Última modificação:</strong> " . date('d/m/Y H:i:s', filemtime($logoPath)) . "<br>";

    // Testar conversão para base64
    $imageData = file_get_contents($logoPath);
    $base64 = 'data:image/png;base64,' . base64_encode($imageData);
    echo "<strong>Base64 gerado:</strong> " . (strlen($base64) > 100 ? "✅ SIM (" . strlen($base64) . " chars)" : "❌ NÃO") . "<br>";

    // Mostrar a imagem
    echo "<br><img src='$base64' style='max-width: 200px; border: 1px solid #ccc;'>";
} else {
    echo "<br><strong>Listando arquivos na pasta:</strong><br>";
    $dir = __DIR__ . '/../src/assets/img/';
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "- $file<br>";
            }
        }
    } else {
        echo "Pasta não existe: $dir";
    }
}
?>
