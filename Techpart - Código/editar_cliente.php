<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['tipo'] != 'administrador') {
    header("Location: login.php");
    exit;
}
require_once 'config.php';
if (!isset($_GET['id'])) {
    header("Location: clientes.php");
    exit;
}
$id = intval($_GET['id']);
$usuario = $_SESSION['usuario'];
$cliente = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim(mysqli_real_escape_string($conn, $_POST['nome']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $telefone = trim(mysqli_real_escape_string($conn, $_POST['telefone']));
    $endereco = trim(mysqli_real_escape_string($conn, $_POST['endereco']));
    if (empty($nome) || empty($email)) {
        $error = "Preencha todos os campos obrigatórios.";
    } else {
        $sql = "UPDATE clientes SET nome=?, email=?, telefone=?, endereco=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $email, $telefone, $endereco, $id);
        if ($stmt->execute()) {
            header("Location: clientes.php?status=success&msg=cliente_atualizado");
            exit;
        } else {
            $error = "Erro ao atualizar cliente.";
        }
        $stmt->close();
    }
}
$sql = "SELECT * FROM clientes WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    header("Location: clientes.php");
    exit;
}
$cliente = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; min-height: 100vh; }
        .admin-header { background: linear-gradient(135deg, #36b9cc 0%, #258fa3 100%); color: white; padding: 20px 0; margin-bottom: 30px; }
    </style>
</head>
<body>
<header class="admin-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 d-flex align-items-center">
                    <i class="fas fa-user-friends me-3"></i> Editar Cliente
                </h3>
            </div>
            <div class="col-md-6 text-end">
                <span class="me-3">Bem-vindo, <span class="user-name"><?php echo htmlspecialchars($usuario); ?></span></span>
                <a href="clientes.php" class="logout-btn me-3"><i class="fas fa-users me-1"></i> Clientes</a>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
            </div>
        </div>
    </div>
</header>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-user-edit me-2"></i> Editar Cliente</div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome*</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email*</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo htmlspecialchars($cliente['endereco']); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Salvar Alterações</button>
                        <a href="clientes.php" class="btn btn-secondary ms-2">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 