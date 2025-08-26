<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config.php';
    $nome = trim(mysqli_real_escape_string($conn, $_POST['nome']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $telefone = trim(mysqli_real_escape_string($conn, $_POST['telefone']));
    $endereco = trim(mysqli_real_escape_string($conn, $_POST['endereco']));
    if (empty($nome) || empty($email)) {
        header("Location: form_cadastrar_cliente.php?status=error&msg=campos_obrigatorios");
        exit;
    }
    $sql = "INSERT INTO clientes (nome, email, telefone, endereco) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $telefone, $endereco);
    if ($stmt->execute()) {
        header("Location: clientes.php?status=success&msg=cliente_cadastrado");
    } else {
        header("Location: form_cadastrar_cliente.php?status=error&msg=erro_cadastro");
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: clientes.php");
} 