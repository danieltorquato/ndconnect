<?php
// Gerador de PDF usando mPDF
error_reporting(0);
ini_set('display_errors', 0);

// Verificar se o mPDF está disponível
if (!class_exists('Mpdf\Mpdf')) {
    // Tentar carregar via autoload do Composer
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    } else {
        die('mPDF não encontrado. Execute: composer install');
    }
}

use Mpdf\Mpdf;

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

// Função para converter imagem para base64
function getLogoBase64() {
    $logoPath = 'https://ndconnect.torquatoit.com/assets/img/logo.jpeg';

    // Tentar carregar a imagem da URL
    $imageData = @file_get_contents($logoPath);

    if ($imageData !== false && !empty($imageData)) {
        $base64 = base64_encode($imageData);
        return 'data:image/jpeg;base64,' . $base64;
    }

    // Fallback: tentar carregar do caminho local
    $localPath = __DIR__ . '/../src/assets/img/logo.jpeg';
    if (file_exists($localPath)) {
        $imageData = file_get_contents($localPath);
        if ($imageData !== false) {
            $base64 = base64_encode($imageData);
            return 'data:image/jpeg;base64,' . $base64;
        }
    }

    return null;
}

try {
    // Iniciar buffer de output
    ob_start();

    // Verificar se o ID foi fornecido
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die('ID do orçamento não fornecido');
    }

    $orcamentoId = (int)$_GET['id'];
    $orcamento = getOrcamentoData($orcamentoId);

    if (!$orcamento) {
        die('Orçamento não encontrado');
    }

    // Obter logo em base64
    $logoBase64 = getLogoBase64();

        // Criar instância do mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'tempDir' => sys_get_temp_dir(),
            'debug' => false,
            'allow_charset_conversion' => false,
            'autoScriptToLang' => false,
            'autoLangToFont' => false
        ]);

    // Configurar metadados
    $mpdf->SetTitle('Orçamento N.D Connect - ' . $orcamentoId);
    $mpdf->SetAuthor('N.D Connect');
    $mpdf->SetCreator('N.D Connect');
    $mpdf->SetSubject('Orçamento de Equipamentos para Eventos');
    $mpdf->SetKeywords('orçamento, eventos, equipamentos, N.D Connect');

    // HTML do orçamento - Layout idêntico ao simple_pdf.php
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Orçamento - ' . $orcamento['numero_orcamento'] . '</title>
        <style>
            /* Reset e base */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                line-height: 1.4;
                color: #333;
                background: white;
                padding: 0;
            }

            /* Container principal */
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                overflow: hidden;
            }

            /* Header com logo circular */
            .header {
                text-align: center;
                padding: 0px 0;
                background: white;
            }

            .logo-container {
                display: inline-block;
                width: 100px;
                height: 100px;
                background: #0C2B59;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 15px;
                position: relative;
            }

            .logo-container::after {
                content: "";
                position: absolute;
                top: -5px;
                right: -5px;
                width: 20px;
                height: 20px;
                background: #E8622D;
                border-radius: 50%;
            }

            .logo-text {
                color: white;
                font-weight: bold;
                font-size: 12px;
                text-align: center;
                line-height: 1.2;
            }

            .logo {
                max-width: 100px;
                height: auto;
                border-radius: 50%;
            }

            /* Faixas azuis */
            .blue-bar {
                background: #0C2B59;
                color: white;
                padding: 12px 20px;
                text-align: center;
                font-weight: bold;
                font-size: 16px;
                text-transform: uppercase;
            }

            .blue-bar.left {
                text-align: left;
                font-size: 14px;
            }

            .blue-bar.small {
                font-size: 12px;
                padding: 8px 20px;
            }

            /* Seção de dados do cliente */
            .cliente-section {
                background: white;
                padding: 20px;
            }

            .cliente-table {
                width: 100%;
                margin-top: 10px;
            }

            .cliente-label {
                font-size: 11px;
                color: #666;
                font-weight: 500;
                margin-bottom: 3px;
                text-transform: uppercase;
            }

            .cliente-value {
                font-size: 13px;
                color: #333;
                font-weight: 400;
            }


            /* Seção de datas */
            .datas-section {
                background: #F8FAFC;
                padding: 15px 20px;
                display: flex;
                justify-content: space-between;
            }

            .data-item {
                text-align: center;
                flex: 1;
            }

            .data-label {
                font-size: 11px;
                color: #666;
                font-weight: 500;
                margin-bottom: 5px;
                text-transform: uppercase;
            }

            .data-value {
                font-size: 14px;
                color: #333;
                font-weight: bold;
            }

            /* Seção de itens */
            .itens-section {
                background: white;
            }

            .orange-bar {
                background: #E8622D;
                color: white;
                padding: 12px 20px;
                font-weight: bold;
                font-size: 14px;
                text-transform: uppercase;
            }

            /* Tabela de itens */
            .itens-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
            }

            .itens-table th {
                background: #0C2B59;
                color: white;
                padding: 12px 8px;
                text-align: left;
                font-weight: bold;
                font-size: 12px;
                text-transform: uppercase;
            }

            .itens-table th.center {
                text-align: center;
            }

            .itens-table td {
                padding: 10px 8px;
                border-bottom: 1px solid #eee;
                font-size: 14px;
                background: white;
            }

            .itens-table td.center {
                text-align: center;
            }

            .itens-table td.right {
                text-align: right;
            }

            .produto-nome {
                font-weight: 600;
                color: #333;
                font-size: 15px;
            }

            /* Seção de totais */
            .totais-section {
                background: white;
                padding: 20px;
            }

            .total-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 5px 0;
                font-size: 13px;
            }

            .total-label {
                color: #666;
                font-weight: 400;
            }

            .total-value {
                color: #333;
                font-weight: 500;
                float: right;
            }

            .total-separator {
                height: 2px;
                background: #E8622D;
                margin: 10px 0;
            }

            .total-final {
                font-size: 16px;
                font-weight: bold;
                color: #E8622D;
            }

            /* Observações */
            .observacoes-section {
                background: #FFF8DC;
                padding: 15px 20px;
                margin: 0;
            }

            .observacoes-title {
                font-size: 12px;
                font-weight: bold;
                color: #0C2B59;
                margin-bottom: 8px;
                text-transform: uppercase;
            }

            .observacoes-text {
                font-size: 12px;
                color: #333;
                line-height: 1.4;
            }

            /* Footer */
            .footer {
                background: #0C2B59;
                color: white;
                padding: 20px;
                text-align: center;
            }

            .footer-title {
                font-size: 14px;
                font-weight: bold;
                margin-bottom: 8px;
                text-transform: uppercase;
            }

            .footer-specialization {
                font-size: 11px;
                margin-bottom: 8px;
                opacity: 0.9;
            }

            .footer-contact {
                font-size: 10px;
                opacity: 0.8;
            }

            /* Print styles */
            @media print {
                .blue-bar {
                    background: #0C2B59 !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }

                .orange-bar {
                    background: #E8622D !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }

                .footer {
                    background: #0C2B59 !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header com logo -->
            <div class="header">
                ' . ($logoBase64 ? '<img src="' . $logoBase64 . '" alt="N.D Connect Logo" class="logo">' : '
                <div class="logo-container">
                    <div class="logo-text">N.D<br>CONNECT</div>
                </div>
                ') . '
            </div>

            <!-- Faixa principal -->
            <div class="blue-bar">
   ORÇAMENTO N° ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '
            </div>

            <!-- Dados do cliente -->
            <div class="blue-bar left">
                DADOS DO CLIENTE
            </div>
            <div class="cliente-section">
                <table class="cliente-table" cellpadding="10" cellspacing="0" border="0">
                    <tr>
                        <td width="50%" style="vertical-align: top;">
                            <div class="cliente-label">NOME</div>
                            <div class="cliente-value">' . htmlspecialchars((empty($orcamento['cliente_nome']) || trim($orcamento['cliente_nome']) === '') ? 'Não Informado' : $orcamento['cliente_nome']) . '</div>
                        </td>
                        <td width="50%" style="vertical-align: top;">
                            <div class="cliente-label">E-MAIL</div>
                            <div class="cliente-value">' . htmlspecialchars((empty($orcamento['email']) || trim($orcamento['email']) === '') ? 'Não Informado' : $orcamento['email']) . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%" style="vertical-align: top;">
                            <div class="cliente-label">TELEFONE</div>
                            <div class="cliente-value">' . htmlspecialchars((empty($orcamento['telefone']) || trim($orcamento['telefone']) === '') ? 'Não Informado' : $orcamento['telefone']) . '</div>
                        </td>
                        <td width="50%" style="vertical-align: top;">
                            <div class="cliente-label">CPF/CNPJ</div>
                            <div class="cliente-value">' . htmlspecialchars((empty($orcamento['cpf_cnpj']) || trim($orcamento['cpf_cnpj']) === '') ? 'Não Informado' : $orcamento['cpf_cnpj']) . '</div>
                        </td>
                    </tr>
                </table>';

    if (!empty($orcamento['endereco'])) {
        $html .= '
                </table>
                <div style="margin-top: 10px;">
                    <div class="cliente-label">ENDEREÇO</div>
                    <div class="cliente-value">' . htmlspecialchars($orcamento['endereco']) . '</div>
                </div>';
    } else {
        $html .= '
                </table>';
    }

    $html .= '
            </div>

            <!-- Datas -->
            <div class="datas-section">
                <div class="data-item">
                    <div class="data-label">DATA DO ORÇAMENTO</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($orcamento['data_orcamento'])) . '</div>
                </div>
                <div class="data-item">
                    <div class="data-label">VÁLIDO ATÉ</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($orcamento['data_validade'])) . '</div>
                </div>
            </div>

            <!-- Itens do orçamento -->
            <div class="orange-bar">
                ITENS DO ORÇAMENTO
            </div>
            <div class="itens-section">
                <table class="itens-table">
                    <thead>
                        <tr>
                            <th>PRODUTO</th>
                            <th class="center">QTD</th>
                            <th class="center">PREÇO UNIT.</th>
                            <th class="center">SUBTOTAL</th>
                            <th class="center">UNID.</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($orcamento['itens'] as $item) {
            $subtotal_final = isset($item['subtotal_com_desconto']) && $item['subtotal_com_desconto'] > 0 ? $item['subtotal_com_desconto'] : $item['subtotal'];
            $tem_desconto = isset($item['desconto_porcentagem']) && $item['desconto_porcentagem'] > 0 || isset($item['desconto_valor']) && $item['desconto_valor'] > 0;

            $html .= '
                            <tr>
                                <td>
                                    <div class="produto-nome">' . htmlspecialchars($item['produto_nome']) . '</div>';

            if ($tem_desconto) {
                $html .= '
                                    <div style="font-size: 10px; color: #666; margin-top: 2px;">';
                if (isset($item['desconto_porcentagem']) && $item['desconto_porcentagem'] > 0) {
                    $html .= 'Desconto: ' . $item['desconto_porcentagem'] . '%';
                } elseif (isset($item['desconto_valor']) && $item['desconto_valor'] > 0) {
                    $html .= 'Desconto: R$ ' . number_format($item['desconto_valor'], 2, ',', '.');
                }
                $html .= '</div>';
            }

            $html .= '
                                </td>
                                <td class="center">' . $item['quantidade'] . '</td>
                                <td class="right">R$ ' . number_format($item['preco_unitario'], 2, ',', '.') . '</td>';

            if ($tem_desconto) {
                $html .= '
                                <td class="right">
                                    <div style="text-decoration: line-through; color: #999; font-size: 11px;">R$ ' . number_format($item['subtotal'], 2, ',', '.') . '</div>
                                    <div style="color: #2dd36f; font-weight: bold;">R$ ' . number_format($subtotal_final, 2, ',', '.') . '</div>
                                </td>';
            } else {
                $html .= '
                                <td class="right">R$ ' . number_format($subtotal_final, 2, ',', '.') . '</td>';
            }

            $html .= '
                                <td class="center">' . htmlspecialchars($item['unidade']) . '</td>
                            </tr>';
        }

    $html .= '
                    </tbody>
                </table>
            </div>

            <!-- Totais -->
            <div class="totais-section">';

        // Calcular subtotal real considerando descontos dos itens
        $subtotal_real = 0;
        foreach ($orcamento['itens'] as $item) {
            $subtotal_real += isset($item['subtotal_com_desconto']) && $item['subtotal_com_desconto'] > 0 ? $item['subtotal_com_desconto'] : $item['subtotal'];
        }

        $html .= '
                <div class="total-item">
                    <span class="total-label">SUBTOTAL:</span>
                    <span class="total-value">R$ ' . number_format($subtotal_real, 2, ',', '.') . '</span>
                </div>';

        if ($orcamento['desconto'] > 0) {
            $desconto_texto = '';
            if (isset($orcamento['desconto_tipo']) && $orcamento['desconto_tipo'] === 'porcentagem') {
                $desconto_texto = $orcamento['desconto'] . '%';
            } else {
                $desconto_texto = 'R$ ' . number_format($orcamento['desconto'], 2, ',', '.');
            }

            $html .= '
                <div class="total-item">
                    <span class="total-label">DESCONTO:</span>
                    <span class="total-value">- ' . $desconto_texto . '</span>
                </div>';
        }

    $html .= '
                <div class="total-separator"></div>
                <div class="total-item total-final">
                    <span class="total-label">TOTAL:</span>
                    <span class="total-value">R$ ' . number_format($orcamento['total'], 2, ',', '.') . '</span>
                </div>
            </div>';

    if (!empty($orcamento['observacoes'])) {
        $html .= '
            <!-- Observações -->
            <div class="observacoes-section">
                <div class="observacoes-title">OBSERVAÇÕES</div>
                <div class="observacoes-text">' . nl2br(htmlspecialchars($orcamento['observacoes'])) . '</div>
            </div>';
    }

    $html .= '
            <!-- Footer -->
            <div class="footer">
                <div class="footer-title">N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</div>
                <div class="footer-specialization">Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED</div>
                <div class="footer-contact">Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br</div>
            </div>
        </div>
    </body>
    </html>';

        // Limpar buffer de output
        ob_clean();

        // Adicionar HTML ao mPDF
        $mpdf->WriteHTML($html);

        // Definir nome do arquivo
        $filename = 'Orçamento N° ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '.pdf';

        // Gerar PDF
        $mpdf->Output($filename, 'D');

} catch (Exception $e) {
    error_log('Erro no PDF mPDF: ' . $e->getMessage());
    header('Content-Type: text/html');
    die('Erro ao gerar PDF: ' . $e->getMessage());
}

