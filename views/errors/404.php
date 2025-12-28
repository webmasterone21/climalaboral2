<?php
/**
 * Vista de error 404 - Página no encontrada
 * views/errors/404.php
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página No Encontrada</title>
    
    <!-- CSS Framework -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 2rem;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .error-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid;
        }
        
        .btn-light-custom {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .btn-light-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .btn-outline-custom {
            background: transparent;
            color: white;
            border-color: white;
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }
        
        .floating-icons {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .floating-icon {
            position: absolute;
            opacity: 0.1;
            font-size: 2rem;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-icon:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-icon:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .floating-icon:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            33% {
                transform: translateY(-20px) rotate(120deg);
            }
            66% {
                transform: translateY(-10px) rotate(240deg);
            }
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-message {
                font-size: 1rem;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-custom {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>

    <!-- Iconos flotantes decorativos -->
    <div class="floating-icons">
        <i class="fas fa-search floating-icon"></i>
        <i class="fas fa-exclamation-triangle floating-icon"></i>
        <i class="fas fa-question-circle floating-icon"></i>
    </div>

    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-title">¡Oops! Página no encontrada</h1>
        <p class="error-message">
            La página que estás buscando no existe o ha sido movida. 
            No te preocupes, puedes volver al inicio o explorar otras secciones.
        </p>
        
        <div class="error-actions">
            <a href="<?= BASE_URL ?>" class="btn-custom btn-light-custom">
                <i class="fas fa-home me-2"></i>
                Ir al Inicio
            </a>
            
            <a href="javascript:history.back()" class="btn-custom btn-outline-custom">
                <i class="fas fa-arrow-left me-2"></i>
                Volver Atrás
            </a>
        </div>
        
        <div class="mt-4">
            <small class="opacity-75">
                Si crees que esto es un error, por favor 
                <a href="mailto:soporte@sistema.com" class="text-white text-decoration-underline">
                    contacta al soporte técnico
                </a>
            </small>
        </div>
    </div>

    <script>
        // Agregar efecto de partículas opcional
        document.addEventListener('DOMContentLoaded', function() {
            // Log del error para debugging
            console.log('404 Error - Page not found:', window.location.href);
            
            // Efecto de hover en botones
            const buttons = document.querySelectorAll('.btn-custom');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.05)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>

</body>
</html>