<?php
// Gerador de PDF do RECIBO (layout ND Connect) usando mPDF
error_reporting(0);
ini_set('display_errors', 0);

if (!class_exists('Mpdf\\Mpdf')) {
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    } else {
        die('mPDF não encontrado. Execute: composer install');
    }
}

use Mpdf\Mpdf;

require_once 'Config/Database.php';

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
    if (!$orcamento) return null;

    return $orcamento;
}

function getLogoBase64() {
    $logoPath = 'https://ndconnect.torquatoit.com/assets/img/logo.jpeg';
    $imageData = @file_get_contents($logoPath);
    if ($imageData !== false && !empty($imageData)) {
        return 'data:image/jpeg;base64,' . base64_encode($imageData);
    }

    $localPath = __DIR__ . '/../src/assets/img/logo.jpeg';
    if (file_exists($localPath)) {
        $imageData = file_get_contents($localPath);
        if ($imageData !== false) {
            return 'data:image/jpeg;base64,' . base64_encode($imageData);
        }
    }

    return null;
}

function gerarHTMLRecibo($orcamento, $logoBase64) {
    $clienteNome = !empty($orcamento['empresa']) ? $orcamento['empresa'] : ($orcamento['cliente_nome'] ?? '');
    $clienteNome = $clienteNome ?: '____________________';
    $email = $orcamento['email'] ?? '';
    $telefone = $orcamento['telefone'] ?? '';
    $dataOrcamento = !empty($orcamento['data_orcamento']) ? date('d/m/Y', strtotime($orcamento['data_orcamento'])) : '';
    $dataHoje = date('d/m/Y');
    $numero = isset($orcamento['numero_orcamento']) ? str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) : '------';
    $total = isset($orcamento['total']) ? number_format((float)$orcamento['total'], 2, ',', '.') : '0,00';
    $observacoes = $orcamento['observacoes'] ?? '';

    // IMPORTANTE: layout deve permanecer igual ao aprovado no front.
    return '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Recibo - N.D Connect</title>
  <style>
    :root {
      --nd-primary: #0C2B59;
      --nd-secondary: #E8622D;
      --nd-accent: #F7A64C;
      --nd-light: #FFFFFF;
      --nd-dark: #0C2B59;
      --nd-medium: #64748b;
    }
    * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    body { margin: 0; padding: 24px; background: #ffffff; color: var(--nd-dark); }
    .recibo-container {
      max-width: 800px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(12, 43, 89, 0.15);
      overflow: hidden;
      border: 1px solid rgba(12, 43, 89, 0.1);
    }
    .recibo-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 24px;
      background: linear-gradient(135deg, var(--nd-secondary) 0%, var(--nd-accent) 100%);
      color: #ffffff;
    }
    .recibo-header-left { display: flex; align-items: center; gap: 12px; }
    .recibo-logo {
      width: 56px;
      height: 56px;
      border-radius: 8px;
      object-fit: cover;
      border: 2px solid rgba(255,255,255,0.7);
    }
    .recibo-header-title h1 { margin: 0; font-size: 20px; letter-spacing: 0.5px; }
    .recibo-header-title p { margin: 2px 0 0 0; font-size: 12px; opacity: 0.9; }
    .recibo-header-right { text-align: right; font-size: 12px; }
    .recibo-header-right span.label {
      display: block; text-transform: uppercase; font-weight: 600; letter-spacing: 0.08em; font-size: 10px; opacity: 0.8;
    }
    .recibo-header-right span.valor { display: block; margin-top: 4px; font-size: 18px; font-weight: 700; }
    .recibo-body { padding: 24px; }
    .secao { margin-bottom: 18px; }
    .secao-titulo {
      font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--nd-primary);
      letter-spacing: 0.08em; margin-bottom: 6px;
    }
    .linha { font-size: 13px; color: var(--nd-medium); margin: 2px 0; }
    .linha strong { color: var(--nd-dark); }
    .valor-destaque { font-size: 16px; font-weight: 700; color: var(--nd-secondary); margin-top: 4px; }
    .texto-principal { font-size: 13px; line-height: 1.6; color: var(--nd-dark); margin-top: 8px; }
    .rodape { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 32px; font-size: 12px; color: var(--nd-medium); }
    .assinatura { text-align: center; margin-top: 40px; }
    .linha-assinatura { border-top: 1px solid rgba(12, 43, 89, 0.4); width: 260px; margin: 0 auto 6px auto; }
    .assinatura-nome { font-size: 12px; font-weight: 600; color: var(--nd-dark); }
    .assinatura-label { font-size: 11px; color: var(--nd-medium); }
    .recibo-footer {
      padding: 10px 24px 14px 24px;
      background: linear-gradient(135deg, rgba(12,43,89,0.03) 0%, rgba(232,98,45,0.06) 100%);
      border-top: 1px solid rgba(12, 43, 89, 0.08);
      font-size: 11px;
      color: var(--nd-medium);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .footer-left { font-weight: 500; color: var(--nd-primary); }
    .footer-right { text-align: right; }
  </style>
</head>
<body>
  <div class="recibo-container">
    <div class="recibo-header">
      <div class="recibo-header-left">
        ' . ($logoBase64 ? '<img src="' . $logoBase64 . '" alt="N.D Connect" class="recibo-logo" />' : '') . '
        <div class="recibo-header-title">
          <h1>N.D CONNECT</h1>
          <p>Equipamentos para eventos • Palcos • Geradores • Som • Luz • Painéis LED</p>
        </div>
      </div>
      <div class="recibo-header-right">
        <span class="label">Recibo</span>
        <span class="valor">R$ ' . $total . '</span>
        <span style="margin-top:4px;">Orçamento nº ' . $numero . '</span>
      </div>
    </div>
    <div class="recibo-body">
      <div class="secao">
        <div class="secao-titulo">Dados do Pagador</div>
        <div class="linha"><strong>Nome / Empresa:</strong> ' . htmlspecialchars($clienteNome) . '</div>
        ' . (!empty($email) ? '<div class="linha"><strong>E-mail:</strong> ' . htmlspecialchars($email) . '</div>' : '') . '
        ' . (!empty($telefone) ? '<div class="linha"><strong>Telefone:</strong> ' . htmlspecialchars($telefone) . '</div>' : '') . '
      </div>

      <div class="secao">
        <div class="secao-titulo">Detalhes do Recebimento</div>
        ' . (!empty($dataOrcamento) ? '<div class="linha"><strong>Data do Orçamento:</strong> ' . $dataOrcamento . '</div>' : '') . '
        <div class="linha"><strong>Data do Recibo:</strong> ' . $dataHoje . '</div>
        <div class="texto-principal">
          Recebemos de <strong>' . htmlspecialchars($clienteNome) . '</strong> a importância de
          <strong>R$ ' . $total . '</strong>, referente aos serviços prestados conforme
          Orçamento nº <strong>' . $numero . '</strong>.
        </div>
      </div>

      <div class="secao">
        <div class="secao-titulo">Valor Recebido</div>
        <div class="valor-destaque">R$ ' . $total . '</div>
        ' . (!empty($observacoes) ? '<div class="linha" style="margin-top:8px;"><strong>Observações:</strong> ' . nl2br(htmlspecialchars($observacoes)) . '</div>' : '') . '
      </div>

      <div class="assinatura">
        <div class="linha-assinatura"></div>
        <div class="assinatura-nome">N.D CONNECT - EQUIPAMENTOS PARA EVENTOS</div>
        <div class="assinatura-label">Responsável</div>
      </div>

      <div class="rodape">
        <div>Este recibo é válido como comprovante de pagamento.</div>
        <div>' . $dataHoje . '</div>
      </div>
    </div>
    <div class="recibo-footer">
      <div class="footer-left">N.D CONNECT - Equipamentos para Eventos</div>
      <div class="footer-right">
        <div>Contato: (11) 99999-9999</div>
        <div>E-mail: contato@ndconnect.com.br</div>
      </div>
    </div>
  </div>
</body>
</html>';
}

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die('ID do orçamento não fornecido');
    }

    $orcamentoId = (int)$_GET['id'];
    $orcamento = getOrcamentoData($orcamentoId);
    if (!$orcamento) {
        die('Orçamento não encontrado');
    }

    $logoBase64 = getLogoBase64();
    $html = gerarHTMLRecibo($orcamento, $logoBase64);

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'tempDir' => sys_get_temp_dir(),
    ]);

    $numero = isset($orcamento['numero_orcamento']) ? str_pad($orcamento['numero_orcamento'], 6, '0', STR_PAD_LEFT) : $orcamentoId;
    $filename = 'Recibo N° ' . $numero . '.pdf';

    $mpdf->SetTitle('Recibo N.D Connect - ' . $orcamentoId);
    $mpdf->SetAuthor('N.D Connect');
    $mpdf->SetCreator('N.D Connect');
    $mpdf->SetSubject('Recibo');

    $mpdf->WriteHTML($html);

    $action = $_GET['action'] ?? 'download'; // download por padrão
    if ($action === 'view') {
        // inline no navegador
        $mpdf->Output($filename, 'I');
    } else {
        // download direto do arquivo
        $mpdf->Output($filename, 'D');
    }
} catch (Exception $e) {
    error_log('Erro no PDF RECIBO mPDF: ' . $e->getMessage());
    header('Content-Type: text/html');
    die('Erro ao gerar PDF do recibo: ' . $e->getMessage());
}

