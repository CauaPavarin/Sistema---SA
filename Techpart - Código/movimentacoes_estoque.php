<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}
require_once 'config.php';
$usuario = $_SESSION['usuario'];

// Buscar ID do usuário no banco de dados
$sql = "SELECT id FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($id_usuario);
$stmt->fetch();
$stmt->close();

// Se não encontrou o usuário, criar um registro para ele
if (empty($id_usuario)) {
    $sql = "INSERT INTO usuarios (usuario, senha, tipo) VALUES (?, 'senha_padrao', 'administrador')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $id_usuario = $conn->insert_id;
    $stmt->close();
}

// Processar formulário de movimentação
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_produto = intval($_POST['id_produto']);
    $tipo = $_POST['tipo'];
    $quantidade = intval($_POST['quantidade']);
    $motivo = trim(mysqli_real_escape_string($conn, $_POST['motivo']));
    
    if ($id_produto > 0 && $quantidade > 0) {
        // Iniciar transação
        $conn->begin_transaction();
        
        try {
            // Registrar movimentação
            $sql = "INSERT INTO movimentacoes_estoque (id_produto, tipo, quantidade, motivo, id_usuario) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isisi", $id_produto, $tipo, $quantidade, $motivo, $id_usuario);
            $stmt->execute();
            
            // Atualizar estoque
            if ($tipo == 'entrada') {
                $sql = "UPDATE produtos SET quantidade_estoque = quantidade_estoque + ? WHERE id = ?";
            } else {
                $sql = "UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?";
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $quantidade, $id_produto);
            $stmt->execute();
            
            // Verificar se o estoque ficou negativo
            if ($tipo == 'saida') {
                $sql = "SELECT quantidade_estoque FROM produtos WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_produto);
                $stmt->execute();
                $stmt->bind_result($estoque_atual);
                $stmt->fetch();
                $stmt->close();
                
                if ($estoque_atual < 0) {
                    throw new Exception("Estoque não pode ficar negativo!");
                }
            }
            
            // Confirmar transação
            $conn->commit();
            $mensagem = "Movimentação registrada com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (Exception $e) {
            // Reverter transação em caso de erro
            $conn->rollback();
            $mensagem = "Erro: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    } else {
        $mensagem = "Erro: Produto ou quantidade inválidos!";
        $tipo_mensagem = "danger";
    }
}

// Buscar produtos para o formulário
$sql = "SELECT id, codigo, descricao, quantidade_estoque FROM produtos ORDER BY descricao";
$produtos = $conn->query($sql);

// Buscar últimas movimentações
$sql = "SELECT m.id, m.data_movimentacao, m.tipo, m.quantidade, m.motivo, 
               p.codigo, p.descricao, u.usuario 
        FROM movimentacoes_estoque m
        JOIN produtos p ON m.id_produto = p.id
        JOIN usuarios u ON m.id_usuario = u.id
        ORDER BY m.data_movimentacao DESC
        LIMIT 50";
$movimentacoes = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentações de Estoque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { 
            background-color: #f8f9fa; 
            min-height: 100vh; 
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23e9ecef' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
        }
        .admin-header { 
            background: linear-gradient(135deg, #fd7e14 0%, #c76a0b 100%); 
            color: white; 
            padding: 20px 0; 
            margin-bottom: 30px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        .admin-header::before {
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
        .admin-header .container {
            position: relative;
            z-index: 1;
        }
        .entrada { 
            color: #28a745; 
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
        .entrada i {
            margin-right: 5px;
            font-size: 14px;
        }
        .saida { 
            color: #dc3545; 
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
        .saida i {
            margin-right: 5px;
            font-size: 14px;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 20px;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .card-header h5 {
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .card-header h5 i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-label {
            font-weight: 500;
            color: #4e73df;
            margin-bottom: 8px;
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
        
        .btn-light {
            border-radius: 50px;
            font-weight: 500;
            padding: 8px 15px;
            transition: all 0.3s;
        }
        
        .btn-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .table-responsive {
            border-radius: 10px;
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
            padding: 12px 15px;
        }
        
        .table tbody tr {
            transition: all 0.3s;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fc;
            transform: translateX(5px);
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .user-name {
            font-weight: 600;
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-decoration: none;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .fade-in-delay-1 {
            animation: fadeIn 0.6s ease-out 0.1s forwards;
            opacity: 0;
        }
        
        .fade-in-delay-2 {
            animation: fadeIn 0.6s ease-out 0.2s forwards;
            opacity: 0;
        }
        
        .movimento-row-entrada {
            border-left: 3px solid #28a745;
        }
        
        .movimento-row-saida {
            border-left: 3px solid #dc3545;
        }
        
        .movimento-badge {
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .movimento-badge-entrada {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .movimento-badge-saida {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
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
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="m-0 d-flex align-items-center">
                        <i class="fas fa-boxes me-3"></i> Movimentações de Estoque
                    </h3>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Bem-vindo, <span class="user-name"><?php echo htmlspecialchars($usuario); ?></span></span>
                    <a href="admin_dashboard.php" class="logout-btn me-3"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    <a href="listar_produtos.php" class="btn btn-light me-2"><i class="fas fa-list me-1"></i> Produtos</a>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <?php if (isset($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show fade-in" role="alert">
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-minus me-2"></i> Nova Movimentação</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="id_produto" class="form-label">Produto</label>
                                <select name="id_produto" id="id_produto" class="form-select" required>
                                    <option value="">Selecione um produto</option>
                                    <?php while ($produto = $produtos->fetch_assoc()): ?>
                                    <option value="<?php echo $produto['id']; ?>"><?php echo htmlspecialchars($produto['codigo'] . ' - ' . $produto['descricao'] . ' (Estoque: ' . $produto['quantidade_estoque'] . ')'); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo de Movimentação</label>
                                <select name="tipo" id="tipo" class="form-select" required>
                                    <option value="entrada">Entrada</option>
                                    <option value="saida">Saída</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade</label>
                                <input type="number" name="quantidade" id="quantidade" class="form-control" min="1" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="motivo" class="form-label">Motivo</label>
                                <textarea name="motivo" id="motivo" class="form-control" rows="3" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Registrar Movimentação</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card fade-in-delay-1">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Últimas Movimentações</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Produto</th>
                                        <th>Tipo</th>
                                        <th>Qtd</th>
                                        <th>Motivo</th>
                                        <th>Usuário</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($movimentacoes->num_rows > 0): ?>
                                        <?php 
                                        $delay = 0;
                                        while ($mov = $movimentacoes->fetch_assoc()): 
                                            $delay += 0.05;
                                            $row_class = $mov['tipo'] == 'entrada' ? 'movimento-row-entrada' : 'movimento-row-saida';
                                            $badge_class = $mov['tipo'] == 'entrada' ? 'movimento-badge-entrada' : 'movimento-badge-saida';
                                        ?>
                                        <tr class="<?php echo $row_class; ?> fade-in" style="animation-delay: <?php echo $delay; ?>s">
                                            <td><?php echo date('d/m/Y H:i', strtotime($mov['data_movimentacao'])); ?></td>
                                            <td><?php echo htmlspecialchars($mov['codigo'] . ' - ' . $mov['descricao']); ?></td>
                                            <td>
                                                <?php if ($mov['tipo'] == 'entrada'): ?>
                                                <span class="entrada movimento-badge <?php echo $badge_class; ?>"><i class="fas fa-arrow-up"></i> Entrada</span>
                                                <?php else: ?>
                                                <span class="saida movimento-badge <?php echo $badge_class; ?>"><i class="fas fa-arrow-down"></i> Saída</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo $mov['quantidade']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($mov['motivo']); ?></td>
                                            <td><?php echo htmlspecialchars($mov['usuario']); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <div class="empty-icon">
                                                        <i class="fas fa-box-open"></i>
                                                    </div>
                                                    <h4 class="empty-text">Nenhuma movimentação encontrada</h4>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?> 