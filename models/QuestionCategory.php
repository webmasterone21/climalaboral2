<?php
/**
 * Modelo de Categorías de Preguntas HERCO
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * Gestiona las 18 categorías oficiales HERCO 2024 con sus
 * configuraciones, colores, iconos y descripciones específicas.
 * 
 * @package EncuestasHERCO\Models
 * @version 2.0.0
 * @author Sistema HERCO
 */

class QuestionCategory extends Model
{
    /**
     * Nombre de la tabla
     */
    protected $table = 'question_categories';
    
    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'name',
        'description',
        'herco_code',
        'color',
        'icon',
        'order_index',
        'is_active',
        'is_herco_category'
    ];
    
    /**
     * Reglas de validación
     */
    protected $validationRules = [
        'name' => 'required|min:3|max:200',
        'herco_code' => 'required|max:10',
        'color' => 'required',
        'order_index' => 'numeric'
    ];
    
    /**
     * 18 Categorías HERCO 2024 oficiales con configuraciones completas
     */
    const HERCO_CATEGORIES_2024 = [
        [
            'id' => 1,
            'name' => 'Satisfacción Laboral',
            'herco_code' => 'SL',
            'description' => 'Evalúa el nivel general de satisfacción del empleado con su trabajo, incluyendo aspectos como motivación, compromiso y recomendación de la empresa como lugar de trabajo.',
            'color' => '#3b82f6',
            'icon' => 'fas fa-smile',
            'priority' => 'high',
            'dimensions' => ['motivación', 'compromiso', 'satisfacción general', 'recomendación'],
            'target_score' => 4.0
        ],
        [
            'id' => 2,
            'name' => 'Participación y Autonomía',
            'herco_code' => 'PA',
            'description' => 'Mide el grado de autonomía en la toma de decisiones, participación en procesos organizacionales y empoderamiento del empleado en su puesto de trabajo.',
            'color' => '#10b981',
            'icon' => 'fas fa-user-check',
            'priority' => 'high',
            'dimensions' => ['autonomía', 'toma de decisiones', 'participación', 'empoderamiento'],
            'target_score' => 3.8
        ],
        [
            'id' => 3,
            'name' => 'Comunicación y Objetivos',
            'herco_code' => 'CO',
            'description' => 'Evalúa la claridad en la comunicación organizacional, definición de objetivos y comprensión del rol individual en el logro de metas empresariales.',
            'color' => '#f59e0b',
            'icon' => 'fas fa-comments',
            'priority' => 'high',
            'dimensions' => ['claridad comunicacional', 'objetivos definidos', 'alineación estratégica'],
            'target_score' => 4.1
        ],
        [
            'id' => 4,
            'name' => 'Equilibrio y Evaluación',
            'herco_code' => 'EE',
            'description' => 'Analiza el equilibrio entre vida personal y laboral, así como la justicia y efectividad de los procesos de evaluación del desempeño.',
            'color' => '#ef4444',
            'icon' => 'fas fa-balance-scale',
            'priority' => 'high',
            'dimensions' => ['work-life balance', 'evaluación justa', 'retroalimentación'],
            'target_score' => 3.7
        ],
        [
            'id' => 5,
            'name' => 'Distribución y Carga de Trabajo',
            'herco_code' => 'DCT',
            'description' => 'Examina la equidad en la distribución de tareas, manejo de la carga laboral y razonabilidad de plazos y expectativas de trabajo.',
            'color' => '#8b5cf6',
            'icon' => 'fas fa-tasks',
            'priority' => 'medium',
            'dimensions' => ['carga equilibrada', 'distribución equitativa', 'plazos razonables'],
            'target_score' => 3.6
        ],
        [
            'id' => 6,
            'name' => 'Reconocimiento y Promoción',
            'herco_code' => 'RP',
            'description' => 'Mide la efectividad de los sistemas de reconocimiento, oportunidades de crecimiento profesional y promoción dentro de la organización.',
            'color' => '#f97316',
            'icon' => 'fas fa-trophy',
            'priority' => 'high',
            'dimensions' => ['reconocimiento', 'crecimiento profesional', 'promoción interna'],
            'target_score' => 3.5
        ],
        [
            'id' => 7,
            'name' => 'Ambiente de Trabajo',
            'herco_code' => 'AT',
            'description' => 'Evalúa las condiciones físicas del lugar de trabajo, comodidad del espacio laboral y adecuación de las instalaciones para el desempeño óptimo.',
            'color' => '#06b6d4',
            'icon' => 'fas fa-building',
            'priority' => 'medium',
            'dimensions' => ['condiciones físicas', 'comodidad', 'infraestructura'],
            'target_score' => 4.0
        ],
        [
            'id' => 8,
            'name' => 'Capacitación',
            'herco_code' => 'CAP',
            'description' => 'Analiza la calidad y relevancia de los programas de capacitación, desarrollo profesional y inversión en el crecimiento de competencias.',
            'color' => '#84cc16',
            'icon' => 'fas fa-graduation-cap',
            'priority' => 'high',
            'dimensions' => ['desarrollo profesional', 'capacitación relevante', 'inversión en competencias'],
            'target_score' => 3.8
        ],
        [
            'id' => 9,
            'name' => 'Tecnología y Recursos',
            'herco_code' => 'TR',
            'description' => 'Examina la disponibilidad y calidad de herramientas tecnológicas, recursos necesarios para el trabajo y soporte técnico proporcionado.',
            'color' => '#6366f1',
            'icon' => 'fas fa-laptop',
            'priority' => 'medium',
            'dimensions' => ['herramientas tecnológicas', 'recursos adecuados', 'soporte técnico'],
            'target_score' => 3.9
        ],
        [
            'id' => 10,
            'name' => 'Colaboración y Compañerismo',
            'herco_code' => 'CC',
            'description' => 'Mide la calidad de las relaciones interpersonales, trabajo en equipo y ambiente de colaboración entre colegas y departamentos.',
            'color' => '#ec4899',
            'icon' => 'fas fa-users',
            'priority' => 'high',
            'dimensions' => ['trabajo en equipo', 'relaciones interpersonales', 'colaboración'],
            'target_score' => 4.2
        ],
        [
            'id' => 11,
            'name' => 'Normativas y Regulaciones',
            'herco_code' => 'NR',
            'description' => 'Evalúa el conocimiento y aplicación consistente de políticas internas, procedimientos organizacionales y cumplimiento normativo.',
            'color' => '#64748b',
            'icon' => 'fas fa-gavel',
            'priority' => 'medium',
            'dimensions' => ['políticas claras', 'aplicación consistente', 'cumplimiento normativo'],
            'target_score' => 4.1
        ],
        [
            'id' => 12,
            'name' => 'Compensación y Beneficios',
            'herco_code' => 'CB',
            'description' => 'Analiza la satisfacción con el paquete salarial, beneficios ofrecidos y competitividad de la compensación total en el mercado.',
            'color' => '#059669',
            'icon' => 'fas fa-dollar-sign',
            'priority' => 'high',
            'dimensions' => ['salario justo', 'beneficios atractivos', 'competitividad salarial'],
            'target_score' => 3.4
        ],
        [
            'id' => 13,
            'name' => 'Bienestar y Salud',
            'herco_code' => 'BS',
            'description' => 'Examina los programas de bienestar, promoción de la salud ocupacional y preocupación organizacional por el bienestar integral del empleado.',
            'color' => '#dc2626',
            'icon' => 'fas fa-heart',
            'priority' => 'high',
            'dimensions' => ['programas de bienestar', 'salud ocupacional', 'salud mental'],
            'target_score' => 3.9
        ],
        [
            'id' => 14,
            'name' => 'Seguridad en el Trabajo',
            'herco_code' => 'ST',
            'description' => 'Mide la efectividad de las medidas de seguridad laboral, protocolos de prevención y capacitación en seguridad ocupacional.',
            'color' => '#7c3aed',
            'icon' => 'fas fa-shield-alt',
            'priority' => 'high',
            'dimensions' => ['medidas de seguridad', 'protocolos claros', 'capacitación en seguridad'],
            'target_score' => 4.3
        ],
        [
            'id' => 15,
            'name' => 'Información y Comunicación',
            'herco_code' => 'IC',
            'description' => 'Evalúa la efectividad de los canales de comunicación interna, flujo de información y transparencia organizacional.',
            'color' => '#0891b2',
            'icon' => 'fas fa-info-circle',
            'priority' => 'medium',
            'dimensions' => ['canales efectivos', 'información oportuna', 'transparencia'],
            'target_score' => 3.8
        ],
        [
            'id' => 16,
            'name' => 'Relaciones con Supervisores',
            'herco_code' => 'RS',
            'description' => 'Analiza la calidad de la relación supervisor-subordinado, apoyo recibido y efectividad del liderazgo directo.',
            'color' => '#ea580c',
            'icon' => 'fas fa-user-tie',
            'priority' => 'high',
            'dimensions' => ['relación supervisor', 'apoyo directo', 'liderazgo efectivo'],
            'target_score' => 4.0
        ],
        [
            'id' => 17,
            'name' => 'Feedback y Reconocimiento',
            'herco_code' => 'FR',
            'description' => 'Examina la frecuencia y calidad de la retroalimentación recibida, reconocimiento del desempeño y comunicación bidireccional.',
            'color' => '#65a30d',
            'icon' => 'fas fa-comment-alt',
            'priority' => 'high',
            'dimensions' => ['retroalimentación regular', 'reconocimiento oportuno', 'comunicación bidireccional'],
            'target_score' => 3.7
        ],
        [
            'id' => 18,
            'name' => 'Diversidad e Inclusión',
            'herco_code' => 'DI',
            'description' => 'Mide la promoción de la diversidad, inclusión organizacional y igualdad de oportunidades para todos los empleados.',
            'color' => '#be185d',
            'icon' => 'fas fa-globe',
            'priority' => 'high',
            'dimensions' => ['ambiente inclusivo', 'igualdad de oportunidades', 'respeto a la diversidad'],
            'target_score' => 4.1
        ]
    ];
    
    /**
     * Niveles de prioridad para las categorías
     */
    const PRIORITY_LEVELS = [
        'high' => 'Alta',
        'medium' => 'Media',
        'low' => 'Baja'
    ];
    
    /**
     * Rangos de evaluación HERCO
     */
    const EVALUATION_RANGES = [
        'excellent' => ['min' => 4.5, 'max' => 5.0, 'label' => 'Excelente', 'color' => '#10b981'],
        'good' => ['min' => 4.0, 'max' => 4.4, 'label' => 'Bueno', 'color' => '#84cc16'],
        'acceptable' => ['min' => 3.5, 'max' => 3.9, 'label' => 'Aceptable', 'color' => '#f59e0b'],
        'needs_improvement' => ['min' => 3.0, 'max' => 3.4, 'label' => 'Necesita Mejora', 'color' => '#f97316'],
        'critical' => ['min' => 1.0, 'max' => 2.9, 'label' => 'Crítico', 'color' => '#ef4444']
    ];
    
    /**
     * Inicialización del modelo
     */
    protected function boot()
    {
        // Verificar y crear tabla si no existe
        $this->ensureTableExists();
        
        // Cargar categorías HERCO si no existen
        $this->loadHercoCategories();
    }
    
    /**
     * Verificar y crear tabla si no existe
     */
    private function ensureTableExists()
    {
        try {
            if (!$this->tableExists()) {
                $this->createCategoriesTable();
            }
        } catch (Exception $e) {
            error_log("Error verificando tabla question_categories: " . $e->getMessage());
        }
    }
    
    /**
     * Crear tabla de categorías
     */
    private function createCategoriesTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS question_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT NULL,
            herco_code VARCHAR(10) NULL,
            color VARCHAR(7) DEFAULT '#3b82f6',
            icon VARCHAR(50) DEFAULT 'fas fa-question-circle',
            priority ENUM('high', 'medium', 'low') DEFAULT 'medium',
            target_score DECIMAL(3,2) DEFAULT 4.00,
            dimensions JSON NULL,
            order_index INT DEFAULT 0,
            is_active BOOLEAN DEFAULT true,
            is_herco_category BOOLEAN DEFAULT true,
            herco_version VARCHAR(10) DEFAULT '2024',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            UNIQUE KEY unique_herco_code (herco_code),
            INDEX idx_herco_code (herco_code),
            INDEX idx_active (is_active),
            INDEX idx_order (order_index),
            INDEX idx_priority (priority),
            INDEX idx_herco (is_herco_category, herco_version)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        try {
            $this->getConnection()->exec($sql);
            error_log("Tabla 'question_categories' creada exitosamente");
        } catch (PDOException $e) {
            error_log("Error creando tabla question_categories: " . $e->getMessage());
            throw new Exception("No se pudo crear la tabla de categorías");
        }
    }
    
    /**
     * Cargar categorías HERCO 2024 si no existen
     */
    private function loadHercoCategories()
    {
        try {
            $connection = $this->getConnection();
            
            // Verificar si ya existen categorías HERCO 2024
            $stmt = $connection->query("SELECT COUNT(*) FROM question_categories WHERE is_herco_category = 1 AND herco_version = '2024'");
            if ($stmt->fetchColumn() >= 18) {
                return; // Ya existen las 18 categorías
            }
            
            $this->insertHercoCategories2024();
            
        } catch (Exception $e) {
            error_log("Error cargando categorías HERCO: " . $e->getMessage());
        }
    }
    
    /**
     * Insertar categorías HERCO 2024
     */
    private function insertHercoCategories2024()
    {
        try {
            $connection = $this->getConnection();
            
            // Limpiar categorías existentes para evitar duplicados
            $connection->exec("DELETE FROM question_categories WHERE is_herco_category = 1");
            
            $sql = "
            INSERT INTO question_categories 
            (name, description, herco_code, color, icon, priority, target_score, dimensions, order_index, is_herco_category, herco_version) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, '2024')
            ";
            
            $stmt = $connection->prepare($sql);
            
            foreach (self::HERCO_CATEGORIES_2024 as $index => $category) {
                $stmt->execute([
                    $category['name'],
                    $category['description'],
                    $category['herco_code'],
                    $category['color'],
                    $category['icon'],
                    $category['priority'],
                    $category['target_score'],
                    json_encode($category['dimensions']),
                    $index + 1
                ]);
            }
            
            error_log("18 categorías HERCO 2024 insertadas exitosamente con configuraciones completas");
            
        } catch (Exception $e) {
            error_log("Error insertando categorías HERCO 2024: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener todas las categorías HERCO activas
     */
    public function getHercoCategories($activeOnly = true)
    {
        try {
            $conditions = 'is_herco_category = 1 AND herco_version = ?';
            $params = ['2024'];
            
            if ($activeOnly) {
                $conditions .= ' AND is_active = 1';
            }
            
            $categories = $this->where($conditions, $params, 'order_index', 'ASC');
            
            // Agregar estadísticas de preguntas a cada categoría
            foreach ($categories as &$category) {
                $category['questions_count'] = $this->getQuestionsCount($category['id']);
                $category['dimensions_array'] = json_decode($category['dimensions'], true) ?? [];
                $category['priority_label'] = self::PRIORITY_LEVELS[$category['priority']] ?? 'Media';
            }
            
            return $categories;
            
        } catch (Exception $e) {
            error_log("Error obteniendo categorías HERCO: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener categorías por prioridad
     */
    public function getByPriority($priority = 'high')
    {
        if (!array_key_exists($priority, self::PRIORITY_LEVELS)) {
            throw new Exception("Prioridad no válida: {$priority}");
        }
        
        $conditions = 'is_herco_category = 1 AND herco_version = ? AND priority = ? AND is_active = 1';
        $params = ['2024', $priority];
        
        return $this->where($conditions, $params, 'order_index', 'ASC');
    }
    
    /**
     * Obtener categoría por código HERCO
     */
    public function getByHercoCode($hercoCode)
    {
        $conditions = 'herco_code = ? AND is_herco_category = 1 AND herco_version = ?';
        $params = [$hercoCode, '2024'];
        
        $results = $this->where($conditions, $params, null, null, 1);
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Obtener categorías para encuesta express (10 principales)
     */
    public function getForExpressSurvey()
    {
        try {
            $connection = $this->getConnection();
            
            // Seleccionar las 10 categorías de mayor prioridad
            $sql = "
            SELECT * FROM question_categories 
            WHERE is_herco_category = 1 
            AND herco_version = '2024' 
            AND is_active = 1
            AND priority = 'high'
            ORDER BY order_index ASC
            LIMIT 10
            ";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error obteniendo categorías para encuesta express: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de categorías
     */
    public function getCategoriesStats()
    {
        try {
            $connection = $this->getConnection();
            
            $sql = "
            SELECT 
                qc.*,
                COUNT(q.id) as questions_count,
                SUM(CASE WHEN q.is_active = 1 THEN 1 ELSE 0 END) as active_questions,
                SUM(CASE WHEN q.is_herco_default = 1 THEN 1 ELSE 0 END) as herco_default_questions
            FROM question_categories qc
            LEFT JOIN questions q ON qc.id = q.category_id
            WHERE qc.is_herco_category = 1 AND qc.herco_version = '2024'
            GROUP BY qc.id
            ORDER BY qc.order_index ASC
            ";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de categorías: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Evaluar puntuación según rangos HERCO
     */
    public function evaluateScore($score)
    {
        foreach (self::EVALUATION_RANGES as $level => $range) {
            if ($score >= $range['min'] && $score <= $range['max']) {
                return [
                    'level' => $level,
                    'label' => $range['label'],
                    'color' => $range['color'],
                    'score' => $score
                ];
            }
        }
        
        // Por defecto, crítico
        return [
            'level' => 'critical',
            'label' => 'Crítico',
            'color' => '#ef4444',
            'score' => $score
        ];
    }
    
    /**
     * Obtener mapa de colores por categoría
     */
    public function getColorMap()
    {
        try {
            $connection = $this->getConnection();
            
            $sql = "SELECT herco_code, color FROM question_categories WHERE is_herco_category = 1 AND herco_version = '2024'";
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            
            $colorMap = [];
            while ($row = $stmt->fetch()) {
                $colorMap[$row['herco_code']] = $row['color'];
            }
            
            return $colorMap;
            
        } catch (Exception $e) {
            error_log("Error obteniendo mapa de colores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener resumen de categorías para dashboard
     */
    public function getDashboardSummary()
    {
        try {
            $categories = $this->getHercoCategories();
            
            $summary = [
                'total_categories' => count($categories),
                'high_priority' => 0,
                'medium_priority' => 0,
                'low_priority' => 0,
                'total_questions' => 0,
                'avg_target_score' => 0
            ];
            
            $totalTargetScore = 0;
            
            foreach ($categories as $category) {
                // Contar por prioridad
                switch ($category['priority']) {
                    case 'high':
                        $summary['high_priority']++;
                        break;
                    case 'medium':
                        $summary['medium_priority']++;
                        break;
                    case 'low':
                        $summary['low_priority']++;
                        break;
                }
                
                $summary['total_questions'] += $category['questions_count'];
                $totalTargetScore += $category['target_score'];
            }
            
            $summary['avg_target_score'] = $summary['total_categories'] > 0 ? 
                round($totalTargetScore / $summary['total_categories'], 2) : 0;
            
            return $summary;
            
        } catch (Exception $e) {
            error_log("Error obteniendo resumen para dashboard: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar categorías
     */
    public function search($term)
    {
        try {
            $conditions = '(name LIKE ? OR description LIKE ? OR herco_code LIKE ?) AND is_herco_category = 1 AND herco_version = ?';
            $params = ["%{$term}%", "%{$term}%", "%{$term}%", '2024'];
            
            return $this->where($conditions, $params, 'order_index', 'ASC');
            
        } catch (Exception $e) {
            error_log("Error buscando categorías: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualizar orden de categorías
     */
    public function updateOrder($categoryOrders)
    {
        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            
            $sql = "UPDATE question_categories SET order_index = ? WHERE id = ?";
            $stmt = $connection->prepare($sql);
            
            foreach ($categoryOrders as $categoryId => $orderIndex) {
                $stmt->execute([$orderIndex, $categoryId]);
            }
            
            $connection->commit();
            return true;
            
        } catch (Exception $e) {
            if ($connection->inTransaction()) {
                $connection->rollback();
            }
            error_log("Error actualizando orden de categorías: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener configuración completa de una categoría
     */
    public function getFullConfiguration($categoryId)
    {
        try {
            $category = $this->find($categoryId);
            
            if (!$category) {
                return null;
            }
            
            // Agregar información adicional
            $category['questions_count'] = $this->getQuestionsCount($categoryId);
            $category['dimensions_array'] = json_decode($category['dimensions'], true) ?? [];
            $category['priority_label'] = self::PRIORITY_LEVELS[$category['priority']] ?? 'Media';
            $category['evaluation_range'] = $this->evaluateScore($category['target_score']);
            
            return $category;
            
        } catch (Exception $e) {
            error_log("Error obteniendo configuración completa de categoría: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Contar preguntas de una categoría
     */
    private function getQuestionsCount($categoryId)
    {
        try {
            $connection = $this->getConnection();
            
            $sql = "SELECT COUNT(*) FROM questions WHERE category_id = ? AND is_active = 1";
            $stmt = $connection->prepare($sql);
            $stmt->execute([$categoryId]);
            
            return (int) $stmt->fetchColumn();
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Exportar configuración HERCO para reportes
     */
    public function exportHercoConfiguration()
    {
        try {
            $categories = $this->getHercoCategories();
            
            $export = [
                'version' => '2024',
                'total_categories' => count($categories),
                'generated_at' => date('Y-m-d H:i:s'),
                'categories' => []
            ];
            
            foreach ($categories as $category) {
                $export['categories'][] = [
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'herco_code' => $category['herco_code'],
                    'description' => $category['description'],
                    'priority' => $category['priority'],
                    'target_score' => $category['target_score'],
                    'dimensions' => json_decode($category['dimensions'], true),
                    'order_index' => $category['order_index'],
                    'questions_count' => $category['questions_count']
                ];
            }
            
            return $export;
            
        } catch (Exception $e) {
            error_log("Error exportando configuración HERCO: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener rangos de evaluación
     */
    public static function getEvaluationRanges()
    {
        return self::EVALUATION_RANGES;
    }
    
    /**
     * Obtener niveles de prioridad
     */
    public static function getPriorityLevels()
    {
        return self::PRIORITY_LEVELS;
    }
    
    /**
     * Obtener categorías HERCO 2024 (constante)
     */
    public static function getHerco2024Categories()
    {
        return self::HERCO_CATEGORIES_2024;
    }
    
    /**
     * Validar código HERCO
     */
    public function validateHercoCode($code)
    {
        $validCodes = array_column(self::HERCO_CATEGORIES_2024, 'herco_code');
        return in_array($code, $validCodes);
    }
    
    /**
     * Generar reporte de configuración de categorías
     */
    public function generateConfigurationReport()
    {
        try {
            $stats = $this->getCategoriesStats();
            $summary = $this->getDashboardSummary();
            
            return [
                'summary' => $summary,
                'categories' => $stats,
                'evaluation_ranges' => self::EVALUATION_RANGES,
                'priority_levels' => self::PRIORITY_LEVELS,
                'generated_at' => date('Y-m-d H:i:s'),
                'version' => '2024'
            ];
            
        } catch (Exception $e) {
            error_log("Error generando reporte de configuración: " . $e->getMessage());
            return null;
        }
    }
}
?>