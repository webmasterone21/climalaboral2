<?php
/**
 * AuthController - ADAPTADO A ESTRUCTURA REAL DE BD
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * @version 2.0.0
 */

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        try {
            // Si ya está autenticado, redirigir al dashboard
            if ($this->isAuthenticated()) {
                $this->redirect('/admin/dashboard');
                return;
            }
            
            // Preparar datos para la vista
            $data = [
                'page_title' => 'Iniciar Sesión',
                'csrf_token' => $this->generateCSRFToken()
            ];
            
            $this->render('auth/login', $data, 'auth');
            
        } catch (Exception $e) {
            error_log("Error mostrando login: " . $e->getMessage());
            $this->showBasicLoginForm();
        }
    }
    
    /**
     * Procesar intento de login - ADAPTADO A BD REAL
     */
    public function login()
    {
        try {
            // DEBUG: Verificar que la sesión está iniciada
            if (session_status() !== PHP_SESSION_ACTIVE) {
                error_log("⚠️ SESIÓN NO ACTIVA al inicio de login()");
                session_start();
            }
            
            error_log("🔐 Iniciando proceso de login...");
            error_log("📋 Session ID: " . session_id());
            error_log("📂 Session Save Path: " . session_save_path());
            
            // Verificar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('/login');
                return;
            }
            
            // Obtener datos del formulario
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            error_log("📧 Email ingresado: {$email}");
            
            // Validaciones básicas
            if (empty($email) || empty($password)) {
                error_log("❌ Email o password vacíos");
                $this->setFlashMessage('Email y contraseña son requeridos', 'error');
                $this->redirect('/login');
                return;
            }
            
            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log("❌ Formato de email inválido");
                $this->setFlashMessage('El formato del email no es válido', 'error');
                $this->redirect('/login');
                return;
            }
            
            // Intentar autenticar
            error_log("🔍 Buscando usuario en BD...");
            $user = $this->authenticateUser($email, $password);
            
            if (!$user) {
                error_log("❌ Autenticación fallida para: {$email}");
                $this->setFlashMessage('Email o contraseña incorrectos', 'error');
                $this->logFailedLogin($email);
                $this->redirect('/login');
                return;
            }
            
            error_log("✅ Usuario encontrado: ID=" . $user['id'] . ", Role=" . $user['role']);
            
            // Verificar estado del usuario
            if ($user['status'] !== 'active') {
                error_log("❌ Usuario no activo: status=" . $user['status']);
                $this->setFlashMessage('Su cuenta está desactivada. Contacte al administrador.', 'error');
                $this->redirect('/login');
                return;
            }
            
            // ✅ CREAR SESIÓN
            error_log("💾 Creando sesión para usuario ID: " . $user['id']);
            $this->createUserSession($user, $remember);
            
            // Verificar que la sesión se guardó correctamente
            if (!isset($_SESSION['user_id'])) {
                error_log("❌ ERROR CRÍTICO: La sesión no se guardó");
                $this->setFlashMessage('Error al crear sesión. Intente nuevamente.', 'error');
                $this->redirect('/login');
                return;
            }
            
            error_log("✅ Sesión creada exitosamente");
            error_log("📋 user_id en sesión: " . $_SESSION['user_id']);
            error_log("📋 user_role en sesión: " . $_SESSION['user_role']);
            error_log("📋 user_email en sesión: " . $_SESSION['user_email']);
            
            // Registrar login exitoso (sin tabla security_logs)
            $this->logSuccessfulLogin($user['id']);
            
            // Actualizar último acceso (sin columna last_login)
            $this->updateLastLogin($user['id']);
            
            // CRÍTICO: Guardar sesión explícitamente
            session_write_close();
            
            // Redirigir según rol
            $redirectUrl = $this->getRedirectUrl($user['role']);
            error_log("🔄 Redirigiendo a: {$redirectUrl}");
            
            // Reabrir sesión para mensaje flash
            session_start();
            // ✅ CORRECCIÓN: Usar 'name' en lugar de 'first_name'
            $this->setFlashMessage('¡Bienvenido, ' . ($user['name'] ?? $user['full_name'] ?? 'Usuario') . '!', 'success');
            
            $this->redirect($redirectUrl);
            
        } catch (Exception $e) {
            error_log("💥 ERROR CRÍTICO en login(): " . $e->getMessage());
            error_log("📍 Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
            
            $this->setFlashMessage('Error del sistema. Por favor, intente nuevamente.', 'error');
            $this->redirect('/login');
        }
    }
    
    /**
     * Autenticar usuario
     */
    private function authenticateUser($email, $password)
    {
        try {
            // ✅ CORRECCIÓN: Query simple sin JOINs que fallan
            $user = $this->db->fetch(
                'SELECT * FROM users WHERE email = ? LIMIT 1',
                [$email]
            );
            
            if (!$user) {
                error_log("❌ Usuario no encontrado en BD para email: {$email}");
                return false;
            }
            
            error_log("✅ Usuario encontrado en BD");
            error_log("📋 Columnas disponibles: " . implode(', ', array_keys($user)));
            
            // Verificar contraseña
            if (!password_verify($password, $user['password'])) {
                error_log("❌ Contraseña incorrecta");
                return false;
            }
            
            error_log("✅ Contraseña verificada correctamente");
            return $user;
            
        } catch (Exception $e) {
            error_log("💥 Error en authenticateUser(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear sesión de usuario - ADAPTADO
     */
    private function createUserSession($user, $remember = false)
    {
        try {
            error_log("🔐 Iniciando createUserSession...");
            
            // Regenerar ID de sesión por seguridad
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
                error_log("✅ Session ID regenerado: " . session_id());
            }
            
            // ✅ ADAPTADO: Usar columnas que SÍ existen en tu BD
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'] ?? $user['full_name'] ?? 'Usuario';
            $_SESSION['username'] = $user['username'] ?? '';
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            error_log("✅ Variables de sesión establecidas:");
            error_log("   - user_id: " . $_SESSION['user_id']);
            error_log("   - user_email: " . $_SESSION['user_email']);
            error_log("   - user_role: " . $_SESSION['user_role']);
            error_log("   - user_name: " . $_SESSION['user_name']);
            
            // Si "recordar sesión", extender tiempo de vida
            // ⚠️ IMPORTANTE: Esto debe hacerse ANTES de session_start()
            if ($remember) {
                $lifetime = 60 * 60 * 24 * 30; // 30 días
                // No llamar session_set_cookie_params aquí si la sesión ya está activa
                ini_set('session.gc_maxlifetime', $lifetime);
                error_log("✅ Sesión configurada para recordar (30 días)");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("💥 Error en createUserSession(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout()
    {
        try {
            // Registrar logout si hay usuario
            if (isset($_SESSION['user_id'])) {
                $this->logLogout($_SESSION['user_id']);
            }
            
            // Limpiar sesión
            $_SESSION = [];
            
            // Destruir cookie de sesión
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            // Destruir sesión
            session_destroy();
            
            // Iniciar nueva sesión para mensajes flash
            session_start();
            $this->setFlashMessage('Ha cerrado sesión correctamente', 'success');
            
            $this->redirect('/login');
            
        } catch (Exception $e) {
            error_log("Error en logout: " . $e->getMessage());
            $this->redirect('/login');
        }
    }
    
    /**
     * Mostrar formulario de recuperación de contraseña
     */
    public function showForgotPassword()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/admin/dashboard');
            return;
        }
        
        $data = [
            'page_title' => 'Recuperar Contraseña',
            'csrf_token' => $this->generateCSRFToken()
        ];
        
        $this->render('auth/forgot-password', $data, 'auth');
    }
    
    /**
     * Obtener URL de redirección según rol
     */
    private function getRedirectUrl($role)
    {
        $redirects = [
            'admin' => '/admin/dashboard',
            'super_admin' => '/admin/dashboard',
            'superadmin' => '/admin/dashboard',
            'manager' => '/admin/dashboard',
            'user' => '/admin/surveys'
        ];
        
        return $redirects[$role] ?? '/admin/dashboard';
    }
    
    /**
     * Actualizar último acceso - ADAPTADO (sin columna last_login)
     */
    private function updateLastLogin($userId)
    {
        try {
            // ✅ CORRECCIÓN: Actualizar updated_at en lugar de last_login
            $this->db->update(
                'users',
                ['updated_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$userId]
            );
            error_log("✅ Fecha de actualización guardada para user_id: {$userId}");
        } catch (Exception $e) {
            error_log("⚠️ No se pudo actualizar fecha: " . $e->getMessage());
            // No es crítico, continuar
        }
    }
    
    /**
     * Registrar intento de login fallido - SIN tabla security_logs
     */
    private function logFailedLogin($email)
    {
        // Solo registrar en error_log ya que no existe la tabla
        error_log("❌ Login fallido para: {$email} desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
    
    /**
     * Registrar login exitoso - SIN tabla security_logs
     */
    private function logSuccessfulLogin($userId)
    {
        // Solo registrar en error_log ya que no existe la tabla
        error_log("✅ Login exitoso para user_id: {$userId} desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
    
    /**
     * Registrar logout - SIN tabla security_logs
     */
    private function logLogout($userId)
    {
        // Solo registrar en error_log ya que no existe la tabla
        error_log("👋 Logout para user_id: {$userId}");
    }
    
    /**
     * Mostrar formulario básico de login (emergencia)
     */
    private function showBasicLoginForm()
    {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login - Sistema HERCO</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-5">
                        <div class="card shadow">
                            <div class="card-body p-5">
                                <h3 class="text-center mb-4">🏢 Sistema HERCO</h3>
                                <h5 class="text-center text-muted mb-4">Iniciar Sesión</h5>
                                
                                <?php if (!empty($_SESSION['flash_messages'])): ?>
                                    <?php foreach ($_SESSION['flash_messages'] as $msg): ?>
                                        <div class="alert alert-<?= $msg['type'] ?> alert-dismissible">
                                            <?= htmlspecialchars($msg['message']) ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php unset($_SESSION['flash_messages']); ?>
                                <?php endif; ?>
                                
                                <form method="POST" action="/login">
                                    <input type="hidden" name="csrf_token" value="<?= $this->generateCSRFToken() ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required autofocus>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                        <label class="form-check-label" for="remember">Recordar sesión</label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                                </form>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">Sistema HERCO v2.0</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit;
    }
}
