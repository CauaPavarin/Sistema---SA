<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}
require_once 'config.php';
$usuario = $_SESSION['usuario'];
// Buscar clientes
$clientes = [];
$res = $conn->query("SELECT id, nome FROM clientes ORDER BY nome");
while ($row = $res->fetch_assoc()) {
    $clientes[] = $row;
}
// Buscar vendedores
$vendedores = [];
$res = $conn->query("SELECT id, nome FROM vendedores ORDER BY nome");
while ($row = $res->fetch_assoc()) {
    $vendedores[] = $row;
}
// Buscar produtos
$produtos = [];
$res = $conn->query("SELECT id, descricao, preco, quantidade_estoque FROM produtos WHERE quantidade_estoque > 0 ORDER BY descricao");
while ($row = $res->fetch_assoc()) {
    $produtos[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Venda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; min-height: 100vh; }
        .admin-header { background: linear-gradient(135deg, #fd7e14 0%, #c76a0b 100%); color: white; padding: 20px 0; margin-bottom: 30px; }
    </style>
</head>
<body>
<header class="admin-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 d-flex align-items-center">
                    <i class="fas fa-exchange-alt me-3"></i> Nova Venda
                </h3>
            </div>
            <div class="col-md-6 text-end">
                <span class="me-3">Bem-vindo, <span class="user-name"><?php echo htmlspecialchars($usuario); ?></span></span>
                <a href="admin_dashboard.php" class="logout-btn me-3"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
            </div>
        </div>
    </div>
</header>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header"><i class="fas fa-exchange-alt me-2"></i> Registrar Nova Venda</div>
                <div class="card-body">
                    <form action="cadastrar_venda.php" method="post">
                        <div class="mb-3">
                            <label for="id_cliente" class="form-label">Cliente*</label>
                            <select class="form-select" id="id_cliente" name="id_cliente" required>
                                <option value="" selected disabled>Selecione o cliente</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_vendedor" class="form-label">Vendedor*</label>
                            <select class="form-select" id="id_vendedor" name="id_vendedor" required>
                                <option value="" selected disabled>Selecione o vendedor</option>
                                <?php foreach ($vendedores as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo htmlspecialchars($v['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Produtos*</label>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Preço</th>
                                            <th>Estoque</th>
                                            <th>Quantidade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produtos as $p): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['descricao']); ?></td>
                                            <td>R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                                            <td><?php echo $p['quantidade_estoque']; ?></td>
                                            <td>
                                                <input type="number" class="form-control" name="produtos[<?php echo $p['id']; ?>]" min="0" max="<?php echo $p['quantidade_estoque']; ?>" value="0">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save me-2"></i>Registrar Venda</button>
                        <a href="admin_dashboard.php" class="btn btn-secondary ms-2">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 