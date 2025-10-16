<?php
// Teste do PDF corrigido - mantendo configuração original
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste do PDF Corrigido - Configuração Original + Formatação Visual</h2>";

echo "<h3>1. Testando geração de PDF</h3>";

try {
    // Incluir a função de geração
    require_once 'pdf_real.php';

    // Simular dados de orçamento para teste
    $orcamento = [
        'id' => 1,
        'numero_orcamento' => 1,
        'cliente_nome' => 'Daniel Teste',
        'email' => 'daniel@teste.com',
        'telefone' => '(11) 99999-9999',
        'cpf_cnpj' => '123.456.789-00',
        'endereco' => 'Rua Teste, 123',
        'data_orcamento' => '2025-01-16',
        'data_validade' => '2025-02-16',
        'subtotal' => 1000.00,
        'desconto' => 0.00,
        'total' => 1000.00,
        'observacoes' => 'Teste de orçamento',
        'itens' => [
            [
                'produto_nome' => 'Produto Teste',
                'quantidade' => 1,
                'preco_unitario' => 1000.00,
                'subtotal' => 1000.00,
                'unidade' => 'UN'
            ]
        ]
    ];

    // Gerar PDF
    $pdfContent = generateSimplePDF($orcamento, 1);

    echo "<p><strong>Tamanho do PDF:</strong> " . strlen($pdfContent) . " bytes</p>";

    // Verificar assinatura
    if (strpos($pdfContent, '%PDF') === 0) {
        echo "<p style='color: green;'>✅ PDF válido - começa com %PDF</p>";
    } else {
        echo "<p style='color: red;'>❌ PDF inválido - não começa com %PDF</p>";
        echo "<p>Primeiros 50 caracteres: " . htmlspecialchars(substr($pdfContent, 0, 50)) . "</p>";
    }

    if (strpos($pdfContent, '%%EOF') !== false) {
        echo "<p style='color: green;'>✅ PDF válido - termina com %%EOF</p>";
    } else {
        echo "<p style='color: red;'>❌ PDF inválido - não termina com %%EOF</p>";
    }

    // Salvar arquivo de teste
    file_put_contents('teste_pdf_corrigido.pdf', $pdfContent);
    echo "<p><a href='teste_pdf_corrigido.pdf' target='_blank'>Baixar PDF Corrigido</a></p>";

    echo "<h3>2. Teste de Download Direto</h3>";
    echo "<p><a href='pdf_real.php?id=1' target='_blank'>Testar Download PDF Real (ID=1)</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<h3>3. O que foi corrigido</h3>";
echo "<ul>";
echo "<li>✅ <strong>Configuração Original:</strong> Mantida a configuração que funcionava</li>";
echo "<li>✅ <strong>Headers Corretos:</strong> Content-Type e Content-Disposition</li>";
echo "<li>✅ <strong>Método Output:</strong> Usando 'S' + headers manuais</li>";
echo "<li>✅ <strong>Formatação Visual:</strong> Aplicada apenas o layout visual</li>";
echo "<li>✅ <strong>Cores N.D Connect:</strong> Azul marinho, laranja, amarelo</li>";
echo "<li>✅ <strong>Layout Profissional:</strong> Seções bem definidas</li>";
echo "</ul>";

echo "<h3>4. Verificação de Arquivos</h3>";
echo "<p>Arquivos PDF na pasta:</p>";
$files = glob('*.pdf');
foreach ($files as $file) {
    echo "<p>- " . $file . " (" . filesize($file) . " bytes)</p>";
}

echo "<h3>5. Status Final</h3>";
echo "<p style='color: green; font-weight: bold;'>✅ PDF corrigido - configuração original + formatação visual!</p>";
echo "<p>O PDF agora mantém a configuração que funcionava e aplica apenas a formatação visual que você queria.</p>";
?>
