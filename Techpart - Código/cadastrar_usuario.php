<?php
// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config.php';
    
    // Limpar e validar dados
    $usuario = trim(mysqli_real_escape_string($conn, $_POST['usuario']));
    $senha = trim(mysqli_real_escape_string($conn, $_POST['senha']));
    $confirmar_senha = trim(mysqli_real_escape_string($conn, $_POST['confirmar_senha']));
    // Forçar tipo administrador
    $tipo = 'administrador';
    
    // Verificar se campos obrigatórios foram preenchidos
    if (empty($usuario) || empty($senha)) {
        header("Location: login.php?status=error");
        exit;
    }
    
    // Verificar se as senhas coincidem
    if ($senha != $confirmar_senha) {
        header("Location: login.php?status=error");
        exit;
    }
    
    // Em um sistema real, você deve criptografar a senha antes de armazenar
    // $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Inserir dados no banco
    $sql = "INSERT INTO usuarios (usuario, senha, tipo) VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $usuario, $senha, $tipo);
    
    if ($stmt->execute()) {
        header("Location: login.php?status=registered");
    } else {
        header("Location: login.php?status=error");
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    // Se tentou acessar diretamente, redireciona para a página inicial
    header("Location: login.php");
}
?> 