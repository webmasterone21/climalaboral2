<?php
/**
 * Instalador del Sistema HERCO v2.0
 * Versión CORREGIDA - Plug and Play
 */

// ⭐ ACTIVAR ERRORES PARA DEBUG
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificar si ya está instalado
if (file_exists(__DIR__ . '/../config/installed.lock')) {
    header('Location: /');
    exit('Sistema ya instalado');
}

require_once __DIR__ . '/AutoInstaller.php';
$installer = new AutoInstaller();

// Determinar el paso actual
$step = $_GET['step'] ?? 'requirements';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Sistema HERCO v2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        .installer-container {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }
        .installer-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .installer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .installer-body {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
        }
        .step.active {
            color: #667eea;
            font-weight: bold;
        }
        .step.completed {
            color: #28a745;
        }
        .requirement-item {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .requirement-item.success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }
        .requirement-item.error {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .alert-custom {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-card">
            <div class="installer-header">
                <h1>🚀 Instalador Sistema HERCO v2.0</h1>
                <p class="mb-0">Instalación Automática en 3 Pasos</p>
            </div>
            
            <div class="installer-body">
                <div class="step-indicator">
                    <div class="step <?php echo $step === 'requirements' ? 'active' : ($step !== 'requirements' ? 'completed' : ''); ?>">
                        1. Requisitos
                    </div>
                    <div class="step <?php echo $step === 'database' ? 'active' : ($step === 'complete' ? 'completed' : ''); ?>">
                        2. Configuración
                    </div>
                    <div class="step <?php echo $step === 'complete' ? 'active' : ''; ?>">
                        3. Completado
                    </div>
                </div>

                <?php if ($step === 'requirements'): ?>
                    <!-- PASO 1: REQUISITOS -->
                    <h3 class="mb-4">Verificación de Requisitos del Sistema</h3>
                    
                    <?php
                    $requirements = $installer->checkRequirements();
                    $allMet = true;
                    
                    foreach ($requirements as $key => $req) {
                        if (!$req['status']) {
                            $allMet = false;
                        }
                    }
                    ?>
                    
                    <div class="requirements-list">
                        <?php foreach ($requirements as $key => $req): ?>
                            <div class="requirement-item <?php echo $req['status'] ? 'success' : 'error'; ?>">
                                <span>
                                    <?php if ($key === 'php_version'): ?>
                                        PHP >= <?php echo $req['required']; ?> (Instalado: <?php echo $req['current']; ?>)
                                    <?php else: ?>
                                        <?php echo $req['name']; ?>
                                    <?php endif; ?>
                                </span>
                                <span>
                                    <?php if ($req['status']): ?>
                                        <strong style="color: #28a745;">✅ OK</strong>
                                    <?php else: ?>
                                        <strong style="color: #dc3545;">❌ Falta</strong>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($allMet): ?>
                        <div class="alert alert-success mt-4">
                            <strong>✅ Perfecto!</strong> Todos los requisitos se cumplen. Puedes continuar con la instalación.
                        </div>
                        <div class="text-end mt-4">
                            <a href="?step=database" class="btn btn-primary btn-lg">
                                Continuar a Configuración →
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger mt-4">
                            <strong>❌ Requisitos Faltantes</strong>
                            <p class="mb-0">Por favor, corrige los requisitos marcados en rojo antes de continuar.</p>
                        </div>
                    <?php endif; ?>

                <?php elseif ($step === 'database'): ?>
                    <!-- PASO 2: CONFIGURACIÓN -->
                    <h3 class="mb-4">Configuración del Sistema</h3>
                    
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
                        echo "<div class='alert-custom' style='background: #d1ecf1; color: #0c5460;'>";
                        echo "<strong>🔄 Instalando...</strong> Por favor espera...";
                        echo "</div>";
                        
                        // ⭐ MOSTRAR ERRORES DE INSTALACIÓN
                        $result = $installer->install($_POST);
                        
                        if ($result['success']) {
                            echo "<div class='alert alert-success'>";
                            echo "<strong>✅ ¡Instalación Exitosa!</strong>";
                            echo "</div>";
                            
                            // Mostrar detalles de cada paso
                            echo "<h5>Detalles de la Instalación:</h5>";
                            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
                            foreach ($result['steps'] as $stepName => $stepResult) {
                                $icon = $stepResult['success'] ? '✅' : '❌';
                                echo "<p><strong>$icon</strong> " . ucfirst(str_replace('_', ' ', $stepName)) . ": " . $stepResult['message'] . "</p>";
                            }
                            echo "</div>";
                            
                            echo "<script>
                                setTimeout(function() {
                                    window.location.href = '?step=complete';
                                }, 2000);
                            </script>";
                        } else {
                            echo "<div class='alert alert-danger'>";
                            echo "<strong>❌ Error durante la instalación</strong><br><br>";
                            
                            // ⭐ MOSTRAR ERRORES DETALLADOS
                            foreach ($result['steps'] as $stepName => $stepResult) {
                                if (!$stepResult['success']) {
                                    echo "<strong>" . ucfirst(str_replace('_', ' ', $stepName)) . ":</strong><br>";
                                    echo htmlspecialchars($stepResult['message']) . "<br><br>";
                                }
                            }
                            echo "</div>";
                            
                            echo "<a href='?step=database' class='btn btn-secondary'>← Volver a Intentar</a>";
                        }
                    } else {
                    ?>
                    
                    <form method="POST" action="?step=database" id="installForm">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <strong>📊 Configuración de Base de Datos MySQL</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Host de MySQL</label>
                                        <input type="text" name="db_host" class="form-control" 
                                               value="localhost" required>
                                        <small class="text-muted">Generalmente: localhost</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Puerto</label>
                                        <input type="number" name="db_port" class="form-control" 
                                               value="3306" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nombre de la Base de Datos</label>
                                    <input type="text" name="db_database" class="form-control" 
                                           value="herco_db" required>
                                    <small class="text-muted">Se creará automáticamente si no existe</small>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Usuario de MySQL</label>
                                        <input type="text" name="db_username" class="form-control" 
                                               value="root" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contraseña de MySQL</label>
                                        <input type="password" name="db_password" class="form-control"
                                               placeholder="(dejar vacío si no tiene)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <strong>👤 Usuario Administrador</strong>
                            </div>
                            <div class="card-body">
                                                            
                                <div class="mb-3">
                                    <label class="form-label">Nombre Completo del Administrador</label>
<input type="text" name="admin_name" class="form-control" 
       placeholder="Juan Pérez" required minlength="3">
                                    <input type="email" name="admin_email" class="form-control" 
                                           placeholder="admin@ejemplo.com" required>
                                </div>
                                <div class="mb-3">
    <label class="form-label">Email del Administrador</label>
    <input type="email" name="admin_email" class="form-control" 
           placeholder="admin@ejemplo.com" required>
</div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Contraseña del Administrador</label>
                                    <input type="password" name="admin_password" class="form-control" 
                                           minlength="8" required>
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">URL del Sitio (opcional)</label>
                            <input type="url" name="site_url" class="form-control" 
                                   value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                            <small class="text-muted">Se autodetectó la URL actual</small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="?step=requirements" class="btn btn-secondary">
                                ← Volver
                            </a>
                            <button type="submit" name="install" class="btn btn-primary btn-lg">
                                🚀 Instalar Sistema Ahora
                            </button>
                        </div>
                    </form>
                    
                    <?php } ?>

                <?php elseif ($step === 'complete'): ?>
                    <!-- PASO 3: COMPLETADO -->
                    <div class="text-center">
                        <div style="font-size: 72px; margin-bottom: 20px;">🎉</div>
                        <h2 class="text-success mb-4">¡Instalación Completada Exitosamente!</h2>
                        
                        <div class="alert alert-success text-start">
                            <h5>✅ El sistema está listo para usar</h5>
                            <ul class="mb-0">
                                <li>Base de datos creada y configurada</li>
                                <li>8 tablas del sistema instaladas</li>
                                <li>18 categorías HERCO preconfiguradas</li>
                                <li>Usuario administrador creado</li>
                                <li>Archivos de configuración generados</li>
                            </ul>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5>🔑 Tus Credenciales de Acceso</h5>
                                <table class="table table-sm">
                                    
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><code><?php echo htmlspecialchars($_SESSION['install_email'] ?? ''); ?></code></td>
                                    </tr>
                                </table>
                                <small class="text-muted">Guarda estas credenciales en un lugar seguro</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning text-start">
                            <strong>⚠️ Importante - Seguridad:</strong>
                            <p>Por seguridad, bloquea el acceso al instalador editando tu <code>.htaccess</code>:</p>
                            <pre style="font-size: 11px;"># Comentar estas líneas (añadir # al inicio):
# RewriteCond %{REQUEST_URI} ^/install/ [NC]
# RewriteRule ^ - [L]</pre>
                        </div>
                        
                        <a href="/login" class="btn btn-primary btn-lg">
                            🎯 Ir al Sistema →
                        </a>
                    </div>
                    
                    <?php
                    unset($_SESSION['install_username']);
                    unset($_SESSION['install_email']);
                    ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <p class="text-white small">Sistema HERCO v2.0 - Instalación Plug & Play</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
