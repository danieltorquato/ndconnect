<?php
require_once 'Config/Database.php';

// Função para obter dados do orçamento
function getOrcamentoData($id) {
    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT o.*, c.nome as cliente_nome, c.email, c.telefone, c.endereco, c.cpf_cnpj
              FROM orcamentos o
              LEFT JOIN clientes c ON o.cliente_id = c.id
              WHERE o.id = :id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orcamento) {
        return null;
    }

    // Buscar itens do orçamento
    $queryItens = "SELECT oi.*, p.nome as produto_nome, p.descricao, p.unidade
                   FROM orcamento_itens oi
                   LEFT JOIN produtos p ON oi.produto_id = p.id
                   WHERE oi.orcamento_id = :orcamento_id";

    $stmtItens = $conn->prepare($queryItens);
    $stmtItens->bindParam(':orcamento_id', $id);
    $stmtItens->execute();

    $orcamento['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    return $orcamento;
}

// Função para gerar HTML do orçamento
function generateOrcamentoHTML($orcamento) {
    $dataOrcamento = date('d/m/Y', strtotime($orcamento['data_orcamento']));
    $dataValidade = date('d/m/Y', strtotime($orcamento['data_validade']));

    $html = '
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Orçamento N.D Connect - ' . $orcamento['id'] . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #0C2B59 0%, #E8622D 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .logo {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .orcamento-number {
                font-size: 18px;
                opacity: 0.9;
            }
            .content {
                padding: 30px;
            }
            .section {
                margin-bottom: 30px;
            }
            .section-title {
                background: #0C2B59;
                color: white;
                padding: 10px 15px;
                margin: 0 0 15px 0;
                font-weight: bold;
                border-radius: 5px;
            }
            .cliente-info {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 20px;
            }
            .info-item {
                margin-bottom: 10px;
            }
            .info-label {
                font-weight: bold;
                color: #0C2B59;
            }
            .info-value {
                color: #333;
            }
            .dates {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-top: 20px;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            .table th {
                background: #E8622D;
                color: white;
                padding: 12px;
                text-align: left;
                font-weight: bold;
            }
            .table td {
                padding: 12px;
                border-bottom: 1px solid #ddd;
            }
            .table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .totals {
                margin-top: 20px;
                text-align: right;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #ddd;
            }
            .total-final {
                font-size: 18px;
                font-weight: bold;
                color: #0C2B59;
                border-top: 2px solid #0C2B59;
                padding-top: 10px;
                margin-top: 10px;
            }
            .observacoes {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                border-left: 4px solid #E8622D;
                margin-top: 20px;
            }
            .footer {
                background: #f8f9fa;
                padding: 20px;
                text-align: center;
                color: #666;
                font-size: 14px;
            }
            .share-buttons {
                margin-top: 20px;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 10px;
                text-align: center;
            }
            .share-button {
                display: inline-block;
                margin: 5px;
                padding: 12px 24px;
                background: #0C2B59;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                transition: background 0.3s;
            }
            .share-button:hover {
                background: #E8622D;
            }
            .whatsapp-btn {
                background: #25D366;
            }
            .download-btn {
                background: #0C2B59;
            }
            .share-btn {
                background: #6c757d;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">N.D CONNECT</div>
                <div class="orcamento-number">ORÇAMENTO N° ' . str_pad($orcamento['id'], 6, '0', STR_PAD_LEFT) . '</div>
            </div>

            <div class="content">
                <div class="section">
                    <h3 class="section-title">DADOS DO CLIENTE</h3>
                    <div class="cliente-info">
                        <div>
                            <div class="info-item">
                                <span class="info-label">Nome:</span>
                                <span class="info-value">' . htmlspecialchars($orcamento['cliente_nome']) . '</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value">' . htmlspecialchars($orcamento['email']) . '</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Endereço:</span>
                                <span class="info-value">' . htmlspecialchars($orcamento['endereco']) . '</span>
                            </div>
                        </div>
                        <div>
                            <div class="info-item">
                                <span class="info-label">Telefone:</span>
                                <span class="info-value">' . htmlspecialchars($orcamento['telefone']) . '</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">CPF/CNPJ:</span>
                                <span class="info-value">' . htmlspecialchars($orcamento['cpf_cnpj']) . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="dates">
                        <div class="info-item">
                            <span class="info-label">Data do Orçamento:</span>
                            <span class="info-value">' . $dataOrcamento . '</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Válido até:</span>
                            <span class="info-value">' . $dataValidade . '</span>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3 class="section-title">ITENS DO ORÇAMENTO</h3>
                    <table class="table">
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
        $precoUnitario = number_format($item['preco_unitario'], 2, ',', '.');
        $subtotal = number_format($item['subtotal'], 2, ',', '.');

        $html .= '
                            <tr>
                                <td>' . htmlspecialchars($item['produto_nome']) . '</td>
                                <td>' . $item['quantidade'] . '</td>
                                <td>R$ ' . $precoUnitario . '</td>
                                <td>R$ ' . $subtotal . '</td>
                                <td>' . htmlspecialchars($item['unidade']) . '</td>
                            </tr>';
    }

    $subtotal = number_format($orcamento['subtotal'], 2, ',', '.');
    $desconto = number_format($orcamento['desconto'], 2, ',', '.');
    $total = number_format($orcamento['total'], 2, ',', '.');

    $html .= '
                        </tbody>
                    </table>

                    <div class="totals">
                        <div class="total-row">
                            <span>SUBTOTAL:</span>
                            <span>R$ ' . $subtotal . '</span>
                        </div>';

    if ($orcamento['desconto'] > 0) {
        $html .= '
                        <div class="total-row">
                            <span>DESCONTO:</span>
                            <span>- R$ ' . $desconto . '</span>
                        </div>';
    }

    $html .= '
                        <div class="total-row total-final">
                            <span>TOTAL:</span>
                            <span>R$ ' . $total . '</span>
                        </div>
                    </div>
                </div>';

    if (!empty($orcamento['observacoes'])) {
        $html .= '
                <div class="observacoes">
                    <strong>Observações:</strong><br>
                    ' . nl2br(htmlspecialchars($orcamento['observacoes'])) . '
                </div>';
    }

    $html .= '
                <div class="share-buttons">
                    <h4>Compartilhar Orçamento</h4>
                    <a href="https://wa.me/?text=' . urlencode('Orçamento N.D Connect - ' . $orcamento['id'] . '%0AValor: R$ ' . $total . '%0AVisualizar: ' . $_SERVER['HTTP_HOST'] . '/simple_pdf.php?id=' . $orcamento['id']) . '" class="share-button whatsapp-btn" target="_blank">
                        📱 WhatsApp
                    </a>
                    <a href="download_pdf.php?id=' . $orcamento['id'] . '" class="share-button download-btn">
                        📄 Download PDF
                    </a>
                    <a href="javascript:void(0)" onclick="navigator.share ? navigator.share({title: \'Orçamento N.D Connect\', text: \'Orçamento N.D Connect - ' . $orcamento['id'] . '\', url: window.location.href}) : alert(\'Compartilhamento nativo não suportado\')" class="share-button share-btn">
                        🔗 Compartilhar
                    </a>
                </div>
            </div>

            <div class="footer">
                <strong>N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</strong><br>
                Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED<br>
                Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br
            </div>
        </div>
    </body>
    </html>';

    return $html;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID do orçamento não fornecido');
}

$orcamentoId = (int)$_GET['id'];
$orcamento = getOrcamentoData($orcamentoId);

if (!$orcamento) {
    die('Orçamento não encontrado');
}

// Gerar HTML
$html = generateOrcamentoHTML($orcamento);

// Se for para download, usar mPDF
if (isset($_GET['download']) && $_GET['download'] == '1') {
    // Incluir mPDF se disponível
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output('orcamento_' . $orcamentoId . '.pdf', 'D');
        exit;
    } else {
        // Fallback: usar wkhtmltopdf se disponível
        $tempFile = tempnam(sys_get_temp_dir(), 'orcamento_') . '.html';
        file_put_contents($tempFile, $html);

        $pdfFile = tempnam(sys_get_temp_dir(), 'orcamento_') . '.pdf';
        $command = "wkhtmltopdf --page-size A4 --margin-top 0.75in --margin-right 0.75in --margin-bottom 0.75in --margin-left 0.75in \"$tempFile\" \"$pdfFile\"";

        exec($command, $output, $return);

        if ($return === 0 && file_exists($pdfFile)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="orcamento_' . $orcamentoId . '.pdf"');
            readfile($pdfFile);
            unlink($tempFile);
            unlink($pdfFile);
            exit;
        } else {
            // Fallback final: mostrar HTML
            echo $html;
        }
    }
} else {
    // Mostrar HTML normal
    echo $html;
}
?>
