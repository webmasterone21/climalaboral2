<?php
/**
 * Modelo Base - ORM Simplificado
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial - CORREGIDO
 * 
 * ✅ CORRECCIÓN: Uso correcto del Singleton Database::getInstance()
 * 
 * @package EncuestasHERCO\Core
 * @version 2.0.0
 * @author Sistema HERCO
 */

abstract class Model
{
    /**
     * Instancia de la base de datos
     * @var Database
     */
    protected $db;
    
    /**
     * Nombre de la tabla
     * @var string
     */
    protected $table = '';
    
    /**
     * Clave primaria
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Campos que se pueden asignar masivamente
     * @var array
     */
    protected $fillable = [];
    
    /**
     * Campos que no se pueden asignar masivamente
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    /**
     * Campos que se manejan automáticamente
     * @var array
     */
    protected $timestamps = ['created_at', 'updated_at'];
    
    /**
     * Campos que deben ser parseados como fechas
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    
    /**
     * Campos que deben ser ocultados en arrays/JSON
     * @var array
     */
    protected $hidden = [];
    
    /**
     * Campos adicionales que se deben incluir en arrays/JSON
     * @var array
     */
    protected $appends = [];
    
    /**
     * Casting de tipos para campos
     * @var array
     */
    protected $casts = [];
    
    /**
     * Reglas de validación
     * @var array
     */
    protected $rules = [];
    
    /**
     * Mensajes de validación personalizados
     * @var array
     */
    protected $messages = [];
    
    /**
     * Relaciones definidas
     * @var array
     */
    protected $relations = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // ✅ CORREGIDO: Usar getInstance() en lugar de new Database()
        $this->db = Database::getInstance();
        
        // Auto-detectar nombre de tabla si no está definido
        if (empty($this->table)) {
            $className = get_class($this);
            $this->table = $this->pluralize(strtolower($className));
        }
    }
    
    /**
     * Buscar registro por ID
     * 
     * @param mixed $id ID del registro
     * @return array|false
     */
    public function find($id)
    {
        try {
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            return $result ? $this->castAttributes($result) : false;
            
        } catch (Exception $e) {
            error_log("Error en find(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar registro por ID o lanzar excepción
     * 
     * @param mixed $id ID del registro
     * @return array
     * @throws Exception
     */
    public function findOrFail($id)
    {
        $result = $this->find($id);
        
        if (!$result) {
            throw new Exception("Registro no encontrado con ID: {$id}");
        }
        
        return $result;
    }
    
    /**
     * Buscar registros por condición WHERE
     * 
     * @param string $where Cláusula WHERE (ej: "status = ? AND active = ?")
     * @param array $params Parámetros para bind
     * @param string $orderBy Orden (opcional)
     * @param int $limit Límite (opcional)
     * @return array
     */
    public function findByCondition($where, $params = [], $orderBy = null, $limit = null)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$where}";
            
            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy}";
            }
            
            if ($limit) {
                $sql .= " LIMIT {$limit}";
            }
            
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en findByCondition(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar primer registro que coincida con condiciones
     * 
     * @param array $conditions Condiciones WHERE
     * @return array|false
     */
    public function where($conditions)
    {
        try {
            $whereClause = [];
            $params = [];
            
            foreach ($conditions as $field => $value) {
                if (is_array($value)) {
                    // Manejo de operadores
                    $operator = $value[0];
                    $val = $value[1];
                    $whereClause[] = "{$field} {$operator} ?";
                    $params[] = $val;
                } else {
                    $whereClause[] = "{$field} = ?";
                    $params[] = $value;
                }
            }
            
            $where = implode(' AND ', $whereClause);
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare("SELECT * FROM {$this->table} WHERE {$where}");
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result ? $this->castAttributes($result) : false;
            
        } catch (Exception $e) {
            error_log("Error en where(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los registros
     * 
     * @param array $conditions Condiciones opcionales
     * @param string $orderBy Ordenamiento
     * @param int $limit Límite de registros
     * @return array
     */
    public function all($conditions = [], $orderBy = '', $limit = 0)
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            
            // Agregar condiciones WHERE
            if (!empty($conditions)) {
                $whereClause = [];
                foreach ($conditions as $field => $value) {
                    $whereClause[] = "{$field} = ?";
                    $params[] = $value;
                }
                $sql .= " WHERE " . implode(' AND ', $whereClause);
            }
            
            // Agregar ORDER BY
            if (!empty($orderBy)) {
                $sql .= " ORDER BY {$orderBy}";
            }
            
            // Agregar LIMIT
            if ($limit > 0) {
                $sql .= " LIMIT {$limit}";
            }
            
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            
            // Aplicar casting a todos los resultados
            return array_map([$this, 'castAttributes'], $results);
            
        } catch (Exception $e) {
            error_log("Error en all(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear nuevo registro
     * 
     * @param array $data Datos del registro
     * @return int|false ID del registro creado o false si falla
     */
    public function create($data)
    {
        try {
            // Filtrar campos permitidos
            $data = $this->filterFillable($data);
            
            // Validar datos
            $errors = $this->validate($data);
            if (!empty($errors)) {
                throw new Exception("Errores de validación: " . implode(', ', array_flatten($errors)));
            }
            
            // Agregar timestamps
            if (in_array('created_at', $this->timestamps)) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            if (in_array('updated_at', $this->timestamps)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            
            // Ejecutar hook before create
            $data = $this->beforeCreate($data);
            
            // Construir query de inserción
            $fields = array_keys($data);
            $placeholders = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute(array_values($data));
            
            $id = $connection->lastInsertId();
            
            // Ejecutar hook after create
            $this->afterCreate($id, $data);
            
            return $id;
            
        } catch (Exception $e) {
            error_log("Error en create(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar registro
     * 
     * @param mixed $id ID del registro
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update($id, $data)
    {
        try {
            // Filtrar campos permitidos
            $data = $this->filterFillable($data);
            
            // Validar datos
            $errors = $this->validate($data, $id);
            if (!empty($errors)) {
                throw new Exception("Errores de validación: " . implode(', ', array_flatten($errors)));
            }
            
            // Agregar timestamp de actualización
            if (in_array('updated_at', $this->timestamps)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }
            
            // Ejecutar hook before update
            $data = $this->beforeUpdate($id, $data);
            
            // Construir query de actualización
            $sets = [];
            foreach (array_keys($data) as $field) {
                $sets[] = "{$field} = ?";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . 
                   " WHERE {$this->primaryKey} = ?";
            
            $params = array_values($data);
            $params[] = $id;
            
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            
            $affected = $stmt->rowCount();
            
            // Ejecutar hook after update
            $this->afterUpdate($id, $data);
            
            return $affected > 0;
            
        } catch (Exception $e) {
            error_log("Error en update(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar registro
     * 
     * @param mixed $id ID del registro
     * @param bool $soft Si usar soft delete
     * @return bool
     */
    public function delete($id, $soft = false)
    {
        try {
            // Ejecutar hook before delete
            $this->beforeDelete($id);
            
            $connection = $this->db->getConnection();
            
            if ($soft && in_array('deleted_at', $this->timestamps)) {
                // Soft delete
                $sql = "UPDATE {$this->table} SET deleted_at = ? WHERE {$this->primaryKey} = ?";
                $stmt = $connection->prepare($sql);
                $stmt->execute([date('Y-m-d H:i:s'), $id]);
                $affected = $stmt->rowCount();
            } else {
                // Hard delete
                $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
                $stmt = $connection->prepare($sql);
                $stmt->execute([$id]);
                $affected = $stmt->rowCount();
            }
            
            // Ejecutar hook after delete
            $this->afterDelete($id);
            
            return $affected > 0;
            
        } catch (Exception $e) {
            error_log("Error en delete(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar registros
     * 
     * @param array $conditions Condiciones WHERE
     * @return int
     */
    public function count($conditions = [])
    {
        try {
            $whereClause = '1=1';
            $params = [];
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $field => $value) {
                    $where[] = "{$field} = ?";
                    $params[] = $value;
                }
                $whereClause = implode(' AND ', $where);
            }
            
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClause}";
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            
            return (int) $stmt->fetchColumn();
            
        } catch (Exception $e) {
            error_log("Error en count(): " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Verificar si existe un registro
     * 
     * @param array $conditions Condiciones
     * @return bool
     */
    public function exists($conditions)
    {
        return $this->count($conditions) > 0;
    }
    
    /**
     * Paginación de resultados - VERSIÓN CORREGIDA
     * 
     * @param int $page Página actual
     * @param int $perPage Registros por página
     * @param string $where Cláusula WHERE completa (opcional)
     * @param array $params Parámetros para bind (opcional)
     * @param string $orderBy Ordenamiento (opcional)
     * @return array
     */
    public function paginate($page = 1, $perPage = 20, $where = '', $params = [], $orderBy = '')
    {
        try {
            $offset = ($page - 1) * $perPage;
            $connection = $this->db->getConnection();
            
            // ✅ Asegurar que $params es un array
            if (!is_array($params)) {
                error_log("⚠️ paginate() recibió params no-array: " . gettype($params));
                $params = [];
            }
            
            // Construir WHERE
            $whereClause = '';
            if (!empty($where) && is_string($where)) {
                $whereClause = "WHERE {$where}";
            }
            
            // Ordenamiento por defecto
            if (empty($orderBy)) {
                $orderBy = "{$this->primaryKey} DESC";
            }
            
            // Contar total
            $totalSql = "SELECT COUNT(*) FROM {$this->table} {$whereClause}";
            $stmt = $connection->prepare($totalSql);
            $stmt->execute($params);
            $total = (int) $stmt->fetchColumn();
            
            // Obtener datos
            $dataSql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $connection->prepare($dataSql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Aplicar casting
            $data = array_map([$this, 'castAttributes'], $data);
            
            return [
                'data' => $data,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'from' => $total > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $total)
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error en paginate(): " . $e->getMessage());
            return [
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => 1,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0
                ]
            ];
        }
    }
    
    /**
     * Validar datos
     * 
     * @param array $data Datos a validar
     * @param mixed $id ID para reglas de unicidad
     * @return array Errores de validación
     */
    public function validate($data, $id = null)
    {
        $errors = [];
        
        foreach ($this->rules as $field => $rules) {
            $value = isset($data[$field]) ? $data[$field] : null;
            $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;
            
            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = isset($ruleParts[1]) ? $ruleParts[1] : null;
                
                $error = $this->validateRule($field, $value, $ruleName, $ruleValue, $id);
                if ($error) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = $error;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar regla individual
     * 
     * @param string $field Campo
     * @param mixed $value Valor
     * @param string $rule Regla
     * @param mixed $ruleValue Valor de la regla
     * @param mixed $id ID para unicidad
     * @return string|null Error o null si es válido
     */
    private function validateRule($field, $value, $rule, $ruleValue, $id)
    {
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return $this->getValidationMessage($field, $rule, "El campo {$field} es requerido");
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $this->getValidationMessage($field, $rule, "El campo {$field} debe ser un email válido");
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < $ruleValue) {
                    return $this->getValidationMessage($field, $rule, "El campo {$field} debe tener al menos {$ruleValue} caracteres");
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > $ruleValue) {
                    return $this->getValidationMessage($field, $rule, "El campo {$field} no puede tener más de {$ruleValue} caracteres");
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return $this->getValidationMessage($field, $rule, "El campo {$field} debe ser numérico");
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    return $this->getValidationMessage($field, $rule, "El campo {$field} debe ser un número entero");
                }
                break;
                
            case 'unique':
                if (!empty($value)) {
                    $table = $ruleValue ?: $this->table;
                    $column = $field;
                    
                    $query = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
                    $params = [$value];
                    
                    if ($id) {
                        $query .= " AND {$this->primaryKey} != ?";
                        $params[] = $id;
                    }
                    
                    $connection = $this->db->getConnection();
                    $stmt = $connection->prepare($query);
                    $stmt->execute($params);
                    
                    if ($stmt->fetchColumn() > 0) {
                        return $this->getValidationMessage($field, $rule, "El valor del campo {$field} ya existe");
                    }
                }
                break;
                
            case 'in':
                if (!empty($value) && $ruleValue) {
                    $allowedValues = explode(',', $ruleValue);
                    if (!in_array($value, $allowedValues)) {
                        return $this->getValidationMessage($field, $rule, "El campo {$field} debe ser uno de: " . implode(', ', $allowedValues));
                    }
                }
                break;
                
            case 'date':
                if (!empty($value) && !$this->isValidDate($value)) {
                    return $this->getValidationMessage($field, $rule, "El campo {$field} debe ser una fecha válida");
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Obtener mensaje de validación
     * 
     * @param string $field Campo
     * @param string $rule Regla
     * @param string $default Mensaje por defecto
     * @return string
     */
    private function getValidationMessage($field, $rule, $default)
    {
        $key = "{$field}.{$rule}";
        return isset($this->messages[$key]) ? $this->messages[$key] : $default;
    }
    
    /**
     * Verificar si es una fecha válida
     * 
     * @param string $date Fecha
     * @return bool
     */
    private function isValidDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Filtrar campos fillable
     * 
     * @param array $data Datos
     * @return array
     */
    private function filterFillable($data)
    {
        if (!empty($this->fillable)) {
            // Solo campos en fillable
            return array_intersect_key($data, array_flip($this->fillable));
        }
        
        if (!empty($this->guarded)) {
            // Todos excepto guarded
            return array_diff_key($data, array_flip($this->guarded));
        }
        
        return $data;
    }
    
    /**
     * Aplicar casting a atributos
     * 
     * @param array $attributes Atributos
     * @return array
     */
    private function castAttributes($attributes)
    {
        foreach ($this->casts as $field => $type) {
            if (isset($attributes[$field])) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $attributes[$field] = (int) $attributes[$field];
                        break;
                        
                    case 'float':
                    case 'double':
                        $attributes[$field] = (float) $attributes[$field];
                        break;
                        
                    case 'bool':
                    case 'boolean':
                        $attributes[$field] = (bool) $attributes[$field];
                        break;
                        
                    case 'array':
                    case 'json':
                        $attributes[$field] = json_decode($attributes[$field], true);
                        break;
                        
                    case 'date':
                        if (!empty($attributes[$field])) {
                            $attributes[$field] = date('Y-m-d', strtotime($attributes[$field]));
                        }
                        break;
                        
                    case 'datetime':
                        if (!empty($attributes[$field])) {
                            $attributes[$field] = date('Y-m-d H:i:s', strtotime($attributes[$field]));
                        }
                        break;
                }
            }
        }
        
        return $attributes;
    }
    
    /**
     * Convertir a nombre plural (simple)
     * 
     * @param string $word Palabra
     * @return string
     */
    private function pluralize($word)
    {
        $pluralRules = [
            'survey' => 'surveys',
            'company' => 'companies',
            'category' => 'categories',
            'user' => 'users',
            'question' => 'questions',
            'response' => 'responses',
            'participant' => 'participants'
        ];
        
        if (isset($pluralRules[$word])) {
            return $pluralRules[$word];
        }
        
        // Reglas simples de pluralización
        if (substr($word, -1) === 'y') {
            return substr($word, 0, -1) . 'ies';
        }
        
        if (in_array(substr($word, -1), ['s', 'x', 'z']) || in_array(substr($word, -2), ['ch', 'sh'])) {
            return $word . 'es';
        }
        
        return $word . 's';
    }
    
    // ========================================
    // HOOKS DE EVENTOS
    // ========================================
    
    /**
     * Hook antes de crear
     * 
     * @param array $data Datos
     * @return array
     */
    protected function beforeCreate($data)
    {
        return $data;
    }
    
    /**
     * Hook después de crear
     * 
     * @param int $id ID creado
     * @param array $data Datos
     * @return void
     */
    protected function afterCreate($id, $data)
    {
        // Override en modelos específicos
    }
    
    /**
     * Hook antes de actualizar
     * 
     * @param mixed $id ID
     * @param array $data Datos
     * @return array
     */
    protected function beforeUpdate($id, $data)
    {
        return $data;
    }
    
    /**
     * Hook después de actualizar
     * 
     * @param mixed $id ID
     * @param array $data Datos
     * @return void
     */
    protected function afterUpdate($id, $data)
    {
        // Override en modelos específicos
    }
    
    /**
     * Hook antes de eliminar
     * 
     * @param mixed $id ID
     * @return void
     */
    protected function beforeDelete($id)
    {
        // Override en modelos específicos
    }
    
    /**
     * Hook después de eliminar
     * 
     * @param mixed $id ID
     * @return void
     */
    protected function afterDelete($id)
    {
        // Override en modelos específicos
    }
    
} // ← ÚNICA llave de cierre de la clase Model

// ========================================
// FUNCIÓN HELPER (FUERA DE LA CLASE)
// ========================================

// Función helper para aplanar arrays
if (!function_exists('array_flatten')) {
    function array_flatten($array) {
        $result = [];
        foreach ($array as $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }
}