<?php
// Redirecionar para o script de PDF real
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID do orçamento não fornecido');
}

$orcamentoId = (int)$_GET['id'];

// Verificar se TCPDF está disponível
if (file_exists('vendor/autoload.php')) {
    // Usar PDF real com TCPDF
    require_once 'pdf_real.php';
} else {
    // Fallback para HTML
    require_once 'Config/Database.php';

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

    $orcamento = getOrcamentoData($orcamentoId);

    if (!$orcamento) {
        die('Orçamento não encontrado');
    }

// Gerar HTML para conversão em PDF
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
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #0C2B59 0%, #E8622D 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
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
            border: 1px solid #ddd;
            border-top: none;
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
        </div>

        <div class="footer">
            <strong>N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</strong><br>
            Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED<br>
            Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br
        </div>
    </div>
</body>
</html>';

// Configurar headers para download
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename="orcamento_' . $orcamentoId . '.html"');

// Usar JavaScript para imprimir/salvar como PDF
echo $html . '
<script>
// Função para imprimir/salvar como PDF
function printOrSavePDF() {
    window.print();
}

// Adicionar botões de ação
document.addEventListener("DOMContentLoaded", function() {
    const content = document.querySelector(".content");
    const actionButtons = document.createElement("div");
    actionButtons.style.cssText = "text-align: center; margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 10px;";

    actionButtons.innerHTML = `
        <h4>Compartilhar Orçamento</h4>
        <button onclick="shareWhatsApp()" style="margin: 5px; padding: 12px 24px; background: #25D366; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">📱 WhatsApp</button>
        <button onclick="printOrSavePDF()" style="margin: 5px; padding: 12px 24px; background: #0C2B59; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">📄 Download PDF</button>
        <button onclick="shareNative()" style="margin: 5px; padding: 12px 24px; background: #6c757d; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">🔗 Compartilhar</button>
    `;

    content.appendChild(actionButtons);
});

function shareWhatsApp() {
    const text = `Orçamento N.D Connect - ' . $orcamentoId . '%0AValor: R$ ' . $total . '%0AVisualizar: ' . $_SERVER['HTTP_HOST'] . '/simple_pdf.php?id=' . $orcamentoId . '`;
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, "_blank");
}

function shareNative() {
    if (navigator.share) {
        navigator.share({
            title: "Orçamento N.D Connect",
            text: "Orçamento N.D Connect - ' . $orcamentoId . '",
            url: window.location.href
        });
    } else {
        alert("Compartilhamento nativo não suportado neste navegador");
    }
}

// Adicionar estilos para impressão
const printStyles = document.createElement("style");
printStyles.textContent = `
    @media print {
        body { margin: 0; padding: 0; }
        .container { box-shadow: none; }
        button { display: none !important; }
    }
`;
document.head.appendChild(printStyles);
</script>';
?>
