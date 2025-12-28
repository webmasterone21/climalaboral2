<?php
/**
 * Modelo de Encuestas - VERSIÃ“N CORREGIDA
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * GestiÃ³n de datos de encuestas: CRUD, validaciones,
 * relaciones con preguntas y participantes, estadÃ­sticas.
 * 
 * CORRECCIONES APLICADAS:
 * âœ… VerificaciÃ³n de dependencias de base de datos
 * âœ… Validaciones robustas de datos
 * âœ… Manejo de errores mejorado
 * âœ… MÃ©todos de consulta optimizados
 * âœ… Relaciones sin dependencias externas
 * 
 * @package EncuestasHERCO\Models
 * @version 2.0.0
 * @author Sistema HERCO
 */

class Survey extends Model
{
    protected $table = 'surveys';
    protected $primaryKey = 'id';
    
    // Estados vÃ¡lidos de encuesta
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';
    
    // Tipos de encuesta
    const TYPE_HERCO = 'herco';
    const TYPE_CUSTOM = 'custom';
    const TYPE_PULSE = 'pulse';
    
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'status',
        'start_date',
        'end_date',
        'created_by'
        // created_at y updated_at se gestionan automáticamente por la tabla
    ];
    
    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Validaciones para crear encuesta
     */
    public function validateCreate($data)
    {
        $errors = [];
        
        // TÃ­tulo requerido
        if (empty($data['title'])) {
            $errors['title'] = 'El tÃ­tulo es requerido';
        } elseif (strlen($data['title']) < 5) {
            $errors['title'] = 'El tÃ­tulo debe tener al menos 5 caracteres';
        } elseif (strlen($data['title']) > 255) {
            $errors['title'] = 'El tÃ­tulo no puede exceder 255 caracteres';
        }
        
        // Descripción requerida
        if (empty($data['description'])) {
            $errors['description'] = 'La descripciÃ³n es requerida';
        } elseif (strlen($data['description']) < 10) {
            $errors['description'] = 'La descripciÃ³n debe tener al menos 10 caracteres';
        }
        
        // Tipo válido (OPCIONAL - no existe en tabla actual)
        if (!empty($data['type']) && !in_array($data['type'], $this->getValidTypes())) {
            $errors['type'] = 'Tipo de encuesta invÃ¡lido';
        }
        
        // Company ID requerido
        if (empty($data['company_id'])) {
            $errors['company_id'] = 'ID de empresa requerido';
        }
        
        // Fechas vÃ¡lidas
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $startDate = strtotime($data['start_date']);
            $endDate = strtotime($data['end_date']);
            
            if ($startDate === false) {
                $errors['start_date'] = 'Fecha de inicio invÃ¡lida';
            }
            
            if ($endDate === false) {
                $errors['end_date'] = 'Fecha de fin invÃ¡lida';
            }
            
            if ($startDate && $endDate && $endDate <= $startDate) {
                $errors['end_date'] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validaciones para actualizar encuesta
     */
    public function validateUpdate($data, $id)
    {
        $errors = $this->validateCreate($data);
        
        // Verificar que la encuesta existe
        try {
            $survey = $this->findById($id);
            if (!$survey) {
                $errors['id'] = 'Encuesta no encontrada';
            } else {
                // Validaciones especÃ­ficas para actualizaciÃ³n
                if ($survey['status'] === self::STATUS_COMPLETED) {
                    $errors['status'] = 'No se puede modificar una encuesta completada';
                }
                
                // Si tiene participantes, restringir ciertos cambios
                if ($this->hasParticipants($id)) {
                    if (isset($data['type']) && $data['type'] !== $survey['type']) {
                        $errors['type'] = 'No se puede cambiar el tipo de encuesta con participantes';
                    }
                    
                    if (isset($data['anonymous']) && $data['anonymous'] != $survey['anonymous']) {
                        $errors['anonymous'] = 'No se puede cambiar la configuraciÃ³n de anonimato con participantes';
                    }
                }
            }
        } catch (Exception $e) {
            $errors['database'] = 'Error validando encuesta';
        }
        
        return $errors;
    }
    
    /**
     * Crear nueva encuesta
     * CORREGIDO: Solo usa campos que existen en la tabla surveys
     */
    public function create($data)
    {
        try {
            // Validar datos
            $errors = $this->validateCreate($data);
            if (!empty($errors)) {
                throw new ValidationException('Datos invÃ¡lidos', $errors);
            }
            
            // Preparar datos SOLO con campos que existen en la tabla
            $surveyData = [
                'company_id' => $data['company_id'],
                'title' => $this->sanitizeInput($data['title']),
                'description' => $this->sanitizeInput($data['description']),
                'status' => $data['status'] ?? self::STATUS_DRAFT,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'created_by' => $data['created_by'] ?? null
                // created_at y updated_at se manejan automáticamente por la tabla
            ];
            
            // Insertar en base de datos
            $sql = "INSERT INTO {$this->table} (" . implode(', ', array_keys($surveyData)) . ") 
                    VALUES (" . implode(', ', array_fill(0, count($surveyData), '?')) . ")";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(array_values($surveyData));
            
            if ($result) {
                $surveyId = $this->db->lastInsertId();
                
                // Si es tipo HERCO, crear preguntas por defecto
                if (isset($data['type']) && $data['type'] === self::TYPE_HERCO) {
                    $this->createHercoQuestions($surveyId);
                }
                
                return $surveyId;
            }
            
            throw new Exception('Error insertando encuesta en base de datos');
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log("Error creando encuesta: " . $e->getMessage());
            throw new Exception('Error creando encuesta: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualizar encuesta existente
     */
    public function update($id, $data)
    {
        try {
            // Validar datos
            $errors = $this->validateUpdate($data, $id);
            if (!empty($errors)) {
                throw new ValidationException('Datos invÃ¡lidos', $errors);
            }
            
            // Preparar datos de actualizaciÃ³n
            $updateData = [];
            $allowedFields = [
                'title', 'description', 'status', 'start_date', 'end_date',
                'instructions', 'thank_you_message', 'anonymous', 
                'allow_multiple_responses', 'randomize_questions', 
                'show_progress', 'auto_save'
            ];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    if (in_array($field, ['title', 'description', 'instructions', 'thank_you_message'])) {
                        $updateData[$field] = $this->sanitizeInput($data[$field]);
                    } elseif (in_array($field, ['anonymous', 'allow_multiple_responses', 'randomize_questions', 'show_progress', 'auto_save'])) {
                        $updateData[$field] = !empty($data[$field]) ? 1 : 0;
                    } else {
                        $updateData[$field] = $data[$field];
                    }
                }
            }
            
            // Actualizar configuraciones si se proporcionan
            if (isset($data['settings'])) {
                $updateData['settings'] = $this->prepareSettings($data);
            }
            
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            // Construir query de actualizaciÃ³n
            $setClause = implode(', ', array_map(function($field) {
                return "{$field} = ?";
            }, array_keys($updateData)));
            
            $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";
            $params = array_values($updateData);
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                return $stmt->rowCount() > 0;
            }
            
            throw new Exception('Error actualizando encuesta en base de datos');
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log("Error actualizando encuesta {$id}: " . $e->getMessage());
            throw new Exception('Error actualizando encuesta: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener encuesta con estadÃ­sticas
     */
    public function findWithStats($id, $companyId = null)
    {
        try {
            $survey = $this->findById($id);
            
            if (!$survey) {
                return null;
            }
            
            // Verificar empresa si se proporciona
            if ($companyId && $survey['company_id'] != $companyId) {
                return null;
            }
            
            // Agregar estadÃ­sticas
            $survey['stats'] = $this->getSurveyStats($id);
            $survey['settings_decoded'] = $this->decodeSettings($survey['settings']);
            
            return $survey;
            
        } catch (Exception $e) {
            error_log("Error obteniendo encuesta con estadÃ­sticas {$id}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener encuestas por empresa con filtros
     */
    public function findByCompany($companyId, $filters = [])
    {
        try {
            $conditions = ['company_id = ?'];
            $params = [$companyId];
            
            // Filtro por estado
            if (!empty($filters['status'])) {
                $conditions[] = 'status = ?';
                $params[] = $filters['status'];
            }
            
            // Filtro por tipo
            if (!empty($filters['type'])) {
                $conditions[] = 'type = ?';
                $params[] = $filters['type'];
            }
            
            // Filtro por bÃºsqueda
            if (!empty($filters['search'])) {
                $conditions[] = '(title LIKE ? OR description LIKE ?)';
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Filtro por fechas
            if (!empty($filters['date_from'])) {
                $conditions[] = 'created_at >= ?';
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $conditions[] = 'created_at <= ?';
                $params[] = $filters['date_to'] . ' 23:59:59';
            }
            
            $whereClause = implode(' AND ', $conditions);
            $orderBy = $filters['order_by'] ?? 'created_at DESC';
            
            return $this->findByCondition($whereClause, $params, $orderBy);
            
        } catch (Exception $e) {
            error_log("Error obteniendo encuestas por empresa {$companyId}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadÃ­sticas de una encuesta
     */
    public function getSurveyStats($surveyId)
    {
        try {
            $stats = [
                'total_participants' => 0,
                'pending_participants' => 0,
                'completed_responses' => 0,
                'in_progress_responses' => 0,
                'completion_rate' => 0,
                'total_questions' => 0,
                'average_completion_time' => 0
            ];
            
            // Contar participantes si la tabla existe
            if ($this->tableExists('participants')) {
                $sql = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
                        FROM participants 
                        WHERE survey_id = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$surveyId]);
                $result = $stmt->fetch();
                
                if ($result) {
                    $stats['total_participants'] = (int)$result['total'];
                    $stats['pending_participants'] = (int)$result['pending'];
                    $stats['completed_responses'] = (int)$result['completed'];
                    $stats['in_progress_responses'] = (int)$result['in_progress'];
                    
                    if ($stats['total_participants'] > 0) {
                        $stats['completion_rate'] = round(
                            ($stats['completed_responses'] / $stats['total_participants']) * 100, 
                            1
                        );
                    }
                }
            }
            
            // Contar preguntas si la tabla existe
            if ($this->tableExists('questions')) {
                $sql = "SELECT COUNT(*) as total FROM questions WHERE survey_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$surveyId]);
                $result = $stmt->fetch();
                
                if ($result) {
                    $stats['total_questions'] = (int)$result['total'];
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadÃ­sticas de encuesta {$surveyId}: " . $e->getMessage());
            return [
                'total_participants' => 0,
                'pending_participants' => 0,
                'completed_responses' => 0,
                'in_progress_responses' => 0,
                'completion_rate' => 0,
                'total_questions' => 0,
                'average_completion_time' => 0
            ];
        }
    }
    
    /**
     * Activar encuesta
     */
    public function activate($id, $companyId = null)
    {
        try {
            $survey = $this->findById($id);
            
            if (!$survey) {
                throw new Exception('Encuesta no encontrada');
            }
            
            if ($companyId && $survey['company_id'] != $companyId) {
                throw new Exception('Sin permisos para activar esta encuesta');
            }
            
            if ($survey['status'] === self::STATUS_ACTIVE) {
                throw new Exception('La encuesta ya estÃ¡ activa');
            }
            
            // Validar que tenga al menos una pregunta
            $stats = $this->getSurveyStats($id);
            if ($stats['total_questions'] === 0) {
                throw new Exception('No se puede activar una encuesta sin preguntas');
            }
            
            // Activar encuesta
            $updateData = [
                'status' => self::STATUS_ACTIVE,
                'start_date' => $survey['start_date'] ?: date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->update($id, $updateData);
            
        } catch (Exception $e) {
            error_log("Error activando encuesta {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Pausar encuesta
     */
    public function pause($id, $companyId = null)
    {
        try {
            $survey = $this->findById($id);
            
            if (!$survey) {
                throw new Exception('Encuesta no encontrada');
            }
            
            if ($companyId && $survey['company_id'] != $companyId) {
                throw new Exception('Sin permisos para pausar esta encuesta');
            }
            
            if ($survey['status'] !== self::STATUS_ACTIVE) {
                throw new Exception('Solo se pueden pausar encuestas activas');
            }
            
            return $this->update($id, [
                'status' => self::STATUS_PAUSED,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error pausando encuesta {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Completar encuesta
     */
    public function complete($id, $companyId = null)
    {
        try {
            $survey = $this->findById($id);
            
            if (!$survey) {
                throw new Exception('Encuesta no encontrada');
            }
            
            if ($companyId && $survey['company_id'] != $companyId) {
                throw new Exception('Sin permisos para completar esta encuesta');
            }
            
            if ($survey['status'] === self::STATUS_COMPLETED) {
                throw new Exception('La encuesta ya estÃ¡ completada');
            }
            
            return $this->update($id, [
                'status' => self::STATUS_COMPLETED,
                'end_date' => $survey['end_date'] ?: date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error completando encuesta {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Verificar si la encuesta tiene participantes
     */
    public function hasParticipants($surveyId)
    {
        try {
            if (!$this->tableExists('participants')) {
                return false;
            }
            
            $sql = "SELECT COUNT(*) as count FROM participants WHERE survey_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surveyId]);
            $result = $stmt->fetch();
            
            return $result && $result['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Error verificando participantes de encuesta {$surveyId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si la encuesta tiene respuestas
     */
    public function hasResponses($surveyId)
    {
        try {
            if (!$this->tableExists('responses')) {
                return false;
            }
            
            $sql = "SELECT COUNT(*) as count 
                    FROM responses r 
                    INNER JOIN questions q ON r.question_id = q.id 
                    WHERE q.survey_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surveyId]);
            $result = $stmt->fetch();
            
            return $result && $result['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Error verificando respuestas de encuesta {$surveyId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Duplicar encuesta
     */
    public function duplicate($id, $newTitle = null, $companyId = null)
    {
        try {
            $survey = $this->findById($id);
            
            if (!$survey) {
                throw new Exception('Encuesta original no encontrada');
            }
            
            if ($companyId && $survey['company_id'] != $companyId) {
                throw new Exception('Sin permisos para duplicar esta encuesta');
            }
            
            // Preparar datos para la nueva encuesta
            $newData = $survey;
            unset($newData['id']);
            
            $newData['title'] = $newTitle ?: $survey['title'] . ' (Copia)';
            $newData['status'] = self::STATUS_DRAFT;
            $newData['start_date'] = null;
            $newData['end_date'] = null;
            $newData['created_at'] = date('Y-m-d H:i:s');
            $newData['updated_at'] = date('Y-m-d H:i:s');
            
            // Crear nueva encuesta
            $newSurveyId = $this->create($newData);
            
            // Duplicar preguntas si existen
            if ($this->tableExists('questions')) {
                $this->duplicateQuestions($id, $newSurveyId);
            }
            
            return $newSurveyId;
            
        } catch (Exception $e) {
            error_log("Error duplicando encuesta {$id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear preguntas HERCO por defecto
     */
    private function createHercoQuestions($surveyId)
    {
        try {
            if (!$this->tableExists('questions') || !$this->tableExists('question_categories')) {
                return false;
            }
            
            // Obtener categorÃ­as HERCO
            $categories = $this->getHercoCategories();
            
            foreach ($categories as $categoryId => $categoryName) {
                // Crear pregunta bÃ¡sica para cada categorÃ­a
                $questionData = [
                    'survey_id' => $surveyId,
                    'category_id' => $categoryId,
                    'question_text' => "Â¿QuÃ© tan satisfecho estÃ¡ con {$categoryName}?",
                    'question_type' => 'likert_5',
                    'required' => 1,
                    'order_index' => $categoryId,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $sql = "INSERT INTO questions (" . implode(', ', array_keys($questionData)) . ") 
                        VALUES (" . implode(', ', array_fill(0, count($questionData), '?')) . ")";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute(array_values($questionData));
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error creando preguntas HERCO para encuesta {$surveyId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Duplicar preguntas de una encuesta
     */
    private function duplicateQuestions($originalSurveyId, $newSurveyId)
    {
        try {
            $sql = "SELECT * FROM questions WHERE survey_id = ? ORDER BY order_index";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$originalSurveyId]);
            $questions = $stmt->fetchAll();
            
            foreach ($questions as $question) {
                unset($question['id']);
                $question['survey_id'] = $newSurveyId;
                $question['created_at'] = date('Y-m-d H:i:s');
                
                $insertSql = "INSERT INTO questions (" . implode(', ', array_keys($question)) . ") 
                              VALUES (" . implode(', ', array_fill(0, count($question), '?')) . ")";
                
                $insertStmt = $this->db->prepare($insertSql);
                $insertStmt->execute(array_values($question));
            }
            
        } catch (Exception $e) {
            error_log("Error duplicando preguntas: " . $e->getMessage());
        }
    }
    
    /**
     * Preparar configuraciones como JSON
     */
    private function prepareSettings($data)
    {
        $settings = [];
        
        // Configuraciones especÃ­ficas de la encuesta
        $settingFields = [
            'email_notifications',
            'reminder_enabled',
            'reminder_days',
            'completion_redirect_url',
            'custom_css',
            'logo_url',
            'color_scheme'
        ];
        
        foreach ($settingFields as $field) {
            if (isset($data[$field])) {
                $settings[$field] = $data[$field];
            }
        }
        
        return json_encode($settings);
    }
    
    /**
     * Decodificar configuraciones JSON
     */
    private function decodeSettings($settingsJson)
    {
        if (empty($settingsJson)) {
            return [];
        }
        
        $decoded = json_decode($settingsJson, true);
        return is_array($decoded) ? $decoded : [];
    }
    
    /**
     * Obtener categorÃ­as HERCO
     */
    private function getHercoCategories()
    {
        try {
            if ($this->tableExists('question_categories')) {
                $sql = "SELECT id, name FROM question_categories ORDER BY id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $categories = $stmt->fetchAll();
                
                $result = [];
                foreach ($categories as $category) {
                    $result[$category['id']] = $category['name'];
                }
                
                return $result;
            }
            
            // CategorÃ­as por defecto si no hay tabla
            return [
                1 => 'SatisfacciÃ³n Laboral',
                2 => 'ParticipaciÃ³n y AutonomÃ­a',
                3 => 'ComunicaciÃ³n y Objetivos',
                4 => 'Equilibrio y EvaluaciÃ³n',
                5 => 'DistribuciÃ³n y Carga de Trabajo',
                6 => 'Reconocimiento y PromociÃ³n',
                7 => 'Ambiente de Trabajo',
                8 => 'CapacitaciÃ³n',
                9 => 'TecnologÃ­a y Recursos',
                10 => 'ColaboraciÃ³n y CompaÃ±erismo',
                11 => 'Normativas y Regulaciones',
                12 => 'CompensaciÃ³n y Beneficios',
                13 => 'Bienestar y Salud',
                14 => 'Seguridad en el Trabajo',
                15 => 'InformaciÃ³n y ComunicaciÃ³n',
                16 => 'Relaciones con Supervisores',
                17 => 'Feedback y Reconocimiento',
                18 => 'Diversidad e InclusiÃ³n'
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo categorÃ­as HERCO: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener tipos vÃ¡lidos de encuesta
     */
    public function getValidTypes()
    {
        return [
            self::TYPE_HERCO,
            self::TYPE_CUSTOM,
            self::TYPE_PULSE
        ];
    }
    
    /**
     * Obtener estados vÃ¡lidos de encuesta
     */
    public function getValidStatuses()
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_ACTIVE,
            self::STATUS_PAUSED,
            self::STATUS_COMPLETED,
            self::STATUS_ARCHIVED
        ];
    }
    
    /**
     * Obtener label de estado
     */
    public function getStatusLabel($status)
    {
        $labels = [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_PAUSED => 'Pausada',
            self::STATUS_COMPLETED => 'Completada',
            self::STATUS_ARCHIVED => 'Archivada'
        ];
        
        return $labels[$status] ?? 'Desconocido';
    }
    
    /**
     * Obtener label de tipo
     */
    public function getTypeLabel($type)
    {
        $labels = [
            self::TYPE_HERCO => 'HERCO 2024',
            self::TYPE_CUSTOM => 'Personalizada',
            self::TYPE_PULSE => 'Pulso'
        ];
        
        return $labels[$type] ?? 'Desconocido';
    }
    
    /**
     * Verificar si una tabla existe
     */
    private function tableExists($tableName)
    {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tableName]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Sanitizar entrada de datos
     */
    private function sanitizeInput($input)
    {
        if (is_string($input)) {
            return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
        }
        return $input;
    }
}

/**
 * ExcepciÃ³n personalizada para validaciones
 */
class ValidationException extends Exception
{
    private $errors;
    
    public function __construct($message, $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}
?>