<?php
/**
 * Modelo de Log de Actividades
 * Registra todas las acciones importantes del sistema
 */
class ActivityLog extends Model {
    protected $table = 'activity_logs';
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    /**
     * Registra una actividad en el sistema
     */
    public static function log($action, $entityType = null, $entityId = null, $description = null) {
        try {
            $log = new self();
            $log->user_id = $_SESSION['user_id'] ?? null;
            $log->action = $action;
            $log->entity_type = $entityType;
            $log->entity_id = $entityId;
            $log->description = $description;
            $log->ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $log->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $log->created_at = date('Y-m-d H:i:s');
            
            return $log->save();
        } catch (Exception $e) {
            error_log("Error al registrar actividad: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene logs por usuario
     */
    public static function getByUser($userId, $limit = 50) {
        $instance = new self();
        return $instance->where('user_id', $userId)
                       ->orderBy('created_at', 'DESC')
                       ->limit($limit)
                       ->get();
    }

    /**
     * Obtiene logs por tipo de entidad
     */
    public static function getByEntity($entityType, $entityId = null, $limit = 50) {
        $instance = new self();
        $query = $instance->where('entity_type', $entityType);
        
        if ($entityId !== null) {
            $query->where('entity_id', $entityId);
        }
        
        return $query->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Limpia logs antiguos (mantener últimos 90 días)
     */
    public static function cleanup($days = 90) {
        $instance = new self();
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $instance->where('created_at', '<', $cutoffDate)->delete();
    }
}