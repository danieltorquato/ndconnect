<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();

    echo "=== TESTE DA API DE LEADS ===\n\n";

    // 1. Verificar se a tabela de leads existe
    $stmt = $db->prepare("SHOW TABLES LIKE 'leads'");
    $stmt->execute();
    $tabelaExiste = $stmt->fetch();

    if (!$tabelaExiste) {
        echo "❌ Tabela 'leads' não existe!\n";
        echo "Criando tabela...\n";

        $sql = "CREATE TABLE leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            empresa VARCHAR(100),
            mensagem TEXT NOT NULL,
            origem VARCHAR(50) DEFAULT 'site',
            status ENUM('novo', 'contatado', 'qualificado', 'convertido', 'perdido') DEFAULT 'novo',
            lido BOOLEAN DEFAULT FALSE,
            data_leitura TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        $db->exec($sql);
        echo "✅ Tabela 'leads' criada\n\n";
    } else {
        echo "✅ Tabela 'leads' existe\n\n";
    }

    // 2. Verificar estrutura da tabela
    echo "📋 ESTRUTURA DA TABELA LEADS:\n";
    $stmt = $db->prepare("DESCRIBE leads");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($colunas as $coluna) {
        echo "   - {$coluna['Field']} ({$coluna['Type']}) - {$coluna['Null']} - {$coluna['Default']}\n";
    }
    echo "\n";

    // 3. Contar leads existentes
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM leads");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 TOTAL DE LEADS: {$total['total']}\n\n";

    // 4. Verificar leads por status
    echo "📈 LEADS POR STATUS:\n";
    $stmt = $db->prepare("
        SELECT
            status,
            COUNT(*) as quantidade,
            SUM(CASE WHEN lido = 0 OR lido IS NULL THEN 1 ELSE 0 END) as nao_lidos
        FROM leads
        GROUP BY status
        ORDER BY status
    ");
    $stmt->execute();
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stats as $stat) {
        echo "   - {$stat['status']}: {$stat['quantidade']} total, {$stat['nao_lidos']} não lidos\n";
    }
    echo "\n";

    // 5. Mostrar leads recentes
    echo "🕒 LEADS RECENTES (últimos 5):\n";
    $stmt = $db->prepare("
        SELECT id, nome, email, status, lido, created_at
        FROM leads
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($leads as $lead) {
        $lido = $lead['lido'] ? 'SIM' : 'NÃO';
        echo "   - ID: {$lead['id']} | {$lead['nome']} | {$lead['email']} | {$lead['status']} | Lido: {$lido} | {$lead['created_at']}\n";
    }
    echo "\n";

    // 6. Inserir leads de teste se não houver nenhum
    if ($total['total'] == 0) {
        echo "🔧 Inserindo leads de teste...\n";

        $leadsTeste = [
            ['João Silva', 'joao@email.com', '11999999999', 'Empresa A', 'Interesse em orçamento', 'novo'],
            ['Maria Santos', 'maria@email.com', '11888888888', 'Empresa B', 'Solicitação de proposta', 'contatado'],
            ['Pedro Costa', 'pedro@email.com', '11777777777', null, 'Dúvidas sobre produtos', 'qualificado'],
            ['Ana Oliveira', 'ana@email.com', '11666666666', 'Empresa C', 'Orçamento urgente', 'novo'],
            ['Carlos Lima', 'carlos@email.com', '11555555555', 'Empresa D', 'Proposta comercial', 'convertido']
        ];

        $stmt = $db->prepare("
            INSERT INTO leads (nome, email, telefone, empresa, mensagem, status, lido)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($leadsTeste as $lead) {
            $lido = in_array($lead[5], ['novo', 'contatado']) ? 0 : 1;
            $stmt->execute([$lead[0], $lead[1], $lead[2], $lead[3], $lead[4], $lead[5], $lido]);
        }

        echo "✅ Leads de teste inseridos\n\n";

        // Mostrar novamente as estatísticas
        echo "📈 NOVAS ESTATÍSTICAS:\n";
        $stmt->execute();
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($stats as $stat) {
            echo "   - {$stat['status']}: {$stat['quantidade']} total, {$stat['nao_lidos']} não lidos\n";
        }
    }

    echo "\n🎯 TESTE CONCLUÍDO!\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
