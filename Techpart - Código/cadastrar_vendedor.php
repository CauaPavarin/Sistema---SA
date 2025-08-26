<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config.php';
    
    // Limpar e validar dados
    $codigo = trim(mysqli_real_escape_string($conn, $_POST['codigo']));
    $nome = trim(mysqli_real_escape_string($conn, $_POST['nome']));
    $celular = trim(mysqli_real_escape_string($conn, $_POST['celular']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    
    // Verificar se campos obrigatórios foram preenchidos
    if (empty($codigo) || empty($nome) || empty($celular) || empty($email)) {
        header("Location: vendedores.php?status=error");
        exit;
    }
    
    // Inserir dados no banco
    $sql = "INSERT INTO vendedores (codigo, nome, celular, email) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $codigo, $nome, $celular, $email);
    
    if ($stmt->execute()) {
        header("Location: vendedores.php?status=success");
    } else {
        header("Location: vendedores.php?status=error");
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    // Se tentou acessar diretamente, redireciona para a página inicial
    header("Location: vendedores.php");
}
?> 