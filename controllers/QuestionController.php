<?php
/**
 * Controlador de Preguntas
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * Controlador QuestionController para gestión de preguntas:
 * constructor visual, tipos, categorías, validaciones, plantillas HERCO
 * 
 * @package EncuestasHERCO\Controllers
 * @version 2.0.0
 * @author Sistema HERCO
 */

class QuestionController extends Controller
{
    /**
     * Modelos necesarios
     */
    private $questionModel;
    private $categoryModel;
    private $surveyModel;
    
    /**
     * Inicializar controlador
     */
    protected function initialize()
    {
        // Requerir autenticación
        $this->requireAuth();
        
        // Cargar modelos
        $this->questionModel = new Question();
        $this->categoryModel = new QuestionCategory();
        $this->surveyModel = new Survey();
    }
    
    /**
     * Lista de preguntas (general o por encuesta)
     * 
     * @return void
     */
    public function index()
    {
        try {
            $surveyId = $this->get('survey_id', '');
            $categoryId = $this->get('category_id', '');
            $search = $this->get('search', '');
            
            // Obtener preguntas según filtros
            if ($surveyId) {
                $questions = $this->questionModel->getBySurvey($surveyId);
                $survey = $this->surveyModel->findById($surveyId);
                $pageTitle = 'Preguntas: ' . ($survey['title'] ?? 'Encuesta');
            } elseif ($categoryId) {
                $questions = $this->questionModel->getByCategory($categoryId);
                $category = $this->getCategoryById($categoryId);
                $pageTitle = 'Preguntas: ' . ($category['name'] ?? 'Categoría');
            } else {
                $questions = $this->getAllQuestions($search);
                $pageTitle = 'Todas las Preguntas';
            }
            
            // Obtener categorías y estadísticas
            $categories = $this->getCategories();
            $questionTypes = $this->getQuestionTypes();
            
            $this->render('admin/questions/index', [
                'page_title' => $pageTitle,
                'questions' => $questions,
                'categories' => $categories,
                'question_types' => $questionTypes,
                'filters' => [
                    'survey_id' => $surveyId,
                    'category_id' => $categoryId,
                    'search' => $search
                ]
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Error en index de preguntas: " . $e->getMessage());
            $this->setFlashMessage('Error cargando preguntas', 'error');
            $this->redirect('/admin/dashboard');
        }
    }
    
    /**
     * Constructor visual de preguntas
     * 
     * @return void
     */
    public function builder()
    {
        try {
            $surveyId = $this->get('survey_id');
            
            if (!$surveyId) {
                $this->setFlashMessage('Debe especificar una encuesta', 'error');
                $this->redirect('/admin/surveys');
                return;
            }
            
            // Obtener encuesta
            $survey = $this->surveyModel->findById($surveyId);
            
            if (!$survey) {
                $this->setFlashMessage('Encuesta no encontrada', 'error');
                $this->redirect('/admin/surveys');
                return;
            }
            
            // Verificar permisos
            if (!$this->canAccessSurvey($survey)) {
                $this->setFlashMessage('No tiene permisos para acceder a esta encuesta', 'error');
                $this->redirect('/admin/surveys');
                return;
            }
            
            // Obtener datos necesarios
            $categories = $this->getCategories();
            $questionTypes = $this->getQuestionTypes();
            $existingQuestions = $this->questionModel->getBySurvey($surveyId);
            $templates = $this->getQuestionTemplates();
            
            $this->render('admin/questions/builder', [
                'page_title' => $survey ? 'Constructor: ' . $survey['title'] : 'Constructor de Preguntas',
                'survey' => $survey,
                'survey_id' => $surveyId,
                'categories' => $categories,
                'templates' => $templates,
                'question_types' => $questionTypes,
                'existing_questions' => $existingQuestions,
                'herco_scales' => $this->getHercoScales()
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Error en builder: " . $e->getMessage());
            $this->setFlashMessage('Error cargando constructor', 'error');
            $this->redirect('/admin/questions');
        }
    }
    
    /**
     * Crear nueva pregunta
     * 
     * @return void
     */
    public function create()
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/questions');
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token de seguridad inválido', 403);
            return;
        }
        
        try {
            // Obtener datos del formulario
            $questionData = [
                'survey_id' => $this->post('survey_id', ''),
                'category_id' => $this->post('category_id', ''),
                'question_type_id' => $this->post('question_type_id', ''),
                'title' => trim($this->post('title', '')),
                'description' => trim($this->post('description', '')),
                'is_required' => $this->post('is_required', false),
                'order_index' => $this->post('order_index', null),
                'help_text' => trim($this->post('help_text', '')),
                'placeholder' => trim($this->post('placeholder', '')),
                'min_value' => $this->post('min_value', null),
                'max_value' => $this->post('max_value', null),
                'options' => $this->post('options', null),
                'validation_rules' => $this->post('validation_rules', null),
                'conditional_logic' => $this->post('conditional_logic', null)
            ];
            
            // Crear pregunta
            $questionId = $this->questionModel->createQuestion($questionData);
            
            if (!$questionId) {
                throw new Exception('Error creando la pregunta');
            }
            
            $this->logActivity('question_created', "Pregunta creada: {$questionData['title']}", [
                'question_id' => $questionId,
                'survey_id' => $questionData['survey_id']
            ]);
            
            if ($this->isAjaxRequest()) {
                $this->jsonSuccess([
                    'question_id' => $questionId,
                    'question' => $this->questionModel->findById($questionId)
                ], 'Pregunta creada exitosamente');
            } else {
                $this->setFlashMessage('Pregunta creada exitosamente', 'success');
                $this->redirect('/admin/questions/builder?survey_id=' . $questionData['survey_id']);
            }
            
        } catch (Exception $e) {
            error_log("Error creando pregunta: " . $e->getMessage());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->setFlashMessage($e->getMessage(), 'error');
                $this->redirect('/admin/questions');
            }
        }
    }
    
    /**
     * Actualizar pregunta
     * 
     * @return void
     */
    public function update()
    {
        $questionId = $this->getParam('id');
        
        if (!$this->isPost()) {
            $this->redirect('/admin/questions');
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token de seguridad inválido', 403);
            return;
        }
        
        try {
            $question = $this->questionModel->findById($questionId);
            
            if (!$question) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Verificar permisos
            if (!$this->canEditQuestion($question)) {
                throw new Exception('No tiene permisos para editar esta pregunta');
            }
            
            // Obtener datos de actualización
            $updateData = [
                'category_id' => $this->post('category_id', $question['category_id']),
                'question_type_id' => $this->post('question_type_id', $question['question_type_id']),
                'title' => trim($this->post('title', $question['title'])),
                'description' => trim($this->post('description', $question['description'])),
                'is_required' => $this->post('is_required', $question['is_required']),
                'help_text' => trim($this->post('help_text', $question['help_text'])),
                'placeholder' => trim($this->post('placeholder', $question['placeholder'])),
                'min_value' => $this->post('min_value', $question['min_value']),
                'max_value' => $this->post('max_value', $question['max_value']),
                'options' => $this->post('options', $question['options']),
                'validation_rules' => $this->post('validation_rules', $question['validation_rules']),
                'conditional_logic' => $this->post('conditional_logic', $question['conditional_logic'])
            ];
            
            // Actualizar pregunta
            $updated = $this->questionModel->updateQuestion($questionId, $updateData);
            
            if (!$updated) {
                throw new Exception('Error actualizando la pregunta');
            }
            
            $this->logActivity('question_updated', "Pregunta actualizada: {$updateData['title']}", [
                'question_id' => $questionId
            ]);
            
            if ($this->isAjaxRequest()) {
                $this->jsonSuccess([
                    'question' => $this->questionModel->findById($questionId)
                ], 'Pregunta actualizada exitosamente');
            } else {
                $this->setFlashMessage('Pregunta actualizada exitosamente', 'success');
                $this->redirect('/admin/questions/builder?survey_id=' . $question['survey_id']);
            }
            
        } catch (Exception $e) {
            error_log("Error actualizando pregunta: " . $e->getMessage());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->setFlashMessage($e->getMessage(), 'error');
                $this->redirect('/admin/questions');
            }
        }
    }
    
    /**
     * Eliminar pregunta
     * 
     * @return void
     */
    public function delete()
    {
        $questionId = $this->getParam('id');
        
        if (!$this->isPost()) {
            $this->redirect('/admin/questions');
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token de seguridad inválido', 403);
            return;
        }
        
        try {
            $question = $this->questionModel->findById($questionId);
            
            if (!$question) {
                throw new Exception('Pregunta no encontrada');
            }
            
            if (!$this->canEditQuestion($question)) {
                throw new Exception('No tiene permisos para eliminar esta pregunta');
            }
            
            // Verificar si tiene respuestas
            $hasResponses = $this->questionModel->hasResponses($questionId);
            
            if ($hasResponses && !$this->post('confirm_delete_with_responses', false)) {
                if ($this->isAjaxRequest()) {
                    $this->jsonError('La pregunta tiene respuestas. Confirme la eliminación.', 400, [
                        'has_responses' => true
                    ]);
                } else {
                    $this->setFlashMessage('La pregunta tiene respuestas y no puede ser eliminada', 'error');
                    $this->redirect('/admin/questions');
                }
                return;
            }
            
            // Eliminar pregunta
            $deleted = $this->questionModel->delete($questionId);
            
            if (!$deleted) {
                throw new Exception('Error eliminando la pregunta');
            }
            
            $this->logActivity('question_deleted', "Pregunta eliminada: {$question['title']}", [
                'question_id' => $questionId,
                'survey_id' => $question['survey_id']
            ]);
            
            if ($this->isAjaxRequest()) {
                $this->jsonSuccess(null, 'Pregunta eliminada exitosamente');
            } else {
                $this->setFlashMessage('Pregunta eliminada exitosamente', 'success');
                $this->redirect('/admin/questions/builder?survey_id=' . $question['survey_id']);
            }
            
        } catch (Exception $e) {
            error_log("Error eliminando pregunta: " . $e->getMessage());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->setFlashMessage($e->getMessage(), 'error');
                $this->redirect('/admin/questions');
            }
        }
    }
    
    /**
     * Duplicar pregunta
     * 
     * @return void
     */
    public function duplicate()
    {
        $questionId = $this->getParam('id');
        
        if (!$this->isPost()) {
            $this->redirect('/admin/questions');
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token de seguridad inválido', 403);
            return;
        }
        
        try {
            $question = $this->questionModel->findById($questionId);
            
            if (!$question) {
                throw new Exception('Pregunta no encontrada');
            }
            
            if (!$this->canEditQuestion($question)) {
                throw new Exception('No tiene permisos para duplicar esta pregunta');
            }
            
            // Duplicar pregunta
            $newQuestionId = $this->questionModel->duplicateQuestion($questionId);
            
            if (!$newQuestionId) {
                throw new Exception('Error duplicando la pregunta');
            }
            
            $this->logActivity('question_duplicated', "Pregunta duplicada: {$question['title']}", [
                'original_question_id' => $questionId,
                'new_question_id' => $newQuestionId
            ]);
            
            if ($this->isAjaxRequest()) {
                $this->jsonSuccess([
                    'question_id' => $newQuestionId,
                    'question' => $this->questionModel->findById($newQuestionId)
                ], 'Pregunta duplicada exitosamente');
            } else {
                $this->setFlashMessage('Pregunta duplicada exitosamente', 'success');
                $this->redirect('/admin/questions/builder?survey_id=' . $question['survey_id']);
            }
            
        } catch (Exception $e) {
            error_log("Error duplicando pregunta: " . $e->getMessage());
            
            if ($this->isAjaxRequest()) {
                $this->jsonError($e->getMessage());
            } else {
                $this->setFlashMessage($e->getMessage(), 'error');
                $this->redirect('/admin/questions');
            }
        }
    }
    
    /**
     * Reordenar preguntas
     * 
     * @return void
     */
    public function reorder()
    {
        if (!$this->isPost()) {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        if (!$this->validateCSRF()) {
            $this->jsonError('Token de seguridad inválido', 403);
            return;
        }
        
        try {
            $order = $this->post('order', []);
            
            if (empty($order)) {
                throw new Exception('No se recibió el orden de las preguntas');
            }
            
            // Reordenar preguntas
            $result = $this->questionModel->reorderQuestions($order);
            
            if (!$result) {
                throw new Exception('Error reordenando las preguntas');
            }
            
            $this->logActivity('questions_reordered', 'Preguntas reordenadas', [
                'order' => $order
            ]);
            
            $this->jsonSuccess(null, 'Preguntas reordenadas exitosamente');
            
        } catch (Exception $e) {
            error_log("Error reordenando preguntas: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Gestión de categorías
     * 
     * @return void
     */
    public function categories()
    {
        try {
            $categories = $this->getCategories();
            
            $this->render('admin/questions/categories', [
                'page_title' => 'Categorías HERCO',
                'categories' => $categories
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Error en categorías: " . $e->getMessage());
            $this->setFlashMessage('Error cargando categorías', 'error');
            $this->redirect('/admin/questions');
        }
    }
    
    /**
     * Validar configuración de pregunta
     * 
     * @return void
     */
    public function validateConfig()
    {
        if (!$this->isPost()) {
            $this->jsonError('Método no permitido', 405);
            return;
        }
        
        try {
            $config = $this->post('config', []);
            
            $validation = $this->validateQuestionConfig($config);
            
            $this->jsonSuccess($validation);
            
        } catch (Exception $e) {
            $this->jsonError('Error validando configuración');
        }
    }
    
    /**
     * API: Obtener opciones dinámicas para tipo de pregunta
     * 
     * @return void
     */
    public function apiGetTypeOptions()
    {
        try {
            $typeId = $this->get('type_id', '');
            
            $options = $this->getQuestionTypeOptions($typeId);
            
            $this->jsonSuccess($options);
            
        } catch (Exception $e) {
            $this->jsonError('Error obteniendo opciones');
        }
    }
    
    // ==========================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ==========================================
    
    /**
     * Verificar si puede acceder a la encuesta
     * 
     * @param array $survey Datos de la encuesta
     * @return bool
     */
    private function canAccessSurvey($survey)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        // Por ahora permitir acceso a todos los usuarios autenticados
        // En el futuro cuando se agregue company_id a users, descomentar:
        // if (isset($this->user['company_id']) && isset($survey['company_id'])) {
        //     return $survey['company_id'] == $this->user['company_id'];
        // }
        
        return true;
    }
    
    /**
     * Verificar si puede editar la pregunta
     * 
     * @param array $question Datos de la pregunta
     * @return bool
     */
    private function canEditQuestion($question)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        // Verificar a través de la encuesta
        $survey = $this->surveyModel->findById($question['survey_id']);
        return $survey && $this->canAccessSurvey($survey);
    }
    
    /**
     * Obtener todas las preguntas con filtros
     * 
     * @param string $search Término de búsqueda
     * @return array
     */
    private function getAllQuestions($search = '')
    {
        try {
            $sql = "SELECT 
                        q.*,
                        qc.name as category_name,
                        s.title as survey_title
                    FROM questions q
                    LEFT JOIN question_categories qc ON q.category_id = qc.id
                    LEFT JOIN surveys s ON q.survey_id = s.id
                    WHERE 1=1";
            
            $params = [];
            
            if ($search) {
                $sql .= " AND (q.title LIKE ? OR q.description LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY q.created_at DESC LIMIT 100";
            
            return $this->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Error obteniendo preguntas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener categoría por ID
     * 
     * @param int $id ID de la categoría
     * @return array|null
     */
    private function getCategoryById($id)
    {
        try {
            $sql = "SELECT * FROM question_categories WHERE id = ?";
            $category = $this->fetchOne($sql, [$id]);
            
            // Si no existe en BD, buscar en las categorías por defecto
            if (!$category) {
                $defaultCategories = $this->getDefaultHercoCategories();
                foreach ($defaultCategories as $cat) {
                    if ($cat['id'] == $id) {
                        return $cat;
                    }
                }
            }
            
            return $category;
            
        } catch (Exception $e) {
            error_log("Error obteniendo categoría {$id}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener categorías HERCO
     * 
     * @return array
     */
    private function getCategories()
    {
        try {
            // Intentar cargar desde la base de datos
            $sql = "SELECT * FROM question_categories ORDER BY id ASC";
            $categories = $this->fetchAll($sql);
            
            // Si no hay categorías en BD, retornar las 18 categorías HERCO por defecto
            if (empty($categories)) {
                return $this->getDefaultHercoCategories();
            }
            
            return $categories;
            
        } catch (Exception $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            // En caso de error, retornar categorías por defecto
            return $this->getDefaultHercoCategories();
        }
    }
    
    /**
     * Obtener 18 categorías HERCO por defecto
     * 
     * @return array
     */
    private function getDefaultHercoCategories()
    {
        return [
            ['id' => 1, 'name' => 'Satisfacción Laboral'],
            ['id' => 2, 'name' => 'Participación y Autonomía'],
            ['id' => 3, 'name' => 'Comunicación y Objetivos'],
            ['id' => 4, 'name' => 'Equilibrio y Evaluación'],
            ['id' => 5, 'name' => 'Distribución y Carga de Trabajo'],
            ['id' => 6, 'name' => 'Reconocimiento y Promoción'],
            ['id' => 7, 'name' => 'Ambiente de Trabajo'],
            ['id' => 8, 'name' => 'Capacitación'],
            ['id' => 9, 'name' => 'Tecnología y Recursos'],
            ['id' => 10, 'name' => 'Colaboración y Compañerismo'],
            ['id' => 11, 'name' => 'Normativas y Regulaciones'],
            ['id' => 12, 'name' => 'Compensación y Beneficios'],
            ['id' => 13, 'name' => 'Bienestar y Salud'],
            ['id' => 14, 'name' => 'Seguridad en el Trabajo'],
            ['id' => 15, 'name' => 'Información y Comunicación'],
            ['id' => 16, 'name' => 'Relaciones con Supervisores'],
            ['id' => 17, 'name' => 'Feedback y Reconocimiento'],
            ['id' => 18, 'name' => 'Diversidad e Inclusión']
        ];
    }
    
    /**
     * Obtener tipos de preguntas
     * 
     * @return array
     */
    private function getQuestionTypes()
    {
        return [
            1 => ['id' => 1, 'name' => 'Texto Corto', 'code' => 'text'],
            2 => ['id' => 2, 'name' => 'Texto Largo', 'code' => 'textarea'],
            3 => ['id' => 3, 'name' => 'Número', 'code' => 'number'],
            9 => ['id' => 9, 'name' => 'Opción Múltiple', 'code' => 'multiple_choice'],
            10 => ['id' => 10, 'name' => 'Casillas de Verificación', 'code' => 'checkbox'],
            11 => ['id' => 11, 'name' => 'Lista Desplegable', 'code' => 'dropdown'],
            13 => ['id' => 13, 'name' => 'Escala Likert 5', 'code' => 'likert_5'],
            14 => ['id' => 14, 'name' => 'Escala Likert 7', 'code' => 'likert_7'],
            15 => ['id' => 15, 'name' => 'Sí/No', 'code' => 'yes_no'],
            16 => ['id' => 16, 'name' => 'Calificación', 'code' => 'rating']
        ];
    }
    
    /**
     * Obtener plantillas de preguntas
     * 
     * @return array
     */
    private function getQuestionTemplates()
    {
        return [
            'herco_satisfaction' => [
                'name' => 'Satisfacción Laboral HERCO',
                'category_id' => 1,
                'type_id' => 13,
                'questions' => [
                    '¿Qué tan satisfecho está con su trabajo actual?',
                    '¿Cómo calificaría su nivel de motivación en el trabajo?'
                ]
            ],
            'herco_autonomy' => [
                'name' => 'Participación y Autonomía HERCO',
                'category_id' => 2,
                'type_id' => 13,
                'questions' => [
                    '¿Tiene autonomía para tomar decisiones en su trabajo?',
                    '¿Se siente involucrado en las decisiones importantes?'
                ]
            ]
        ];
    }
    
    /**
     * Obtener escalas HERCO predefinidas
     * 
     * @return array
     */
    private function getHercoScales()
    {
        return [
            'likert_5' => [
                1 => 'Muy en desacuerdo',
                2 => 'En desacuerdo',
                3 => 'Neutral',
                4 => 'De acuerdo',
                5 => 'Muy de acuerdo'
            ],
            'likert_7' => [
                1 => 'Totalmente en desacuerdo',
                2 => 'En desacuerdo',
                3 => 'Algo en desacuerdo',
                4 => 'Neutral',
                5 => 'Algo de acuerdo',
                6 => 'De acuerdo',
                7 => 'Totalmente de acuerdo'
            ],
            'satisfaction' => [
                1 => 'Muy insatisfecho',
                2 => 'Insatisfecho',
                3 => 'Neutral',
                4 => 'Satisfecho',
                5 => 'Muy satisfecho'
            ]
        ];
    }
    
    /**
     * Validar configuración de pregunta
     * 
     * @param array $config Configuración a validar
     * @return array
     */
    private function validateQuestionConfig($config)
    {
        $errors = [];
        $warnings = [];
        
        // Validaciones básicas
        if (empty($config['title'])) {
            $errors[] = 'El título es requerido';
        }
        
        if (empty($config['question_type_id'])) {
            $errors[] = 'El tipo de pregunta es requerido';
        }
        
        // Validaciones específicas por tipo
        if (isset($config['question_type_id'])) {
            $typeId = $config['question_type_id'];
            
            // Tipos que requieren opciones
            if (in_array($typeId, [9, 10, 11, 12])) {
                if (empty($config['options'])) {
                    $errors[] = 'Este tipo de pregunta requiere opciones';
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Obtener opciones para tipo de pregunta
     * 
     * @param int $typeId ID del tipo de pregunta
     * @return array
     */
    private function getQuestionTypeOptions($typeId)
    {
        $options = [
            'supports_options' => false,
            'supports_validation' => false,
            'supports_conditions' => true,
            'default_options' => [],
            'validation_rules' => []
        ];
        
        switch ($typeId) {
            case 9:  // Multiple Choice
            case 10: // Checkbox
            case 11: // Dropdown
            case 12: // Radio
                $options['supports_options'] = true;
                $options['default_options'] = [
                    ['value' => 'option1', 'label' => 'Opción 1'],
                    ['value' => 'option2', 'label' => 'Opción 2']
                ];
                break;
                
            case 13: // Likert 5
                $options['supports_options'] = true;
                $options['default_options'] = [
                    ['value' => 1, 'label' => 'Muy en desacuerdo'],
                    ['value' => 2, 'label' => 'En desacuerdo'],
                    ['value' => 3, 'label' => 'Neutral'],
                    ['value' => 4, 'label' => 'De acuerdo'],
                    ['value' => 5, 'label' => 'Muy de acuerdo']
                ];
                break;
                
            case 3: // Number
                $options['supports_validation'] = true;
                $options['validation_rules'] = ['min_value', 'max_value'];
                break;
                
            case 1: // Text
            case 2: // Textarea
                $options['supports_validation'] = true;
                $options['validation_rules'] = ['min_length', 'max_length'];
                break;
        }
        
        return $options;
    }
}
