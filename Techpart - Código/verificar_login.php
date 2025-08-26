<?php
session_start();

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config.php';
    
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    // Forçar tipo administrador
    $tipo = 'administrador';
    
    // Verificar se o usuário existe no banco de dados e validar a senha
    $sql = "SELECT id, usuario, senha, tipo FROM usuarios WHERE usuario = ? AND tipo = 'administrador'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Usuário encontrado, verificar a senha
        $row = $result->fetch_assoc();
        
        // Verificar se a senha está correta
        if ($row['senha'] === $senha) {
            // Login bem-sucedido
            $_SESSION['logado'] = true;
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['tipo'] = $row['tipo'];
            $_SESSION['usuario_id'] = $row['id'];
            
            // Redirecionar para o painel administrativo
            header("Location: admin_dashboard.php");
            exit;
        } else {
            // Senha incorreta
            header("Location: login.php?status=error");
            exit;
        }
    } else {
        // Usuário não encontrado ou não é administrador
        header("Location: login.php?status=error");
        exit;
    }
    
    $stmt->close();
    $conn->close();
} else {
    // Se alguém tentou acessar diretamente este arquivo
    header("Location: login.php");
    exit;
}
?> 