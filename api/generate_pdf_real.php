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

// Função para gerar PDF usando HTML2PDF (alternativa mais leve)
function generatePDF($orcamento) {
    // Verificar se mPDF está disponível
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';

        try {
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

            $html = generateOrcamentoHTML($orcamento);
            $mpdf->WriteHTML($html);

            return $mpdf->Output('orcamento_' . $orcamento['id'] . '.pdf', 'S'); // Retorna como string

        } catch (Exception $e) {
            // Se mPDF falhar, usar fallback
            return generatePDFFallback($orcamento);
        }
    } else {
        // Usar fallback se mPDF não estiver disponível
        return generatePDFFallback($orcamento);
    }
}

// Fallback: Gerar PDF usando wkhtmltopdf ou similar
function generatePDFFallback($orcamento) {
    $html = generateOrcamentoHTML($orcamento);

    // Salvar HTML temporário
    $tempFile = tempnam(sys_get_temp_dir(), 'orcamento_') . '.html';
    file_put_contents($tempFile, $html);

    // Tentar usar wkhtmltopdf se disponível
    $pdfFile = tempnam(sys_get_temp_dir(), 'orcamento_') . '.pdf';
    $command = "wkhtmltopdf --page-size A4 --margin-top 0.75in --margin-right 0.75in --margin-bottom 0.75in --margin-left 0.75in \"$tempFile\" \"$pdfFile\" 2>/dev/null";

    exec($command, $output, $return);

    if ($return === 0 && file_exists($pdfFile)) {
        $pdfContent = file_get_contents($pdfFile);
        unlink($tempFile);
        unlink($pdfFile);
        return $pdfContent;
    }

    // Se wkhtmltopdf não estiver disponível, usar uma solução mais simples
    return generateSimplePDF($orcamento);
}

// Solução mais simples: Gerar PDF básico usando FPDF
function generateSimplePDF($orcamento) {
    // Esta é uma implementação básica - você pode expandir conforme necessário
    $dataOrcamento = date('d/m/Y', strtotime($orcamento['data_orcamento']));
    $dataValidade = date('d/m/Y', strtotime($orcamento['data_validade']));

    // Por enquanto, vamos retornar o HTML para download
    // Em produção, você pode implementar FPDF ou similar
    return generateOrcamentoHTML($orcamento);
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
        <title>Orçamento N.D Connect - ' . $orcamento['id'] . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: white;
                font-size: 12px;
                line-height: 1.4;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
            }
            .header {
                background: linear-gradient(135deg, #0C2B59 0%, #E8622D 100%);
                color: white;
                padding: 20px;
                text-align: center;
                border-radius: 10px 10px 0 0;
                margin-bottom: 0;
            }
            .logo {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .orcamento-number {
                font-size: 16px;
                opacity: 0.9;
            }
            .content {
                padding: 20px;
                border: 1px solid #ddd;
                border-top: none;
            }
            .section {
                margin-bottom: 20px;
            }
            .section-title {
                background: #0C2B59;
                color: white;
                padding: 8px 12px;
                margin: 0 0 10px 0;
                font-weight: bold;
                border-radius: 4px;
                font-size: 14px;
            }
            .cliente-info {
                display: table;
                width: 100%;
                margin-bottom: 15px;
            }
            .info-row {
                display: table-row;
            }
            .info-cell {
                display: table-cell;
                padding: 5px 10px 5px 0;
                width: 50%;
                vertical-align: top;
            }
            .info-label {
                font-weight: bold;
                color: #0C2B59;
                font-size: 11px;
            }
            .info-value {
                color: #333;
                font-size: 11px;
            }
            .dates {
                display: table;
                width: 100%;
                margin-top: 15px;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
                font-size: 10px;
            }
            .table th {
                background: #E8622D;
                color: white;
                padding: 8px;
                text-align: left;
                font-weight: bold;
                font-size: 10px;
            }
            .table td {
                padding: 6px 8px;
                border-bottom: 1px solid #ddd;
                font-size: 10px;
            }
            .table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .totals {
                margin-top: 15px;
                text-align: right;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                padding: 5px 0;
                border-bottom: 1px solid #ddd;
                font-size: 11px;
            }
            .total-final {
                font-size: 14px;
                font-weight: bold;
                color: #0C2B59;
                border-top: 2px solid #0C2B59;
                padding-top: 8px;
                margin-top: 8px;
            }
            .observacoes {
                background: #f8f9fa;
                padding: 10px;
                border-radius: 4px;
                border-left: 4px solid #E8622D;
                margin-top: 15px;
                font-size: 11px;
            }
            .footer {
                background: #f8f9fa;
                padding: 15px;
                text-align: center;
                color: #666;
                font-size: 10px;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 10px 10px;
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
                        <div class="info-row">
                            <div class="info-cell">
                                <span class="info-label">Nome:</span><br>
                                <span class="info-value">' . htmlspecialchars($orcamento['cliente_nome']) . '</span>
                            </div>
                            <div class="info-cell">
                                <span class="info-label">Email:</span><br>
                                <span class="info-value">' . htmlspecialchars($orcamento['email']) . '</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell">
                                <span class="info-label">Telefone:</span><br>
                                <span class="info-value">' . htmlspecialchars($orcamento['telefone']) . '</span>
                            </div>
                            <div class="info-cell">
                                <span class="info-label">CPF/CNPJ:</span><br>
                                <span class="info-value">' . htmlspecialchars($orcamento['cpf_cnpj']) . '</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell">
                                <span class="info-label">Endereço:</span><br>
                                <span class="info-value">' . htmlspecialchars($orcamento['endereco']) . '</span>
                            </div>
                            <div class="info-cell">
                                <span class="info-label">Data do Orçamento:</span><br>
                                <span class="info-value">' . $dataOrcamento . '</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell">
                                <span class="info-label">Válido até:</span><br>
                                <span class="info-value">' . $dataValidade . '</span>
                            </div>
                            <div class="info-cell">
                                <!-- Espaço vazio para alinhamento -->
                            </div>
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

// Gerar PDF
$pdfContent = generatePDF($orcamento);

// Configurar headers para download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="orcamento_' . $orcamentoId . '.pdf"');
header('Content-Length: ' . strlen($pdfContent));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Enviar conteúdo do PDF
echo $pdfContent;
?>
