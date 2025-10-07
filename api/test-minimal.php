<?php
// Teste minimalista para identificar o erro 500
echo json_encode([
    'success' => true,
    'message' => 'Teste minimalista funcionando',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
