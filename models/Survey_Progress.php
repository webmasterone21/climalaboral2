<?php
/**
 * Modelo SurveyProgress - Gestión del progreso de encuestas
 * models/SurveyProgress.php
 * 
 * Maneja el sistema de guardado automático y recuperación del progreso:
 * - Guardado automático de respuestas parciales
 * - Recuperación de sesiones interrumpidas
 * - Gestión de estado de participación
 * - Limpieza de datos temporales
 */

class SurveyProgress {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Guardar progreso de encuesta
     */
    public function saveProgress($data) {
        try {
            $sql = "INSERT INTO survey_progress (participant_id, current_question_id, answers_data, saved_at) 
                    VALUES (:participant_id, :current_question_id, :answers_data, NOW())
                    ON DUPLICATE KEY UPDATE 
                    current_question_id = VALUES(current_question_id),
                    answers_data = VALUES(answers_data),
                    saved_at = NOW()";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':participant_id' => $data['participant_id'],
                ':current_question_id' => $data['current_question_id'] ?? null,
                ':answers_data' => $data['answers_data']
            ]);
            
        } catch (PDOException $e) {
            error_log('Error saving progress: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener progreso por participante
     */
    public function getByParticipant($participant_id) {
        try {
            $sql = "SELECT sp.*, 
                           p.survey_id,
                           p.status as participant_status,
                           p.progress_percentage
                    FROM survey_progress sp
                    LEFT JOIN participants p ON sp.participant_id = p.id
                    WHERE sp.participant_id = :participant_id
                    ORDER BY sp.saved_at DESC
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':participant_id' => $participant_id]);
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log('Error getting progress by participant: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener progreso por token de participante
     */
    public function getByToken($token) {
        try {
            $sql = "SELECT sp.*, 
                           p.survey_id,
                           p.status as participant_status,
                           p.progress_percentage,
                           p.session_token
                    FROM survey_progress sp
                    LEFT JOIN participants p ON sp.participant_id = p.id
                    WHERE p.session_token = :token
                    ORDER BY sp.saved_at DESC
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log('Error getting progress by token: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar progreso existente
     */
    public function updateProgress($participant_id, $data) {
        try {
            $fields = [];
            $params = [':participant_id' => $participant_id];
            
            if (isset($data['current_question_id'])) {
                $fields[] = 'current_question_id = :current_question_id';
                $params[':current_question_id'] = $data['current_question_id'];
            }
            
            if (isset($data['answers_data'])) {
                $fields[] = 'answers_data = :answers_data';
                $params[':answers_data'] = $data['answers_data'];
            }
            
            if (isset($data['current_section'])) {
                $fields[] = 'current_section = :current_section';
                $params[':current_section'] = $data['current_section'];
            }
            
            if (isset($data['progress_percentage'])) {
                $fields[] = 'progress_percentage = :progress_percentage';
                $params[':progress_percentage'] = $data['progress_percentage'];
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $fields[] = 'saved_at = NOW()';
            
            $sql = "UPDATE survey_progress SET " . implode(', ', $fields) . " WHERE participant_id = :participant_id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log('Error updating progress: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar progreso de participante
     */
    public function deleteByParticipant($participant_id) {
        try {
            $sql = "DELETE FROM survey_progress WHERE participant_id = :participant_id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([':participant_id' => $participant_id]);
            
        } catch (PDOException $e) {
            error_log('Error deleting progress: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de progreso por encuesta
     */
    public function getSurveyProgressStats($survey_id) {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT p.id) as total_participants,
                        COUNT(DISTINCT sp.participant_id) as participants_with_progress,
                        AVG(p.progress_percentage) as average_progress,
                        COUNT(CASE WHEN p.status = 'completed' THEN 1 END) as completed_count,
                        COUNT(CASE WHEN p.status = 'in_progress' THEN 1 END) as in_progress_count,
                        COUNT(CASE WHEN p.status = 'abandoned' THEN 1 END) as abandoned_count
                    FROM participants p
                    LEFT JOIN survey_progress sp ON p.id = sp.participant_id
                    WHERE p.survey_id = :survey_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':survey_id' => $survey_id]);
            
            $stats = $stmt->fetch();
            
            if ($stats) {
                $stats['completion_rate'] = $stats['total_participants'] > 0 
                    ? round(($stats['completed_count'] / $stats['total_participants']) * 100, 1)
                    : 0;
                    
                $stats['abandonment_rate'] = $stats['total_participants'] > 0
                    ? round(($stats['abandoned_count'] / $stats['total_participants']) * 100, 1)
                    : 0;
                    
                $stats['average_progress'] = round($stats['average_progress'], 1);
            }
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log('Error getting survey progress stats: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener participantes con progreso guardado
     */
    public function getParticipantsWithProgress($survey_id, $status = null) {
        try {
            $sql = "SELECT p.*, 
                           sp.current_question_id,
                           sp.saved_at,
                           sp.answers_data,
                           q.question_text as current_question_text,
                           TIMESTAMPDIFF(HOUR, sp.saved_at, NOW()) as hours_since_save
                    FROM participants p
                    INNER JOIN survey_progress sp ON p.id = sp.participant_id
                    LEFT JOIN questions q ON sp.current_question_id = q.id
                    WHERE p.survey_id = :survey_id";
            
            $params = [':survey_id' => $survey_id];
            
            if ($status) {
                $sql .= " AND p.status = :status";
                $params[':status'] = $status;
            }
            
            $sql .= " ORDER BY sp.saved_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log('Error getting participants with progress: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Restaurar respuestas desde progreso guardado
     */
    public function restoreAnswers($participant_id) {
        try {
            $progress = $this->getByParticipant($participant_id);
            
            if (!$progress || !$progress['answers_data']) {
                return [];
            }
            
            $answers = json_decode($progress['answers_data'], true);
            
            if (!is_array($answers)) {
                return [];
            }
            
            return $answers;
            
        } catch (Exception $e) {
            error_log('Error restoring answers: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Convertir progreso a respuestas permanentes
     */
    public function convertToResponses($participant_id) {
        try {
            $this->db->beginTransaction();
            
            $progress = $this->getByParticipant($participant_id);
            
            if (!$progress || !$progress['answers_data']) {
                $this->db->rollBack();
                return false;
            }
            
            $answers = json_decode($progress['answers_data'], true);
            
            if (!is_array($answers)) {
                $this->db->rollBack();
                return false;
            }
            
            $responseModel = new Response($this->db);
            
            foreach ($answers as $questionId => $answerData) {
                if (is_array($answerData)) {
                    // Múltiples respuestas
                    foreach ($answerData as $value) {
                        $responseData = [
                            'participant_id' => $participant_id,
                            'question_id' => $questionId,
                            'answer_value' => is_numeric($value) ? $value : null,
                            'answer_text' => !is_numeric($value) ? $value : null
                        ];
                        
                        $responseModel->create($responseData);
                    }
                } else {
                    // Respuesta única
                    $responseData = [
                        'participant_id' => $participant_id,
                        'question_id' => $questionId,
                        'answer_value' => is_numeric($answerData) ? $answerData : null,
                        'answer_text' => !is_numeric($answerData) ? $answerData : null
                    ];
                    
                    $responseModel->create($responseData);
                }
            }
            
            // Eliminar progreso temporal
            $this->deleteByParticipant($participant_id);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error converting progress to responses: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpiar progreso antiguo (mayor a X días)
     */
    public function cleanOldProgress($days = 30) {
        try {
            $sql = "DELETE sp FROM survey_progress sp
                    LEFT JOIN participants p ON sp.participant_id = p.id
                    WHERE sp.saved_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                    AND (p.status = 'abandoned' OR p.status IS NULL)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':days' => $days]);
            
            $deletedRows = $stmt->rowCount();
            
            if ($deletedRows > 0) {
                error_log("Cleaned {$deletedRows} old progress records");
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log('Error cleaning old progress: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar participantes abandonados
     */
    public function markAbandoned($hours = 24) {
        try {
            $sql = "UPDATE participants p
                    INNER JOIN survey_progress sp ON p.id = sp.participant_id
                    SET p.status = 'abandoned'
                    WHERE p.status = 'in_progress'
                    AND sp.saved_at < DATE_SUB(NOW(), INTERVAL :hours HOUR)
                    AND p.progress_percentage < 100";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':hours' => $hours]);
            
            $updatedRows = $stmt->rowCount();
            
            if ($updatedRows > 0) {
                error_log("Marked {$updatedRows} participants as abandoned");
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log('Error marking abandoned participants: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener tiempo promedio de completado
     */
    public function getAverageCompletionTime($survey_id) {
        try {
            $sql = "SELECT 
                        AVG(TIMESTAMPDIFF(MINUTE, p.started_at, p.completed_at)) as avg_minutes
                    FROM participants p
                    WHERE p.survey_id = :survey_id 
                    AND p.status = 'completed'
                    AND p.started_at IS NOT NULL 
                    AND p.completed_at IS NOT NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':survey_id' => $survey_id]);
            
            $result = $stmt->fetch();
            
            return $result ? round($result['avg_minutes'], 1) : 0;
            
        } catch (PDOException $e) {
            error_log('Error getting average completion time: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener puntos de abandono más comunes
     */
    public function getAbandonmentPoints($survey_id) {
        try {
            $sql = "SELECT 
                        sp.current_question_id,
                        q.question_text,
                        q.order_position,
                        COUNT(*) as abandonment_count,
                        qc.name as category_name
                    FROM survey_progress sp
                    LEFT JOIN participants p ON sp.participant_id = p.id
                    LEFT JOIN questions q ON sp.current_question_id = q.id
                    LEFT JOIN question_categories qc ON q.category_id = qc.id
                    WHERE p.survey_id = :survey_id 
                    AND p.status = 'abandoned'
                    GROUP BY sp.current_question_id, q.question_text, q.order_position, qc.name
                    ORDER BY abandonment_count DESC, q.order_position ASC
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':survey_id' => $survey_id]);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log('Error getting abandonment points: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar datos de progreso
     */
    public function validateProgressData($data) {
        $errors = [];
        
        if (empty($data['participant_id'])) {
            $errors['participant_id'] = 'ID de participante requerido';
        }
        
        if (isset($data['answers_data'])) {
            if (!is_string($data['answers_data']) && !is_array($data['answers_data'])) {
                $errors['answers_data'] = 'Formato de datos de respuestas inválido';
            }
            
            if (is_string($data['answers_data'])) {
                $decoded = json_decode($data['answers_data'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errors['answers_data'] = 'JSON de respuestas inválido';
                }
            }
        }
        
        if (isset($data['progress_percentage'])) {
            if (!is_numeric($data['progress_percentage']) || 
                $data['progress_percentage'] < 0 || 
                $data['progress_percentage'] > 100) {
                $errors['progress_percentage'] = 'Porcentaje de progreso debe estar entre 0 y 100';
            }
        }
        
        return $errors;
    }
    
    /**
     * Obtener resumen de sesión activa
     */
    public function getActiveSession($participant_id) {
        try {
            $sql = "SELECT 
                        sp.*,
                        p.started_at,
                        p.progress_percentage,
                        TIMESTAMPDIFF(MINUTE, p.started_at, NOW()) as session_duration,
                        TIMESTAMPDIFF(MINUTE, sp.saved_at, NOW()) as time_since_save
                    FROM survey_progress sp
                    LEFT JOIN participants p ON sp.participant_id = p.id
                    WHERE sp.participant_id = :participant_id
                    AND p.status = 'in_progress'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':participant_id' => $participant_id]);
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log('Error getting active session: ' . $e->getMessage());
            return false;
        }
    }
}