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
if (isset($_POST['action']) && $tipo_usuario === 'administrador') {
    $action = $_POST['action'];
    
    // Alternar status do fornecedor (ativar/desativar)
    if ($action === 'toggle_status' && isset($_POST['id_fornecedor'])) {
        $id_fornecedor = intval($_POST['id_fornecedor']);
        $novo_status = isset($_POST['novo_status']) ? 1 : 0;
        
        $sql_update = "UPDATE fornecedores SET ativo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ii", $novo_status, $id_fornecedor);
        
        if ($stmt->execute()) {
            $mensagem = "Status do fornecedor atualizado com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao atualizar status do fornecedor: " . $conn->error;
            $tipo_mensagem = "danger";
        }
        
        $stmt->close();
    }
    
    // Excluir fornecedor
    else if ($action === 'excluir' && isset($_POST['id_fornecedor'])) {
        $id_fornecedor = intval($_POST['id_fornecedor']);
        
        // Verificar se o fornecedor está sendo usado em compras
        $sql_check = "SELECT COUNT(*) as total FROM compras WHERE id_fornecedor = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $id_fornecedor);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row = $result_check->fetch_assoc();
        
        if ($row['total'] > 0) {
            $mensagem = "Não é possível excluir este fornecedor pois ele está associado a compras existentes. Considere desativá-lo em vez de excluí-lo.";
            $tipo_mensagem = "warning";
        } else {
            // Excluir o fornecedor
            $sql_delete = "DELETE FROM fornecedores WHERE id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $id_fornecedor);
            
            if ($stmt_delete->execute()) {
                $mensagem = "Fornecedor excluído com sucesso!";
                $tipo_mensagem = "success";
            } else {
                $mensagem = "Erro ao excluir fornecedor: " . $conn->error;
                $tipo_mensagem = "danger";
            }
            
            $stmt_delete->close();
        }
        
        $stmt_check->close();
    }
}

// Filtros
$filtro_nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$filtro_cnpj = isset($_GET['cnpj']) ? $_GET['cnpj'] : '';
$filtro_status = isset($_GET['status']) ? $_GET['status'] : '';

// Construir a consulta SQL com filtros
$sql_fornecedores = "
    SELECT * FROM fornecedores
    WHERE 1=1
";

// Adicionar filtros à consulta
if (!empty($filtro_nome)) {
    $sql_fornecedores .= " AND nome LIKE '%" . $conn->real_escape_string($filtro_nome) . "%'";
}

if (!empty($filtro_cnpj)) {
    $sql_fornecedores .= " AND cnpj LIKE '%" . $conn->real_escape_string($filtro_cnpj) . "%'";
}

if ($filtro_status !== '') {
    $sql_fornecedores .= " AND ativo = " . ($filtro_status === '1' ? '1' : '0');
}

$sql_fornecedores .= " ORDER BY nome ASC";

$result_fornecedores = $conn->query($sql_fornecedores);
$fornecedores = [];

if ($result_fornecedores) {
    while ($row = $result_fornecedores->fetch_assoc()) {
        $fornecedores[] = $row;
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
    <title>Listar Fornecedores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; }
        .page-header { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; padding: 20px 0; margin-bottom: 30px; }
        .card { border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: none; }
        .card-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,0.05); font-weight: 600; }
        .table th { font-weight: 600; }
        .table td { vertical-align: middle; }
        .badge-status { width: 80px; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="m-0"><i class="fas fa-truck me-3"></i> Fornecedores</h3>
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
            <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-filter me-2"></i> Filtros</span>
                <?php if ($tipo_usuario === 'administrador'): ?>
                    <a href="cadastrar_fornecedor.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle me-1"></i> Novo Fornecedor
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($filtro_nome); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="cnpj" class="form-label">CNPJ</label>
                        <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?php echo htmlspecialchars($filtro_cnpj); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="1" <?php echo $filtro_status === '1' ? 'selected' : ''; ?>>Ativo</option>
                            <option value="0" <?php echo $filtro_status === '0' ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i> Lista de Fornecedores
                <span class="badge bg-secondary ms-2"><?php echo count($fornecedores); ?> fornecedores encontrados</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CNPJ</th>
                                <th>Contato</th>
                                <th>Telefone</th>
                                <th>E-mail</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($fornecedores) > 0): ?>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fornecedor['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($fornecedor['cnpj']); ?></td>
                                        <td><?php echo htmlspecialchars($fornecedor['contato']); ?></td>
                                        <td><?php echo htmlspecialchars($fornecedor['telefone']); ?></td>
                                        <td><?php echo htmlspecialchars($fornecedor['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $fornecedor['ativo'] ? 'bg-success' : 'bg-danger'; ?> badge-status">
                                                <?php echo $fornecedor['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="editar_fornecedor.php?id=<?php echo $fornecedor['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <a href="visualizar_fornecedor.php?id=<?php echo $fornecedor['id']; ?>" class="btn btn-sm btn-outline-info btn-icon ms-1" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($tipo_usuario === 'administrador'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id_fornecedor" value="<?php echo $fornecedor['id']; ?>">
                                                        <input type="hidden" name="novo_status" value="<?php echo $fornecedor['ativo'] ? '0' : '1'; ?>">
                                                        <button type="submit" class="btn btn-sm <?php echo $fornecedor['ativo'] ? 'btn-outline-warning' : 'btn-outline-success'; ?> btn-icon ms-1" title="<?php echo $fornecedor['ativo'] ? 'Desativar' : 'Ativar'; ?>">
                                                            <i class="fas <?php echo $fornecedor['ativo'] ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon ms-1" title="Excluir" 
                                                            data-bs-toggle="modal" data-bs-target="#modalExcluir<?php echo $fornecedor['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    
                                                    <!-- Modal de Confirmação de Exclusão -->
                                                    <div class="modal fade" id="modalExcluir<?php echo $fornecedor['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Confirmar Exclusão</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Tem certeza que deseja excluir o fornecedor <strong><?php echo htmlspecialchars($fornecedor['nome']); ?></strong>?</p>
                                                                    <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="action" value="excluir">
                                                                        <input type="hidden" name="id_fornecedor" value="<?php echo $fornecedor['id']; ?>">
                                                                        <button type="submit" class="btn btn-danger">Excluir</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i> Nenhum fornecedor encontrado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
