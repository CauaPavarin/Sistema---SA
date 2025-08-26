<?php
// Configurações do banco de dados
$host = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_cadastro";

try {
    // Criar conexão usando mysqli
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Verificar conexão
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }
    
    echo "<h1>Conexão com o banco de dados estabelecida com sucesso!</h1>";
    
    // Exibir estrutura da tabela produtos
    $result = $conn->query("DESCRIBE produtos");
    
    echo "<h2>Estrutura da tabela produtos:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>Erro ao obter a estrutura da tabela: " . $conn->error . "</td></tr>";
    }
    
    echo "</table>";
    
    // Fechar conexão
    $conn->close();
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?> 