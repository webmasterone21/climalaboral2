<?php
/**
 * Modelo de Participantes
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * Modelo Participant para gestión completa de participantes:
 * empleados, invitaciones, importación masiva, seguimiento
 * 
 * @package EncuestasHERCO\Models
 * @version 2.0.0
 * @author Sistema HERCO
 */

class Participant extends Model
{
    /**
     * Tabla de la base de datos
     * @var string
     */
    protected $table = 'participants';
    
    /**
     * Campos que se pueden asignar masivamente
     * @var array
     */
    protected $fillable = [
        'company_id',
        'employee_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'department',
        'position',
        'manager_email',
        'hire_date',
        'status',
        'language',
        'timezone',
        'metadata',
        'tags'
    ];
    
    /**
     * Campos que deben ser casteados
     * @var array
     */
    protected $casts = [
        'hire_date' => 'date',
        'metadata' => 'array',
        'tags' => 'array'
    ];
    
    /**
     * Estados de participantes
     */
    const STATUS = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'suspended' => 'Suspendido',
        'terminated' => 'Dado de baja'
    ];
    
    /**
     * Idiomas disponibles
     */
    const LANGUAGES = [
        'es' => 'Español',
        'en' => 'English',
        'fr' => 'Français',
        'pt' => 'Português'
    ];
    
    /**
     * Reglas de validación
     * @var array
     */
    protected $validationRules = [
        'company_id' => 'required|integer',
        'email' => 'required|email|max:255',
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'department' => 'nullable|string|max:100',
        'position' => 'nullable|string|max:100',
        'status' => 'string|in:active,inactive,suspended,terminated'
    ];
    
    /**
     * Relación con empresa
     * 
     * @return array|null
     */
    public function company()
    {
        return $this->belongsTo('Company', 'company_id');
    }
    
    /**
     * Relación con respuestas de encuestas
     * 
     * @return array
     */
    public function surveyResponses()
    {
        return $this->hasMany('Response', 'participant_id');
    }
    
    /**
     * Relación con invitaciones
     * 
     * @return array
     */
    public function invitations()
    {
        return $this->hasMany('SurveyInvitation', 'participant_id');
    }
    
    /**
     * Crear participante con validaciones
     * 
     * @param array $data Datos del participante
     * @return int|false ID del participante creado
     */
    public function createParticipant($data)
    {
        try {
            // Validar datos
            $validation = $this->validateParticipantData($data);
            if (!$validation['valid']) {
                throw new Exception('Datos inválidos: ' . implode(', ', $validation['errors']));
            }
            
            // Verificar email único en la empresa
            if ($this->emailExistsInCompany($data['email'], $data['company_id'])) {
                throw new Exception('El email ya existe en esta empresa');
            }
            
            // Procesar datos
            $data['email'] = strtolower(trim($data['email']));
            $data['first_name'] = trim($data['first_name']);
            $data['last_name'] = trim($data['last_name']);
            $data['status'] = $data['status'] ?? 'active';
            $data['language'] = $data['language'] ?? 'es';
            $data['timezone'] = $data['timezone'] ?? 'America/Tegucigalpa';
            
            // Procesar metadatos
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $data['metadata'] = json_encode($data['metadata']);
            }
            
            // Procesar tags
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = json_encode($data['tags']);
            }
            
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            return $this->create($data);
            
        } catch (Exception $e) {
            error_log("Error creando participante: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar participante
     * 
     * @param int $id ID del participante
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function updateParticipant($id, $data)
    {
        try {
            $participant = $this->find($id);
            if (!$participant) {
                throw new Exception('Participante no encontrado');
            }
            
            // Validar datos
            $validation = $this->validateParticipantData($data, $id);
            if (!$validation['valid']) {
                throw new Exception('Datos inválidos: ' . implode(', ', $validation['errors']));
            }
            
            // Verificar email único si cambió
            if (isset($data['email']) && $data['email'] !== $participant['email']) {
                if ($this->emailExistsInCompany($data['email'], $participant['company_id'], $id)) {
                    throw new Exception('El email ya existe en esta empresa');
                }
                $data['email'] = strtolower(trim($data['email']));
            }
            
            // Procesar arrays
            foreach (['metadata', 'tags'] as $field) {
                if (isset($data[$field]) && is_array($data[$field])) {
                    $data[$field] = json_encode($data[$field]);
                }
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            return $this->update($id, $data);
            
        } catch (Exception $e) {
            error_log("Error actualizando participante: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Importar participantes desde array
     * 
     * @param int $companyId ID de la empresa
     * @param array $participants Lista de participantes
     * @param array $options Opciones de importación
     * @return array Resultado de la importación
     */
    public function importParticipants($companyId, $participants, $options = [])
    {
        $result = [
            'total' => 0,
            'imported' => 0,
            'updated' => 0,
            'errors' => 0,
            'error_details' => []
        ];
        
        try {
            $this->db->beginTransaction();
            
            $allowUpdates = $options['allow_updates'] ?? true;
            $skipErrors = $options['skip_errors'] ?? true;
            
            foreach ($participants as $index => $participantData) {
                $result['total']++;
                $rowNumber = $index + 1;
                
                try {
                    // Agregar company_id
                    $participantData['company_id'] = $companyId;
                    
                    // Verificar si ya existe
                    $existing = null;
                    if (!empty($participantData['email'])) {
                        $existing = $this->db->fetch(
                            "SELECT id FROM {$this->table} WHERE email = ? AND company_id = ?",
                            [strtolower(trim($participantData['email'])), $companyId]
                        );
                    }
                    
                    if ($existing && $allowUpdates) {
                        // Actualizar existente
                        if ($this->updateParticipant($existing['id'], $participantData)) {
                            $result['updated']++;
                        } else {
                            throw new Exception('Error actualizando participante existente');
                        }
                    } elseif (!$existing) {
                        // Crear nuevo
                        if ($this->createParticipant($participantData)) {
                            $result['imported']++;
                        } else {
                            throw new Exception('Error creando nuevo participante');
                        }
                    } else {
                        throw new Exception('Participante ya existe y no se permiten actualizaciones');
                    }
                    
                } catch (Exception $e) {
                    $result['errors']++;
                    $result['error_details'][] = [
                        'row' => $rowNumber,
                        'email' => $participantData['email'] ?? '',
                        'error' => $e->getMessage()
                    ];
                    
                    if (!$skipErrors) {
                        throw $e;
                    }
                }
            }
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en importación masiva: " . $e->getMessage());
            $result['error_details'][] = [
                'row' => 'General',
                'error' => $e->getMessage()
            ];
            return $result;
        }
    }
    
    /**
     * Obtener participantes por empresa
     * 
     * @param int $companyId ID de la empresa
     * @param array $filters Filtros
     * @param int $limit Límite
     * @param int $offset Offset
     * @return array
     */
    public function getByCompany($companyId, $filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE company_id = ?";
        $params = [$companyId];
        
        // Aplicar filtros
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['department'])) {
            $sql .= " AND department = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['tags'])) {
            $sql .= " AND JSON_CONTAINS(tags, ?)";
            $params[] = json_encode($filters['tags']);
        }
        
        $sql .= " ORDER BY last_name ASC, first_name ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtener participantes activos
     * 
     * @param int|null $companyId ID de empresa
     * @param int $limit Límite
     * @return array
     */
    public function getActive($companyId = null, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active'";
        $params = [];
        
        if ($companyId) {
            $sql .= " AND company_id = ?";
            $params[] = $companyId;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtener estadísticas de participantes
     * 
     * @param int $companyId ID de la empresa
     * @return array
     */
    public function getCompanyStats($companyId)
    {
        try {
            $stats = [
                'total' => 0,
                'by_status' => [],
                'by_department' => [],
                'recent_additions' => 0,
                'participation_rate' => 0
            ];
            
            // Total por estado
            $statusStats = $this->db->fetchAll(
                "SELECT status, COUNT(*) as count FROM {$this->table} 
                 WHERE company_id = ? GROUP BY status",
                [$companyId]
            );
            
            foreach ($statusStats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
                $stats['total'] += $stat['count'];
            }
            
            // Total por departamento
            $stats['by_department'] = $this->db->fetchAll(
                "SELECT department, COUNT(*) as count FROM {$this->table} 
                 WHERE company_id = ? AND department IS NOT NULL AND department != ''
                 GROUP BY department ORDER BY count DESC LIMIT 10",
                [$companyId]
            );
            
            // Adiciones recientes (últimos 30 días)
            $stats['recent_additions'] = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM {$this->table} 
                 WHERE company_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                [$companyId]
            );
            
            // Tasa de participación (participantes que han respondido al menos una encuesta)
            $totalParticipants = $stats['by_status']['active'] ?? 0;
            $participantsWithResponses = $this->db->fetchColumn(
                "SELECT COUNT(DISTINCT p.id) FROM {$this->table} p
                 JOIN survey_responses sr ON p.id = sr.participant_id
                 WHERE p.company_id = ? AND p.status = 'active'",
                [$companyId]
            );
            
            if ($totalParticipants > 0) {
                $stats['participation_rate'] = round(($participantsWithResponses / $totalParticipants) * 100, 2);
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de participantes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar participantes
     * 
     * @param int $companyId ID de la empresa
     * @param string $query Término de búsqueda
     * @param int $limit Límite de resultados
     * @return array
     */
    public function search($companyId, $query, $limit = 20)
    {
        $searchTerm = '%' . $query . '%';
        
        return $this->db->fetchAll(
            "SELECT id, email, first_name, last_name, department, position, status
             FROM {$this->table} 
             WHERE company_id = ? 
             AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR department LIKE ?)
             ORDER BY 
                CASE WHEN email LIKE ? THEN 1 
                     WHEN CONCAT(first_name, ' ', last_name) LIKE ? THEN 2 
                     ELSE 3 END,
                last_name ASC, first_name ASC
             LIMIT ?",
            [$companyId, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]
        );
    }
    
    /**
     * Obtener departamentos únicos
     * 
     * @param int $companyId ID de la empresa
     * @return array
     */
    public function getDepartments($companyId)
    {
        return $this->db->fetchAll(
            "SELECT department, COUNT(*) as participant_count
             FROM {$this->table} 
             WHERE company_id = ? AND department IS NOT NULL AND department != ''
             GROUP BY department 
             ORDER BY department ASC",
            [$companyId]
        );
    }
    
    /**
     * Obtener posiciones únicas
     * 
     * @param int $companyId ID de la empresa
     * @return array
     */
    public function getPositions($companyId)
    {
        return $this->db->fetchAll(
            "SELECT position, COUNT(*) as participant_count
             FROM {$this->table} 
             WHERE company_id = ? AND position IS NOT NULL AND position != ''
             GROUP BY position 
             ORDER BY position ASC",
            [$companyId]
        );
    }
    
    /**
     * Activar/desactivar participantes en lote
     * 
     * @param array $participantIds IDs de participantes
     * @param string $status Nuevo estado
     * @return bool
     */
    public function bulkUpdateStatus($participantIds, $status)
    {
        try {
            if (empty($participantIds) || !in_array($status, array_keys(self::STATUS))) {
                return false;
            }
            
            $placeholders = str_repeat('?,', count($participantIds) - 1) . '?';
            $params = array_merge([$status, date('Y-m-d H:i:s')], $participantIds);
            
            $affected = $this->db->query(
                "UPDATE {$this->table} SET status = ?, updated_at = ? WHERE id IN ({$placeholders})",
                $params
            )->rowCount();
            
            return $affected > 0;
            
        } catch (Exception $e) {
            error_log("Error en actualización masiva: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exportar participantes
     * 
     * @param int $companyId ID de la empresa
     * @param array $filters Filtros
     * @param string $format Formato (csv, excel)
     * @return array|string Datos exportados
     */
    public function exportParticipants($companyId, $filters = [], $format = 'csv')
    {
        try {
            $sql = "SELECT 
                        employee_id,
                        email,
                        first_name,
                        last_name,
                        phone,
                        department,
                        position,
                        manager_email,
                        hire_date,
                        status,
                        language,
                        created_at
                    FROM {$this->table} 
                    WHERE company_id = ?";
            
            $params = [$companyId];
            
            // Aplicar filtros
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['department'])) {
                $sql .= " AND department = ?";
                $params[] = $filters['department'];
            }
            
            $sql .= " ORDER BY last_name ASC, first_name ASC";
            
            $participants = $this->db->fetchAll($sql, $params);
            
            if ($format === 'csv') {
                return $this->formatAsCsv($participants);
            }
            
            return $participants;
            
        } catch (Exception $e) {
            error_log("Error exportando participantes: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar datos de participante
     * 
     * @param array $data Datos a validar
     * @param int|null $participantId ID del participante (para updates)
     * @return array
     */
    private function validateParticipantData($data, $participantId = null)
    {
        $errors = [];
        
        // Validar email
        if (empty($data['email'])) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Formato de email inválido';
        }
        
        // Validar nombres
        if (empty($data['first_name'])) {
            $errors[] = 'El nombre es requerido';
        } elseif (strlen($data['first_name']) > 100) {
            $errors[] = 'El nombre no puede exceder 100 caracteres';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'El apellido es requerido';
        } elseif (strlen($data['last_name']) > 100) {
            $errors[] = 'El apellido no puede exceder 100 caracteres';
        }
        
        // Validar empresa
        if (empty($data['company_id'])) {
            $errors[] = 'La empresa es requerida';
        } elseif (!$this->db->exists('companies', 'id = ?', [$data['company_id']])) {
            $errors[] = 'La empresa no existe';
        }
        
        // Validar estado
        if (!empty($data['status']) && !array_key_exists($data['status'], self::STATUS)) {
            $errors[] = 'Estado no válido';
        }
        
        // Validar idioma
        if (!empty($data['language']) && !array_key_exists($data['language'], self::LANGUAGES)) {
            $errors[] = 'Idioma no válido';
        }
        
        // Validar fecha de contratación
        if (!empty($data['hire_date']) && !strtotime($data['hire_date'])) {
            $errors[] = 'Fecha de contratación inválida';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Verificar si email existe en la empresa
     * 
     * @param string $email Email a verificar
     * @param int $companyId ID de la empresa
     * @param int|null $excludeId ID a excluir de la búsqueda
     * @return bool
     */
    private function emailExistsInCompany($email, $companyId, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = ? AND company_id = ?";
        $params = [strtolower(trim($email)), $companyId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->fetch($sql, $params) !== false;
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
                return '"' . str_replace('"', '""', $value ?? '') . '"';
            }, $row)) . "\n";
        }
        
        return $csv;
    }
}
?>