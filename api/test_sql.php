<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->connect();

    echo "Testando consulta SQL...\n";

    $stmt = $db->prepare("
        SELECT perm.pode_acessar, p.rota
        FROM permissoes_nivel perm
        JOIN paginas_sistema p ON perm.pagina_id = p.id
        WHERE perm.nivel_id = ? AND p.rota = ?
    ");
    $stmt->execute([1, 'painel']);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        echo "Resultado encontrado: " . json_encode($resultado) . "\n";
        echo "Pode acessar: " . ($resultado['pode_acessar'] ? 'SIM' : 'NÃO') . "\n";
    } else {
        echo "Nenhum resultado encontrado!\n";
    }

    // Testar com outras páginas
    $paginas = ['admin/gestao-leads', 'orcamento', 'produtos'];
    foreach ($paginas as $pagina) {
        $stmt->execute([1, $pagina]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $status = $resultado ? ($resultado['pode_acessar'] ? 'SIM' : 'NÃO') : 'NÃO ENCONTRADO';
        echo "$pagina: $status\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
