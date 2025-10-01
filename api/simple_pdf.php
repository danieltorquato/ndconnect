<?php
require_once 'Controllers/OrcamentoController.php';

// Função para converter imagem para base64
function getLogoBase64() {
    $logoPath = __DIR__ . '/../src/assets/img/logo.jpeg';
    if (file_exists($logoPath)) {
        $imageData = file_get_contents($logoPath);
        $base64 = base64_encode($imageData);
        return 'data:image/jpeg;base64,' . $base64;
    }
    return null;
}

function gerarPDFSimples($orcamento) {
    $logoBase64 = getLogoBase64();

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Orçamento - ' . $orcamento['numero_orcamento'] . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background: white;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #f97316;
                padding-bottom: 20px;
            }
            .logo {
                display: inline-block;
                background: #1e3a8a;
                color: white;
                padding: 10px 20px;
                border-radius: 50px;
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .company-name {
                color: #1e3a8a;
                font-size: 28px;
                font-weight: bold;
                margin: 10px 0;
            }
            .orcamento-title {
                color: #f97316;
                font-size: 20px;
                font-weight: bold;
            }
            .cliente-section {
                background: #f8fafc;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            .section-title {
                color: #1e3a8a;
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 10px;
                border-bottom: 2px solid #1e3a8a;
                padding-bottom: 5px;
            }
            .cliente-info {
                margin: 5px 0;
                color: #333;
            }
            .itens-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .itens-table th {
                background: #f97316;
                color: white;
                padding: 12px 8px;
                text-align: center;
                font-weight: bold;
            }
            .itens-table td {
                padding: 10px 8px;
                border: 1px solid #ddd;
                text-align: center;
            }
            .itens-table tr:nth-child(even) {
                background: #f8fafc;
            }
            .itens-table tr:nth-child(odd) {
                background: white;
            }
            .produto-nome {
                text-align: left;
                font-weight: bold;
                color: #1e3a8a;
            }
            .totais {
                margin-top: 20px;
                text-align: right;
            }
            .total-line {
                margin: 5px 0;
                font-size: 14px;
            }
            .total-final {
                font-size: 18px;
                font-weight: bold;
                color: #1e3a8a;
                border-top: 2px solid #1e3a8a;
                padding-top: 10px;
                margin-top: 10px;
            }
            .observacoes {
                background: #f8fafc;
                padding: 15px;
                border-radius: 8px;
                margin-top: 20px;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #ddd;
                padding-top: 15px;
            }
            .data-info {
                margin: 10px 0;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="header">';

    if ($logoBase64) {
        $html .= '<img src="' . $logoBase64 . '" alt="N.D CONNECT" style="max-height: 80px; margin-bottom: 10px;">';
    } else {
        $html .= '<div class="logo">N.D CONNECT</div>';
    }

    $html .= '
            <div class="company-name">N.D CONNECT</div>
            <div class="orcamento-title">ORÇAMENTO Nº ' . $orcamento['numero_orcamento'] . '</div>
        </div>

        <div class="cliente-section">
            <div class="section-title">DADOS DO CLIENTE</div>
            <div class="cliente-info"><strong>Nome:</strong> ' . htmlspecialchars($orcamento['cliente_nome']) . '</div>';

    if (!empty($orcamento['email'])) {
        $html .= '<div class="cliente-info"><strong>Email:</strong> ' . htmlspecialchars($orcamento['email']) . '</div>';
    }

    if (!empty($orcamento['telefone'])) {
        $html .= '<div class="cliente-info"><strong>Telefone:</strong> ' . htmlspecialchars($orcamento['telefone']) . '</div>';
    }

    if (!empty($orcamento['endereco'])) {
        $html .= '<div class="cliente-info"><strong>Endereço:</strong> ' . htmlspecialchars($orcamento['endereco']) . '</div>';
    }

    if (!empty($orcamento['cpf_cnpj'])) {
        $html .= '<div class="cliente-info"><strong>CPF/CNPJ:</strong> ' . htmlspecialchars($orcamento['cpf_cnpj']) . '</div>';
    }

    $html .= '
        </div>

        <div class="data-info">
            <strong>Data do Orçamento:</strong> ' . date('d/m/Y', strtotime($orcamento['data_orcamento'])) . ' |
            <strong>Válido até:</strong> ' . date('d/m/Y', strtotime($orcamento['data_validade'])) . '
        </div>

        <div class="section-title">ITENS DO ORÇAMENTO</div>
        <table class="itens-table">
            <thead>
                <tr>
                    <th style="width: 50%;">PRODUTO</th>
                    <th style="width: 10%;">QTD</th>
                    <th style="width: 15%;">PREÇO UNIT.</th>
                    <th style="width: 15%;">SUBTOTAL</th>
                    <th style="width: 10%;">UNID.</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($orcamento['itens'] as $item) {
        $html .= '
                <tr>
                    <td class="produto-nome">' . htmlspecialchars($item['produto_nome']) . '</td>
                    <td>' . $item['quantidade'] . '</td>
                    <td>R$ ' . number_format($item['preco_unitario'], 2, ',', '.') . '</td>
                    <td>R$ ' . number_format($item['subtotal'], 2, ',', '.') . '</td>
                    <td>' . htmlspecialchars($item['unidade']) . '</td>
                </tr>';
    }

    $html .= '
            </tbody>
        </table>

        <div class="totais">
            <div class="total-line">
                <strong>SUBTOTAL: R$ ' . number_format($orcamento['subtotal'], 2, ',', '.') . '</strong>
            </div>';

    if ($orcamento['desconto'] > 0) {
        $html .= '<div class="total-line">DESCONTO: - R$ ' . number_format($orcamento['desconto'], 2, ',', '.') . '</div>';
    }

    $html .= '
            <div class="total-final">
                TOTAL: R$ ' . number_format($orcamento['total'], 2, ',', '.') . '
            </div>
        </div>';

    if (!empty($orcamento['observacoes'])) {
        $html .= '
        <div class="observacoes">
            <div class="section-title">OBSERVAÇÕES</div>
            <div>' . nl2br(htmlspecialchars($orcamento['observacoes'])) . '</div>
        </div>';
    }

    $html .= '
        <div class="footer">
            <div><strong>N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</strong></div>
            <div>Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED</div>
            <div>Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br</div>
        </div>
    </body>
    </html>';

    return $html;
}

// Endpoint para gerar PDF
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $orcamentoController = new OrcamentoController();
    $orcamento = $orcamentoController->getById($_GET['id']);

    if ($orcamento) {
        $html = gerarPDFSimples($orcamento);

        // Configurar headers para impressão/PDF
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
    } else {
        http_response_code(404);
        echo 'Orçamento não encontrado';
    }
} else {
    http_response_code(400);
    echo 'ID do orçamento não fornecido';
}
?>
