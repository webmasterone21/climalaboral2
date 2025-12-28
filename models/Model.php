<?php
/**
 * Modelo Base - Sistema HERCO v2.0
 * 
 * Clase base para todos los modelos del sistema
 * Incluye funcionalidades comunes de CRUD y validaciÃƒÂ³n
 * 
 * @package HERCO\Models
 * @version 2.0
 * @author Sistema HERCO
 */

abstract class Model {
    
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $hidden = [];
    protected $casts = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $timestamps = true;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        
        if (empty($this->table)) {
            $this->table = $this->getTableName();
        }
    }
    
    /**
     * Obtener nombre de tabla automÃƒÂ¡ticamente
     */
    protected function getTableName() {
        $className = strtolower(get_class($this));
        return $className . 's'; // PluralizaciÃƒÂ³n simple
    }
    
    /**
     * Buscar por ID
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result) {
                return $this->transformResult($result);
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Error en findById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Buscar todos los registros
     */
    public function findAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if ($limit) {
                $sql .= " LIMIT {$limit} OFFSET {$offset}";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            return array_map([$this, 'transformResult'], $results);
            
        } catch (Exception $e) {
            $this->logError("Error en findAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar con condiciones
     */
    public function findWhere($conditions, $limit = null, $offset = 0) {
        try {
            $whereClause = $this->buildWhereClause($conditions);
            $sql = "SELECT * FROM {$this->table} WHERE {$whereClause['sql']}";
            
            if ($limit) {
                $sql .= " LIMIT {$limit} OFFSET {$offset}";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($whereClause['values']);
            $results = $stmt->fetchAll();
            
            return array_map([$this, 'transformResult'], $results);
            
        } catch (Exception $e) {
            $this->logError("Error en findWhere: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar uno con condiciones
     */
    public function findOneWhere($conditions) {
        $results = $this->findWhere($conditions, 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Crear nuevo registro
     */
    public function create($data) {
        try {
            $data = $this->filterData($data);
            $data = $this->addTimestamps($data, 'create');
            
            // Validar datos
            $validationErrors = $this->validate($data);
            if (!empty($validationErrors)) {
                throw new Exception('Datos invÃƒÂ¡lidos: ' . implode(', ', $validationErrors));
            }
            
            $columns = array_keys($data);
            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            
            $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ($placeholders)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(array_values($data));
            
            if ($result) {
                $id = $this->db->lastInsertId();
                return $this->findById($id);
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Error en create: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar registro
     */
    public function update($id, $data) {
        try {
            $data = $this->filterData($data);
            $data = $this->addTimestamps($data, 'update');
            
            // Validar datos
            $validationErrors = $this->validate($data, $id);
            if (!empty($validationErrors)) {
                throw new Exception('Datos invÃƒÂ¡lidos: ' . implode(', ', $validationErrors));
            }
            
            $columns = array_keys($data);
            $setClause = implode(' = ?, ', $columns) . ' = ?';
            
            $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
            
            $stmt = $this->db->prepare($sql);
            $values = array_values($data);
            $values[] = $id;
            
            $result = $stmt->execute($values);
            
            if ($result) {
                return $this->findById($id);
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Error en update: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar registro
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
            return $stmt->execute([$id]);
            
        } catch (Exception $e) {
            $this->logError("Error en delete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar registros
     */
    public function count($conditions = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $values = [];
            
            if (!empty($conditions)) {
                $whereClause = $this->buildWhereClause($conditions);
                $sql .= " WHERE {$whereClause['sql']}";
                $values = $whereClause['values'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            $result = $stmt->fetch();
            
            return (int)$result['count'];
            
        } catch (Exception $e) {
            $this->logError("Error en count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Verificar si existe
     */
    public function exists($conditions) {
        return $this->count($conditions) > 0;
    }
    
    /**
     * PaginaciÃƒÂ³n
     */
    public function paginate($page = 1, $perPage = 15, $conditions = []) {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($conditions);
        $data = empty($conditions) ? 
            $this->findAll($perPage, $offset) : 
            $this->findWhere($conditions, $perPage, $offset);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    /**
     * Construir clÃƒÂ¡usula WHERE
     */
    protected function buildWhereClause($conditions) {
        $sql = [];
        $values = [];
        
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // IN clause
                $placeholders = str_repeat('?,', count($value) - 1) . '?';
                $sql[] = "{$column} IN ({$placeholders})";
                $values = array_merge($values, $value);
            } elseif (strpos($column, ' ') !== false) {
                // Operador personalizado (ej: "age >" => 18)
                $sql[] = $column . ' ?';
                $values[] = $value;
            } else {
                // Igualdad simple
                $sql[] = "{$column} = ?";
                $values[] = $value;
            }
        }
        
        return [
            'sql' => implode(' AND ', $sql),
            'values' => $values
        ];
    }
    
    /**
     * Filtrar datos según fillable/guarded
     * CORREGIDO: Siempre excluye timestamps automáticos
     */
    protected function filterData($data) {
        // SIEMPRE excluir timestamps automáticos y campos protegidos
        $alwaysGuarded = ['id', 'created_at', 'updated_at'];
        $data = array_diff_key($data, array_flip($alwaysGuarded));
        
        if (!empty($this->fillable)) {
            // Solo permitir campos en fillable
            return array_intersect_key($data, array_flip($this->fillable));
        } elseif (!empty($this->guarded)) {
            // Excluir campos en guarded adicionales
            return array_diff_key($data, array_flip($this->guarded));
        }
        
        return $data;
    }
    
    /**
     * Agregar timestamps
     * CORREGIDO: No agrega timestamps si la tabla los maneja automáticamente
     */
protected function addTimestamps($data, $action) {
    if (!$this->timestamps) {
        return $data;
    }
    
    // NO agregar timestamps si ya vienen en los datos
    // Esto permite que la BD los maneje automáticamente con DEFAULT CURRENT_TIMESTAMP
    
    // Solo agregar timestamps manualmente si NO están definidos Y son necesarios
    // En la mayoría de casos, la BD los maneja con DEFAULT y ON UPDATE
    
    return $data;
}
    
    /**
     * Casting de atributos
     */
    protected function castAttribute($value, $type) {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int)$value;
            case 'float':
            case 'double':
                return (float)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            case 'object':
                return json_decode($value);
            case 'datetime':
                return new DateTime($value);
            default:
                return $value;
        }
    }
    
    /**
     * ValidaciÃƒÂ³n de datos (override en subclases)
     */
    protected function validate($data, $id = null) {
        return []; // Sin errores por defecto
    }
    
    /**
     * Registrar error
     */
    protected function logError($message) {
        error_log("[" . get_class($this) . "] " . $message);
    }
    
    /**
     * Ejecutar query personalizada
     */
    protected function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logError("Error en query: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ejecutar query que retorna un solo resultado
     */
    protected function queryOne($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->logError("Error en queryOne: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Ejecutar query de escritura
     */
    protected function execute($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            $this->logError("Error en execute: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Iniciar transacciÃƒÂ³n
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Confirmar transacciÃƒÂ³n
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Revertir transacciÃƒÂ³n
     */
    public function rollback() {
        return $this->db->rollback();
    }
    
    /**
     * MÃƒÂ©todos mÃƒÂ¡gicos para propiedades dinÃƒÂ¡micas
     */
    public function __get($name) {
        return $this->$name ?? null;
    }
    
    public function __set($name, $value) {
        $this->$name = $value;
    }
    
    public function __isset($name) {
        return isset($this->$name);
    }
}