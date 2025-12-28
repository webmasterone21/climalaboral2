<?php
/**
 * Script de Migración - Agregar Tabla activity_logs
 * Sistema HERCO v2.1 - VERSIÓN MEJORADA
 * 
 * CARACTERÍSTICAS:
 * - ✅ Detecta configuración automáticamente
 * - ✅ Formulario de configuración si no existe
 * - ✅ Manejo robusto de errores
 * - ✅ Interfaz profesional
 * 
 * INSTRUCCIONES:
 * 1. Subir a la raíz del proyecto
 * 2. Acceder: http://tudominio.com/add_activity_logs_table_v2.php
 * 3. Seguir instrucciones en pantalla
 * 4. Eliminar después de usar
 * 
 * @version 2.0
 */

// Configuración de seguridad y errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Variables globales de configuración
$dbConfig = null;
$configSource = '';

// PASO 1: Intentar cargar configuración automáticamente
$possibleConfigs = [
    __DIR__ . '/config/database_config.php',
    __DIR__ . '/config/database.php',
    __DIR__ . '/config/db.php',
    __DIR__ . '/config.php'
];

foreach ($possibleConfigs as $configFile) {
    if (file_exists($configFile)) {
        require_once $configFile;
        
        // Verificar si las constantes están definidas
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            $dbConfig = [
                'host' => DB_HOST,
                'port' => defined('DB_PORT') ? DB_PORT : 3306,
                'name' => DB_NAME,
                'user' => DB_USER,
                'pass' => defined('DB_PASS') ? DB_PASS : ''
            ];
            $configSource = 'Archivo: ' . basename($configFile);
            break;
        }
    }
}

// PASO 2: Si no hay configuración en archivo, intentar desde sesión
if ($dbConfig === null && isset($_SESSION['db_migration_config'])) {
    $dbConfig = $_SESSION['db_migration_config'];
    $configSource = 'Sesión temporal';
}

// PASO 3: Si se envió formulario de configuración, guardar en sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $dbConfig = [
        'host' => trim($_POST['db_host'] ?? 'localhost'),
        'port' => intval($_POST['db_port'] ?? 3306),
        'name' => trim($_POST['db_name'] ?? ''),
        'user' => trim($_POST['db_user'] ?? ''),
        'pass' => $_POST['db_pass'] ?? ''
    ];
    
    // Validar que tenemos lo mínimo necesario
    if (empty($dbConfig['host']) || empty($dbConfig['name']) || empty($dbConfig['user'])) {
        $error = "Por favor completa todos los campos requeridos.";
        $dbConfig = null;
    } else {
        $_SESSION['db_migration_config'] = $dbConfig;
        $configSource = 'Configuración manual';
    }
}

/**
 * Función para crear conexión PDO
 */
function createConnection($config) {
    try {
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4",
            $config['host'],
            $config['port'],
            $config['name']
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        return new PDO($dsn, $config['user'], $config['pass'], $options);
        
    } catch (PDOException $e) {
        throw new Exception("Error de conexión: " . $e->getMessage());
    }
}

/**
 * Función para verificar si la tabla existe
 */
function tableExists($pdo, $tableName) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$tableName]);
    return $stmt->rowCount() > 0;
}

/**
 * Función para crear la tabla activity_logs
 */
function createActivityLogsTable($pdo) {
    $sql = "
        CREATE TABLE activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            description TEXT,
            metadata JSON,
            entity_type VARCHAR(50),
            entity_id INT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_action (action),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='Tabla de logs de actividad del sistema HERCO v2.1'
    ";
    
    $pdo->exec($sql);
    return true;
}

/**
 * Función para insertar log de migración
 */
function insertMigrationLog($pdo) {
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, description, entity_type, ip_address, user_agent)
        VALUES (NULL, 'migration', 'Tabla activity_logs creada via script de migración v2', 'system', ?, ?)
    ");
    
    $stmt->execute([
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    return true;
}

// Procesar ejecución de migración
$migrationResult = null;
$migrationError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute_migration']) && $dbConfig) {
    try {
        $pdo = createConnection($dbConfig);
        
        // Verificar versión de MySQL
        $version = $pdo->query("SELECT VERSION()")->fetchColumn();
        
        // Verificar si la tabla ya existe
        if (tableExists($pdo, 'activity_logs')) {
            $migrationResult = [
                'status' => 'warning',
                'message' => 'La tabla activity_logs ya existe en la base de datos.',
                'details' => 'No se realizaron cambios.'
            ];
        } else {
            // Crear la tabla
            createActivityLogsTable($pdo);
            
            // Insertar log de migración
            insertMigrationLog($pdo);
            
            $migrationResult = [
                'status' => 'success',
                'message' => '¡Migración completada exitosamente!',
                'details' => 'La tabla activity_logs ha sido creada correctamente.',
                'mysql_version' => $version
            ];
        }
        
    } catch (Exception $e) {
        $migrationError = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración activity_logs - HERCO v2.1</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .header p {
            opacity: 0.9;
            font-size: 1rem;
        }
        .content {
            padding: 30px;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alert-info {
            background: #d1ecf1;
            border-color: #0c5460;
            color: #0c5460;
        }
        .alert-success {
            background: #d4edda;
            border-color: #155724;
            color: #155724;
        }
        .alert-warning {
            background: #fff3cd;
            border-color: #856404;
            color: #856404;
        }
        .alert-danger {
            background: #f8d7da;
            border-color: #721c24;
            color: #721c24;
        }
        .alert strong {
            display: block;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #6c757d;
            font-size: 0.875rem;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-block {
            width: 100%;
            text-align: center;
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-box h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-item strong {
            color: #667eea;
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            overflow-x: auto;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #212529;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Migración de Base de Datos</h1>
            <p>Agregar tabla <strong>activity_logs</strong> - Sistema HERCO v2.1</p>
        </div>
        
        <div class="content">
            <?php if ($migrationResult): ?>
                <!-- RESULTADO DE LA MIGRACIÓN -->
                <div class="alert alert-<?php echo $migrationResult['status']; ?>">
                    <strong><?php echo $migrationResult['message']; ?></strong>
                    <p><?php echo $migrationResult['details']; ?></p>
                    <?php if (isset($migrationResult['mysql_version'])): ?>
                        <p><small>Versión MySQL: <?php echo $migrationResult['mysql_version']; ?></small></p>
                    <?php endif; ?>
                </div>
                
                <?php if ($migrationResult['status'] === 'success'): ?>
                    <div class="info-box">
                        <h3>✅ Próximos Pasos</h3>
                        <ol style="margin-left: 20px;">
                            <li>Elimina este archivo del servidor por seguridad</li>
                            <li>Verifica que el sistema funciona correctamente</li>
                            <li>Los logs de actividad ahora están habilitados</li>
                        </ol>
                    </div>
                    
                    <a href="/" class="btn btn-success btn-block">Ir al Sistema</a>
                <?php else: ?>
                    <a href="?reset=1" class="btn btn-block">Intentar Nuevamente</a>
                <?php endif; ?>
                
            <?php elseif ($migrationError): ?>
                <!-- ERROR EN LA MIGRACIÓN -->
                <div class="alert alert-danger">
                    <strong>❌ Error en la Migración</strong>
                    <p><?php echo htmlspecialchars($migrationError); ?></p>
                </div>
                
                <a href="?reset=1" class="btn btn-block">Intentar Nuevamente</a>
                
            <?php elseif ($dbConfig === null): ?>
                <!-- FORMULARIO DE CONFIGURACIÓN -->
                <div class="alert alert-warning">
                    <strong>⚙️ Configuración Requerida</strong>
                    <p>No se pudo detectar la configuración de base de datos automáticamente. Por favor, ingresa los datos de conexión:</p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="save_config" value="1">
                    
                    <div class="form-group">
                        <label>Host de Base de Datos *</label>
                        <input type="text" name="db_host" value="localhost" required>
                        <small>Generalmente "localhost" en hosting compartido</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Puerto</label>
                        <input type="number" name="db_port" value="3306" required>
                        <small>Puerto estándar de MySQL: 3306</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre de Base de Datos *</label>
                        <input type="text" name="db_name" required placeholder="mi_base_datos">
                        <small>El nombre exacto de tu base de datos</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Usuario *</label>
                        <input type="text" name="db_user" required placeholder="usuario_bd">
                        <small>Usuario con permisos para crear tablas</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="db_pass" placeholder="••••••••">
                        <small>Puede estar vacía en algunos servidores locales</small>
                    </div>
                    
                    <button type="submit" class="btn btn-block">Guardar y Continuar</button>
                </form>
                
                <div class="info-box" style="margin-top: 30px;">
                    <h3>💡 ¿Dónde Encontrar Esta Información?</h3>
                    <ul style="margin-left: 20px;">
                        <li><strong>cPanel:</strong> Sección "Bases de datos MySQL"</li>
                        <li><strong>Archivo de configuración:</strong> config/database_config.php</li>
                        <li><strong>Hosting:</strong> Panel de control del proveedor</li>
                    </ul>
                </div>
                
            <?php else: ?>
                <!-- INFORMACIÓN ANTES DE EJECUTAR -->
                <div class="alert alert-info">
                    <strong>ℹ️ Configuración Detectada</strong>
                    <p>Se ha cargado la configuración de base de datos correctamente.</p>
                    <p><small>Fuente: <?php echo htmlspecialchars($configSource); ?></small></p>
                </div>
                
                <div class="info-box">
                    <h3>📊 Información de Conexión</h3>
                    <div class="info-item">
                        <strong>Host:</strong> <?php echo htmlspecialchars($dbConfig['host']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Puerto:</strong> <?php echo htmlspecialchars($dbConfig['port']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Base de Datos:</strong> <?php echo htmlspecialchars($dbConfig['name']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Usuario:</strong> <?php echo htmlspecialchars($dbConfig['user']); ?>
                    </div>
                </div>
                
                <div class="info-box">
                    <h3>🔧 ¿Qué Hará Este Script?</h3>
                    <ol style="margin-left: 20px;">
                        <li>Verificará si la tabla <code>activity_logs</code> ya existe</li>
                        <li>Creará la tabla con la estructura correcta si no existe</li>
                        <li>Agregará índices para optimizar las consultas</li>
                        <li>Registrará un log de la migración</li>
                    </ol>
                </div>
                
                <div class="code">-- SQL que se ejecutará:
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    metadata JSON,
    entity_type VARCHAR(50),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                </div>
                
                <div class="alert alert-warning">
                    <strong>⚠️ Importante</strong>
                    <p>Es recomendable hacer un respaldo de la base de datos antes de continuar, aunque este script solo agrega una nueva tabla sin modificar datos existentes.</p>
                </div>
                
                <form method="POST" style="text-align: center;">
                    <input type="hidden" name="execute_migration" value="1">
                    <button type="submit" class="btn btn-success" style="font-size: 1.1rem; padding: 15px 40px;" 
                            onclick="return confirm('¿Estás seguro de ejecutar la migración?')">
                        🚀 Ejecutar Migración
                    </button>
                </form>
                
                <p style="text-align: center; margin-top: 15px;">
                    <a href="?reset=1" style="color: #667eea; text-decoration: none;">← Cambiar Configuración</a>
                </p>
                
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p><strong>Sistema HERCO v2.1</strong> - Script de Migración</p>
            <p style="margin-top: 10px;">
                ⚠️ Recuerda eliminar este archivo después de ejecutar la migración
            </p>
        </div>
    </div>
</body>
</html>
