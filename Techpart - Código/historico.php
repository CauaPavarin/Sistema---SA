<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}
require_once 'config.php';
$usuario = $_SESSION['usuario'];
// Buscar vendas
$sql = "SELECT v.id, v.data_venda, v.valor_total, c.nome AS cliente, vd.nome AS vendedor FROM vendas v JOIN clientes c ON v.id_cliente = c.id JOIN vendedores vd ON v.id_vendedor = vd.id ORDER BY v.data_venda DESC";
$res = $conn->query($sql);
$vendas = [];
while ($row = $res->fetch_assoc()) {
    $vendas[] = $row;
}
// Buscar itens de cada venda
$itens_venda = [];
if (count($vendas) > 0) {
    $ids = implode(',', array_column($vendas, 'id'));
    $sql = "SELECT iv.id_venda, p.descricao, iv.quantidade, iv.preco_unitario FROM itens_venda iv JOIN produtos p ON iv.id_produto = p.id WHERE iv.id_venda IN ($ids)";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $itens_venda[$row['id_venda']][] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Vendas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; min-height: 100vh; }
        .admin-header { background: linear-gradient(135deg, #6f42c1 0%, #4b2e83 100%); color: white; padding: 20px 0; margin-bottom: 30px; }
    </style>
</head>
<body>
<header class="admin-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 d-flex align-items-center">
                    <i class="fas fa-history me-3"></i> Histórico de Vendas
                </h3>
            </div>
            <div class="col-md-6 text-end">
                <span class="me-3">Bem-vindo, <span class="user-name"><?php echo htmlspecialchars($usuario); ?></span></span>
                <a href="admin_dashboard.php" class="logout-btn me-3"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                <a href="form_cadastrar_venda.php" class="btn btn-success me-2"><i class="fas fa-plus me-1"></i> Nova Venda</a>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
            </div>
        </div>
    </div>
</header>
<div class="container">
    <h2 class="mb-4">Histórico de Vendas</h2>
    <?php if (count($vendas) > 0): ?>
    <div class="accordion" id="accordionVendas">
        <?php foreach ($vendas as $i => $venda): ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?php echo $venda['id']; ?>">
                <button class="accordion-button <?php echo $i > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $venda['id']; ?>" aria-expanded="<?php echo $i == 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $venda['id']; ?>">
                    <strong>#<?php echo $venda['id']; ?></strong> | Cliente: <?php echo htmlspecialchars($venda['cliente']); ?> | Vendedor: <?php echo htmlspecialchars($venda['vendedor']); ?> | Data: <?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?> | Valor Total: <span class="text-success">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></span>
                </button>
            </h2>
            <div id="collapse<?php echo $venda['id']; ?>" class="accordion-collapse collapse <?php echo $i == 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $venda['id']; ?>" data-bs-parent="#accordionVendas">
                <div class="accordion-body">
                    <h6>Produtos vendidos:</h6>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Valor Unitário</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens_venda[$venda['id']] ?? [] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                                <td><?php echo $item['quantidade']; ?></td>
                                <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">Nenhuma venda registrada ainda.</div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 