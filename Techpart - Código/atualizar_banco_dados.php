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
    <title>Atualização do Banco de Dados</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
</head>
<body>
    <div class='container mt-5'>
        <h1>Atualização do Banco de Dados</h1>";

// Ler o arquivo SQL
$sql = file_get_contents('adicionar_novas_tabelas.sql');

// Executar múltiplas consultas
if ($conn->multi_query($sql)) {
    echo "<div class='alert alert-success'>Banco de dados atualizado com sucesso!</div>";
    
    // Limpar resultados para evitar problemas
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    echo "<div class='mt-4'>
            <p>As seguintes tabelas foram adicionadas ou atualizadas:</p>
            <ul class='list-group'>
                <li class='list-group-item'>fornecedores</li>
                <li class='list-group-item'>compras</li>
                <li class='list-group-item'>itens_compra</li>
                <li class='list-group-item'>avaliacao_fornecedores</li>
                <li class='list-group-item'>niveis_estoque</li>
                <li class='list-group-item'>alertas_estoque</li>
                <li class='list-group-item'>servicos_tecnicos</li>
                <li class='list-group-item'>pecas_servico</li>
                <li class='list-group-item'>historico_servicos</li>
                <li class='list-group-item'>configuracoes_relatorios</li>
            </ul>
          </div>";
    
    echo "<div class='mt-4'>
            <p>O sistema está pronto para uso com as novas funcionalidades!</p>
            <a href='index.php' class='btn btn-primary'>Ir para a página inicial</a>
          </div>";
} else {
    echo "<div class='alert alert-danger'>Erro ao atualizar banco de dados: " . $conn->error . "</div>";
}

$conn->close();

echo "</div>
</body>
</html>";
?> 