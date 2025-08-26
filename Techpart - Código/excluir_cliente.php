<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}
if (isset($_GET['id'])) {
    require_once 'config.php';
    $id = intval($_GET['id']);
    $sql = "DELETE FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: clientes.php?status=success&msg=cliente_excluido");
    } else {
        header("Location: clientes.php?status=error&msg=erro_excluir");
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: clientes.php");
} 