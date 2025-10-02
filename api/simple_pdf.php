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
            .share-buttons {
                background: #f8f9fa;
                padding: 20px;
                text-align: center;
                margin: 20px 0;
                border-radius: 10px;
                border: 1px solid #ddd;
            }
            .share-buttons h3 {
                color: #1e3a8a;
                margin-bottom: 10px;
                font-size: 18px;
            }
            .share-buttons p {
                color: #6b7280;
                margin-bottom: 15px;
                font-size: 14px;
            }
            .button-group {
                display: flex;
                gap: 10px;
                justify-content: center;
                flex-wrap: wrap;
            }
            .share-buttons button {
                padding: 12px 20px;
                border: none;
                border-radius: 8px;
                font-weight: bold;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s ease;
                min-width: 120px;
            }
            .btn-whatsapp {
                background: #25D366;
                color: white;
            }
            .btn-whatsapp:hover {
                background: #1ea952;
                transform: translateY(-2px);
            }
            .btn-download {
                background: #1e3a8a;
                color: white;
            }
            .btn-download:hover {
                background: #1e40af;
                transform: translateY(-2px);
            }
            .btn-share {
                background: #6b7280;
                color: white;
            }
            .btn-share:hover {
                background: #4b5563;
                transform: translateY(-2px);
            }
            .btn-print {
                background: #f97316;
                color: white;
            }
            .btn-print:hover {
                background: #ea580c;
                transform: translateY(-2px);
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #ddd;
                padding-top: 15px;
            }
            @media print {
                .share-buttons {
                    display: none;
                }
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
        <div class="share-buttons">
            <h3>📤 Compartilhar Orçamento</h3>
            <p>Clique em WhatsApp para compartilhar o arquivo PDF diretamente</p>
            <div class="button-group">
                <button onclick="shareWhatsApp()" class="btn-whatsapp">📱 WhatsApp (Arquivo PDF)</button>
                <button onclick="downloadPDF()" class="btn-download">📄 Download PDF</button>

                <button onclick="printPDF()" class="btn-print">🖨️ Imprimir</button>
            </div>
        </div>

        <div class="footer">
            <div><strong>N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</strong></div>
            <div>Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED</div>
            <div>Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br</div>
        </div>

        <script>
        async function shareWhatsApp() {
            try {
                // Gerar PDF e obter URL
                const pdfUrl = window.location.origin + "/pdf_real.php?id=' . $orcamento['id'] . '";

                // Criar mensagem formatada
                const message = `🏢 *N.D CONNECT - EQUIPAMENTOS PARA EVENTOS*

📋 *Orçamento Nº ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '*
💰 *Valor Total: R$ ' . number_format($orcamento['total'], 2, ',', '.') . '*
📅 *Válido até: ' . date('d/m/Y', strtotime($orcamento['data_validade'])) . '*

👤 *Cliente:* ' . htmlspecialchars($orcamento['cliente_nome']) . '

📄 *Baixar PDF:* ${pdfUrl}

✨ *Agradecemos pela preferência!*
🎉 *N.D Connect - Sua parceira em eventos inesquecíveis*`;

                // Tentar usar Web Share API com arquivo se disponível
                if (navigator.share && navigator.canShare) {
                    try {
                        // Tentar buscar o arquivo PDF
                        const response = await fetch(pdfUrl);
                        const blob = await response.blob();
                        const file = new File([blob], "orcamento_' . $orcamento['id'] . '.pdf", { type: "application/pdf" });

                        // Verificar se pode compartilhar arquivo
                        if (navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: "Orçamento N.D Connect",
                                text: message,
                                files: [file]
                            });
                            return;
                        }
                    } catch (e) {
                        console.log("Não foi possível compartilhar arquivo, usando link:", e);
                    }
                }

                // Fallback: abrir WhatsApp Web/App com link
                const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
                window.open(whatsappUrl, "_blank");

            } catch (error) {
                console.error("Erro ao compartilhar:", error);
                alert("Erro ao compartilhar orçamento. Tente novamente.");
            }
        }

        function downloadPDF() {
            window.open("pdf_real.php?id=' . $orcamento['id'] . '", "_blank");
        }

        async function shareNative() {
            try {
                const pdfUrl = window.location.origin + "/pdf_real.php?id=' . $orcamento['id'] . '";

                // Tentar compartilhar arquivo PDF
                if (navigator.share && navigator.canShare) {
                    try {
                        const response = await fetch(pdfUrl);
                        const blob = await response.blob();
                        const file = new File([blob], "orcamento_' . $orcamento['id'] . '.pdf", { type: "application/pdf" });

                        if (navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: "Orçamento N.D Connect - ' . $orcamento['numero_orcamento'] . '",
                                text: "Orçamento de equipamentos para eventos",
                                files: [file]
                            });
                            return;
                        }
                    } catch (e) {
                        console.log("Não foi possível compartilhar arquivo:", e);
                    }
                }

                // Fallback: compartilhar URL
                if (navigator.share) {
                    await navigator.share({
                        title: "Orçamento N.D Connect",
                        text: "Orçamento N.D Connect - ' . $orcamento['numero_orcamento'] . '",
                        url: pdfUrl
                    });
                } else {
                    alert("Compartilhamento nativo não suportado neste navegador");
                }
            } catch (error) {
                console.log("Compartilhamento cancelado ou erro:", error);
            }
        }

        function printPDF() {
            window.print();
        }
        </script>
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
