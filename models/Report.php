<?php
class Report {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Obtener información básica de la encuesta
     */
    public function getSurveyInfo($survey_id) {
        $sql = "SELECT s.*, c.name as company_name, u.username as consultant_name,
                       COUNT(DISTINCT p.id) as total_participants,
                       COUNT(CASE WHEN p.status = 'completed' THEN 1 END) as completed_responses,
                       ROUND(AVG(CASE WHEN p.status = 'completed' THEN p.progress_percentage END), 2) as avg_completion
                FROM surveys s 
                LEFT JOIN companies c ON s.company_id = c.id 
                LEFT JOIN users u ON s.user_id = u.id 
                LEFT JOIN participants p ON s.id = p.survey_id
                WHERE s.id = :survey_id
                GROUP BY s.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Obtener estadísticas básicas
     */
    public function getBasicStats($survey_id) {
        // Total de preguntas
        $sql = "SELECT COUNT(*) as total_questions FROM questions WHERE survey_id = :survey_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        $totalQuestions = $stmt->fetchColumn();
        
        // Participantes por estado
        $sql = "SELECT status, COUNT(*) as count 
                FROM participants 
                WHERE survey_id = :survey_id 
                GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        $participantsByStatus = $stmt->fetchAll();
        
        // Promedio general de respuestas
        $sql = "SELECT AVG(r.answer_value) as avg_score
                FROM responses r
                JOIN participants p ON r.participant_id = p.id
                WHERE p.survey_id = :survey_id AND r.answer_value IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        $avgScore = $stmt->fetchColumn();
        
        // Tasa de finalización
        $sql = "SELECT 
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / 
                    NULLIF(COUNT(*), 0) as completion_rate
                FROM participants 
                WHERE survey_id = :survey_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        $completionRate = $stmt->fetchColumn();
        
        return [
            'total_questions' => $totalQuestions,
            'participants_by_status' => $participantsByStatus,
            'avg_score' => round($avgScore ?? 0, 2),
            'completion_rate' => round($completionRate ?? 0, 2)
        ];
    }
    
    /**
     * Obtener estadísticas por categoría
     */
    public function getCategoryStats($survey_id) {
        $sql = "SELECT 
                    qc.id,
                    qc.name as category_name,
                    qc.order_position,
                    COUNT(DISTINCT q.id) as total_questions,
                    COUNT(DISTINCT r.id) as total_responses,
                    AVG(r.answer_value) as avg_score,
                    MIN(r.answer_value) as min_score,
                    MAX(r.answer_value) as max_score,
                    STDDEV(r.answer_value) as std_deviation
                FROM question_categories qc
                LEFT JOIN questions q ON qc.id = q.category_id AND q.survey_id = :survey_id
                LEFT JOIN responses r ON q.id = r.question_id AND r.answer_value IS NOT NULL
                LEFT JOIN participants p ON r.participant_id = p.id AND p.status = 'completed'
                WHERE qc.id IN (SELECT DISTINCT category_id FROM questions WHERE survey_id = :survey_id)
                GROUP BY qc.id, qc.name, qc.order_position
                ORDER BY qc.order_position";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener análisis detallado por pregunta
     */
    public function getDetailedQuestionAnalysis($survey_id) {
        $sql = "SELECT 
                    q.id,
                    q.question_text,
                    qc.name as category_name,
                    qc.order_position as category_order,
                    q.order_position,
                    qt.name as question_type,
                    COUNT(DISTINCT r.id) as total_responses,
                    AVG(r.answer_value) as avg_score,
                    MIN(r.answer_value) as min_score,
                    MAX(r.answer_value) as max_score,
                    STDDEV(r.answer_value) as std_deviation
                FROM questions q
                LEFT JOIN question_categories qc ON q.category_id = qc.id
                LEFT JOIN question_types qt ON q.question_type_id = qt.id
                LEFT JOIN responses r ON q.id = r.question_id AND r.answer_value IS NOT NULL
                LEFT JOIN participants p ON r.participant_id = p.id AND p.status = 'completed'
                WHERE q.survey_id = :survey_id
                GROUP BY q.id, q.question_text, qc.name, qc.order_position, q.order_position, qt.name
                ORDER BY qc.order_position, q.order_position";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        
        $questions = $stmt->fetchAll();
        
        // Obtener distribución de respuestas para cada pregunta
        foreach ($questions as &$question) {
            $question['response_distribution'] = $this->getResponseDistribution($question['id']);
            $question['response_percentage'] = $this->calculateResponsePercentages($question['response_distribution'], $question['total_responses']);
        }
        
        return $questions;
    }
    
    /**
     * Obtener distribución de respuestas para una pregunta
     */
    public function getResponseDistribution($question_id) {
        $sql = "SELECT 
                    r.answer_value,
                    qo.option_text,
                    COUNT(*) as frequency
                FROM responses r
                LEFT JOIN question_options qo ON r.answer_value = qo.option_value AND qo.question_id = :question_id
                JOIN participants p ON r.participant_id = p.id AND p.status = 'completed'
                WHERE r.question_id = :question_id AND r.answer_value IS NOT NULL
                GROUP BY r.answer_value, qo.option_text
                ORDER BY r.answer_value";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':question_id' => $question_id]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Calcular porcentajes de respuesta
     */
    private function calculateResponsePercentages($distribution, $total) {
        if ($total == 0) return [];
        
        $percentages = [];
        foreach ($distribution as $item) {
            $percentages[] = [
                'answer_value' => $item['answer_value'],
                'option_text' => $item['option_text'],
                'frequency' => $item['frequency'],
                'percentage' => round(($item['frequency'] / $total) * 100, 2)
            ];
        }
        
        return $percentages;
    }
    
    /**
     * Obtener estadísticas por departamento
     */
    public function getDepartmentStats($survey_id) {
        $sql = "SELECT 
                    d.id,
                    d.name as department_name,
                    COUNT(DISTINCT p.id) as total_participants,
                    COUNT(CASE WHEN p.status = 'completed' THEN 1 END) as completed_participants,
                    AVG(CASE WHEN p.status = 'completed' THEN r.answer_value END) as avg_score,
                    COUNT(CASE WHEN p.status = 'completed' THEN 1 END) * 100.0 / 
                    NULLIF(COUNT(DISTINCT p.id), 0) as completion_rate
                FROM departments d
                LEFT JOIN participants p ON d.id = p.department_id AND p.survey_id = :survey_id
                LEFT JOIN responses r ON p.id = r.participant_id AND r.answer_value IS NOT NULL
                GROUP BY d.id, d.name
                HAVING total_participants > 0
                ORDER BY d.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener comparación entre departamentos por categoría
     */
    public function getDepartmentCategoryComparison($survey_id) {
        $sql = "SELECT 
                    d.name as department_name,
                    qc.name as category_name,
                    qc.order_position,
                    AVG(r.answer_value) as avg_score,
                    COUNT(r.id) as total_responses
                FROM departments d
                JOIN participants p ON d.id = p.department_id AND p.survey_id = :survey_id AND p.status = 'completed'
                JOIN responses r ON p.id = r.participant_id AND r.answer_value IS NOT NULL
                JOIN questions q ON r.question_id = q.id
                JOIN question_categories qc ON q.category_id = qc.id
                GROUP BY d.id, d.name, qc.id, qc.name, qc.order_position
                ORDER BY d.name, qc.order_position";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':survey_id' => $survey_id]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener datos para gráfico de barras por categoría
     */
    public function getCategoryBarChartData($survey_id) {
        $categories = $this->getCategoryStats($survey_id);
        
        $chartData = [
            'labels' => [],
            'datasets' => [[
                'label' => 'Promedio por Categoría',
                'data' => [],
                'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1
            ]]
        ];
        
        foreach ($categories as $category) {
            $chartData['labels'][] = $category['category_name'];
            $chartData['datasets'][0]['data'][] = round($category['avg_score'] ?? 0, 2);
        }
        
        return $chartData;
    }
    
    /**
     * Generar reporte global estilo HERCO
     */
    public function generateGlobalReport($survey_id) {
        $surveyInfo = $this->getSurveyInfo($survey_id);
        $categories = $this->getCategoryStats($survey_id);
        $questions = $this->getDetailedQuestionAnalysis($survey_id);
        $departments = $this->getDepartmentStats($survey_id);
        
        // Agrupar preguntas por categoría
        $questionsByCategory = [];
        foreach ($questions as $question) {
            $categoryName = $question['category_name'];
            if (!isset($questionsByCategory[$categoryName])) {
                $questionsByCategory[$categoryName] = [
                    'info' => array_filter($categories, function($cat) use ($categoryName) {
                        return $cat['category_name'] === $categoryName;
                    })[0] ?? null,
                    'questions' => []
                ];
            }
            $questionsByCategory[$categoryName]['questions'][] = $question;
        }
        
        // Calcular resultado global
        $globalScore = 0;
        $totalQuestions = count($questions);
        
        foreach ($questions as $question) {
            $globalScore += $question['avg_score'] ?? 0;
        }
        
        $globalAverage = $totalQuestions > 0 ? round($globalScore / $totalQuestions, 2) : 0;
        $globalPercentage = round(($globalAverage / 5) * 100, 2); // Asumiendo escala de 1-5
        
        return [
            'survey_info' => $surveyInfo,
            'questions_by_category' => $questionsByCategory,
            'departments' => $departments,
            'global_score' => $globalAverage,
            'global_percentage' => $globalPercentage,
            'total_questions' => $totalQuestions,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Obtener resumen ejecutivo
     */
    public function getExecutiveSummary($survey_id) {
        $globalReport = $this->generateGlobalReport($survey_id);
        $categories = array_values($globalReport['questions_by_category']);
        
        // Encontrar mejores y peores categorías
        $sortedCategories = $categories;
        usort($sortedCategories, function($a, $b) {
            $avgA = $a['info']['avg_score'] ?? 0;
            $avgB = $b['info']['avg_score'] ?? 0;
            return $avgB <=> $avgA;
        });
        
        $strengths = array_slice($sortedCategories, 0, 3);
        $opportunities = array_slice($sortedCategories, -3);
        
        return [
            'global_score' => $globalReport['global_score'],
            'global_percentage' => $globalReport['global_percentage'],
            'total_participants' => $globalReport['survey_info']['completed_responses'],
            'completion_rate' => $globalReport['survey_info']['avg_completion'],
            'strengths' => $strengths,
            'opportunities' => array_reverse($opportunities),
            'participation_by_department' => $globalReport['departments']
        ];
    }
}
?>