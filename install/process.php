<?php
/**
 * Procesador del Instalador HERCO v2.1
 * install/process.php
 * 
 * VERSIÓN TOTALMENTE CORREGIDA:
 * ✅ Compatible 100% con AutoInstaller v2.1
 * ✅ Estructura users completa con todas las columnas
 * ✅ Roles unificados: admin, manager, hr, user, viewer
 * ✅ 18 categorías HERCO 2024
 * ✅ Sin tablas innecesarias
 * ✅ Sin variables indefinidas
 * ✅ Sistema de logging robusto
 * 
 * @package EncuestasHERCO\Install
 * @version 2.1.0
 * @author Sistema HERCO
 */

// Configuración de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

/**
 * Enviar respuesta JSON y terminar ejecución
 */
function sendResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Logging de errores
 */
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/installer.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    @file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Logging de éxitos
 */
function logSuccess($message, $context = []) {
    $logFile = __DIR__ . '/../logs/installer-success.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    @file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

// ============================================================================
// PUNTO DE ENTRADA PRINCIPAL
// ============================================================================

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Método no permitido. Use POST.');
    }
    
    // Obtener paso del instalador
    $step = $_POST['step'] ?? '';
    
    if (empty($step)) {
        sendResponse(false, 'Paso de instalación no especificado');
    }
    
    // Procesar según el paso
    switch ($step) {
        case 'test_database':
            handleDatabaseTest();
            break;
            
        case 'install_system':
        case 'complete_installation':
            handleCompleteInstallation();
            break;
            
        default:
            sendResponse(false, 'Paso de instalación no válido: ' . htmlspecialchars($step));
    }
    
} catch (Exception $e) {
    logError('Error general en instalador', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    sendResponse(false, 'Error interno del instalador: ' . $e->getMessage());
}

// ============================================================================
// MANEJO DE PRUEBA DE CONEXIÓN A BASE DE DATOS
// ============================================================================

/**
 * Probar conexión a base de datos
 */
function handleDatabaseTest() {
    // Validar campos requeridos
    $required = ['db_host', 'db_name', 'db_user'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            sendResponse(false, "El campo {$field} es requerido");
        }
    }
    
    $host = trim($_POST['db_host']);
    $port = (int)($_POST['db_port'] ?? 3306);
    $database = trim($_POST['db_name']);
    $username = trim($_POST['db_user']);
    $password = $_POST['db_pass'] ?? '';
    
    try {
        // Intentar conexión
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        
        // Verificar si la base de datos existe
        $stmt = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($database));
        $dbExists = $stmt->rowCount() > 0;
        
        // Si no existe, intentar crearla
        if (!$dbExists) {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            logSuccess('Base de datos creada', ['database' => $database]);
        }
        
        // Conectar a la base de datos específica
        $pdo->exec("USE `{$database}`");
        
        // Verificar versión de MySQL
        $version = $pdo->query("SELECT VERSION()")->fetchColumn();
        
        // Guardar datos en sesión para el paso 3
        session_start();
        $_SESSION['db_host'] = $host;
        $_SESSION['db_port'] = $port;
        $_SESSION['db_name'] = $database;
        $_SESSION['db_user'] = $username;
        $_SESSION['db_pass'] = $password;
        
        logSuccess('Datos de BD guardados en sesión', [
            'host' => $host,
            'database' => $database
        ]);
        
        sendResponse(true, 'Conexión exitosa a la base de datos', [
            'database_exists' => $dbExists,
            'mysql_version' => $version,
            'connection_info' => [
                'host' => $host,
                'port' => $port,
                'database' => $database
            ]
        ]);
        
    } catch (PDOException $e) {
        logError('Error de conexión a base de datos', [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'error' => $e->getMessage()
        ]);
        
        sendResponse(false, 'Error de conexión: ' . $e->getMessage());
    }
}

// ============================================================================
// INSTALACIÓN COMPLETA DEL SISTEMA
// ============================================================================

/**
 * Completar instalación del sistema
 */
function handleCompleteInstallation() {
    // Validar campos de administrador
    $requiredAdmin = ['admin_name', 'admin_email', 'admin_password'];
    
    foreach ($requiredAdmin as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            sendResponse(false, "El campo {$field} es requerido");
        }
    }
    
    // Validar email
    if (!filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Email de administrador no válido');
    }
    
    // Validar contraseña
    if (strlen($_POST['admin_password']) < 8) {
        sendResponse(false, 'La contraseña debe tener al menos 8 caracteres');
    }
    
    // Obtener datos de BD
    $dbConfig = [
        'host' => trim($_POST['db_host'] ?? 'localhost'),
        'port' => (int)($_POST['db_port'] ?? 3306),
        'database' => trim($_POST['db_name'] ?? ''),
        'username' => trim($_POST['db_user'] ?? ''),
        'password' => $_POST['db_pass'] ?? ''
    ];
    
    // Validar datos mínimos de BD
    if (empty($dbConfig['database']) || empty($dbConfig['username'])) {
        sendResponse(false, 'Faltan datos de conexión a la base de datos');
    }
    
    // Preparar datos de configuración
    $companyName = trim($_POST['company_name'] ?? 'Mi Empresa');
    
    $postData = [
        'company_name' => $companyName,
        'admin_name' => trim($_POST['admin_name']),
        'admin_email' => trim($_POST['admin_email']),
        'admin_password' => $_POST['admin_password']
    ];
    
    try {
        // 1. Conectar a la base de datos
        $pdo = connectDatabase($dbConfig);
        
        // 2. Instalar estructura de base de datos
        installDatabase($pdo);
        logSuccess('Estructura de base de datos creada');
        
        // 3. Insertar datos iniciales
        insertInitialData($pdo, $postData);
        logSuccess('Datos iniciales insertados', [
            'company' => $postData['company_name'],
            'admin' => $postData['admin_email']
        ]);
        
        // 4. Crear archivos de configuración
        createConfigFiles($dbConfig, $postData);
        logSuccess('Archivos de configuración creados');
        
        // 5. Crear directorios necesarios
        createDirectories();
        logSuccess('Directorios del sistema creados');
        
        // Respuesta exitosa
        sendResponse(true, '¡Sistema instalado exitosamente!', [
            'admin_email' => $postData['admin_email'],
            'redirect_url' => '../admin/dashboard',
            'installation_time' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        logError('Error durante instalación', [
            'step' => 'complete_installation',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        sendResponse(false, 'Error durante la instalación: ' . $e->getMessage());
    }
}

// ============================================================================
// FUNCIONES AUXILIARES DE INSTALACIÓN
// ============================================================================

/**
 * Conectar a la base de datos
 */
function connectDatabase($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        return new PDO($dsn, $config['username'], $config['password'], $options);
        
    } catch (PDOException $e) {
        throw new Exception('No se pudo conectar a la base de datos: ' . $e->getMessage());
    }
}

/**
 * Instalar estructura completa de base de datos - VERSIÓN CORREGIDA v2.1
 */
function installDatabase($pdo) {
    $sql = "
    -- ========================================
    -- TABLA DE EMPRESAS
    -- ========================================
    CREATE TABLE IF NOT EXISTS companies (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        logo VARCHAR(255),
        address TEXT,
        phone VARCHAR(50),
        email VARCHAR(255),
        website VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla de empresas';

    -- ========================================
    -- TABLA DE DEPARTAMENTOS
    -- ========================================
    CREATE TABLE IF NOT EXISTS departments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        company_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        manager_id INT,
        parent_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        INDEX idx_company (company_id),
        INDEX idx_parent (parent_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla de departamentos';

    -- ========================================
    -- TABLA DE USUARIOS - VERSIÓN COMPLETA CORREGIDA v2.1
    -- Compatible 100% con AutoInstaller y UserController
    -- ========================================
    CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        company_id INT DEFAULT 1,
        name VARCHAR(255) NOT NULL COMMENT 'Nombre completo del usuario',
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'manager', 'hr', 'user', 'viewer') DEFAULT 'user',
        status ENUM('active', 'inactive', 'pending', 'suspended') DEFAULT 'active',
        department_id INT NULL COMMENT 'ID del departamento (FK a departments)',
        department VARCHAR(100) NULL COMMENT 'Nombre del departamento (texto libre)',
        phone VARCHAR(20) NULL,
        position VARCHAR(100) NULL COMMENT 'Cargo o puesto',
        avatar VARCHAR(255) NULL,
        email_verified_at TIMESTAMP NULL,
        last_login TIMESTAMP NULL,
        failed_login_attempts INT DEFAULT 0,
        locked_until TIMESTAMP NULL,
        password_changed_at TIMESTAMP NULL,
        remember_token VARCHAR(255) NULL,
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        INDEX idx_email (email),
        INDEX idx_company (company_id),
        INDEX idx_role (role),
        INDEX idx_status (status),
        INDEX idx_department_id (department_id),
        INDEX idx_last_login (last_login)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla de usuarios del sistema HERCO v2.1 - Estructura completa';

    -- ========================================
    -- TABLA DE CATEGORÍAS HERCO
    -- ========================================
    CREATE TABLE IF NOT EXISTS question_categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        herco_code VARCHAR(20) NULL COMMENT 'Código HERCO oficial',
        color VARCHAR(7) DEFAULT '#007bff',
        icon VARCHAR(50) DEFAULT 'fas fa-question-circle',
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_active (is_active),
        INDEX idx_sort (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='18 Categorías HERCO 2024';

    -- ========================================
    -- TABLA DE ENCUESTAS
    -- ========================================
    CREATE TABLE IF NOT EXISTS surveys (
        id INT PRIMARY KEY AUTO_INCREMENT,
        company_id INT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        instructions TEXT,
        status ENUM('draft', 'active', 'paused', 'completed', 'archived') DEFAULT 'draft',
        start_date DATE,
        end_date DATE,
        is_anonymous TINYINT(1) DEFAULT 1,
        allow_multiple_responses TINYINT(1) DEFAULT 0,
        show_progress TINYINT(1) DEFAULT 1,
        randomize_questions TINYINT(1) DEFAULT 0,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_status (status),
        INDEX idx_company (company_id),
        INDEX idx_dates (start_date, end_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla de encuestas';

    -- ========================================
    -- TABLA DE PREGUNTAS
    -- ========================================
    CREATE TABLE IF NOT EXISTS questions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        survey_id INT NOT NULL,
        category_id INT,
        question_text TEXT NOT NULL,
        question_type ENUM('likert5', 'likert7', 'text', 'multiple', 'yesno') DEFAULT 'likert5',
        is_required TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES question_categories(id) ON DELETE SET NULL,
        INDEX idx_survey (survey_id),
        INDEX idx_category (category_id),
        INDEX idx_sort (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla de preguntas';

    -- ========================================
    -- TABLA DE PARTICIPANTES
    -- ========================================
    CREATE TABLE IF NOT EXISTS participants (
        id INT PRIMARY KEY AUTO_INCREMENT,
        survey_id INT NOT NULL,
        company_id INT,
        department_id INT,
        name VARCHAR(255),
        email VARCHAR(255),
        employee_id VARCHAR(100),
        phone VARCHAR(50),
        position VARCHAR(100),
        token VARCHAR(255) UNIQUE NOT NULL,
        status ENUM('pending', 'invited', 'started', 'completed', 'expired') DEFAULT 'pending',
        started_at TIMESTAMP NULL,
        completed_at TIMESTAMP NULL,
        invitation_sent_at TIMESTAMP NULL,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_survey (survey_id),
        INDEX idx_token (token),
        INDEX idx_email (email),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla de participantes';

    -- ========================================
    -- TABLA DE RESPUESTAS
    -- ========================================
    CREATE TABLE IF NOT EXISTS responses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        participant_id INT NOT NULL,
        question_id INT NOT NULL,
        answer_value TEXT,
        answer_numeric INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
        INDEX idx_participant (participant_id),
        INDEX idx_question (question_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla de respuestas';

    -- ========================================
    -- TABLA DE LOGS DE ACTIVIDAD
    -- ========================================
    CREATE TABLE IF NOT EXISTS activity_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
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
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla de logs de actividad';
    ";
    
    try {
        $pdo->exec($sql);
        logSuccess('Estructura de base de datos instalada exitosamente');
        
    } catch (PDOException $e) {
        throw new Exception('Error al crear estructura de base de datos: ' . $e->getMessage());
    }
}

/**
 * Insertar datos iniciales del sistema - VERSIÓN CORREGIDA v2.1
 */
function insertInitialData($pdo, $postData) {
    try {
        // 1. Insertar empresa principal
        $stmt = $pdo->prepare("
            INSERT INTO companies (id, name, description) 
            VALUES (1, ?, 'Empresa principal del sistema') 
            ON DUPLICATE KEY UPDATE name = VALUES(name)
        ");
        $stmt->execute([$postData['company_name']]);
        
        // 2. Insertar usuario administrador - ✅ CORREGIDO
        $adminPassword = password_hash($postData['admin_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (
                name, email, password, role, company_id, status,
                email_verified_at, password_changed_at
            ) 
            VALUES (?, ?, ?, 'admin', 1, 'active', NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                name = VALUES(name), 
                password = VALUES(password),
                role = 'admin'
        ");
        $stmt->execute([
            $postData['admin_name'],
            $postData['admin_email'],
            $adminPassword
        ]);
        
        // 3. Insertar 18 categorías HERCO 2024
        $categories = [
            ['Satisfacción Laboral', 'Evaluación general del clima laboral', 'HERCO-01', '#e74c3c', 'fas fa-smile'],
            ['Participación y Autonomía', 'Empoderamiento de los empleados', 'HERCO-02', '#3498db', 'fas fa-users-cog'],
            ['Comunicación y Objetivos', 'Claridad organizacional', 'HERCO-03', '#2ecc71', 'fas fa-comments'],
            ['Equilibrio y Evaluación', 'Work-life balance', 'HERCO-04', '#f39c12', 'fas fa-balance-scale'],
            ['Distribución y Carga de Trabajo', 'Equidad laboral', 'HERCO-05', '#9b59b6', 'fas fa-tasks'],
            ['Reconocimiento y Promoción', 'Desarrollo profesional', 'HERCO-06', '#1abc9c', 'fas fa-trophy'],
            ['Ambiente de Trabajo', 'Condiciones físicas', 'HERCO-07', '#34495e', 'fas fa-building'],
            ['Capacitación', 'Desarrollo de competencias', 'HERCO-08', '#e67e22', 'fas fa-graduation-cap'],
            ['Tecnología y Recursos', 'Herramientas disponibles', 'HERCO-09', '#95a5a6', 'fas fa-laptop'],
            ['Colaboración y Compañerismo', 'Relaciones interpersonales', 'HERCO-10', '#f1c40f', 'fas fa-handshake'],
            ['Normativas y Regulaciones', 'Cumplimiento', 'HERCO-11', '#c0392b', 'fas fa-gavel'],
            ['Compensación y Beneficios', 'Satisfacción salarial', 'HERCO-12', '#27ae60', 'fas fa-money-bill-wave'],
            ['Bienestar y Salud', 'Programas de wellness', 'HERCO-13', '#8e44ad', 'fas fa-heartbeat'],
            ['Seguridad en el Trabajo', 'Prevención de riesgos', 'HERCO-14', '#d35400', 'fas fa-shield-alt'],
            ['Información y Comunicación', 'Flujo informativo', 'HERCO-15', '#2980b9', 'fas fa-info-circle'],
            ['Relaciones con Supervisores', 'Calidad del liderazgo', 'HERCO-16', '#16a085', 'fas fa-user-tie'],
            ['Feedback y Reconocimiento', 'Retroalimentación', 'HERCO-17', '#f39c12', 'fas fa-star'],
            ['Diversidad e Inclusión', 'Ambiente inclusivo', 'HERCO-18', '#e91e63', 'fas fa-hands-helping']
        ];
        
        foreach ($categories as $index => $category) {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO question_categories 
                (name, description, herco_code, color, icon, sort_order) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $category[0],
                $category[1],
                $category[2],
                $category[3],
                $category[4],
                $index + 1
            ]);
        }
        
        logSuccess('Datos iniciales insertados correctamente', [
            'categories' => count($categories),
            'admin_email' => $postData['admin_email']
        ]);
        
    } catch (PDOException $e) {
        throw new Exception('Error al insertar datos iniciales: ' . $e->getMessage());
    }
}

/**
 * Crear archivos de configuración del sistema
 */
function createConfigFiles($dbConfig, $postData) {
    $configDir = dirname(__DIR__) . '/config';
    
    // Crear directorio config si no existe
    if (!is_dir($configDir)) {
        if (!mkdir($configDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de configuración');
        }
    }
    
    // 1. Archivo de configuración de base de datos
    $dbConfigContent = "<?php
/**
 * Configuración de Base de Datos
 * Sistema HERCO v2.1
 * Generado automáticamente: " . date('Y-m-d H:i:s') . "
 */

return [
    'host' => '{$dbConfig['host']}',
    'port' => {$dbConfig['port']},
    'database' => '{$dbConfig['database']}',
    'username' => '{$dbConfig['username']}',
    'password' => '{$dbConfig['password']}',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci\"
    ]
];
";
    
    if (!file_put_contents($configDir . '/database_config.php', $dbConfigContent)) {
        throw new Exception('No se pudo crear el archivo de configuración de base de datos');
    }
    
    // 2. Archivo de bloqueo de instalación
    if (!file_put_contents($configDir . '/installed.lock', date('Y-m-d H:i:s'))) {
        throw new Exception('No se pudo crear el archivo de bloqueo');
    }
    
    // 3. Archivo de información de instalación
    $installInfo = [
        'installation_date' => date('Y-m-d H:i:s'),
        'version' => '2.1.0',
        'herco_version' => '2024',
        'admin_email' => $postData['admin_email'],
        'company_name' => $postData['company_name'],
        'installer_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'php_version' => PHP_VERSION,
        'mysql_version' => 'auto-detected'
    ];
    
    if (!file_put_contents($configDir . '/installation.info', json_encode($installInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        throw new Exception('No se pudo crear el archivo de información de instalación');
    }
    
    logSuccess('Archivos de configuración creados', [
        'database_config' => 'created',
        'installed_lock' => 'created',
        'installation_info' => 'created'
    ]);
}

/**
 * Crear directorios necesarios del sistema
 */
function createDirectories() {
    $baseDir = dirname(__DIR__);
    
    $directories = [
        '/logs',
        '/logs/app',
        '/logs/security',
        '/logs/admin',
        '/uploads',
        '/uploads/companies',
        '/uploads/companies/logos',
        '/uploads/users',
        '/uploads/users/avatars',
        '/uploads/surveys',
        '/uploads/surveys/attachments',
        '/uploads/surveys/exports',
        '/uploads/temp',
        '/backups',
        '/backups/automatic',
        '/backups/automatic/daily',
        '/backups/automatic/weekly',
        '/backups/automatic/monthly',
        '/backups/manual',
        '/cache',
        '/cache/views',
        '/cache/data'
    ];
    
    $created = 0;
    $errors = [];
    
    foreach ($directories as $dir) {
        $fullPath = $baseDir . $dir;
        if (!is_dir($fullPath)) {
            if (@mkdir($fullPath, 0755, true)) {
                $created++;
            } else {
                $errors[] = $dir;
            }
        }
    }
    
    if (!empty($errors)) {
        logError('Algunos directorios no pudieron ser creados', ['errors' => $errors]);
    }
    
    logSuccess('Directorios del sistema creados', [
        'total' => count($directories),
        'created' => $created,
        'errors' => count($errors)
    ]);
}
?>