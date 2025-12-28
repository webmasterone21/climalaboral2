<?php
/**
 * Vista de error 403 - Acceso Denegado
 * views/errors/403.php
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado</title>
    
    <!-- CSS Framework -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
        
        .error-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
            color: rgba(255,255,255,0.9);
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
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
            color: #dc3545;
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
            color: #dc3545;
            transform: translateY(-2px);
        }
        
        .access-info {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
        }
        
        .access-info h5 {
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .access-info ul {
            text-align: left;
            margin-bottom: 0;
        }
        
        .access-info li {
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 4rem;
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

    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <div class="error-code">403</div>
        <h1 class="error-title">Acceso Denegado</h1>
        <p class="error-message">
            No tienes permisos para acceder a este recurso. 
            Tu cuenta no tiene los privilegios necesarios para realizar esta acción.
        </p>
        
        <div class="access-info">
            <h5><i class="fas fa-info-circle me-2"></i>¿Por qué veo este mensaje?</h5>
            <ul>
                <li>No has iniciado sesión en el sistema</li>
                <li>Tu cuenta no tiene los permisos necesarios</li>
                <li>La página requiere privilegios de administrador</li>
                <li>Tu sesión puede haber expirado</li>
            </ul>
        </div>
        
        <div class="error-actions">
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>login" class="btn-custom btn-light-custom">
                <i class="fas fa-sign-in-alt me-2"></i>
                Iniciar Sesión
            </a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn-custom btn-light-custom">
                <i class="fas fa-tachometer-alt me-2"></i>
                Ir al Dashboard
            </a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>" class="btn-custom btn-outline-custom">
                <i class="fas fa-home me-2"></i>
                Página Principal
            </a>
        </div>
        
        <div class="mt-4">
            <small class="opacity-75">
                Si crees que deberías tener acceso a esta página, 
                <a href="mailto:admin@sistema.com" class="text-white text-decoration-underline">
                    contacta al administrador
                </a>
            </small>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Log del error para debugging
            console.log('403 Error - Access denied to:', window.location.href);
            
            // Verificar si hay información de usuario en sesión
            const userLoggedIn = <?= json_encode(isset($_SESSION['user_id'])) ?>;
            const userRole = <?= json_encode($_SESSION['user_role'] ?? null) ?>;
            
            if (userLoggedIn) {
                console.log('User is logged in with role:', userRole);
            } else {
                console.log('User is not logged in');
            }
            
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