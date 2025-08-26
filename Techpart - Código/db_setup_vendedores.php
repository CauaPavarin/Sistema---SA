<?php
require_once 'config.php';

// SQL para criar tabela de vendedores (se não existir)
$sql = "CREATE TABLE IF NOT EXISTS vendedores (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    celular VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela de vendedores configurada com sucesso!";
} else {
    echo "Erro ao configurar tabela de vendedores: " . $conn->error;
}

// SQL para criar tabela de usuários (se não existir)
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('administrador', 'cliente') NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<br>Tabela de usuários configurada com sucesso!";
} else {
    echo "<br>Erro ao configurar tabela de usuários: " . $conn->error;
}

$conn->close();
?> 