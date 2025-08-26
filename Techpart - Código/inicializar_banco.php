<?php
// Configurações de conexão
$host = "localhost";
$username = "root";
$password = "";

// Criar conexão sem selecionar banco de dados
$conn = new mysqli($host, $username, $password);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Inicialização do Banco de Dados</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
</head>
<body>
    <div class='container mt-5'>
        <h1>Inicializando banco de dados do sistema de controle de estoque</h1>";

// Verificar se deve carregar dados de exemplo
$carregar_exemplos = isset($_GET['exemplos']) && $_GET['exemplos'] == '1';

// Ler o arquivo SQL
$sql = file_get_contents('criar_banco_dados.sql');

// Executar múltiplas consultas
if ($conn->multi_query($sql)) {
    echo "<div class='alert alert-success'><strong>Banco de dados criado com sucesso!</strong></div>";
    
    // Limpar resultados para evitar problemas
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    echo "<div class='mt-4'>As seguintes operações foram realizadas:</div>";
    echo "<ul class='list-group mt-2 mb-4'>";
    echo "<li class='list-group-item'>Criação do banco de dados 'sistema_cadastro'</li>";
    echo "<li class='list-group-item'>Criação da tabela de clientes</li>";
    echo "<li class='list-group-item'>Criação da tabela de vendedores</li>";
    echo "<li class='list-group-item'>Criação da tabela de produtos</li>";
    echo "<li class='list-group-item'>Criação da tabela de usuários</li>";
    echo "<li class='list-group-item'>Criação da tabela de vendas</li>";
    echo "<li class='list-group-item'>Criação da tabela de itens de venda</li>";
    echo "<li class='list-group-item'>Criação da tabela de movimentações de estoque</li>";
    echo "<li class='list-group-item'>Criação de índices para melhorar a performance</li>";
    echo "<li class='list-group-item'>Inserção de usuário administrador padrão (login: admin, senha: admin123)</li>";
    echo "</ul>";
    
    // Carregar dados de exemplo se solicitado
    if ($carregar_exemplos) {
        // Reconectar ao banco de dados
        $conn->close();
        $conn = new mysqli($host, $username, $password, "sistema_cadastro");
        
        if ($conn->connect_error) {
            echo "<div class='alert alert-danger'>Falha ao conectar para carregar dados de exemplo: " . $conn->connect_error . "</div>";
        } else {
            $sql_exemplos = file_get_contents('dados_exemplo.sql');
            
            if ($conn->multi_query($sql_exemplos)) {
                echo "<div class='alert alert-success'>Dados de exemplo carregados com sucesso!</div>";
                
                // Limpar resultados
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
            } else {
                echo "<div class='alert alert-danger'>Erro ao carregar dados de exemplo: " . $conn->error . "</div>";
            }
        }
    } else {
        echo "<div class='mt-3'>
                <a href='inicializar_banco.php?exemplos=1' class='btn btn-primary'>Carregar Dados de Exemplo</a>
              </div>";
    }
    
    echo "<div class='mt-4'>
            <p>O sistema está pronto para uso!</p>
            <a href='index.php' class='btn btn-success'>Ir para a página inicial</a>
          </div>";
} else {
    echo "<div class='alert alert-danger'>Erro ao criar banco de dados: " . $conn->error . "</div>";
}

$conn->close();

echo "</div>
</body>
</html>";
?> 