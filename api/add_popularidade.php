<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $conn = $database->connect();

    echo "=== Adicionando Coluna de Popularidade ===\n";

    // Verificar se a coluna já existe
    $checkColumn = "SHOW COLUMNS FROM produtos LIKE 'popularidade'";
    $stmt = $conn->prepare($checkColumn);
    $stmt->execute();
    $columnExists = $stmt->fetch();

    if ($columnExists) {
        echo "✓ Coluna 'popularidade' já existe na tabela produtos.\n";
    } else {
        // Adicionar coluna de popularidade
        $addColumn = "ALTER TABLE produtos ADD COLUMN popularidade INT DEFAULT 0";
        $conn->exec($addColumn);
        echo "✓ Coluna 'popularidade' adicionada com sucesso!\n";
    }

    // Atualizar valores de popularidade
    echo "\n=== Atualizando Valores de Popularidade ===\n";

    $updates = [
        "Palco 3x3m" => 95,
        "Palco 4x4m" => 85,
        "Palco 6x4m" => 70,
        "Rampa de acesso" => 30,
        "Gerador 5KVA" => 90,
        "Gerador 10KVA" => 80,
        "Gerador 20KVA" => 60,
        "Gerador 30KVA" => 40,
        "Máquina de fumaça" => 75,
        "Máquina de neblina" => 65,
        "Canhão de luz" => 55,
        "Efeitos pirotécnicos" => 35,
        "Stand 3x3m" => 70,
        "Stand 6x3m" => 60,
        "Stand 9x3m" => 45,
        "Parede divisória" => 25,
        "Sistema de som 2.1" => 88,
        "Sistema de som 4.1" => 78,
        "Microfone sem fio" => 82,
        "Mixer de áudio" => 68,
        "Kit de iluminação básico" => 72,
        "Kit de iluminação profissional" => 62,
        "Laser show" => 52,
        "Stroboscópio" => 42,
        "Painel LED 2x1m" => 65,
        "Painel LED 3x2m" => 55,
        "Painel LED 4x3m" => 45,
        "Painel LED 6x4m" => 35
    ];

    $updateQuery = "UPDATE produtos SET popularidade = :popularidade WHERE nome = :nome";
    $stmt = $conn->prepare($updateQuery);

    $updated = 0;
    foreach ($updates as $nome => $popularidade) {
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':popularidade', $popularidade);
        if ($stmt->execute()) {
            echo "✓ {$nome}: {$popularidade} pontos\n";
            $updated++;
        }
    }

    echo "\n=== Verificação Final ===\n";

    // Verificar os produtos mais populares
    $checkQuery = "SELECT nome, popularidade FROM produtos ORDER BY popularidade DESC LIMIT 10";
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Top 10 produtos mais populares:\n";
    foreach ($produtos as $produto) {
        echo "• {$produto['nome']}: {$produto['popularidade']} pontos\n";
    }

    echo "\n✅ Processo concluído com sucesso!\n";
    echo "📊 Total de produtos atualizados: {$updated}\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
