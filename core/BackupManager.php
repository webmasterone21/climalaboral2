<?php
require_once __DIR__ . '/../config/backup.php';

class BackupManager {
    private function loadConfig() {
        return getBackupConfig(); // Usar función del config/backup.php
    }
    
    private function getDatabaseConfig() {
        $config = getBackupConfig();
        return $config['database']; // Usar configuración centralizada
    }
}
<?php
/**
 * Gestor Principal de Respaldos
 * core/BackupManager.php
 */
class BackupManager {
    private $db;
    private $config;
    private $backupPath;
    private $logFile;
    
    public function __construct($database) {
        $this->db = $database;
        $this->backupPath = BACKUP_PATH ?? __DIR__ . '/../backups/';
        $this->logFile = $this->backupPath . 'backup.log';
        $this->config = $this->loadConfig();
        
        // Crear directorio si no existe
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    /**
     * Crear respaldo completo de la base de datos
     */
    public function createBackup($type = 'manual', $compress = true) {
        $startTime = microtime(true);
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $filepath = $this->backupPath . $filename;
        
        try {
            $this->log("Iniciando respaldo: {$filename} (Tipo: {$type})", 'INFO');
            
            // Registrar inicio en base de datos
            $backupLogId = $this->logBackupStart($filename, $type);
            
            // Obtener configuración de conexión
            $dbConfig = $this->getDatabaseConfig();
            
            // Crear comando mysqldump con opciones avanzadas
            $command = $this->buildMysqldumpCommand($dbConfig, $filepath);
            
            // Ejecutar respaldo
            $output = [];
            $returnVar = 0;
            exec($command . ' 2>&1', $output, $returnVar);
            
            if ($returnVar !== 0 || !file_exists($filepath)) {
                throw new Exception("Error en mysqldump: " . implode("\n", $output));
            }
            
            $fileSize = filesize($filepath);
            
            // Comprimir si está habilitado
            if ($compress && $this->config['compress_backups']) {
                $compressedFile = $this->compressBackup($filepath);
                if ($compressedFile) {
                    unlink($filepath); // Eliminar archivo sin comprimir
                    $filepath = $compressedFile;
                    $filename = basename($compressedFile);
                    $fileSize = filesize($filepath);
                }
            }
            
            $duration = round(microtime(true) - $startTime, 2);
            
            // Actualizar log de respaldo como exitoso
            $this->logBackupEnd($backupLogId, 'success', $filename, $fileSize, $duration);
            
            // Limpiar respaldos antiguos
            $this->cleanOldBackups();
            
            // Actualizar timestamp del último respaldo
            $this->updateLastBackupTime();
            
            $this->log("Respaldo completado exitosamente: {$filename} ({$this->formatBytes($fileSize)}) en {$duration}s", 'SUCCESS');
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => $fileSize,
                'duration' => $duration,
                'compressed' => $compress && $this->config['compress_backups']
            ];
            
        } catch (Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $errorMsg = $e->getMessage();
            
            if (isset($backupLogId)) {
                $this->logBackupEnd($backupLogId, 'error', $filename, 0, $duration, $errorMsg);
            }
            
            $this->log("Error en respaldo {$filename}: {$errorMsg}", 'ERROR');
            
            return [
                'success' => false,
                'error' => $errorMsg,
                'filename' => $filename,
                'duration' => $duration
            ];
        }
    }
    
    /**
     * Restaurar base de datos desde respaldo
     */
    public function restoreBackup($filename, $confirmOverwrite = false) {
        if (!$confirmOverwrite) {
            throw new Exception("Debe confirmar que desea sobrescribir la base de datos actual");
        }
        
        $filepath = $this->backupPath . $filename;
        
        if (!file_exists($filepath)) {
            throw new Exception("Archivo de respaldo no encontrado: {$filename}");
        }
        
        try {
            $this->log("Iniciando restauración desde: {$filename}", 'INFO');
            
            $dbConfig = $this->getDatabaseConfig();
            
            // Descomprimir si es necesario
            $sqlFile = $filepath;
            if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
                $sqlFile = $this->decompressBackup($filepath);
            }
            
            // Validar estructura del archivo SQL
            if (!$this->validateBackupFile($sqlFile)) {
                throw new Exception("El archivo de respaldo no es válido o está corrupto");
            }
            
            // Crear respaldo de seguridad antes de restaurar
            $safetyBackup = $this->createBackup('safety_before_restore', false);
            if (!$safetyBackup['success']) {
                throw new Exception("No se pudo crear respaldo de seguridad antes de restaurar");
            }
            
            // Comando de restauración
            $command = sprintf(
                'mysql -h%s -u%s -p%s %s < %s 2>&1',
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($sqlFile)
            );
            
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);
            
            // Limpiar archivo temporal si se descomprimió
            if ($sqlFile !== $filepath && file_exists($sqlFile)) {
                unlink($sqlFile);
            }
            
            if ($returnVar !== 0) {
                throw new Exception("Error en restauración: " . implode("\n", $output));
            }
            
            $this->log("Restauración completada exitosamente desde: {$filename}", 'SUCCESS');
            
            return [
                'success' => true,
                'message' => 'Base de datos restaurada exitosamente',
                'filename' => $filename,
                'safety_backup' => $safetyBackup['filename']
            ];
            
        } catch (Exception $e) {
            $this->log("Error en restauración desde {$filename}: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Programar respaldos automáticos
     */
    public function scheduleBackup() {
        $config = $this->getBackupConfig();
        
        if (!$config['auto_backup']) {
            return false;
        }
        
        $now = new DateTime();
        $lastBackup = $config['last_backup'] ? new DateTime($config['last_backup']) : null;
        
        $shouldBackup = false;
        
        switch ($config['backup_frequency']) {
            case 'daily':
                $shouldBackup = !$lastBackup || $lastBackup->format('Y-m-d') < $now->format('Y-m-d');
                break;
                
            case 'weekly':
                $shouldBackup = !$lastBackup || $lastBackup->diff($now)->days >= 7;
                break;
                
            case 'monthly':
                $shouldBackup = !$lastBackup || $lastBackup->format('Y-m') < $now->format('Y-m');
                break;
        }
        
        if ($shouldBackup) {
            $this->log("Ejecutando respaldo automático programado ({$config['backup_frequency']})", 'INFO');
            return $this->createBackup('automatic');
        }
        
        return false;
    }
    
    /**
     * Obtener lista de respaldos disponibles
     */
    public function getBackupList() {
        $backups = [];
        $pattern = $this->backupPath . 'backup_*.{sql,sql.gz}';
        
        foreach (glob($pattern, GLOB_BRACE) as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'filepath' => $file,
                'size' => filesize($file),
                'size_formatted' => $this->formatBytes(filesize($file)),
                'created' => filemtime($file),
                'created_formatted' => date('d/m/Y H:i:s', filemtime($file)),
                'compressed' => pathinfo($file, PATHINFO_EXTENSION) === 'gz'
            ];
        }
        
        // Obtener información adicional de la base de datos
        $sql = "SELECT * FROM backup_logs ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        $dbLogs = [];
        
        while ($log = $stmt->fetch()) {
            $dbLogs[$log['filename']] = $log;
        }
        
        // Combinar información
        foreach ($backups as &$backup) {
            if (isset($dbLogs[$backup['filename']])) {
                $backup = array_merge($backup, $dbLogs[$backup['filename']]);
            }
        }
        
        // Ordenar por fecha de creación descendente
        usort($backups, function($a, $b) {
            return $b['created'] - $a['created'];
        });
        
        return $backups;
    }
    
    // MÉTODOS PRIVADOS
    private function buildMysqldumpCommand($config, $filepath) {
        $command = sprintf(
            'mysqldump --single-transaction --routines --triggers --events ' .
            '--hex-blob --opt --quick --lock-tables=false ' .
            '-h%s -u%s -p%s %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database'])
        );
        
        $command .= ' > ' . escapeshellarg($filepath);
        return $command;
    }
    
    private function compressBackup($filepath) {
        if (!extension_loaded('zlib')) {
            $this->log("Extensión zlib no disponible, saltando compresión", 'WARNING');
            return false;
        }
        
        $compressedFile = $filepath . '.gz';
        
        $input = fopen($filepath, 'rb');
        $output = gzopen($compressedFile, 'wb9');
        
        if ($input && $output) {
            while (!feof($input)) {
                gzwrite($output, fread($input, 8192));
            }
            
            fclose($input);
            gzclose($output);
            
            return $compressedFile;
        }
        
        return false;
    }
    
    private function decompressBackup($filepath) {
        if (pathinfo($filepath, PATHINFO_EXTENSION) !== 'gz') {
            return $filepath;
        }
        
        $sqlFile = str_replace('.gz', '', $filepath);
        
        $input = gzopen($filepath, 'rb');
        $output = fopen($sqlFile, 'wb');
        
        if ($input && $output) {
            while (!gzeof($input)) {
                fwrite($output, gzread($input, 8192));
            }
            
            gzclose($input);
            fclose($output);
            
            return $sqlFile;
        }
        
        throw new Exception("No se pudo descomprimir el archivo de respaldo");
    }
    
    private function validateBackupFile($filepath) {
        if (!file_exists($filepath) || filesize($filepath) < 100) {
            return false;
        }
        
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return false;
        }
        
        $firstLine = fgets($handle);
        $hasCreateTable = false;
        $lineCount = 0;
        
        while (($line = fgets($handle)) && $lineCount < 100) {
            if (stripos($line, 'CREATE TABLE') !== false) {
                $hasCreateTable = true;
                break;
            }
            $lineCount++;
        }
        
        fclose($handle);
        
        return (stripos($firstLine, 'MySQL dump') !== false || 
                stripos($firstLine, 'mysqldump') !== false) && 
               $hasCreateTable;
    }
    
    private function cleanOldBackups() {
        $maxBackups = $this->config['max_backups'];
        
        if ($maxBackups <= 0) {
            return;
        }
        
        $backups = $this->getBackupList();
        
        if (count($backups) > $maxBackups) {
            $toDelete = array_slice($backups, $maxBackups);
            
            foreach ($toDelete as $backup) {
                try {
                    $this->deleteBackup($backup['filename']);
                } catch (Exception $e) {
                    $this->log("Error eliminando respaldo antiguo {$backup['filename']}: " . $e->getMessage(), 'ERROR');
                }
            }
        }
    }
    
    private function loadConfig() {
        $defaultConfig = [
            'backup_frequency' => 'weekly',
            'backup_time' => '02:00:00',
            'max_backups' => 10,
            'auto_backup' => true,
            'compress_backups' => true
        ];
        
        try {
            $config = $this->getBackupConfig();
            return array_merge($defaultConfig, $config);
        } catch (Exception $e) {
            return $defaultConfig;
        }
    }
    
    private function getBackupConfig() {
        $sql = "SELECT * FROM backup_config ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetch() ?: [];
    }
    
    private function getDatabaseConfig() {
        return [
            'host' => DB_HOST ?? 'localhost',
            'username' => DB_USER ?? 'root',
            'password' => DB_PASS ?? '',
            'database' => DB_NAME ?? 'encuestas_clima'
        ];
    }
    
    private function updateLastBackupTime() {
        $sql = "UPDATE backup_config SET last_backup = NOW() ORDER BY id DESC LIMIT 1";
        $this->db->exec($sql);
    }
    
    private function logBackupStart($filename, $type) {
        $sql = "INSERT INTO backup_logs (filename, backup_type, status) VALUES (:filename, :type, 'in_progress')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':filename' => $filename, ':type' => $type]);
        return $this->db->lastInsertId();
    }
    
    private function logBackupEnd($logId, $status, $filename, $fileSize, $duration, $errorMsg = null) {
        $sql = "UPDATE backup_logs SET 
                status = :status, 
                filename = :filename,
                file_size = :file_size, 
                duration_seconds = :duration, 
                error_message = :error_message
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $logId,
            ':status' => $status,
            ':filename' => $filename,
            ':file_size' => $fileSize,
            ':duration' => $duration,
            ':error_message' => $errorMsg
        ]);
    }
    
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // Log a archivo
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // Log a consola si está en CLI
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    public function deleteBackup($filename) {
        $filepath = $this->backupPath . $filename;
        
        if (!file_exists($filepath)) {
            throw new Exception("Archivo de respaldo no encontrado: {$filename}");
        }
        
        if (unlink($filepath)) {
            // Actualizar registro en base de datos
            $sql = "UPDATE backup_logs SET status = 'deleted' WHERE filename = :filename";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':filename' => $filename]);
            
            $this->log("Respaldo eliminado: {$filename}", 'INFO');
            return true;
        }
        
        return false;
    }
}
?>