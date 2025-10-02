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
                background: linear-gradient(135deg,rgba(255, 106, 0, 0.7) 0%, #0C2B59 100%);
                color: white;
                padding: 30px;
                text-align: center;
                position: relative;
            }

            .logo {
                max-width: 120px;
                height: auto;
                margin-bottom: 20px;
                border-radius: 8px;
            }

            .company-name {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 8px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            }

            .company-tagline {
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

            /* Conteúdo principal */
            .content {
                padding: 40px;
            }

            /* Informações do cliente */
            .cliente-section {
                background: #f8f9fa;
                padding: 25px;
                border-radius: 12px;
                margin-bottom: 30px;
                border-left: 4px solid #3498db;
            }

            .section-title {
                font-size: 20px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
            }

            .section-title::before {
                content: "👤";
                margin-right: 10px;
                font-size: 24px;
            }

            .cliente-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
            }

            .cliente-item {
                display: flex;
                flex-direction: column;
            }

            .cliente-label {
                font-size: 12px;
                color: #6c757d;
                text-transform: uppercase;
                font-weight: 600;
                margin-bottom: 5px;
                letter-spacing: 0.5px;
            }

            .cliente-value {
                font-size: 16px;
                color: #2c3e50;
                font-weight: 500;
            }

            /* Datas */
            .datas-section {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .data-item {
                text-align: center;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 12px;
                border: 2px solid #e9ecef;
            }

            .data-label {
                font-size: 14px;
                color: #6c757d;
                font-weight: 600;
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .data-value {
                font-size: 18px;
                color: #2c3e50;
                font-weight: 700;
            }

            /* Tabela de itens */
            .itens-section {
                margin-bottom: 30px;
            }

            .itens-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .itens-table th {
                background: #2c3e50;
                color: white;
                padding: 15px 12px;
                text-align: left;
                font-weight: 600;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .itens-table td {
                padding: 15px 12px;
                border-bottom: 1px solid #e9ecef;
                font-size: 14px;
            }

            .itens-table tr:nth-child(even) {
                background: #f8f9fa;
            }

            .itens-table tr:hover {
                background: #e3f2fd;
            }

            .produto-nome {
                font-weight: 600;
                color: #2c3e50;
            }

            .categoria {
                font-size: 12px;
                color: #6c757d;
                background: #e9ecef;
                padding: 2px 8px;
                border-radius: 12px;
                display: inline-block;
            }

            .quantidade {
                text-align: center;
                font-weight: 600;
            }

            .preco {
                text-align: right;
                font-weight: 600;
                color: #27ae60;
            }

            .subtotal {
                text-align: right;
                font-weight: 700;
                color: #2c3e50;
            }

            /* Totais */
            .totais-section {
                background: #f8f9fa;
                padding: 25px;
                border-radius: 12px;
                margin-bottom: 30px;
            }

            .total-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 0;
                border-bottom: 1px solid #e9ecef;
            }

            .total-item:last-child {
                border-bottom: none;
                font-size: 18px;
                font-weight: 700;
                color: #2c3e50;
                background: #e3f2fd;
                padding: 15px;
                margin: 10px -15px -15px -15px;
                border-radius: 0 0 12px 12px;
            }

            .total-label {
                font-size: 16px;
                color: #6c757d;
            }

            .total-value {
                font-size: 16px;
                font-weight: 600;
                color: #2c3e50;
            }

            /* Observações */
            .observacoes-section {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                padding: 20px;
                border-radius: 12px;
                margin-bottom: 30px;
            }

            .observacoes-title {
                font-size: 16px;
                font-weight: 600;
                color: #856404;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
            }

            .observacoes-title::before {
                content: "📝";
                margin-right: 8px;
            }

            .observacoes-text {
                color: #856404;
                line-height: 1.6;
            }

            /* Botões de ação */
            .action-buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
                margin-bottom: 30px;
            }

            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
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

            /* Footer */
            .footer {
                background: #2c3e50;
                color: white;
                padding: 30px;
                text-align: center;
            }

            .footer-title {
                font-size: 20px;
                font-weight: 700;
                margin-bottom: 15px;
            }

            .footer-info {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }

            .footer-item {
                text-align: center;
            }

            .footer-label {
                font-size: 12px;
                opacity: 0.8;
                margin-bottom: 5px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .footer-value {
                font-size: 16px;
                font-weight: 600;
            }

            .footer-note {
                font-size: 14px;
                opacity: 0.9;
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            }

            /* Responsividade */
            @media (max-width: 768px) {
                body {
                    padding: 10px;
                }

                .content {
                    padding: 20px;
                }

                .header {
                    padding: 20px;
                }

                .company-name {
                    font-size: 24px;
                }

                .cliente-grid {
                    grid-template-columns: 1fr;
                }

                .datas-section {
                    grid-template-columns: 1fr;
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
                    font-size: 12px;
                }

                .itens-table th,
                .itens-table td {
                    padding: 10px 8px;
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

                .action-buttons {
                    display: none;
                }

                .header {
                    background: #2c3e50 !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }

                .footer {
                    background: #2c3e50 !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                ' . ($logoBase64 ? '<img src="' . $logoBase64 . '" alt="N.D Connect Logo" class="logo">' : '') . '
                <div class="company-name">N.D CONNECT</div>
                <div class="company-tagline">Equipamentos para Eventos</div>
                <div class="orcamento-number">ORÇAMENTO Nº ' . str_pad($numero_orcamento, 6, '0', STR_PAD_LEFT) . '</div>
            </div>

            <!-- Conteúdo principal -->
            <div class="content">
                <!-- Informações do cliente -->
                <div class="cliente-section">
                    <div class="section-title">Dados do Cliente</div>
                    <div class="cliente-grid">
                        <div class="cliente-item">
                            <div class="cliente-label">Nome</div>
                            <div class="cliente-value">' . htmlspecialchars($cliente_nome) . '</div>
                        </div>
                        ' . (!empty($cliente_email) ? '
                        <div class="cliente-item">
                            <div class="cliente-label">E-mail</div>
                            <div class="cliente-value">' . htmlspecialchars($cliente_email) . '</div>
                        </div>
                        ' : '') . '
                        ' . (!empty($cliente_telefone) ? '
                        <div class="cliente-item">
                            <div class="cliente-label">Telefone</div>
                            <div class="cliente-value">' . htmlspecialchars($cliente_telefone) . '</div>
                        </div>
                        ' : '') . '
                        ' . (!empty($cliente_endereco) ? '
                        <div class="cliente-item">
                            <div class="cliente-label">Endereço</div>
                            <div class="cliente-value">' . htmlspecialchars($cliente_endereco) . '</div>
                        </div>
                        ' : '') . '
                        ' . (!empty($cliente_cpf_cnpj) ? '
                        <div class="cliente-item">
                            <div class="cliente-label">CPF/CNPJ</div>
                            <div class="cliente-value">' . htmlspecialchars($cliente_cpf_cnpj) . '</div>
                        </div>
                        ' : '') . '
                    </div>
                </div>

                <!-- Datas -->
                <div class="datas-section">
                    <div class="data-item">
                        <div class="data-label">Data do Orçamento</div>
                        <div class="data-value">' . date('d/m/Y', strtotime($data_orcamento)) . '</div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Válido até</div>
                        <div class="data-value">' . date('d/m/Y', strtotime($data_validade)) . '</div>
                    </div>
                </div>

                <!-- Itens do orçamento -->
                <div class="itens-section">
                    <div class="section-title">Itens do Orçamento</div>
                    <table class="itens-table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ';

    foreach ($itens as $item) {
        $produto_nome = isset($item['produto_nome']) ? $item['produto_nome'] : 'Produto não informado';
        $categoria_nome = isset($item['categoria_nome']) ? $item['categoria_nome'] : '';
        $quantidade = isset($item['quantidade']) ? $item['quantidade'] : 0;
        $preco_unitario = isset($item['preco_unitario']) ? $item['preco_unitario'] : 0;
        $subtotal_item = isset($item['subtotal']) ? $item['subtotal'] : 0;
        $unidade = isset($item['unidade']) ? $item['unidade'] : 'un';

        $html .= '
                            <tr>
                                <td>
                                    <div class="produto-nome">' . htmlspecialchars($produto_nome) . '</div>
                                    ' . (!empty($categoria_nome) ? '<div class="categoria">' . htmlspecialchars($categoria_nome) . '</div>' : '') . '
                                </td>
                                <td class="quantidade">' . $quantidade . ' ' . htmlspecialchars($unidade) . '</td>
                                <td class="preco">R$ ' . number_format($preco_unitario, 2, ',', '.') . '</td>
                                <td class="subtotal">R$ ' . number_format($subtotal_item, 2, ',', '.') . '</td>
                            </tr>';
    }

    $html .= '
                        </tbody>
                    </table>
                </div>

                <!-- Totais -->
                <div class="totais-section">
                    <div class="total-item">
                        <span class="total-label">Subtotal</span>
                        <span class="total-value">R$ ' . number_format($subtotal, 2, ',', '.') . '</span>
                    </div>
                    ' . ($desconto > 0 ? '
                    <div class="total-item">
                        <span class="total-label">Desconto</span>
                        <span class="total-value">- R$ ' . number_format($desconto, 2, ',', '.') . '</span>
                    </div>
                    ' : '') . '
                    <div class="total-item">
                        <span class="total-label">Total</span>
                        <span class="total-value">R$ ' . number_format($total, 2, ',', '.') . '</span>
                    </div>
                </div>

                ' . (!empty($observacoes) ? '
                <!-- Observações -->
                <div class="observacoes-section">
                    <div class="observacoes-title">Observações</div>
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
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-title">N.D CONNECT</div>
                <div class="footer-info">
                    <div class="footer-item">
                        <div class="footer-label">Telefone</div>
                        <div class="footer-value">(11) 99999-9999</div>
                    </div>
                    <div class="footer-item">
                        <div class="footer-label">E-mail</div>
                        <div class="footer-value">contato@ndconnect.com.br</div>
                    </div>
                    <div class="footer-item">
                        <div class="footer-label">Site</div>
                        <div class="footer-value">www.ndconnect.com.br</div>
                    </div>
                </div>
                <div class="footer-note">
                    Especializada em palcos, geradores, efeitos, stands, som, luz e painéis LED<br>
                    Sua parceira em eventos inesquecíveis
                </div>
            </div>
        </div>

        <script>
        function shareWhatsApp() {
            try {
                const orcamentoUrl = window.location.href;
                const pdfUrl = window.location.origin + "/pdf_real.php?id=' . $id . '";

                const message = "🏢 *N.D CONNECT - EQUIPAMENTOS PARA EVENTOS*\\n\\nOlá ' . htmlspecialchars($cliente_nome) . '! 👋\\n\\nSegue o orçamento solicitado:\\n\\n📋 *Orçamento Nº ' . str_pad($numero_orcamento, 6, '0', STR_PAD_LEFT) . '*\\n💰 *Valor Total: R$ ' . number_format($total, 2, ',', '.') . '*\\n📅 *Válido até: ' . date('d/m/Y', strtotime($data_validade)) . '*\\n\\n📄 *Baixar PDF:* " + pdfUrl + "\\n\\n✨ *Agradecemos pela preferência!*\\n🎉 *N.D Connect - Sua parceira em eventos inesquecíveis*";

                const telefone = "' . addslashes(htmlspecialchars($cliente_telefone)) . '";
                let whatsappUrl;

                if (telefone) {
                    // Limpar e formatar telefone
                    const telefoneLimpo = telefone.replace(/[^0-9]/g, "");
                    const telefoneFormatado = telefoneLimpo.startsWith("55") ? telefoneLimpo : "55" + telefoneLimpo;
                    whatsappUrl = "https://wa.me/" + telefoneFormatado + "?text=" + encodeURIComponent(message);
                } else {
                    whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(message);
                }

                window.open(whatsappUrl, "_blank");

            } catch (error) {
                console.error("Erro ao compartilhar no WhatsApp:", error);
                alert("Erro ao abrir WhatsApp. Tente novamente.");
            }
        }

        function downloadPDF() {
            try {
                window.open("pdf_real.php?id=' . $id . '", "_blank");
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
                        const file = new File([blob], "orcamento_' . strtolower(explode(' ', $cliente_nome)[0]) . '_' . $id . '.pdf", { type: "application/pdf" });

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
