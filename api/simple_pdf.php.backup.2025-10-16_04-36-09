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

    // Verificar e definir valores padrão para evitar warnings
    $numero_orcamento = isset($orcamento['numero_orcamento']) ? $orcamento['numero_orcamento'] : 'N/A';
    $cliente_nome = isset($orcamento['cliente_nome']) ? $orcamento['cliente_nome'] : 'Cliente não informado';
    $cliente_email = isset($orcamento['email']) ? $orcamento['email'] : '';
    $cliente_telefone = isset($orcamento['telefone']) ? $orcamento['telefone'] : '';
    $cliente_endereco = isset($orcamento['endereco']) ? $orcamento['endereco'] : '';
    $cliente_cpf_cnpj = isset($orcamento['cpf_cnpj']) ? $orcamento['cpf_cnpj'] : '';
    $data_orcamento = isset($orcamento['data_orcamento']) ? $orcamento['data_orcamento'] : date('Y-m-d');
    $data_validade = isset($orcamento['data_validade']) ? $orcamento['data_validade'] : date('Y-m-d', strtotime('+10 days'));
    $itens = isset($orcamento['itens']) && is_array($orcamento['itens']) ? $orcamento['itens'] : [];
    $subtotal = isset($orcamento['subtotal']) ? $orcamento['subtotal'] : 0;
    $desconto = isset($orcamento['desconto']) ? $orcamento['desconto'] : 0;
    $total = isset($orcamento['total']) ? $orcamento['total'] : 0;
    $observacoes = isset($orcamento['observacoes']) ? $orcamento['observacoes'] : '';
    $id = isset($orcamento['id']) ? $orcamento['id'] : 0;

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Orçamento - ' . $numero_orcamento . '</title>
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
                padding: 20px 0;
                background: white;
            }

            .logo-container {
                display: inline-block;
                width: 80px;
                height: 80px;
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
                max-width: 60px;
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

            .cliente-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-top: 10px;
            }

            .cliente-item {
                display: flex;
                flex-direction: column;
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
                padding: 10px 8px;
                text-align: left;
                font-weight: bold;
                font-size: 11px;
                text-transform: uppercase;
            }

            .itens-table th.center {
                text-align: center;
            }

            .itens-table td {
                padding: 8px;
                border-bottom: 1px solid #eee;
                font-size: 12px;
                background: white;
            }

            .itens-table td.center {
                text-align: center;
            }

            .itens-table td.right {
                text-align: right;
            }

            .produto-nome {
                font-weight: 500;
                color: #333;
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

            /* Botões de ação */
            .action-buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
                margin: 20px;
            }

            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.3s ease;
                min-width: 140px;
                justify-content: center;
            }

            .btn-whatsapp {
                background: #25d366;
                color: white;
            }

            .btn-whatsapp:hover {
                background: #128c7e;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
            }

            .btn-email {
                background: #3498db;
                color: white;
            }

            .btn-email:hover {
                background: #2980b9;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
            }

            .btn-print {
                background: #95a5a6;
                color: white;
            }

            .btn-print:hover {
                background: #7f8c8d;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(149, 165, 166, 0.3);
            }

            .btn-download {
                background: #e74c3c;
                color: white;
            }

            .btn-download:hover {
                background: #c0392b;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
            }

            /* Responsividade */
            @media (max-width: 768px) {
                .cliente-grid {
                    grid-template-columns: 1fr;
                }

                .datas-section {
                    flex-direction: column;
                    gap: 10px;
                }

                .action-buttons {
                    flex-direction: column;
                    align-items: center;
                }

                .btn {
                    width: 100%;
                    max-width: 300px;
                }

                .itens-table {
                    font-size: 10px;
                }

                .itens-table th,
                .itens-table td {
                    padding: 6px 4px;
                }
            }

            /* Print styles */
            @media print {
                .action-buttons {
                    display: none;
                }

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
                EQUIPAMENTOS PARA EVENTOS
            </div>

            <!-- Número do orçamento -->
            <div class="blue-bar small">
                ORÇAMENTO N° ' . str_pad($numero_orcamento, 6, '0', STR_PAD_LEFT) . '
            </div>

            <!-- Dados do cliente -->
            <div class="blue-bar left">
                DADOS DO CLIENTE
            </div>
            <div class="cliente-section">
                <div class="cliente-grid">
                    <div class="cliente-item">
                        <div class="cliente-label">NOME</div>
                        <div class="cliente-value">' . htmlspecialchars($cliente_nome) . '</div>
                    </div>
                    <div class="cliente-item">
                        <div class="cliente-label">E-MAIL</div>
                        <div class="cliente-value">' . htmlspecialchars($cliente_email) . '</div>
                    </div>
                    <div class="cliente-item">
                        <div class="cliente-label">TELEFONE</div>
                        <div class="cliente-value">' . htmlspecialchars($cliente_telefone) . '</div>
                    </div>
                    <div class="cliente-item">
                        <div class="cliente-label">CPF/CNPJ</div>
                        <div class="cliente-value">' . htmlspecialchars($cliente_cpf_cnpj) . '</div>
                    </div>
                </div>
            </div>

            <!-- Datas -->
            <div class="datas-section">
                <div class="data-item">
                    <div class="data-label">DATA DO ORÇAMENTO</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($data_orcamento)) . '</div>
                </div>
                <div class="data-item">
                    <div class="data-label">VÁLIDO ATÉ</div>
                    <div class="data-value">' . date('d/m/Y', strtotime($data_validade)) . '</div>
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
                    <tbody>
                        ';

    foreach ($itens as $item) {
        $produto_nome = isset($item['produto_nome']) ? $item['produto_nome'] : 'Produto não informado';
        $quantidade = isset($item['quantidade']) ? $item['quantidade'] : 0;
        $preco_unitario = isset($item['preco_unitario']) ? $item['preco_unitario'] : 0;
        $subtotal_item = isset($item['subtotal']) ? $item['subtotal'] : 0;
        $unidade = isset($item['unidade']) ? $item['unidade'] : 'un';

        $html .= '
                        <tr>
                            <td>
                                <div class="produto-nome">' . htmlspecialchars($produto_nome) . '</div>
                            </td>
                            <td class="center">' . $quantidade . '</td>
                            <td class="right">R$ ' . number_format($preco_unitario, 2, ',', '.') . '</td>
                            <td class="right">R$ ' . number_format($subtotal_item, 2, ',', '.') . '</td>
                            <td class="center">' . htmlspecialchars($unidade) . '</td>
                        </tr>';
    }

    $html .= '
                    </tbody>
                </table>
            </div>

            <!-- Totais -->
            <div class="totais-section">
                <div class="total-item">
                    <span class="total-label">SUBTOTAL:</span>
                    <span class="total-value">R$ ' . number_format($subtotal, 2, ',', '.') . '</span>
                </div>
                ' . ($desconto > 0 ? '
                <div class="total-item">
                    <span class="total-label">DESCONTO:</span>
                    <span class="total-value">- R$ ' . number_format($desconto, 2, ',', '.') . '</span>
                </div>
                ' : '') . '
                <div class="total-separator"></div>
                <div class="total-item total-final">
                    <span class="total-label">TOTAL:</span>
                    <span class="total-value">R$ ' . number_format($total, 2, ',', '.') . '</span>
                </div>
            </div>

            ' . (!empty($observacoes) ? '
            <!-- Observações -->
            <div class="observacoes-section">
                <div class="observacoes-title">OBSERVAÇÕES</div>
                <div class="observacoes-text">' . nl2br(htmlspecialchars($observacoes)) . '</div>
            </div>
            ' : '') . '

            <!-- Botões de ação -->
            <div class="action-buttons">
                <button class="btn btn-whatsapp" onclick="shareWhatsApp()">
                    📱 WhatsApp
                </button>
                <button class="btn btn-email" onclick="shareEmail()">
                    📧 E-mail
                </button>
                <button class="btn btn-print" onclick="printPDF()">
                    🖨️ Imprimir
                </button>
                <button class="btn btn-download" onclick="downloadPDF()">
                    💾 Baixar PDF
                </button>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-title">N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</div>
                <div class="footer-specialization">Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED</div>
                <div class="footer-contact">Contato: (11) 99999-9999 | Email: contato@ndconnect.com.br</div>
            </div>
        </div>

        <script>
        async function shareWhatsApp() {
            try {
                const pdfUrl = window.location.origin + "/pdf_real.php?id=' . $id . '";
                const message = "🏢 *N.D CONNECT - EQUIPAMENTOS PARA EVENTOS*\\n\\nOlá ' . htmlspecialchars($cliente_nome) . '! 👋\\n\\nSegue o orçamento solicitado:\\n\\n📋 *Orçamento Nº ' . str_pad($numero_orcamento, 6, '0', STR_PAD_LEFT) . '*\\n💰 *Valor Total: R$ ' . number_format($total, 2, ',', '.') . '*\\n📅 *Válido até: ' . date('d/m/Y', strtotime($data_validade)) . '*\\n\\n📄 *PDF em anexo*\\n\\n✨ *Agradecemos pela preferência!*\\n🎉 *N.D Connect - Sua parceira em eventos inesquecíveis*";

                // Mostrar loading no botão
                const button = event?.target || document.querySelector(".btn-whatsapp");
                const originalText = button.innerHTML;
                button.innerHTML = "⏳ Baixando PDF...";
                button.disabled = true;

                console.log("Baixando PDF para compartilhamento...");

                // Baixar o PDF
                const response = await fetch(pdfUrl);

                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }

                const blob = await response.blob();
                console.log("PDF baixado, tamanho:", blob.size, "bytes");

                // Criar arquivo para compartilhamento
                const fileName = "' . 'orcamento_' . strtolower(explode(' ', $cliente_nome)[0]) . '_' . $id . '.pdf' . '";
                const file = new File([blob], fileName, { type: "application/pdf" });

                console.log("Arquivo criado:", file.name, "tamanho:", file.size);

                // Tentar usar Web Share API para anexar o arquivo
                if (navigator.share && navigator.canShare) {
                    try {
                        if (navigator.canShare({ files: [file] })) {
                            console.log("Compartilhando arquivo via Web Share API...");
                            await navigator.share({
                                title: "Orçamento N.D Connect - ' . $numero_orcamento . '",
                                text: message,
                                files: [file]
                            });
                            console.log("Arquivo compartilhado com sucesso!");

                            button.innerHTML = originalText;
                            button.disabled = false;
                            return;
                        } else {
                            console.log("Arquivo não pode ser compartilhado via Web Share API");
                        }
                    } catch (e) {
                        console.log("Erro na Web Share API:", e);
                    }
                }

                // Fallback: abrir WhatsApp Web com link para download
                console.log("Usando fallback para WhatsApp Web...");
                const telefone = "' . addslashes(htmlspecialchars($cliente_telefone)) . '";
                let whatsappUrl;

                if (telefone) {
                    // Limpar e formatar telefone
                    const telefoneLimpo = telefone.replace(/[^0-9]/g, "");
                    const telefoneFormatado = telefoneLimpo.startsWith("55") ? telefoneLimpo : "55" + telefoneLimpo;
                    whatsappUrl = "https://wa.me/" + telefoneFormatado + "?text=" + encodeURIComponent(message + "\\n\\n📄 *Baixar PDF:* " + pdfUrl);
                } else {
                    whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(message + "\\n\\n📄 *Baixar PDF:* " + pdfUrl);
                }

                window.open(whatsappUrl, "_blank");

                button.innerHTML = originalText;
                button.disabled = false;

            } catch (error) {
                console.error("Erro ao compartilhar no WhatsApp:", error);
                alert("Erro ao baixar PDF. Tente novamente.");

                const button = event?.target || document.querySelector(".btn-whatsapp");
                if (button) {
                    button.innerHTML = "📱 WhatsApp";
                    button.disabled = false;
                }
            }
        }

        function downloadPDF() {
            try {
                window.open("/pdf_real.php?id=' . $id . '", "_blank");
            } catch (error) {
                console.error("Erro ao baixar PDF:", error);
                alert("Erro ao baixar PDF. Tente novamente.");
            }
        }

        async function shareEmail() {
            try {
                const orcamentoUrl = window.location.href;
                const pdfUrl = window.location.origin + "/pdf_real.php?id=' . $id . '";

                const email = "' . htmlspecialchars($cliente_email) . '";
                let emailUrl;

                if (email) {
                    const assunto = "Orçamento N.D Connect - Nº ' . str_pad($numero_orcamento, 6, '0', STR_PAD_LEFT) . '";
                    const corpo = "Olá ' . htmlspecialchars($cliente_nome) . '! 👋\n\nEsperamos que esteja bem! Segue em anexo o orçamento solicitado para seu evento.\n\n📋 *DETALHES DO ORÇAMENTO*\n• Número: ' . str_pad($numero_orcamento, 6, '0', STR_PAD_LEFT) . '\n• Valor Total: R$ ' . number_format($total, 2, ',', '.') . '\n• Válido até: ' . date('d/m/Y', strtotime($data_validade)) . '\n\n📄 *ARQUIVOS ANEXOS*\n• PDF para impressão: " + pdfUrl + "\n• Visualização online: " + orcamentoUrl + "\n\n✨ *Agradecemos pela preferência!*\n🎉 *N.D Connect - Sua parceira em eventos inesquecíveis*\n\n---\nN.D CONNECT - EQUIPAMENTOS PARA EVENTOS\nEspecializada em palcos, geradores, efeitos, stands, som, luz e painéis LED\nContato: (11) 99999-9999 | Email: contato@ndconnect.com.br";

                    emailUrl = "mailto:" + email + "?subject=" + encodeURIComponent(assunto) + "&body=" + encodeURIComponent(corpo);
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
                        const file = new File([blob], "' . 'orcamento_' . strtolower(explode(' ', $cliente_nome)[0]) . '_' . $id . '.pdf' . '", { type: "application/pdf" });

                        if (navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: "Orçamento N.D Connect - ' . $numero_orcamento . '",
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
    $response = $orcamentoController->getById($_GET['id']);

    if ($response['success'] && $response['data']) {
        $orcamento = $response['data'];
        $html = gerarPDFSimples($orcamento);

        // Configurar headers para impressão/PDF
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
    } else {
        http_response_code(404);
        echo 'Orçamento não encontrado: ' . $response['message'];
    }
} else {
    http_response_code(400);
    echo 'ID do orçamento não fornecido';
}
?>
