<?php
/**
 * PASO 1: Verificación de Requisitos
 * install/steps/requirements.php
 * 
 * VERSIÓN CORREGIDA:
 * - UTF-8 sin corrupción
 * - Verificaciones adicionales
 * - Mejor categorización de errores
 * - Interfaz mejorada
 */

// Verificar requisitos del servidor de forma exhaustiva
$requirements = [
    'php_version' => [
        'name' => 'PHP 7.4 o superior',
        'required' => true,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'current' => PHP_VERSION,
        'description' => 'Versión mínima requerida para el sistema',
        'recommendation' => 'Se recomienda PHP 8.1 o superior para mejor rendimiento'
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL Extension',
        'required' => true,
        'status' => extension_loaded('pdo_mysql'),
        'current' => extension_loaded('pdo_mysql') ? 'Instalado' : 'No instalado',
        'description' => 'Requerido para conexión a base de datos MySQL',
        'recommendation' => 'Esencial para el funcionamiento del sistema'
    ],
    'mbstring' => [
        'name' => 'Mbstring Extension',
        'required' => true,
        'status' => extension_loaded('mbstring'),
        'current' => extension_loaded('mbstring') ? 'Instalado' : 'No instalado',
        'description' => 'Requerido para soporte de caracteres UTF-8',
        'recommendation' => 'Necesario para caracteres especiales y acentos'
    ],
    'openssl' => [
        'name' => 'OpenSSL Extension',
        'required' => true,
        'status' => extension_loaded('openssl'),
        'current' => extension_loaded('openssl') ? 'Instalado' : 'No instalado',
        'description' => 'Requerido para encriptación y seguridad',
        'recommendation' => 'Esencial para hash de contraseñas y tokens seguros'
    ],
    'json' => [
        'name' => 'JSON Extension',
        'required' => true,
        'status' => extension_loaded('json'),
        'current' => extension_loaded('json') ? 'Instalado' : 'No instalado',
        'description' => 'Requerido para procesamiento de datos JSON',
        'recommendation' => 'Necesario para configuraciones y datos del sistema'
    ],
    'file_permissions' => [
        'name' => 'Permisos de Escritura',
        'required' => true,
        'status' => is_writable('../config') && is_writable('../uploads'),
        'current' => is_writable('../config') && is_writable('../uploads') ? 'Configurado correctamente' : 'Sin permisos de escritura',
        'description' => 'Carpetas config/ y uploads/ deben ser escribibles',
        'recommendation' => 'chmod 755 o 775 en las carpetas necesarias'
    ],
    'memory_limit' => [
        'name' => 'Límite de Memoria PHP',
        'required' => false,
        'status' => true, // Siempre pasa, es informativo
        'current' => ini_get('memory_limit'),
        'description' => 'Memoria disponible para PHP',
        'recommendation' => 'Recomendado: 128MB o superior para operaciones complejas'
    ],
    'max_execution_time' => [
        'name' => 'Tiempo Máximo de Ejecución',
        'required' => false,
        'status' => (int)ini_get('max_execution_time') >= 60 || ini_get('max_execution_time') == 0,
        'current' => ini_get('max_execution_time') == 0 ? 'Sin límite' : ini_get('max_execution_time') . ' segundos',
        'description' => 'Tiempo límite para scripts PHP',
        'recommendation' => 'Mínimo 60 segundos para instalación'
    ],
    'post_max_size' => [
        'name' => 'Tamaño Máximo POST',
        'required' => false,
        'status' => return_bytes(ini_get('post_max_size')) >= 8388608, // 8MB
        'current' => ini_get('post_max_size'),
        'description' => 'Tamaño máximo de datos POST',
        'recommendation' => 'Mínimo 8MB para subida de archivos'
    ],
    'upload_max_filesize' => [
        'name' => 'Tamaño Máximo de Archivo',
        'required' => false,
        'status' => return_bytes(ini_get('upload_max_filesize')) >= 5242880, // 5MB
        'current' => ini_get('upload_max_filesize'),
        'description' => 'Tamaño máximo de archivos subidos',
        'recommendation' => 'Mínimo 5MB para logos y documentos'
    ]
];

// Función helper para convertir tamaños
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

// Verificar si todos los requisitos obligatorios se cumplen
$allRequirementsMet = true;
$criticalErrors = [];
$warnings = [];

foreach($requirements as $key => $req) {
    if($req['required'] && !$req['status']) {
        $allRequirementsMet = false;
        $criticalErrors[] = $req['name'];
    } elseif(!$req['required'] && !$req['status']) {
        $warnings[] = $req['name'];
    }
}

// Verificaciones adicionales del sistema
$systemInfo = [
    'os' => php_uname('s') . ' ' . php_uname('r'),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
    'php_version' => PHP_VERSION,
    'php_sapi' => php_sapi_name(),
    'max_memory' => ini_get('memory_limit'),
    'max_execution' => ini_get('max_execution_time') . 's',
    'max_post' => ini_get('post_max_size'),
    'max_upload' => ini_get('upload_max_filesize'),
    'date_timezone' => date_default_timezone_get(),
    'loaded_extensions' => count(get_loaded_extensions())
];
?>

<div class="step-content">
    <h3><i class="fas fa-server text-primary"></i> Verificación de Requisitos del Sistema</h3>
    <p class="text-muted">
        Verificando que su servidor cumple con los requisitos técnicos necesarios para el funcionamiento óptimo del sistema.
    </p>
    
    <!-- Estado general -->
    <?php if(!$allRequirementsMet): ?>
    <div class="alert alert-danger">
        <h5><i class="fas fa-exclamation-triangle"></i> Requisitos Críticos No Cumplidos</h5>
        <p>Los siguientes requisitos <strong>obligatorios</strong> no se cumplen y deben ser resueltos antes de continuar:</p>
        <ul class="mb-3">
            <?php foreach($criticalErrors as $error): ?>
                <li><strong><?= htmlspecialchars($error) ?></strong></li>
            <?php endforeach; ?>
        </ul>
        <div class="bg-light p-3 rounded">
            <h6><i class="fas fa-question-circle"></i> ¿Necesita ayuda?</h6>
            <p class="mb-0">
                Contacte a su proveedor de hosting o administrador del servidor para resolver estos problemas.
                La mayoría de hostings modernos incluyen estas extensiones por defecto.
            </p>
        </div>
    </div>
    <?php elseif(!empty($warnings)): ?>
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-circle"></i> Advertencias de Configuración</h5>
        <p>Su servidor cumple los requisitos mínimos, pero hay algunas configuraciones que podrían mejorarse:</p>
        <ul class="mb-0">
            <?php foreach($warnings as $warning): ?>
                <li><?= htmlspecialchars($warning) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php else: ?>
    <div class="alert alert-success">
        <h5><i class="fas fa-check-circle"></i> Servidor Completamente Compatible</h5>
        <p class="mb-0">
            Su servidor cumple con todos los requisitos técnicos. 
            Puede proceder con confianza con la instalación del sistema.
        </p>
    </div>
    <?php endif; ?>
    
    <!-- Lista detallada de verificaciones -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list-check"></i> Verificaciones Detalladas</h5>
        </div>
        <div class="card-body p-0">
            <?php foreach($requirements as $key => $req): ?>
            <div class="requirement-check <?= $req['status'] ? ($req['required'] ? 'success' : 'info') : ($req['required'] ? 'error' : 'warning') ?>">
                <div class="requirement-info">
                    <h6 class="mb-1">
                        <?php if($req['status']): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php else: ?>
                            <i class="fas fa-<?= $req['required'] ? 'times-circle text-danger' : 'exclamation-triangle text-warning' ?>"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($req['name']) ?>
                        <?php if(!$req['required']): ?>
                            <span class="badge bg-secondary ms-2">Opcional</span>
                        <?php else: ?>
                            <span class="badge bg-primary ms-2">Requerido</span>
                        <?php endif; ?>
                    </h6>
                    <small class="text-muted d-block"><?= htmlspecialchars($req['description']) ?></small>
                    <?php if(isset($req['recommendation'])): ?>
                        <small class="text-info d-block"><i class="fas fa-lightbulb"></i> <?= htmlspecialchars($req['recommendation']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="requirement-status">
                    <strong><?= htmlspecialchars($req['current']) ?></strong>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Información detallada del servidor -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información Técnica del Servidor</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Sistema Operativo:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['os']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Servidor Web:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['server']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Versión PHP:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['php_version']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>SAPI PHP:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['php_sapi']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Zona Horaria:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['date_timezone']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Límite de Memoria:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['max_memory']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tiempo Máximo:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['max_execution']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tamaño Máximo POST:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['max_post']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tamaño Máximo Upload:</strong></td>
                            <td><?= htmlspecialchars($systemInfo['max_upload']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Extensiones Cargadas:</strong></td>
                            <td><?= $systemInfo['loaded_extensions'] ?> extensiones</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botones de navegación -->
    <div class="d-flex justify-content-between mt-4">
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Verificar Nuevamente
        </button>
        
        <?php if($allRequirementsMet): ?>
        <a href="?step=2" class="btn btn-primary btn-lg">
            <i class="fas fa-arrow-right"></i> Continuar con Base de Datos
        </a>
        <?php else: ?>
        <button class="btn btn-secondary btn-lg" disabled title="Resuelva los problemas críticos primero">
            <i class="fas fa-times"></i> Resolver Problemas Primero
        </button>
        <?php endif; ?>
    </div>
    
    <!-- Ayuda y solución de problemas -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-question-circle"></i> Solución de Problemas Comunes</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-server"></i> Hosting Compartido</h6>
                    <p class="small">Si está usando hosting compartido (GoDaddy, Hostinger, etc.):</p>
                    <ul class="small">
                        <li>Contacte soporte técnico para habilitar extensiones faltantes</li>
                        <li>Solicite ajuste de permisos en carpetas</li>
                        <li>Verifique que tenga PHP 7.4 o superior</li>
                        <li>Asegúrese de que PDO MySQL esté habilitado</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-terminal"></i> Servidor Propio/VPS</h6>
                    <p class="small">En su propio servidor, ejecute estos comandos:</p>
                    <div class="bg-dark text-light p-2 rounded small">
                        <code>
                            # Ubuntu/Debian:<br>
                            sudo apt-get install php-mysql php-mbstring<br>
                            sudo chmod 755 config/ uploads/<br>
                            sudo systemctl restart apache2
                        </code>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-12">
                    <h6><i class="fas fa-exclamation-triangle"></i> Problemas Frecuentes</h6>
                    <div class="accordion" id="troubleshootingAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    PDO MySQL no está instalado
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body small">
                                    <strong>Solución:</strong> Esta extensión es esencial para conectar con MySQL.
                                    <br>• <strong>Hosting compartido:</strong> Contacte soporte técnico
                                    <br>• <strong>Servidor propio:</strong> <code>sudo apt-get install php-mysql</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Permisos de escritura incorrectos
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body small">
                                    <strong>Solución:</strong> El servidor web debe poder escribir en ciertas carpetas.
                                    <br>• <strong>Comando:</strong> <code>chmod 755 config/ uploads/</code>
                                    <br>• <strong>Panel de control:</strong> Use el administrador de archivos para cambiar permisos
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Versión de PHP muy antigua
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body small">
                                    <strong>Solución:</strong> Se requiere PHP 7.4 o superior.
                                    <br>• <strong>Hosting compartido:</strong> Cambie la versión desde el panel de control
                                    <br>• <strong>Servidor propio:</strong> Actualice PHP a una versión compatible
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>