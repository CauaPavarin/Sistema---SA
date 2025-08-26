<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'config.php';
    
    // Limpar e validar dados
    $codigo = trim(mysqli_real_escape_string($conn, $_POST['codigo']));
    $descricao = trim(mysqli_real_escape_string($conn, $_POST['descricao']));
    $tipo = trim(mysqli_real_escape_string($conn, $_POST['tipo']));
    $preco = floatval(str_replace(',', '.', $_POST['preco']));
    $quantidade_estoque = intval($_POST['quantidade_estoque']);
    $fabricante = trim(mysqli_real_escape_string($conn, $_POST['fabricante']));
    $modelo = trim(mysqli_real_escape_string($conn, $_POST['modelo']));
    $especificacoes = trim(mysqli_real_escape_string($conn, $_POST['especificacoes']));
    $imagem_url = trim(mysqli_real_escape_string($conn, $_POST['imagem_url']));
    
    // Verificar se campos obrigatórios foram preenchidos
    if (empty($codigo) || empty($descricao) || empty($tipo) || $preco <= 0) {
        header("Location: form_cadastrar_produto.php?status=error&msg=campos_obrigatorios");
        exit;
    }

    $sql = "INSERT INTO produtos (codigo, descricao, tipo, preco, quantidade_estoque, fabricante, modelo, especificacoes, imagem_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdissss", $codigo, $descricao, $tipo, $preco, $quantidade_estoque, $fabricante, $modelo, $especificacoes, $imagem_url);
    
    if ($stmt->execute()) {
        header("Location: listar_produtos.php?status=success&msg=produto_cadastrado");
    } else {
        header("Location: form_cadastrar_produto.php?status=error&msg=erro_cadastro&erro=" . urlencode($conn->error));
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: listar_produtos.php");
}
?> 