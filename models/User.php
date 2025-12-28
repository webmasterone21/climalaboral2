<?php
/**
 * Modelo de Usuario - VERSIÃ“N FINAL v2.3
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * CORRECCIONES APLICADAS:
 * âœ… Usa mÃ©todos heredados de Model (find, findByCondition, etc.)
 * âœ… Solo sobrescribe mÃ©todos que necesitan lÃ³gica especial
 * âœ… Compatible con la clase Model base
 * 
 * @package EncuestasHERCO\Models
 * @version 2.3.0
 * @author Sistema HERCO
 */

class User extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'users';
    
    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'name',
        'email', 
        'password',
        'role',
        'status',
        'company_id',
        'department_id',
        'department',
        'phone',
        'position',
        'avatar'
    ];
    
    /**
     * Campos ocultos (no incluidos en respuestas)
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];
    
    /**
     * Roles disponibles en el sistema
     * CORREGIDO: Alineado con la estructura ENUM de la base de datos
     */
    const ROLES = [
        'superadmin' => 'Super Administrador',
        'admin' => 'Administrador',
        'user' => 'Usuario'
    ];
    
    /**
     * Estados disponibles para usuarios
     */
    const STATUSES = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'pending' => 'Pendiente',
        'suspended' => 'Suspendido'
    ];
    
    /**
     * âœ… ALIAS: findById() llama a find() heredado de Model
     * 
     * @param int $id ID del usuario
     * @return array|false Datos del usuario o false si no existe
     */
    public function findById($id)
    {
        try {
            error_log("ðŸ” [User::findById] Buscando usuario con ID: {$id}");
            
            // Usar el mÃ©todo find() heredado de Model
            $user = $this->find($id);
            
            if ($user) {
                error_log("âœ… [User::findById] Usuario encontrado");
                return $user;
            }
            
            error_log("âŒ [User::findById] Usuario no encontrado con ID: {$id}");
            return null;
            
        } catch (Exception $e) {
            error_log("âŒ Error en findById(): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * âœ… NUEVO: Buscar por campo especÃ­fico
     * 
     * @param string $field Campo a buscar
     * @param mixed $value Valor a buscar
     * @return array|null
     */
    public function findBy($field, $value)
    {
        try {
            $results = $this->findByCondition("{$field} = ?", [$value], null, 1);
            return !empty($results) ? $results[0] : null;
            
        } catch (Exception $e) {
            error_log("Error en findBy(): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Sobrescribir mÃ©todo create para hash de contraseÃ±as
     */
    public function create($data)
    {
        try {
            // Hashear contraseÃ±a si estÃ¡ presente
            if (isset($data['password'])) {
                $data['password'] = $this->hashPassword($data['password']);
            }
            
            // Llamar al create() del padre (Model)
            return parent::create($data);
            
        } catch (Exception $e) {
            error_log("Error en create(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sobrescribir mÃ©todo update para hash de contraseÃ±as
     */
    public function update($id, $data)
    {
        try {
            // Hashear contraseÃ±a si estÃ¡ presente y no estÃ¡ vacÃ­a
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = $this->hashPassword($data['password']);
                $data['password_changed_at'] = date('Y-m-d H:i:s');
            } else {
                // Remover password si estÃ¡ vacÃ­o (no actualizar)
                unset($data['password']);
            }
            
            // Llamar al update() del padre (Model)
            return parent::update($id, $data);
            
        } catch (Exception $e) {
            error_log("Error en update(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hashear contraseÃ±a
     */
    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verificar contraseÃ±a
     */
    public function verifyPassword($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }
    
    /**
     * Autenticar usuario con email y contraseÃ±a
     */
    public function authenticate($email, $password)
    {
        try {
            $user = $this->findBy('email', $email);
            
            if (!$user) {
                return null;
            }
            
            // Verificar contraseÃ±a
            if (!$this->verifyPassword($password, $user['password'])) {
                return null;
            }
            
            // Usuario autenticado correctamente
            // Actualizar Ãºltimo login
            $this->updateLastLogin($user['id']);
            
            return $user;
            
        } catch (Exception $e) {
            error_log("Error en authenticate(): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar Ãºltimo login
     */
    private function updateLastLogin($userId)
    {
        try {
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare("UPDATE {$this->table} SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
            
        } catch (PDOException $e) {
            error_log("Error actualizando Ãºltimo login: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener usuarios activos
     */
    public function getActiveUsers()
    {
        return $this->findByCondition("status = ?", ['active'], 'name ASC');
    }
    
    /**
     * Obtener usuarios por empresa
     */
    public function getByCompany($companyId)
    {
        return $this->findByCondition("company_id = ? AND status = ?", [$companyId, 'active'], 'name ASC');
    }
    
    /**
     * Obtener administradores
     */
    public function getAdmins()
    {
        return $this->findByCondition("role IN ('admin', 'super_admin') AND status = ?", ['active'], 'name ASC');
    }
    
    /**
     * Cambiar contraseÃ±a
     */
    public function changePassword($userId, $newPassword)
    {
        try {
            $hashedPassword = $this->hashPassword($newPassword);
            
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare(
                "UPDATE {$this->table} SET password = ?, password_changed_at = NOW(), updated_at = NOW() WHERE id = ?"
            );
            
            return $stmt->execute([$hashedPassword, $userId]);
            
        } catch (PDOException $e) {
            error_log("Error cambiando contraseÃ±a: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar estado del usuario
     */
    public function changeStatus($userId, $status)
    {
        if (!array_key_exists($status, self::STATUSES)) {
            return false;
        }
        
        return $this->update($userId, ['status' => $status]);
    }
    
    /**
     * Verificar si el usuario tiene un rol especÃ­fico
     */
    public function hasRole($user, $role)
    {
        if (is_array($role)) {
            return in_array($user['role'], $role);
        }
        
        return $user['role'] === $role;
    }
    
    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin($user)
    {
        return $this->hasRole($user, ['admin', 'super_admin']);
    }
    
    /**
     * Verificar si el usuario puede gestionar otros usuarios
     */
    public function canManageUsers($user)
    {
        return $this->hasRole($user, ['admin', 'super_admin', 'manager', 'hr']);
    }
    
    /**
     * Obtener estadÃ­sticas de usuarios
     */
    public function getStats($companyId = null)
    {
        try {
            $conditions = $companyId ? "WHERE company_id = {$companyId}" : "";
            
            $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN role IN ('admin', 'super_admin') THEN 1 ELSE 0 END) as admins,
                SUM(CASE WHEN role = 'manager' THEN 1 ELSE 0 END) as managers,
                SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users,
                SUM(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_last_month
            FROM {$this->table} {$conditions}
            ";
            
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
        } catch (PDOException $e) {
            error_log("Error obteniendo estadÃ­sticas de usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar usuarios
     */
    public function search($term, $companyId = null, $limit = 50)
    {
        try {
            $conditions = "(name LIKE ? OR email LIKE ?)";
            $params = ["%{$term}%", "%{$term}%"];
            
            if ($companyId) {
                $conditions .= " AND company_id = ?";
                $params[] = $companyId;
            }
            
            return $this->findByCondition($conditions, $params, 'name ASC', $limit);
            
        } catch (Exception $e) {
            error_log("Error buscando usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar email Ãºnico (excluir ID actual en actualizaciones)
     */
    public function isEmailUnique($email, $excludeId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
            $params = [$email];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] == 0;
            
        } catch (PDOException $e) {
            error_log("Error verificando email Ãºnico: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener roles disponibles
     */
    public static function getRoles()
    {
        return self::ROLES;
    }
    
    /**
     * Obtener estados disponibles
     */
    public static function getStatuses()
    {
        return self::STATUSES;
    }
    
    /**
     * Obtener label de rol
     */
    public static function getRoleLabel($role)
    {
        return self::ROLES[$role] ?? 'Desconocido';
    }
    
    /**
     * Obtener label de estado
     */
    public static function getStatusLabel($status)
    {
        return self::STATUSES[$status] ?? 'Desconocido';
    }
}
