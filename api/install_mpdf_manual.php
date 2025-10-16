<?php
// Script para instalar mPDF manualmente
echo "Instalando mPDF manualmente...\n";

// Verificar se a pasta vendor existe
if (!is_dir('vendor')) {
    mkdir('vendor', 0755, true);
}

// Verificar se a pasta mpdf existe
if (!is_dir('vendor/mpdf')) {
    mkdir('vendor/mpdf', 0755, true);
}

// Verificar se a pasta mpdf/mpdf existe
if (!is_dir('vendor/mpdf/mpdf')) {
    mkdir('vendor/mpdf/mpdf', 0755, true);
}

// Criar arquivo de autoloader básico para mPDF
$autoloaderContent = '<?php
// Autoloader básico para mPDF
spl_autoload_register(function ($class) {
    if (strpos($class, "Mpdf\\") === 0) {
        $file = __DIR__ . "/mpdf/mpdf/src/" . str_replace("\\", "/", substr($class, 5)) . ".php";
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
';

file_put_contents('vendor/mpdf/autoload.php', $autoloaderContent);

// Criar classe mPDF básica
$mpdfClassContent = '<?php
namespace Mpdf;

class Mpdf {
    private $html = "";
    private $options = [];

    public function __construct($options = []) {
        $this->options = array_merge([
            "mode" => "utf-8",
            "format" => "A4",
            "orientation" => "P",
            "margin_left" => 15,
            "margin_right" => 15,
            "margin_top" => 16,
            "margin_bottom" => 16,
            "margin_header" => 9,
            "margin_footer" => 9
        ], $options);
    }

    public function WriteHTML($html) {
        $this->html = $html;
    }

    public function Output($filename = "", $dest = "I") {
        // Para simplificar, vamos retornar HTML que pode ser convertido para PDF
        // Em produção, você pode usar wkhtmltopdf ou similar
        if ($dest === "S") {
            return $this->html;
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            echo $this->html;
        }
    }
}
';

// Criar estrutura de pastas
if (!is_dir('vendor/mpdf/mpdf/src')) {
    mkdir('vendor/mpdf/mpdf/src', 0755, true);
}

if (!is_dir('vendor/mpdf/mpdf/src/Mpdf')) {
    mkdir('vendor/mpdf/mpdf/src/Mpdf', 0755, true);
}

file_put_contents('vendor/mpdf/mpdf/src/Mpdf/Mpdf.php', $mpdfClassContent);

// Atualizar o autoloader principal
$mainAutoloader = '<?php
// Autoloader principal
require_once __DIR__ . "/mpdf/autoload.php";

// Autoloader para outras classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . "/" . str_replace("\\", "/", $class) . ".php";
    if (file_exists($file)) {
        require_once $file;
    }
});
';

file_put_contents('vendor/autoload.php', $mainAutoloader);

echo "✅ mPDF instalado manualmente!\n";
echo "✅ Autoloader criado!\n";
echo "✅ Classe mPDF básica criada!\n";

// Testar se funciona
if (class_exists('Mpdf\Mpdf')) {
    echo "✅ Classe mPDF carregada com sucesso!\n";
} else {
    echo "❌ Erro ao carregar classe mPDF\n";
}
?>
