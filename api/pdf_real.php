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

    $query = "SELECT o.*, c.nome as cliente_nome, c.email, c.telefone, c.endereco, c.cpf_cnpj, c.empresa
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

    // Buscar itens do orçamento com suporte a produtos customizados
    $queryItens = "SELECT oi.*,
                          CASE
                            WHEN oi.produto_customizado = 1 THEN oi.nome_customizado
                            ELSE p.nome
                          END as produto_nome,
                          CASE
                            WHEN oi.produto_customizado = 1 THEN oi.unidade_customizada
                            ELSE p.unidade
                          END as unidade,
                          p.descricao
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

// Função para gerar HTML de múltiplos shows
function generateMultiShowHTML($orcamento, $shows, $logoBase64) {
    $html = '';

    foreach ($shows as $index => $show) {
        $showNumber = $index + 1;
        $isLastShow = ($index === count($shows) - 1);

        // Processar múltiplas datas do evento
        $datasEvento = [];
        if (isset($show['datasEvento']) && is_array($show['datasEvento'])) {
            foreach ($show['datasEvento'] as $data) {
                $datasEvento[] = date('d/m', strtotime($data));
            }
        }

        $dataEventoFormatada = '';
        if (count($datasEvento) === 1) {
            $dataEventoFormatada = $datasEvento[0];
        } else if (count($datasEvento) === 2) {
            $dataEventoFormatada = $datasEvento[0] . ' e ' . $datasEvento[1];
        } else if (count($datasEvento) > 2) {
            $ultimaData = array_pop($datasEvento);
            $dataEventoFormatada = implode(', ', $datasEvento) . ' e ' . $ultimaData;
        } else {
            $dataEventoFormatada = '-';
        }

        $nomeEvento = isset($show['nome']) ? $show['nome'] : 'Evento';
        $nomeExibicao = !empty($orcamento['empresa']) ? $orcamento['empresa'] : $orcamento['cliente_nome'];

        $html .= '
        <div class="show-page">
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
                ORÇAMENTO N° ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . ' - SHOW ' . $showNumber . '
            </div>

            <!-- Dados do cliente -->
            <div class="blue-bar left">
                DADOS DO CLIENTE
            </div>
            <div class="cliente-section">
                <table class="cliente-table" cellpadding="10" cellspacing="0" border="0">
                    <tr>
                        <td width="50%" style="vertical-align: top;">
                            <div class="cliente-label">EMPRESA</div>
                            <div class="cliente-value">' . htmlspecialchars($nomeExibicao) . '</div>
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
                </table>
            </div>

            <!-- Datas -->
            <div class="datas-section">
                <div class="data-item">
                    <div class="data-label">DATA DO ORÇAMENTO</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($orcamento['data_orcamento'])) . '</div>
                </div>
                <div class="data-item">
                    <div class="data-label">NOME DO EVENTO</div>
                    <div class="data-value">' . htmlspecialchars($nomeEvento) . '</div>
                </div>
                <div class="data-item">
                    <div class="data-label">DATA DO EVENTO</div>
                    <div class="data-value">' . $dataEventoFormatada . '</div>
                </div>
            </div>

            <!-- Itens do orçamento -->
            <div class="orange-bar">
                ITENS DO ORÇAMENTO - SHOW ' . $showNumber . '
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

        foreach ($show['itensOrcamento'] as $item) {
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
        foreach ($show['itensOrcamento'] as $item) {
            $subtotal_real += isset($item['subtotal_com_desconto']) && $item['subtotal_com_desconto'] > 0 ? $item['subtotal_com_desconto'] : $item['subtotal'];
        }

        $html .= '
                <div class="total-item">
                    <span class="total-label">SUBTOTAL:</span>
                    <span class="total-value">R$ ' . number_format($subtotal_real, 2, ',', '.') . '</span>
                </div>';

        if ($show['desconto'] > 0) {
            $desconto_texto = '';
            if (isset($show['descontoTipo']) && $show['descontoTipo'] === 'porcentagem') {
                $desconto_texto = $show['desconto'] . '%';
            } else {
                $desconto_texto = 'R$ ' . number_format($show['desconto'], 2, ',', '.');
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
                    <span class="total-label">TOTAL SHOW ' . $showNumber . ':</span>
                    <span class="total-value">R$ ' . number_format($show['total'], 2, ',', '.') . '</span>
                </div>
            </div>';

        if (!empty($show['observacoes'])) {
            $html .= '
            <!-- Observações -->
            <div class="observacoes-section">
                <div class="observacoes-title">OBSERVAÇÕES - SHOW ' . $showNumber . '</div>
                <div class="observacoes-text">' . nl2br(htmlspecialchars($show['observacoes'])) . '</div>
            </div>';
        }

        // Adicionar quebra de página se não for o último show
        if (!$isLastShow) {
            $html .= '<div style="page-break-before: always;"></div>';
        }
    }

    // Adicionar página final com total geral se houver múltiplos shows
    if (count($shows) > 1) {
        $totalGeral = 0;
        foreach ($shows as $show) {
            $totalGeral += $show['total'];
        }

        $html .= '<div style="page-break-before: always;"></div>';
        $html .= '
        <div class="show-page">
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
                RESUMO GERAL - ORÇAMENTO N° ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '
            </div>

            <!-- Dados do cliente -->
            <div class="blue-bar left">
                DADOS DO CLIENTE
            </div>
            <div class="cliente-section">
                <table class="cliente-table" cellpadding="10" cellspacing="0" border="0">
                    <tr>
                        <td width="50%" style="vertical-align: top;">
                            <div class="cliente-label">EMPRESA</div>
                            <div class="cliente-value">' . htmlspecialchars($nomeExibicao) . '</div>
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
                </table>
            </div>

            <!-- Datas -->
            <div class="datas-section">
                <div class="data-item">
                    <div class="data-label">DATA DO ORÇAMENTO</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($orcamento['data_orcamento'])) . '</div>
                </div>
                <div class="data-item">
                    <div class="data-label">QUANTIDADE DE SHOWS</div>
                    <div class="data-value">' . count($shows) . '</div>
                </div>
            </div>

            <!-- Resumo dos shows -->
            <div class="orange-bar">
                RESUMO POR SHOW
            </div>
            <div class="itens-section">
                <table class="itens-table">
                    <thead>
                        <tr>
                            <th>SHOW</th>
                            <th class="center">NOME DO EVENTO</th>
                            <th class="center">DATAS</th>
                            <th class="center">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($shows as $index => $show) {
            $showNumber = $index + 1;
            $datasEvento = [];
            if (isset($show['datasEvento']) && is_array($show['datasEvento'])) {
                foreach ($show['datasEvento'] as $data) {
                    $datasEvento[] = date('d/m', strtotime($data));
                }
            }

            $dataEventoFormatada = '';
            if (count($datasEvento) === 1) {
                $dataEventoFormatada = $datasEvento[0];
            } else if (count($datasEvento) === 2) {
                $dataEventoFormatada = $datasEvento[0] . ' e ' . $datasEvento[1];
            } else if (count($datasEvento) > 2) {
                $ultimaData = array_pop($datasEvento);
                $dataEventoFormatada = implode(', ', $datasEvento) . ' e ' . $ultimaData;
            } else {
                $dataEventoFormatada = '-';
            }

            $nomeEvento = isset($show['nome']) ? $show['nome'] : 'Evento';

            $html .= '
                            <tr>
                                <td>
                                    <div class="produto-nome">Show ' . $showNumber . '</div>
                                </td>
                                <td class="center">' . htmlspecialchars($nomeEvento) . '</td>
                                <td class="center">' . $dataEventoFormatada . '</td>
                                <td class="right">R$ ' . number_format($show['total'], 2, ',', '.') . '</td>
                            </tr>';
        }

        $html .= '
                    </tbody>
                </table>
            </div>

            <!-- Total Geral -->
            <div class="totais-section">
                <div class="total-separator"></div>
                <div class="total-item total-final" style="font-size: 20px; padding: 20px 0;">
                    <span class="total-label">TOTAL GERAL (TODOS OS SHOWS):</span>
                    <span class="total-value">R$ ' . number_format($totalGeral, 2, ',', '.') . '</span>
                </div>
            </div>';

        if (!empty($orcamento['observacoes'])) {
            $html .= '
            <!-- Observações Gerais -->
            <div class="observacoes-section">
                <div class="observacoes-title">OBSERVAÇÕES GERAIS</div>
                <div class="observacoes-text">' . nl2br(htmlspecialchars($orcamento['observacoes'])) . '</div>
            </div>';
        }

        $html .= '</div>';
    }

    return $html;
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

    // Verificar se há múltiplos shows
    $shows = [];
    if (isset($orcamento['quantidade_shows']) && $orcamento['quantidade_shows'] > 1) {
        // Decodificar shows do JSON
        $shows = json_decode($orcamento['shows'], true);
        if (!$shows) {
            $shows = [];
        }
    } else {
        // Show único - converter dados atuais para formato de show
        $shows = [[
            'id' => 1,
            'nome' => $orcamento['nome_evento'] ?: 'Evento',
            'datasEvento' => json_decode($orcamento['data_evento'], true) ?: [],
            'itensOrcamento' => $orcamento['itens'],
            'observacoes' => $orcamento['observacoes'],
            'subtotal' => $orcamento['subtotal'],
            'desconto' => $orcamento['desconto'],
            'descontoTipo' => $orcamento['desconto_tipo'],
            'total' => $orcamento['total']
        ]];
    }

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
                margin-bottom: 10px;
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
            ' . generateMultiShowHTML($orcamento, $shows, $logoBase64) . '

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

        // Verificar se é para visualizar na web ou baixar
        $action = $_GET['action'] ?? 'view'; // 'view' para visualizar, 'download' para baixar

        if ($action === 'download') {
            // Gerar PDF para download
            $mpdf->Output($filename, 'D');
        } else {
            // Gerar PDF para visualização na web com botão de download
            $pdfContent = $mpdf->Output($filename, 'S'); // 'S' para obter o conteúdo como string

            // HTML para mostrar o PDF com botão de download
            $downloadUrl = "download_pdf_completo.php?id=" . $orcamentoId;

            echo '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Visualizar PDF - Orçamento ' . $orcamento['numero_orcamento'] . '</title>
                <style>
                    body { margin: 0; padding: 20px; background: #f5f5f5; font-family: Arial, sans-serif; }
                    .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                    .header h1 { margin: 0; color: #0C2B59; }
                    .header p { margin: 10px 0 0 0; color: #666; }
                    .buttons { margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap; }
                    .btn {
                        padding: 12px 24px;
                        border: none;
                        border-radius: 6px;
                        font-size: 16px;
                        cursor: pointer;
                        text-decoration: none;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        transition: all 0.3s;
                    }
                    .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
                    .btn-download { background: #E8622D; color: white; }
                    .btn-download:hover { background: #d55a26; }
                    .btn-whatsapp { background: #25D366; color: white; }
                    .btn-whatsapp:hover { background: #1ea952; }
                    .btn-email { background: #4285F4; color: white; }
                    .btn-email:hover { background: #3367d6; }
                    .pdf-container { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                    iframe { width: 100%; height: 80vh; border: none; border-radius: 8px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>📄 Orçamento N° ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '</h1>
                    <p>Visualizando PDF completo com múltiplos shows</p>
                    <div class="buttons">
                        <a href="' . $downloadUrl . '" class="btn btn-download">📥 Baixar PDF Completo</a>
                        <a href="https://wa.me/5511999999999?text=' . urlencode('Olá! Gostaria de mais informações sobre o orçamento N° ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT)) . '" class="btn btn-whatsapp" target="_blank">📱 WhatsApp</a>
                        <a href="mailto:' . $orcamento['email'] . '?subject=Orçamento N° ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '&body=Olá! Segue em anexo o orçamento solicitado." class="btn btn-email">📧 E-mail</a>
                    </div>
                </div>
                <div class="pdf-container">
                    <iframe src="data:application/pdf;base64,' . base64_encode($pdfContent) . '"></iframe>
                </div>
            </body>
            </html>';
        }

} catch (Exception $e) {
    error_log('Erro no PDF mPDF: ' . $e->getMessage());
    header('Content-Type: text/html');
    die('Erro ao gerar PDF: ' . $e->getMessage());
}

