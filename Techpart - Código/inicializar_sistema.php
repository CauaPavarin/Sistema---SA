<?php
// Arquivo de inicialização do sistema
// Este script cria os arquivos necessários para o funcionamento do sistema

// Definir os arquivos de dados
$arquivo_produtos = 'produtos.txt';

// Verificar e criar arquivo de produtos se não existir
if (!file_exists($arquivo_produtos)) {
    file_put_contents($arquivo_produtos, "");
    echo "Arquivo de produtos criado com sucesso.<br>";
} else {
    echo "Arquivo de produtos já existe.<br>";
}

// Verificar permissões
if (is_writable($arquivo_produtos)) {
    echo "O arquivo de produtos tem permissão de escrita.<br>";
} else {
    echo "<strong>ATENÇÃO:</strong> O arquivo de produtos não tem permissão de escrita. Por favor, verifique as permissões.<br>";
}

echo "<br>Sistema inicializado com sucesso!<br>";
echo "<br><a href='index.php'>Voltar para a página inicial</a>";
?> 