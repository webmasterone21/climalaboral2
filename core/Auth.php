/ ==============================================
// ARCHIVO 2: core/Auth.php
// ==============================================
<?php
/**
 * Funciones de ayuda para autenticación y autorización
 * Úsalas en cualquier parte del sistema
 */

/**
 * Verificar si el usuario está autenticado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtener datos del usuario actual
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['user_role'],
        'email' => $_SESSION['user_email']
    ];
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

/**
 * Verificar si el usuario es administrador
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Verificar si el usuario es consultor
 */
function isConsultant() {
    return hasRole('consultant');
}

/**
 * Verificar permisos para una acción específica
 */
function hasPermission($action, $resource = null) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'];
    
    // Los administradores tienen todos los permisos
    if ($userRole === 'admin') {
        return true;
    }
    
    // Permisos específicos para consultores
    if ($userRole === 'consultant') {
        $consultantPermissions = [
            'create_survey',
            'edit_own_survey',
            'view_own_surveys',
            'delete_own_survey',
            'view_own_reports',
            'export_own_reports'
        ];
        
        return in_array($action, $consultantPermissions);
    }
    
    return false;
}

/**
 * Redirigir si no está autenticado
 */
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}

/**
 * Redirigir si no tiene el rol requerido
 */
function requireRole($role) {
    requireAuth();
    
    if (!hasRole($role)) {
        include 'views/errors/403.php';
        exit;
    }
}

/**
 * Verificar propiedad del recurso
 */
function isOwner($resourceUserId) {
    return isLoggedIn() && $_SESSION['user_id'] == $resourceUserId;
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verificar cookie "recordarme"
 */
function checkRememberCookie() {
    if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
        try {
            $database = new Database();
            $db = $database->connect();
            
            $sql = "SELECT u.* FROM users u 
                    JOIN remember_tokens rt ON u.id = rt.user_id 
                    WHERE rt.token = ? AND rt.expires_at > NOW()";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$_COOKIE['remember_token']]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Recrear sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['login_time'] = time();
                
                session_regenerate_id(true);
                return true;
            }
        } catch (Exception $e) {
            error_log('Remember cookie error: ' . $e->getMessage());
        }
    }
    
    return false;
}

// ==============================================
// ARCHIVO 3: SQL ADICIONAL PARA BASE DE DATOS
// Ejecutar este SQL después del instalador
// ==============================================

/*
-- Tabla para tokens de "recordarme"
CREATE TABLE remember_tokens (
    user_id INT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla para control de intentos de login
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
);

-- Tabla para logs de actividad
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created (created_at)
);

-- Agregar campo active a usuarios
ALTER TABLE users ADD COLUMN active BOOLEAN DEFAULT TRUE AFTER role;

-- Agregar configuración para registro
INSERT INTO system_config (config_key, config_value, description) VALUES
('allow_registration', 'true', 'Permitir registro de nuevos usuarios'),
('session_timeout', '3600', 'Tiempo de vida de sesión en segundos'),
('max_login_attempts', '5', 'Máximo intentos de login por IP'),
('lockout_duration', '900', 'Duración de bloqueo en segundos (15 minutos)');

-- Evento para limpiar tokens expirados
DELIMITER //
CREATE EVENT IF NOT EXISTS cleanup_expired_tokens
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DELETE FROM remember_tokens WHERE expires_at < NOW();
    DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY);
    DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
END //
DELIMITER ;
*/