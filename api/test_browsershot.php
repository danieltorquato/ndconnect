<?php
// Teste do Browsershot
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se o Browsershot está disponível
if (!class_exists('Spatie\Browsershot\Browsershot')) {
    // Tentar carregar via autoload do Composer
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    } else {
        die('Browsershot não encontrado. Execute: composer install');
    }
}

use Spatie\Browsershot\Browsershot;

try {
    // Dados de teste
    $orcamento = array(
        'numero_orcamento' => 12345,
        'cliente_nome' => 'João Silva Santos',
        'email' => 'joao.silva@email.com',
        'telefone' => '(11) 99999-9999',
        'cpf_cnpj' => '123.456.789-00',
        'endereco' => 'Rua das Flores, 123 - Centro - São Paulo/SP',
        'data_orcamento' => '2024-01-15',
        'data_validade' => '2024-02-15',
        'subtotal' => 2500.00,
        'desconto' => 250.00,
        'total' => 2250.00,
        'observacoes' => 'Orçamento válido por 30 dias. Pagamento à vista com 10% de desconto. Entrega em até 7 dias úteis.',
        'itens' => array(
            array(
                'produto_nome' => 'Palco 3x3m Profissional',
                'quantidade' => 2,
                'preco_unitario' => 800.00,
                'subtotal' => 1600.00,
                'unidade' => 'un'
            ),
            array(
                'produto_nome' => 'Gerador 5kVA Silencioso',
                'quantidade' => 1,
                'preco_unitario' => 500.00,
                'subtotal' => 500.00,
                'unidade' => 'un'
            ),
            array(
                'produto_nome' => 'Sistema de Som 2.1',
                'quantidade' => 1,
                'preco_unitario' => 400.00,
                'subtotal' => 400.00,
                'unidade' => 'un'
            )
        )
    );

    // Criar HTML com dados inline (para teste)
    $html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Orçamento N.D Connect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; background: white; }
        .header { background: #0C2B59; color: white; padding: 20px; text-align: center; }
        .logo-text { font-size: 28px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { font-size: 16px; opacity: 0.9; }
        .orcamento-numero { background: white; color: #0C2B59; text-align: center; padding: 15px; font-size: 20px; font-weight: bold; }
        .dados-cliente { background: #0C2B59; color: white; padding: 15px 20px; }
        .dados-cliente h2 { font-size: 16px; margin-bottom: 10px; }
        .cliente-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; }
        .cliente-item { display: flex; align-items: center; }
        .cliente-label { font-weight: bold; color: #64748B; margin-right: 8px; min-width: 80px; }
        .cliente-value { color: white; }
        .datas { background: #F8FAFC; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .datas-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: center; }
        .data-label { font-weight: bold; color: #64748B; font-size: 10px; text-transform: uppercase; }
        .data-value { color: #0C2B59; font-size: 13px; font-weight: bold; margin-top: 5px; }
        .itens-titulo { background: #E8622D; color: white; padding: 12px 20px; font-size: 16px; font-weight: bold; margin: 20px 0 0 0; }
        .tabela-itens { width: 100%; border-collapse: collapse; margin: 0; }
        .tabela-itens th { background: #E8622D; color: white; padding: 10px 8px; text-align: left; font-size: 11px; font-weight: bold; }
        .tabela-itens th:nth-child(2), .tabela-itens th:nth-child(3), .tabela-itens th:nth-child(4), .tabela-itens th:nth-child(5) { text-align: center; }
        .tabela-itens td { padding: 10px 8px; border: 1px solid #ddd; font-size: 10px; }
        .tabela-itens tr:nth-child(even) { background: #F8FAFC; }
        .produto-nome { font-weight: bold; color: #0C2B59; }
        .quantidade, .unidade { text-align: center; }
        .preco-unitario { color: #059669; font-weight: bold; text-align: center; }
        .subtotal { font-weight: bold; text-align: center; }
        .totais { background: #F8FAFC; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .total-item { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .total-label { color: #64748B; font-weight: bold; }
        .total-value { font-weight: bold; }
        .desconto { color: #dc2626; }
        .total-final { border-top: 2px solid #E8622D; padding-top: 10px; margin-top: 10px; font-size: 18px; }
        .total-final .total-label { color: #E8622D; }
        .total-final .total-value { color: #E8622D; font-size: 20px; }
        .observacoes { background: #fef3c7; border: 2px solid #F7A64C; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .observacoes h3 { color: #0C2B59; font-size: 14px; margin-bottom: 8px; }
        .observacoes p { color: #92400e; font-style: italic; font-size: 11px; }
        .footer { background: #0C2B59; color: white; padding: 20px; text-align: center; margin-top: 30px; }
        .footer h3 { font-size: 14px; margin-bottom: 8px; }
        .footer p { font-size: 11px; margin-bottom: 5px; }
        .footer .contato { color: #cbd5e1; font-size: 10px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="logo-text">N.D CONNECT</div>
            <div class="subtitle">EQUIPAMENTOS PARA EVENTOS</div>
        </div>

        <div class="orcamento-numero">ORÇAMENTO Nº ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '</div>

        <div class="dados-cliente">
            <h2>DADOS DO CLIENTE</h2>
            <div class="cliente-grid">
                <div class="cliente-item">
                    <span class="cliente-label">NOME:</span>
                    <span class="cliente-value">' . $orcamento['cliente_nome'] . '</span>
                </div>
                <div class="cliente-item">
                    <span class="cliente-label">E-MAIL:</span>
                    <span class="cliente-value">' . $orcamento['email'] . '</span>
                </div>
                <div class="cliente-item">
                    <span class="cliente-label">TELEFONE:</span>
                    <span class="cliente-value">' . $orcamento['telefone'] . '</span>
                </div>
                <div class="cliente-item">
                    <span class="cliente-label">CPF/CNPJ:</span>
                    <span class="cliente-value">' . $orcamento['cpf_cnpj'] . '</span>
                </div>
                <div class="cliente-item">
                    <span class="cliente-label">ENDEREÇO:</span>
                    <span class="cliente-value">' . $orcamento['endereco'] . '</span>
                </div>
            </div>
        </div>

        <div class="datas">
            <div class="datas-grid">
                <div>
                    <div class="data-label">DATA DO ORÇAMENTO</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($orcamento['data_orcamento'])) . '</div>
                </div>
                <div>
                    <div class="data-label">VÁLIDO ATÉ</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($orcamento['data_validade'])) . '</div>
                </div>
            </div>
        </div>

        <div class="itens-titulo">ITENS DO ORÇAMENTO</div>

        <table class="tabela-itens">
            <thead>
                <tr>
                    <th>PRODUTO</th>
                    <th>QTD</th>
                    <th>PREÇO UNIT.</th>
                    <th>SUBTOTAL</th>
                    <th>UNID.</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($orcamento['itens'] as $item) {
        $html .= '<tr>
                    <td class="produto-nome">' . $item['produto_nome'] . '</td>
                    <td class="quantidade">' . $item['quantidade'] . '</td>
                    <td class="preco-unitario">R$ ' . number_format($item['preco_unitario'], 2, ',', '.') . '</td>
                    <td class="subtotal">R$ ' . number_format($item['subtotal'], 2, ',', '.') . '</td>
                    <td class="unidade">' . $item['unidade'] . '</td>
                </tr>';
    }

    $html .= '</tbody>
        </table>

        <div class="totais">
            <div class="total-item">
                <span class="total-label">SUBTOTAL:</span>
                <span class="total-value">R$ ' . number_format($orcamento['subtotal'], 2, ',', '.') . '</span>
            </div>
            <div class="total-item">
                <span class="total-label">DESCONTO:</span>
                <span class="total-value desconto">- R$ ' . number_format($orcamento['desconto'], 2, ',', '.') . '</span>
            </div>
            <div class="total-item total-final">
                <span class="total-label">TOTAL:</span>
                <span class="total-value">R$ ' . number_format($orcamento['total'], 2, ',', '.') . '</span>
            </div>
        </div>

        <div class="observacoes">
            <h3>OBSERVAÇÕES</h3>
            <p>' . $orcamento['observacoes'] . '</p>
        </div>

        <div class="footer">
            <h3>N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</h3>
            <p>Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED</p>
            <p class="contato">Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br | Site: www.ndconnect.com.br</p>
        </div>
    </div>
</body>
</html>';

    // Configurar Browsershot
    $browsershot = Browsershot::html($html)
        ->format('A4')
        ->margins(10, 10, 10, 10)
        ->showBackground()
        ->timeout(60);

    // Gerar PDF
    $pdfContent = $browsershot->pdf();

    // Headers para download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="teste_browsershot.pdf"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Enviar conteúdo do PDF
    echo $pdfContent;

} catch (Exception $e) {
    error_log('Erro no teste Browsershot: ' . $e->getMessage());
    header('Content-Type: text/html');
    die('Erro ao gerar PDF: ' . $e->getMessage());
}
?>
