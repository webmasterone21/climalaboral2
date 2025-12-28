<?php
/**
 * AutoInstaller - Sistema HERCO v2.1
 * Instalador AutomÃ¡tico - VERSIÃ“N COMPLETAMENTE CORREGIDA
 * 
 * CORRECCIONES v2.1:
 * âœ… Tabla users con TODAS las columnas necesarias (department, department_id, etc.)
 * âœ… Estructura alineada 100% con UserController
 * âœ… 18 categorÃ­as HERCO 2024 preconfiguradas
 * âœ… Validaciones mejoradas
 * âœ… Sin datos demo, sistema limpio para producciÃ³n
 * 
 * @package EncuestasHERCO\Install
 * @version 2.1.0
 * @author Sistema HERCO
 */

class AutoInstaller
{
    private $db;
    private $errors = [];
    private $config = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Verificar requisitos del sistema
     */
    public function checkRequirements()
    {
        $requirements = [
            'php_version' => [
                'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
                'required' => '7.4.0',
                'current' => PHP_VERSION
            ],
            'pdo' => [
                'status' => extension_loaded('pdo'),
                'name' => 'PDO'
            ],
            'pdo_mysql' => [
                'status' => extension_loaded('pdo_mysql'),
                'name' => 'PDO MySQL'
            ],
            'mbstring' => [
                'status' => extension_loaded('mbstring'),
                'name' => 'Mbstring'
            ],
            'json' => [
                'status' => extension_loaded('json'),
                'name' => 'JSON'
            ],
            'session' => [
                'status' => extension_loaded('session'),
                'name' => 'Session'
            ]
        ];
        
        // Verificar permisos de escritura
        $directories = [
            'config' => __DIR__ . '/../config',
            'logs' => __DIR__ . '/../logs',
            'uploads' => __DIR__ . '/../uploads',
            'backups' => __DIR__ . '/../backups'
        ];
        
        foreach ($directories as $name => $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            $requirements['writable_' . $name] = [
                'status' => is_writable($path),
                'name' => 'Escritura en ' . $name,
                'path' => $path
            ];
        }
        
        return $requirements;
    }
    
    /**
     * Probar conexiÃ³n a base de datos
     */
    public function testDatabaseConnection($host, $username, $password, $database, $port = 3306)
    {
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Verificar si la base de datos existe
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$database}'");
            $dbExists = $stmt->rowCount() > 0;
            
            if (!$dbExists) {
                // Crear la base de datos
                $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            
            // Conectar a la base de datos especÃ­fica
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $this->db = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            return [
                'success' => true,
                'message' => 'ConexiÃ³n exitosa',
                'database_created' => !$dbExists
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de conexiÃ³n: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear estructura de base de datos - VERSIÃ“N TOTALMENTE CORREGIDA v2.1
     */
    public function createDatabaseStructure()
    {
        try {
            // ========================================
            // 1. TABLA DE EMPRESAS
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS companies (
                    id INT AUTO_INCREMENT PRIMARY KEY,
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
                COMMENT='Tabla de empresas del sistema HERCO v2.1'
            ");
            
            // ========================================
            // 2. TABLA DE DEPARTAMENTOS
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS departments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    company_id INT NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    manager_id INT,
                    parent_id INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                    INDEX idx_company (company_id),
                    INDEX idx_parent (parent_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                COMMENT='Tabla de departamentos'
            ");
            
            // ========================================
            // 3. TABLA DE USUARIOS - âœ… ESTRUCTURA COMPLETA CORREGIDA
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
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
                    updated_by INT NULL COMMENT 'Usuario que realizó la última actualización',
                    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
                    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
                    INDEX idx_company (company_id),
                    INDEX idx_email (email),
                    INDEX idx_role (role),
                    INDEX idx_status (status),
                    INDEX idx_department_id (department_id),
                    INDEX idx_last_login (last_login)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                COMMENT='Tabla de usuarios del sistema HERCO v2.1 - ESTRUCTURA COMPLETA'
            ");
            
            // ========================================
            // 4. TABLA DE CATEGORÃAS HERCO
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS question_categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(200) NOT NULL,
                    description TEXT,
                    herco_code VARCHAR(20) NULL COMMENT 'CÃ³digo HERCO oficial',
                    color VARCHAR(7) DEFAULT '#007bff',
                    icon VARCHAR(50) DEFAULT 'fas fa-question-circle',
                    sort_order INT DEFAULT 0,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_active (is_active),
                    INDEX idx_sort (sort_order)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                COMMENT='18 CategorÃ­as HERCO 2024'
            ");
            
            // ========================================
            // 5. TABLA DE ENCUESTAS
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS surveys (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    company_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    instructions TEXT,
                    start_date DATE,
                    end_date DATE,
                    status ENUM('draft', 'active', 'closed', 'archived') DEFAULT 'draft',
                    is_anonymous TINYINT(1) DEFAULT 1,
                    allow_multiple_responses TINYINT(1) DEFAULT 0,
                    show_progress TINYINT(1) DEFAULT 1,
                    randomize_questions TINYINT(1) DEFAULT 0,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_company (company_id),
                    INDEX idx_created_by (created_by),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                COMMENT='Tabla de encuestas'
            ");
            
            // ========================================
            // 6. TABLA DE PREGUNTAS
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS questions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    survey_id INT NOT NULL,
                    category_id INT,
                    question_text TEXT NOT NULL,
                    question_type ENUM('likert5', 'likert7', 'text', 'multiple', 'yesno') DEFAULT 'likert5',
                    is_required BOOLEAN DEFAULT true,
                    sort_order INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES question_categories(id) ON DELETE SET NULL,
                    INDEX idx_survey (survey_id),
                    INDEX idx_category (category_id),
                    INDEX idx_sort (sort_order)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                COMMENT='Tabla de preguntas'
            ");
            
            // ========================================
            // 7. TABLA DE PARTICIPANTES
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS participants (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    survey_id INT NOT NULL,
                    company_id INT DEFAULT 1,
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
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
                    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
                    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_survey (survey_id),
                    INDEX idx_company (company_id),
                    INDEX idx_created_by (created_by),
                    INDEX idx_token (token),
                    INDEX idx_status (status),
                    INDEX idx_email (email)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                COMMENT='Tabla de participantes'
            ");
            
            // ========================================
            // 8. TABLA DE RESPUESTAS
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS responses (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    participant_id INT NOT NULL,
                    question_id INT NOT NULL,
                    answer_value TEXT,
                    answer_numeric INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
                    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
                    INDEX idx_participant (participant_id),
                    INDEX idx_question (question_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                COMMENT='Tabla de respuestas'
            ");
            
            // ========================================
            // 9. TABLA DE LOGS
            // ========================================
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS activity_logs (
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
                    INDEX idx_created (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                COMMENT='Tabla de logs de actividad'
            ");
            
            return ['success' => true, 'message' => 'Estructura creada correctamente'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al crear estructura: ' . $e->getMessage()];
        }
    }
    
    /**
     * Insertar datos iniciales - âœ… VERSIÃ“N CORREGIDA
     */
    public function insertInitialData($adminUsername, $adminEmail, $adminPassword)
    {
        try {
            // ========================================
            // 1. Insertar empresa por defecto
            // ========================================
            $this->db->exec("
                INSERT INTO companies (id, name, description) 
                VALUES (1, 'Mi Empresa', 'Empresa principal del sistema') 
                ON DUPLICATE KEY UPDATE id = 1
            ");
            
            // ========================================
            // 2. Insertar 18 categorÃ­as HERCO 2024
            // ========================================
            $categories = [
                ['SatisfacciÃ³n Laboral', 'EvaluaciÃ³n general del clima laboral', 'HERCO-01'],
                ['ParticipaciÃ³n y AutonomÃ­a', 'Empoderamiento de los empleados', 'HERCO-02'],
                ['ComunicaciÃ³n y Objetivos', 'Claridad organizacional', 'HERCO-03'],
                ['Equilibrio y EvaluaciÃ³n', 'Work-life balance', 'HERCO-04'],
                ['DistribuciÃ³n y Carga de Trabajo', 'Equidad laboral', 'HERCO-05'],
                ['Reconocimiento y PromociÃ³n', 'Desarrollo profesional', 'HERCO-06'],
                ['Ambiente de Trabajo', 'Condiciones fÃ­sicas', 'HERCO-07'],
                ['CapacitaciÃ³n', 'Desarrollo de competencias', 'HERCO-08'],
                ['TecnologÃ­a y Recursos', 'Herramientas disponibles', 'HERCO-09'],
                ['ColaboraciÃ³n y CompaÃ±erismo', 'Relaciones interpersonales', 'HERCO-10'],
                ['Normativas y Regulaciones', 'Cumplimiento', 'HERCO-11'],
                ['CompensaciÃ³n y Beneficios', 'SatisfacciÃ³n salarial', 'HERCO-12'],
                ['Bienestar y Salud', 'Programas de wellness', 'HERCO-13'],
                ['Seguridad en el Trabajo', 'PrevenciÃ³n de riesgos', 'HERCO-14'],
                ['InformaciÃ³n y ComunicaciÃ³n', 'Flujo informativo', 'HERCO-15'],
                ['Relaciones con Supervisores', 'Calidad del liderazgo', 'HERCO-16'],
                ['Feedback y Reconocimiento', 'RetroalimentaciÃ³n', 'HERCO-17'],
                ['Diversidad e InclusiÃ³n', 'Ambiente inclusivo', 'HERCO-18']
            ];
            
            $stmt = $this->db->prepare("
                INSERT INTO question_categories (name, description, herco_code, sort_order) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($categories as $index => $category) {
                $stmt->execute([
                    $category[0],
                    $category[1],
                    $category[2],
                    $index + 1
                ]);
            }
            
            // ========================================
            // 3. Insertar usuario administrador - âœ… CORREGIDO
            // ========================================
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    name, email, password, role, status, company_id,
                    email_verified_at, password_changed_at
                ) 
                VALUES (?, ?, ?, 'admin', 'active', 1, NOW(), NOW())
            ");
            
            $adminName = 'Administrador Sistema';
            
            $stmt->execute([
                $adminName,
                $adminEmail,
                $hashedPassword
            ]);
            
            // Guardar credenciales en sesiÃ³n
            $_SESSION['install_username'] = $adminUsername;
            $_SESSION['install_email'] = $adminEmail;
            
            return ['success' => true, 'message' => 'Datos iniciales insertados'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al insertar datos: ' . $e->getMessage()];
        }
    }
    
    /**
     * Crear archivos de configuraciÃ³n
     */
    public function createConfigFiles($dbConfig, $siteUrl = '')
    {
        try {
            // Crear database_config.php
            $dbConfigContent = "<?php
/**
 * ConfiguraciÃ³n de Base de Datos
 * Generado automÃ¡ticamente por el instalador
 * Sistema HERCO v2.1
 */

return [
    'host' => '{$dbConfig['host']}',
    'port' => {$dbConfig['port']},
    'database' => '{$dbConfig['database']}',
    'username' => '{$dbConfig['username']}',
    'password' => '{$dbConfig['password']}',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];
";
            
            file_put_contents(
                __DIR__ . '/../config/database_config.php',
                $dbConfigContent
            );
            
            // Crear app.php si no existe
            if (!file_exists(__DIR__ . '/../config/app.php')) {
                $appConfigContent = "<?php
/**
 * ConfiguraciÃ³n de AplicaciÃ³n
 * Sistema HERCO v2.1
 */

return [
    'app_name' => 'Sistema HERCO v2.1',
    'debug' => true,
    'timezone' => 'America/Tegucigalpa',
    'base_url' => '{$siteUrl}',
    'session_lifetime' => 7200, // 2 horas
];
";
                
                file_put_contents(
                    __DIR__ . '/../config/app.php',
                    $appConfigContent
                );
            }
            
            // Crear archivo de instalaciÃ³n completada
            file_put_contents(
                __DIR__ . '/../config/installed.lock',
                date('Y-m-d H:i:s')
            );
            
            // Crear directorios necesarios
            $directories = [
                __DIR__ . '/../logs/app',
                __DIR__ . '/../logs/security',
                __DIR__ . '/../uploads/companies',
                __DIR__ . '/../uploads/users',
                __DIR__ . '/../backups'
            ];
            
            foreach ($directories as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
            
            return ['success' => true, 'message' => 'Archivos de configuraciÃ³n creados'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al crear archivos: ' . $e->getMessage()];
        }
    }
    
    /**
     * Proceso completo de instalaciÃ³n
     */
    public function install($data)
    {
        $steps = [];
        
        // Paso 1: Probar conexiÃ³n
        $result = $this->testDatabaseConnection(
            $data['db_host'],
            $data['db_username'],
            $data['db_password'],
            $data['db_database'],
            $data['db_port'] ?? 3306
        );
        
        $steps['database_connection'] = $result;
        
        if (!$result['success']) {
            return ['success' => false, 'steps' => $steps];
        }
        
        // Paso 2: Crear estructura
        $result = $this->createDatabaseStructure();
        $steps['database_structure'] = $result;
        
        if (!$result['success']) {
            return ['success' => false, 'steps' => $steps];
        }
        
        // Paso 3: Insertar datos iniciales
        $result = $this->insertInitialData(
            $data['admin_username'],
            $data['admin_email'],
            $data['admin_password']
        );
        
        $steps['initial_data'] = $result;
        
        if (!$result['success']) {
            return ['success' => false, 'steps' => $steps];
        }
        
        // Paso 4: Crear archivos de configuraciÃ³n
        $dbConfig = [
            'host' => $data['db_host'],
            'port' => $data['db_port'] ?? 3306,
            'database' => $data['db_database'],
            'username' => $data['db_username'],
            'password' => $data['db_password']
        ];
        
        $result = $this->createConfigFiles($dbConfig, $data['site_url'] ?? '');
        $steps['config_files'] = $result;
        
        return [
            'success' => true,
            'steps' => $steps,
            'message' => 'Â¡InstalaciÃ³n completada exitosamente!'
        ];
    }
}
?>