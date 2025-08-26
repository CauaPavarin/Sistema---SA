<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
$usuario = $_SESSION['usuario'];
$tipo_usuario = $_SESSION['tipo'];

// Processar ações
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Resolver alerta
    if ($action === 'resolver' && isset($_POST['id_alerta'])) {
        $id_alerta = intval($_POST['id_alerta']);
        $id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 1; // Fallback para o usuário 1 se não houver ID na sessão
        
        $sql_resolver = "
            UPDATE alertas_estoque 
            SET resolvido = TRUE, 
                data_resolucao = NOW(), 
                id_usuario_resolucao = ?
            WHERE id = ?
        ";
        
        $stmt = $conn->prepare($sql_resolver);
        $stmt->bind_param("ii", $id_usuario, $id_alerta);
        
        if ($stmt->execute()) {
            $mensagem = "Alerta marcado como resolvido com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao resolver alerta: " . $conn->error;
            $tipo_mensagem = "danger";
        }
        
        $stmt->close();
    }
    
    // Reabrir alerta
    else if ($action === 'reabrir' && isset($_POST['id_alerta'])) {
        $id_alerta = intval($_POST['id_alerta']);
        
        $sql_reabrir = "
            UPDATE alertas_estoque 
            SET resolvido = FALSE, 
                data_resolucao = NULL, 
                id_usuario_resolucao = NULL
            WHERE id = ?
        ";
        
        $stmt = $conn->prepare($sql_reabrir);
        $stmt->bind_param("i", $id_alerta);
        
        if ($stmt->execute()) {
            $mensagem = "Alerta reaberto com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao reabrir alerta: " . $conn->error;
            $tipo_mensagem = "danger";
        }
        
        $stmt->close();
    }
    
    // Verificar estoque de todos os produtos
    else if ($action === 'verificar_estoque') {
        // 1. Obter produtos com estoque abaixo do mínimo
        $sql_verificar = "
            SELECT p.id, p.codigo, p.descricao, p.quantidade_estoque, 
                   COALESCE(ne.estoque_minimo, 5) as estoque_minimo,
                   COALESCE(ne.estoque_ideal, 20) as estoque_ideal
            FROM produtos p
            LEFT JOIN niveis_estoque ne ON p.id = ne.id_produto
            WHERE p.quantidade_estoque <= COALESCE(ne.estoque_minimo, 5)
        ";
        
        $result_verificar = $conn->query($sql_verificar);
        $alertas_criados = 0;
        $alertas_atualizados = 0;
        
        if ($result_verificar && $result_verificar->num_rows > 0) {
            while ($produto = $result_verificar->fetch_assoc()) {
                $id_produto = $produto['id'];
                $codigo = $produto['codigo'];
                $descricao = $produto['descricao'];
                $qtd_atual = $produto['quantidade_estoque'];
                $estoque_minimo = $produto['estoque_minimo'];
                
                // Determinar o tipo de alerta
                $tipo_alerta = ($qtd_atual <= ($estoque_minimo / 2)) ? 'critico' : 'baixo';
                
                // Verificar se já existe um alerta não resolvido para este produto
                $sql_check = "
                    SELECT id FROM alertas_estoque 
                    WHERE id_produto = ? AND resolvido = FALSE
                ";
                
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("i", $id_produto);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                
                if ($result_check->num_rows > 0) {
                    // Atualizar alerta existente
                    $alerta = $result_check->fetch_assoc();
                    $id_alerta = $alerta['id'];
                    
                    $mensagem_alerta = "Produto {$codigo} - {$descricao} com estoque baixo! Quantidade atual: {$qtd_atual}, Mínimo: {$estoque_minimo}";
                    
                    $sql_update = "
                        UPDATE alertas_estoque 
                        SET tipo = ?, mensagem = ?, data_alerta = NOW()
                        WHERE id = ?
                    ";
                    
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("ssi", $tipo_alerta, $mensagem_alerta, $id_alerta);
                    
                    if ($stmt_update->execute()) {
                        $alertas_atualizados++;
                    }
                    
                    $stmt_update->close();
                } else {
                    // Criar novo alerta
                    $mensagem_alerta = "Produto {$codigo} - {$descricao} com estoque baixo! Quantidade atual: {$qtd_atual}, Mínimo: {$estoque_minimo}";
                    
                    $sql_insert = "
                        INSERT INTO alertas_estoque (id_produto, tipo, mensagem, data_alerta)
                        VALUES (?, ?, ?, NOW())
                    ";
                    
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("iss", $id_produto, $tipo_alerta, $mensagem_alerta);
                    
                    if ($stmt_insert->execute()) {
                        $alertas_criados++;
                    }
                    
                    $stmt_insert->close();
                }
                
                $stmt_check->close();
            }
            
            $mensagem = "Verificação concluída! {$alertas_criados} novos alertas criados e {$alertas_atualizados} alertas atualizados.";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Nenhum produto com estoque baixo encontrado.";
            $tipo_mensagem = "info";
        }
    }
}

// Filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_resolvido = isset($_GET['resolvido']) ? $_GET['resolvido'] : '';
$filtro_produto = isset($_GET['produto']) ? $_GET['produto'] : '';

// Construir a consulta SQL com filtros
$sql_alertas = "
    SELECT a.*, p.codigo, p.descricao, p.quantidade_estoque, u.usuario as usuario_resolucao
    FROM alertas_estoque a
    JOIN produtos p ON a.id_produto = p.id
    LEFT JOIN usuarios u ON a.id_usuario_resolucao = u.id
    WHERE 1=1
";

// Adicionar filtros à consulta
if (!empty($filtro_tipo)) {
    $sql_alertas .= " AND a.tipo = '$filtro_tipo'";
}

if ($filtro_resolvido !== '') {
    $resolvido = ($filtro_resolvido === '1') ? 'TRUE' : 'FALSE';
    $sql_alertas .= " AND a.resolvido = $resolvido";
}

if (!empty($filtro_produto)) {
    $sql_alertas .= " AND (p.codigo LIKE '%$filtro_produto%' OR p.descricao LIKE '%$filtro_produto%')";
}

$sql_alertas .= " ORDER BY a.resolvido ASC, a.data_alerta DESC";

$result_alertas = $conn->query($sql_alertas);
$alertas = [];

if ($result_alertas) {
    while ($row = $result_alertas->fetch_assoc()) {
        $alertas[] = $row;
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
    <title>Alertas de Estoque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { 
            background-color: #f8f9fa; 
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23e9ecef' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
        }
        .page-header { 
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); 
            color: white; 
            padding: 20px 0; 
            margin-bottom: 30px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.8;
            z-index: 0;
        }
        .page-header .container {
            position: relative;
            z-index: 1;
        }
        .card { 
            border-radius: 15px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            border: none; 
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .card-header { 
            background-color: #fff; 
            border-bottom: 1px solid rgba(0,0,0,0.05); 
            font-weight: 600;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        .alert-card { 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            position: relative;
            overflow: hidden;
        }
        .alert-card:hover { 
            transform: translateY(-8px); 
        }
        .alert-badge { 
            position: absolute; 
            top: 10px; 
            right: 10px;
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            z-index: 2;
        }
        .alert-critical { 
            border-left: 4px solid #e74a3b; 
        }
        .alert-critical::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(231, 74, 59, 0.05) 0%, rgba(255, 255, 255, 0) 60%);
            z-index: 0;
        }
        .alert-low { 
            border-left: 4px solid #f6c23e; 
        }
        .alert-low::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(246, 194, 62, 0.05) 0%, rgba(255, 255, 255, 0) 60%);
            z-index: 0;
        }
        .alert-expiration { 
            border-left: 4px solid #36b9cc; 
        }
        .alert-expiration::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(54, 185, 204, 0.05) 0%, rgba(255, 255, 255, 0) 60%);
            z-index: 0;
        }
        .alert-resolved { 
            opacity: 0.7; 
            background-color: #f8f9fa;
        }
        .btn-icon { 
            width: 32px; 
            height: 32px; 
            padding: 0; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        .btn-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-outline-primary {
            border-width: 2px;
        }
        .btn-outline-success {
            border-width: 2px;
        }
        .btn-outline-warning {
            border-width: 2px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        .alert-status {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-pending {
            background-color: #f6c23e;
            box-shadow: 0 0 0 rgba(246, 194, 62, 0.4);
            animation: pulse-warning 2s infinite;
        }
        
        .status-critical {
            background-color: #e74a3b;
            box-shadow: 0 0 0 rgba(231, 74, 59, 0.4);
            animation: pulse-danger 1.5s infinite;
        }
        
        .status-resolved {
            background-color: #1cc88a;
        }
        
        @keyframes pulse-warning {
            0% {
                box-shadow: 0 0 0 0 rgba(246, 194, 62, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(246, 194, 62, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(246, 194, 62, 0);
            }
        }
        
        @keyframes pulse-danger {
            0% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(231, 74, 59, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(231, 74, 59, 0);
            }
        }
        
        .filter-container {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .filter-container:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .filter-title {
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .filter-title i {
            margin-right: 10px;
            color: #4e73df;
        }
        
        .form-select, .form-control {
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #858796 0%, #60616f 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(133, 135, 150, 0.3);
        }
        
        .card-body {
            position: relative;
            z-index: 1;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .alert-info {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .alert-warning {
            background-color: #fff8e1;
            color: #f57f17;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
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
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="m-0"><i class="fas fa-exclamation-triangle me-3"></i> Alertas de Estoque</h3>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Bem-vindo, <span class="fw-bold"><?php echo htmlspecialchars($usuario); ?></span></span>
                    <?php if ($tipo_usuario === 'administrador'): ?>
                        <a href="admin_dashboard.php" class="btn btn-light btn-sm me-2"><i class="fas fa-tachometer-alt me-1"></i> Painel Principal</a>
                    <?php else: ?>
                        <a href="cliente_dashboard.php" class="btn btn-light btn-sm me-2"><i class="fas fa-tachometer-alt me-1"></i> Painel Principal</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-light btn-sm"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show fade-in" role="alert">
                <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <div class="card filter-container fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="filter-title"><i class="fas fa-filter me-2"></i> Filtros</span>
                <div>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="verificar_estoque">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-sync-alt me-1"></i> Verificar Estoque
                        </button>
                    </form>
                    <a href="dashboard_analitico.php" class="btn btn-secondary btn-sm ms-2">
                        <i class="fas fa-chart-line me-1"></i> Dashboard
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="tipo" class="form-label">Tipo de Alerta</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="">Todos</option>
                            <option value="baixo" <?php echo $filtro_tipo === 'baixo' ? 'selected' : ''; ?>>Estoque Baixo</option>
                            <option value="critico" <?php echo $filtro_tipo === 'critico' ? 'selected' : ''; ?>>Estoque Crítico</option>
                            <option value="vencimento" <?php echo $filtro_tipo === 'vencimento' ? 'selected' : ''; ?>>Vencimento Próximo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="resolvido" class="form-label">Status</label>
                        <select name="resolvido" id="resolvido" class="form-select">
                            <option value="">Todos</option>
                            <option value="0" <?php echo $filtro_resolvido === '0' ? 'selected' : ''; ?>>Pendentes</option>
                            <option value="1" <?php echo $filtro_resolvido === '1' ? 'selected' : ''; ?>>Resolvidos</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="produto" class="form-label">Produto</label>
                        <input type="text" name="produto" id="produto" class="form-control" placeholder="Código ou descrição" value="<?php echo htmlspecialchars($filtro_produto); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <?php if (count($alertas) > 0): ?>
                <?php 
                $delay = 0;
                foreach ($alertas as $alerta): 
                    $delay += 0.1;
                ?>
                    <?php 
                        $card_class = '';
                        $badge_class = '';
                        $badge_text = '';
                        $status_class = '';
                        
                        if ($alerta['resolvido']) {
                            $card_class = 'alert-resolved';
                            $status_class = 'status-resolved';
                        }
                        
                        switch ($alerta['tipo']) {
                            case 'baixo':
                                $card_class .= ' alert-low';
                                $badge_class = 'bg-warning';
                                $badge_text = 'Estoque Baixo';
                                if (!$alerta['resolvido']) $status_class = 'status-pending';
                                break;
                            case 'critico':
                                $card_class .= ' alert-critical';
                                $badge_class = 'bg-danger';
                                $badge_text = 'Estoque Crítico';
                                if (!$alerta['resolvido']) $status_class = 'status-critical';
                                break;
                            case 'vencimento':
                                $card_class .= ' alert-expiration';
                                $badge_class = 'bg-info';
                                $badge_text = 'Vencimento Próximo';
                                if (!$alerta['resolvido']) $status_class = 'status-pending';
                                break;
                        }
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4 fade-in" style="animation-delay: <?php echo $delay; ?>s">
                        <div class="card alert-card <?php echo $card_class; ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><span class="alert-status <?php echo $status_class; ?>"></span><?php echo htmlspecialchars($alerta['codigo'] . ' - ' . $alerta['descricao']); ?></span>
                                <span class="badge <?php echo $badge_class; ?> <?php echo (!$alerta['resolvido'] && $alerta['tipo'] === 'critico') ? 'pulse' : ''; ?>"><?php echo $badge_text; ?></span>
                            </div>
                            <div class="card-body">
                                <p class="mb-1">
                                    <strong>Estoque Atual:</strong> 
                                    <span class="badge <?php echo $alerta['tipo'] === 'critico' ? 'bg-danger' : 'bg-warning'; ?>">
                                        <?php echo $alerta['quantidade_estoque']; ?>
                                    </span>
                                </p>
                                <p class="mb-3"><?php echo htmlspecialchars($alerta['mensagem']); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($alerta['data_alerta'])); ?>
                                    </small>
                                    
                                    <div class="btn-group">
                                        <a href="movimentacoes_estoque.php?produto=<?php echo $alerta['id_produto']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Adicionar Estoque">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                        
                                        <?php if (!$alerta['resolvido']): ?>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="resolver">
                                                <input type="hidden" name="id_alerta" value="<?php echo $alerta['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success btn-icon ms-1" title="Marcar como Resolvido">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="reabrir">
                                                <input type="hidden" name="id_alerta" value="<?php echo $alerta['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning btn-icon ms-1" title="Reabrir Alerta">
                                                    <i class="fas fa-redo-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($alerta['resolvido']): ?>
                                    <div class="mt-2 pt-2 border-top">
                                        <small class="text-muted">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Resolvido em <?php echo date('d/m/Y H:i', strtotime($alerta['data_resolucao'])); ?>
                                            <?php if ($alerta['usuario_resolucao']): ?>
                                                por <?php echo htmlspecialchars($alerta['usuario_resolucao']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state fade-in">
                        <div class="empty-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 class="empty-text">Nenhum alerta encontrado com os filtros selecionados.</h4>
                        <a href="alertas_estoque.php" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-2"></i> Limpar Filtros
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

