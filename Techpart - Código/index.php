<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}

// Redirecionar para a nova página de listagem de produtos
header("Location: listar_produtos.php");
exit;
?> 