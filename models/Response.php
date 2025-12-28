<?php
/**
 * Modelo de Respuestas
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * Modelo Response para gestión completa de respuestas:
 * respuestas de encuestas, validaciones, análisis, progreso
 * 
 * @package EncuestasHERCO\Models
 * @version 2.0.0
 * @author Sistema HERCO
 */

class Response extends Model
{
    /**
     * Tabla de la base de datos
     * @var string
     */
    protected $table = 'survey_responses';
    
    /**
     * Campos que se pueden asignar masivamente
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'participant_id',
        'ip_address',
        'user_agent',
        'started_at',
        'completed_at',
        'last_activity',
        'progress_percentage',
        'status',
        'completion_time_seconds',
        'device_type',
        'browser',
        'session_id',
        'metadata'
    ];
    
    /**
     * Campos que deben ser casteados
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity' => 'datetime',
        'progress_percentage' => 'float',
        'completion_time_seconds' => 'integer',
        'metadata' => 'array'
    ];
    
    /**
     * Estados de respuestas
     */
    const STATUS = [
        'draft' => 'Borrador',
        'in_progress' => 'En Progreso',
        'completed' => 'Completada',
        'abandoned' => 'Abandonada',
        'expired' => 'Expirada',
        'invalid' => 'Inválida'
    ];
    
    /**
     * Tipos de dispositivo
     */
    const DEVICE_TYPES = [
        'desktop' => 'Escritorio',
        'tablet' => 'Tablet',
        'mobile' => 'Móvil',
        'unknown' => 'Desconocido'
    ];
    
    /**
     * Reglas de validación
     * @var array
     */
    protected $validationRules = [
        'survey_id' => 'required|integer',
        'participant_id' => 'required|integer',
        'ip_address' => 'nullable|string|max:45',
        'status' => 'string|in:draft,in_progress,completed,abandoned,expired,invalid',
        'progress_percentage' => 'numeric|min:0|max:100'
    ];
    
    /**
     * Relación con encuesta
     * 
     * @return array|null
     */
    public function survey()
    {
        return $this->belongsTo('Survey', 'survey_id');
    }
    
    /**
     * Relación con participante
     * 
     * @return array|null
     */
    public function participant()
    {
        return $this->belongsTo('Participant', 'participant_id');
    }
    
    /**
     * Relación con respuestas individuales
     * 
     * @return array
     */
    public function questionResponses()
    {
        return $this->hasMany('QuestionResponse', 'survey_response_id');
    }
    
    /**
     * Iniciar nueva respuesta
     * 
     * @param int $surveyId ID de la encuesta
     * @param int $participantId ID del participante
     * @param array $metadata Metadatos adicionales
     * @return int|false ID de la respuesta creada
     */
    public function startResponse($surveyId, $participantId, $metadata = [])
    {
        try {
            // Verificar si ya existe una respuesta activa
            $existing = $this->db->fetch(
                "SELECT id, status FROM {$this->table} 
                 WHERE survey_id = ? AND participant_id = ? 
                 AND status IN ('draft', 'in_progress')
                 ORDER BY created_at DESC LIMIT 1",
                [$surveyId, $participantId]
            );
            
            if ($existing) {
                // Actualizar respuesta existente
                $this->update($existing['id'], [
                    'last_activity' => date('Y-m-d H:i:s'),
                    'ip_address' => $this->getClientIP(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'session_id' => session_id(),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                return $existing['id'];
            }
            
            // Crear nueva respuesta
            $data = [
                'survey_id' => $surveyId,
                'participant_id' => $participantId,
                'ip_address' => $this->getClientIP(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'started_at' => date('Y-m-d H:i:s'),
                'last_activity' => date('Y-m-d H:i:s'),
                'status' => 'draft',
                'progress_percentage' => 0,
                'device_type' => $this->detectDeviceType(),
                'browser' => $this->detectBrowser(),
                'session_id' => session_id(),
                'metadata' => json_encode($metadata),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->create($data);
            
        } catch (Exception $e) {
            error_log("Error iniciando respuesta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar progreso de respuesta
     * 
     * @param int $responseId ID de la respuesta
     * @param float $progress Porcentaje de progreso (0-100)
     * @return bool
     */
    public function updateProgress($responseId, $progress)
    {
        try {
            $data = [
                'progress_percentage' => min(100, max(0, $progress)),
                'last_activity' => date('Y-m-d H:i:s'),
                'status' => $progress > 0 ? 'in_progress' : 'draft',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->update($responseId, $data);
            
        } catch (Exception $e) {
            error_log("Error actualizando progreso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Completar respuesta
     * 
     * @param int $responseId ID de la respuesta
     * @return bool
     */
    public function completeResponse($responseId)
    {
        try {
            $response = $this->find($responseId);
            if (!$response) {
                throw new Exception('Respuesta no encontrada');
            }
            
            // Calcular tiempo de finalización
            $startTime = strtotime($response['started_at']);
            $completionTime = time() - $startTime;
            
            $data = [
                'completed_at' => date('Y-m-d H:i:s'),
                'last_activity' => date('Y-m-d H:i:s'),
                'status' => 'completed',
                'progress_percentage' => 100,
                'completion_time_seconds' => $completionTime,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->update($responseId, $data);
            
        } catch (Exception $e) {
            error_log("Error completando respuesta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar respuesta como abandonada
     * 
     * @param int $responseId ID de la respuesta
     * @return bool
     */
    public function abandonResponse($responseId)
    {
        try {
            return $this->update($responseId, [
                'status' => 'abandoned',
                'last_activity' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error marcando respuesta como abandonada: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener respuestas por encuesta
     * 
     * @param int $surveyId ID de la encuesta
     * @param string|null $status Estado específico
     * @param int $limit Límite de resultados
     * @param int $offset Offset para paginación
     * @return array
     */
    public function getBySurvey($surveyId, $status = null, $limit = 20, $offset = 0)
    {
        $sql = "SELECT 
                    sr.*,
                    p.email as participant_email,
                    p.first_name as participant_first_name,
                    p.last_name as participant_last_name,
                    p.department,
                    COUNT(qr.id) as answers_count,
                    s.title as survey_title
                FROM {$this->table} sr
                LEFT JOIN participants p ON sr.participant_id = p.id
                LEFT JOIN surveys s ON sr.survey_id = s.id
                LEFT JOIN question_responses qr ON sr.id = qr.survey_response_id
                WHERE sr.survey_id = ?";
        
        $params = [$surveyId];
        
        if ($status) {
            $sql .= " AND sr.status = ?";
            $params[] = $status;
        }
        
        $sql .= " GROUP BY sr.id
                  ORDER BY sr.created_at DESC
                  LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtener respuestas recientes
     * 
     * @param int|null $companyId ID de empresa (opcional)
     * @param int $limit Límite de resultados
     * @return array
     */
    public function getRecent($companyId = null, $limit = 10)
    {
        $sql = "SELECT 
                    sr.*,
                    p.email as participant_email,
                    p.first_name as participant_first_name,
                    p.last_name as participant_last_name,
                    s.title as survey_title,
                    s.company_id
                FROM {$this->table} sr
                LEFT JOIN participants p ON sr.participant_id = p.id
                LEFT JOIN surveys s ON sr.survey_id = s.id
                WHERE sr.status IN ('completed', 'in_progress')";
        
        $params = [];
        
        if ($companyId) {
            $sql .= " AND s.company_id = ?";
            $params[] = $companyId;
        }
        
        $sql .= " ORDER BY sr.last_activity DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtener estadísticas de respuestas
     * 
     * @param int $surveyId ID de la encuesta
     * @return array
     */
    public function getSurveyStats($surveyId)
    {
        try {
            $stats = [
                'total_invited' => 0,
                'total_started' => 0,
                'total_completed' => 0,
                'total_abandoned' => 0,
                'response_rate' => 0,
                'completion_rate' => 0,
                'average_completion_time' => 0,
                'device_distribution' => [],
                'daily_progress' => []
            ];
            
            // Total de participantes invitados
            $stats['total_invited'] = $this->db->fetchColumn(
                "SELECT COUNT(DISTINCT participant_id) FROM survey_invitations WHERE survey_id = ?",
                [$surveyId]
            );
            
            // Estadísticas por estado
            $statusStats = $this->db->fetchAll(
                "SELECT status, COUNT(*) as count FROM {$this->table} 
                 WHERE survey_id = ? GROUP BY status",
                [$surveyId]
            );
            
            foreach ($statusStats as $stat) {
                switch ($stat['status']) {
                    case 'in_progress':
                    case 'draft':
                        $stats['total_started'] += $stat['count'];
                        break;
                    case 'completed':
                        $stats['total_completed'] = $stat['count'];
                        $stats['total_started'] += $stat['count'];
                        break;
                    case 'abandoned':
                        $stats['total_abandoned'] = $stat['count'];
                        $stats['total_started'] += $stat['count'];
                        break;
                }
            }
            
            // Calcular tasas
            if ($stats['total_invited'] > 0) {
                $stats['response_rate'] = round(($stats['total_started'] / $stats['total_invited']) * 100, 2);
            }
            
            if ($stats['total_started'] > 0) {
                $stats['completion_rate'] = round(($stats['total_completed'] / $stats['total_started']) * 100, 2);
            }
            
            // Tiempo promedio de finalización
            $avgTime = $this->db->fetchColumn(
                "SELECT AVG(completion_time_seconds) FROM {$this->table} 
                 WHERE survey_id = ? AND status = 'completed' AND completion_time_seconds IS NOT NULL",
                [$surveyId]
            );
            
            $stats['average_completion_time'] = $avgTime ? round($avgTime / 60, 1) : 0; // En minutos
            
            // Distribución por dispositivo
            $stats['device_distribution'] = $this->db->fetchAll(
                "SELECT device_type, COUNT(*) as count FROM {$this->table} 
                 WHERE survey_id = ? AND device_type IS NOT NULL 
                 GROUP BY device_type ORDER BY count DESC",
                [$surveyId]
            );
            
            // Progreso diario (últimos 30 días)
            $stats['daily_progress'] = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(CASE WHEN status IN ('draft', 'in_progress') THEN 1 END) as started,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
                 FROM {$this->table} 
                 WHERE survey_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at) 
                 ORDER BY date ASC",
                [$surveyId]
            );
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Limpiar respuestas abandonadas antiguas
     * 
     * @param int $daysOld Días de antigüedad
     * @return int Número de respuestas limpiadas
     */
    public function cleanupAbandonedResponses($daysOld = 30)
    {
        try {
            // Marcar como abandonadas las respuestas sin actividad
            $this->db->update($this->table, [
                'status' => 'abandoned',
                'updated_at' => date('Y-m-d H:i:s')
            ], "status IN ('draft', 'in_progress') AND last_activity < DATE_SUB(NOW(), INTERVAL ? DAY)", [$daysOld]);
            
            return $this->db->getAffectedRows();
            
        } catch (Exception $e) {
            error_log("Error limpiando respuestas abandonadas: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Exportar respuestas
     * 
     * @param int $surveyId ID de la encuesta
     * @param string $format Formato (csv, excel, json)
     * @param array $filters Filtros adicionales
     * @return array|false Datos exportados
     */
    public function exportResponses($surveyId, $format = 'csv', $filters = [])
    {
        try {
            $sql = "SELECT 
                        sr.id,
                        sr.started_at,
                        sr.completed_at,
                        sr.status,
                        sr.progress_percentage,
                        sr.completion_time_seconds,
                        sr.device_type,
                        sr.ip_address,
                        p.email as participant_email,
                        p.first_name,
                        p.last_name,
                        p.department,
                        p.position,
                        s.title as survey_title
                    FROM {$this->table} sr
                    LEFT JOIN participants p ON sr.participant_id = p.id
                    LEFT JOIN surveys s ON sr.survey_id = s.id
                    WHERE sr.survey_id = ?";
            
            $params = [$surveyId];
            
            // Aplicar filtros
            if (!empty($filters['status'])) {
                $sql .= " AND sr.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['department'])) {
                $sql .= " AND p.department = ?";
                $params[] = $filters['department'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND sr.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND sr.created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " ORDER BY sr.created_at DESC";
            
            $responses = $this->db->fetchAll($sql, $params);
            
            // Procesar según formato
            switch ($format) {
                case 'csv':
                    return $this->formatAsCsv($responses);
                case 'excel':
                    return $this->formatAsExcel($responses);
                case 'json':
                default:
                    return $responses;
            }
            
        } catch (Exception $e) {
            error_log("Error exportando respuestas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Detectar tipo de dispositivo
     * 
     * @return string
     */
    private function detectDeviceType()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        if (preg_match('/Tablet/', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }
    
    /**
     * Detectar navegador
     * 
     * @return string
     */
    private function detectBrowser()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/Chrome/', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/', $userAgent)) return 'Safari';
        if (preg_match('/Edge/', $userAgent)) return 'Edge';
        if (preg_match('/Opera/', $userAgent)) return 'Opera';
        
        return 'Unknown';
    }
    
    /**
     * Obtener IP del cliente
     * 
     * @return string
     */
    private function getClientIP()
    {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Formatear datos como CSV
     * 
     * @param array $data Datos a formatear
     * @return string CSV formateado
     */
    private function formatAsCsv($data)
    {
        if (empty($data)) {
            return '';
        }
        
        $csv = '';
        
        // Headers
        $headers = array_keys($data[0]);
        $csv .= implode(',', $headers) . "\n";
        
        // Data rows
        foreach ($data as $row) {
            $csv .= implode(',', array_map(function($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, $row)) . "\n";
        }
        
        return $csv;
    }
    
    /**
     * Formatear datos como Excel (placeholder)
     * 
     * @param array $data Datos a formatear
     * @return array Datos formateados para Excel
     */
    private function formatAsExcel($data)
    {
        // TODO: Implementar generación de Excel real con PhpSpreadsheet
        return $data;
    }
}
?>