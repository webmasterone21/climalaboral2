<?php
/**
 * Instalador de Base de Datos Inteligente
 * install/DatabaseInstaller.php
 */

class DatabaseInstaller {
    private $db;
    private $steps = [
        'security' => [
            'file' => 'database_security.sql',
            'description' => 'Tablas de seguridad críticas',
            'required' => true
        ],
        'base' => [
            'file' => 'database_base.sql', 
            'description' => 'Estructura básica del sistema',
            'required' => true
        ],
        'data' => [
            'file' => 'database_data.sql',
            'description' => 'Datos iniciales y configuraciones', 
            'required' => true
        ],
        'advanced' => [
            'file' => 'database_advanced.sql',
            'description' => 'Funcionalidades avanzadas (procedimientos, triggers)',
            'required' => false
        ]
    ];
    
    private $log = [];
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Ejecutar instalación completa
     */
    public function install() {
        $results = [
            'success' => true,
            'steps_completed' => [],
            'steps_failed' => [],
            'warnings' => [],
            'errors' => []
        ];
        
        foreach($this->steps as $step => $config) {
            $stepResult = $this->executeStep($step, $config);
            
            if($stepResult['success']) {
                $results['steps_completed'][] = $step;
                $this->log("✅ {$step}: {$config['description']} - Completado");
            } else {
                $results['steps_failed'][] = $step;
                
                if($config['required']) {
                    $results['success'] = false;
                    $results['errors'][] = $stepResult['error'];
                    $this->log("❌ {$step}: {$stepResult['error']} - CRÍTICO");
                    break; // Parar en errores críticos
                } else {
                    $results['warnings'][] = $stepResult['error'];
                    $this->log("⚠️ {$step}: {$stepResult['error']} - Opcional, continuando");
                }
            }
        }
        
        // Crear usuario admin si todo fue exitoso
        if($results['success']) {
            $this->createAdminUser();
        }
        
        $results['log'] = $this->log;
        return $results;
    }
    
    /**
     * Ejecutar un paso específico
     */
    private function executeStep($step, $config) {
        try {
            $filePath = __DIR__ . '/' . $config['file'];
            
            if(!file_exists($filePath)) {
                throw new Exception("Archivo no encontrado: {$config['file']}");
            }
            
            $sql = file_get_contents($filePath);
            
            // Verificar permisos antes de ejecutar funciones avanzadas
            if($step === 'advanced' && !$this->hasAdvancedPermissions()) {
                throw new Exception("Servidor no soporta funciones avanzadas (procedimientos/triggers)");
            }
            
            $this->executeSQLFile($sql);
            
            return ['success' => true];
            
        } catch(Exception $e) {
            return [
                'success' => false, 
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Ejecutar archivo SQL con manejo de errores
     */
    private function executeSQLFile($sql) {
        // Limpiar comentarios y líneas vacías
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        $sql = preg_replace('/^\s*$/m', '', $sql);
        
        // Dividir en statements individuales
        $statements = explode(';', $sql);
        
        foreach($statements as $statement) {
            $statement = trim($statement);
            if(empty($statement)) continue;
            
            try {
                $this->db->query($statement);
            } catch(PDOException $e) {
                // Log del error específico pero continuar con otras consultas
                $this->log("Error en statement: " . substr($statement, 0, 100) . "...");
                throw new Exception("Error SQL: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Verificar si el servidor soporta funciones avanzadas
     */
    private function hasAdvancedPermissions() {
        try {
            // Intentar crear un procedure simple para verificar permisos
            $this->db->query("
                DROP PROCEDURE IF EXISTS test_permissions;
                CREATE PROCEDURE test_permissions() BEGIN SELECT 1; END;
                DROP PROCEDURE test_permissions;
            ");
            return true;
        } catch(Exception $e) {
            return false;
        }
    }
    
    /**
     * Crear usuario administrador
     */
    private function createAdminUser() {
        // Este método se llamaría después del formulario web
        $this->log("Sistema listo para crear usuario administrador via web");
    }
    
    /**
     * Verificar integridad post-instalación
     */
    public function verifyInstallation() {
        $required_tables = [
            'users', 'companies', 'surveys', 'questions', 
            'responses', 'participants', 'question_categories',
            'login_attempts', 'user_sessions', 'activity_logs'
        ];
        
        $missing_tables = [];
        
        foreach($required_tables as $table) {
            if(!$this->tableExists($table)) {
                $missing_tables[] = $table;
            }
        }
        
        return [
            'success' => empty($missing_tables),
            'missing_tables' => $missing_tables,
            'total_tables_found' => count($required_tables) - count($missing_tables)
        ];
    }
    
    /**
     * Verificar si una tabla existe
     */
    private function tableExists($table) {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            return $stmt->rowCount() > 0;
        } catch(Exception $e) {
            return false;
        }
    }
    
    /**
     * Logging interno
     */
    private function log($message) {
        $this->log[] = date('Y-m-d H:i:s') . " - " . $message;
    }
    
    /**
     * Obtener log completo
     */
    public function getLog() {
        return $this->log;
    }
}
?>