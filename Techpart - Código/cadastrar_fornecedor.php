<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
$usuario = $_SESSION['usuario'];

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar dados do formulário
    $nome = trim($_POST['nome']);
    $cnpj = trim($_POST['cnpj']);
    $contato = trim($_POST['contato']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $endereco = trim($_POST['endereco']);
    $site = trim($_POST['site']);
    $observacoes = trim($_POST['observacoes']);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Validar campos obrigatórios
    $erros = [];
    if (empty($nome)) {
        $erros[] = "Nome do fornecedor é obrigatório";
    }
    if (empty($cnpj)) {
        $erros[] = "CNPJ é obrigatório";
    }
    
    // Se não houver erros, inserir no banco de dados
    if (empty($erros)) {
        $sql = "INSERT INTO fornecedores (nome, cnpj, contato, telefone, email, endereco, site, observacoes, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $nome, $cnpj, $contato, $telefone, $email, $endereco, $site, $observacoes, $ativo);
        
        if ($stmt->execute()) {
            $mensagem = "Fornecedor cadastrado com sucesso!";
            $tipo_mensagem = "success";
            
            // Limpar os campos após o cadastro bem-sucedido
            $nome = $cnpj = $contato = $telefone = $email = $endereco = $site = $observacoes = "";
            $ativo = 1;
        } else {
            $mensagem = "Erro ao cadastrar fornecedor: " . $conn->error;
            $tipo_mensagem = "danger";
        }
        
        $stmt->close();
    } else {
        $mensagem = "Por favor, corrija os seguintes erros:<br>" . implode("<br>", $erros);
        $tipo_mensagem = "danger";
    }
}

// Fechar conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Fornecedor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; }
        .page-header { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; padding: 20px 0; margin-bottom: 30px; }
        .card { border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: none; }
        .card-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,0.05); font-weight: 600; }
        .form-label { font-weight: 500; }
        .required::after { content: " *"; color: red; }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="m-0"><i class="fas fa-truck me-3"></i> Cadastrar Fornecedor</h3>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Bem-vindo, <span class="fw-bold"><?php echo htmlspecialchars($usuario); ?></span></span>
                    <a href="admin_dashboard.php" class="btn btn-light btn-sm me-2"><i class="fas fa-tachometer-alt me-1"></i> Painel Principal</a>
                    <a href="logout.php" class="btn btn-light btn-sm"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-2"></i> Novo Fornecedor
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nome" class="form-label required">Nome da Empresa</label>
                                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cnpj" class="form-label required">CNPJ</label>
                                    <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?php echo isset($cnpj) ? htmlspecialchars($cnpj) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="contato" class="form-label">Nome do Contato</label>
                                    <input type="text" class="form-control" id="contato" name="contato" value="<?php echo isset($contato) ? htmlspecialchars($contato) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo isset($telefone) ? htmlspecialchars($telefone) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="site" class="form-label">Site</label>
                                    <input type="url" class="form-control" id="site" name="site" value="<?php echo isset($site) ? htmlspecialchars($site) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo isset($endereco) ? htmlspecialchars($endereco) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo isset($observacoes) ? htmlspecialchars($observacoes) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="ativo" name="ativo" <?php echo (!isset($ativo) || $ativo) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="ativo">Fornecedor Ativo</label>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Salvar Fornecedor
                                </button>
                                <a href="listar_fornecedores.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-list me-2"></i> Listar Fornecedores
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para CNPJ
        document.getElementById('cnpj').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 14) {
                value = value.substring(0, 14);
            }
            
            // Formatar CNPJ: XX.XXX.XXX/XXXX-XX
            if (value.length > 12) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
            } else if (value.length > 8) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4}).*/, '$1.$2.$3/$4');
            } else if (value.length > 5) {
                value = value.replace(/^(\d{2})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,3}).*/, '$1.$2');
            }
            
            e.target.value = value;
        });
        
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            // Formatar telefone: (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
            }
            
            e.target.value = value;
        });
    </script>
</body>
</html> 