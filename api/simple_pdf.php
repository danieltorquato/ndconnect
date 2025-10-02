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
            /* Reset e base */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                line-height: 1.6;
                color: #333;
                background: #f8fafc;
                padding: 20px;
            }

            /* Container principal */
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            /* Header */
            .header {
                background: linear-gradient(135deg, #0C2B59 0%, #1e3a8a 100%);
                color: white;
                padding: 30px;
                text-align: center;
                position: relative;
            }

            .header::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, #E8622D 0%, #F7A64C 100%);
                opacity: 0.1;
                z-index: 1;
            }

            .header-content {
                position: relative;
                z-index: 2;
            }

            .logo {
                font-size: 32px;
                font-weight: 800;
                margin-bottom: 10px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            }

            .company-subtitle {
                font-size: 16px;
                opacity: 0.9;
                margin-bottom: 20px;
            }

            .orcamento-number {
                background: rgba(255, 255, 255, 0.2);
                padding: 12px 24px;
                border-radius: 25px;
                font-size: 18px;
                font-weight: 600;
                display: inline-block;
                backdrop-filter: blur(10px);
            }

            /* Dados do cliente */
            .cliente-section {
                padding: 30px;
                background: white;
            }

            .section-title {
                font-size: 20px;
                font-weight: 700;
                color: #0C2B59;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 3px solid #E8622D;
                position: relative;
            }

            .section-title::after {
                content: "";
                position: absolute;
                bottom: -3px;
                left: 0;
                width: 50px;
                height: 3px;
                background: #F7A64C;
            }

            .cliente-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .cliente-item {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .cliente-label {
                font-weight: 600;
                color: #64748b;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .cliente-value {
                font-size: 16px;
                color: #1e293b;
                font-weight: 500;
            }

            .data-info {
                background: #f1f5f9;
                padding: 20px;
                border-radius: 8px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
            }

            .data-item {
                text-align: center;
            }

            .data-label {
                font-size: 12px;
                color: #64748b;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 5px;
            }

            .data-value {
                font-size: 16px;
                color: #0C2B59;
                font-weight: 700;
            }

            /* Tabela de itens */
            .itens-section {
                padding: 0 30px 30px;
            }

            .itens-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .itens-table th {
                background: linear-gradient(135deg, #E8622D 0%, #F7A64C 100%);
                color: white;
                padding: 16px 12px;
                font-weight: 700;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                text-align: left;
            }

            .itens-table th:first-child {
                border-top-left-radius: 8px;
            }

            .itens-table th:last-child {
                border-top-right-radius: 8px;
            }

            .itens-table td {
                padding: 16px 12px;
                border-bottom: 1px solid #e2e8f0;
                font-size: 14px;
            }

            .itens-table tr:last-child td {
                border-bottom: none;
            }

            .itens-table tr:nth-child(even) {
                background: #f8fafc;
            }

            .produto-nome {
                font-weight: 600;
                color: #0C2B59;
            }

            .quantidade, .preco, .subtotal, .unidade {
                text-align: center;
                font-weight: 500;
            }

            .preco, .subtotal {
                color: #059669;
                font-weight: 600;
            }

            /* Totais */
            .totais-section {
                padding: 0 30px 30px;
            }

            .totais {
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                padding: 25px;
                border-radius: 12px;
                margin-top: 20px;
            }

            .total-line {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 12px;
                font-size: 16px;
            }

            .total-label {
                font-weight: 600;
                color: #64748b;
            }

            .total-value {
                font-weight: 700;
                color: #0C2B59;
            }

            .total-final {
                border-top: 2px solid #E8622D;
                padding-top: 15px;
                margin-top: 15px;
                font-size: 20px;
                font-weight: 800;
            }

            .total-final .total-value {
                color: #E8622D;
                font-size: 24px;
            }

            /* Observações */
            .observacoes {
                padding: 0 30px 30px;
            }

            .observacoes-content {
                background: #fef3c7;
                padding: 20px;
                border-radius: 8px;
                border-left: 4px solid #F7A64C;
            }

            .observacoes-text {
                color: #92400e;
                font-style: italic;
                line-height: 1.6;
            }

            /* Botões de compartilhamento */
            .share-buttons {
                background: #f8fafc;
                padding: 30px;
                text-align: center;
                border-top: 1px solid #e2e8f0;
            }

            .share-title {
                font-size: 18px;
                font-weight: 700;
                color: #0C2B59;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }

            .share-subtitle {
                color: #64748b;
                margin-bottom: 25px;
                font-size: 14px;
            }

            .button-group {
                display: flex;
                gap: 12px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .share-buttons button {
                padding: 12px 20px;
                border: none;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s ease;
                min-width: 140px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .btn-whatsapp {
                background: #25D366;
                color: white;
            }

            .btn-whatsapp:hover {
                background: #1ea952;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
            }

            .btn-download {
                background: #0C2B59;
                color: white;
            }

            .btn-download:hover {
                background: #1e3a8a;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(12, 43, 89, 0.3);
            }

            .btn-share {
                background: #E8622D;
                color: white;
            }

            .btn-share:hover {
                background: #d5511a;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(232, 98, 45, 0.3);
            }

            .btn-print {
                background: #64748b;
                color: white;
            }

            .btn-print:hover {
                background: #475569;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
            }

            /* Footer */
            .footer {
                background: #0C2B59;
                color: white;
                padding: 25px 30px;
                text-align: center;
            }

            .footer-company {
                font-size: 18px;
                font-weight: 700;
                margin-bottom: 8px;
            }

            .footer-description {
                font-size: 14px;
                opacity: 0.9;
                margin-bottom: 8px;
            }

            .footer-contact {
                font-size: 12px;
                opacity: 0.8;
            }

            /* Responsividade */
            @media (max-width: 768px) {
                .cliente-grid {
                    grid-template-columns: 1fr;
                }

                .data-info {
                    flex-direction: column;
                    gap: 15px;
                }

                .button-group {
                    flex-direction: column;
                    align-items: center;
                }

                .share-buttons button {
                    width: 100%;
                    max-width: 300px;
                }
            }

            /* Print styles */
            @media print {
                body {
                    background: white;
                    padding: 0;
                }

                .container {
                    box-shadow: none;
                    border-radius: 0;
                }

                .share-buttons {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="header-content">';

    if ($logoBase64) {
        $html .= '<img src="' . $logoBase64 . '" alt="N.D CONNECT" style="width: 200px; height: 80px; margin-bottom: 15px;">';
    } else {
        $html .= '<div class="logo">N.D CONNECT</div>';
    }

    $html .= '
                    <div class="company-subtitle">EQUIPAMENTOS PARA EVENTOS</div>
                    <div class="orcamento-number">ORÇAMENTO Nº ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '</div>
                </div>
            </div>

            <div class="cliente-section">
                <div class="section-title">DADOS DO CLIENTE</div>
                <div class="cliente-grid">
                    <div class="cliente-item">
                        <div class="cliente-label">Nome</div>
                        <div class="cliente-value">' . htmlspecialchars($orcamento['cliente_nome']) . '</div>
                    </div>';

    if (!empty($orcamento['email'])) {
        $html .= '
                    <div class="cliente-item">
                        <div class="cliente-label">E-mail</div>
                        <div class="cliente-value">' . htmlspecialchars($orcamento['email']) . '</div>
                    </div>';
    }

    if (!empty($orcamento['telefone'])) {
        $html .= '
                    <div class="cliente-item">
                        <div class="cliente-label">Telefone</div>
                        <div class="cliente-value">' . htmlspecialchars($orcamento['telefone']) . '</div>
                    </div>';
    }

    if (!empty($orcamento['endereco'])) {
        $html .= '
                    <div class="cliente-item">
                        <div class="cliente-label">Endereço</div>
                        <div class="cliente-value">' . htmlspecialchars($orcamento['endereco']) . '</div>
                    </div>';
    }

    if (!empty($orcamento['cpf_cnpj'])) {
        $html .= '
                    <div class="cliente-item">
                        <div class="cliente-label">CPF/CNPJ</div>
                        <div class="cliente-value">' . htmlspecialchars($orcamento['cpf_cnpj']) . '</div>
                    </div>';
    }

    $html .= '
                </div>
            </div>

            <div class="data-info">
                <div class="data-item">
                    <div class="data-label">Data do Orçamento</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($orcamento['data_orcamento'])) . '</div>
                </div>
                <div class="data-item">
                    <div class="data-label">Válido até</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($orcamento['data_validade'])) . '</div>
                </div>
            </div>

            <div class="itens-section">
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

            <div class="totais-section">
                <div class="totais">
                    <div class="total-line">
                        <span class="total-label">SUBTOTAL</span>
                        <span class="total-value">R$ ' . number_format($orcamento['subtotal'], 2, ',', '.') . '</span>
                    </div>';

    if ($orcamento['desconto'] > 0) {
        $html .= '
                    <div class="total-line">
                        <span class="total-label">DESCONTO</span>
                        <span class="total-value">- R$ ' . number_format($orcamento['desconto'], 2, ',', '.') . '</span>
                    </div>';
    }

    $html .= '
                    <div class="total-line total-final">
                        <span class="total-label">TOTAL</span>
                        <span class="total-value">R$ ' . number_format($orcamento['total'], 2, ',', '.') . '</span>
                    </div>
                </div>
            </div>';

    if (!empty($orcamento['observacoes'])) {
        $html .= '
            <div class="observacoes">
                <div class="section-title">OBSERVAÇÕES</div>
                <div class="observacoes-content">
                    <div class="observacoes-text">' . nl2br(htmlspecialchars($orcamento['observacoes'])) . '</div>
                </div>
            </div>';
    }

    $html .= '
        <div class="share-buttons">
            <h3>📤 Compartilhar Orçamento</h3>
            <p>Clique em WhatsApp para enviar direto para o cliente ou E-mail para enviar por e-mail</p>
            <div class="button-group">
                <button onclick="shareWhatsApp()" class="btn-whatsapp">📱 WhatsApp (Envio Direto)</button>
                <button onclick="shareEmail()" class="btn-share">📧 E-mail (Envio Direto)</button>
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
                const message = "🏢 *N.D CONNECT - EQUIPAMENTOS PARA EVENTOS*\\n\\nOlá ' . htmlspecialchars($orcamento['cliente_nome']) . '! 👋\\n\\nSegue o orçamento solicitado:\\n\\n📋 *Orçamento Nº ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '*\\n💰 *Valor Total: R$ ' . number_format($orcamento['total'], 2, ',', '.') . '*\\n📅 *Válido até: ' . date('d/m/Y', strtotime($orcamento['data_validade'])) . '*\\n\\n📄 *Baixar PDF:* " + pdfUrl + "\\n\\n✨ *Agradecemos pela preferência!*\\n🎉 *N.D Connect - Sua parceira em eventos inesquecíveis*";

                // Verificar se tem telefone para envio direto
                const telefone = "' . addslashes(htmlspecialchars($orcamento['cliente_telefone'] ?? '')) . '";
                let whatsappUrl;

                if (telefone && telefone.trim() != "") {
                    const numero = telefone.replace(/[^0-9]/g, "");
                    if (numero.length === 10 || numero.length === 11) {
                        const numeroWhatsApp = "+55" + numero;
                        whatsappUrl = "https://wa.me/" + numeroWhatsApp + "?text=" + encodeURIComponent(message);
                        console.log("Enviando para:", telefone, "->", numeroWhatsApp);
                    } else {
                        // Telefone inválido, usar fallback
                        whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(message);
                        console.log("Telefone inválido, usando fallback");
                    }
                } else {
                    // Sem telefone, usar fallback
                    whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(message);
                    console.log("Sem telefone, usando fallback");
                }

                // Tentar usar Web Share API com arquivo se disponível
                if (navigator.share && navigator.canShare) {
                    try {
                        // Tentar buscar o arquivo PDF
                        const response = await fetch(pdfUrl);
                        const blob = await response.blob();
                        const file = new File([blob], "orcamento_' . strtolower(explode(' ', $orcamento['cliente_nome'])[0]) . '_' . $orcamento['id'] . '.pdf", { type: "application/pdf" });

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
                        console.log("Não foi possível compartilhar arquivo, usando WhatsApp:", e);
                    }
                }

                // Abrir WhatsApp com número específico ou fallback
                window.open(whatsappUrl, "_blank");

            } catch (error) {
                console.error("Erro ao compartilhar:", error);
                alert("Erro ao compartilhar orçamento. Tente novamente.");
            }
        }

        function downloadPDF() {
            window.open("pdf_real.php?id=' . $orcamento['id'] . '", "_blank");
        }

        async function shareEmail() {
            try {
                const pdfUrl = window.location.origin + "/pdf_real.php?id=' . $orcamento['id'] . '";
                const orcamentoUrl = window.location.href;

                // Verificar se tem e-mail para envio direto
                const email = "' . htmlspecialchars($orcamento['cliente_email'] ?? '') . '";
                let emailUrl;

                if (email && email.trim() !== "") {
                    // Validar e-mail
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailRegex.test(email)) {
                        const assunto = "Orçamento N.D Connect - Nº ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '";
                        const corpo = "Olá ' . htmlspecialchars($orcamento['cliente_nome']) . '! 👋\n\nEsperamos que esteja bem! Segue em anexo o orçamento solicitado para seu evento.\n\n📋 *DETALHES DO ORÇAMENTO*\n• Número: ' . str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) . '\n• Valor Total: R$ ' . number_format($orcamento['total'], 2, ',', '.') . '\n• Válido até: ' . date('d/m/Y', strtotime($orcamento['data_validade'])) . '\n\n📄 *ARQUIVOS ANEXOS*\n• PDF para impressão: " + pdfUrl + "\n• Visualização online: " + orcamentoUrl + "\n\n✨ *Agradecemos pela preferência!*\n🎉 *N.D Connect - Sua parceira em eventos inesquecíveis*\n\n---\nN.D CONNECT - EQUIPAMENTOS PARA EVENTOS\nEspecializada em palcos, geradores, efeitos, stands, som, luz e painéis LED\nContato: (11) 99999-9999 | Email: contato@ndconnect.com.br";

                        emailUrl = "mailto:" + email + "?subject=" + encodeURIComponent(assunto) + "&body=" + encodeURIComponent(corpo);
                        console.log("Enviando e-mail para:", email);
                    } else {
                        // E-mail inválido, usar fallback
                        emailUrl = "mailto:?subject=" + encodeURIComponent("Orçamento N.D Connect") + "&body=" + encodeURIComponent("Segue o orçamento: " + pdfUrl);
                        console.log("E-mail inválido, usando fallback");
                    }
                } else {
                    // Sem e-mail, usar fallback
                    emailUrl = "mailto:?subject=" + encodeURIComponent("Orçamento N.D Connect") + "&body=" + encodeURIComponent("Segue o orçamento: " + pdfUrl);
                    console.log("Sem e-mail, usando fallback");
                }

                // Tentar usar Web Share API com arquivo se disponível
                if (navigator.share && navigator.canShare) {
                    try {
                        const response = await fetch(pdfUrl);
                        const blob = await response.blob();
                        const file = new File([blob], "orcamento_' . strtolower(explode(' ', $orcamento['cliente_nome'])[0]) . '_' . $orcamento['id'] . '.pdf", { type: "application/pdf" });

                        if (navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: "Orçamento N.D Connect - ' . $orcamento['numero_orcamento'] . '",
                                text: "Orçamento de equipamentos para eventos",
                                files: [file]
                            });
                            return;
                        }
                    } catch (e) {
                        console.log("Não foi possível compartilhar arquivo, usando e-mail:", e);
                    }
                }

                // Abrir cliente de e-mail com destinatário específico ou fallback
                window.open(emailUrl, "_blank");

            } catch (error) {
                console.error("Erro ao compartilhar por e-mail:", error);
                alert("Erro ao abrir cliente de e-mail. Tente novamente.");
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
