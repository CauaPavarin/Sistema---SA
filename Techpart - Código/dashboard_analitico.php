<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
$usuario = $_SESSION['usuario'];

// Obter dados para o dashboard
// 1. Produtos com estoque baixo
$sql_estoque_baixo = "
    SELECT p.id, p.codigo, p.descricao, p.quantidade_estoque, ne.estoque_minimo
    FROM produtos p
    LEFT JOIN niveis_estoque ne ON p.id = ne.id_produto
    WHERE p.quantidade_estoque <= COALESCE(ne.estoque_minimo, 5)
    ORDER BY p.quantidade_estoque ASC
    LIMIT 10
";
$result_estoque_baixo = $conn->query($sql_estoque_baixo);
$produtos_estoque_baixo = [];
if ($result_estoque_baixo) {
    while ($row = $result_estoque_baixo->fetch_assoc()) {
        $produtos_estoque_baixo[] = $row;
    }
}

// 2. Produtos mais vendidos
$sql_produtos = "
    SELECT p.descricao, SUM(iv.quantidade) as total_vendido
    FROM itens_venda iv
    JOIN produtos p ON iv.id_produto = p.id
    GROUP BY iv.id_produto
    ORDER BY total_vendido DESC
    LIMIT 10
";
$result_produtos = $conn->query($sql_produtos);
$produtos_mais_vendidos = [];
if ($result_produtos) {
    while ($row = $result_produtos->fetch_assoc()) {
        $produtos_mais_vendidos[] = $row;
    }
}

// Consulta de vendas por dia
$sql_vendas_dia = "
    SELECT DATE(data_venda) as dia, SUM(valor_total) as valor_total, COUNT(*) as total_vendas
    FROM vendas
    WHERE data_venda >= DATE_SUB(CURRENT_DATE(), INTERVAL 15 DAY)
    GROUP BY dia
    ORDER BY dia
";
$result_vendas_dia = $conn->query($sql_vendas_dia);
$vendas_por_dia = [];
if ($result_vendas_dia) {
    while ($row = $result_vendas_dia->fetch_assoc()) {
        $vendas_por_dia[] = $row;
    }
}

// 5. Alertas de estoque não resolvidos
$sql_alertas = "
    SELECT a.id, a.tipo, a.mensagem, a.data_alerta, p.codigo, p.descricao
    FROM alertas_estoque a
    JOIN produtos p ON a.id_produto = p.id
    WHERE a.resolvido = FALSE
    ORDER BY a.data_alerta DESC
    LIMIT 10
";
$result_alertas = $conn->query($sql_alertas);
$alertas_estoque = [];
if ($result_alertas) {
    while ($row = $result_alertas->fetch_assoc()) {
        $alertas_estoque[] = $row;
    }
}

// 6. Resumo geral
$sql_resumo = "
    SELECT 
        (SELECT COUNT(*) FROM produtos) as total_produtos,
        (SELECT COUNT(*) FROM clientes) as total_clientes,
        (SELECT COUNT(*) FROM vendedores) as total_vendedores,
        (SELECT COUNT(*) FROM alertas_estoque WHERE resolvido = FALSE) as alertas_pendentes,
        (SELECT SUM(valor_total) FROM vendas WHERE MONTH(data_venda) = MONTH(CURRENT_DATE()) AND YEAR(data_venda) = YEAR(CURRENT_DATE())) as vendas_mes_atual
";
$result_resumo = $conn->query($sql_resumo);
$resumo = $result_resumo ? $result_resumo->fetch_assoc() : null;

// Fechar conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analítico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; min-height: 100vh; }
        .dashboard-header { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; padding: 20px 0; margin-bottom: 30px; }
        .card { border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px; border: none; }
        .card-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,0.05); font-weight: 600; }
        .card-header i { margin-right: 5px; }
        .stat-card { transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-value { font-size: 2rem; font-weight: 700; }
        .stat-label { color: #6c757d; font-size: 0.9rem; }
        .stat-icon { font-size: 3rem; opacity: 0.2; position: absolute; right: 20px; top: 10px; }
        .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
        .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
        .bg-gradient-danger { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%); }
        .bg-gradient-secondary { background: linear-gradient(135deg, #858796 0%, #60616f 100%); }
        .text-white-50 { color: rgba(255,255,255,0.5); }
        .alert-badge { position: absolute; top: -5px; right: -5px; }
    </style>
</head>
<body>
    <header class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="m-0"><i class="fas fa-chart-line me-3"></i> Dashboard Analítico</h3>
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
        <!-- Resumo em cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="stat-label">Total de Produtos</div>
                        <div class="stat-value"><?php echo number_format($resumo['total_produtos'] ?? 0); ?></div>
                        <i class="fas fa-boxes stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-gradient-success text-white">
                    <div class="card-body">
                        <div class="stat-label">Vendas do Mês</div>
                        <div class="stat-value">R$ <?php echo number_format($resumo['vendas_mes_atual'] ?? 0, 2, ',', '.'); ?></div>
                        <i class="fas fa-dollar-sign stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-gradient-info text-white">
                    <div class="card-body">
                        <div class="stat-label">Clientes Cadastrados</div>
                        <div class="stat-value"><?php echo number_format($resumo['total_clientes'] ?? 0); ?></div>
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card bg-gradient-danger text-white">
                    <div class="card-body">
                        <div class="stat-label">Alertas Pendentes</div>
                        <div class="stat-value"><?php echo number_format($resumo['alertas_pendentes'] ?? 0); ?></div>
                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Vendas -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-area"></i> Vendas dos Últimos 15 Dias
                    </div>
                    <div class="card-body">
                        <canvas id="vendasChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Alertas de Estoque -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-exclamation-triangle"></i> Alertas de Estoque</span>
                        <span class="badge bg-danger"><?php echo count($alertas_estoque); ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (count($alertas_estoque) > 0): ?>
                                <?php foreach ($alertas_estoque as $alerta): ?>
                                    <div class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($alerta['codigo'] . ' - ' . $alerta['descricao']); ?></h6>
                                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($alerta['data_alerta'])); ?></small>
                                        </div>
                                        <p class="mb-1">
                                            <?php 
                                                $badge_class = '';
                                                switch ($alerta['tipo']) {
                                                    case 'baixo':
                                                        $badge_class = 'bg-warning';
                                                        break;
                                                    case 'critico':
                                                        $badge_class = 'bg-danger';
                                                        break;
                                                    case 'vencimento':
                                                        $badge_class = 'bg-info';
                                                        break;
                                                }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($alerta['tipo']); ?></span>
                                            <?php echo htmlspecialchars($alerta['mensagem']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item">
                                    <p class="mb-0 text-center text-success">
                                        <i class="fas fa-check-circle me-2"></i> Não há alertas pendentes
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="alertas_estoque.php" class="btn btn-sm btn-primary">Ver Todos os Alertas</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Produtos com Estoque Baixo -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-exclamation-circle"></i> Produtos com Estoque Baixo
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Produto</th>
                                        <th>Estoque</th>
                                        <th>Mínimo</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($produtos_estoque_baixo) > 0): ?>
                                        <?php foreach ($produtos_estoque_baixo as $produto): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($produto['codigo']); ?></td>
                                                <td><?php echo htmlspecialchars($produto['descricao']); ?></td>
                                                <td>
                                                    <span class="badge bg-danger"><?php echo $produto['quantidade_estoque']; ?></span>
                                                </td>
                                                <td><?php echo $produto['estoque_minimo']; ?></td>
                                                <td>
                                                    <a href="movimentacoes_estoque.php?produto=<?php echo $produto['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-plus-circle"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhum produto com estoque baixo</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produtos Mais Vendidos -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-trophy"></i> Produtos Mais Vendidos (30 dias)
                    </div>
                    <div class="card-body">
                        <canvas id="produtosChart" height="260"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Links Rápidos -->
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-link"></i> Ações Rápidas
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="form_cadastrar_produto.php" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-plus-circle me-2"></i> Novo Produto
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="form_cadastrar_venda.php" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-shopping-cart me-2"></i> Nova Venda
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="cadastrar_fornecedor.php" class="btn btn-info text-white w-100 mb-2">
                                    <i class="fas fa-truck me-2"></i> Novo Fornecedor
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="relatorios.php" class="btn btn-secondary w-100 mb-2">
                                    <i class="fas fa-chart-bar me-2"></i> Relatórios
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="movimentacoes_estoque.php" class="btn btn-dark w-100 mb-2">
                                    <i class="fas fa-boxes me-2"></i> Movimentações
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="form_cadastrar_cliente.php" class="btn btn-warning text-white w-100 mb-2">
                                    <i class="fas fa-user-plus me-2"></i> Novo Cliente
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gráfico de Vendas
        const vendasCtx = document.getElementById('vendasChart').getContext('2d');
        const vendasChart = new Chart(vendasCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php
                        foreach ($vendas_por_dia as $venda) {
                            echo "'" . date('d/m', strtotime($venda['dia'])) . "',";
                        }
                    ?>
                ],
                datasets: [{
                    label: 'Valor Total (R$)',
                    data: [
                        <?php
                            foreach ($vendas_por_dia as $venda) {
                                echo $venda['valor_total'] . ",";
                            }
                        ?>
                    ],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.3
                }, {
                    label: 'Quantidade de Vendas',
                    data: [
                        <?php
                            foreach ($vendas_por_dia as $venda) {
                                echo $venda['total_vendas'] . ",";
                            }
                        ?>
                    ],
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#1cc88a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Valor (R$)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toFixed(2).replace('.', ',');
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Quantidade'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Gráfico de Produtos Mais Vendidos
        const produtosCtx = document.getElementById('produtosChart').getContext('2d');
        const produtosChart = new Chart(produtosCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php
                        foreach ($produtos_mais_vendidos as $produto) {
                            echo "'" . substr($produto['descricao'], 0, 20) . "',";
                        }
                    ?>
                ],
                datasets: [{
                    label: 'Quantidade Vendida',
                    data: [
                        <?php
                            foreach ($produtos_mais_vendidos as $produto) {
                                echo $produto['total_vendido'] . ",";
                            }
                        ?>
                    ],
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(133, 135, 150, 0.8)',
                        'rgba(105, 0, 132, 0.8)',
                        'rgba(0, 63, 92, 0.8)',
                        'rgba(88, 80, 141, 0.8)',
                        'rgba(188, 80, 144, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
