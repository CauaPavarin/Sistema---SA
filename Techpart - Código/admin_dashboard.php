<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador</title>
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
        }
        
        .admin-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
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
            z-index: -1;
        }
        
        .card-dashboard {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            margin-bottom: 20px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.07);
            position: relative;
            height: 100%;
        }
        
        .card-dashboard:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card-header-dashboard {
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header-dashboard::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            top: -60%;
            left: -60%;
            transition: all 0.4s ease;
        }
        
        .card-dashboard:hover .card-header-dashboard::before {
            top: 110%;
            left: -10%;
        }
        
        .card-body-dashboard {
            padding: 30px;
            text-align: center;
            background: white;
            transition: all 0.3s;
        }
        
        .icon-dashboard {
            font-size: 4rem;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .card-dashboard:hover .icon-dashboard {
            transform: scale(1.1);
        }
        
        .vendedores-card .card-header-dashboard {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }
        
        .vendedores-card .icon-dashboard {
            color: #1cc88a;
        }
        
        .produtos-card .card-header-dashboard {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }
        
        .produtos-card .icon-dashboard {
            color: #f6c23e;
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
        }
        
        .dashboard-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
            color: #333;
        }
        
        .dashboard-title:after {
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
        
        .card-title {
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .card-text {
            color: #6c757d;
            margin-bottom: 25px;
        }
        
        .btn-card {
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #1cc88a, #13855c);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f6c23e, #dda20a);
            border: none;
            color: white;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            padding: 30px;
            border-radius: 15px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.8;
            z-index: 0;
        }
        
        .welcome-text {
            position: relative;
            z-index: 1;
        }
        
        .welcome-subtitle {
            opacity: 0.8;
            max-width: 600px;
        }
        
        .user-name {
            font-weight: 600;
            position: relative;
            display: inline-block;
        }
        
        .user-name::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 2px;
            left: 0;
            background-color: rgba(255, 255, 255, 0.4);
        }
        
        .animated-card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .delayed-1 {
            animation-delay: 0.1s;
        }
        
        .delayed-2 {
            animation-delay: 0.3s;
        }
    </style>
</head>
<body>
    <header class="admin-header mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="m-0 d-flex align-items-center">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        Painel do Administrador
                    </h3>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Bem-vindo, <span class="user-name"><?php echo htmlspecialchars($usuario); ?></span></span>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt me-2"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-4">
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2 class="mb-3"><i class="fas fa-hand-sparkles me-2"></i> Olá, <?php echo htmlspecialchars($usuario); ?>!</h2>
                <p class="welcome-subtitle mb-0">
                    Bem-vindo ao painel administrativo do Sistema de Cadastro. Aqui você pode gerenciar vendedores e produtos para manter seu sistema sempre organizado.
                </p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="dashboard-title">Área Administrativa</h2>
                <p class="lead">Escolha uma opção abaixo para gerenciar o sistema</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4 animated-card delayed-1">
                <div class="card card-dashboard vendedores-card h-100">
                    <div class="card-header-dashboard">
                        <h4 class="m-0">Vendedores</h4>
                    </div>
                    <div class="card-body-dashboard">
                        <i class="fas fa-users icon-dashboard"></i>
                        <h5 class="card-title">Cadastro de Vendedores</h5>
                        <p class="card-text">Gerencie os vendedores do sistema, adicionando, editando ou removendo registros conforme necessário.</p>
                        <a href="vendedores.php" class="btn btn-success btn-card">
                            <i class="fas fa-arrow-right me-2"></i> Acessar
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4 animated-card delayed-2">
                <div class="card card-dashboard produtos-card h-100">
                    <div class="card-header-dashboard">
                        <h4 class="m-0">Produtos</h4>
                    </div>
                    <div class="card-body-dashboard">
                        <i class="fas fa-box-open icon-dashboard"></i>
                        <h5 class="card-title">Cadastro de Produtos</h5>
                        <p class="card-text">Gerencie os produtos do sistema, adicionando, editando ou removendo itens do catálogo de produtos.</p>
                        <a href="index.php" class="btn btn-warning btn-card">
                            <i class="fas fa-arrow-right me-2"></i> Acessar
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4 animated-card delayed-3">
                <div class="card card-dashboard clientes-card h-100">
                    <div class="card-header-dashboard" style="background: linear-gradient(135deg, #36b9cc 0%, #258fa3 100%);">
                        <h4 class="m-0">Clientes</h4>
                    </div>
                    <div class="card-body-dashboard">
                        <i class="fas fa-user-friends icon-dashboard" style="color: #36b9cc;"></i>
                        <h5 class="card-title">Cadastro de Clientes</h5>
                        <p class="card-text">Gerencie os clientes do sistema, adicionando, editando ou removendo registros conforme necessário.</p>
                        <a href="clientes.php" class="btn btn-info text-white btn-card">
                            <i class="fas fa-arrow-right me-2"></i> Acessar
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4 animated-card delayed-4">
                <div class="card card-dashboard estoque-card h-100">
                    <div class="card-header-dashboard" style="background: linear-gradient(135deg, #20c997 0%, #17a67a 100%);">
                        <h4 class="m-0">Estoque</h4>
                    </div>
                    <div class="card-body-dashboard">
                        <i class="fas fa-boxes icon-dashboard" style="color: #20c997;"></i>
                        <h5 class="card-title">Movimentações de Estoque</h5>
                        <p class="card-text">Registre entradas e saídas de produtos, ajuste quantidades e acompanhe o histórico de movimentações.</p>
                        <a href="movimentacoes_estoque.php" class="btn btn-success btn-card">
                            <i class="fas fa-arrow-right me-2"></i> Acessar
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4 animated-card delayed-5">
                <div class="card card-dashboard historico-card h-100">
                    <div class="card-header-dashboard" style="background: linear-gradient(135deg, #6f42c1 0%, #4b2e83 100%);">
                        <h4 class="m-0">Histórico de Vendas</h4>
                    </div>
                    <div class="card-body-dashboard">
                        <i class="fas fa-history icon-dashboard" style="color: #6f42c1;"></i>
                        <h5 class="card-title">Histórico de Vendas</h5>
                        <p class="card-text">Visualize o histórico completo de vendas realizadas, com detalhes de cada transação.</p>
                        <a href="historico.php" class="btn btn-secondary btn-card">
                            <i class="fas fa-arrow-right me-2"></i> Acessar
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Novas funcionalidades -->
            <div class="col-md-4 mb-4 animated-card delayed-6">
                <div class="card card-dashboard dashboard-card h-100">
                    <div class="card-header-dashboard" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                        <h4 class="m-0">Dashboard Analítico</h4>
                    </div>
                    <div class="card-body-dashboard">
                        <i class="fas fa-chart-line icon-dashboard" style="color: #4e73df;"></i>
                        <h5 class="card-title">Dashboard Analítico</h5>
                        <p class="card-text">Visualize gráficos e estatísticas detalhadas sobre vendas, estoque e desempenho do negócio.</p>
                        <a href="dashboard_analitico.php" class="btn btn-primary btn-card">
                            <i class="fas fa-arrow-right me-2"></i> Acessar
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 animated-card delayed-6">
                <div class="card card-dashboard alertas-card h-100">
                    <div class="card-header-dashboard" style="background: linear-gradient(135deg, #e74a3b 0%, #b02a1a 100%);">
                        <h4 class="m-0">Alertas de Estoque</h4>
                    </div>
                    <div class="card-body-dashboard">
                        <i class="fas fa-exclamation-triangle icon-dashboard" style="color: #e74a3b;"></i>
                        <h5 class="card-title">Sistema de Alertas</h5>
                        <p class="card-text">Monitore produtos com estoque baixo e configure alertas para evitar falta de produtos.</p>
                        <a href="alertas_estoque.php" class="btn btn-danger btn-card">
                            <i class="fas fa-arrow-right me-2"></i> Acessar
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 animated-card delayed-7">
                <div class="card card-dashboard fornecedores-card h-100">
                    <div class="card-header-dashboard" style="background: linear-gradient(135deg, #5a5c69 0%, #373840 100%);">
                        <h4 class="m-0">Fornecedores</h4>
                    </div>
                    <div class="card-body-dashboard">
                        <i class="fas fa-truck icon-dashboard" style="color: #5a5c69;"></i>
                        <h5 class="card-title">Gestão de Fornecedores</h5>
                        <p class="card-text">Cadastre e gerencie fornecedores, registre compras e avalie o desempenho dos fornecedores.</p>
                        <a href="listar_fornecedores.php" class="btn btn-dark btn-card">
                            <i class="fas fa-arrow-right me-2"></i> Acessar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animações para ícones ao passar o mouse
        document.querySelectorAll('.icon-dashboard').forEach(icon => {
            icon.addEventListener('mouseover', function() {
                this.classList.add('fa-beat');
            });
            
            icon.addEventListener('mouseout', function() {
                this.classList.remove('fa-beat');
            });
        });
    </script>
</body>
</html> 