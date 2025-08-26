<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$tipo_usuario = $_SESSION['tipo'];
$mensagem = '';

// Processar mensagens de status
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success':
            $alertClass = 'alert-success';
            $msg = $_GET['msg'] ?? '';
            switch ($msg) {
                case 'produto_cadastrado':
                    $mensagem = 'Peça cadastrada com sucesso!';
                    break;
                case 'produto_atualizado':
                    $mensagem = 'Peça atualizada com sucesso!';
                    break;
                case 'produto_excluido':
                    $mensagem = 'Peça excluída com sucesso!';
                    break;
                default:
                    $mensagem = 'Operação realizada com sucesso!';
            }
            break;
        case 'error':
            $alertClass = 'alert-danger';
            $msg = $_GET['msg'] ?? '';
            switch ($msg) {
                case 'campos_obrigatorios':
                    $mensagem = 'Erro: Preencha todos os campos obrigatórios!';
                    break;
                case 'erro_cadastro':
                    $mensagem = 'Erro ao cadastrar a peça. Tente novamente.';
                    break;
                default:
                    $mensagem = 'Ocorreu um erro na operação.';
            }
            break;
    }
}

require_once 'config.php';
$produtos = [];
$sql = "SELECT * FROM produtos ORDER BY criado_em DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Parts - Catálogo de Peças</title>
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
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
        
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
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
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .product-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }
        
        .product-img {
            height: 180px;
            object-fit: contain;
            background-color: white;
            padding: 15px;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-img {
            transform: scale(1.05);
        }
        
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }
        
        .stock-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }
        
        .stock-low {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
            color: white;
            animation: pulse 2s infinite;
        }
        
        .stock-critical {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
            color: white;
            animation: pulse 1.5s infinite;
        }
        
        .stock-good {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
        }
        
        .card-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            font-weight: 600;
        }
        
        .product-title {
            font-weight: 600;
            font-size: 16px;
            height: 48px;
            overflow: hidden;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-description {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-weight: 700;
            font-size: 18px;
            color: #4e73df;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .product-stock {
            font-size: 14px;
            font-weight: 500;
        }
        
        .product-actions {
            margin-top: 15px;
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: translateY(-3px);
        }
        
        .search-box {
            position: relative;
            margin-bottom: 30px;
        }
        
        .search-input {
            border-radius: 50px;
            padding-left: 45px;
            height: 50px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .search-input:focus {
            box-shadow: 0 5px 20px rgba(78, 115, 223, 0.15);
            border-color: #4e73df;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .filter-select {
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.1);
            height: 45px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .filter-select:focus {
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.15);
            border-color: #4e73df;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            animation: fadeIn 0.8s;
        }
        
        .empty-icon {
            font-size: 60px;
            color: #d1d3e2;
            margin-bottom: 20px;
        }
        
        .empty-text {
            color: #858796;
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            color: #4e73df;
        }
        
        .table tbody tr {
            transition: all 0.3s;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fc;
            transform: translateX(5px);
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-name {
            font-weight: 600;
        }
        
        .pagination {
            margin-top: 30px;
            justify-content: center;
        }
        
        .page-link {
            border-radius: 50%;
            margin: 0 5px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4e73df;
            border: 1px solid #e3e6f0;
            transition: all 0.3s;
        }
        
        .page-link:hover {
            background-color: #4e73df;
            color: white;
            border-color: #4e73df;
        }
        
        .page-item.active .page-link {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #eaecf4;
            margin-top: 5px;
        }
        
        .progress-bar {
            border-radius: 4px;
        }
        
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            flex: 1;
            border-radius: 15px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            min-width: 200px;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 40px;
            opacity: 0.1;
        }
        
        .stat-title {
            font-size: 14px;
            color: #858796;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-change {
            font-size: 12px;
            display: flex;
            align-items: center;
        }
        
        .stat-up {
            color: #1cc88a;
        }
        
        .stat-down {
            color: #e74a3b;
        }
    </style>
</head>
<body>
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
                    <?php if ($tipo_usuario == 'administrador'): ?>
                    <a href="admin_dashboard.php" class="logout-btn me-2">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                    <?php endif; ?>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt me-1"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="page-title"><i class="fas fa-desktop me-2"></i> Catálogo de Peças</h2>
            
            <?php if ($tipo_usuario == 'administrador'): ?>
            <a href="form_cadastrar_produto.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Nova Peça
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($mensagem)): ?>
        <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i> <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php endif; ?>
        
        <!-- Listagem de Produtos -->
        <?php if (count($produtos) > 0): ?>
            <div class="row">
                <?php foreach ($produtos as $produto): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card h-100">
                            <div class="p-3 text-center" style="height: 220px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <?php if (!empty($produto['imagem_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" alt="<?php echo htmlspecialchars($produto['descricao']); ?>" class="img-fluid" style="max-height: 200px; width: auto;">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/300x200?text=Sem+Imagem" alt="Sem imagem" class="img-fluid" style="max-height: 200px; width: auto;">
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($produto['descricao']); ?></h5>
                                <p class="card-text text-muted">
                                    <small>
                                        <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($produto['codigo']); ?><br>
                                        <i class="fas fa-layer-group me-1"></i> <?php echo htmlspecialchars($produto['tipo']); ?>
                                    </small>
                                </p>
                                <p class="card-text mb-2">
                                    <?php if (!empty($produto['fabricante'])): ?>
                                        <span class="d-block"><i class="fas fa-industry me-1"></i> <?php echo htmlspecialchars($produto['fabricante']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($produto['modelo'])): ?>
                                        <span class="d-block"><i class="fas fa-info-circle me-1"></i> <?php echo htmlspecialchars($produto['modelo']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="fw-bold text-primary fs-5">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                    <span class="badge bg-<?php echo ($produto['quantidade_estoque'] > 0) ? 'success' : 'danger'; ?> rounded-pill">
                                        <?php echo ($produto['quantidade_estoque'] > 0) ? 'Em estoque' : 'Indisponível'; ?>
                                    </span>
                                </div>
                                
                                <?php if ($tipo_usuario == 'administrador'): ?>
                                <div class="mt-3 d-flex justify-content-between">
                                    <a href="editar_produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i> Editar</a>
                                    <a href="#" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $produto['id']; ?>">
                                        <i class="fas fa-trash-alt me-1"></i> Excluir
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal de Confirmação de Exclusão -->
                    <div class="modal fade" id="deleteModal<?php echo $produto['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $produto['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $produto['id']; ?>">Confirmar Exclusão</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Você tem certeza que deseja excluir o produto <strong><?php echo htmlspecialchars($produto['descricao']); ?></strong>?</p>
                                    <p class="text-danger">Esta ação não pode ser desfeita.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <a href="excluir_produto.php?id=<?php echo $produto['id']; ?>" class="btn btn-danger">Excluir</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Nenhum produto encontrado.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 