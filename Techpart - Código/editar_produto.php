<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

// Verificar se o ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listar_produtos.php");
    exit;
}

$id = intval($_GET['id']);
$usuario = $_SESSION['usuario'];
$error = null;
$produto = null;

// Buscar dados do produto no banco de dados
require_once 'config.php';

$sql = "SELECT * FROM produtos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $produto = $result->fetch_assoc();
} else {
    header("Location: listar_produtos.php");
    exit;
}

$stmt->close();

// Processar formulário de edição
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = trim(mysqli_real_escape_string($conn, $_POST['codigo']));
    $descricao = trim(mysqli_real_escape_string($conn, $_POST['descricao']));
    $tipo = trim(mysqli_real_escape_string($conn, $_POST['tipo']));
    $preco = floatval(str_replace(',', '.', $_POST['preco']));
    $quantidade_estoque = intval($_POST['quantidade_estoque']);
    $fabricante = trim(mysqli_real_escape_string($conn, $_POST['fabricante']));
    $modelo = trim(mysqli_real_escape_string($conn, $_POST['modelo']));
    $especificacoes = trim(mysqli_real_escape_string($conn, $_POST['especificacoes']));
    $imagem_url = trim(mysqli_real_escape_string($conn, $_POST['imagem_url']));
    if (empty($codigo) || empty($descricao) || empty($tipo) || $preco <= 0) {
        $error = "Todos os campos obrigatórios devem ser preenchidos.";
    } else {
        $sql = "UPDATE produtos SET codigo=?, descricao=?, tipo=?, preco=?, quantidade_estoque=?, fabricante=?, modelo=?, especificacoes=?, imagem_url=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdissssi", $codigo, $descricao, $tipo, $preco, $quantidade_estoque, $fabricante, $modelo, $especificacoes, $imagem_url, $id);
        if ($stmt->execute()) {
            header("Location: listar_produtos.php?status=success&msg=produto_atualizado");
            exit;
        } else {
            $error = "Erro ao atualizar produto: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Peça de Computador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding-top: 0;
        }
        
        .tech-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }
        
        .tech-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.8;
            z-index: -1;
        }
        
        .logout-btn {
            color: white;
            text-decoration: none;
            transition: all 0.2s;
            padding: 8px 15px;
            border-radius: 50px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .logout-btn:hover {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }
        
        .page-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
            color: #333;
            font-weight: 600;
            animation: fadeInUp 0.8s;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #4e73df, #224abe);
            border-radius: 3px;
            transition: width 0.3s;
        }
        
        .page-title:hover:after {
            width: 100px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            animation: fadeInUp 0.5s;
            margin-bottom: 30px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            padding: 20px;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
            color: white !important;
            font-weight: 600;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            border: 1px solid #e1e5eb;
            transition: all 0.3s;
            background-color: #f9fafc;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
            border-color: #4e73df;
            background-color: #fff;
        }
        
        .form-label {
            color: #4e5d78;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(78, 115, 223, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(78, 115, 223, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.2);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
        }
        
        .floating-element {
            position: absolute;
            background-color: rgba(78, 115, 223, 0.1);
            border-radius: 50%;
            z-index: -1;
            animation: float 15s infinite ease-in-out;
        }
        
        .element-1 {
            width: 80px;
            height: 80px;
            top: 15%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .element-2 {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 2s;
        }
        
        .element-3 {
            width: 100px;
            height: 100px;
            top: 40%;
            right: 10%;
            animation-delay: 4s;
        }
        
        .element-4 {
            width: 50px;
            height: 50px;
            bottom: 10%;
            right: 20%;
            animation-delay: 6s;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-10px, 10px) rotate(5deg); }
            50% { transform: translate(10px, 20px) rotate(0deg); }
            75% { transform: translate(15px, 5px) rotate(-5deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }
        
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px dashed #e1e5eb;
            animation: fadeIn 0.5s;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .specs-info {
            color: #6c757d;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .input-group-text {
            background-color: #4e73df;
            color: white;
            border: 1px solid #4e73df;
            border-radius: 10px 0 0 10px;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <!-- Elementos flutuantes decorativos -->
    <div class="floating-element element-1"></div>
    <div class="floating-element element-2"></div>
    <div class="floating-element element-3"></div>
    <div class="floating-element element-4"></div>

    <!-- Cabeçalho -->
    <header class="tech-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0"><i class="fas fa-microchip me-2"></i> Tech Parts</h1>
                    <p class="mb-0">Sistema de Gerenciamento de Peças de Computador</p>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Olá, <?php echo htmlspecialchars($usuario); ?>!</span>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h2 class="page-title"><i class="fas fa-edit me-2"></i> Editar Peça</h2>
                
                <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-microchip me-2"></i> Informações da Peça
                    </div>
                    <div class="card-body">
                        <form action="editar_produto.php?id=<?php echo $id; ?>" method="post">
                            <div class="form-section">
                                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i> Informações Básicas</h5>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="codigo" class="form-label">Código (SKU)*</label>
                                        <input type="text" class="form-control" id="codigo" name="codigo" required 
                                               value="<?php echo htmlspecialchars($produto['codigo']); ?>">
                                    </div>
                                    <div class="col-md-8">
                                        <label for="descricao" class="form-label">Descrição Curta*</label>
                                        <input type="text" class="form-control" id="descricao" name="descricao" required 
                                               value="<?php echo htmlspecialchars($produto['descricao'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tipo" class="form-label">Tipo*</label>
                                        <select class="form-select" id="tipo" name="tipo" required>
                                            <option value="" disabled>Selecione um tipo</option>
                                            <?php
                                            $tipos = ['Processador', 'Placa-mãe', 'Memória RAM', 'Placa de Vídeo', 
                                                           'Armazenamento', 'Gabinete', 'Fonte de Alimentação', 
                                                           'Cooler/Watercooler', 'Periféricos', 'Monitor', 'Notebook', 'Outros'];
                                            
                                            foreach ($tipos as $tipo) {
                                                $selected = ($produto['tipo'] == $tipo) ? 'selected' : '';
                                                echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fabricante" class="form-label">Fabricante</label>
                                        <input type="text" class="form-control" id="fabricante" name="fabricante" 
                                               value="<?php echo htmlspecialchars($produto['fabricante'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" 
                                           value="<?php echo htmlspecialchars($produto['modelo'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h5 class="mb-3"><i class="fas fa-dollar-sign me-2"></i> Informações de Estoque e Preço</h5>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="preco" class="form-label">Preço (R$)*</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="text" class="form-control" id="preco" name="preco" required
                                                   value="<?php echo number_format($produto['preco'] ?? 0, 2, ',', '.'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="quantidade_estoque" class="form-label">Quantidade em Estoque*</label>
                                        <input type="number" class="form-control" id="quantidade_estoque" name="quantidade_estoque" 
                                               required min="0" value="<?php echo intval($produto['quantidade_estoque'] ?? 0); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i> Descrição Detalhada</h5>
                                <div class="mb-3">
                                    <label for="especificacoes" class="form-label">Especificações Técnicas</label>
                                    <textarea class="form-control" id="especificacoes" name="especificacoes" rows="5"><?php echo htmlspecialchars($produto['especificacoes'] ?? ''); ?></textarea>
                                    <div class="specs-info">
                                        <i class="fas fa-info-circle me-1"></i> Dica: Insira as especificações em formato de lista, separando cada item com uma quebra de linha.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h5 class="mb-3"><i class="fas fa-image me-2"></i> Imagem</h5>
                                <div class="mb-3">
                                    <label for="imagem_url" class="form-label">URL da Imagem</label>
                                    <input type="text" class="form-control" id="imagem_url" name="imagem_url" 
                                           value="<?php echo htmlspecialchars($produto['imagem_url'] ?? ''); ?>">
                                    <div class="specs-info">
                                        <i class="fas fa-info-circle me-1"></i> Insira a URL de uma imagem do produto. Recomendamos imagens com fundo branco.
                                    </div>
                                </div>
                                
                                <?php if (!empty($produto['imagem_url'])): ?>
                                <div class="text-center mt-3">
                                    <p><strong>Visualização da Imagem:</strong></p>
                                    <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" alt="Imagem do Produto" 
                                         class="img-thumbnail" style="max-height: 200px;">
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="listar_produtos.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Voltar</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Formatação do campo de preço
        document.getElementById('preco').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value === '') {
                e.target.value = '0,00';
                return;
            }
            value = (parseInt(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            e.target.value = value;
        });
    </script>
</body>
</html> 