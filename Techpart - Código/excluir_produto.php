<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    require_once 'config.php';
    $id = intval($_GET['id']);
    $sql = "DELETE FROM produtos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: listar_produtos.php?status=success&msg=produto_excluido");
    } else {
        header("Location: listar_produtos.php?status=error&msg=erro_excluir");
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: listar_produtos.php");
}
?> 