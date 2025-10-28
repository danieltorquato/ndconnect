<?php
// Arquivo para download do PDF completo com múltiplos shows
require_once 'pdf_real.php';

// Este arquivo será chamado quando o usuário quiser baixar o PDF completo
// Ele simplesmente chama o pdf_real.php com action=download
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    header("Location: pdf_real.php?id=" . $id . "&action=download");
    exit;
} else {
    die('ID do orçamento não fornecido');
}
?>
