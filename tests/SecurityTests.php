<?php
/**
 * Tests Automatizados para Sistema de Seguridad
 * Sistema HERCO v2.0
 * 
 * Ejecutar: php tests/SecurityTest.php
 * 
 * @package EncuestasHERCO\Tests
 */

require_once __DIR__ . '/../core/SecurityMiddleware.php';
require_once __DIR__ . '/../core/SecurityHelpers.php';

class SecurityTest
{
    private $passed = 0;
    private $failed = 0;
    private $tests = [];
    
    public function __construct()
    {
        // Iniciar sesión para tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        echo "\n";
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║  🧪 TESTS DE SEGURIDAD - Sistema HERCO v2.0          ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }
    
    /**
     * Ejecutar todos los tests
     */
    public function runAll()
    {
        $this->testCsrfFunctions();
        $this->testSanitization();
        $this->testAuthentication();
        $this->testRateLimiting();
        $this->testPasswordHelpers();
        $this->testSessionHelpers();
        $this->testSecurityMiddleware();
        
        $this->printResults();
    }
    
    // ==========================================
    // CSRF TESTS
    // ==========================================
    
    private function testCsrfFunctions()
    {
        echo "📋 Testing CSRF Functions...\n";
        
        // Test 1: Generar token
        $token = csrf_token();
        $this->assert(
            !empty($token) && strlen($token) === 64,
            "csrf_token() genera token de 64 caracteres"
        );
        
        // Test 2: Token persiste en sesión
        $this->assert(
            isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token,
            "csrf_token() guarda en sesión"
        );
        
        // Test 3: csrf_field() genera HTML
        $field = csrf_field();
        $this->assert(
            strpos($field, '<input type="hidden"') !== false &&
            strpos($field, 'name="_token"') !== false &&
            strpos($field, $token) !== false,
            "csrf_field() genera HTML correcto"
        );
        
        // Test 4: Verificar token válido
        $this->assert(
            csrf_verify($token) === true,
            "csrf_verify() acepta token válido"
        );
        
        // Test 5: Rechazar token inválido
        $this->assert(
            csrf_verify('invalid-token') === false,
            "csrf_verify() rechaza token inválido"
        );
        
        // Test 6: Regenerar token
        $newToken = csrf_regenerate();
        $this->assert(
            $newToken !== $token && strlen($newToken) === 64,
            "csrf_regenerate() genera nuevo token"
        );
        
        echo "\n";
    }
    
    // ==========================================
    // SANITIZATION TESTS
    // ==========================================
    
    private function testSanitization()
    {
        echo "🧼 Testing Sanitization Functions...\n";
        
        // Test 1: Sanitizar input básico
        $input = "  <script>alert('xss')</script>  ";
        $clean = sanitize_input($input);
        $this->assert(
            strpos($clean, '<script>') === false,
            "sanitize_input() escapa HTML peligroso"
        );
        
        // Test 2: Sanitizar con HTML permitido
        $input = "<p>Hello <strong>World</strong></p><script>bad</script>";
        $clean = sanitize_input($input, true);
        $this->assert(
            strpos($clean, '<p>') !== false &&
            strpos($clean, '<strong>') !== false &&
            strpos($clean, '<script>') === false,
            "sanitize_input() permite HTML seguro"
        );
        
        // Test 3: Sanitizar array
        $input = [
            'name' => '  John Doe  ',
            'email' => '<script>test@test.com</script>'
        ];
        $clean = sanitize_input($input);
        $this->assert(
            $clean['name'] === 'John Doe' &&
            strpos($clean['email'], '<script>') === false,
            "sanitize_input() funciona con arrays"
        );
        
        // Test 4: Sanitizar email válido
        $email = sanitize_email('  test@example.com  ');
        $this->assert(
            $email === 'test@example.com',
            "sanitize_email() limpia email válido"
        );
        
        // Test 5: Rechazar email inválido
        $email = sanitize_email('not-an-email');
        $this->assert(
            $email === false,
            "sanitize_email() rechaza email inválido"
        );
        
        // Test 6: Sanitizar URL válida
        $url = sanitize_url('  https://example.com  ');
        $this->assert(
            $url === 'https://example.com',
            "sanitize_url() limpia URL válida"
        );
        
        // Test 7: Sanitizar entero
        $number = sanitize_int('42abc');
        $this->assert(
            $number === 42,
            "sanitize_int() extrae número entero"
        );
        
        // Test 8: Sanitizar entero inválido con default
        $number = sanitize_int('invalid', 999);
        $this->assert(
            $number === 999,
            "sanitize_int() usa valor por defecto"
        );
        
        // Test 9: Sanitizar float
        $number = sanitize_float('3.14abc');
        $this->assert(
            abs($number - 3.14) < 0.01,
            "sanitize_float() extrae número flotante"
        );
        
        // Test 10: Sanitizar filename
        $filename = sanitize_filename('../../../etc/passwd');
        $this->assert(
            $filename === 'passwd' || $filename === '___etc_passwd',
            "sanitize_filename() previene path traversal"
        );
        
        echo "\n";
    }
    
    // ==========================================
    // AUTHENTICATION TESTS
    // ==========================================
    
    private function testAuthentication()
    {
        echo "🔐 Testing Authentication Functions...\n";
        
        // Test 1: Usuario no autenticado por defecto
        unset($_SESSION['user_id']);
        $this->assert(
            is_authenticated() === false,
            "is_authenticated() retorna false sin sesión"
        );
        
        // Test 2: Usuario autenticado
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'test_user';
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_email'] = 'test@test.com';
        
        $this->assert(
            is_authenticated() === true,
            "is_authenticated() retorna true con sesión"
        );
        
        // Test 3: Obtener ID de usuario
        $this->assert(
            current_user_id() === 1,
            "current_user_id() retorna ID correcto"
        );
        
        // Test 4: Obtener datos de usuario
        $user = current_user();
        $this->assert(
            $user['id'] === 1 &&
            $user['username'] === 'test_user' &&
            $user['role'] === 'admin',
            "current_user() retorna datos completos"
        );
        
        // Test 5: Verificar rol
        $this->assert(
            has_role('admin') === true,
            "has_role() verifica rol correcto"
        );
        
        // Test 6: Verificar rol incorrecto
        $this->assert(
            has_role('user') === false,
            "has_role() rechaza rol incorrecto"
        );
        
        // Test 7: Verificar múltiples roles
        $this->assert(
            has_role(['admin', 'manager']) === true,
            "has_role() acepta array de roles"
        );
        
        // Test 8: Verificar admin
        $this->assert(
            is_admin() === true,
            "is_admin() identifica administrador"
        );
        
        // Test 9: Verificar propiedad
        $this->assert(
            is_owner(1) === true,
            "is_owner() verifica propiedad correcta"
        );
        
        // Test 10: No es propietario
        $this->assert(
            is_owner(999) === false,
            "is_owner() rechaza propiedad incorrecta"
        );
        
        echo "\n";
    }
    
    // ==========================================
    // RATE LIMITING TESTS
    // ==========================================
    
    private function testRateLimiting()
    {
        echo "⏱️  Testing Rate Limiting...\n";
        
        // Limpiar rate limits previos
        unset($_SESSION['rate_limits']);
        
        $identifier = 'test@test.com';
        
        // Test 1: Primeros intentos permitidos
        for ($i = 1; $i <= 5; $i++) {
            $allowed = check_login_rate_limit($identifier);
            if ($i < 5) {
                record_failed_login($identifier);
            }
        }
        
        $this->assert(
            $allowed === true,
            "check_login_rate_limit() permite primeros 5 intentos"
        );
        
        // Test 2: 6to intento bloqueado
        record_failed_login($identifier);
        $allowed = check_login_rate_limit($identifier);
        
        $this->assert(
            $allowed === false,
            "check_login_rate_limit() bloquea después de 5 intentos"
        );
        
        // Test 3: Limpiar intentos
        clear_login_attempts($identifier);
        $allowed = check_login_rate_limit($identifier);
        
        $this->assert(
            $allowed === true,
            "clear_login_attempts() restablece el límite"
        );
        
        echo "\n";
    }
    
    // ==========================================
    // PASSWORD TESTS
    // ==========================================
    
    private function testPasswordHelpers()
    {
        echo "🔑 Testing Password Functions...\n";
        
        $password = 'MySecurePassword123!';
        
        // Test 1: Hash de password
        $hash = hash_password($password);
        $this->assert(
            !empty($hash) && strlen($hash) === 60,
            "hash_password() genera hash bcrypt de 60 caracteres"
        );
        
        // Test 2: Verificar password correcto
        $this->assert(
            verify_password($password, $hash) === true,
            "verify_password() acepta password correcto"
        );
        
        // Test 3: Rechazar password incorrecto
        $this->assert(
            verify_password('WrongPassword', $hash) === false,
            "verify_password() rechaza password incorrecto"
        );
        
        // Test 4: Verificar necesidad de rehash
        $needsRehash = needs_password_rehash($hash);
        $this->assert(
            is_bool($needsRehash),
            "needs_password_rehash() retorna booleano"
        );
        
        echo "\n";
    }
    
    // ==========================================
    // SESSION TESTS
    // ==========================================
    
    private function testSessionHelpers()
    {
        echo "📦 Testing Session Functions...\n";
        
        // Test 1: Set flash message
        set_flash('success', 'Test message');
        $this->assert(
            has_flash() === true,
            "set_flash() guarda mensaje"
        );
        
        // Test 2: Get flash messages
        $messages = get_flash();
        $this->assert(
            count($messages) === 1 &&
            $messages[0]['type'] === 'success' &&
            $messages[0]['message'] === 'Test message',
            "get_flash() retorna mensajes correctos"
        );
        
        // Test 3: Flash messages se limpian
        $this->assert(
            has_flash() === false,
            "get_flash() limpia mensajes después de leer"
        );
        
        // Test 4: Regenerar sesión
        $oldId = session_id();
        regenerate_session();
        $newId = session_id();
        $this->assert(
            $oldId !== $newId,
            "regenerate_session() cambia ID de sesión"
        );
        
        echo "\n";
    }
    
    // ==========================================
    // SECURITY MIDDLEWARE TESTS
    // ==========================================
    
    private function testSecurityMiddleware()
    {
        echo "🛡️  Testing SecurityMiddleware...\n";
        
        // Test 1: Crear instancia
        $middleware = new SecurityMiddleware();
        $this->assert(
            $middleware !== null,
            "SecurityMiddleware se instancia correctamente"
        );
        
        // Test 2: Obtener token CSRF
        $token = $middleware->getCsrfToken();
        $this->assert(
            !empty($token) && strlen($token) === 64,
            "SecurityMiddleware genera token CSRF"
        );
        
        // Test 3: Check rate limiting
        $identifier = 'middleware@test.com';
        clear_login_attempts($identifier);
        
        $allowed = $middleware->checkLoginRateLimit($identifier);
        $this->assert(
            $allowed === true,
            "checkLoginRateLimit() funciona"
        );
        
        // Test 4: Record failed login
        $middleware->recordFailedLogin($identifier);
        $this->assert(
            true, // Si no lanza excepción, funciona
            "recordFailedLogin() registra intento"
        );
        
        // Test 5: Clear attempts
        $middleware->clearLoginAttempts($identifier);
        $this->assert(
            true,
            "clearLoginAttempts() limpia intentos"
        );
        
        echo "\n";
    }
    
    // ==========================================
    // HELPER METHODS
    // ==========================================
    
    private function assert($condition, $message)
    {
        if ($condition) {
            $this->passed++;
            echo "  ✅ {$message}\n";
        } else {
            $this->failed++;
            echo "  ❌ {$message}\n";
        }
        
        $this->tests[] = [
            'message' => $message,
            'passed' => $condition
        ];
    }
    
    private function printResults()
    {
        $total = $this->passed + $this->failed;
        $percentage = $total > 0 ? round(($this->passed / $total) * 100, 2) : 0;
        
        echo "\n";
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║  📊 RESULTADOS DE LOS TESTS                           ║\n";
        echo "╠════════════════════════════════════════════════════════╣\n";
        echo sprintf("║  Total Tests:    %-37d║\n", $total);
        echo sprintf("║  ✅ Pasados:     %-37d║\n", $this->passed);
        echo sprintf("║  ❌ Fallidos:    %-37d║\n", $this->failed);
        echo sprintf("║  📈 Porcentaje:  %-34s%%  ║\n", $percentage);
        echo "╚════════════════════════════════════════════════════════╝\n";
        
        if ($this->failed === 0) {
            echo "\n🎉 ¡Todos los tests pasaron exitosamente!\n\n";
            exit(0);
        } else {
            echo "\n⚠️  Algunos tests fallaron. Revise los errores arriba.\n\n";
            exit(1);
        }
    }
}

// ==========================================
// EJECUTAR TESTS
// ==========================================

// Solo ejecutar si se llama directamente
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $test = new SecurityTest();
    $test->runAll();
}
