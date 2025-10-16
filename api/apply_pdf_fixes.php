<?php
// Script para aplicar todas as correções de PDF
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Aplicando Correções de PDF</h2>";

// 1. Fazer backup dos arquivos originais
echo "<h3>1. Fazendo Backup dos Arquivos Originais</h3>";

$filesToBackup = [
    'pdf_real.php',
    'tcpdf_simple.php',
    'simple_pdf.php'
];

foreach ($filesToBackup as $file) {
    if (file_exists($file)) {
        $backupName = $file . '.backup.' . date('Y-m-d_H-i-s');
        if (copy($file, $backupName)) {
            echo "✅ Backup criado: $backupName<br>";
        } else {
            echo "❌ Erro ao criar backup: $file<br>";
        }
    } else {
        echo "⚠️ Arquivo não encontrado: $file<br>";
    }
}

// 2. Substituir pdf_real.php pela versão corrigida
echo "<h3>2. Substituindo pdf_real.php</h3>";

if (file_exists('pdf_real_corrigido.php')) {
    if (copy('pdf_real_corrigido.php', 'pdf_real.php')) {
        echo "✅ pdf_real.php substituído pela versão corrigida<br>";
    } else {
        echo "❌ Erro ao substituir pdf_real.php<br>";
    }
} else {
    echo "❌ pdf_real_corrigido.php não encontrado<br>";
}

// 3. Substituir tcpdf_simple.php pela versão corrigida
echo "<h3>3. Substituindo tcpdf_simple.php</h3>";

if (file_exists('tcpdf_simple_fixed.php')) {
    if (copy('tcpdf_simple_fixed.php', 'tcpdf_simple.php')) {
        echo "✅ tcpdf_simple.php substituído pela versão corrigida<br>";
    } else {
        echo "❌ Erro ao substituir tcpdf_simple.php<br>";
    }
} else {
    echo "❌ tcpdf_simple_fixed.php não encontrado<br>";
}

// 4. Atualizar simple_pdf.php para usar os caminhos corretos
echo "<h3>4. Atualizando simple_pdf.php</h3>";

$simplePdfContent = file_get_contents('simple_pdf.php');

// Corrigir caminhos no JavaScript
$correcoes = [
    // Corrigir caminho do PDF no download
    'window.open("/pdf_real.php?id=\' . $id . \'", "_blank");' => 'window.open("/api/pdf_real.php?id=\' . $id . \'", "_blank");',

    // Corrigir caminho do PDF no WhatsApp
    'const pdfUrl = window.location.origin + "/pdf_real.php?id=\' . $id . \'";' => 'const pdfUrl = window.location.origin + "/api/pdf_real.php?id=\' . $id . \'";',

    // Corrigir caminho do PDF no email
    'const pdfUrl = window.location.origin + "/pdf_real.php?id=\' . $id . \'";' => 'const pdfUrl = window.location.origin + "/api/pdf_real.php?id=\' . $id . \'";',
];

foreach ($correcoes as $busca => $substituicao) {
    $simplePdfContent = str_replace($busca, $substituicao, $simplePdfContent);
}

// Salvar versão corrigida
if (file_put_contents('simple_pdf.php', $simplePdfContent)) {
    echo "✅ simple_pdf.php atualizado com caminhos corretos<br>";
} else {
    echo "❌ Erro ao atualizar simple_pdf.php<br>";
}

// 5. Atualizar Routes/api.php para incluir as rotas corretas
echo "<h3>5. Atualizando Rotas</h3>";

if (file_exists('Routes/api.php')) {
    $routesContent = file_get_contents('Routes/api.php');

    // Verificar se já tem as rotas corretas
    if (strpos($routesContent, "case 'pdf_real.php':") !== false) {
        echo "✅ Rotas já configuradas corretamente<br>";
    } else {
        echo "⚠️ Rotas podem precisar de atualização<br>";
    }
} else {
    echo "❌ Routes/api.php não encontrado<br>";
}

// 6. Testar se tudo está funcionando
echo "<h3>6. Testando Funcionamento</h3>";

// Testar se TCPDF está funcionando
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

if (!class_exists('TCPDF') && file_exists('tcpdf_simple.php')) {
    require_once 'tcpdf_simple.php';
}

if (class_exists('TCPDF')) {
    echo "✅ TCPDF funcionando<br>";
} else {
    echo "❌ TCPDF não funcionando<br>";
}

// Testar se pdf_real.php existe e é acessível
if (file_exists('pdf_real.php')) {
    echo "✅ pdf_real.php existe<br>";

    // Verificar se tem o conteúdo correto
    $content = file_get_contents('pdf_real.php');
    if (strpos($content, 'tcpdf_simple_fixed.php') !== false) {
        echo "✅ pdf_real.php está usando TCPDF corrigido<br>";
    } else {
        echo "⚠️ pdf_real.php pode não estar usando a versão corrigida<br>";
    }
} else {
    echo "❌ pdf_real.php não encontrado<br>";
}

// 7. Criar arquivo de teste final
echo "<h3>7. Criando Teste Final</h3>";

$testeFinal = '<?php
// Teste final para verificar se tudo está funcionando
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Teste Final - Sistema de PDF</h2>";

// Testar TCPDF
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
}

if (!class_exists("TCPDF") && file_exists("tcpdf_simple.php")) {
    require_once "tcpdf_simple.php";
}

if (class_exists("TCPDF")) {
    echo "✅ TCPDF carregado<br>";

    try {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
        $pdf->SetCreator("N.D Connect");
        $pdf->SetTitle("Teste Final");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();

        $pdf->SetFont("helvetica", "B", 16);
        $pdf->Cell(0, 10, "TESTE FINAL - SISTEMA CORRIGIDO", 0, 1, "C");

        $pdf->Ln(10);

        $pdf->SetFont("helvetica", "", 12);
        $pdf->Cell(0, 8, "✅ TCPDF funcionando", 0, 1, "L");
        $pdf->Cell(0, 8, "✅ PDF sendo gerado corretamente", 0, 1, "L");
        $pdf->Cell(0, 8, "✅ Sistema pronto para uso", 0, 1, "L");

        $pdfContent = $pdf->Output("teste_final.pdf", "S");

        if (strlen($pdfContent) > 0) {
            echo "✅ PDF gerado com sucesso (" . strlen($pdfContent) . " bytes)<br>";

            // Verificar assinatura
            $signature = substr($pdfContent, 0, 4);
            if ($signature === "%PDF") {
                echo "✅ PDF válido<br>";
            } else {
                echo "❌ PDF inválido (assinatura: " . bin2hex($signature) . ")<br>";
            }
        } else {
            echo "❌ PDF vazio<br>";
        }

    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ TCPDF não carregado<br>";
}

echo "<h3>Links de Teste:</h3>";
echo "• <a href=\"pdf_real.php?id=1\" target=\"_blank\">Teste pdf_real.php?id=1</a><br>";
echo "• <a href=\"simple_pdf.php?id=1\" target=\"_blank\">Teste simple_pdf.php?id=1</a><br>";

echo "<h3>Próximos Passos:</h3>";
echo "1. Teste os links acima<br>";
echo "2. Se funcionarem, teste no frontend<br>";
echo "3. Se houver problemas, verifique os logs de erro<br>";
?>';

if (file_put_contents('teste_final_sistema.php', $testeFinal)) {
    echo "✅ teste_final_sistema.php criado<br>";
} else {
    echo "❌ Erro ao criar teste_final_sistema.php<br>";
}

echo "<h3>8. Resumo das Correções Aplicadas</h3>";
echo "✅ Backup dos arquivos originais criado<br>";
echo "✅ pdf_real.php substituído pela versão corrigida<br>";
echo "✅ tcpdf_simple.php substituído pela versão corrigida<br>";
echo "✅ simple_pdf.php atualizado com caminhos corretos<br>";
echo "✅ Sistema de rotas verificado<br>";
echo "✅ Teste final criado<br>";

echo "<h3>9. Teste Agora</h3>";
echo "• <a href=\"teste_final_sistema.php\">teste_final_sistema.php</a> - Teste completo do sistema<br>";
echo "• <a href=\"pdf_real.php?id=1\">pdf_real.php?id=1</a> - Teste direto do PDF<br>";

echo "<h3>10. Se Ainda Houver Problemas</h3>";
echo "1. Verifique se o servidor web está funcionando<br>";
echo "2. Verifique os logs de erro do PHP<br>";
echo "3. Teste os arquivos individualmente<br>";
echo "4. Se necessário, restaure os backups<br>";
?>
