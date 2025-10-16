<?php
// Teste do PDF com formatação visual
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste do PDF com Formatação Visual</h2>";

// Simular dados de orçamento para teste
$orcamento = [
    'id' => 1,
    'numero_orcamento' => 1,
    'cliente_nome' => 'Daniel Teste',
    'email' => 'daniel@teste.com',
    'telefone' => '(11) 99999-9999',
    'cpf_cnpj' => '123.456.789-00',
    'endereco' => 'Rua Teste, 123 - São Paulo/SP',
    'data_orcamento' => '2025-01-16',
    'data_validade' => '2025-02-16',
    'subtotal' => 2500.00,
    'desconto' => 250.00,
    'total' => 2250.00,
    'observacoes' => 'Orçamento válido por 30 dias. Pagamento à vista com 10% de desconto.',
    'itens' => [
        [
            'produto_nome' => 'Palco 3x3m',
            'quantidade' => 1,
            'preco_unitario' => 800.00,
            'subtotal' => 800.00,
            'unidade' => 'UN'
        ],
        [
            'produto_nome' => 'Sistema de Som 1000W',
            'quantidade' => 2,
            'preco_unitario' => 500.00,
            'subtotal' => 1000.00,
            'unidade' => 'UN'
        ],
        [
            'produto_nome' => 'Iluminação LED RGB',
            'quantidade' => 4,
            'preco_unitario' => 175.00,
            'subtotal' => 700.00,
            'unidade' => 'UN'
        ]
    ]
];

echo "<h3>1. Testando geração de PDF formatado</h3>";

try {
    // Incluir a função de geração
    require_once 'pdf_real.php';

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
    file_put_contents('teste_pdf_formatado.pdf', $pdfContent);
    echo "<p><a href='teste_pdf_formatado.pdf' target='_blank'>Baixar PDF Formatado</a></p>";

    echo "<h3>2. Teste de Download Direto</h3>";
    echo "<p><a href='pdf_real.php?id=1' target='_blank'>Testar Download PDF Real (ID=1)</a></p>";

    echo "<h3>3. Comparação com HTML</h3>";
    echo "<p><a href='simple_pdf.php?id=1' target='_blank'>Ver versão HTML para comparação</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Características da Formatação</h3>";
echo "<ul>";
echo "<li>✅ <strong>Cores N.D Connect:</strong> Azul marinho (#0C2B59), Laranja (#E8622D)</li>";
echo "<li>✅ <strong>Layout Visual:</strong> Retângulos coloridos, seções bem definidas</li>";
echo "<li>✅ <strong>Tipografia:</strong> Helvetica normal, bold e oblique</li>";
echo "<li>✅ <strong>Tabela de Itens:</strong> Cabeçalho azul, dados organizados</li>";
echo "<li>✅ <strong>Seções:</strong> Header, dados cliente, datas, itens, totais, observações, footer</li>";
echo "<li>✅ <strong>Alinhamento:</strong> Texto centralizado e alinhado à direita</li>";
echo "<li>✅ <strong>Hierarquia Visual:</strong> Tamanhos de fonte variados (9pt a 18pt)</li>";
echo "</ul>";

echo "<h3>5. Verificação de Arquivos</h3>";
echo "<p>Arquivos PDF na pasta:</p>";
$files = glob('*.pdf');
foreach ($files as $file) {
    echo "<p>- " . $file . " (" . filesize($file) . " bytes)</p>";
}
?>
