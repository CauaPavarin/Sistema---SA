<?php
// Verificar se a extensão mysqli está habilitada
if (!extension_loaded('mysqli')) {
    echo "<!DOCTYPE html>
    <html lang='pt-br'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Erro - Extensão mysqli não encontrada</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h4>Erro: Extensão mysqli não encontrada</h4>
                <p>A extensão mysqli do PHP não está habilitada no servidor. Por favor, habilite-a no arquivo php.ini e reinicie o servidor.</p>
                <p>Para habilitar a extensão mysqli:</p>
                <ol>
                    <li>Localize o arquivo php.ini (geralmente em C:\xampp\php\php.ini)</li>
                    <li>Remova o ponto e vírgula (;) da linha <code>;extension=mysqli</code> para <code>extension=mysqli</code></li>
                    <li>Salve o arquivo e reinicie o servidor Apache</li>
                </ol>
            </div>
            <a href='index.php' class='btn btn-primary'>Voltar para a página inicial</a>
        </div>
    </body>
    </html>";
    exit;
}

// Configurações de conexão
$host = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_cadastro";

// Criar conexão
try {
    $conn = new mysqli($host, $username, $password);
    
    // Verificar conexão
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html lang='pt-br'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Erro de Conexão</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h4>Erro de Conexão</h4>
                <p>" . $e->getMessage() . "</p>
                <p>Verifique se o servidor MySQL está em execução e se as credenciais estão corretas.</p>
            </div>
            <a href='index.php' class='btn btn-primary'>Voltar para a página inicial</a>
        </div>
    </body>
    </html>";
    exit;
}

echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificação da Estrutura do Banco de Dados</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
</head>
<body>
    <div class='container mt-5'>
        <h1>Verificação da Estrutura do Banco de Dados</h1>";

// Verificar se o banco de dados existe
$sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Criar banco de dados
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success'>Banco de dados '$dbname' criado com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger'>Erro ao criar banco de dados: " . $conn->error . "</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-info'>Banco de dados '$dbname' já existe.</div>";
}

// Selecionar o banco de dados
$conn->select_db($dbname);

// Verificar e criar tabelas necessárias
$tabelas = [
    "usuarios" => "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(50) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            tipo ENUM('administrador', 'cliente') NOT NULL,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ",
    "clientes" => "
        CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefone VARCHAR(20),
            endereco VARCHAR(255),
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ",
    "vendedores" => "
        CREATE TABLE IF NOT EXISTS vendedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(50) NOT NULL,
            nome VARCHAR(100) NOT NULL,
            celular VARCHAR(20) NOT NULL,
            email VARCHAR(100) NOT NULL,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ",
    "produtos" => "
        CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(20) NOT NULL,
            descricao VARCHAR(255) NOT NULL,
            tipo VARCHAR(50),
            preco DECIMAL(10,2) NOT NULL,
            custo DECIMAL(10,2) DEFAULT 0.00,
            quantidade_estoque INT DEFAULT 0,
            fabricante VARCHAR(100),
            modelo VARCHAR(100),
            especificacoes TEXT,
            imagem_url VARCHAR(255),
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ",
    "vendas" => "
        CREATE TABLE IF NOT EXISTS vendas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT,
            id_vendedor INT,
            data_venda DATETIME DEFAULT CURRENT_TIMESTAMP,
            valor_total DECIMAL(10,2) DEFAULT 0.00,
            FOREIGN KEY (id_cliente) REFERENCES clientes(id) ON DELETE RESTRICT,
            FOREIGN KEY (id_vendedor) REFERENCES vendedores(id) ON DELETE RESTRICT
        )
    ",
    "itens_venda" => "
        CREATE TABLE IF NOT EXISTS itens_venda (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_venda INT,
            id_produto INT,
            quantidade INT NOT NULL,
            preco_unitario DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (id_venda) REFERENCES vendas(id) ON DELETE CASCADE,
            FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE RESTRICT
        )
    ",
    "movimentacoes_estoque" => "
        CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_produto INT,
            tipo ENUM('entrada', 'saida') NOT NULL,
            quantidade INT NOT NULL,
            motivo VARCHAR(255),
            data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            id_usuario INT,
            FOREIGN KEY (id_produto) REFERENCES produtos(id) ON DELETE RESTRICT,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
        )
    "
];

echo "<h2 class='mt-4'>Verificando tabelas...</h2>";
echo "<ul class='list-group mt-3'>";

foreach ($tabelas as $tabela => $sql_create) {
    // Verificar se a tabela existe
    $sql = "SHOW TABLES LIKE '$tabela'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) {
        // Criar tabela
        if ($conn->query($sql_create) === TRUE) {
            echo "<li class='list-group-item list-group-item-success'>Tabela '$tabela' criada com sucesso!</li>";
        } else {
            echo "<li class='list-group-item list-group-item-danger'>Erro ao criar tabela '$tabela': " . $conn->error . "</li>";
        }
    } else {
        echo "<li class='list-group-item list-group-item-info'>Tabela '$tabela' já existe.</li>";
    }
}

echo "</ul>";

// Verificar se existe pelo menos um usuário administrador
$sql = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'administrador'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    // Criar usuário administrador padrão
    $sql = "INSERT INTO usuarios (usuario, senha, tipo) VALUES ('admin', 'admin123', 'administrador')";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success mt-3'>Usuário administrador padrão criado com sucesso! (Login: admin / Senha: admin123)</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Erro ao criar usuário administrador: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='alert alert-info mt-3'>Já existe pelo menos um usuário administrador no sistema.</div>";
}

// Criar índices para melhorar a performance
$indices = [
    "CREATE INDEX IF NOT EXISTS idx_produtos_codigo ON produtos(codigo)",
    "CREATE INDEX IF NOT EXISTS idx_vendas_data ON vendas(data_venda)",
    "CREATE INDEX IF NOT EXISTS idx_vendas_cliente ON vendas(id_cliente)",
    "CREATE INDEX IF NOT EXISTS idx_vendas_vendedor ON vendas(id_vendedor)",
    "CREATE INDEX IF NOT EXISTS idx_itens_venda_produto ON itens_venda(id_produto)",
    "CREATE INDEX IF NOT EXISTS idx_movimentacoes_produto ON movimentacoes_estoque(id_produto)",
    "CREATE INDEX IF NOT EXISTS idx_movimentacoes_data ON movimentacoes_estoque(data_movimentacao)"
];

echo "<h2 class='mt-4'>Criando índices...</h2>";
echo "<ul class='list-group mt-3'>";

foreach ($indices as $index_sql) {
    if ($conn->query($index_sql) === TRUE) {
        echo "<li class='list-group-item list-group-item-success'>Índice criado ou já existe!</li>";
    } else {
        echo "<li class='list-group-item list-group-item-danger'>Erro ao criar índice: " . $conn->error . "</li>";
    }
}

echo "</ul>";

$conn->close();

echo "<div class='mt-4'>
        <p>Verificação concluída!</p>
        <a href='index.php' class='btn btn-primary'>Voltar para a página inicial</a>
      </div>";

echo "</div>
</body>
</html>";
?> 