<?php
/**
 * Modelo Question
 * Gestión completa de preguntas del sistema de encuestas
 * Compatible con metodología HERCO 2024
 * 
 * @package Models
 * @version 2.0
 * @author Sistema de Encuestas HERCO
 */

class Question extends Model
{
    // ==========================================
    // CONFIGURACIÓN DE LA TABLA
    // ==========================================
    
    protected $table = 'questions';
    protected $primaryKey = 'id';
    protected $timestamps = ['created_at', 'updated_at'];
    
    protected $fillable = [
        'survey_id',
        'category_id',
        'question_type_id',
        'title',
        'description',
        'options',
        'is_required',
        'order_index',
        'validation_rules',
        'conditional_logic',
        'help_text',
        'placeholder',
        'min_value',
        'max_value',
        'status'
    ];
    
    protected $casts = [
        'is_required' => 'boolean',
        'order_index' => 'integer',
        'min_value' => 'integer',
        'max_value' => 'integer'
    ];
    
    // ==========================================
    // CONSTANTES DE TIPOS DE PREGUNTAS
    // ==========================================
    
    const TYPE_TEXT = 1;
    const TYPE_TEXTAREA = 2;
    const TYPE_NUMBER = 3;
    const TYPE_EMAIL = 4;
    const TYPE_PHONE = 5;
    const TYPE_DATE = 6;
    const TYPE_TIME = 7;
    const TYPE_DATETIME = 8;
    const TYPE_MULTIPLE_CHOICE = 9;
    const TYPE_CHECKBOX = 10;
    const TYPE_DROPDOWN = 11;
    const TYPE_RADIO = 12;
    const TYPE_LIKERT_5 = 13;
    const TYPE_LIKERT_7 = 14;
    const TYPE_YES_NO = 15;
    const TYPE_RATING = 16;
    const TYPE_SLIDER = 17;
    const TYPE_FILE_UPLOAD = 18;
    const TYPE_MATRIX = 19;
    const TYPE_RANKING = 20;
    
    // ==========================================
    // 18 CATEGORÍAS HERCO 2024 (IDs)
    // ==========================================
    
    const CATEGORY_SATISFACCION_LABORAL = 1;
    const CATEGORY_PARTICIPACION_AUTONOMIA = 2;
    const CATEGORY_COMUNICACION_OBJETIVOS = 3;
    const CATEGORY_EQUILIBRIO_EVALUACION = 4;
    const CATEGORY_DISTRIBUCION_CARGA = 5;
    const CATEGORY_RECONOCIMIENTO_PROMOCION = 6;
    const CATEGORY_AMBIENTE_TRABAJO = 7;
    const CATEGORY_CAPACITACION = 8;
    const CATEGORY_TECNOLOGIA_RECURSOS = 9;
    const CATEGORY_COLABORACION_COMPANERISMO = 10;
    const CATEGORY_NORMATIVAS_REGULACIONES = 11;
    const CATEGORY_COMPENSACION_BENEFICIOS = 12;
    const CATEGORY_BIENESTAR_SALUD = 13;
    const CATEGORY_SEGURIDAD_TRABAJO = 14;
    const CATEGORY_INFORMACION_COMUNICACION = 15;
    const CATEGORY_RELACIONES_SUPERVISORES = 16;
    const CATEGORY_FEEDBACK_RECONOCIMIENTO = 17;
    const CATEGORY_DIVERSIDAD_INCLUSION = 18;
    
    // ==========================================
    // CONSTANTES DE ESTADO
    // ==========================================
    
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DRAFT = 'draft';
    
    // ==========================================
    // REGLAS DE VALIDACIÓN
    // ==========================================
    
    protected $validationRules = [
        'survey_id' => 'required|integer',
        'category_id' => 'required|integer',
        'question_type_id' => 'required|integer',
        'title' => 'required|string|max:500',
        'description' => 'nullable|string|max:2000',
        'options' => 'nullable|json',
        'is_required' => 'boolean',
        'order_index' => 'required|integer|min:0',
        'validation_rules' => 'nullable|json',
        'conditional_logic' => 'nullable|json',
        'help_text' => 'nullable|string|max:500',
        'placeholder' => 'nullable|string|max:255',
        'min_value' => 'nullable|integer',
        'max_value' => 'nullable|integer',
        'status' => 'required|in:active,inactive,draft'
    ];
    
    // ==========================================
    // CONSTRUCTOR
    // ==========================================
    
    public function __construct()
    {
        parent::__construct();
    }
    
    // ==========================================
    // MÉTODOS CRUD PRINCIPALES
    // ==========================================
    
    /**
     * Crear nueva pregunta con validaciones completas
     * 
     * @param array $data Datos de la pregunta
     * @return mixed ID de la pregunta creada o false
     */
    public function createQuestion($data)
    {
        try {
            // Validar datos básicos
            $errors = $this->validateQuestionData($data);
            if (!empty($errors)) {
                throw new Exception('Errores de validación: ' . implode(', ', $errors));
            }
            
            // Validar opciones según el tipo de pregunta
            if (isset($data['question_type_id']) && isset($data['options'])) {
                $optionsError = $this->validateOptionsByType(
                    $data['question_type_id'], 
                    $data['options']
                );
                if ($optionsError) {
                    throw new Exception($optionsError);
                }
            }
            
            // Preparar opciones según el tipo
            if (isset($data['question_type_id'])) {
                $data['options'] = $this->prepareOptions(
                    $data['question_type_id'], 
                    $data['options'] ?? null
                );
            }
            
            // Obtener el siguiente order_index si no se proporciona
            if (!isset($data['order_index'])) {
                $data['order_index'] = $this->getNextOrderIndex($data['survey_id']);
            }
            
            // Establecer valores por defecto
            $data['status'] = $data['status'] ?? self::STATUS_ACTIVE;
            $data['is_required'] = $data['is_required'] ?? false;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Insertar pregunta
            $questionId = $this->insert($data);
            
            if (!$questionId) {
                throw new Exception('Error al crear la pregunta en la base de datos');
            }
            
            return $questionId;
            
        } catch (Exception $e) {
            error_log("Error creando pregunta: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Actualizar pregunta existente
     * 
     * @param int $id ID de la pregunta
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function updateQuestion($id, $data)
    {
        try {
            $question = $this->findById($id);
            
            if (!$question) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Validar datos
            $errors = $this->validateQuestionData($data, $id);
            if (!empty($errors)) {
                throw new Exception('Errores de validación: ' . implode(', ', $errors));
            }
            
            // Validar opciones si se actualizan
            if (isset($data['question_type_id']) && isset($data['options'])) {
                $optionsError = $this->validateOptionsByType(
                    $data['question_type_id'], 
                    $data['options']
                );
                if ($optionsError) {
                    throw new Exception($optionsError);
                }
            }
            
            // Preparar opciones
            if (isset($data['question_type_id'])) {
                $data['options'] = $this->prepareOptions(
                    $data['question_type_id'], 
                    $data['options'] ?? $question['options']
                );
            }
            
            // Actualizar timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Actualizar en BD
            return $this->update($id, $data);
            
        } catch (Exception $e) {
            error_log("Error actualizando pregunta {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Eliminar pregunta con verificaciones
     * 
     * ⚠️ CORRECCIÓN: Firma compatible con Model::delete($id, $soft = false)
     * 
     * @param mixed $id ID de la pregunta
     * @param bool $soft Si usar soft delete (no implementado para preguntas)
     * @return bool
     */
    public function delete($id, $soft = false)
    {
        try {
            $question = $this->findById($id);
            
            if (!$question) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Verificar si tiene respuestas
            if ($this->hasResponses($id)) {
                throw new Exception('No se puede eliminar una pregunta con respuestas');
            }
            
            // Nota: El parámetro $soft está disponible para compatibilidad con Model
            // pero las preguntas siempre se eliminan físicamente (hard delete)
            // Si en el futuro se necesita soft delete, descomentar la sección siguiente
            
            /*
            if ($soft) {
                // Soft delete - marcar como eliminada
                return $this->update($id, [
                    'status' => self::STATUS_INACTIVE,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);
            }
            */
            
            // Hard delete - eliminación física
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Reordenar preguntas restantes
                $this->reorderAfterDelete($question['survey_id'], $question['order_index']);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error eliminando pregunta {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    // ==========================================
    // CONSULTAS ESPECIALIZADAS
    // ==========================================
    
    /**
     * Obtener todas las preguntas de una encuesta
     * 
     * @param int $surveyId ID de la encuesta
     * @param bool $includeInactive Incluir preguntas inactivas
     * @return array
     */
    public function getBySurvey($surveyId, $includeInactive = false)
    {
        try {
            $sql = "SELECT q.*, 
                           qc.name as category_name,
                           qc.description as category_description,
                           qt.name as type_name,
                           qt.input_type as input_type,
                           (SELECT COUNT(*) FROM question_responses qr WHERE qr.question_id = q.id) as response_count
                    FROM {$this->table} q
                    LEFT JOIN question_categories qc ON q.category_id = qc.id
                    LEFT JOIN question_types qt ON q.question_type_id = qt.id
                    WHERE q.survey_id = ?";
            
            if (!$includeInactive) {
                $sql .= " AND q.status = '" . self::STATUS_ACTIVE . "'";
            }
            
            $sql .= " ORDER BY q.order_index ASC, q.id ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surveyId]);
            
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decodificar campos JSON
            foreach ($questions as &$question) {
                $question['options'] = $this->parseOptions($question['options']);
                $question['validation_rules'] = $this->parseJson($question['validation_rules']);
                $question['conditional_logic'] = $this->parseJson($question['conditional_logic']);
            }
            
            return $questions;
            
        } catch (Exception $e) {
            error_log("Error obteniendo preguntas de encuesta {$surveyId}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener preguntas por categoría HERCO
     * 
     * @param int $surveyId ID de la encuesta
     * @param int $categoryId ID de la categoría HERCO
     * @return array
     */
    public function getByCategoryAndSurvey($surveyId, $categoryId)
    {
        try {
            $sql = "SELECT q.*, 
                           qc.name as category_name,
                           qt.name as type_name
                    FROM {$this->table} q
                    LEFT JOIN question_categories qc ON q.category_id = qc.id
                    LEFT JOIN question_types qt ON q.question_type_id = qt.id
                    WHERE q.survey_id = ? AND q.category_id = ?
                    AND q.status = ?
                    ORDER BY q.order_index ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surveyId, $categoryId, self::STATUS_ACTIVE]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error obteniendo preguntas por categoría: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener pregunta completa con todas sus relaciones
     * 
     * @param int $id ID de la pregunta
     * @return array|null
     */
    public function getFullQuestion($id)
    {
        try {
            $sql = "SELECT q.*,
                           qc.name as category_name,
                           qc.description as category_description,
                           qc.color as category_color,
                           qt.name as type_name,
                           qt.input_type as input_type,
                           qt.validation_schema as type_validation,
                           s.title as survey_title,
                           s.status as survey_status,
                           (SELECT COUNT(*) FROM question_responses qr WHERE qr.question_id = q.id) as response_count
                    FROM {$this->table} q
                    LEFT JOIN question_categories qc ON q.category_id = qc.id
                    LEFT JOIN question_types qt ON q.question_type_id = qt.id
                    LEFT JOIN surveys s ON q.survey_id = s.id
                    WHERE q.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $question = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($question) {
                // Parsear campos JSON
                $question['options'] = $this->parseOptions($question['options']);
                $question['validation_rules'] = $this->parseJson($question['validation_rules']);
                $question['conditional_logic'] = $this->parseJson($question['conditional_logic']);
                $question['type_validation'] = $this->parseJson($question['type_validation']);
            }
            
            return $question;
            
        } catch (Exception $e) {
            error_log("Error obteniendo pregunta completa {$id}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Contar preguntas por encuesta
     * 
     * @param int $surveyId ID de la encuesta
     * @param string $status Estado específico o null para todos
     * @return int
     */
    public function countBySurvey($surveyId, $status = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE survey_id = ?";
            $params = [$surveyId];
            
            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
            
        } catch (Exception $e) {
            error_log("Error contando preguntas: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener estadísticas de preguntas por encuesta
     * 
     * @param int $surveyId ID de la encuesta
     * @return array
     */
    public function getStatsBySurvey($surveyId)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_required = 1 THEN 1 ELSE 0 END) as required,
                        SUM(CASE WHEN is_required = 0 THEN 1 ELSE 0 END) as optional,
                        COUNT(DISTINCT category_id) as categories,
                        COUNT(DISTINCT question_type_id) as types
                    FROM {$this->table}
                    WHERE survey_id = ? AND status = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surveyId, self::STATUS_ACTIVE]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'total' => 0,
                'required' => 0,
                'optional' => 0,
                'categories' => 0,
                'types' => 0
            ];
        }
    }
    
    // ==========================================
    // MÉTODOS DE ORDENAMIENTO
    // ==========================================
    
    /**
     * Reordenar preguntas
     * 
     * @param array $order Array con estructura: [question_id => new_order]
     * @return bool
     */
    public function reorderQuestions($order)
    {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE {$this->table} SET order_index = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            foreach ($order as $questionId => $newOrder) {
                $stmt->execute([$newOrder, $questionId]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error reordenando preguntas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener el siguiente índice de orden para una encuesta
     * 
     * @param int $surveyId ID de la encuesta
     * @return int
     */
    public function getNextOrderIndex($surveyId)
    {
        try {
            $sql = "SELECT COALESCE(MAX(order_index), -1) + 1 as next_order 
                    FROM {$this->table} 
                    WHERE survey_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surveyId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['next_order'];
            
        } catch (Exception $e) {
            error_log("Error obteniendo siguiente orden: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Reordenar después de eliminar una pregunta
     * 
     * @param int $surveyId ID de la encuesta
     * @param int $deletedOrder Orden de la pregunta eliminada
     * @return bool
     */
    private function reorderAfterDelete($surveyId, $deletedOrder)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET order_index = order_index - 1 
                    WHERE survey_id = ? AND order_index > ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$surveyId, $deletedOrder]);
            
        } catch (Exception $e) {
            error_log("Error reordenando después de eliminar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mover pregunta a una posición específica
     * 
     * @param int $questionId ID de la pregunta
     * @param int $newPosition Nueva posición
     * @return bool
     */
    public function moveToPosition($questionId, $newPosition)
    {
        try {
            $question = $this->findById($questionId);
            if (!$question) {
                throw new Exception('Pregunta no encontrada');
            }
            
            $oldPosition = $question['order_index'];
            $surveyId = $question['survey_id'];
            
            if ($oldPosition == $newPosition) {
                return true; // Ya está en la posición correcta
            }
            
            $this->db->beginTransaction();
            
            if ($newPosition < $oldPosition) {
                // Mover hacia arriba
                $sql = "UPDATE {$this->table} 
                        SET order_index = order_index + 1 
                        WHERE survey_id = ? 
                        AND order_index >= ? 
                        AND order_index < ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$surveyId, $newPosition, $oldPosition]);
            } else {
                // Mover hacia abajo
                $sql = "UPDATE {$this->table} 
                        SET order_index = order_index - 1 
                        WHERE survey_id = ? 
                        AND order_index > ? 
                        AND order_index <= ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$surveyId, $oldPosition, $newPosition]);
            }
            
            // Actualizar posición de la pregunta
            $sql = "UPDATE {$this->table} SET order_index = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newPosition, $questionId]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error moviendo pregunta a posición: " . $e->getMessage());
            return false;
        }
    }
    
    // ==========================================
    // MÉTODOS DE VALIDACIÓN
    // ==========================================
    
    /**
     * Validar datos de pregunta
     * 
     * @param array $data Datos a validar
     * @param int|null $id ID para actualización
     * @return array Errores de validación
     */
    private function validateQuestionData($data, $id = null)
    {
        $errors = [];
        
        // Validar survey_id
        if (empty($data['survey_id'])) {
            $errors[] = 'El ID de encuesta es requerido';
        } elseif (!$this->surveyExists($data['survey_id'])) {
            $errors[] = 'La encuesta especificada no existe';
        }
        
        // Validar category_id
        if (empty($data['category_id'])) {
            $errors[] = 'La categoría es requerida';
        } elseif (!$this->categoryExists($data['category_id'])) {
            $errors[] = 'La categoría especificada no existe';
        }
        
        // Validar question_type_id
        if (empty($data['question_type_id'])) {
            $errors[] = 'El tipo de pregunta es requerido';
        } elseif (!$this->questionTypeExists($data['question_type_id'])) {
            $errors[] = 'El tipo de pregunta especificado no existe';
        }
        
        // Validar título
        if (empty($data['title']) || trim($data['title']) === '') {
            $errors[] = 'El título de la pregunta es requerido';
        } elseif (strlen($data['title']) > 500) {
            $errors[] = 'El título no puede exceder 500 caracteres';
        }
        
        // Validar min_value y max_value
        if (isset($data['min_value']) && isset($data['max_value'])) {
            if ($data['min_value'] > $data['max_value']) {
                $errors[] = 'El valor mínimo no puede ser mayor al valor máximo';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar opciones según el tipo de pregunta
     * 
     * @param int $typeId ID del tipo de pregunta
     * @param mixed $options Opciones
     * @return string Error o cadena vacía
     */
    private function validateOptionsByType($typeId, $options)
    {
        $typesWithOptions = [
            self::TYPE_MULTIPLE_CHOICE,
            self::TYPE_CHECKBOX,
            self::TYPE_DROPDOWN,
            self::TYPE_RADIO
        ];
        
        if (in_array($typeId, $typesWithOptions)) {
            if (empty($options)) {
                return 'Las opciones son requeridas para este tipo de pregunta';
            }
            
            $optionsArray = $this->parseOptions($options);
            if (count($optionsArray) < 2) {
                return 'Se requieren al menos 2 opciones para este tipo de pregunta';
            }
            
            if (count($optionsArray) > 20) {
                return 'No se pueden tener más de 20 opciones';
            }
        }
        
        return '';
    }
    
    /**
     * Verificar si una pregunta tiene respuestas
     * 
     * @param int $questionId ID de la pregunta
     * @return bool
     */
    public function hasResponses($questionId)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM question_responses WHERE question_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$questionId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
            
        } catch (Exception $e) {
            error_log("Error verificando respuestas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si existe una encuesta
     * 
     * @param int $surveyId ID de la encuesta
     * @return bool
     */
    private function surveyExists($surveyId)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM surveys WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surveyId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Verificar si existe una categoría
     * 
     * @param int $categoryId ID de la categoría
     * @return bool
     */
    private function categoryExists($categoryId)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM question_categories WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$categoryId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Verificar si existe un tipo de pregunta
     * 
     * @param int $typeId ID del tipo
     * @return bool
     */
    private function questionTypeExists($typeId)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM question_types WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$typeId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    // ==========================================
    // MÉTODOS AUXILIARES
    // ==========================================
    
    /**
     * Preparar opciones según el tipo de pregunta
     * 
     * @param int $typeId ID del tipo
     * @param mixed $options Opciones proporcionadas
     * @return string JSON de opciones
     */
    private function prepareOptions($typeId, $options)
    {
        // Tipos que usan opciones predefinidas (escala Likert)
        switch ($typeId) {
            case self::TYPE_LIKERT_5:
                return json_encode([
                    '1' => 'Muy en desacuerdo',
                    '2' => 'En desacuerdo',
                    '3' => 'Neutral',
                    '4' => 'De acuerdo',
                    '5' => 'Muy de acuerdo'
                ]);
                
            case self::TYPE_LIKERT_7:
                return json_encode([
                    '1' => 'Totalmente en desacuerdo',
                    '2' => 'En desacuerdo',
                    '3' => 'Algo en desacuerdo',
                    '4' => 'Neutral',
                    '5' => 'Algo de acuerdo',
                    '6' => 'De acuerdo',
                    '7' => 'Totalmente de acuerdo'
                ]);
                
            case self::TYPE_YES_NO:
                return json_encode([
                    '1' => 'Sí',
                    '0' => 'No'
                ]);
                
            default:
                // Para otros tipos, usar las opciones proporcionadas
                if (empty($options)) {
                    return json_encode([]);
                }
                
                // Si ya es JSON válido, retornarlo
                if (is_string($options) && $this->isJson($options)) {
                    return $options;
                }
                
                // Si es array, convertir a JSON
                if (is_array($options)) {
                    return json_encode($options);
                }
                
                return json_encode([]);
        }
    }
    
    /**
     * Parsear opciones de JSON a array
     * 
     * @param string $options JSON de opciones
     * @return array
     */
    private function parseOptions($options)
    {
        if (empty($options)) {
            return [];
        }
        
        if (is_array($options)) {
            return $options;
        }
        
        $decoded = json_decode($options, true);
        return is_array($decoded) ? $decoded : [];
    }
    
    /**
     * Parsear JSON genérico
     * 
     * @param string $json Cadena JSON
     * @return mixed
     */
    private function parseJson($json)
    {
        if (empty($json)) {
            return null;
        }
        
        if (is_array($json)) {
            return $json;
        }
        
        return json_decode($json, true);
    }
    
    /**
     * Verificar si una cadena es JSON válido
     * 
     * @param string $string Cadena a verificar
     * @return bool
     */
    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Duplicar pregunta
     * 
     * @param int $questionId ID de la pregunta a duplicar
     * @param int|null $newSurveyId ID de nueva encuesta (opcional)
     * @return int|false ID de la pregunta duplicada
     */
    public function duplicateQuestion($questionId, $newSurveyId = null)
    {
        try {
            $question = $this->findById($questionId);
            
            if (!$question) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Preparar datos para duplicar
            $newData = $question;
            unset($newData['id']);
            unset($newData['created_at']);
            unset($newData['updated_at']);
            unset($newData['response_count']);
            
            // Cambiar encuesta si se proporciona
            if ($newSurveyId) {
                $newData['survey_id'] = $newSurveyId;
            }
            
            // Obtener nuevo orden
            $newData['order_index'] = $this->getNextOrderIndex($newData['survey_id']);
            
            // Modificar título para indicar que es copia
            $newData['title'] = $question['title'] . ' (Copia)';
            
            // Crear nueva pregunta
            return $this->createQuestion($newData);
            
        } catch (Exception $e) {
            error_log("Error duplicando pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar estado de pregunta
     * 
     * @param int $questionId ID de la pregunta
     * @param string $status Nuevo estado
     * @return bool
     */
    public function changeStatus($questionId, $status)
    {
        try {
            $validStatuses = [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DRAFT];
            
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Estado inválido');
            }
            
            return $this->update($questionId, ['status' => $status]);
            
        } catch (Exception $e) {
            error_log("Error cambiando estado de pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener preguntas requeridas de una encuesta
     * 
     * @param int $surveyId ID de la encuesta
     * @return array
     */
    public function getRequiredQuestions($surveyId)
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE survey_id = ? 
                    AND is_required = 1 
                    AND status = ?
                    ORDER BY order_index ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surveyId, self::STATUS_ACTIVE]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error obteniendo preguntas requeridas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clonar todas las preguntas de una encuesta a otra
     * 
     * @param int $sourceSurveyId Encuesta origen
     * @param int $targetSurveyId Encuesta destino
     * @return bool
     */
    public function cloneSurveyQuestions($sourceSurveyId, $targetSurveyId)
    {
        try {
            $questions = $this->getBySurvey($sourceSurveyId, true);
            
            if (empty($questions)) {
                return true; // No hay preguntas que clonar
            }
            
            $this->db->beginTransaction();
            
            foreach ($questions as $question) {
                unset($question['id']);
                unset($question['created_at']);
                unset($question['updated_at']);
                unset($question['response_count']);
                unset($question['category_name']);
                unset($question['type_name']);
                
                $question['survey_id'] = $targetSurveyId;
                
                $this->createQuestion($question);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error clonando preguntas: " . $e->getMessage());
            return false;
        }
    }
}
