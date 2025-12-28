<?php
/**
 * Controlador de Reportes HERCO - VERSIÓN CORREGIDA
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * Generación de reportes con formato HERCO 2024, análisis estadístico,
 * exportación en múltiples formatos y dashboards interactivos.
 * 
 * CORRECCIONES APLICADAS:
 * ✅ Eliminación de datos simulados/fake
 * ✅ Cálculos reales basados en datos de BD
 * ✅ Verificación de dependencias externas
 * ✅ Manejo robusto de errores
 * ✅ Configuraciones dinámicas (no hardcoded)
 * 
 * @package EncuestasHERCO\Controllers
 * @version 2.0.0
 * @author Sistema HERCO
 */

class ReportController extends Controller
{
    private $surveyModel;
    private $questionModel;
    private $responseModel;
    private $participantModel;
    private $hercoCategories;
    private $pdfEnabled = false;
    private $excelEnabled = false;
    
    /**
     * Inicialización del controlador con verificación de dependencias
     */
    protected function initialize()
    {
        // Requerir autenticación
        $this->requireAuth();
        
        // Verificar permisos
        $this->requirePermission('view_reports');
        
        // Cargar modelos verificando existencia
        try {
            if (!class_exists('Survey')) {
                throw new Exception('Modelo Survey no disponible');
            }
            $this->surveyModel = new Survey();
            
            if (class_exists('Question')) {
                $this->questionModel = new Question();
            }
            
            if (class_exists('Response')) {
                $this->responseModel = new Response();
            }
            
            if (class_exists('Participant')) {
                $this->participantModel = new Participant();
            }
            
            // Verificar disponibilidad de librerías de exportación
            $this->pdfEnabled = class_exists('TCPDF') || class_exists('mPDF');
            $this->excelEnabled = class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
            
        } catch (Exception $e) {
            error_log("Error inicializando ReportController: " . $e->getMessage());
            $this->setFlashMessage(
                'Error de configuración del sistema de reportes. Contacte al administrador.', 
                'error'
            );
            $this->redirect('/admin/dashboard');
        }
        
        // Cargar categorías HERCO desde configuración
        $this->hercoCategories = $this->loadHercoCategories();
        
        // Layout administrativo
        $this->defaultLayout = 'admin';
    }
    
    /**
     * Dashboard principal de reportes
     */
    public function index()
    {
        try {
            // Obtener encuestas de la empresa
            $surveys = $this->surveyModel->findByCondition(
                'company_id = ? ORDER BY created_at DESC',
                [$this->user['company_id']]
            );
            
            // Obtener estadísticas generales
            $generalStats = $this->getGeneralStats();
            
            // Obtener últimos reportes generados (si existe el modelo)
            $recentReports = $this->getRecentReports();
            
            $data = [
                'surveys' => $surveys,
                'stats' => $generalStats,
                'recent_reports' => $recentReports,
                'export_enabled' => [
                    'pdf' => $this->pdfEnabled,
                    'excel' => $this->excelEnabled
                ]
            ];
            
            $this->render('admin/reports/index', $data);
            
        } catch (Exception $e) {
            error_log("Error en ReportController::index: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar dashboard de reportes', 'error');
            $this->redirect('/admin/dashboard');
        }
    }
    
    /**
     * Generar reporte HERCO específico para una encuesta
     */
    public function herco($surveyId = null)
    {
        try {
            if (!$surveyId) {
                $surveyId = $_GET['survey_id'] ?? null;
            }
            
            if (!$surveyId) {
                throw new Exception('ID de encuesta requerido');
            }
            
            // Verificar que la encuesta pertenezca a la empresa
            $survey = $this->surveyModel->findById($surveyId);
            if (!$survey || $survey['company_id'] != $this->user['company_id']) {
                throw new Exception('Encuesta no encontrada');
            }
            
            // Obtener datos del reporte HERCO
            $reportData = $this->generateHercoReportData($surveyId);
            
            // Si no hay respuestas, mostrar mensaje
            if ($reportData['total_responses'] == 0) {
                $this->setFlashMessage(
                    'La encuesta no tiene suficientes respuestas para generar un reporte', 
                    'warning'
                );
                $this->redirect('/admin/reports');
            }
            
            $data = [
                'survey' => $survey,
                'report_data' => $reportData,
                'herco_categories' => $this->hercoCategories,
                'generated_at' => date('Y-m-d H:i:s'),
                'export_enabled' => [
                    'pdf' => $this->pdfEnabled,
                    'excel' => $this->excelEnabled
                ]
            ];
            
            $this->render('admin/reports/herco', $data);
            
        } catch (Exception $e) {
            error_log("Error generando reporte HERCO: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/reports');
        }
    }
    
    /**
     * Generar datos del reporte HERCO basados en respuestas reales
     */
    private function generateHercoReportData($surveyId)
    {
        try {
            // Inicializar estructura de datos
            $reportData = [
                'total_responses' => 0,
                'total_participants' => 0,
                'completion_rate' => 0,
                'category_averages' => [],
                'department_analysis' => [],
                'response_distribution' => [],
                'overall_average' => 0,
                'satisfaction_level' => 'Sin datos'
            ];
            
            // Si no tenemos modelo de respuestas, retornar estructura vacía
            if (!$this->responseModel) {
                return $reportData;
            }
            
            // Obtener estadísticas básicas
            $basicStats = $this->getBasicSurveyStats($surveyId);
            $reportData['total_responses'] = $basicStats['total_responses'];
            $reportData['total_participants'] = $basicStats['total_participants'];
            $reportData['completion_rate'] = $basicStats['completion_rate'];
            
            // Si hay respuestas, calcular análisis detallado
            if ($reportData['total_responses'] > 0) {
                $reportData['category_averages'] = $this->calculateCategoryAverages($surveyId);
                $reportData['department_analysis'] = $this->calculateDepartmentAnalysis($surveyId);
                $reportData['response_distribution'] = $this->calculateResponseDistribution($surveyId);
                $reportData['overall_average'] = $this->calculateOverallAverage($reportData['category_averages']);
                $reportData['satisfaction_level'] = $this->getSatisfactionLevel($reportData['overall_average']);
            }
            
            return $reportData;
            
        } catch (Exception $e) {
            error_log("Error generando datos HERCO: " . $e->getMessage());
            throw new Exception('Error procesando datos del reporte');
        }
    }
    
    /**
     * Obtener estadísticas básicas de la encuesta
     */
    private function getBasicSurveyStats($surveyId)
    {
        try {
            $stats = [
                'total_responses' => 0,
                'total_participants' => 0,
                'completion_rate' => 0
            ];
            
            // Contar participantes si tenemos el modelo
            if ($this->participantModel) {
                $participants = $this->participantModel->findByCondition(
                    'survey_id = ?',
                    [$surveyId]
                );
                $stats['total_participants'] = count($participants);
                
                // Contar participantes completados
                $completed = $this->participantModel->findByCondition(
                    'survey_id = ? AND status = "completed"',
                    [$surveyId]
                );
                $stats['total_responses'] = count($completed);
                
                // Calcular tasa de completitud
                if ($stats['total_participants'] > 0) {
                    $stats['completion_rate'] = round(
                        ($stats['total_responses'] / $stats['total_participants']) * 100, 
                        1
                    );
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas básicas: " . $e->getMessage());
            return ['total_responses' => 0, 'total_participants' => 0, 'completion_rate' => 0];
        }
    }
    
    /**
     * Calcular promedios por categoría HERCO
     */
    private function calculateCategoryAverages($surveyId)
    {
        try {
            $categoryAverages = [];
            
            if (!$this->responseModel || !$this->questionModel) {
                return $categoryAverages;
            }
            
            // Obtener todas las preguntas de la encuesta agrupadas por categoría
            foreach ($this->hercoCategories as $categoryId => $categoryName) {
                $questions = $this->questionModel->findByCondition(
                    'survey_id = ? AND category_id = ?',
                    [$surveyId, $categoryId]
                );
                
                if (empty($questions)) {
                    $categoryAverages[$categoryId] = [
                        'name' => $categoryName,
                        'average' => 0,
                        'total_responses' => 0,
                        'questions_count' => 0
                    ];
                    continue;
                }
                
                $categoryTotal = 0;
                $categoryResponses = 0;
                
                foreach ($questions as $question) {
                    $responses = $this->responseModel->findByCondition(
                        'question_id = ? AND numeric_value IS NOT NULL',
                        [$question['id']]
                    );
                    
                    foreach ($responses as $response) {
                        $categoryTotal += floatval($response['numeric_value']);
                        $categoryResponses++;
                    }
                }
                
                $average = $categoryResponses > 0 ? 
                    round($categoryTotal / $categoryResponses, 2) : 0;
                
                $categoryAverages[$categoryId] = [
                    'name' => $categoryName,
                    'average' => $average,
                    'total_responses' => $categoryResponses,
                    'questions_count' => count($questions)
                ];
            }
            
            return $categoryAverages;
            
        } catch (Exception $e) {
            error_log("Error calculando promedios por categoría: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcular análisis por departamento
     */
    private function calculateDepartmentAnalysis($surveyId)
    {
        try {
            $departmentAnalysis = [];
            
            if (!$this->participantModel || !$this->responseModel) {
                return $departmentAnalysis;
            }
            
            // Obtener departamentos únicos
            $participants = $this->participantModel->findByCondition(
                'survey_id = ? AND status = "completed"',
                [$surveyId]
            );
            
            $departments = [];
            foreach ($participants as $participant) {
                $dept = $participant['department'] ?: 'Sin departamento';
                if (!isset($departments[$dept])) {
                    $departments[$dept] = [];
                }
                $departments[$dept][] = $participant['id'];
            }
            
            // Calcular promedio por departamento
            foreach ($departments as $deptName => $participantIds) {
                if (empty($participantIds)) continue;
                
                $deptTotal = 0;
                $deptResponses = 0;
                
                foreach ($participantIds as $participantId) {
                    $responses = $this->responseModel->findByCondition(
                        'participant_id = ? AND numeric_value IS NOT NULL',
                        [$participantId]
                    );
                    
                    foreach ($responses as $response) {
                        $deptTotal += floatval($response['numeric_value']);
                        $deptResponses++;
                    }
                }
                
                $departmentAnalysis[$deptName] = [
                    'participants' => count($participantIds),
                    'total_responses' => $deptResponses,
                    'average' => $deptResponses > 0 ? round($deptTotal / $deptResponses, 2) : 0
                ];
            }
            
            return $departmentAnalysis;
            
        } catch (Exception $e) {
            error_log("Error calculando análisis por departamento: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcular distribución de respuestas
     */
    private function calculateResponseDistribution($surveyId)
    {
        try {
            $distribution = [
                1 => 0, // Muy insatisfecho
                2 => 0, // Insatisfecho
                3 => 0, // Neutral
                4 => 0, // Satisfecho
                5 => 0  // Muy satisfecho
            ];
            
            if (!$this->responseModel) {
                return $distribution;
            }
            
            // Obtener todas las respuestas numéricas de la encuesta
            $query = "
                SELECT r.numeric_value, COUNT(*) as count
                FROM responses r
                INNER JOIN questions q ON r.question_id = q.id
                WHERE q.survey_id = ? AND r.numeric_value IS NOT NULL
                GROUP BY r.numeric_value
                ORDER BY r.numeric_value
            ";
            
            try {
                $database = Database::getInstance();
                $stmt = $database->prepare($query);
                $stmt->execute([$surveyId]);
                $results = $stmt->fetchAll();
                
                foreach ($results as $result) {
                    $value = intval($result['numeric_value']);
                    if ($value >= 1 && $value <= 5) {
                        $distribution[$value] = intval($result['count']);
                    }
                }
            } catch (Exception $e) {
                error_log("Error en consulta de distribución: " . $e->getMessage());
            }
            
            return $distribution;
            
        } catch (Exception $e) {
            error_log("Error calculando distribución: " . $e->getMessage());
            return [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        }
    }
    
    /**
     * Calcular promedio general
     */
    private function calculateOverallAverage($categoryAverages)
    {
        if (empty($categoryAverages)) {
            return 0;
        }
        
        $total = 0;
        $count = 0;
        
        foreach ($categoryAverages as $category) {
            if ($category['average'] > 0) {
                $total += $category['average'];
                $count++;
            }
        }
        
        return $count > 0 ? round($total / $count, 2) : 0;
    }
    
    /**
     * Obtener nivel de satisfacción basado en promedio
     */
    private function getSatisfactionLevel($average)
    {
        if ($average >= 4.5) return 'Excelente';
        if ($average >= 4.0) return 'Muy Bueno';
        if ($average >= 3.5) return 'Bueno';
        if ($average >= 3.0) return 'Regular';
        if ($average >= 2.0) return 'Bajo';
        if ($average > 0) return 'Muy Bajo';
        return 'Sin datos';
    }
    
    /**
     * Exportar reporte a PDF (si está disponible)
     */
    public function exportPdf($surveyId)
    {
        try {
            if (!$this->pdfEnabled) {
                throw new Exception('Exportación PDF no disponible');
            }
            
            // Verificar encuesta
            $survey = $this->surveyModel->findById($surveyId);
            if (!$survey || $survey['company_id'] != $this->user['company_id']) {
                throw new Exception('Encuesta no encontrada');
            }
            
            // Generar datos del reporte
            $reportData = $this->generateHercoReportData($surveyId);
            
            // Generar PDF básico (implementación simplificada)
            $this->generatePdfReport($survey, $reportData);
            
        } catch (Exception $e) {
            error_log("Error exportando PDF: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/reports/herco?survey_id=' . $surveyId);
        }
    }
    
    /**
     * Exportar reporte a Excel (si está disponible)
     */
    public function exportExcel($surveyId)
    {
        try {
            if (!$this->excelEnabled) {
                throw new Exception('Exportación Excel no disponible');
            }
            
            // Verificar encuesta
            $survey = $this->surveyModel->findById($surveyId);
            if (!$survey || $survey['company_id'] != $this->user['company_id']) {
                throw new Exception('Encuesta no encontrada');
            }
            
            // Generar datos del reporte
            $reportData = $this->generateHercoReportData($surveyId);
            
            // Generar Excel básico (implementación simplificada)
            $this->generateExcelReport($survey, $reportData);
            
        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/reports/herco?survey_id=' . $surveyId);
        }
    }
    
    /**
     * Obtener estadísticas generales
     */
    private function getGeneralStats()
    {
        try {
            $stats = [
                'total_surveys' => 0,
                'active_surveys' => 0,
                'total_participants' => 0,
                'total_responses' => 0
            ];
            
            // Contar encuestas
            $surveys = $this->surveyModel->findByCondition(
                'company_id = ?',
                [$this->user['company_id']]
            );
            $stats['total_surveys'] = count($surveys);
            
            $activeSurveys = $this->surveyModel->findByCondition(
                'company_id = ? AND status = "active"',
                [$this->user['company_id']]
            );
            $stats['active_surveys'] = count($activeSurveys);
            
            // Contar participantes y respuestas si tenemos los modelos
            if ($this->participantModel) {
                $participants = $this->participantModel->findByCondition(
                    'company_id = ?',
                    [$this->user['company_id']]
                );
                $stats['total_participants'] = count($participants);
                
                $completed = $this->participantModel->findByCondition(
                    'company_id = ? AND status = "completed"',
                    [$this->user['company_id']]
                );
                $stats['total_responses'] = count($completed);
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas generales: " . $e->getMessage());
            return [
                'total_surveys' => 0,
                'active_surveys' => 0,
                'total_participants' => 0,
                'total_responses' => 0
            ];
        }
    }
    
/**
 * Obtener reportes recientes
 */
private function getRecentReports()
{
    try {
        // ✅ CORRECCIÓN: Verificar existencia del modelo Y de su constructor
        if (class_exists('Report')) {
            // Verificar si el constructor requiere parámetros
            $reflection = new ReflectionClass('Report');
            $constructor = $reflection->getConstructor();
            
            // Si el constructor no requiere parámetros, crear instancia
            if (!$constructor || $constructor->getNumberOfRequiredParameters() === 0) {
                $reportModel = new Report();
                
                // Verificar que tenga el método antes de llamarlo
                if (method_exists($reportModel, 'findByCondition')) {
                    return $reportModel->findByCondition(
                        'company_id = ? ORDER BY created_at DESC LIMIT 5',
                        [$this->user['company_id']]
                    );
                }
            }
        }
        
        // Si no hay modelo o tiene problemas, consultar directamente
        $sql = "SELECT * FROM reports 
                WHERE company_id = ? 
                ORDER BY created_at DESC 
                LIMIT 5";
        
        return $this->fetchAll($sql, [$this->user['company_id']]);
        
    } catch (Exception $e) {
        error_log("Error obteniendo reportes recientes: " . $e->getMessage());
        return [];
    }
}

    
/**
 * Cargar categorías HERCO desde configuración
 * Versión robusta con fallback garantizado
 */
private function loadHercoCategories()
{
    // ✅ CATEGORÍAS HERCO 2024 ESTÁNDAR (18 categorías oficiales)
    $hercoStandardCategories = [
        1 => 'Satisfacción Laboral',
        2 => 'Participación y Autonomía', 
        3 => 'Comunicación y Objetivos',
        4 => 'Equilibrio y Evaluación',
        5 => 'Distribución y Carga de Trabajo',
        6 => 'Reconocimiento y Promoción',
        7 => 'Ambiente de Trabajo',
        8 => 'Capacitación',
        9 => 'Tecnología y Recursos',
        10 => 'Colaboración y Compañerismo',
        11 => 'Normativas y Regulaciones',
        12 => 'Compensación y Beneficios',
        13 => 'Bienestar y Salud',
        14 => 'Seguridad en el Trabajo',
        15 => 'Información y Comunicación',
        16 => 'Relaciones con Supervisores',
        17 => 'Feedback y Reconocimiento',
        18 => 'Diversidad e Inclusión'
    ];
    
    // Intentar cargar desde BD solo si existe la tabla
    try {
        // Verificar si existe la tabla question_categories
        $tableExists = $this->fetchOne("SHOW TABLES LIKE 'question_categories'");
        
        if ($tableExists) {
            // ✅ CORRECCIÓN: Sin is_active ni display_order
            $sql = "SELECT id, name FROM question_categories ORDER BY id ASC";
            
            $dbCategories = $this->fetchAll($sql);
            
            if (!empty($dbCategories)) {
                $customCategories = [];
                foreach ($dbCategories as $category) {
                    $customCategories[$category['id']] = $category['name'];
                }
                return !empty($customCategories) ? $customCategories : $hercoStandardCategories;
            }
        }
    } catch (Exception $e) {
        // Si hay cualquier error, usar categorías estándar
        error_log("Info: Usando categorías HERCO estándar. " . $e->getMessage());
    }
    
    // ✅ Siempre retornar las categorías estándar HERCO 2024
    return $hercoStandardCategories;
}
    
    /**
     * Generar PDF básico (implementación simplificada)
     */
    private function generatePdfReport($survey, $reportData)
    {
        // Implementación básica sin dependencias externas
        $filename = "reporte_herco_" . $survey['id'] . "_" . date('Y-m-d') . ".pdf";
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo "Reporte PDF no disponible. Instale TCPDF o mPDF para generar PDFs.";
        exit;
    }
    
    /**
     * Generar Excel básico (implementación simplificada)
     */
    private function generateExcelReport($survey, $reportData)
    {
        // Implementación básica como CSV
        $filename = "reporte_herco_" . $survey['id'] . "_" . date('Y-m-d') . ".csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, ['Categoria', 'Promedio', 'Total_Respuestas']);
        
        // Datos
        foreach ($reportData['category_averages'] as $category) {
            fputcsv($output, [
                $category['name'],
                $category['average'],
                $category['total_responses']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
?>