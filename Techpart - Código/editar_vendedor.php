<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Verificar se o ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: vendedores.php");
    exit;
}

$id = intval($_GET['id']);
$usuario = $_SESSION['usuario'];

// Processar formulário de edição
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpar e validar dados
    $codigo = trim(mysqli_real_escape_string($conn, $_POST['codigo']));
    $nome = trim(mysqli_real_escape_string($conn, $_POST['nome']));
    $celular = trim(mysqli_real_escape_string($conn, $_POST['celular']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    
    // Verificar se campos obrigatórios foram preenchidos
    if (empty($codigo) || empty($nome) || empty($celular) || empty($email)) {
        $error = "Todos os campos são obrigatórios";
    } else {
        // Atualizar dados no banco
        $sql = "UPDATE vendedores SET codigo = ?, nome = ?, celular = ?, email = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $codigo, $nome, $celular, $email, $id);
        
        if ($stmt->execute()) {
            header("Location: vendedores.php?status=success");
            exit;
        } else {
            $error = "Erro ao atualizar vendedor: " . $conn->error;
        }
        
        $stmt->close();
    }
}

// Buscar dados do vendedor
$sql = "SELECT * FROM vendedores WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: vendedores.php");
    exit;
}

$vendedor = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vendedor</title>
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
        
        .btn-action {
            background: linear-gradient(135deg, #1cc88a, #13855c);
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
            color: white;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(28, 200, 138, 0.3);
            color: white;
        }
        
        .btn-action:before {
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
        
        .btn-action:hover:before {
            width: 100%;
        }
        
        .btn-cancel {
            background: #6c757d;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-cancel:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
            background: #5a6268;
            color: white;
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
    </style>
</head>
<body>
    <div class="floating-elements" id="floating-elements"></div>
    
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
        <h1 class="page-title">Editar Vendedor</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="m-0"><i class="fas fa-user-edit me-2"></i>Editar Vendedor #<?php echo $id; ?></h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label">Código do Vendedor</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" value="<?php echo htmlspecialchars($vendedor['codigo']); ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($vendedor['nome']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="text" class="form-control" id="celular" name="celular" value="<?php echo htmlspecialchars($vendedor['celular']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($vendedor['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <button type="submit" class="btn btn-action me-2">
                            <i class="fas fa-save me-2"></i>Salvar Alterações
                        </button>
                        <a href="vendedores.php" class="btn btn-cancel">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
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
            
            // Foco no primeiro campo do formulário
            document.querySelector('form input:first-child').focus();
        });
    </script>
</body>
</html>
<?php $conn->close(); ?> 