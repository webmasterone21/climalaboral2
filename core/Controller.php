<?php
/**
 * Controlador Base
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * Clase base para todos los controladores del sistema
 * Proporciona funcionalidades comunes y métodos helper
 * 
 * @package Core
 * @version 2.0.0
 * @author Sistema HERCO
 */

abstract class Controller
{
    /**
     * @var Database Instancia de la base de datos
     */
    protected $db;
    
    /**
     * @var View Motor de vistas
     */
    protected $view;
    
    /**
     * @var array Datos del usuario autenticado
     */
    protected $user;
    
    /**
     * @var array Datos para pasar a las vistas
     */
    protected $data = [];
    
    /**
     * @var array Parámetros de la ruta
     */
    protected $params = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Obtener instancia de la base de datos
        $this->db = Database::getInstance();
        
        // Inicializar motor de vistas
        $this->view = new View();
        
        // Cargar datos del usuario si está autenticado
        $this->loadUser();
        
        // Ejecutar método de inicialización del controlador hijo
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }
    
    // ==========================================
    // MÉTODOS HELPER PARA PARÁMETROS
    // ==========================================
    
    /**
     * Obtener parámetro de $_GET
     * 
     * @param string $key Clave del parámetro
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Obtener parámetro de $_POST
     * 
     * @param string $key Clave del parámetro
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Obtener parámetro de la ruta
     * 
     * @param string $key Clave del parámetro
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function getParam($key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
    
    /**
     * Obtener parámetro de $_REQUEST (GET, POST o COOKIE)
     * 
     * @param string $key Clave del parámetro
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function request($key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }
    
    /**
     * Establecer parámetros de ruta
     * 
     * @param array $params Parámetros
     * @return void
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
    
    /**
     * Obtener todos los parámetros GET
     * 
     * @return array
     */
    protected function getAllGet()
    {
        return $_GET;
    }
    
    /**
     * Obtener todos los parámetros POST
     * 
     * @return array
     */
    protected function getAllPost()
    {
        return $_POST;
    }
    
    /**
     * Verificar si existe un parámetro GET
     * 
     * @param string $key Clave del parámetro
     * @return bool
     */
    protected function hasGet($key)
    {
        return isset($_GET[$key]);
    }
    
    /**
     * Verificar si existe un parámetro POST
     * 
     * @param string $key Clave del parámetro
     * @return bool
     */
    protected function hasPost($key)
    {
        return isset($_POST[$key]);
    }
    
    /**
     * Obtener método de la solicitud HTTP
     * 
     * @return string
     */
    protected function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * Verificar si la solicitud es POST
     * 
     * @return bool
     */
    protected function isPost()
    {
        return $this->getMethod() === 'POST';
    }
    
    /**
     * Verificar si la solicitud es GET
     * 
     * @return bool
     */
    protected function isGet()
    {
        return $this->getMethod() === 'GET';
    }
    
    /**
     * Verificar si la solicitud es AJAX
     * 
     * @return bool
     */
    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Alias de isAjax
     * 
     * @return bool
     */
    protected function isAjaxRequest()
    {
        return $this->isAjax();
    }
    
    /**
     * Verificar método HTTP específico
     * 
     * @param string $method Método a verificar
     * @return bool
     */
    protected function isMethod($method)
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }
    
    // ==========================================
    // AUTENTICACIÓN Y AUTORIZACIÓN
    // ==========================================
    
/**
 * Cargar datos del usuario autenticado
 * 
 * @return void
 */
private function loadUser()
{
    if (isset($_SESSION['user_id'])) {
        try {
            // ✅ QUERY CORREGIDA - Sin JOIN problemático
            $sql = "SELECT * FROM users WHERE id = ? AND status = 'active'";
            
            // Usar el método fetch de Database
            $this->user = $this->db->fetch($sql, [$_SESSION['user_id']]);
            
            if (!$this->user) {
                // Usuario no encontrado o inactivo
                $this->logout();
                return;
            }
            
            // ✅ Asegurar que company_id existe y tiene valor
            if (!isset($this->user['company_id']) || empty($this->user['company_id'])) {
                $this->user['company_id'] = 1;
                
                // Actualizar en BD
                try {
                    $updateSql = "UPDATE users SET company_id = 1 WHERE id = ?";
                    $this->db->execute($updateSql, [$this->user['id']]);
                } catch (Exception $e) {
                    error_log("Error actualizando company_id: " . $e->getMessage());
                }
            }
            
            // ✅ Cargar nombre de la empresa (opcional, sin JOIN problemático)
            if (isset($this->user['company_id'])) {
                try {
                    $companySql = "SELECT name FROM companies WHERE id = ?";
                    $company = $this->db->fetch($companySql, [$this->user['company_id']]);
                    if ($company) {
                        $this->user['company_name'] = $company['name'];
                    }
                } catch (Exception $e) {
                    // Si falla, no es crítico
                    error_log("Error cargando empresa: " . $e->getMessage());
                }
            }
            
            // Agregar datos del usuario a las vistas
            $this->data['current_user'] = $this->user;
            
        } catch (Exception $e) {
            error_log("Error cargando usuario: " . $e->getMessage());
            $this->user = null;
        }
    }
}
    
    /**
     * Requerir autenticación
     * 
     * @return void
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            if ($this->isAjax()) {
                $this->jsonError('No autenticado', 401);
            } else {
                $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
                $this->redirect('/login');
            }
            exit;
        }
    }
    
    /**
     * Verificar si el usuario está autenticado
     * 
     * @return bool
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && !empty($this->user);
    }
    
    /**
     * Verificar si el usuario es administrador
     * 
     * @return bool
     */
    protected function isAdmin()
    {
        return $this->user && in_array($this->user['role'], ['super_admin', 'admin']);
    }
    
    /**
     * Verificar si el usuario es super administrador
     * 
     * @return bool
     */
    protected function isSuperAdmin()
    {
        return $this->user && $this->user['role'] === 'super_admin';
    }
    
    /**
     * Requerir rol de administrador
     * 
     * @return void
     */
    protected function requireAdmin()
    {
        $this->requireAuth();
        
        if (!$this->isAdmin()) {
            if ($this->isAjax()) {
                $this->jsonError('Acceso denegado', 403);
            } else {
                $this->setFlashMessage('No tiene permisos para acceder a esta sección', 'error');
                $this->redirect('/admin/dashboard');
            }
            exit;
        }
    }
    
    /**
     * Requerir rol específico
     * 
     * @param string|array $roles Rol o roles permitidos
     * @return void
     */
    protected function requireRole($roles)
    {
        $this->requireAuth();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($this->user['role'], $roles)) {
            if ($this->isAjax()) {
                $this->jsonError('Acceso denegado', 403);
            } else {
                $this->setFlashMessage('No tiene permisos suficientes', 'error');
                $this->redirect('/admin/dashboard');
            }
            exit;
        }
    }

    /**
 * Requerir permiso específico
 * 
 * @param string $permission Permiso requerido
 * @param bool $redirect Si debe redirigir en caso de fallo
 * @return bool
 */
protected function requirePermission($permission, $redirect = true)
{
    // Primero verificar autenticación
    $this->requireAuth();
    
    // Super admin tiene todos los permisos
    if ($this->isSuperAdmin()) {
        return true;
    }
    
    // Admin regular tiene todos los permisos
    if ($this->isAdmin()) {
        return true;
    }
    
    // Verificar permiso específico del usuario
    $hasPermission = $this->checkUserPermission($permission);
    
    if (!$hasPermission && $redirect) {
        if ($this->isAjax()) {
            $this->jsonError('No tiene permisos para esta acción', 403);
        } else {
            $this->setFlashMessage('No tiene permisos para acceder a esta sección', 'error');
            $this->redirect('/admin/dashboard');
        }
        exit;
    }
    
    return $hasPermission;
}

/**
 * Verificar si el usuario tiene un permiso específico
 * 
 * @param string $permission Nombre del permiso
 * @return bool
 */
protected function checkUserPermission($permission)
{
    if (!$this->user) {
        return false;
    }
    
    // Verificar en sesión primero (caché)
    if (isset($_SESSION['user_permissions'])) {
        return in_array($permission, $_SESSION['user_permissions']);
    }
    
    // Consultar en base de datos
    try {
        $sql = "SELECT COUNT(*) as has_permission
                FROM user_permissions up
                INNER JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = ? 
                AND p.permission_key = ?
                AND up.is_active = 1";
        
        $result = $this->fetchOne($sql, [$this->user['id'], $permission]);
        
        if ($result) {
            return intval($result['has_permission']) > 0;
        }
        
        return false;
        
    } catch (Exception $e) {
        // Si hay error en la consulta o las tablas no existen, 
        // permitir acceso para usuarios admin
        error_log("Error verificando permiso: " . $e->getMessage());
        return $this->isAdmin();
    }
}
    
    /**
     * Cerrar sesión
     * 
     * @return void
     */
    protected function logout()
    {
        session_destroy();
        $this->redirect('/login');
    }
    
    // ==========================================
    // PROTECCIÓN CSRF
    // ==========================================
    
    /**
     * Generar token CSRF
     * 
     * @return string
     */
    protected function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Obtener token CSRF
     * 
     * @return string
     */
    protected function getCSRFToken()
    {
        return $_SESSION['csrf_token'] ?? $this->generateCSRFToken();
    }
    
    /**
     * Validar token CSRF
     * 
     * @param string|null $token Token a validar (si es null, se obtiene de POST)
     * @return bool
     */
    protected function validateCSRF($token = null)
    {
        if ($token === null) {
            $token = $this->post('csrf_token');
        }
        
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Validar token CSRF o lanzar excepción
     * 
     * @throws Exception
     * @return void
     */
    protected function validateCsrfToken()
    {
        if (!$this->validateCSRF()) {
            throw new Exception('Token de seguridad inválido');
        }
    }
    
    // ==========================================
    // MENSAJES FLASH
    // ==========================================
    
    /**
     * Establecer mensaje flash
     * 
     * @param string $message Mensaje
     * @param string $type Tipo (success, error, warning, info)
     * @return void
     */
    protected function setFlashMessage($message, $type = 'info')
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        
        $_SESSION['flash_messages'][] = [
            'message' => $message,
            'type' => $type
        ];
    }
    
    /**
     * Obtener y limpiar mensajes flash
     * 
     * @return array
     */
    protected function getFlashMessages()
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
    
    /**
     * Agregar mensaje flash a los datos de vista
     * 
     * @return void
     */
    protected function addFlashToView()
    {
        $this->data['flash_messages'] = $this->getFlashMessages();
    }
    
    // ==========================================
    // RENDERIZADO Y RESPUESTAS
    // ==========================================
    
    /**
     * Renderizar vista
     * 
     * @param string $template Plantilla (ej: 'admin/dashboard')
     * @param array $data Datos adicionales
     * @param string|bool $layout Layout a usar ('app', 'admin', 'auth', false para sin layout)
     * @return void
     */
    protected function render($template, $data = [], $layout = 'app')
    {
        // Agregar mensajes flash automáticamente
        $this->addFlashToView();
        
        // Agregar token CSRF
        $this->data['csrf_token'] = $this->getCSRFToken();
        
        // Combinar datos
        $viewData = array_merge($this->data, $data);
        
        // Renderizar vista
        $this->view->render($template, $viewData, $layout);
    }
    
    /**
     * Renderizar vista sin layout
     * 
     * @param string $template Plantilla
     * @param array $data Datos
     * @return string HTML renderizado
     */
    protected function renderPartial($template, $data = [])
    {
        $viewData = array_merge($this->data, $data);
        return $this->view->renderPartial($template, $viewData);
    }
    
    /**
     * Responder con JSON
     * 
     * @param array $data Datos
     * @param int $statusCode Código de estado HTTP
     * @return void
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Responder con error JSON
     * 
     * @param string $message Mensaje de error
     * @param int $statusCode Código de estado HTTP
     * @param array $details Detalles adicionales
     * @return void
     */
    protected function jsonError($message, $statusCode = 400, $details = [])
    {
        $response = [
            'success' => false,
            'error' => $message,
            'status_code' => $statusCode
        ];
        
        if (!empty($details)) {
            $response['details'] = $details;
        }
        
        $this->jsonResponse($response, $statusCode);
    }
    
    /**
     * Responder con éxito JSON
     * 
     * @param mixed $data Datos
     * @param string $message Mensaje de éxito
     * @return void
     */
    protected function jsonSuccess($data = null, $message = 'Operación exitosa')
    {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->jsonResponse($response);
    }
    
    /**
     * Redireccionar
     * 
     * @param string $url URL de destino
     * @param int $code Código de respuesta
     * @return void
     */
    protected function redirect($url, $code = 302)
    {
        // Si no es URL absoluta, hacerla relativa a la base
        if (!preg_match('/^https?:\/\//', $url)) {
            if ($url[0] !== '/') {
                $url = '/' . $url;
            }
            $url = $this->getBaseUrl() . $url;
        }
        
        http_response_code($code);
        header("Location: " . $url);
        exit;
    }
    
    /**
     * Obtener URL base de la aplicación
     * 
     * @return string
     */
    protected function getBaseUrl()
    {
        // Detectar protocolo
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        
        // Obtener host
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Obtener directorio base (si la app no está en la raíz)
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = str_replace('\\', '/', dirname($scriptName));
        
        if ($basePath === '/') {
            $basePath = '';
        }
        
        return $protocol . '://' . $host . $basePath;
    }
    
    // ==========================================
    // VALIDACIÓN
    // ==========================================
    
    /**
     * Validar datos
     * 
     * @param array $data Datos a validar
     * @param array $rules Reglas de validación
     * @return array Errores encontrados (vacío si todo es válido)
     */
    protected function validate($data, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value) && $value !== '0') {
                            $errors[$field][] = "El campo {$field} es requerido";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "El campo {$field} debe ser un email válido";
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < $ruleValue) {
                            $errors[$field][] = "El campo {$field} debe tener al menos {$ruleValue} caracteres";
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > $ruleValue) {
                            $errors[$field][] = "El campo {$field} no debe exceder {$ruleValue} caracteres";
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = "El campo {$field} debe ser numérico";
                        }
                        break;
                        
                    case 'integer':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                            $errors[$field][] = "El campo {$field} debe ser un número entero";
                        }
                        break;
                        
                    case 'url':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[$field][] = "El campo {$field} debe ser una URL válida";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitizar entrada
     * 
     * @param mixed $data Datos a sanitizar
     * @return mixed
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    // ==========================================
    // LOGGING Y ACTIVIDAD
    // ==========================================
    
    /**
     * Registrar actividad del usuario
     * 
     * @param string $action Acción realizada
     * @param string $description Descripción
     * @param array $metadata Metadatos adicionales
     * @return void
     */
    protected function logActivity($action, $description = '', $metadata = [])
    {
        if (!$this->user) {
            return;
        }
        
        try {
            $sql = "INSERT INTO activity_logs 
                    (user_id, action, description, metadata, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            // Usar el método execute de Database
            $this->db->execute($sql, [
                $this->user['id'],
                $action,
                $description,
                json_encode($metadata),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
        } catch (Exception $e) {
            error_log("Error registrando actividad: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar error en log
     * 
     * @param string $message Mensaje de error
     * @param array $context Contexto adicional
     * @return void
     */
    protected function logError($message, $context = [])
    {
        $logMessage = $message;
        
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context);
        }
        
        error_log($logMessage);
    }
    
    // ==========================================
    // UTILIDADES
    // ==========================================
    
    /**
     * Paginación
     * 
     * @param int $total Total de elementos
     * @param int $perPage Elementos por página
     * @param int $currentPage Página actual
     * @return array Datos de paginación
     */
    protected function paginate($total, $perPage = 20, $currentPage = 1)
    {
        $totalPages = ceil($total / $perPage);
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $perPage;
        
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages
        ];
    }
    
    /**
     * Subir archivo
     * 
     * @param array $file Archivo de $_FILES
     * @param string $destination Directorio de destino
     * @param array $allowedTypes Tipos MIME permitidos
     * @param int $maxSize Tamaño máximo en bytes
     * @return array|false Información del archivo o false en error
     */
    protected function uploadFile($file, $destination, $allowedTypes = [], $maxSize = 5242880)
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return false;
        }
        
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Verificar tamaño
        if ($file['size'] > $maxSize) {
            return false;
        }
        
        // Verificar tipo MIME
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                return false;
            }
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $filepath = rtrim($destination, '/') . '/' . $filename;
        
        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return false;
        }
        
        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => $file['size'],
            'type' => $file['type']
        ];
    }
    
    // ==========================================
    // MÉTODOS HELPER PARA BASE DE DATOS
    // ==========================================
    
    /**
     * Ejecutar consulta y obtener un registro
     * 
     * @param string $sql SQL query
     * @param array $params Parámetros
     * @return array|false
     */
    protected function fetchOne($sql, $params = [])
    {
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Ejecutar consulta y obtener todos los registros
     * 
     * @param string $sql SQL query
     * @param array $params Parámetros
     * @return array
     */
    protected function fetchAll($sql, $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Ejecutar consulta y obtener un valor
     * 
     * @param string $sql SQL query
     * @param array $params Parámetros
     * @return mixed
     */
    protected function fetchColumn($sql, $params = [])
    {
        return $this->db->fetchColumn($sql, $params);
    }
    
    /**
     * Ejecutar consulta de escritura
     * 
     * @param string $sql SQL query
     * @param array $params Parámetros
     * @return bool
     */
    protected function execute($sql, $params = [])
    {
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Obtener conexión PDO directa (para casos especiales)
     * 
     * @return PDO
     */
    protected function getConnection()
    {
        return $this->db->getConnection();
    }
    
    /**
     * Iniciar transacción
     * 
     * @return bool
     */
    protected function beginTransaction()
    {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     * 
     * @return bool
     */
    protected function commit()
    {
        return $this->db->commit();
    }
    
    /**
     * Revertir transacción
     * 
     * @return bool
     */
    protected function rollback()
    {
        return $this->db->rollback();
    }
}
