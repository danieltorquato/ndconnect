<?php
// Teste específico para WhatsApp
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<h2>Teste de WhatsApp e PDF</h2>";

// Simular dados de orçamento para teste
$orcamentoTeste = [
    "id" => 1,
    "numero_orcamento" => 1,
    "cliente_nome" => "Cliente Teste",
    "email" => "teste@exemplo.com",
    "telefone" => "11999999999",
    "total" => 1000.00,
    "data_validade" => date("Y-m-d", strtotime("+10 days"))
];

echo "<h3>Dados do Orçamento de Teste:</h3>";
echo "ID: " . $orcamentoTeste["id"] . "<br>";
echo "Número: " . $orcamentoTeste["numero_orcamento"] . "<br>";
echo "Cliente: " . $orcamentoTeste["cliente_nome"] . "<br>";
echo "Total: R$ " . number_format($orcamentoTeste["total"], 2, ",", ".") . "<br>";

echo "<h3>Testando URLs:</h3>";

// Testar URL do PDF
$pdfUrl = "pdf_real.php?id=" . $orcamentoTeste["id"];
echo "URL do PDF: <a href='$pdfUrl' target='_blank'>$pdfUrl</a><br>";

// Testar se o arquivo existe
if (file_exists("pdf_real.php")) {
    echo "✅ pdf_real.php existe<br>";
} else {
    echo "❌ pdf_real.php não existe<br>";
}

// Testar se o TCPDF funciona
if (file_exists("vendor/autoload.php")) {
    require_once "vendor/autoload.php";
}

if (!class_exists("TCPDF") && file_exists("tcpdf_simple.php")) {
    require_once "tcpdf_simple.php";
}

if (class_exists("TCPDF")) {
    echo "✅ TCPDF disponível<br>";
    
    try {
        // Teste básico de PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
        $pdf->SetCreator("N.D Connect");
        $pdf->SetTitle("Teste WhatsApp");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 0, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
        
        $pdf->SetFont("helvetica", "B", 16);
        $pdf->Cell(0, 10, "Teste de PDF para WhatsApp", 0, 1, "C");
        
        echo "✅ PDF de teste criado com sucesso<br>";
        
    } catch (Exception $e) {
        echo "❌ Erro ao criar PDF: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ TCPDF não disponível<br>";
}

echo "<h3>Teste de JavaScript para WhatsApp:</h3>";
?>

<script>
// Função de teste para WhatsApp
async function testarWhatsApp() {
    console.log("Iniciando teste do WhatsApp...");
    
    try {
        const pdfUrl = "pdf_real.php?id=1";
        console.log("Tentando baixar PDF de:", pdfUrl);
        
        const response = await fetch(pdfUrl, {
            method: "GET",
            headers: {
                "Accept": "application/pdf"
            }
        });
        
        console.log("Status da resposta:", response.status);
        console.log("Headers:", Object.fromEntries(response.headers.entries()));
        
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status} - ${response.statusText}`);
        }
        
        const blob = await response.blob();
        console.log("Blob criado:", blob.type, blob.size, "bytes");
        
        if (blob.size === 0) {
            throw new Error("PDF vazio recebido");
        }
        
        // Testar se é realmente um PDF
        const arrayBuffer = await blob.arrayBuffer();
        const uint8Array = new Uint8Array(arrayBuffer);
        const pdfSignature = uint8Array.slice(0, 4);
        const isPdf = pdfSignature[0] === 0x25 && pdfSignature[1] === 0x50 && 
                     pdfSignature[2] === 0x44 && pdfSignature[3] === 0x46; // %PDF
        
        console.log("Assinatura PDF:", Array.from(pdfSignature).map(b => b.toString(16)).join(" "));
        console.log("É PDF válido:", isPdf);
        
        if (isPdf) {
            console.log("✅ PDF válido recebido!");
            alert("✅ PDF baixado com sucesso! Tamanho: " + blob.size + " bytes");
        } else {
            console.warn("⚠️ Arquivo não parece ser um PDF válido");
            alert("⚠️ Arquivo recebido não é um PDF válido");
        }
        
    } catch (error) {
        console.error("❌ Erro no teste:", error);
        alert("❌ Erro: " + error.message);
    }
}

// Executar teste automaticamente
document.addEventListener("DOMContentLoaded", function() {
    console.log("Página carregada, executando teste...");
    testarWhatsApp();
});
</script>

<button onclick="testarWhatsApp()" style="padding: 10px 20px; background: #25d366; color: white; border: none; border-radius: 5px; cursor: pointer;">
    🔄 Testar WhatsApp Novamente
</button>
