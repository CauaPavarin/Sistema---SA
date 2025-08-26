<?php
// Configurações de conexão
$host = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_cadastro";

// Criar conexão
$conn = new mysqli($host, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Importação de Dados de Exemplo</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
</head>
<body>
    <div class='container mt-5'>
        <h1>Importação de Dados de Exemplo</h1>";

// Ler o arquivo SQL
$sql = file_get_contents('dados_exemplo.sql');

// Executar múltiplas consultas
if ($conn->multi_query($sql)) {
    echo "<div class='alert alert-success'>Dados de exemplo importados com sucesso!</div>";
    
    // Limpar resultados para evitar problemas
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    echo "<div class='mt-3'>Foram adicionados:</div>";
    echo "<ul class='list-group mt-2 mb-4'>";
    echo "<li class='list-group-item'>5 clientes de exemplo</li>";
    echo "<li class='list-group-item'>3 vendedores de exemplo</li>";
    echo "<li class='list-group-item'>10 produtos de exemplo</li>";
    echo "<li class='list-group-item'>5 vendas com seus respectivos itens</li>";
    echo "<li class='list-group-item'>Movimentações de estoque</li>";
    echo "</ul>";
    
    echo "<div class='mt-4'>
            <p>O sistema está pronto para uso!</p>
            <a href='index.php' class='btn btn-success'>Ir para a página inicial</a>
          </div>";
} else {
    echo "<div class='alert alert-danger'>Erro ao importar dados de exemplo: " . $conn->error . "</div>";
}

$conn->close();

echo "</div>
</body>
</html>";
?> 