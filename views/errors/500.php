<?php
/**
 * Vista de error 500 - Error Interno del Servidor
 * views/errors/500.php
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error del Servidor</title>
    
    <!-- CSS Framework -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            text-align: center;
            color: white;
            max-width: 700px;
            padding: 2rem;
        }
        
        .error-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: shake 1s ease-in-out infinite alternate;
            color: rgba(255,255,255,0.9);
        }
        
        @keyframes shake {
            0% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-5px) rotate(-5deg);
            }
            50% {
                transform: translateX(5px) rotate(5deg);
            }
            75% {
                transform: translateX(-5px) rotate(-5deg);
            }
            100% {
                transform: translateX(0);
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
            color: #6c757d;
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
            color: #6c757d;
            transform: translateY(-2px);
        }
        
        .technical-info {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
            text-align: left;
        }
        
        .technical-info h5 {
            margin-bottom: 1rem;
            font-weight: 600;
            text-align: center;
        }
        
        .technical-info ul {
            margin-bottom: 1rem;
        }
        
        .technical-info li {
            margin-bottom: 0.5rem;
        }
        
        .support-info {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .error-id {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            opacity: 0.7;
            margin-top: 1rem;
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
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <div class="error-code">500</div>
        <h1 class="error-title">Error del Servidor</h1>
        <p class="error-message">
            Oops! Algo salió mal en nuestros servidores. 
            Nuestro equipo técnico ha sido notificado y está trabajando para solucionarlo.
        </p>
        
        <div class="technical-info">
            <h5><i class="fas fa-cogs me-2"></i>¿Qué puedes hacer?</h5>
            <ul>
                <li><strong>Espera unos minutos</strong> y vuelve a intentarlo</li>
                <li><strong>Actualiza la página</strong> usando Ctrl+F5 (Windows) o Cmd+R (Mac)</li>
                <li><strong>Verifica tu conexión</strong> a internet</li>
                <li><strong>Intenta acceder</strong> desde otra página del sitio</li>
            </ul>
            
            <div class="support-info">
                <strong><i class="fas fa-headset me-2"></i>¿Necesitas ayuda inmediata?</strong><br>
                Contacta a nuestro equipo de soporte técnico:<br>
                📧 Email: <a href="mailto:soporte@sistema.com" class="text-white">soporte@sistema.com</a><br>
                📞 Teléfono: +504 2234-5678<br>
                💬 Chat en vivo: Disponible 24/7
            </div>
        </div>
        
        <div class="error-actions">
            <a href="javascript:location.reload()" class="btn-custom btn-light-custom">
                <i class="fas fa-redo me-2"></i>
                Reintentar
            </a>
            
            <a href="<?= BASE_URL ?>" class="btn-custom btn-outline-custom">
                <i class="fas fa-home me-2"></i>
                Ir al Inicio
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn-custom btn-outline-custom">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>
            <?php endif; ?>
        </div>
        
        <div class="error-id">
            <small>
                ID del Error: <?= uniqid('ERR-') ?><br>
                Timestamp: <?= date('Y-m-d H:i:s') ?><br>
                User Agent: <?= substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50) ?>...
            </small>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Log del error para debugging
            const errorId = 'ERR-<?= uniqid() ?>';
            console.error('500 Internal Server Error:', {
                errorId: errorId,
                url: window.location.href,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent
            });
            
            // Auto-retry después de 30 segundos
            let autoRetryTimeout;
            let retryCountdown = 30;
            
            function startAutoRetry() {
                const retryBtn = document.querySelector('.btn-light-custom');
                const originalText = retryBtn.innerHTML;
                
                function updateCountdown() {
                    if (retryCountdown > 0) {
                        retryBtn.innerHTML = `<i class="fas fa-clock me-2"></i>Auto-retry en ${retryCountdown}s`;
                        retryCountdown--;
                        autoRetryTimeout = setTimeout(updateCountdown, 1000);
                    } else {
                        // Auto-retry
                        location.reload();
                    }
                }
                
                updateCountdown();
            }
            
            // Iniciar auto-retry después de 5 segundos
            setTimeout(startAutoRetry, 5000);
            
            // Cancelar auto-retry si el usuario interactúa
            document.addEventListener('click', function() {
                if (autoRetryTimeout) {
                    clearTimeout(autoRetryTimeout);
                    autoRetryTimeout = null;
                }
            });
            
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
            
            // Enviar reporte de error automático (opcional)
            function sendErrorReport() {
                fetch('/api/error-report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        errorId: errorId,
                        url: window.location.href,
                        userAgent: navigator.userAgent,
                        timestamp: new Date().toISOString(),
                        userId: <?= json_encode($_SESSION['user_id'] ?? null) ?>,
                        sessionId: <?= json_encode(session_id()) ?>
                    })
                }).catch(err => {
                    console.log('Could not send error report:', err);
                });
            }
            
            // Enviar reporte después de 2 segundos
            setTimeout(sendErrorReport, 2000);
        });
    </script>

</body>
</html>