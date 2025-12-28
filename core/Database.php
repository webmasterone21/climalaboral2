<?php
/**
 * Database - Gestor de Base de Datos - VERSIÓN CORREGIDA
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * @version 2.0.1 - Corrección de método prepare() público
 */

class Database
{
    private static $instance = null;
    private $connection = null;
    private $config = [];
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct()
    {
        $this->loadConfig();
        $this->connect();
    }
    
    /**
     * Obtener instancia única (Singleton)
     * 
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Cargar configuración de base de datos
     */
    private function loadConfig()
    {
        $configFile = __DIR__ . '/../config/database_config.php';
        
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            throw new Exception('Archivo de configuración de base de datos no encontrado');
        }
    }
    
    /**
     * Conectar a la base de datos
     */
    private function connect()
    {
        try {
            $host = $this->config['host'] ?? 'localhost';
            $port = $this->config['port'] ?? 3306;
            $database = $this->config['database'] ?? '';
            $username = $this->config['username'] ?? 'root';
            $password = $this->config['password'] ?? '';
            $charset = $this->config['charset'] ?? 'utf8mb4';
            
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
            ];
            
            $this->connection = new PDO($dsn, $username, $password, $options);
            
        } catch (PDOException $e) {
            error_log("Error de conexión a base de datos: " . $e->getMessage());
            throw new Exception('No se pudo conectar a la base de datos');
        }
    }
    
    /**
     * Obtener conexión PDO
     * 
     * @return PDO
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * ✅ NUEVO: Preparar statement - Para compatibilidad con código legacy
     * 
     * @param string $sql Consulta SQL
     * @return PDOStatement
     */
    public function prepare($sql)
    {
        return $this->getConnection()->prepare($sql);
    }
    
    /**
     * Probar conexión
     * 
     * @return bool
     */
    public function testConnection()
    {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Ejecutar query y obtener un registro
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return array|false
     */
    public function fetch($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en fetch(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecutar query y obtener todos los registros
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return array
     */
    public function fetchAll($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en fetchAll(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ejecutar query y obtener un valor
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return mixed
     */
    public function fetchColumn($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en fetchColumn(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar registros
     * 
     * @param string $table Tabla
     * @param string $where Condición WHERE
     * @param array $params Parámetros
     * @return int
     */
    public function count($table, $where = '', $params = [])
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$table}";
            
            if (!empty($where)) {
                $sql .= " WHERE {$where}";
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en count(): " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Insertar registro
     * 
     * @param string $table Tabla
     * @param array $data Datos
     * @return int|false ID insertado o false en error
     */
    public function insert($table, $data)
    {
        try {
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(array_values($data));
            
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en insert(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar registro(s)
     * 
     * @param string $table Tabla
     * @param array $data Datos a actualizar
     * @param string $where Condición WHERE
     * @param array $whereParams Parámetros de WHERE
     * @return bool
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        try {
            $sets = [];
            $values = [];
            
            foreach ($data as $column => $value) {
                // Si el valor es un RawExpression, usarlo directamente
                if ($value instanceof RawExpression) {
                    $sets[] = "{$column} = {$value->getValue()}";
                } else {
                    $sets[] = "{$column} = ?";
                    $values[] = $value;
                }
            }
            
            $sql = "UPDATE {$table} SET " . implode(', ', $sets);
            
            if (!empty($where)) {
                $sql .= " WHERE {$where}";
                $values = array_merge($values, $whereParams);
            }
            
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Error en update(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar registro(s)
     * 
     * @param string $table Tabla
     * @param string $where Condición WHERE
     * @param array $params Parámetros
     * @return bool
     */
    public function delete($table, $where, $params = [])
    {
        try {
            $sql = "DELETE FROM {$table}";
            
            if (!empty($where)) {
                $sql .= " WHERE {$where}";
            }
            
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en delete(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecutar query directamente
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return bool
     */
    public function execute($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error en execute(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Iniciar transacción
     * 
     * @return bool
     */
    public function beginTransaction()
    {
        try {
            return $this->connection->beginTransaction();
        } catch (PDOException $e) {
            error_log("Error iniciando transacción: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Confirmar transacción
     * 
     * @return bool
     */
    public function commit()
    {
        try {
            return $this->connection->commit();
        } catch (PDOException $e) {
            error_log("Error en commit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Revertir transacción
     * 
     * @return bool
     */
    public function rollback()
    {
        try {
            return $this->connection->rollback();
        } catch (PDOException $e) {
            error_log("Error en rollback: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener último ID insertado
     * 
     * @return string
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Crear expresión SQL raw (para campos calculados)
     * 
     * @param string $value Expresión SQL
     * @return RawExpression
     */
    public function raw($value)
    {
        return new RawExpression($value);
    }
    
    /**
     * Escapar valor
     * 
     * @param string $value Valor a escapar
     * @return string
     */
    public function quote($value)
    {
        return $this->connection->quote($value);
    }
    
    /**
     * Cerrar conexión
     */
    public function close()
    {
        $this->connection = null;
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
    
    /**
     * Prevenir clonación (Singleton)
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización (Singleton)
     */
    public function __wakeup()
    {
        throw new Exception("No se puede deserializar un singleton");
    }
}

/**
 * Clase auxiliar para expresiones SQL raw
 */
class RawExpression
{
    private $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function __toString()
    {
        return $this->value;
    }
}
