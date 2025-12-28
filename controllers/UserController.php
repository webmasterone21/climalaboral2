<?php
/**
 * Controlador de Usuarios - VERSIION CORREGIDA v2.0.2
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * CORRECCION CRITICA:
 * OK Metodo edit() simplificado sin dependencias problematicas
 * OK Mejor manejo de errores
 * OK Logging mejorado para debugging
 * 
 * @package EncuestasHERCO\Controllers
 * @version 2.0.2
 */

class UserController extends Controller
{
    private $userModel;
    private $activityLogModel;
    private $companyModel;
    
    /**
     * Inicializacion del controlador
     */
    protected function initialize()
    {
        // Requerir autenticacion
        $this->requireAuth();
        
        // Verificar permisos administrativos
        $this->requirePermission('manage_users');
        
        // Cargar modelo de usuarios
        try {
            if (!class_exists('User')) {
                throw new Exception('Modelo User no disponible');
            }
            $this->userModel = new User();
            
            // Modelos opcionales
            if (class_exists('ActivityLog')) {
                $this->activityLogModel = new ActivityLog();
            }
            
            if (class_exists('Company')) {
                $this->companyModel = new Company();
            }
            
        } catch (Exception $e) {
            error_log("ERROR Error inicializando UserController: " . $e->getMessage());
            $this->setFlashMessage(
                'Error de configuracion del sistema de usuarios. Contacte al administrador.', 
                'error'
            );
            $this->redirect('/admin/dashboard');
        }
        
        // Layout administrativo
        $this->defaultLayout = 'admin';
    }
    
    /**
     * Lista de usuarios con filtros avanzados
     */
    public function index()
    {
        try {
            // Obtener filtros de URL
            $filters = $this->getFilters();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = 20;
            
            // Construir condiciones de filtro
            $conditions = ['company_id = ?'];
            $params = [$this->user['company_id']];
            
            // Filtro por rol
            if (!empty($filters['role'])) {
                $conditions[] = 'role = ?';
                $params[] = $filters['role'];
            }
            
            // Filtro por estado
            if (!empty($filters['status'])) {
                $conditions[] = 'status = ?';
                $params[] = $filters['status'];
            }
            
            // Filtro por busqueda
            if (!empty($filters['search'])) {
                $conditions[] = '(name LIKE ? OR email LIKE ?)';
                $searchParam = "%{$filters['search']}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // Obtener usuarios con paginacion
            $whereClause = implode(' AND ', $conditions);
            $paginationResult = $this->userModel->paginate(
                $page, 
                $limit, 
                $whereClause, 
                $params,
                'created_at DESC'
            );
            
            // Estadisticas de usuarios
            $stats = $this->getUserStats();
            
            $data = [
                'users' => $paginationResult['data'],
                'pagination' => $paginationResult['pagination'],
                'stats' => $stats,
                'filters' => $filters,
                'roles' => $this->userModel->getRoles(),
                'statuses' => $this->userModel->getStatuses()
            ];
            
            $this->render('admin/users/index', $data);
            
        } catch (Exception $e) {
            error_log("ERROR Error en UserController::index: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar usuarios', 'error');
            $this->redirect('/admin/dashboard');
        }
    }
    
    /**
     * Mostrar formulario de creacion de usuario
     */
    public function create()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->store();
            }
            
            $data = [
                'roles' => $this->userModel->getRoles(),
                'statuses' => $this->userModel->getStatuses(),
                'departments' => $this->getDepartments()
            ];
            
            $this->render('admin/users/create', $data);
            
        } catch (Exception $e) {
            error_log("ERROR Error en UserController::create: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar formulario', 'error');
            $this->redirect('/admin/users');
        }
    }
    
    /**
     * Guardar nuevo usuario
     */
    public function store()
    {
        try {
            // Validar CSRF
            $this->validateCsrfToken();
            
            // Validar datos requeridos
            $requiredFields = ['name', 'email', 'password', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo {$field} es requerido");
                }
            }
            
            // Validar email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email invalido');
            }
            
            // Validar que el email no exista
            $existingUser = $this->userModel->findByCondition(
                'email = ?',
                [$_POST['email']]
            );
            
            if (!empty($existingUser)) {
                throw new Exception('El email ya esta registrado');
            }
            
            // Validar rol
            $allowedRoles = array_keys($this->userModel->getRoles());
            if (!in_array($_POST['role'], $allowedRoles)) {
                throw new Exception('Rol invalido');
            }
            
            // Validar contraseÃƒÂ±a
            if (strlen($_POST['password']) < 6) {
                throw new Exception('La contrasena debe tener al menos 6 caracteres');
            }
            
            if ($_POST['password'] !== $_POST['password_confirmation']) {
                throw new Exception('Las contrasenias no coinciden');
            }
            
            // Preparar datos del usuario
            $userData = [
                'company_id' => $this->user['company_id'],
                'name' => $this->sanitize($_POST['name']),
                'email' => strtolower(trim($_POST['email'])),
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role' => $_POST['role'],
                'department' => $this->sanitize($_POST['department'] ?? ''),
                'position' => $this->sanitize($_POST['position'] ?? ''),
                'phone' => $this->sanitize($_POST['phone'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
                'created_by' => $this->user['id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Crear usuario
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                throw new Exception('Error al crear usuario');
            }
            
            // Log de actividad
            $this->logActivity('user_created', 'Usuario creado', [
                'user_id' => $userId,
                'email' => $_POST['email'],
                'role' => $_POST['role']
            ]);
            
            $this->setFlashMessage('Usuario creado exitosamente', 'success');
            $this->redirect('/admin/users');
            
        } catch (Exception $e) {
            error_log("ERROR Error al crear usuario: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/users/create');
        }
    }
    
    /**
     * CORREGIDO: Editar usuario - Version simplificada y robusta
     * CORRECCION: Parametro cambio de $userId a $id para coincidir con :id en la ruta
     */
    public function edit($id)
    {
        error_log("DEBUG UserController::edit() llamado con id: " . $id);
        
        try {
            // Validar que id sea un numero
            if (!is_numeric($id) || $id <= 0) {
                error_log("ERROR id invalido: " . $id);
                throw new Exception('ID de usuario invalido');
            }
            
            // Si es POST, procesar actualizacion
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("DEBUG Procesando actualizacion de usuario");
                return $this->update($id);
            }
            
            // Buscar usuario
            error_log("DEBUG Buscando usuario con ID: " . $id);
            $user = $this->userModel->findById($id);
            
            if (!$user) {
                error_log("ERROR Usuario no encontrado con ID: " . $id);
                throw new Exception('Usuario no encontrado');
            }
            
            // Verificar que pertenezca a la misma empresa
            if ($user['company_id'] != $this->user['company_id']) {
                error_log("ERROR Usuario no pertenece a la empresa actual");
                throw new Exception('Usuario no encontrado');
            }
            
            error_log("OK Usuario encontrado: " . $user['email']);
            
            // Preparar datos para la vista
            $data = [
                'user' => $user,
                'roles' => $this->userModel->getRoles(),
                'statuses' => $this->userModel->getStatuses(),
                'departments' => $this->getDepartments(),
                'page_title' => 'Editar Usuario: ' . $user['name']
            ];
            
            error_log("DEBUG Renderizando vista: admin/users/edit");
            
            // Renderizar vista
            $this->render('admin/users/edit', $data);
            
        } catch (Exception $e) {
            error_log("ERROR Error en UserController::edit: " . $e->getMessage());
            error_log("DEBUG Stack trace: " . $e->getTraceAsString());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/users');
        }
    }
    
    /**
     * Actualizar usuario existente
     * CORRECCION: Parametro cambio de $userId a $id
     */
    public function update($id)
    {
        error_log("DEBUG UserController::update() llamado con id: " . $id);
        
        try {
            // Validar CSRF
            $this->validateCsrfToken();
            
            if (!is_numeric($id) || $id <= 0) {
                throw new Exception('ID de usuario invalido');
            }
            
            // Verificar que el usuario existe
            $user = $this->userModel->findById($id);
            if (!$user || $user['company_id'] != $this->user['company_id']) {
                throw new Exception('Usuario no encontrado');
            }
            
            // Validar campos requeridos
            $requiredFields = ['name', 'email', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field] ?? '')) {
                    throw new Exception("El campo {$field} es requerido");
                }
            }
            
            // Validar email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email invalido');
            }
            
            // Validar que el email no exista en otro usuario
            if ($_POST['email'] !== $user['email']) {
                $existingUser = $this->userModel->findByCondition(
                    'email = ? AND id != ?',
                    [$_POST['email'], $id]
                );
                
                if (!empty($existingUser)) {
                    throw new Exception('El email ya esta registrado');
                }
            }
            
            // Validar rol con logging detallado
            $allowedRoles = array_keys($this->userModel->getRoles());
            error_log("🔍 DEBUG Rol recibido del formulario: " . ($_POST['role'] ?? 'NULL'));
            error_log("🔍 DEBUG Roles permitidos: " . json_encode($allowedRoles));
            error_log("🔍 DEBUG Rol actual del usuario: " . ($user['role'] ?? 'NULL'));
            
            if (!in_array($_POST['role'], $allowedRoles)) {
                throw new Exception('Rol invalido');
            }
            
            // Preparar datos para actualizar
            // CORREGIDO: NO incluir updated_at ni updated_by (la BD los maneja automáticamente)
            $updateData = [
                'name' => $this->sanitize($_POST['name']),
                'email' => strtolower(trim($_POST['email'])),
                'role' => $_POST['role'],
                'department' => $this->sanitize($_POST['department'] ?? ''),
                'position' => $this->sanitize($_POST['position'] ?? ''),
                'phone' => $this->sanitize($_POST['phone'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // Logging para debug
            error_log("DEBUG Datos a actualizar: " . json_encode($updateData));
            
            // Si hay nueva contraseÃƒÂ±a, validar y hashear
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 6) {
                    throw new Exception('La contrasena debe tener al menos 6 caracteres');
                }
                
                if ($_POST['password'] !== $_POST['password_confirmation']) {
                    throw new Exception('Las contrasenias no coinciden');
                }
                
                $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            // Actualizar usuario
            $success = $this->userModel->update($id, $updateData);
            
            error_log("✅ DEBUG Update resultado: " . ($success ? 'EXITOSO' : 'FALLÓ'));
            
            if (!$success) {
                throw new Exception('Error al actualizar usuario');
            }
            
            // Verificar que el rol se guardó correctamente
            $updatedUser = $this->userModel->findById($id);
            error_log("🔍 DEBUG Rol después de update: " . ($updatedUser['role'] ?? 'NULL'));
            
            // Log de actividad
            $this->logActivity('user_updated', 'Usuario actualizado', [
                'user_id' => $id,
                'email' => $_POST['email'],
                'role' => $_POST['role']
            ]);
            
            $this->setFlashMessage('Usuario actualizado exitosamente', 'success');
            $this->redirect('/admin/users');
            
        } catch (Exception $e) {
            error_log("ERROR Error al actualizar usuario: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/users/' . $id . '/edit');
        }
    }
    
    /**
     * Eliminar usuario (soft delete)
     * CORRECCION: Parametro cambio de $userId a $id
     */
    public function destroy($id)
    {
        try {
            // Validar CSRF
            $this->validateCsrfToken();
            
            // Verificar que el usuario existe y pertenece a la misma empresa
            $user = $this->userModel->findById($id);
            
            if (!$user || $user['company_id'] != $this->user['company_id']) {
                throw new Exception('Usuario no encontrado');
            }
            
            // No permitir eliminar al propio usuario
            if ($id == $this->user['id']) {
                throw new Exception('No puedes eliminar tu propio usuario');
            }
            
            // Verificar si el usuario tiene dependencias
            if ($this->checkUserDependencies($id)) {
                // Cambiar estado a inactivo en lugar de eliminar
                $success = $this->userModel->update($id, [
                    'status' => 'inactive',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->user['id']
                ]);
                
                if (!$success) {
                    throw new Exception('Error al desactivar usuario');
                }
                
                $this->setFlashMessage('Usuario desactivado (tiene registros asociados)', 'warning');
            } else {
                // Eliminar usuario
                $success = $this->userModel->delete($id);
                
                if (!$success) {
                    throw new Exception('Error al eliminar usuario');
                }
                
                $this->setFlashMessage('Usuario eliminado exitosamente', 'success');
            }
            
            // Log de actividad
            $this->logActivity('user_deleted', 'Usuario eliminado/desactivado', [
                'user_id' => $id,
                'email' => $user['email']
            ]);
            
        } catch (Exception $e) {
            error_log("ERROR Error eliminando usuario: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
        }
        
        $this->redirect('/admin/users');
    }
    
    /**
     * Obtener filtros de la URL
     */
    private function getFilters()
    {
        return [
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'search' => trim($_GET['search'] ?? '')
        ];
    }
    
    /**
     * Obtener estadisticas de usuarios
     */
    private function getUserStats()
    {
        try {
            $stats = [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'admins' => 0,
                'managers' => 0,
                'users' => 0
            ];
            
            // Obtener todos los usuarios de la empresa
            $users = $this->userModel->findByCondition(
                'company_id = ?',
                [$this->user['company_id']]
            );
            
            $stats['total'] = count($users);
            
            foreach ($users as $user) {
                // Contar por estado
                if ($user['status'] === 'active') {
                    $stats['active']++;
                } else {
                    $stats['inactive']++;
                }
                
                // Contar por rol
                switch ($user['role']) {
                    case 'admin':
                    case 'super_admin':
                        $stats['admins']++;
                        break;
                    case 'manager':
                        $stats['managers']++;
                        break;
                    default:
                        $stats['users']++;
                        break;
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("ERROR Error obteniendo estadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'admins' => 0,
                'managers' => 0,
                'users' => 0
            ];
        }
    }
    
    /**
     * Verificar si el usuario tiene dependencias
     */
    private function checkUserDependencies($id)
    {
        try {
            // Verificar si el usuario creo encuestas
            if (class_exists('Survey')) {
                $surveyModel = new Survey();
                $surveys = $surveyModel->findByCondition(
                    'created_by = ?',
                    [$id]
                );
                
                if (!empty($surveys)) {
                    return true;
                }
            }
            
            // Verificar si el usuario creo participantes
            if (class_exists('Participant')) {
                $participantModel = new Participant();
                $participants = $participantModel->findByCondition(
                    'created_by = ?',
                    [$id]
                );
                
                if (!empty($participants)) {
                    return true;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("ERROR Error verificando dependencias: " . $e->getMessage());
            return true; // En caso de error, asumir que tiene dependencias
        }
    }
    
    /**
     * Obtener departamentos unicos
     */
    /**
     * Obtener lista de departamentos
     * CORREGIDO: Busca en la tabla departments en lugar de users
     */
    private function getDepartments()
    {
        try {
            // Verificar si existe la tabla departments
            $sql = "SHOW TABLES LIKE 'departments'";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Buscar en tabla departments
                $sql = "SELECT id, name FROM departments 
                        WHERE company_id = ? 
                        ORDER BY name ASC";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$this->user['company_id']]);
                $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return $departments;
            }
            
            // Fallback: buscar departamentos únicos en users
            $users = $this->userModel->findByCondition(
                'company_id = ? AND department IS NOT NULL AND department != ""',
                [$this->user['company_id']]
            );
            
            $departments = [];
            foreach ($users as $user) {
                if (!empty($user['department'])) {
                    $departments[] = [
                        'id' => null,
                        'name' => $user['department']
                    ];
                }
            }
            
            // Eliminar duplicados basados en el nombre
            $unique = [];
            foreach ($departments as $dept) {
                $unique[$dept['name']] = $dept;
            }
            
            return array_values($unique);
            
        } catch (Exception $e) {
            error_log("ERROR Error obteniendo departamentos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Logging de actividad
     */
    protected function logActivity($action, $description = '', $metadata = [])
    {
        try {
            if ($this->activityLogModel) {
                // CORREGIDO: No incluir created_at (la BD lo maneja automáticamente)
                $this->activityLogModel->create([
                    'user_id' => $this->user['id'],
                    'action' => $action,
                    'description' => $description,
                    'metadata' => json_encode($metadata)
                ]);
            }
        } catch (Exception $e) {
            error_log("ERROR Error logging actividad: " . $e->getMessage());
        }
    }
    
    /**
     * Metodo delete (alias de destroy para consistencia con rutas)
     * CORRECCION: Parametro cambio de $userId a $id
     */
    public function delete($id)
    {
        return $this->destroy($id);
    }
}
?>
