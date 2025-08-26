<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}
require_once 'config.php';
$usuario = $_SESSION['usuario'];
// Filtros rápidos
$filtro = $_GET['filtro'] ?? 'mes';
$where = '';
$periodo_label = '';
switch ($filtro) {
    case 'dia':
        $where = "WHERE DATE(v.data_venda) = CURDATE()";
        $periodo_label = 'Hoje';
        break;
    case 'semana':
        $where = "WHERE YEARWEEK(v.data_venda, 1) = YEARWEEK(CURDATE(), 1)";
        $periodo_label = 'Esta Semana';
        break;
    case 'mes':
        $where = "WHERE YEAR(v.data_venda) = YEAR(CURDATE()) AND MONTH(v.data_venda) = MONTH(CURDATE())";
        $periodo_label = 'Este Mês';
        break;
    case 'ano':
        $where = "WHERE YEAR(v.data_venda) = YEAR(CURDATE())";
        $periodo_label = 'Este Ano';
        break;
    default:
        $where = '';
        $periodo_label = 'Todas as Vendas';
}
// Buscar vendas filtradas
$sql = "SELECT v.id, v.data_venda, v.valor_total, c.nome AS cliente, vd.nome AS vendedor FROM vendas v JOIN clientes c ON v.id_cliente = c.id JOIN vendedores vd ON v.id_vendedor = vd.id $where ORDER BY v.data_venda DESC";
$res = $conn->query($sql);
$vendas = [];
$ids_vendas = [];
while ($row = $res->fetch_assoc()) {
    $vendas[] = $row;
    $ids_vendas[] = $row['id'];
}
// Calcular total vendido e lucro
$total_vendido = 0;
$total_lucro = 0;
if (count($ids_vendas) > 0) {
    $ids = implode(',', $ids_vendas);
    // Buscar itens das vendas
    $sql = "SELECT iv.id_venda, iv.quantidade, iv.preco_unitario, p.custo FROM itens_venda iv JOIN produtos p ON iv.id_produto = p.id WHERE iv.id_venda IN ($ids)";
    $resItens = $conn->query($sql);
    while ($item = $resItens->fetch_assoc()) {
        $total_vendido += $item['preco_unitario'] * $item['quantidade'];
        $total_lucro += ($item['preco_unitario'] - $item['custo']) * $item['quantidade'];
    }
}
// Gerar dados para o gráfico de vendas
$dados_grafico = [];
if (count($ids_vendas) > 0) {
    require_once 'config.php';
    $sql = "SELECT DATE(v.data_venda) as dia, SUM(v.valor_total) as total 
            FROM vendas v 
            WHERE v.id IN ($ids) 
            GROUP BY dia 
            ORDER BY dia";
    $resGraf = $conn->query($sql);
    while ($row = $resGraf->fetch_assoc()) {
        $dados_grafico[] = $row;
    }
    // Removido: $conn->close();
}

// Gerar dados para o gráfico de lucro
$dados_grafico_lucro = [];
if (count($ids_vendas) > 0) {
    require_once 'config.php';
    $sql = "SELECT DATE(v.data_venda) as dia, 
                   SUM((iv.preco_unitario - p.custo) * iv.quantidade) as lucro 
            FROM vendas v 
            JOIN itens_venda iv ON v.id = iv.id_venda 
            JOIN produtos p ON iv.id_produto = p.id 
            WHERE v.id IN ($ids) 
            GROUP BY dia 
            ORDER BY dia";
    $resGrafLucro = $conn->query($sql);
    while ($row = $resGrafLucro->fetch_assoc()) {
        $dados_grafico_lucro[] = $row;
    }
    // Removido: $conn->close();
}

// Gerar dados para o gráfico de pizza por vendedor
$dados_grafico_vendedor = [];
if (count($ids_vendas) > 0) {
    require_once 'config.php';
    $sql = "SELECT vd.nome AS vendedor, 
                   SUM(v.valor_total) as total 
            FROM vendas v 
            JOIN vendedores vd ON v.id_vendedor = vd.id 
            WHERE v.id IN ($ids) 
            GROUP BY vd.id 
            ORDER BY total DESC";
    $resGrafVendedor = $conn->query($sql);
    while ($row = $resGrafVendedor->fetch_assoc()) {
        $dados_grafico_vendedor[] = $row;
    }
    // Removido: $conn->close();
}

// Fechando a conexão apenas uma vez no final
if (isset($conn)) {
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentações de Vendas</title>
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
                    <i class="fas fa-exchange-alt me-3"></i> Movimentações de Vendas
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
    <h2 class="mb-4">Movimentações de Vendas</h2>
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="?filtro=dia" class="btn btn-outline-primary <?php if($filtro=='dia') echo 'active'; ?>">Hoje</a>
        <a href="?filtro=semana" class="btn btn-outline-primary <?php if($filtro=='semana') echo 'active'; ?>">Esta Semana</a>
        <a href="?filtro=mes" class="btn btn-outline-primary <?php if($filtro=='mes') echo 'active'; ?>">Este Mês</a>
        <a href="?filtro=ano" class="btn btn-outline-primary <?php if($filtro=='ano') echo 'active'; ?>">Este Ano</a>
        <a href="?filtro=todos" class="btn btn-outline-primary <?php if($filtro=='todos') echo 'active'; ?>">Todas</a>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="alert alert-info">
                <strong>Período:</strong> <?php echo $periodo_label; ?><br>
                <strong>Total Vendido:</strong> R$ <?php echo number_format($total_vendido, 2, ',', '.'); ?><br>
                <strong>Lucro:</strong> <span class="text-success">R$ <?php echo number_format($total_lucro, 2, ',', '.'); ?></span>
            </div>
        </div>
    </div>
    <?php if (count($dados_grafico) > 0): ?>
    <div class="mb-4">
        <canvas id="graficoVendas" height="80"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('graficoVendas').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dados_grafico, 'dia')); ?>,
                datasets: [{
                    label: 'Total Vendido (R$)',
                    data: <?php echo json_encode(array_map(function($d){return (float)$d['total'];}, $dados_grafico)); ?>,
                    borderColor: '#fd7e14',
                    backgroundColor: 'rgba(253,126,20,0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: 'Vendas por Dia' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
    <?php endif; ?>
    <?php if (count($dados_grafico_lucro) > 0): ?>
    <div class="mb-4">
        <canvas id="graficoLucro" height="80"></canvas>
    </div>
    <script>
        const ctxLucro = document.getElementById('graficoLucro').getContext('2d');
        const chartLucro = new Chart(ctxLucro, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dados_grafico_lucro, 'dia')); ?>,
                datasets: [{
                    label: 'Lucro (R$)',
                    data: <?php echo json_encode(array_map(function($d){return (float)$d['lucro'];}, $dados_grafico_lucro)); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: 'Lucro por Dia' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
    <?php endif; ?>
    <?php if (count($dados_grafico_vendedor) > 0): ?>
    <div class="mb-4">
        <canvas id="graficoVendedor" height="80"></canvas>
    </div>
    <script>
        const ctxVendedor = document.getElementById('graficoVendedor').getContext('2d');
        const chartVendedor = new Chart(ctxVendedor, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($dados_grafico_vendedor, 'vendedor')); ?>,
                datasets: [{
                    label: 'Total Vendido',
                    data: <?php echo json_encode(array_map(function($d){return (float)$d['total'];}, $dados_grafico_vendedor)); ?>,
                    backgroundColor: [
                        '#fd7e14','#4e73df','#36b9cc','#1cc88a','#f6c23e','#e74a3b','#858796','#5a5c69','#20c9a6','#6f42c1'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: 'Participação por Vendedor' }
                }
            }
        });
    </script>
    <?php endif; ?>
    <?php if (count($vendas) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Valor Total</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendas as $venda): ?>
                <tr>
                    <td><?php echo $venda['id']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                    <td><?php echo htmlspecialchars($venda['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($venda['vendedor']); ?></td>
                    <td>R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detalhesModal<?php echo $venda['id']; ?>">
                            <i class="fas fa-search"></i> Ver Detalhes
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="alert alert-info">Nenhuma movimentação de venda registrada ainda.</div>
    <?php endif; ?>
    <!-- Modais de detalhes -->
    <?php foreach ($vendas as $venda): ?>
    <div class="modal fade" id="detalhesModal<?php echo $venda['id']; ?>" tabindex="-1" aria-labelledby="detalhesModalLabel<?php echo $venda['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalhesModalLabel<?php echo $venda['id']; ?>">Detalhes da Venda #<?php echo $venda['id']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php
                    require 'config.php';
                    $sql = "SELECT p.descricao, iv.quantidade, iv.preco_unitario FROM itens_venda iv JOIN produtos p ON iv.id_produto = p.id WHERE iv.id_venda = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $venda['id']);
                    $stmt->execute();
                    $resItens = $stmt->get_result();
                    ?>
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
                            <?php while ($item = $resItens->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                                <td><?php echo $item['quantidade']; ?></td>
                                <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php $stmt->close(); $conn->close(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 