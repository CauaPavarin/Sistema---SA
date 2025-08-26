<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Cadastro</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            margin: 0;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Partículas de fundo animadas */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            animation: float 15s infinite linear;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
            }
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            z-index: 2;
            position: relative;
            transition: all 0.3s ease;
            transform: translateY(0);
            overflow: hidden;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
        }
        
        .system-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }
        
        .system-title:after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: translateX(-50%);
            border-radius: 3px;
            transition: width 0.3s;
        }
        
        .login-container:hover .system-title:after {
            width: 100px;
        }
        
        .btn-login {
            background: linear-gradient(90deg, #4e73df, #224abe);
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 500;
            margin-top: 15px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-login:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #224abe, #4e73df);
            transition: all 0.4s;
            z-index: -1;
        }
        
        .btn-login:hover:before {
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(78, 115, 223, 0.3);
        }
        
        .btn-register {
            background: linear-gradient(90deg, #36b9cc, #1a8997);
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-register:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #1a8997, #36b9cc);
            transition: all 0.4s;
            z-index: -1;
        }
        
        .btn-register:hover:before {
            width: 100%;
        }
        
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(54, 185, 204, 0.3);
        }
        
        .radio-group {
            margin: 20px 0;
            padding: 10px;
            border-radius: 10px;
            background-color: #f8f9fa;
            transition: all 0.3s;
        }
        
        .radio-group:hover {
            background-color: #e9ecef;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
        }
        
        .form-check {
            padding: 8px 10px;
            transition: all 0.2s;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .form-check:hover {
            background-color: rgba(255, 255, 255, 0.5);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
            border-color: #667eea;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            transition: all 0.3s;
        }
        
        .form-control:focus + .form-label,
        .form-control:not(:placeholder-shown) + .form-label {
            color: #667eea;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #f8f9fa;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 10px 15px;
            transition: all 0.3s;
            position: relative;
        }
        
        .nav-tabs .nav-link:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s;
        }
        
        .nav-tabs .nav-link:hover:after {
            width: 100%;
        }
        
        .nav-tabs .nav-link.active {
            color: #495057;
            font-weight: 600;
            background-color: transparent;
        }
        
        .nav-tabs .nav-link.active:after {
            width: 100%;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .floating-label {
            position: absolute;
            pointer-events: none;
            left: 15px;
            top: 12px;
            transition: 0.2s ease all;
            color: #6c757d;
        }
        
        .form-control:focus ~ .floating-label,
        .form-control:not(:placeholder-shown) ~ .floating-label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #667eea;
            background: white;
            padding: 0 5px;
        }
    </style>
</head>
<body>
    <!-- Partículas animadas no fundo -->
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <i class="fas fa-user-shield logo-icon"></i>
                <h2 class="system-title">Sistema de Cadastro</h2>
            </div>
            
            <?php
            // Exibir mensagens de erro ou sucesso
            if (isset($_GET['status'])) {
                if ($_GET['status'] == 'error') {
                    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Usuário ou senha inválidos.</div>';
                } else if ($_GET['status'] == 'registered') {
                    echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Usuário cadastrado com sucesso! Faça login para continuar.</div>';
                }
            }
            ?>
            
            <div class="card border-0 bg-transparent">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" id="loginTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-tab-pane" type="button" role="tab">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-tab-pane" type="button" role="tab">
                                <i class="fas fa-user-plus me-2"></i>Cadastrar
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="loginTabContent">
                        <!-- Tab Login -->
                        <div class="tab-pane fade show active" id="login-tab-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
                            <form action="verificar_login.php" method="post">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder=" " required>
                                    <span class="floating-label">Usuário</span>
                                </div>
                                
                                <div class="input-group">
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder=" " required>
                                    <span class="floating-label">Senha</span>
                                </div>
                                
                                <div class="radio-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo" id="tipo-admin" value="administrador" checked>
                                        <label class="form-check-label" for="tipo-admin">
                                            <i class="fas fa-user-cog me-2"></i>Administrador
                                        </label>
                                    </div>
                                    <input type="hidden" name="tipo" value="administrador">
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tab Cadastro -->
                        <div class="tab-pane fade" id="register-tab-pane" role="tabpanel" aria-labelledby="register-tab" tabindex="0">
                            <form action="cadastrar_usuario.php" method="post">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="novo-usuario" name="usuario" placeholder=" " required>
                                    <span class="floating-label">Nome de Usuário</span>
                                </div>
                                
                                <div class="input-group">
                                    <input type="password" class="form-control" id="nova-senha" name="senha" placeholder=" " required>
                                    <span class="floating-label">Senha</span>
                                </div>
                                
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmar-senha" name="confirmar_senha" placeholder=" " required>
                                    <span class="floating-label">Confirmar Senha</span>
                                </div>
                                
                                <div class="radio-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo" id="novo-tipo-admin" value="administrador" checked>
                                        <label class="form-check-label" for="novo-tipo-admin">
                                            <i class="fas fa-user-cog me-2"></i>Administrador
                                        </label>
                                    </div>
                                    <input type="hidden" name="tipo" value="administrador">
                                </div>
                                
                                <button type="submit" class="btn btn-info text-white btn-register">
                                    <i class="fas fa-user-plus me-2"></i>Cadastrar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Criar as partículas de fundo
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Propriedades randômicas
                const size = Math.random() * 30 + 10;
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                const delay = Math.random() * 10;
                const duration = Math.random() * 20 + 10;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                particle.style.opacity = Math.random() * 0.5 + 0.1;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;
                
                particlesContainer.appendChild(particle);
            }
        });
        
        // Efeito de foco nos campos
        const formControls = document.querySelectorAll('.form-control');
        
        formControls.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html> 