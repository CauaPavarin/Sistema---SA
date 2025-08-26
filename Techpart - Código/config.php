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

$host = "localhost";
$username = "root";
$password = "";
$dbname = "sistema_cadastro";

// Criar conexão
try {
    $conn = new mysqli($host, $username, $password, $dbname);

    // Verificar conexão
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
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
?> 