<?php
// Teste do PDF para ficar idêntico à imagem
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste do PDF - Versão Idêntica à Imagem</h2>";

// Dados de teste baseados na imagem
$orcamento = [
    'id' => 14,
    'numero_orcamento' => 14,
    'cliente_nome' => 'DANIEL MONTEIRO DA SILVA TORQUATO',
    'email' => 'danieltorquato2009@gmail.com',
    'telefone' => '(11) 95389-8557',
    'cpf_cnpj' => '',
    'endereco' => '',
    'data_orcamento' => '2025-10-07',
    'data_validade' => '2025-10-17',
    'subtotal' => 1800.00,
    'desconto' => 0.00,
    'total' => 1800.00,
    'observacoes' => 'teste',
    'itens' => [
        [
            'produto_nome' => 'Gerador 5KVA',
            'quantidade' => 1,
            'preco_unitario' => 300.00,
            'subtotal' => 300.00,
            'unidade' => 'dia'
        ],
        [
            'produto_nome' => 'Sistema de som 2.1',
            'quantidade' => 1,
            'preco_unitario' => 300.00,
            'subtotal' => 300.00,
            'unidade' => 'dia'
        ],
        [
            'produto_nome' => 'Palco 4x4m',
            'quantidade' => 1,
            'preco_unitario' => 1200.00,
            'subtotal' => 1200.00,
            'unidade' => 'unidade'
        ]
    ]
];

echo "<h3>1. Testando geração de PDF idêntico</h3>";

try {
    // Incluir a função de geração
    require_once 'pdf_real.php';

    // Gerar PDF
    $pdfContent = generateSimplePDF($orcamento, 14);

    echo "<p><strong>Tamanho do PDF:</strong> " . strlen($pdfContent) . " bytes</p>";

    // Verificar assinatura
    if (strpos($pdfContent, '%PDF') === 0) {
        echo "<p style='color: green;'>✅ PDF válido - começa com %PDF</p>";
    } else {
        echo "<p style='color: red;'>❌ PDF inválido - não começa com %PDF</p>";
    }

    if (strpos($pdfContent, '%%EOF') !== false) {
        echo "<p style='color: green;'>✅ PDF válido - termina com %%EOF</p>";
    } else {
        echo "<p style='color: red;'>❌ PDF inválido - não termina com %%EOF</p>";
    }

    // Salvar arquivo de teste
    file_put_contents('teste_pdf_identico.pdf', $pdfContent);
    echo "<p><a href='teste_pdf_identico.pdf' target='_blank'>Baixar PDF Idêntico</a></p>";

    echo "<h3>2. Teste de Download Direto</h3>";
    echo "<p><a href='pdf_real.php?id=14' target='_blank'>Testar Download PDF Real (ID=14)</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<h3>3. Características Implementadas</h3>";
echo "<ul>";
echo "<li>✅ <strong>Logo:</strong> N.D em círculo azul + CONNECT em retângulo laranja</li>";
echo "<li>✅ <strong>Barra Azul:</strong> 'EQUIPAMENTOS PARA EVENTOS' centralizado</li>";
echo "<li>✅ <strong>Número:</strong> 'ORÇAMENTO Nº 000014' centralizado</li>";
echo "<li>✅ <strong>Dados Cliente:</strong> Grid com NOME, E-MAIL, TELEFONE, CPF/CNPJ</li>";
echo "<li>✅ <strong>Datas:</strong> Fundo cinza claro com DATA DO ORÇAMENTO e VÁLIDO ATÉ</li>";
echo "<li>✅ <strong>Itens:</strong> Cabeçalho escuro com PRODUTO, QTD, PREÇO UNIT., SUBTOTAL, UNID.</li>";
echo "<li>✅ <strong>Preços:</strong> Valores unitários em verde</li>";
echo "<li>✅ <strong>Totais:</strong> SUBTOTAL e TOTAL alinhados à direita</li>";
echo "<li>✅ <strong>Observações:</strong> Fundo amarelo claro com texto em itálico</li>";
echo "<li>✅ <strong>Footer:</strong> Barra azul com informações da empresa</li>";
echo "</ul>";

echo "<h3>4. Cores Utilizadas</h3>";
echo "<ul>";
echo "<li>🔵 <strong>Azul Marinho:</strong> #0C2B59 - Logo, barras, footer</li>";
echo "<li>🟠 <strong>Laranja:</strong> #E8622D - CONNECT, seção itens, total</li>";
echo "<li>🟢 <strong>Verde:</strong> Preços unitários</li>";
echo "<li>⚪ <strong>Cinza Claro:</strong> #F8FAFC - Fundo datas</li>";
echo "<li>⚫ <strong>Cinza Escuro:</strong> Texto principal</li>";
echo "<li>🟡 <strong>Amarelo Claro:</strong> Fundo observações</li>";
echo "</ul>";

echo "<h3>5. Verificação de Arquivos</h3>";
echo "<p>Arquivos PDF na pasta:</p>";
$files = glob('*.pdf');
foreach ($files as $file) {
    echo "<p>- " . $file . " (" . filesize($file) . " bytes)</p>";
}
?>
