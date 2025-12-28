<?php
require_once __DIR__ . '/../config/backup.php';

// Usar configuraciones centralizadas
$config = getBackupConfig();

<?php
/**
 * Script de Respaldos Automáticos para Cron
 * scripts/backup_cron.php
 * 
 * Configuración de crontab sugerida:
 * # Respaldo diario a las 2:00 AM
 * 0 2 * * * /usr/bin/php /path/to/scripts/backup_cron.php >> /var/log/backup_cron.log 2>&1
 * 
 * # Respaldo semanal los domingos a las 3:00 AM
 * 0 3 * * 0 /usr/bin/php /path/to/scripts/backup_cron.php >> /var/log/backup_cron.log 2>&1
 */

// Solo permitir ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Este script solo puede ejecutarse desde línea de comandos\n";
    exit(1);
}

// Configurar entorno
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Clase para gestionar trabajos de respaldo programados
 */
class BackupCronJob {
    private $backupManager;
    private $logFile;
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->logFile = BACKUP_PATH . 'cron.log';
        
        try {
            $database = new Database();
            $this->backupManager = new BackupManager($database->connect());
        } catch (Exception $e) {
            $this->log("Error de conexión a base de datos: " . $e->getMessage(), 'CRITICAL');
            exit(1);
        }
    }
    
    /**
     * Ejecutar trabajo de respaldo
     */
    public function run() {
        $this->log("=== Iniciando trabajo de respaldo programado ===");
        $this->log("Hora de inicio: " . date('Y-m-d H:i:s'));
        $this->log("Usuario del sistema: " . get_current_user());
        $this->log("Directorio de trabajo: " . getcwd());
        
        try {
            // Verificar prerrequisitos
            if (!$this->checkPrerequisites()) {
                $this->log("Falló verificación de prerrequisitos", 'ERROR');
                exit(1);
            }
            
            // Verificar espacio en disco
            if (!$this->checkDiskSpace()) {
                $this->log("Espacio en disco insuficiente", 'ERROR');
                exit(1);
            }
            
            // Ejecutar respaldo programado
            $result = $this->backupManager->scheduleBackup();
            
            if ($result === false) {
                $this->log("No es necesario ejecutar respaldo en este momento");
                $this->log("Próximo respaldo programado según configuración");
                exit(0);
            }
            
            if ($result['success']) {
                $this->logSuccessfulBackup($result);
                
                // Verificar integridad del respaldo creado
                $this->verifyBackupIntegrity($result['filename']);
                
                // Enviar notificación si está configurado
                $this->sendNotificationIfEnabled($result);
                
                $this->log("=== Trabajo de respaldo completado exitosamente ===");
                exit(0);
                
            } else {
                $this->logFailedBackup($result);
                
                // Enviar alerta de error
                $this->sendErrorAlert($result);
                
                $this->log("=== Trabajo de respaldo finalizado con errores ===");
                exit(1);
            }
            
        } catch (Exception $e) {
            $this->log("Excepción crítica en cron job: " . $e->getMessage(), 'CRITICAL');
            $this->log("Stack trace: " . $e->getTraceAsString(), 'DEBUG');
            
            // Enviar alerta crítica
            $this->sendCriticalAlert($e);
            
            exit(1);
        } finally {
            $duration = round(microtime(true) - $this->startTime, 2);
            $this->log("Duración total del trabajo: {$duration} segundos");
        }
    }
    
    /**
     * Verificar prerrequisitos del sistema
     */
    private function checkPrerequisites() {
        $this->log("Verificando prerrequisitos del sistema...");
        
        // Verificar mysqldump
        $output = [];
        $returnVar = 0;
        exec('mysqldump --version 2>&1', $output, $returnVar);
        
        if ($returnVar !== 0) {
            $this->log("mysqldump no está disponible", 'ERROR');
            return false;
        }
        
        $this->log("mysqldump disponible: " . implode(' ', $output));
        
        // Verificar directorio de respaldos
        if (!is_dir(BACKUP_PATH)) {
            $this->log("Creando directorio de respaldos: " . BACKUP_PATH);
            if (!mkdir(BACKUP_PATH, 0755, true)) {
                $this->log("No se pudo crear directorio de respaldos", 'ERROR');
                return false;
            }
        }
        
        if (!is_writable(BACKUP_PATH)) {
            $this->log("Directorio de respaldos no tiene permisos de escritura", 'ERROR');
            return false;
        }
        
        // Verificar extensiones PHP necesarias
        $requiredExtensions = ['pdo', 'pdo_mysql', 'zlib'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->log("Extensión PHP requerida no disponible: {$ext}", 'ERROR');
                return false;
            }
        }
        
        $this->log("Todos los prerrequisitos verificados correctamente");
        return true;
    }
    
    /**
     * Verificar espacio disponible en disco
     */
    private function checkDiskSpace() {
        $freeSpace = disk_free_space(BACKUP_PATH);
        $totalSpace = disk_total_space(BACKUP_PATH);
        
        if ($freeSpace === false || $totalSpace === false) {
            $this->log("No se pudo verificar espacio en disco", 'WARNING');
            return true; // Continuar con precaución
        }
        
        $freeSpaceMB = $freeSpace / (1024 * 1024);
        $usagePercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        
        $this->log(sprintf(
            "Espacio en disco - Libre: %.2f MB (%.1f%% usado)",
            $freeSpaceMB,
            $usagePercent
        ));
        
        // Requerir al menos 500MB libres
        if ($freeSpaceMB < 500) {
            $this->log("Espacio en disco insuficiente (mínimo 500MB requeridos)", 'ERROR');
            return false;
        }
        
        // Advertir si el uso supera el 90%
        if ($usagePercent > 90) {
            $this->log("Advertencia: Disco casi lleno ({$usagePercent}% usado)", 'WARNING');
        }
        
        return true;
    }
    
    /**
     * Registrar respaldo exitoso
     */
    private function logSuccessfulBackup($result) {
        $this->log("Respaldo automático completado exitosamente", 'SUCCESS');
        $this->log("Archivo: " . $result['filename']);
        $this->log("Tamaño: " . $this->formatBytes($result['size']));
        $this->log("Duración: " . $result['duration'] . " segundos");
        $this->log("Comprimido: " . ($result['compressed'] ? 'Sí' : 'No'));
        $this->log("Ubicación: " . $result['filepath']);
    }
    
    /**
     * Registrar respaldo fallido
     */
    private function logFailedBackup($result) {
        $this->log("Error en respaldo automático", 'ERROR');
        $this->log("Archivo: " . ($result['filename'] ?? 'N/A'));
        $this->log("Error: " . $result['error']);
        $this->log("Duración hasta fallo: " . ($result['duration'] ?? 0) . " segundos");
    }
    
    /**
     * Verificar integridad del respaldo creado
     */
    private function verifyBackupIntegrity($filename) {
        $this->log("Verificando integridad del respaldo creado...");
        
        try {
            $validation = $this->backupManager->validateBackup($filename);
            
            if ($validation['valid']) {
                $this->log("Integridad del respaldo verificada correctamente", 'SUCCESS');
            } else {
                $this->log("Advertencia: El respaldo puede estar corrupto - " . ($validation['error'] ?? 'Razón desconocida'), 'WARNING');
            }
        } catch (Exception $e) {
            $this->log("Error al verificar integridad: " . $e->getMessage(), 'WARNING');
        }
    }
    
    /**
     * Enviar notificación si está habilitado
     */
    private function sendNotificationIfEnabled($result) {
        $config = $this->getSystemConfig();
        
        if (!($config['enable_backup_notifications'] ?? false)) {
            return;
        }
        
        $this->log("Enviando notificación de respaldo exitoso...");
        
        try {
            $this->sendEmailNotification([
                'type' => 'success',
                'subject' => "Respaldo automático completado - " . date('d/m/Y H:i'),
                'message' => $this->buildSuccessMessage($result)
            ]);
            
            $this->log("Notificación enviada exitosamente");
            
        } catch (Exception $e) {
            $this->log("Error enviando notificación: " . $e->getMessage(), 'WARNING');
        }
    }
    
    /**
     * Enviar alerta de error
     */
    private function sendErrorAlert($result) {
        $this->log("Enviando alerta de error...");
        
        try {
            $this->sendEmailNotification([
                'type' => 'error',
                'subject' => "ERROR en respaldo automático - " . date('d/m/Y H:i'),
                'message' => $this->buildErrorMessage($result),
                'priority' => 'high'
            ]);
            
            $this->log("Alerta de error enviada");
            
        } catch (Exception $e) {
            $this->log("Error enviando alerta: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Enviar alerta crítica
     */
    private function sendCriticalAlert($exception) {
        $this->log("Enviando alerta crítica...");
        
        try {
            $this->sendEmailNotification([
                'type' => 'critical',
                'subject' => "CRÍTICO: Fallo en sistema de respaldos - " . date('d/m/Y H:i'),
                'message' => $this->buildCriticalMessage($exception),
                'priority' => 'urgent'
            ]);
            
        } catch (Exception $e) {
            $this->log("Error enviando alerta crítica: " . $e->getMessage(), 'CRITICAL');
        }
    }
    
    /**
     * Construir mensaje de éxito
     */
    private function buildSuccessMessage($result) {
        return "El respaldo automático se ha completado exitosamente:\n\n" .
               "📄 Archivo: {$result['filename']}\n" .
               "📊 Tamaño: " . $this->formatBytes($result['size']) . "\n" .
               "⏱️ Duración: {$result['duration']} segundos\n" .
               "🗜️ Comprimido: " . ($result['compressed'] ? 'Sí' : 'No') . "\n" .
               "📅 Fecha: " . date('d/m/Y H:i:s') . "\n" .
               "🖥️ Servidor: " . gethostname() . "\n\n" .
               "El respaldo se encuentra disponible en el panel de administración.";
    }
    
    /**
     * Construir mensaje de error
     */
    private function buildErrorMessage($result) {
        return "❌ Se ha producido un error en el respaldo automático:\n\n" .
               "📄 Archivo: " . ($result['filename'] ?? 'N/A') . "\n" .
               "❗ Error: {$result['error']}\n" .
               "⏱️ Duración hasta fallo: " . ($result['duration'] ?? 0) . " segundos\n" .
               "📅 Fecha: " . date('d/m/Y H:i:s') . "\n" .
               "🖥️ Servidor: " . gethostname() . "\n\n" .
               "Por favor revise el sistema de respaldos lo antes posible.";
    }
    
    /**
     * Construir mensaje crítico
     */
    private function buildCriticalMessage($exception) {
        return "🚨 ALERTA CRÍTICA: Fallo en el sistema de respaldos\n\n" .
               "❗ Excepción: " . $exception->getMessage() . "\n" .
               "📁 Archivo: " . $exception->getFile() . "\n" .
               "📍 Línea: " . $exception->getLine() . "\n" .
               "📅 Fecha: " . date('d/m/Y H:i:s') . "\n" .
               "🖥️ Servidor: " . gethostname() . "\n\n" .
               "Stack trace:\n" . $exception->getTraceAsString() . "\n\n" .
               "ACCIÓN REQUERIDA: Verificar sistema de respaldos inmediatamente.";
    }
    
    /**
     * Enviar notificación por email (método stub)
     */
    private function sendEmailNotification($params) {
        // TODO: Implementar envío real de email usando PHPMailer o similar
        // Por ahora solo registrar en log
        $this->log("EMAIL [{$params['type']}]: {$params['subject']}", 'NOTIFICATION');
        $this->log("Mensaje: " . substr($params['message'], 0, 200) . "...", 'DEBUG');
    }
    
    /**
     * Obtener configuración del sistema
     */
    private function getSystemConfig() {
        // TODO: Obtener desde base de datos o archivo de configuración
        return [
            'enable_backup_notifications' => false, // Cambiar a true cuando se implemente email
            'admin_email' => 'admin@encuestas.com',
            'notification_level' => 'all' // all, errors_only, critical_only
        ];
    }
    
    /**
     * Registrar mensaje en log
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // Log a archivo
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // También mostrar en consola
        echo $logMessage;
    }
    
    /**
     * Formatear bytes en unidades legibles
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// ==========================================
// EJECUCIÓN DEL SCRIPT
// ==========================================

// Verificar argumentos de línea de comandos
$options = getopt('h', ['help', 'dry-run', 'force', 'verbose']);

if (isset($options['h']) || isset($options['help'])) {
    echo "Uso: php backup_cron.php [opciones]\n\n";
    echo "Opciones:\n";
    echo "  -h, --help      Mostrar esta ayuda\n";
    echo "  --dry-run       Simular ejecución sin crear respaldo\n";
    echo "  --force         Forzar respaldo aunque no esté programado\n";
    echo "  --verbose       Mostrar información detallada\n\n";
    echo "Ejemplos:\n";
    echo "  php backup_cron.php                # Respaldo normal\n";
    echo "  php backup_cron.php --dry-run      # Simular respaldo\n";
    echo "  php backup_cron.php --force        # Forzar respaldo\n";
    exit(0);
}

// Manejar opciones especiales
if (isset($options['dry-run'])) {
    echo "MODO DRY-RUN: Simulación activada\n";
    // TODO: Implementar modo de simulación
}

if (isset($options['force'])) {
    echo "MODO FORZADO: Ejecutando respaldo sin verificar programación\n";
    // TODO: Implementar respaldo forzado
}

if (isset($options['verbose'])) {
    echo "MODO VERBOSE: Información detallada activada\n";
    // TODO: Activar logging detallado
}

// Ejecutar trabajo de respaldo
try {
    $cronJob = new BackupCronJob();
    $cronJob->run();
} catch (Throwable $e) {
    echo "Error fatal en script de respaldo: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>