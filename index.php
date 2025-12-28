<?php
/**
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * Punto de entrada principal - VERSIÃ“N CORREGIDA
 * 
 * @version 2.0.0
 */

// ==========================================
// 1. CONFIGURACIÓN BÁSICA
// ==========================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // 0 en producciÃ³n, 1 en desarrollo
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Zona horaria
date_default_timezone_set('America/Tegucigalpa');

// ConfiguraciÃ³n de sesiÃ³n segura
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// ==========================================
// 2. VERIFICAR INSTALACIÃ“N
// ==========================================
$installedLockFile = __DIR__ . '/config/installed.lock';

if (!file_exists($installedLockFile)) {
    // Redirigir al instalador
    header('Location: /install/');
    exit('âš ï¸ Sistema no instalado. Redirigiendo al instalador...');
}

// ==========================================
// 3. CARGAR CONFIGURACIONES
// ==========================================
$dbConfigFile = __DIR__ . '/config/database_config.php';
$appConfigFile = __DIR__ . '/config/app.php';

if (!file_exists($dbConfigFile)) {
    die('âŒ Error: Archivo de configuraciÃ³n de base de datos no encontrado.');
}

if (!file_exists($appConfigFile)) {
    die('âŒ Error: Archivo de configuraciÃ³n de aplicaciÃ³n no encontrado.');
}

$dbConfig = require_once $dbConfigFile;
$appConfig = require_once $appConfigFile;

// ==========================================
// 4. AUTOLOADER - CARGAR CLASES DEL SISTEMA
// ==========================================

/**
 * Autocarga personalizada de clases
 * Busca en: core/, controllers/, models/
 */
spl_autoload_register(function ($className) {
    // Directorios donde buscar clases
    $directories = [
        __DIR__ . '/core/',
        __DIR__ . '/controllers/',
        __DIR__ . '/models/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    // Si no se encuentra, registrar error
    error_log("âš ï¸ Clase no encontrada: {$className}");
    return false;
});

// ==========================================
// 5. CARGAR CLASES PRINCIPALES DEL NÃšCLEO
// ==========================================

// Cargar Database
$databaseFile = __DIR__ . '/core/Database.php';
if (!file_exists($databaseFile)) {
    die("âŒ Error crÃ­tico: No se encuentra /core/Database.php");
}
require_once $databaseFile;

// Cargar View
$viewFile = __DIR__ . '/core/View.php';
if (!file_exists($viewFile)) {
    die("âŒ Error crÃ­tico: No se encuentra /core/View.php");
}
require_once $viewFile;

// Cargar Controller
$controllerFile = __DIR__ . '/core/Controller.php';
if (!file_exists($controllerFile)) {
    die("âŒ Error crÃ­tico: No se encuentra /core/Controller.php");
}
require_once $controllerFile;

// Cargar Router - CRÃTICO
$routerFile = __DIR__ . '/core/Router.php';
if (!file_exists($routerFile)) {
    die("âŒ Error crÃ­tico: No se encuentra /core/Router.php en: " . $routerFile);
}
require_once $routerFile;

// Verificar que las clases existen
if (!class_exists('Database')) {
    die("âŒ Error: Clase Database no cargada correctamente");
}
if (!class_exists('View')) {
    die("âŒ Error: Clase View no cargada correctamente");
}
if (!class_exists('Controller')) {
    die("âŒ Error: Clase Controller no cargada correctamente");
}
if (!class_exists('Router')) {
    die("âŒ Error: Clase Router no cargada correctamente");
}

// ==========================================
// 6. INICIALIZAR BASE DE DATOS
// ==========================================
try {
    $database = Database::getInstance();
} catch (Exception $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    die('âŒ Error al conectar con la base de datos. Por favor, verifique la configuraciÃ³n.');
}

// ==========================================
// 7. INICIAR SESIÃ“N - CRÃTICO PARA AUTENTICACIÃ“N
// ==========================================

// Configurar parÃ¡metros de sesiÃ³n ANTES de iniciar
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// Si hay HTTPS, usar cookies seguras
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Configurar tiempo de vida de la sesiÃ³n (2 horas por defecto)
ini_set('session.gc_maxlifetime', 7200);

// Iniciar sesiÃ³n si no estÃ¡ activa
if (session_status() === PHP_SESSION_NONE) {
    if (!session_start()) {
        error_log("âŒ ERROR CRÃTICO: No se pudo iniciar la sesiÃ³n");
        die('Error al iniciar sesiÃ³n. Contacte al administrador.');
    }
    
    // Log de sesiÃ³n iniciada (solo en desarrollo)
    if (($appConfig['debug'] ?? false)) {
        error_log("âœ… SesiÃ³n iniciada: ID=" . session_id());
    }
}

// Verificar si hay sesiÃ³n activa de usuario (para debugging)
if (($appConfig['debug'] ?? false) && isset($_SESSION['user_id'])) {
    error_log("ðŸ‘¤ Usuario en sesiÃ³n: ID=" . $_SESSION['user_id'] . ", Email=" . ($_SESSION['user_email'] ?? 'N/A'));
}

// ==========================================
// 7.5. DEFINIR CONSTANTES GLOBALES
// ==========================================

// âœ… BASE_URL - URL base de la aplicaciÃ³n
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    
    // Si estÃ¡ en raÃ­z, no agregar slash
    $basePath = ($scriptPath === '/' || $scriptPath === '\\') ? '' : $scriptPath;
    
    define('BASE_URL', $protocol . '://' . $host . $basePath . '/');
}

// âœ… BASE_PATH - Ruta fÃ­sica del sistema
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// âœ… APP_NAME - Nombre de la aplicaciÃ³n
if (!defined('APP_NAME')) {
    define('APP_NAME', $appConfig['app_name'] ?? 'Sistema de Encuestas HERCO');
}

// âœ… APP_VERSION - VersiÃ³n del sistema
if (!defined('APP_VERSION')) {
    define('APP_VERSION', $appConfig['version'] ?? '2.0.0');
}

// âœ… APP_ENV - Entorno de ejecuciÃ³n
if (!defined('APP_ENV')) {
    define('APP_ENV', $appConfig['environment'] ?? 'production');
}

// Log de BASE_URL para debugging (opcional)
if (($appConfig['debug'] ?? false)) {
    error_log("ðŸŒ BASE_URL definido: " . BASE_URL);
}

// ==========================================
// 8. INICIALIZAR ENRUTADOR
// ==========================================
$router = new Router();

// ==========================================
// 9. DEFINIR RUTAS DEL SISTEMA
// ==========================================

// --- RUTA RAÃZ ---
$router->add('GET', '/', function() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        header('Location: /admin/dashboard');
    } else {
        header('Location: /login');
    }
    exit;
});

// --- RUTAS DE AUTENTICACIÃ“N ---
$router->add('GET', '/login', 'AuthController', 'showLogin');
$router->add('POST', '/login', 'AuthController', 'login');
$router->add('GET', '/logout', 'AuthController', 'logout');
$router->add('GET', '/forgot-password', 'AuthController', 'showForgotPassword');
$router->add('POST', '/forgot-password', 'AuthController', 'forgotPassword');
$router->add('GET', '/reset-password', 'AuthController', 'showResetPassword');
$router->add('POST', '/reset-password', 'AuthController', 'resetPassword');

// --- RUTAS ADMINISTRATIVAS ---
$router->add('GET', '/admin', function() {
    header('Location: /admin/dashboard');
    exit;
});

$router->add('GET', '/admin/dashboard', 'AdminController', 'dashboard');

// --- RUTAS DE ENCUESTAS ---
$router->add('GET', '/admin/surveys', 'SurveyController', 'index');
$router->add('GET', '/admin/surveys/create', 'SurveyController', 'create');
$router->add('POST', '/admin/surveys/store', 'SurveyController', 'store');
$router->add('GET', '/admin/surveys/:id', 'SurveyController', 'show');
$router->add('GET', '/admin/surveys/:id/edit', 'SurveyController', 'edit');
$router->add('POST', '/admin/surveys/:id/update', 'SurveyController', 'update');
$router->add('POST', '/admin/surveys/:id/delete', 'SurveyController', 'delete');

// --- RUTAS DE PREGUNTAS ---
$router->add('GET', '/admin/questions', 'QuestionController', 'index');
$router->add('GET', '/admin/questions/builder', 'QuestionController', 'builder');
$router->add('POST', '/admin/questions/store', 'QuestionController', 'store');

// --- RUTAS DE PARTICIPANTES ---
$router->add('GET', '/admin/participants', 'ParticipantController', 'index');
$router->add('GET', '/admin/participants/create', 'ParticipantController', 'create');
$router->add('POST', '/admin/participants/store', 'ParticipantController', 'store');
$router->add('GET', '/admin/participants/import', 'ParticipantController', 'import');
$router->add('POST', '/admin/participants/import', 'ParticipantController', 'processImport');
$router->add('GET', '/admin/participants/:id/edit', 'ParticipantController', 'edit');
$router->add('POST', '/admin/participants/:id/update', 'ParticipantController', 'update');
$router->add('POST', '/admin/participants/:id/delete', 'ParticipantController', 'delete');

// --- RUTAS DE REPORTES ---
$router->add('GET', '/admin/reports', 'ReportController', 'index');
$router->add('GET', '/admin/reports/herco', 'ReportController', 'herco');
$router->add('GET', '/admin/reports/export/:format', 'ReportController', 'export');

// --- RUTAS DE CONFIGURACIONES ---
$router->add('GET', '/admin/settings', 'SettingsController', 'index');
$router->add('POST', '/admin/settings/update', 'SettingsController', 'update');

// 🔹 Lista de usuarios
$router->add('GET', '/admin/users', 'UserController', 'index');

// 🔹 Crear usuario (DEBE ir antes de :id)
$router->add('GET', '/admin/users/create', 'UserController', 'create');
$router->add('POST', '/admin/users/store', 'UserController', 'store');

// 🔹 Editar usuario (DEBE ir antes de /admin/users/:id)
$router->add('GET', '/admin/users/:id/edit', 'UserController', 'edit');
$router->add('POST', '/admin/users/:id/update', 'UserController', 'update');

// 🔹 Eliminar usuario
$router->add('POST', '/admin/users/:id/delete', 'UserController', 'delete');

// 🔹 Ver perfil de usuario (DEBE ir al FINAL)
$router->add('GET', '/admin/users/:id', 'UserController', 'show');

// ==========================================
// RUTAS DE EMPRESAS (CompanyController)
// ==========================================

// Listar empresas (solo super admin)
$router->add('GET', '/admin/companies', 'CompanyController', 'index');

// Perfil de empresa (admin regular)
$router->add('GET', '/admin/companies/profile', 'CompanyController', 'profile');
$router->add('POST', '/admin/companies/update', 'CompanyController', 'update');

// Configuraciones de empresa
$router->add('GET', '/admin/companies/settings', 'CompanyController', 'settings');
$router->add('POST', '/admin/companies/settings', 'CompanyController', 'settings');

// Departamentos
$router->add('GET', '/admin/companies/departments', 'CompanyController', 'departments');
$router->add('POST', '/admin/companies/departments', 'CompanyController', 'departments');

// Branding
$router->add('GET', '/admin/companies/branding', 'CompanyController', 'branding');
$router->add('POST', '/admin/companies/branding', 'CompanyController', 'branding');

// CRUD de empresas (solo super admin)
$router->add('GET', '/admin/companies/create', 'CompanyController', 'create');
$router->add('POST', '/admin/companies/store', 'CompanyController', 'store');
$router->add('GET', '/admin/companies/:id/edit', 'CompanyController', 'edit');
$router->add('POST', '/admin/companies/:id/delete', 'CompanyController', 'delete');

// --- RUTAS PÃšBLICAS DE ENCUESTAS ---
$router->add('GET', '/survey/:token', 'SurveyController', 'participate');
$router->add('POST', '/survey/:token/submit', 'SurveyController', 'submitResponse');

// --- RUTAS DE API (futuro) ---
$router->add('GET', '/api/v1/surveys', 'ApiController', 'surveys');
$router->add('POST', '/api/v1/surveys/:id/responses', 'ApiController', 'storeResponse');

// ==========================================
// 10. PROCESAR LA SOLICITUD
// ==========================================
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    
    // Limpiar query string de la URI
    $uri = strtok($uri, '?');
    
    // Despachar ruta
    $router->dispatch($method, $uri);
    
} catch (Exception $e) {
    // Manejar error de enrutamiento
    error_log("Error de enrutamiento: " . $e->getMessage());
    
    http_response_code(500);
    
    if ($appConfig['debug'] ?? false) {
        echo "<h1>Error del Sistema</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>Error del Sistema</h1>";
        echo "<p>Ha ocurrido un error. Por favor, contacte al administrador.</p>";
    }
}