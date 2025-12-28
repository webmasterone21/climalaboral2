<?php
/**
 * Configuración del Sistema de Respaldos
 * config/backup.php
 * 
 * Este archivo contiene todas las configuraciones estáticas
 * del sistema de respaldos automáticos
 */

// ==========================================
// CONFIGURACIONES PRINCIPALES
// ==========================================

// Rutas del sistema de respaldos
define('BACKUP_PATH', __DIR__ . '/../backups/');
define('BACKUP_LOG_FILE', BACKUP_PATH . 'backup.log');
define('BACKUP_CRON_LOG_FILE', BACKUP_PATH . 'cron.log');
define('BACKUP_TEMP_PATH', sys_get_temp_dir() . '/backup_temp/');

// ==========================================
// CONFIGURACIONES DE MYSQL
// ==========================================

// Configuración de conexión para mysqldump
$BACKUP_CONFIG = [
    // Configuraciones de base de datos (se sobrescriben por constantes si existen)
    'database' => [
        'host' => DB_HOST ?? 'localhost',
        'username' => DB_USER ?? 'root', 
        'password' => DB_PASS ?? '',
        'database' => DB_NAME ?? 'encuestas_clima',
        'port' => DB_PORT ?? 3306,
        'charset' => 'utf8mb4'
    ],
    
    // Configuraciones por defecto del sistema
    'defaults' => [
        'backup_frequency' => 'weekly',      // daily, weekly, monthly
        'backup_time' => '02:00:00',         // Hora del día para respaldos automáticos
        'max_backups' => 10,                 // Número máximo de respaldos a mantener
        'auto_backup' => true,               // Activar respaldos automáticos
        'compress_backups' => true,          // Comprimir respaldos por defecto
        'verify_integrity' => true,          // Verificar integridad después del respaldo
        'create_safety_backup' => true       // Crear respaldo de seguridad antes de restaurar
    ],
    
    // Opciones de mysqldump
    'mysqldump_options' => [
        '--single-transaction',              // Consistencia de datos
        '--routines',                        // Incluir procedimientos almacenados
        '--triggers',                        // Incluir triggers
        '--events',                          // Incluir eventos programados
        '--hex-blob',                        // Formato hexadecimal para datos binarios
        '--opt',                            // Optimizaciones automáticas
        '--quick',                          // Recuperar filas una a la vez
        '--lock-tables=false',              // No bloquear tablas
        '--add-drop-table',                 // Agregar DROP TABLE antes de CREATE
        '--disable-keys',                   // Deshabilitar índices durante inserción
        '--extended-insert',                // Usar sintaxis de inserción extendida
        '--complete-insert'                 // Usar nombres de columnas en INSERT
    ],
    
    // Tablas a excluir del respaldo (opcional)
    'excluded_tables' => [
        // 'temp_table',
        // 'cache_table',
        // 'session_data'
    ],
    
    // Configuraciones de compresión
    'compression' => [
        'enabled' => true,
        'level' => 9,                       // Nivel de compresión (1-9)
        'method' => 'gzip',                 // gzip, bzip2
        'extension' => '.gz'
    ],
    
    // Configuraciones de seguridad
    'security' => [
        'require_admin' => true,            // Requerir permisos de admin
        'verify_checksums' => true,         // Verificar checksums de archivos
        'secure_delete' => true,            // Sobrescribir archivos al eliminar
        'access_token_required' => true,    // Requerir token para acceso programático
        'token_expiry_hours' => 24          // Expiración del token de acceso
    ],
    
    // Configuraciones de notificaciones
    'notifications' => [
        'enabled' => false,                 // Activar notificaciones por email
        'on_success' => false,              // Notificar respaldos exitosos
        'on_error' => true,                 // Notificar errores
        'on_critical' => true,              // Notificar errores críticos
        'admin_email' => 'admin@encuestas.com',
        'from_email' => 'sistema@encuestas.com',
        'from_name' => 'Sistema de Respaldos'
    ],
    
    // Configuraciones de validación
    'validation' => [
        'check_file_size' => true,          // Verificar tamaño mínimo de archivo
        'min_file_size_kb' => 50,           // Tamaño mínimo en KB
        'check_sql_structure' => true,      // Verificar estructura SQL válida
        'check_mysql_header' => true,       // Verificar header de MySQL
        'sample_restore_test' => false      // Probar restauración en BD temporal
    ],
    
    // Configuraciones de rendimiento
    'performance' => [
        'memory_limit' => '512M',           // Límite de memoria para operaciones
        'execution_time_limit' => 3600,     // Límite de tiempo en segundos (1 hora)
        'chunk_size' => 8192,               // Tamaño de chunk para operaciones de archivo
        'parallel_compression' => false,    // Compresión en paralelo (requiere threads)
        'optimize_tables' => false          // Optimizar tablas antes del respaldo
    ],
    
    // Configuraciones de logs
    'logging' => [
        'enabled' => true,
        'level' => 'INFO',                  // DEBUG, INFO, WARNING, ERROR, CRITICAL
        'max_log_size_mb' => 10,            // Tamaño máximo del archivo de log
        'rotate_logs' => true,              // Rotar logs automáticamente
        'keep_log_days' => 30,              // Días de logs a mantener
        'log_to_database' => true,          // Registrar en tabla backup_logs
        'log_to_file' => true,              // Registrar en archivo
        'log_to_syslog' => false            // Registrar en syslog del sistema
    ],
    
    // Configuraciones de monitoreo
    'monitoring' => [
        'disk_space_warning_percent' => 85, // Advertir cuando el disco esté al X%
        'disk_space_critical_percent' => 95, // Crítico cuando el disco esté al X%
        'backup_age_warning_days' => 7,     // Advertir si no hay respaldo en X días
        'backup_age_critical_days' => 14,   // Crítico si no hay respaldo en X días
        'check_backup_integrity' => true,   // Verificar integridad periódicamente
        'integrity_check_frequency' => 'weekly' // daily, weekly, monthly
    ]
];

// ==========================================
// CONFIGURACIONES AVANZADAS
// ==========================================

// Configuraciones específicas del entorno
$BACKUP_ENV_CONFIG = [
    'development' => [
        'auto_backup' => false,
        'compress_backups' => false,
        'notifications' => ['enabled' => false],
        'logging' => ['level' => 'DEBUG'],
        'max_backups' => 5
    ],
    
    'staging' => [
        'auto_backup' => true,
        'backup_frequency' => 'daily',
        'compress_backups' => true,
        'notifications' => ['enabled' => true, 'on_success' => false],
        'max_backups' => 7
    ],
    
    'production' => [
        'auto_backup' => true,
        'backup_frequency' => 'daily', 
        'compress_backups' => true,
        'notifications' => ['enabled' => true, 'on_success' => true],
        'max_backups' => 30,
        'create_safety_backup' => true,
        'verify_integrity' => true
    ]
];

// ==========================================
// FUNCIONES DE CONFIGURACIÓN
// ==========================================

/**
 * Obtener configuración combinada según el entorno
 */
function getBackupConfig($environment = null) {
    global $BACKUP_CONFIG, $BACKUP_ENV_CONFIG;
    
    if (!$environment) {
        $environment = defined('APP_ENV') ? APP_ENV : 'production';
    }
    
    $config = $BACKUP_CONFIG;
    
    // Aplicar configuraciones específicas del entorno
    if (isset($BACKUP_ENV_CONFIG[$environment])) {
        $config = array_merge_recursive($config, $BACKUP_ENV_CONFIG[$environment]);
    }
    
    return $config;
}

/**
 * Obtener configuración específica por clave
 */
function getBackupConfigValue($key, $default = null, $environment = null) {
    $config = getBackupConfig($environment);
    
    // Soporte para notación de puntos (ej: 'defaults.max_backups')
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }
    
    return $value;
}

/**
 * Validar configuración del sistema
 */
function validateBackupConfig() {
    $errors = [];
    
    // Verificar directorios
    if (!is_dir(BACKUP_PATH)) {
        if (!mkdir(BACKUP_PATH, 0755, true)) {
            $errors[] = "No se puede crear el directorio de respaldos: " . BACKUP_PATH;
        }
    }
    
    if (!is_writable(BACKUP_PATH)) {
        $errors[] = "El directorio de respaldos no tiene permisos de escritura: " . BACKUP_PATH;
    }
    
    // Verificar extensiones PHP requeridas
    $requiredExtensions = ['pdo', 'pdo_mysql'];
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "Extensión PHP requerida no disponible: {$ext}";
        }
    }
    
    // Verificar compresión si está habilitada
    if (getBackupConfigValue('compression.enabled', true)) {
        if (!extension_loaded('zlib')) {
            $errors[] = "Extensión zlib no disponible para compresión";
        }
    }
    
    // Verificar mysqldump
    $output = [];
    $returnVar = 0;
    exec('mysqldump --version 2>&1', $output, $returnVar);
    
    if ($returnVar !== 0) {
        $errors[] = "mysqldump no está disponible en el sistema";
    }
    
    // Verificar espacio en disco
    $freeSpace = disk_free_space(BACKUP_PATH);
    if ($freeSpace !== false && $freeSpace < (100 * 1024 * 1024)) { // 100MB
        $errors[] = "Espacio en disco insuficiente (menos de 100MB disponibles)";
    }
    
    return $errors;
}

/**
 * Obtener información del sistema para respaldos
 */
function getBackupSystemInfo() {
    return [
        'php_version' => PHP_VERSION,
        'mysql_client_version' => function_exists('mysql_get_client_info') ? mysql_get_client_info() : 'N/A',
        'backup_path' => BACKUP_PATH,
        'backup_path_writable' => is_writable(BACKUP_PATH),
        'free_space' => disk_free_space(BACKUP_PATH),
        'total_space' => disk_total_space(BACKUP_PATH),
        'extensions' => [
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'zlib' => extension_loaded('zlib'),
            'openssl' => extension_loaded('openssl')
        ],
        'mysqldump_available' => shell_exec('mysqldump --version') !== null,
        'current_user' => get_current_user(),
        'operating_system' => PHP_OS,
        'server_name' => gethostname()
    ];
}

// ==========================================
// INICIALIZACIÓN
// ==========================================

// Crear directorios necesarios si no existen
if (!is_dir(BACKUP_PATH)) {
    mkdir(BACKUP_PATH, 0755, true);
}

if (!is_dir(BACKUP_TEMP_PATH)) {
    mkdir(BACKUP_TEMP_PATH, 0755, true);
}

// Configurar zona horaria si no está definida
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

// Configurar límites de memoria y tiempo según configuración
$config = getBackupConfig();
if (isset($config['performance']['memory_limit'])) {
    ini_set('memory_limit', $config['performance']['memory_limit']);
}

if (isset($config['performance']['execution_time_limit'])) {
    set_time_limit($config['performance']['execution_time_limit']);
}

// ==========================================
// VALIDACIÓN INICIAL
// ==========================================

// Ejecutar validación solo si se llama directamente
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Validación de Configuración de Respaldos ===\n";
    
    $errors = validateBackupConfig();
    
    if (empty($errors)) {
        echo "✅ Configuración válida - Sistema de respaldos listo\n";
        
        $info = getBackupSystemInfo();
        echo "\n=== Información del Sistema ===\n";
        foreach ($info as $key => $value) {
            if (is_array($value)) {
                echo "{$key}:\n";
                foreach ($value as $subKey => $subValue) {
                    echo "  {$subKey}: " . ($subValue ? 'Sí' : 'No') . "\n";
                }
            } else {
                echo "{$key}: {$value}\n";
            }
        }
    } else {
        echo "❌ Errores de configuración encontrados:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
        exit(1);
    }
}

// ==========================================
// CONSTANTES ADICIONALES
// ==========================================

// Token de seguridad para acceso programático
define('BACKUP_ACCESS_TOKEN', hash('sha256', 'backup_access_' . (defined('APP_KEY') ? APP_KEY : 'default_key')));

// Prefijo para archivos de respaldo
define('BACKUP_FILE_PREFIX', 'backup_');

// Formatos de fecha para archivos
define('BACKUP_DATE_FORMAT', 'Y-m-d_H-i-s');

// Tipos de respaldo válidos
define('BACKUP_TYPES', ['manual', 'automatic', 'scheduled', 'safety_before_restore']);

// Estados de respaldo válidos
define('BACKUP_STATUSES', ['in_progress', 'success', 'error', 'cancelled', 'deleted']);

// Niveles de log válidos
define('LOG_LEVELS', ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL']);

?>