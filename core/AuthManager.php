<?php
/**
 * Gestor de Autenticación - CORREGIDO
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * Manejo de autenticación, autorización y gestión de sesiones
 * 
 * @package EncuestasHERCO\Core
 * @version 2.0.0
 * @author Sistema HERCO
 */

class AuthManager
{
    /**
     * Instancia de la base de datos
     * @var Database
     */
    private $db;
    
    /**
     * Configuración de autenticación
     * @var array
     */
    private $config = [];
    
    /**
     * Usuario actual
     * @var array|null
     */
    private $currentUser = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // ✅ CORRECCIÓN CRÍTICA: Usar getInstance() en lugar de new Database()
        $this->db = Database::getInstance();
        $this->loadConfig();
    }
    
    /**
     * Cargar configuración
     * 
     * @return void
     */
    private function loadConfig()
    {
        if (file_exists(__DIR__ . '/../config/app.php')) {
            $appConfig = require __DIR__ . '/../config/app.php';
            $this->config = $appConfig['security'] ?? [];
        }
        
        // Configuración por defecto
        $this->config = array_merge([
            'session' => [
                'lifetime' => 7200,
                'name' => 'HERCO_SESSION'
            ],
            'password' => [
                'min_length' => 8,
                'hash_algorithm' => PASSWORD_BCRYPT,
                'hash_cost' => 12
            ],
            'remember_me' => [
                'enabled' => true,
                'lifetime' => 2592000 // 30 días
            ]
        ], $this->config);
    }
    
    /**
     * Autenticar usuario
     * 
     * @param string $email Email del usuario
     * @param string $password Contraseña
     * @param bool $remember Recordar sesión
     * @return array|false Datos del usuario o false si falla
     */
    public function authenticate($email, $password, $remember = false)
    {
        try {
            // Buscar usuario por email
            $user = $this->db->fetch(
                'SELECT u.*, c.name as company_name, c.logo as company_logo 
                 FROM users u 
                 LEFT JOIN companies c ON u.company_id = c.id 
                 WHERE u.email = ? AND u.status = "active"',
                [$email]
            );
            
            if (!$user) {
                $this->logAuthEvent('failed_login', ['email' => $email, 'reason' => 'user_not_found']);
                return false;
            }
            
            // Verificar contraseña
            if (!password_verify($password, $user['password'])) {
                $this->logAuthEvent('failed_login', ['email' => $email, 'reason' => 'invalid_password']);
                return false;
            }
            
            // Verificar si la cuenta está bloqueada
            if ($this->isAccountLocked($user['id'])) {
                $this->logAuthEvent('failed_login', ['email' => $email, 'reason' => 'account_locked']);
                return false;
            }
            
            // Login exitoso
            $this->createSession($user);
            
            if ($remember) {
                $this->createRememberToken($user['id']);
            }
            
            // Actualizar información de login
            $this->updateLoginInfo($user['id']);
            
            // Limpiar intentos fallidos
            $this->clearFailedAttempts($email);
            
            $this->logAuthEvent('successful_login', ['user_id' => $user['id']]);
            
            return $user;
            
        } catch (Exception $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear sesión de usuario
     * 
     * @param array $user Datos del usuario
     * @return void
     */
    private function createSession($user)
    {
        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Generar nuevo token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        $this->currentUser = $user;
    }
    
    /**
     * Cerrar sesión
     * 
     * @return void
     */
    public function logout()
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            // Eliminar tokens de remember me
            $this->clearRememberTokens($userId);
            
            // Registrar logout
            $this->logAuthEvent('logout', ['user_id' => $userId]);
        }
        
        // Limpiar datos de sesión
        $_SESSION = [];
        
        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir remember me cookie si existe
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        // Destruir la sesión
        session_destroy();
        
        $this->currentUser = null;
    }
    
    /**
     * Verificar si el usuario está autenticado
     * 
     * @return bool
     */
    public function isAuthenticated()
    {
        if (isset($_SESSION['user_id'])) {
            // Verificar timeout de sesión
            if ($this->isSessionExpired()) {
                $this->logout();
                return false;
            }
            
            // Actualizar último acceso
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        // Verificar remember me token
        return $this->checkRememberToken();
    }
    
    /**
     * Obtener usuario actual
     * 
     * @return array|null
     */
    public function getCurrentUser()
    {
        if ($this->currentUser) {
            return $this->currentUser;
        }
        
        if (isset($_SESSION['user_id'])) {
            $this->currentUser = $this->db->fetch(
                'SELECT u.*, c.name as company_name, c.logo as company_logo 
                 FROM users u 
                 LEFT JOIN companies c ON u.company_id = c.id 
                 WHERE u.id = ? AND u.status = "active"',
                [$_SESSION['user_id']]
            );
        }
        
        return $this->currentUser;
    }
    
    /**
     * Verificar si el usuario tiene un rol específico
     * 
     * @param string $role Rol a verificar
     * @return bool
     */
    public function hasRole($role)
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     * 
     * @param array $roles Roles a verificar
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], $roles);
    }
    
    /**
     * Verificar si el usuario es administrador
     * 
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasAnyRole(['admin', 'superadmin']);
    }
    
    /**
     * Hash de contraseña
     * 
     * @param string $password Contraseña
     * @return string Hash de la contraseña
     */
    public function hashPassword($password)
    {
        $algorithm = $this->config['password']['hash_algorithm'] ?? PASSWORD_BCRYPT;
        $cost = $this->config['password']['hash_cost'] ?? 12;
        
        return password_hash($password, $algorithm, ['cost' => $cost]);
    }
    
    /**
     * Verificar contraseña
     * 
     * @param string $password Contraseña
     * @param string $hash Hash almacenado
     * @return bool
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Validar fortaleza de contraseña
     * 
     * @param string $password Contraseña
     * @return array Array con errores (vacío si es válida)
     */
    public function validatePassword($password)
    {
        $errors = [];
        $config = $this->config['password'];
        
        // Longitud mínima
        if (strlen($password) < $config['min_length']) {
            $errors[] = "La contraseña debe tener al menos {$config['min_length']} caracteres";
        }
        
        // Mayúsculas
        if (isset($config['require_uppercase']) && $config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula";
        }
        
        // Minúsculas
        if (isset($config['require_lowercase']) && $config['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula";
        }
        
        // Números
        if (isset($config['require_numbers']) && $config['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número";
        }
        
        // Símbolos
        if (isset($config['require_symbols']) && $config['require_symbols'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un símbolo";
        }
        
        return $errors;
    }
    
    /**
     * Generar token de reset de contraseña
     * 
     * @param string $email Email del usuario
     * @return string|false Token generado o false si falla
     */
    public function generatePasswordResetToken($email)
    {
        try {
            $user = $this->db->fetch('SELECT id FROM users WHERE email = ? AND status = "active"', [$email]);
            
            if (!$user) {
                return false;
            }
            
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hora
            
            // Limpiar tokens anteriores
            $this->db->delete('password_resets', 'email = ?', [$email]);
            
            // Crear nuevo token
            $this->db->insert('password_resets', [
                'email' => $email,
                'token' => hash('sha256', $token),
                'expires_at' => $expires,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->logAuthEvent('password_reset_requested', ['email' => $email]);
            
            return $token;
            
        } catch (Exception $e) {
            error_log("Error generando token de reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar token de reset de contraseña
     * 
     * @param string $token Token
     * @return string|false Email del usuario o false si es inválido
     */
    public function verifyPasswordResetToken($token)
    {
        try {
            $hashedToken = hash('sha256', $token);
            
            $reset = $this->db->fetch(
                'SELECT email FROM password_resets 
                 WHERE token = ? AND expires_at > NOW() AND used_at IS NULL',
                [$hashedToken]
            );
            
            return $reset ? $reset['email'] : false;
            
        } catch (Exception $e) {
            error_log("Error verificando token de reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Resetear contraseña
     * 
     * @param string $token Token de reset
     * @param string $newPassword Nueva contraseña
     * @return bool
     */
    public function resetPassword($token, $newPassword)
    {
        try {
            $email = $this->verifyPasswordResetToken($token);
            
            if (!$email) {
                return false;
            }
            
            // Validar nueva contraseña
            $errors = $this->validatePassword($newPassword);
            if (!empty($errors)) {
                return false;
            }
            
            $hashedToken = hash('sha256', $token);
            
            // Actualizar contraseña
            $hashedPassword = $this->hashPassword($newPassword);
            $this->db->update(
                'users',
                ['password' => $hashedPassword, 'password_changed_at' => date('Y-m-d H:i:s')],
                'email = ?',
                [$email]
            );
            
            // Marcar token como usado
            $this->db->update(
                'password_resets',
                ['used_at' => date('Y-m-d H:i:s')],
                'token = ?',
                [$hashedToken]
            );
            
            $this->logAuthEvent('password_reset_completed', ['email' => $email]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error reseteando contraseña: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si la sesión ha expirado
     * 
     * @return bool
     */
    private function isSessionExpired()
    {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        $sessionLifetime = $this->config['session']['lifetime'];
        return (time() - $_SESSION['last_activity']) > $sessionLifetime;
    }
    
    /**
     * Crear token de remember me
     * 
     * @param int $userId ID del usuario
     * @return void
     */
    private function createRememberToken($userId)
    {
        try {
            $token = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);
            $expires = date('Y-m-d H:i:s', time() + $this->config['remember_me']['lifetime']);
            
            // Limpiar tokens antiguos del usuario
            $this->db->delete('remember_tokens', 'user_id = ?', [$userId]);
            
            // Crear nuevo token
            $this->db->insert('remember_tokens', [
                'user_id' => $userId,
                'token' => $hashedToken,
                'expires_at' => $expires,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Establecer cookie
            $cookieLifetime = $this->config['remember_me']['lifetime'];
            setcookie('remember_token', $token, time() + $cookieLifetime, '/', '', true, true);
            
        } catch (Exception $e) {
            error_log("Error creando remember token: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar token de remember me
     * 
     * @return bool
     */
    private function checkRememberToken()
    {
        if (!isset($_COOKIE['remember_token']) || !$this->config['remember_me']['enabled']) {
            return false;
        }
        
        try {
            $token = $_COOKIE['remember_token'];
            $hashedToken = hash('sha256', $token);
            
            $tokenData = $this->db->fetch(
                'SELECT rt.user_id, u.* 
                 FROM remember_tokens rt 
                 JOIN users u ON rt.user_id = u.id 
                 WHERE rt.token = ? AND rt.expires_at > NOW() AND u.status = "active"',
                [$hashedToken]
            );
            
            if ($tokenData) {
                // Recrear sesión
                $this->createSession($tokenData);
                
                // Actualizar token (rotar por seguridad)
                $this->createRememberToken($tokenData['user_id']);
                
                return true;
            }
            
            // Token inválido, limpiar cookie
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            return false;
            
        } catch (Exception $e) {
            error_log("Error verificando remember token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpiar tokens de remember me
     * 
     * @param int $userId ID del usuario
     * @return void
     */
    private function clearRememberTokens($userId)
    {
        try {
            $this->db->delete('remember_tokens', 'user_id = ?', [$userId]);
        } catch (Exception $e) {
            error_log("Error limpiando remember tokens: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar si la cuenta está bloqueada
     * 
     * @param int $userId ID del usuario
     * @return bool
     */
    private function isAccountLocked($userId)
    {
        try {
            $lockInfo = $this->db->fetch(
                'SELECT locked_until FROM users WHERE id = ? AND locked_until > NOW()',
                [$userId]
            );
            
            return $lockInfo !== false;
            
        } catch (Exception $e) {
            error_log("Error verificando bloqueo de cuenta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar información de login
     * 
     * @param int $userId ID del usuario
     * @return void
     */
    private function updateLoginInfo($userId)
    {
        try {
            $this->db->update('users', [
                'last_login' => date('Y-m-d H:i:s'),
                'last_ip' => $this->getClientIP()
            ], 'id = ?', [$userId]);
            
        } catch (Exception $e) {
            error_log("Error actualizando info de login: " . $e->getMessage());
        }
    }
    
    /**
     * Limpiar intentos fallidos
     * 
     * @param string $email Email del usuario
     * @return void
     */
    private function clearFailedAttempts($email)
    {
        try {
            $this->db->delete('failed_login_attempts', 'email = ?', [$email]);
        } catch (Exception $e) {
            error_log("Error limpiando intentos fallidos: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar evento de autenticación
     * 
     * @param string $event Tipo de evento
     * @param array $data Datos adicionales
     * @return void
     */
    private function logAuthEvent($event, $data = [])
    {
        try {
            $logData = array_merge($data, [
                'event' => $event,
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->insert('auth_logs', $logData);
            
        } catch (Exception $e) {
            error_log("Error registrando evento de auth: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener IP del cliente
     * 
     * @return string
     */
    private function getClientIP()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Limpiar sesiones expiradas
     * 
     * @return void
     */
    public function cleanupExpiredSessions()
    {
        try {
            // Limpiar tokens de remember me expirados
            $this->db->delete('remember_tokens', 'expires_at < NOW()');
            
            // Limpiar tokens de reset de contraseña expirados
            $this->db->delete('password_resets', 'expires_at < NOW()');
            
            // Limpiar intentos de login antiguos (más de 24 horas)
            $this->db->delete('failed_login_attempts', 'created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)');
            
        } catch (Exception $e) {
            error_log("Error limpiando sesiones expiradas: " . $e->getMessage());
        }
    }
}
