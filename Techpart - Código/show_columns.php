<?php
require_once 'config.php';

try {
    $stmt = $conn->query('DESCRIBE produtos');
    
    echo "Estrutura da tabela produtos:\n\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?> 