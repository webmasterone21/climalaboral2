<?php
/**
 * Procesador del Instalador HERCO - VERSIÓN COMPLETA CORREGIDA
 * install/process.php
 * 
 * INCLUYE CREACIÓN AUTOMÁTICA DE TODOS LOS ARCHIVOS DEL SISTEMA
 * CORREGIDO: Todos los errores identificados
 */

// Configuración de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Función para enviar respuesta JSON
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

// Función para logging mejorado
function logStep($step, $message, $context = [], $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/installer.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'step' => $step,
        'message' => $message,
        'context' => $context,
        'memory_usage' => memory_get_usage(true),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    @file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Método no permitido. Se requiere POST');
    }
    
    // Obtener paso del instalador
    $step = $_POST['step'] ?? '';
    
    if (empty($step)) {
        sendResponse(false, 'Paso de instalación no especificado');
    }
    
    logStep('STEP_IDENTIFIED', "Paso identificado: {$step}");
    
    switch ($step) {
        case 'test_database':
            handleDatabaseTest();
            break;
            
        case 'install_system':
        case 'complete_installation':
            handleCompleteInstallation();
            break;
            
        default:
            sendResponse(false, 'Paso de instalación no válido: ' . $step);
    }
    
} catch (Exception $e) {
    logStep('ERROR', 'Error general en instalador', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 'ERROR');
    
    sendResponse(false, 'Error interno del instalador: ' . $e->getMessage());
}

/**
 * Manejar prueba de conexión a base de datos
 */
function handleDatabaseTest() {
    logStep('DB_TEST', 'Iniciando prueba de conexión a base de datos');
    
    // Validar campos requeridos
    $required = ['db_host', 'db_name', 'db_user', 'db_pass'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            sendResponse(false, "El campo {$field} es requerido");
        }
    }
    
    $host = trim($_POST['db_host']);
    $port = (int)($_POST['db_port'] ?? 3306);
    $database = trim($_POST['db_name']);
    $username = trim($_POST['db_user']);
    $password = $_POST['db_pass'];
    
    try {
        // Conectar al servidor MySQL
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]);
        
        // Verificar si la base de datos existe
        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$database]);
        $dbExists = $stmt->fetch();
        
        // Crear base de datos si no existe
        if (!$dbExists) {
            $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $dbCreated = true;
            logStep('DB_TEST', 'Base de datos creada automáticamente');
        } else {
            $dbCreated = false;
        }
        
        // Conectar a la base de datos específica
        $dsn_full = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo_full = new PDO($dsn_full, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        // Probar operaciones básicas
        $pdo_full->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $time = $pdo_full->query("SELECT NOW() as `server_time`")->fetch()['server_time'];
        
        // Guardar configuración en sesión
        if (!session_id()) {
            session_start();
        }
        
        $_SESSION['installer_db_config'] = [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'verified' => true,
            'verified_at' => time()
        ];
        
        logStep('DB_TEST', 'Conexión exitosa y configuración guardada');
        
        $message = 'Conexión a base de datos exitosa';
        if ($dbCreated) {
            $message .= '. Base de datos creada automáticamente';
        }
        
        sendResponse(true, $message, [
            'database_exists' => !$dbCreated,
            'database_created' => $dbCreated,
            'server_time' => $time,
            'mysql_version' => $pdo->query("SELECT VERSION()")->fetchColumn()
        ]);
        
    } catch (PDOException $e) {
        logStep('DB_TEST', 'Error de conexión', ['error' => $e->getMessage()], 'ERROR');
        sendResponse(false, 'Error de conexión a la base de datos: ' . $e->getMessage());
    }
}

/**
 * Manejar instalación completa del sistema
 */
function handleCompleteInstallation() {
    if (!session_id()) {
        session_start();
    }
    
    logStep('INSTALL', 'Iniciando instalación completa del sistema');
    
    // Verificar configuración de BD en sesión
    if (empty($_SESSION['installer_db_config']) || !$_SESSION['installer_db_config']['verified']) {
        sendResponse(false, 'Debe probar la conexión a base de datos primero');
    }
    
    // Mapear campos del formulario
    $field_mapping = [
        'admin_name' => $_POST['admin_name'] ?? '',
        'admin_email' => $_POST['admin_email'] ?? '',
        'admin_password' => $_POST['admin_password'] ?? '',
        'company_name' => $_POST['company_name'] ?? $_POST['app_name'] ?? ''
    ];
    
    // Validar campos requeridos
    $required = ['admin_name', 'admin_email', 'admin_password', 'company_name'];
    $errors = [];
    
    foreach ($required as $field) {
        if (empty($field_mapping[$field])) {
            $errors[] = "El campo {$field} es requerido";
        }
    }
    
    if (!filter_var($field_mapping['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email de administrador no válido';
    }
    
    if (strlen($field_mapping['admin_password']) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres';
    }
    
    if (!empty($errors)) {
        sendResponse(false, 'Errores de validación: ' . implode(', ', $errors));
    }
    
    $dbConfig = $_SESSION['installer_db_config'];
    
    try {
        // Conectar a la base de datos
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        logStep('INSTALL', 'Instalando estructura de base de datos');
        installDatabase($pdo);
        
        logStep('INSTALL', 'Insertando datos iniciales');
        insertInitialData($pdo, $field_mapping);
        
        logStep('INSTALL', 'Creando archivos de configuración');
        createConfigFiles($dbConfig, $field_mapping);
        
        logStep('INSTALL', 'Creando archivos del sistema');
        createSystemFiles($field_mapping);
        
        logStep('INSTALL', 'Creando controladores');
        createControllers();
        
        logStep('INSTALL', 'Creando vistas del sistema');
        createSystemViews();
        
        logStep('INSTALL', 'Creando archivo principal');
        createMainIndex();
        
        // Limpiar sesión
        unset($_SESSION['installer_db_config']);
        
        logStep('INSTALL', 'Instalación completada exitosamente');
        
        sendResponse(true, '¡Sistema HERCO instalado exitosamente!', [
            'admin_username' => $field_mapping['admin_email'],
            'admin_email' => $field_mapping['admin_email'],
            'app_name' => $field_mapping['company_name'],
            'installation_time' => date('Y-m-d H:i:s'),
            'login_url' => '../',
            'dashboard_url' => '../admin/dashboard'
        ]);
        
    } catch (Exception $e) {
        logStep('INSTALL', 'Error durante la instalación', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 'ERROR');
        
        sendResponse(false, 'Error durante la instalación: ' . $e->getMessage());
    }
}

/**
 * Instalar estructura completa de base de datos
 */
function installDatabase($pdo) {
    logStep('DB_STRUCTURE', 'Iniciando instalación de estructura completa');
    
    $tables = [
        // Tabla de empresas
        'companies' => "CREATE TABLE IF NOT EXISTS companies (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            logo VARCHAR(255),
            address TEXT,
            phone VARCHAR(50),
            email VARCHAR(255),
            website VARCHAR(255),
            industry VARCHAR(100),
            size ENUM('small', 'medium', 'large') DEFAULT 'medium',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de usuarios
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            company_id INT DEFAULT 1,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(50),
            avatar VARCHAR(255),
            role ENUM('super_admin', 'admin', 'manager', 'user', 'participant') DEFAULT 'user',
            status ENUM('active', 'inactive', 'pending', 'suspended', 'deleted') DEFAULT 'active',
            email_verified BOOLEAN DEFAULT FALSE,
            email_verification_token VARCHAR(255),
            two_factor_enabled BOOLEAN DEFAULT FALSE,
            two_factor_secret VARCHAR(255),
            position VARCHAR(100),
            department_id INT,
            hire_date DATE,
            timezone VARCHAR(50) DEFAULT 'America/Tegucigalpa',
            language VARCHAR(10) DEFAULT 'es',
            preferences JSON,
            last_login TIMESTAMP NULL,
            last_activity TIMESTAMP NULL,
            login_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de sesiones de usuario
        'user_sessions' => "CREATE TABLE IF NOT EXISTS user_sessions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            session_token VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            remember_me BOOLEAN DEFAULT FALSE,
            expires_at TIMESTAMP NOT NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_session_token (session_token),
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de categorías de preguntas HERCO
        'question_categories' => "CREATE TABLE IF NOT EXISTS question_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#007bff',
            icon VARCHAR(50) DEFAULT 'fas fa-question',
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de tipos de pregunta
        'question_types' => "CREATE TABLE IF NOT EXISTS question_types (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            config JSON,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de encuestas
        'surveys' => "CREATE TABLE IF NOT EXISTS surveys (
            id INT PRIMARY KEY AUTO_INCREMENT,
            company_id INT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status ENUM('draft', 'active', 'paused', 'completed') DEFAULT 'draft',
            start_date DATE,
            end_date DATE,
            is_anonymous TINYINT(1) DEFAULT 1,
            allow_multiple_responses TINYINT(1) DEFAULT 0,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de preguntas
        'questions' => "CREATE TABLE IF NOT EXISTS questions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            survey_id INT,
            category_id INT,
            type_id INT,
            title TEXT NOT NULL,
            description TEXT,
            is_required TINYINT(1) DEFAULT 0,
            sort_order INT DEFAULT 0,
            config JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES question_categories(id) ON DELETE SET NULL,
            FOREIGN KEY (type_id) REFERENCES question_types(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de participantes
        'participants' => "CREATE TABLE IF NOT EXISTS participants (
            id INT PRIMARY KEY AUTO_INCREMENT,
            survey_id INT,
            name VARCHAR(255),
            email VARCHAR(255),
            department VARCHAR(255),
            token VARCHAR(255) UNIQUE,
            status ENUM('invited', 'started', 'completed') DEFAULT 'invited',
            invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            started_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de respuestas
        'responses' => "CREATE TABLE IF NOT EXISTS responses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            survey_id INT,
            participant_id INT,
            question_id INT,
            answer_text TEXT,
            answer_numeric DECIMAL(10,2),
            answer_json JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
            FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Tabla de configuración del sistema
        'system_config' => "CREATE TABLE IF NOT EXISTS system_config (
            config_key VARCHAR(100) PRIMARY KEY,
            config_value TEXT,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            logStep('DB_STRUCTURE', "Tabla creada: {$tableName}");
        } catch (PDOException $e) {
            logStep('DB_STRUCTURE', "Error creando tabla {$tableName}", ['error' => $e->getMessage()], 'ERROR');
            throw new Exception("Error creando tabla {$tableName}: " . $e->getMessage());
        }
    }
    
    logStep('DB_STRUCTURE', 'Estructura completa de BD creada', ['tables_created' => count($tables)]);
}

/**
 * Insertar datos iniciales - CORREGIDO
 */
function insertInitialData($pdo, $postData) {
    logStep('DB_DATA', 'Insertando datos iniciales');
    
    try {
        // 1. Insertar empresa - CORREGIDO: usar INSERT IGNORE
        $stmt = $pdo->prepare("INSERT IGNORE INTO companies (id, name, description, industry, size) VALUES (1, ?, 'Empresa principal del sistema', ?, 'medium')");
        $stmt->execute([$postData['company_name'], $_POST['industry'] ?? 'tecnologia']);
        
        // 2. Insertar usuario administrador - CORREGIDO: columnas correctas
        $adminPassword = password_hash($postData['admin_password'], PASSWORD_DEFAULT);
        
        $nameParts = explode(' ', $postData['admin_name'], 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, first_name, last_name, role, company_id, email_verified) VALUES (?, ?, ?, ?, ?, 'admin', 1, 1)");
        $stmt->execute([
            strtolower(str_replace(' ', '', $postData['admin_name'])),
            $postData['admin_email'], 
            $adminPassword, 
            $firstName,
            $lastName
        ]);
        
        logStep('DB_DATA', 'Usuario administrador creado', ['email' => $postData['admin_email']]);
        
        // 3. Insertar 18 categorías HERCO 2024
        $categories = [
            ['Satisfacción Laboral', 'Evaluación general del nivel de satisfacción del empleado', '#e74c3c', 'fas fa-heart'],
            ['Participación y Autonomía', 'Nivel de empoderamiento y toma de decisiones', '#3498db', 'fas fa-users'],
            ['Comunicación y Objetivos', 'Claridad en la comunicación organizacional', '#2ecc71', 'fas fa-comments'],
            ['Equilibrio y Evaluación', 'Balance vida-trabajo y evaluación del desempeño', '#f39c12', 'fas fa-balance-scale'],
            ['Distribución y Carga de Trabajo', 'Equidad en la distribución de tareas', '#9b59b6', 'fas fa-tasks'],
            ['Reconocimiento y Promoción', 'Sistemas de reconocimiento y desarrollo profesional', '#1abc9c', 'fas fa-award'],
            ['Ambiente de Trabajo', 'Condiciones físicas y ambientales', '#34495e', 'fas fa-building'],
            ['Capacitación', 'Programas de desarrollo y capacitación', '#e67e22', 'fas fa-graduation-cap'],
            ['Tecnología y Recursos', 'Herramientas y recursos disponibles', '#95a5a6', 'fas fa-laptop'],
            ['Colaboración y Compañerismo', 'Relaciones interpersonales', '#f1c40f', 'fas fa-handshake'],
            ['Normativas y Regulaciones', 'Cumplimiento de normas y políticas', '#c0392b', 'fas fa-gavel'],
            ['Compensación y Beneficios', 'Satisfacción con la remuneración', '#27ae60', 'fas fa-money-bill-wave'],
            ['Bienestar y Salud', 'Programas de bienestar y salud ocupacional', '#8e44ad', 'fas fa-heartbeat'],
            ['Seguridad en el Trabajo', 'Medidas de prevención de riesgos', '#d35400', 'fas fa-shield-alt'],
            ['Información y Comunicación', 'Flujo de información interna', '#2980b9', 'fas fa-info-circle'],
            ['Relaciones con Supervisores', 'Calidad del liderazgo y supervisión', '#16a085', 'fas fa-user-tie'],
            ['Feedback y Reconocimiento', 'Sistemas de retroalimentación', '#f39c12', 'fas fa-comment-dots'],
            ['Diversidad e Inclusión', 'Ambiente inclusivo y diverso', '#e91e63', 'fas fa-diversity']
        ];
        
        foreach ($categories as $index => $category) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO question_categories (name, description, color, icon, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$category[0], $category[1], $category[2], $category[3], $index + 1]);
        }
        
        // 4. Insertar tipos de pregunta
        $questionTypes = [
            ['Escala Likert 1-5', 'likert_5', 'Escala de acuerdo/desacuerdo de 5 puntos', '{"min":1,"max":5,"labels":["Muy en desacuerdo","En desacuerdo","Neutral","De acuerdo","Muy de acuerdo"]}'],
            ['Escala 1-3', 'scale_3', 'Escala numérica de 3 puntos', '{"min":1,"max":3,"labels":["Bajo","Medio","Alto"]}'],
            ['Escala 1-7', 'scale_7', 'Escala numérica de 7 puntos', '{"min":1,"max":7}'],
            ['Texto corto', 'text_short', 'Respuesta de texto corto', '{"maxLength":255}'],
            ['Texto largo', 'text_long', 'Respuesta de texto largo', '{"maxLength":2000}'],
            ['Selección múltiple', 'multiple_choice', 'Seleccionar una opción', '{"options":[]}'],
            ['Casillas de verificación', 'checkbox', 'Seleccionar múltiples opciones', '{"options":[],"maxSelections":null}'],
            ['NPS', 'nps', 'Net Promoter Score (0-10)', '{"min":0,"max":10}']
        ];
        
        foreach ($questionTypes as $type) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO question_types (name, code, description, config) VALUES (?, ?, ?, ?)");
            $stmt->execute($type);
        }
        
        // 5. Configuración del sistema
        $configs = [
            ['app_name', $postData['company_name'], 'Nombre de la aplicación'],
            ['app_version', '2.0.0', 'Versión del sistema'],
            ['installation_date', date('Y-m-d H:i:s'), 'Fecha de instalación'],
            ['default_language', 'es', 'Idioma por defecto'],
            ['timezone', 'America/Tegucigalpa', 'Zona horaria'],
            ['admin_email', $postData['admin_email'], 'Email del administrador'],
            ['herco_version', '2024', 'Versión HERCO implementada']
        ];
        
        foreach ($configs as $config) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO system_config (config_key, config_value, description) VALUES (?, ?, ?)");
            $stmt->execute($config);
        }
        
        logStep('DB_DATA', 'Datos iniciales insertados completamente');
        
    } catch (PDOException $e) {
        logStep('DB_DATA', 'Error insertando datos', ['error' => $e->getMessage()], 'ERROR');
        throw new Exception("Error insertando datos iniciales: " . $e->getMessage());
    }
}

/**
 * Crear archivos de configuración
 */
function createConfigFiles($dbConfig, $postData) {
    logStep('CONFIG_FILES', 'Creando archivos de configuración');
    
    $configDir = dirname(__DIR__) . '/config';
    
    if (!is_dir($configDir)) {
        if (!mkdir($configDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de configuración');
        }
    }
    
    // 1. Archivo de configuración de base de datos
    $dbConfigContent = "<?php\n/**\n * Configuración de Base de Datos\n * Generado automáticamente por el instalador HERCO\n * Fecha: " . date('Y-m-d H:i:s') . "\n */\n\nreturn [\n    'host' => '{$dbConfig['host']}',\n    'port' => {$dbConfig['port']},\n    'database' => '{$dbConfig['database']}',\n    'username' => '{$dbConfig['username']}',\n    'password' => '{$dbConfig['password']}',\n    'charset' => 'utf8mb4',\n    'collation' => 'utf8mb4_unicode_ci',\n    'options' => [\n        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n        PDO::ATTR_EMULATE_PREPARES => false,\n        PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci\"\n    ]\n];\n";
    
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
        'version' => '2.0.0',
        'admin_email' => $postData['admin_email'],
        'company_name' => $postData['company_name'],
        'installer_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    if (!file_put_contents($configDir . '/installation.info', json_encode($installInfo, JSON_PRETTY_PRINT))) {
        throw new Exception('No se pudo crear el archivo de información de instalación');
    }
    
    logStep('CONFIG_FILES', 'Archivos de configuración creados correctamente');
}

/**
 * Crear archivos del sistema principal
 */
function createSystemFiles($postData) {
    logStep('SYSTEM_FILES', 'Creando archivos de configuración del sistema');
    
    $configDir = dirname(__DIR__) . '/config';
    
    // Crear config/app.php
    $appConfigContent = '<?php
/**
 * Configuración Principal de la Aplicación
 * Sistema HERCO v2.0
 */

return [
    // Información de la aplicación
    "app_name" => "' . $postData['company_name'] . '",
    "app_version" => "2.0.0",
    "app_environment" => "production",
    "app_debug" => false,
    "app_url" => "' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '",
    
    // Configuración de sesiones
    "session_lifetime" => 1440,
    "session_name" => "HERCO_SESSION",
    "session_secure" => ' . (isset($_SERVER['HTTPS']) ? 'true' : 'false') . ',
    
    // Configuración de seguridad
    "security_key" => "' . bin2hex(random_bytes(32)) . '",
    "csrf_token_name" => "_token",
    "password_min_length" => 8,
    
    // Configuración de archivos
    "upload_max_size" => 5242880,
    "allowed_file_types" => ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "xls", "xlsx"],
    
    // Zona horaria
    "timezone" => "America/Tegucigalpa",
    
    // Idioma por defecto
    "default_language" => "es",
    
    // Configuración de reportes HERCO
    "herco_categories_enabled" => true,
    "herco_version" => "2024"
];
';
    
    if (!file_put_contents($configDir . '/app.php', $appConfigContent)) {
        throw new Exception('No se pudo crear config/app.php');
    }
    
    logStep('SYSTEM_FILES', 'Archivo config/app.php creado');
}

/**
 * Crear controladores del sistema
 */
function createControllers() {
    logStep('CONTROLLERS', 'Creando controladores del sistema');
    
    $controllersDir = dirname(__DIR__) . '/controllers';
    $coreDir = dirname(__DIR__) . '/core';
    
    if (!is_dir($controllersDir)) {
        mkdir($controllersDir, 0755, true);
    }
    
    if (!is_dir($coreDir)) {
        mkdir($coreDir, 0755, true);
    }
    
    // Controlador base
    $controllerBaseContent = '<?php
/**
 * Controlador Base
 * Sistema HERCO v2.0
 */

class Controller
{
    protected $db;
    protected $config;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = $this->loadConfig();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    protected function render($view, $data = [])
    {
        extract($data);
        
        $app_name = $this->config["app_name"] ?? "Sistema HERCO";
        $flash_messages = $this->getFlashMessages();
        
        $layout = $data["layout"] ?? "app";
        
        $viewFile = __DIR__ . "/../views/{$view}.php";
        $layoutFile = __DIR__ . "/../views/layouts/{$layout}.php";
        
        if (!file_exists($viewFile)) {
            throw new Exception("Vista no encontrada: {$view}");
        }
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header("Content-Type: application/json");
        echo json_encode($data);
    }
    
    protected function redirect($url, $statusCode = 302)
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
    
    private function loadConfig()
    {
        $configFile = __DIR__ . "/../config/app.php";
        if (file_exists($configFile)) {
            return require $configFile;
        }
        return [];
    }
    
    private function getFlashMessages()
    {
        $messages = $_SESSION["flash_messages"] ?? [];
        unset($_SESSION["flash_messages"]);
        return $messages;
    }
}
';
    
    file_put_contents($coreDir . '/Controller.php', $controllerBaseContent);
    logStep('CONTROLLERS', 'Controlador base creado');
    
    // AuthController
    $authControllerContent = '<?php
/**
 * Controlador de Autenticación
 * Sistema HERCO v2.0
 */

class AuthController extends Controller
{
    public function showLogin()
    {
        if (isset($_SESSION["user_id"])) {
            header("Location: /admin/dashboard");
            exit;
        }
        
        $this->render("auth/login", [
            "page_title" => "Iniciar Sesión",
            "csrf_token" => $this->generateCSRFToken(),
            "layout" => "auth"
        ]);
    }
    
    public function login()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: /login");
            exit;
        }
        
        $email = $_POST["email"] ?? "";
        $password = $_POST["password"] ?? "";
        
        if (empty($email) || empty($password)) {
            $this->setFlashMessage("error", "Email y contraseña son requeridos");
            header("Location: /login");
            exit;
        }
        
        try {
            $db = Database::getInstance();
            
            $user = $db->fetch(
                "SELECT * FROM users WHERE email = ? AND status = ?",
                [$email, "active"]
            );
            
            if (!$user || !password_verify($password, $user["password"])) {
                $this->setFlashMessage("error", "Credenciales incorrectas");
                header("Location: /login");
                exit;
            }
            
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_role"] = $user["role"];
            $_SESSION["user_name"] = $user["first_name"] . " " . $user["last_name"];
            
            $db->update("users", 
                ["last_login" => date("Y-m-d H:i:s"), "login_count" => ($user["login_count"] + 1)],
                "id = ?",
                [$user["id"]]
            );
            
            header("Location: /admin/dashboard");
            exit;
            
        } catch (Exception $e) {
            $this->setFlashMessage("error", "Error del sistema. Intente nuevamente.");
            header("Location: /login");
            exit;
        }
    }
    
    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        
        header("Location: /login");
        exit;
    }
    
    private function generateCSRFToken()
    {
        if (!isset($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }
        return $_SESSION["csrf_token"];
    }
    
    private function setFlashMessage($type, $message)
    {
        if (!isset($_SESSION["flash_messages"])) {
            $_SESSION["flash_messages"] = [];
        }
        $_SESSION["flash_messages"][] = ["type" => $type, "message" => $message];
    }
}
';
    
    file_put_contents($controllersDir . '/AuthController.php', $authControllerContent);
    logStep('CONTROLLERS', 'AuthController creado');
    
    // AdminController
    $adminControllerContent = '<?php
/**
 * Controlador Administrativo
 * Sistema HERCO v2.0
 */

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole("admin");
    }
    
    public function dashboard()
    {
        try {
            $db = Database::getInstance();
            
            $stats = [
                "total_users" => $db->count("users", "status = ?", ["active"]),
                "total_surveys" => $db->count("surveys"),
                "total_responses" => $db->count("responses"),
                "total_companies" => $db->count("companies", "status = ?", ["active"])
            ];
            
            $recentSurveys = $db->fetchAll(
                "SELECT s.*, u.first_name, u.last_name, c.name as company_name 
                 FROM surveys s 
                 LEFT JOIN users u ON s.created_by = u.id 
                 LEFT JOIN companies c ON s.company_id = c.id 
                 ORDER BY s.created_at DESC 
                 LIMIT 5"
            );
            
            $this->render("admin/dashboard", [
                "page_title" => "Dashboard Administrativo",
                "stats" => $stats,
                "recent_surveys" => $recentSurveys
            ]);
            
        } catch (Exception $e) {
            $this->render("admin/dashboard", [
                "page_title" => "Dashboard Administrativo",
                "stats" => ["total_users" => 0, "total_surveys" => 0, "total_responses" => 0, "total_companies" => 0],
                "recent_surveys" => []
            ]);
        }
    }
    
    private function requireAuth()
    {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /login");
            exit;
        }
    }
    
    private function requireRole($role)
    {
        if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== $role) {
            header("Location: /login");
            exit;
        }
    }
}
';
    
    file_put_contents($controllersDir . '/AdminController.php', $adminControllerContent);
    logStep('CONTROLLERS', 'AdminController creado');
}

/**
 * Crear vistas del sistema
 */
function createSystemViews() {
    logStep('VIEWS', 'Creando vistas del sistema');
    
    $viewsDir = dirname(__DIR__) . '/views';
    $authDir = $viewsDir . '/auth';
    $adminDir = $viewsDir . '/admin';
    $layoutsDir = $viewsDir . '/layouts';
    
    // Crear directorios
    if (!is_dir($authDir)) mkdir($authDir, 0755, true);
    if (!is_dir($adminDir)) mkdir($adminDir, 0755, true);
    if (!is_dir($layoutsDir)) mkdir($layoutsDir, 0755, true);
    
    // Layout de autenticación
    $authLayoutContent = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? "Login" ?> - <?= $app_name ?? "Sistema HERCO" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .vh-100 { min-height: 100vh; }
    </style>
</head>
<body class="bg-light">
    <?= $content ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
    
    file_put_contents($layoutsDir . '/auth.php', $authLayoutContent);
    
    // Vista de login
    $loginContent = '<?php
/**
 * Vista de Login
 */
?>
<div class="container-fluid vh-100">
    <div class="row h-100">
        <div class="col-md-6 d-flex align-items-center justify-content-center bg-primary">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bold">Sistema HERCO</h1>
                <p class="lead">Encuestas de Clima Laboral</p>
                <p>Versión 2.0.0 - Profesional</p>
            </div>
        </div>
        <div class="col-md-6 d-flex align-items-center justify-content-center">
            <div class="w-100" style="max-width: 400px;">
                <div class="text-center mb-4">
                    <h2>Iniciar Sesión</h2>
                    <p class="text-muted">Accede a tu cuenta</p>
                </div>
                
                <?php if (!empty($flash_messages)): ?>
                    <?php foreach ($flash_messages as $message): ?>
                        <div class="alert alert-<?= $message["type"] === "error" ? "danger" : $message["type"] ?> alert-dismissible fade show">
                            <?= htmlspecialchars($message["message"]) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <form method="POST" action="/login">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</div>';
    
    file_put_contents($authDir . '/login.php', $loginContent);
    
    // Layout principal
    $appLayoutContent = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? "Dashboard" ?> - <?= $app_name ?? "Sistema HERCO" ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: #343a40; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link:hover { color: #fff; }
        .sidebar .nav-link.active { color: #fff; background: #495057; }
        .main-content { margin-left: 250px; }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Sistema HERCO</h4>
                        <small class="text-muted">v2.0.0</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="pt-3">
                    <?php if (!empty($flash_messages)): ?>
                        <?php foreach ($flash_messages as $message): ?>
                            <div class="alert alert-<?= $message["type"] === "error" ? "danger" : $message["type"] ?> alert-dismissible fade show">
                                <?= htmlspecialchars($message["message"]) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?= $content ?>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
    
    file_put_contents($layoutsDir . '/app.php', $appLayoutContent);
    
    // Dashboard
    $dashboardContent = '<?php
/**
 * Dashboard Administrativo
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Dashboard Administrativo</h1>
                <div>
                    <span class="text-muted">Bienvenido, <?= htmlspecialchars($_SESSION["user_name"] ?? "Usuario") ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Usuarios</h5>
                            <h2 class="mb-0"><?= number_format($stats["total_users"]) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Encuestas</h5>
                            <h2 class="mb-0"><?= number_format($stats["total_surveys"]) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-poll fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Respuestas</h5>
                            <h2 class="mb-0"><?= number_format($stats["total_responses"]) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Empresas</h5>
                            <h2 class="mb-0"><?= number_format($stats["total_companies"]) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sistema HERCO Instalado Correctamente</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h4 class="alert-heading">¡Instalación Exitosa!</h4>
                        <p>El sistema de encuestas de clima laboral HERCO v2.0 se ha instalado correctamente.</p>
                        <hr>
                        <p class="mb-0">Puede comenzar a crear encuestas y gestionar su organización.</p>
                    </div>
                    
                    <?php if (empty($recent_surveys)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-poll fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay encuestas creadas</h5>
                            <p class="text-muted">Comience creando su primera encuesta de clima laboral</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
';
    
    file_put_contents($adminDir . '/dashboard.php', $dashboardContent);
    
    logStep('VIEWS', 'Vistas del sistema creadas');
}

/**
 * Crear index.php principal
 */
function createMainIndex() {
    logStep('INDEX', 'Creando archivo principal index.php');
    
    $indexContent = '<?php
/**
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * Punto de entrada principal - VERSIÓN CORREGIDA
 */

// Configuración básica
error_reporting(E_ALL);
ini_set("display_errors", 0);
ini_set("log_errors", 1);

date_default_timezone_set("America/Tegucigalpa");

// Configuración de sesión
ini_set("session.cookie_httponly", 1);
ini_set("session.cookie_secure", isset($_SERVER["HTTPS"]));
ini_set("session.use_strict_mode", 1);

// Verificar instalación
$installedLockFile = __DIR__ . "/config/installed.lock";

if (!file_exists($installedLockFile)) {
    header("Location: /install/");
    exit("Redirigiendo al instalador...");
}

// Cargar configuraciones
$dbConfig = require_once __DIR__ . "/config/database_config.php";
$appConfig = require_once __DIR__ . "/config/app.php";

// Cargar clases del núcleo
require_once __DIR__ . "/core/Database.php";
require_once __DIR__ . "/core/Router.php";
require_once __DIR__ . "/core/Controller.php";

// Autocarga para controladores
spl_autoload_register(function ($className) {
    $directories = ["controllers", "models", "core"];
    
    foreach ($directories as $dir) {
        $file = __DIR__ . "/{$dir}/{$className}.php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Inicializar base de datos
$database = Database::getInstance();

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar enrutador
$router = new Router();

// DEFINIR RUTAS DEL SISTEMA
$router->add("GET", "/", function() {
    if (isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {
        header("Location: /admin/dashboard");
    } else {
        header("Location: /login");
    }
    exit;
});

// Rutas de autenticación
$router->add("GET", "/login", "AuthController", "showLogin");
$router->add("POST", "/login", "AuthController", "login");
$router->add("GET", "/logout", "AuthController", "logout");

// Rutas administrativas
$router->add("GET", "/admin", function() {
    header("Location: /admin/dashboard");
    exit;
});

$router->add("GET", "/admin/dashboard", "AdminController", "dashboard");

// Procesar la solicitud - CORREGIDO
$router->dispatch($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);
?>';
    
    $indexFile = dirname(__DIR__) . '/index.php';
    
    // Crear backup si existe
    if (file_exists($indexFile)) {
        copy($indexFile, dirname(__DIR__) . '/index_backup.php');
    }
    
    if (!file_put_contents($indexFile, $indexContent)) {
        throw new Exception('No se pudo crear index.php');
    }
    
    logStep('INDEX', 'Archivo index.php creado correctamente');
}
?>