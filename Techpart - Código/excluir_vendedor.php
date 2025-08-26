<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Verificar se o ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: vendedores.php");
    exit;
}

$id = intval($_GET['id']);

// Excluir vendedor
$sql = "DELETE FROM vendedores WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: vendedores.php?status=success");
} else {
    header("Location: vendedores.php?status=error");
}

$stmt->close();
$conn->close();
?> 