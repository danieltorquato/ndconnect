<?php
require_once 'Config/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();

    // Criar uma imagem base64 de teste pequena
    $imagem_base64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    // Testar inserção
    $stmt = $pdo->prepare("INSERT INTO funcionarios (nome_completo, cargo, data_admissao, foto) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([
        'Teste Imagem',
        'Teste',
        '2024-01-01',
        $imagem_base64
    ]);

    if ($result) {
        $id = $pdo->lastInsertId();

        // Verificar se foi salva corretamente
        $stmt = $pdo->prepare("SELECT foto FROM funcionarios WHERE id = ?");
        $stmt->execute([$id]);
        $foto_salva = $stmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'imagem_original' => $imagem_base64,
            'imagem_salva' => $foto_salva,
            'tamanho_original' => strlen($imagem_base64),
            'tamanho_salva' => strlen($foto_salva),
            'sao_iguais' => $imagem_base64 === $foto_salva
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao inserir'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>
