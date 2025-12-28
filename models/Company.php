<?php
/**
 * Modelo Company - Gestión de Empresas
 * models/Company.php
 * 
 * VERSIÓN CORREGIDA v2.0.2
 * - Constructor con parámetro opcional
 * - Usa métodos correctos de Database (fetch, fetchAll, fetchColumn)
 * - Compatible con Database.php del sistema
 */

class Company extends Model {
    protected $table = 'companies';
    protected $fillable = [
        'name', 'logo', 'description', 'contact_email', 'contact_phone',
        'address', 'website', 'industry', 'employee_count', 'status'
    ];
    
    // Estados válidos
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    
    /**
     * Constructor del modelo Company
     * ✅ CORREGIDO: Acepta parámetro opcional para compatibilidad
     */
    public function __construct($database = null) {
        // Si no se proporciona conexión, obtener instancia global
        if ($database === null) {
            $database = Database::getInstance()->getConnection();
        }
        parent::__construct($database);
    }
    
    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================
    
    /**
     * Crear nueva empresa
     */
    public function createCompany($data) {
        try {
            // Validar datos
            $errors = $this->validateCompanyData($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Preparar datos
            $companyData = $this->prepareCompanyData($data);
            
            // Crear empresa
            $companyId = $this->create($companyData);
            
            if ($companyId) {
                $this->logActivity('company_created', 'company', $companyId, 
                    "Empresa creada: {$companyData['name']}");
                
                return [
                    'success' => true,
                    'company_id' => $companyId,
                    'message' => 'Empresa creada exitosamente'
                ];
            }
            
            return ['success' => false, 'errors' => ['general' => 'Error al crear la empresa']];
            
        } catch (Exception $e) {
            error_log('Company creation error: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Error interno del servidor']];
        }
    }
    
    /**
     * Actualizar empresa existente
     */
    public function updateCompany($companyId, $data) {
        try {
            // Verificar que la empresa existe
            $existingCompany = $this->find($companyId);
            if (!$existingCompany) {
                return ['success' => false, 'errors' => ['general' => 'Empresa no encontrada']];
            }
            
            // Validar datos
            $errors = $this->validateCompanyData($data, $companyId);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Preparar datos
            $companyData = $this->prepareCompanyData($data);
            
            // Actualizar
            $success = $this->update($companyId, $companyData);
            
            if ($success) {
                $this->logActivity('company_updated', 'company', $companyId, 
                    "Empresa actualizada: {$companyData['name']}");
                
                return ['success' => true, 'message' => 'Empresa actualizada exitosamente'];
            }
            
            return ['success' => false, 'errors' => ['general' => 'Error al actualizar la empresa']];
            
        } catch (Exception $e) {
            error_log('Update error: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Error interno del servidor']];
        }
    }
    
    /**
     * Eliminar empresa (cambiar status a inactive)
     */
    public function deleteCompany($companyId, $force = false) {
        try {
            $company = $this->find($companyId);
            if (!$company) {
                return ['success' => false, 'message' => 'Empresa no encontrada'];
            }
            
            // Verificar si tiene encuestas asociadas
            $surveyCount = $this->getSurveyCount($companyId);
            
            if ($surveyCount > 0 && !$force) {
                return [
                    'success' => false, 
                    'message' => 'No se puede eliminar una empresa con encuestas asociadas.',
                    'survey_count' => $surveyCount
                ];
            }
            
            if ($force) {
                // Eliminación física (solo administradores)
                if (!$this->isAdmin()) {
                    return ['success' => false, 'message' => 'Solo administradores pueden eliminar permanentemente'];
                }
                
                $success = $this->delete($companyId);
                $action = 'company_deleted_permanently';
                $message = 'Empresa eliminada permanentemente';
            } else {
                // Soft delete - cambiar status
                $success = $this->update($companyId, ['status' => self::STATUS_INACTIVE]);
                $action = 'company_deactivated';
                $message = 'Empresa desactivada exitosamente';
            }
            
            if ($success) {
                $this->logActivity($action, 'company', $companyId, 
                    "Empresa eliminada: {$company['name']}");
                
                return ['success' => true, 'message' => $message];
            }
            
            return ['success' => false, 'message' => 'Error al eliminar la empresa'];
            
        } catch (Exception $e) {
            error_log('Delete error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    // ==========================================
    // CONSULTAS ESPECIALIZADAS
    // ==========================================
    
    /**
     * Obtener todas las empresas activas
     */
    public function getActiveCompanies($includeStats = false) {
        try {
            $sql = "SELECT c.*";
            
            if ($includeStats) {
                $sql .= ", (SELECT COUNT(*) FROM surveys s WHERE s.company_id = c.id) as total_surveys,
                         (SELECT COUNT(*) FROM departments d WHERE d.company_id = c.id) as total_departments";
            }
            
            $sql .= " FROM companies c WHERE c.status = 'active' ORDER BY c.name ASC";
            
            return $this->db->fetchAll($sql);
            
        } catch (Exception $e) {
            error_log('Get active companies error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todas las empresas con filtros
     */
    public function getAllCompanies($filters = []) {
        try {
            $sql = "SELECT c.*,
                           (SELECT COUNT(*) FROM surveys s WHERE s.company_id = c.id) as total_surveys,
                           (SELECT COUNT(*) FROM departments d WHERE d.company_id = c.id) as total_departments
                    FROM companies c
                    WHERE 1=1";
            
            $params = [];
            
            // Filtros
            if (!empty($filters['status'])) {
                $sql .= " AND c.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['industry'])) {
                $sql .= " AND c.industry = ?";
                $params[] = $filters['industry'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (c.name LIKE ? OR c.description LIKE ? OR c.industry LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['employee_count_min'])) {
                $sql .= " AND c.employee_count >= ?";
                $params[] = $filters['employee_count_min'];
            }
            
            if (!empty($filters['employee_count_max'])) {
                $sql .= " AND c.employee_count <= ?";
                $params[] = $filters['employee_count_max'];
            }
            
            // Ordenamiento
            $orderBy = $filters['order_by'] ?? 'name';
            $orderDir = $filters['order_dir'] ?? 'ASC';
            $sql .= " ORDER BY c.{$orderBy} {$orderDir}";
            
            // Paginación
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT " . (int)$filters['limit'];
                
                if (!empty($filters['offset'])) {
                    $sql .= " OFFSET " . (int)$filters['offset'];
                }
            }
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log('Get all companies error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener empresa con estadísticas completas
     */
    public function getCompanyWithStats($companyId) {
        try {
            $sql = "SELECT c.*,
                           (SELECT COUNT(*) FROM surveys s WHERE s.company_id = c.id) as total_surveys,
                           (SELECT COUNT(*) FROM surveys s WHERE s.company_id = c.id AND s.status = 'active') as active_surveys,
                           (SELECT COUNT(*) FROM departments d WHERE d.company_id = c.id) as total_departments,
                           (SELECT COUNT(*) FROM participants p 
                            JOIN surveys s ON p.survey_id = s.id 
                            WHERE s.company_id = c.id) as total_participants,
                           (SELECT COUNT(*) FROM participants p 
                            JOIN surveys s ON p.survey_id = s.id 
                            WHERE s.company_id = c.id AND p.status = 'completed') as completed_responses
                    FROM companies c
                    WHERE c.id = ?";
            
            return $this->db->fetch($sql, [$companyId]);
            
        } catch (Exception $e) {
            error_log('Get company with stats error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar empresas por nombre
     */
    public function searchCompanies($query, $limit = 10) {
        try {
            $sql = "SELECT c.id, c.name, c.industry, c.employee_count
                    FROM companies c
                    WHERE c.status = 'active' 
                    AND (c.name LIKE ? OR c.industry LIKE ?)
                    ORDER BY c.name ASC
                    LIMIT ?";
            
            $searchTerm = '%' . $query . '%';
            
            return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $limit]);
            
        } catch (Exception $e) {
            error_log('Search companies error: ' . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // GESTIÓN DE DEPARTAMENTOS
    // ==========================================
    
    /**
     * Obtener departamentos de una empresa
     */
    public function getDepartments($companyId) {
        try {
            $sql = "SELECT d.*,
                           (SELECT COUNT(*) FROM participants p WHERE p.department_id = d.id) as total_participants
                    FROM departments d
                    WHERE d.company_id = ? AND d.status = 'active'
                    ORDER BY d.order_position ASC, d.name ASC";
            
            return $this->db->fetchAll($sql, [$companyId]);
            
        } catch (Exception $e) {
            error_log('Get departments error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear departamento
     */
    public function createDepartment($companyId, $data) {
        try {
            // Validar que la empresa existe
            $company = $this->find($companyId);
            if (!$company) {
                return ['success' => false, 'message' => 'Empresa no encontrada'];
            }
            
            // Validar datos del departamento
            $errors = $this->validateDepartmentData($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Preparar datos
            $departmentData = [
                'company_id' => $companyId,
                'name' => trim($data['name']),
                'description' => trim($data['description'] ?? '') ?: null,
                'manager_name' => trim($data['manager_name'] ?? '') ?: null,
                'manager_email' => trim($data['manager_email'] ?? '') ?: null,
                'employee_count' => isset($data['employee_count']) ? (int) $data['employee_count'] : 0,
                'order_position' => isset($data['order_position']) ? (int) $data['order_position'] : 0,
                'status' => 'active'
            ];
            
            $departmentId = $this->db->insert('departments', $departmentData);
            
            if ($departmentId) {
                $this->logActivity('department_created', 'department', $departmentId,
                    "Departamento creado: {$departmentData['name']} en empresa {$company['name']}");
                
                return [
                    'success' => true,
                    'department_id' => $departmentId,
                    'message' => 'Departamento creado exitosamente'
                ];
            }
            
            return ['success' => false, 'message' => 'Error al crear el departamento'];
            
        } catch (Exception $e) {
            error_log('Create department error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar departamento
     */
    public function updateDepartment($departmentId, $data) {
        try {
            $errors = $this->validateDepartmentData($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            $departmentData = [
                'name' => trim($data['name']),
                'description' => trim($data['description'] ?? '') ?: null,
                'manager_name' => trim($data['manager_name'] ?? '') ?: null,
                'manager_email' => trim($data['manager_email'] ?? '') ?: null,
                'employee_count' => isset($data['employee_count']) ? (int) $data['employee_count'] : 0,
                'order_position' => isset($data['order_position']) ? (int) $data['order_position'] : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $success = $this->db->update('departments', $departmentData, 'id = ?', [$departmentId]);
            
            if ($success) {
                $this->logActivity('department_updated', 'department', $departmentId,
                    "Departamento actualizado: {$departmentData['name']}");
                
                return ['success' => true, 'message' => 'Departamento actualizado exitosamente'];
            }
            
            return ['success' => false, 'message' => 'Error al actualizar el departamento'];
            
        } catch (Exception $e) {
            error_log('Update department error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Eliminar departamento
     */
    public function deleteDepartment($departmentId) {
        try {
            $participantCount = $this->db->count('participants', 'department_id = ?', [$departmentId]);
            
            if ($participantCount > 0) {
                return [
                    'success' => false, 
                    'message' => 'No se puede eliminar un departamento con participantes asociados'
                ];
            }
            
            $success = $this->db->delete('departments', 'id = ?', [$departmentId]);
            
            if ($success) {
                $this->logActivity('department_deleted', 'department', $departmentId, "Departamento eliminado");
                return ['success' => true, 'message' => 'Departamento eliminado exitosamente'];
            }
            
            return ['success' => false, 'message' => 'Error al eliminar el departamento'];
            
        } catch (Exception $e) {
            error_log('Delete department error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    // ==========================================
    // ESTADÍSTICAS Y REPORTES
    // ==========================================
    
    /**
     * Obtener estadísticas generales de empresas
     */
    public function getCompanyStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_companies,
                        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_companies,
                        COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_companies,
                        AVG(employee_count) as avg_employee_count,
                        SUM(employee_count) as total_employees
                    FROM companies";
            
            return $this->db->fetch($sql);
            
        } catch (Exception $e) {
            error_log('Get company stats error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener industrias más comunes
     */
    public function getTopIndustries($limit = 10) {
        try {
            $sql = "SELECT industry, COUNT(*) as company_count, SUM(employee_count) as total_employees
                    FROM companies 
                    WHERE status = 'active' AND industry IS NOT NULL
                    GROUP BY industry
                    ORDER BY company_count DESC, total_employees DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$limit]);
            
        } catch (Exception $e) {
            error_log('Get top industries error: ' . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // VALIDACIONES
    // ==========================================
    
    /**
     * Validar datos de empresa
     */
    private function validateCompanyData($data, $companyId = null) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre de la empresa es requerido';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'] = 'El nombre no puede tener más de 100 caracteres';
        } else {
            if ($this->nameExists($data['name'], $companyId)) {
                $errors['name'] = 'Ya existe una empresa con este nombre';
            }
        }
        
        if (!empty($data['contact_email']) && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = 'Email de contacto inválido';
        }
        
        if (!empty($data['contact_phone']) && strlen($data['contact_phone']) > 20) {
            $errors['contact_phone'] = 'El teléfono no puede tener más de 20 caracteres';
        }
        
        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors['website'] = 'URL del sitio web inválida';
        }
        
        if (!empty($data['employee_count']) && (!is_numeric($data['employee_count']) || $data['employee_count'] < 0)) {
            $errors['employee_count'] = 'El número de empleados debe ser un número positivo';
        }
        
        if (!empty($data['description']) && strlen($data['description']) > 1000) {
            $errors['description'] = 'La descripción no puede tener más de 1000 caracteres';
        }
        
        return $errors;
    }
    
    /**
     * Validar datos de departamento
     */
    private function validateDepartmentData($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre del departamento es requerido';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'] = 'El nombre no puede tener más de 100 caracteres';
        }
        
        if (!empty($data['manager_email']) && !filter_var($data['manager_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['manager_email'] = 'Email del manager inválido';
        }
        
        if (!empty($data['employee_count']) && (!is_numeric($data['employee_count']) || $data['employee_count'] < 0)) {
            $errors['employee_count'] = 'El número de empleados debe ser un número positivo';
        }
        
        return $errors;
    }
    
    // ==========================================
    // MÉTODOS AUXILIARES
    // ==========================================
    
    /**
     * Preparar datos para inserción/actualización
     */
    private function prepareCompanyData($data) {
        $companyData = [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? '') ?: null,
            'contact_email' => trim($data['contact_email'] ?? '') ?: null,
            'contact_phone' => trim($data['contact_phone'] ?? '') ?: null,
            'address' => trim($data['address'] ?? '') ?: null,
            'website' => trim($data['website'] ?? '') ?: null,
            'industry' => trim($data['industry'] ?? '') ?: null,
            'employee_count' => isset($data['employee_count']) ? (int) $data['employee_count'] : null,
            'status' => isset($data['status']) ? $data['status'] : self::STATUS_ACTIVE
        ];
        
        if (isset($data['logo'])) {
            $companyData['logo'] = $data['logo'];
        }
        
        return $companyData;
    }
    
    /**
     * Verificar si el nombre ya existe
     */
    private function nameExists($name, $excludeId = null) {
        try {
            $sql = "SELECT id FROM companies WHERE name = ?";
            $params = [$name];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $result = $this->db->fetch($sql, $params);
            return $result !== false;
            
        } catch (Exception $e) {
            error_log('Name exists check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener número de encuestas asociadas
     */
    private function getSurveyCount($companyId) {
        try {
            return $this->db->count('surveys', 'company_id = ?', [$companyId]);
        } catch (Exception $e) {
            error_log('Get survey count error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Verificar si el usuario actual es administrador
     */
    private function isAdmin() {
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);
    }
    
    /**
     * Log de actividad
     */
    private function logActivity($action, $entityType, $entityId, $description) {
        try {
            $activityData = [
                'user_id' => $_SESSION['user_id'] ?? null,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'ip_address' => $this->getUserIP(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('activity_logs', $activityData);
            
        } catch (Exception $e) {
            error_log('Log activity error: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener IP del usuario
     */
    private function getUserIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
}