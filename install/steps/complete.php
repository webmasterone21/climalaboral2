<?php
/**
 * PASO 4: Instalación Completada
 * install/steps/complete.php
 * 
 * VERSIÓN CORREGIDA:
 * - UTF-8 sin corrupción
 * - Interfaz más profesional
 * - Información completa
 * - Experiencia de celebración
 */

// Verificar que la instalación se completó
if (!file_exists('../config/installed.lock')) {
    header('Location: ?step=1');
    exit;
}

// Leer información de la instalación
$installationInfo = [];
if (file_exists('../config/installation.info')) {
    $installationInfo = json_decode(file_get_contents('../config/installation.info'), true) ?? [];
}

$adminUsername = $installationInfo['admin_username'] ?? 'admin';
$adminEmail = $installationInfo['admin_email'] ?? '';
$installationDate = $installationInfo['installation_date'] ?? date('Y-m-d H:i:s');
$appName = $installationInfo['app_name'] ?? 'Sistema de Encuestas Clima Laboral';
$appVersion = $installationInfo['app_version'] ?? '1.0.0';
$installerIP = $installationInfo['installer_ip'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';

// Calcular tiempo de instalación
$installTime = strtotime($installationDate);
$duration = time() - $installTime;
$durationText = $duration < 60 ? 'menos de 1 minuto' : floor($duration / 60) . ' minutos';
?>

<div class="step-content text-center">
    <!-- Animación de éxito -->
    <div class="success-animation mb-4">
        <div class="success-icon mb-3">
            <i class="fas fa-check-circle text-success" style="font-size: 5rem; animation: successPulse 2s ease-in-out infinite;"></i>
        </div>
        <div class="confetti-container" id="confettiContainer"></div>
    </div>
    
    <h2 class="text-success mb-3">¡Instalación Completada Exitosamente!</h2>
    <p class="lead text-muted mb-4">
        Su <strong>Sistema de Encuestas de Clima Laboral</strong> está listo para usar.<br>
        La instalación se completó en <strong><?= $durationText ?></strong>.
    </p>
    
    <!-- Resumen de la Instalación -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Resumen de Instalación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Sistema:</strong></td>
                                    <td><?= htmlspecialchars($appName) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Versión:</strong></td>
                                    <td><?= htmlspecialchars($appVersion) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Instalación:</strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($installationDate)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Duración:</strong></td>
                                    <td><?= $durationText ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Usuario Admin:</strong></td>
                                    <td><?= htmlspecialchars($adminUsername) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?= htmlspecialchars($adminEmail) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td><strong>IP Instalación:</strong></td>
                                    <td><?= htmlspecialchars($installerIP) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Características Instaladas -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-double"></i> Sistema Completamente Configurado</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 
                                    <strong>Base de datos:</strong> Creada y configurada con todas las tablas
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 
                                    <strong>18 categorías HERCO 2024:</strong> Preconfiguradas y listas
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 
                                    <strong>Tipos de preguntas:</strong> Likert, texto, opciones múltiples
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 
                                    <strong>Seguridad empresarial:</strong> Activada y configurada
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 
                                    <strong>Usuario administrador:</strong> Creado con permisos completos
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 
                                    <strong>Plantillas de reportes:</strong> Formato HERCO incluido
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 
                                    <strong>Respaldos automáticos:</strong> Configurados semanalmente
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i> 
                                    <strong>Sistema de auditoría:</strong> Logs y seguimiento activos
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Próximos Pasos -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-rocket"></i> Próximos Pasos Recomendados</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="step-card h-100">
                                <div class="mb-3">
                                    <i class="fas fa-sign-in-alt text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                                <h6 class="fw-bold">1. Acceder al Sistema</h6>
                                <p class="small text-muted">
                                    Inicie sesión con las credenciales de administrador que creó durante la instalación.
                                </p>
                                <a href="../login" class="btn btn-primary btn-sm">
                                    <i class="fas fa-sign-in-alt"></i> Ir al Login
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="step-card h-100">
                                <div class="mb-3">
                                    <i class="fas fa-building text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                                <h6 class="fw-bold">2. Configurar Empresa</h6>
                                <p class="small text-muted">
                                    Agregue el logo, datos corporativos y estructura de departamentos de su organización.
                                </p>
                                <div class="small text-info">
                                    <i class="fas fa-lightbulb"></i> Desde el Panel de Administración
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="step-card h-100">
                                <div class="mb-3">
                                    <i class="fas fa-poll text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                                <h6 class="fw-bold">3. Crear Primera Encuesta</h6>
                                <p class="small text-muted">
                                    Utilice las 18 categorías HERCO preconfiguradas para realizar su primera medición.
                                </p>
                                <div class="small text-info">
                                    <i class="fas fa-chart-line"></i> Reportes automáticos incluidos
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botones de Acción Principales -->
    <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-4">
        <a href="../login" class="btn btn-success btn-lg me-md-2" id="accessSystemBtn">
            <i class="fas fa-sign-in-alt"></i> Acceder al Sistema
        </a>
        <a href="../" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-home"></i> Ir al Inicio
        </a>
        <a href="../admin/dashboard" class="btn btn-outline-success btn-lg">
            <i class="fas fa-tachometer-alt"></i> Dashboard Directo
        </a>
    </div>
    
    <!-- Información de Seguridad -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="alert alert-info">
                <h6><i class="fas fa-shield-alt"></i> Configuración de Seguridad Aplicada</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0 text-start small">
                            <li><strong>Instalador bloqueado:</strong> Directorio /install/ protegido automáticamente</li>
                            <li><strong>Contraseñas encriptadas:</strong> Hash seguro con algoritmos bcrypt</li>
                            <li><strong>Sesiones seguras:</strong> Tokens únicos y expiración automática</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0 text-start small">
                            <li><strong>Logs de auditoría:</strong> Seguimiento completo de actividades</li>
                            <li><strong>Rate limiting:</strong> Protección contra ataques automatizados</li>
                            <li><strong>Validaciones:</strong> Entrada de datos sanitizada y validada</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recursos de Ayuda -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-life-ring"></i> Recursos de Ayuda y Soporte</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="mb-2">
                                <i class="fas fa-book text-muted" style="font-size: 1.8rem;"></i>
                            </div>
                            <h6 class="small fw-bold">Manual de Usuario</h6>
                            <p class="small text-muted">Guía completa de uso del sistema con ejemplos prácticos</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="mb-2">
                                <i class="fas fa-video text-muted" style="font-size: 1.8rem;"></i>
                            </div>
                            <h6 class="small fw-bold">Video Tutoriales</h6>
                            <p class="small text-muted">Aprenda a usar el sistema paso a paso con videos explicativos</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="mb-2">
                                <i class="fas fa-chart-bar text-muted" style="font-size: 1.8rem;"></i>
                            </div>
                            <h6 class="small fw-bold">Guía HERCO 2024</h6>
                            <p class="small text-muted">Mejores prácticas para encuestas de clima laboral</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="mb-2">
                                <i class="fas fa-headset text-muted" style="font-size: 1.8rem;"></i>
                            </div>
                            <h6 class="small fw-bold">Soporte Técnico</h6>
                            <p class="small text-muted">Ayuda especializada cuando lo necesite</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas de la Instalación -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-center"><i class="fas fa-chart-pie"></i> Su Sistema en Números</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="fw-bold text-success" style="font-size: 1.5rem;">25+</div>
                            <div class="small text-muted">Tablas de BD</div>
                        </div>
                        <div class="col-3">
                            <div class="fw-bold text-primary" style="font-size: 1.5rem;">18</div>
                            <div class="small text-muted">Categorías HERCO</div>
                        </div>
                        <div class="col-3">
                            <div class="fw-bold text-info" style="font-size: 1.5rem;">12</div>
                            <div class="small text-muted">Tipos de Pregunta</div>
                        </div>
                        <div class="col-3">
                            <div class="fw-bold text-warning" style="font-size: 1.5rem;">3</div>
                            <div class="small text-muted">Plantillas Reporte</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer de Instalación -->
    <div class="mt-5 pt-4 border-top">
        <p class="text-muted small mb-1">
            <i class="fas fa-check-circle text-success me-1"></i>
            <strong>Sistema de Encuestas de Clima Laboral</strong> v<?= htmlspecialchars($appVersion) ?>
        </p>
        <p class="text-muted small mb-0">
            Instalado exitosamente el <?= date('d/m/Y \a \l\a\s H:i', strtotime($installationDate)) ?>
        </p>
        <p class="text-muted small">
            <i class="fas fa-shield-alt text-success me-1"></i>
            Sistema listo para producción con configuración de seguridad empresarial
        </p>
    </div>
</div>

<style>
/* Animaciones y estilos específicos */
@keyframes successPulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes confettiFall {
    0% {
        transform: translateY(-100vh) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #ff6b6b;
    animation: confettiFall 3s linear infinite;
}

.confetti:nth-child(2) { background: #4ecdc4; animation-delay: 0.2s; left: 20%; }
.confetti:nth-child(3) { background: #45b7d1; animation-delay: 0.4s; left: 40%; }
.confetti:nth-child(4) { background: #96ceb4; animation-delay: 0.6s; left: 60%; }
.confetti:nth-child(5) { background: #ffeaa7; animation-delay: 0.8s; left: 80%; }

.success-animation {
    position: relative;
    overflow: hidden;
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.step-card {
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.step-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.table-borderless td {
    border: none;
    padding: 0.25rem 0.5rem;
}

.list-unstyled li {
    padding: 2px 0;
}

#accessSystemBtn {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

.alert {
    border-radius: 12px;
}

.badge {
    font-size: 0.8rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Crear efecto confetti
    createConfetti();
    
    // Agregar efectos de sonido (opcional)
    playSuccessSound();
    
    // Auto-redirect countdown (opcional)
    // startCountdown();
    
    function createConfetti() {
        const container = document.getElementById('confettiContainer');
        const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7', '#fd79a8', '#fdcb6e'];
        
        for(let i = 0; i < 50; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 2 + 's';
                container.appendChild(confetti);
                
                // Remover después de la animación
                setTimeout(() => {
                    if (confetti.parentNode) {
                        confetti.remove();
                    }
                }, 3000);
            }, i * 100);
        }
    }
    
    function playSuccessSound() {
        // Crear un breve sonido de éxito usando Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime); // Do
            oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1); // Mi
            oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2); // Sol
            
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (e) {
            // Los navegadores pueden bloquear el audio sin interacción del usuario
            console.log('Audio no disponible:', e);
        }
    }
    
    // Efecto especial en botones
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Mensaje de bienvenida personalizado
    setTimeout(() => {
        const username = '<?= htmlspecialchars($adminUsername) ?>';
        console.log(`🎉 ¡Bienvenido, ${username}! Tu sistema está listo.`);
    }, 1000);
});

// Easter egg - Konami code para más confetti
let konamiCode = [];
const konamiSequence = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65]; // ↑↑↓↓←→←→BA

document.addEventListener('keydown', function(e) {
    konamiCode.push(e.keyCode);
    if (konamiCode.length > konamiSequence.length) {
        konamiCode.shift();
    }
    
    if (konamiCode.length === konamiSequence.length && 
        konamiCode.every((key, index) => key === konamiSequence[index])) {
        // ¡Código Konami activado!
        document.getElementById('confettiContainer').style.display = 'block';
        for(let i = 0; i < 100; i++) {
            setTimeout(createConfetti, i * 50);
        }
        konamiCode = [];
    }
});
</script>