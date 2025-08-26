<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecionando - Sistema de Cadastro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }
        
        .loader-container {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1;
        }
        
        .loader {
            position: relative;
            width: 120px;
            height: 120px;
        }
        
        .loader span {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform: rotate(calc(18deg * var(--i)));
        }
        
        .loader span::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 0 10px white,
                        0 0 20px white,
                        0 0 40px white,
                        0 0 60px white,
                        0 0 80px white,
                        0 0 100px white;
            animation: animate 2s linear infinite;
            animation-delay: calc(0.1s * var(--i));
        }
        
        @keyframes animate {
            0% {
                transform: scale(1);
            }
            80%, 100% {
                transform: scale(0);
            }
        }
        
        .loader-text {
            margin-top: 40px;
            font-size: 1.5rem;
            font-weight: 500;
            color: white;
            letter-spacing: 1px;
            text-align: center;
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards 0.5s;
        }
        
        .loader-subtext {
            margin-top: 10px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards 0.8s;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            animation: particleFloat 15s infinite linear;
        }
        
        @keyframes particleFloat {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
            }
        }
        
        .logo-icon {
            font-size: 3rem;
            color: white;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .progress-bar {
            width: 250px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 20px;
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards 1s;
        }
        
        .progress {
            width: 0%;
            height: 100%;
            background: white;
            animation: progress 3s ease-out forwards;
        }
        
        @keyframes progress {
            0% {
                width: 0%;
            }
            100% {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="loader-container">
        <i class="fas fa-database logo-icon"></i>
        
        <div class="loader">
            <?php for($i = 0; $i < 20; $i++): ?>
                <span style="--i:<?php echo $i; ?>"></span>
            <?php endfor; ?>
        </div>
        
        <div class="loader-text">Iniciando Sistema de Cadastro</div>
        <div class="loader-subtext">Por favor, aguarde um momento...</div>
        
        <div class="progress-bar">
            <div class="progress"></div>
        </div>
    </div>

    <script>
        // Criar partículas
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Propriedades randômicas
                const size = Math.random() * 20 + 10;
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = Math.random() * 10 + 10;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                particle.style.opacity = Math.random() * 0.3 + 0.1;
                particle.style.animationDuration = `${duration}s`;
                particle.style.animationDelay = `${delay}s`;
                
                particlesContainer.appendChild(particle);
            }
            
            // Redirecionar após 3 segundos
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000);
        });
    </script>
</body>
</html>

<?php
// Esta parte só será executada após o redirecionamento do JavaScript
// Redirecionar para a página de login (caso o JavaScript esteja desativado)
header("Refresh: 3; URL=login.php");
exit;
?> 