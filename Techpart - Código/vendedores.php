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
    <title>Cadastro de Vendedores</title>
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
        
        .admin-header {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
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
            background: linear-gradient(90deg, #1cc88a, #13855c);
            border-radius: 3px;
            transition: width 0.3s;
        }
        
        .page-title:hover:after {
            width: 100px;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
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
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
            color: white !important;
            font-weight: 600;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
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
        
        .card:hover .card-header::before {
            top: 110%;
            left: -10%;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #1cc88a;
            box-shadow: 0 0 0 0.25rem rgba(28, 200, 138, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #1cc88a, #13855c);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(28, 200, 138, 0.3);
        }
        
        .btn-success:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #13855c, #1cc88a);
            transition: all 0.4s;
            z-index: -1;
        }
        
        .btn-success:hover:before {
            width: 100%;
        }
        
        .table-responsive {
            margin-top: 40px;
            animation: fadeInUp 0.8s;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #f8f9fa;
            padding: 15px;
            font-weight: 600;
            color: #495057;
            border-top: none;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(28, 200, 138, 0.05);
        }
        
        .table-section-title {
            font-weight: 600;
            margin: 40px 0 20px;
            color: #333;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
            animation: fadeInUp 0.6s;
        }
        
        .table-section-title::after {
            content: '';
            position: absolute;
            width: 50%;
            height: 3px;
            background: linear-gradient(90deg, #1cc88a, transparent);
            left: 0;
            bottom: 0;
            transition: width 0.3s;
        }
        
        .table-section-title:hover::after {
            width: 100%;
        }
        
        .btn-action {
            border-radius: 50px;
            padding: 8px 15px;
            font-weight: 500;
            margin: 0 2px;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .btn-action.btn-warning {
            padding: 8px 15px;
            background: linear-gradient(135deg, #f6c23e, #dda20a);
            border: none;
        }
        
        .btn-action.btn-danger {
            background: linear-gradient(135deg, #e74a3b, #bd2130);
            border: none;
        }
        
        .btn-action.btn-danger:hover {
            box-shadow: 0 5px 15px rgba(231, 74, 59, 0.3);
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
        
        .floating-elements {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }
        
        .floating-element {
            position: absolute;
            background: linear-gradient(135deg, rgba(28, 200, 138, 0.1) 0%, rgba(19, 133, 92, 0.1) 100%);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
            }
        }
        
        /* Estilo para o modal de confirmação personalizado */
        .confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s;
        }
        
        .confirm-modal-content {
            background: white;
            border-radius: 15px;
            max-width: 400px;
            width: 100%;
            padding: 0;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: transform 0.3s;
            overflow: hidden;
        }
        
        .confirm-modal-header {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .confirm-modal-header i {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        
        .confirm-modal-body {
            padding: 20px;
            text-align: center;
            color: #495057;
        }
        
        .confirm-modal-footer {
            display: flex;
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        
        .confirm-modal-footer .btn {
            flex: 1;
            border-radius: 50px;
            padding: 10px;
            margin: 0 5px;
            font-weight: 500;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #e74a3b, #bd2130);
            color: white;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 74, 59, 0.3);
            color: white;
        }
        
        .btn-cancel {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .fadeIn {
            animation: fadeIn 0.3s;
        }
        
        .bounceIn {
            animation: bounceIn 0.5s;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(50px);
            }
            70% {
                opacity: 1;
                transform: scale(1.05) translateY(-10px);
            }
            100% {
                transform: scale(1) translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="floating-elements" id="floating-elements"></div>
    
    <!-- Modal de confirmação personalizado -->
    <div class="confirm-modal" id="confirmDeleteModal">
        <div class="confirm-modal-content bounceIn">
            <div class="confirm-modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Confirmação</span>
            </div>
            <div class="confirm-modal-body">
                <p>Tem certeza que deseja excluir este vendedor?</p>
            </div>
            <div class="confirm-modal-footer">
                <button class="btn btn-cancel" id="cancelDelete">Cancelar</button>
                <a href="#" class="btn btn-confirm" id="confirmDelete">Excluir</a>
            </div>
        </div>
    </div>
    
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="m-0 d-flex align-items-center">
                        <i class="fas fa-users me-3"></i>
                        Sistema de Cadastro
                    </h3>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Bem-vindo, <span class="user-name"><?php echo htmlspecialchars($usuario); ?></span></span>
                    <a href="admin_dashboard.php" class="logout-btn me-3">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt me-1"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Cadastro de Vendedores</h1>
        
        <?php
        // Exibir mensagens de sucesso ou erro
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Vendedor cadastrado com sucesso!</div>';
            } else if ($_GET['status'] == 'error') {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Erro ao cadastrar vendedor.</div>';
            }
        }
        ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="m-0"><i class="fas fa-user-plus me-2"></i>Novo Vendedor</h5>
            </div>
            <div class="card-body">
                <form action="cadastrar_vendedor.php" method="post">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label">Código do Vendedor</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required>
                        </div>
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="text" class="form-control" id="celular" name="celular" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus-circle me-2"></i>Cadastrar Vendedor
                    </button>
                </form>
            </div>
        </div>
        
        <h2 class="table-section-title">Vendedores Cadastrados</h2>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Celular</th>
                        <th>Email</th>
                        <th>Data de Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once 'config.php';
                    
                    $sql = "SELECT * FROM vendedores ORDER BY id DESC";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>".$row["id"]."</td>";
                            echo "<td>".$row["codigo"]."</td>";
                            echo "<td>".$row["nome"]."</td>";
                            echo "<td>".$row["celular"]."</td>";
                            echo "<td>".$row["email"]."</td>";
                            echo "<td>".date('d/m/Y H:i', strtotime($row["data_cadastro"]))."</td>";
                            echo "<td>
                                <a href='editar_vendedor.php?id=".$row["id"]."' class='btn btn-sm btn-warning btn-action'>
                                    <i class='fas fa-edit me-1'></i>Editar
                                </a>
                                <a href='excluir_vendedor.php?id=".$row["id"]."' class='btn btn-sm btn-danger btn-action delete-btn'>
                                    <i class='fas fa-trash-alt me-1'></i>Excluir
                                </a>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>Nenhum vendedor cadastrado.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Criar elementos flutuantes
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('floating-elements');
            const elementCount = 10;
            
            for (let i = 0; i < elementCount; i++) {
                const element = document.createElement('div');
                element.classList.add('floating-element');
                
                // Propriedades randômicas
                const size = Math.random() * 100 + 50;
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                const delay = Math.random() * 10;
                const duration = Math.random() * 10 + 15;
                
                element.style.width = `${size}px`;
                element.style.height = `${size}px`;
                element.style.left = `${posX}%`;
                element.style.top = `${posY}%`;
                element.style.opacity = Math.random() * 0.3 + 0.1;
                element.style.animationDuration = `${duration}s`;
                element.style.animationDelay = `${delay}s`;
                
                container.appendChild(element);
            }
            
            // Animações para os botões
            const actionButtons = document.querySelectorAll('.btn-action');
            
            actionButtons.forEach(button => {
                button.addEventListener('mouseover', function() {
                    this.querySelector('i').classList.add('fa-beat');
                });
                
                button.addEventListener('mouseout', function() {
                    this.querySelector('i').classList.remove('fa-beat');
                });
            });
        });
        
        // Lógica para o modal de confirmação personalizado
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('confirmDeleteModal');
            const confirmBtn = document.getElementById('confirmDelete');
            const cancelBtn = document.getElementById('cancelDelete');
            let deleteUrl = '';
            
            // Abrir o modal quando clicar em Excluir
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    deleteUrl = this.getAttribute('href');
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden'; // Impedir rolagem
                });
            });
            
            // Confirmar exclusão
            confirmBtn.addEventListener('click', function() {
                if (deleteUrl) {
                    window.location.href = deleteUrl;
                }
            });
            
            // Fechar o modal
            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto'; // Restaurar rolagem
            });
            
            // Clicar fora do modal para fechar
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto'; // Restaurar rolagem
                }
            });
        });
    </script>
</body>
</html> 