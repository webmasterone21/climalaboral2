<?php
/**
 * Controlador de Respaldos - VERSIÓN CORREGIDA
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * Sistema de respaldos automáticos y manuales de base de datos,
 * restauración, gestión de archivos y configuraciones de backup.
 * 
 * CORRECCIONES APLICADAS:
 * ✅ Eliminación de dependencias externas no verificadas
 * ✅ Implementación básica sin librerías adicionales
 * ✅ Validaciones robustas de seguridad
 * ✅ Manejo de errores mejorado
 * ✅ Configuraciones dinámicas
 * 
 * @package EncuestasHERCO\Controllers
 * @version 2.0.0
 * @author Sistema HERCO
 */

class BackupController extends Controller
{
    private $backupPath;
    private $maxBackups = 30;
    private $compressionEnabled = false;
    
    /**
     * Inicialización del controlador con verificación de dependencias
     */
    protected function initialize()
    {
        // Requerir autenticación
        $this->requireAuth();
        
        // Verificar permisos de super admin
        if ($this->user['role'] !== 'super_admin' && $this->user['role'] !== 'admin') {
            $this->setFlashMessage('Sin permisos para gestionar respaldos', 'error');
            $this->redirect('/admin/dashboard');
        }
        
        // Configurar rutas de respaldo
        $this->backupPath = __DIR__ . '/../backups/';
        
        // Crear directorios de respaldo si no existen
        $this->createBackupDirectories();
        
        // Verificar si la compresión está disponible
        $this->compressionEnabled = extension_loaded('zip');
        
        // Layout administrativo
        $this->defaultLayout = 'admin';
    }
    
    /**
     * Dashboard de respaldos
     */
    public function index()
    {
        try {
            // Obtener lista de respaldos
            $backups = $this->getBackupList();
            
            // Obtener estadísticas
            $stats = $this->getBackupStats();
            
            // Configuraciones actuales
            $config = $this->getBackupConfig();
            
            $data = [
                'backups' => $backups,
                'stats' => $stats,
                'config' => $config,
                'compression_enabled' => $this->compressionEnabled,
                'backup_types' => $this->getBackupTypes()
            ];
            
            $this->render('admin/system/backups', $data);
            
        } catch (Exception $e) {
            error_log("Error en BackupController::index: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar respaldos', 'error');
            $this->redirect('/admin/dashboard');
        }
    }
    
    /**
     * Crear respaldo manual
     */
    public function create()
    {
        try {
            // Validar CSRF
            $this->validateCsrfToken();
            
            $type = $_POST['type'] ?? 'full';
            $description = $this->sanitizeInput($_POST['description'] ?? 'Respaldo manual');
            
            // Validar tipo de respaldo
            $allowedTypes = array_keys($this->getBackupTypes());
            if (!in_array($type, $allowedTypes)) {
                throw new Exception('Tipo de respaldo inválido');
            }
            
            // Generar respaldo
            $backupResult = $this->generateBackup($type, $description);
            
            if ($backupResult['success']) {
                $this->setFlashMessage(
                    "Respaldo creado exitosamente: {$backupResult['filename']}", 
                    'success'
                );
                
                // Log de actividad
                $this->logActivity('backup_created', [
                    'type' => $type,
                    'filename' => $backupResult['filename'],
                    'size' => $backupResult['size']
                ]);
            } else {
                throw new Exception($backupResult['error']);
            }
            
        } catch (Exception $e) {
            error_log("Error creando respaldo: " . $e->getMessage());
            $this->setFlashMessage('Error al crear respaldo: ' . $e->getMessage(), 'error');
        }
        
        $this->redirect('/admin/system/backups');
    }
    
    /**
     * Descargar respaldo
     */
    public function download($filename)
    {
        try {
            // Validar nombre de archivo por seguridad
            if (!$this->isValidBackupFilename($filename)) {
                throw new Exception('Nombre de archivo inválido');
            }
            
            $filePath = $this->backupPath . 'manual/' . $filename;
            
            if (!file_exists($filePath)) {
                $filePath = $this->backupPath . 'automatic/' . $filename;
            }
            
            if (!file_exists($filePath)) {
                throw new Exception('Archivo de respaldo no encontrado');
            }
            
            // Log de actividad
            $this->logActivity('backup_downloaded', [
                'filename' => $filename
            ]);
            
            // Enviar archivo
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache');
            
            readfile($filePath);
            exit;
            
        } catch (Exception $e) {
            error_log("Error descargando respaldo: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/system/backups');
        }
    }
    
    /**
     * Eliminar respaldo
     */
    public function delete($filename)
    {
        try {
            // Validar CSRF
            $this->validateCsrfToken();
            
            // Validar nombre de archivo
            if (!$this->isValidBackupFilename($filename)) {
                throw new Exception('Nombre de archivo inválido');
            }
            
            $filePath = $this->backupPath . 'manual/' . $filename;
            
            if (!file_exists($filePath)) {
                $filePath = $this->backupPath . 'automatic/' . $filename;
            }
            
            if (!file_exists($filePath)) {
                throw new Exception('Archivo de respaldo no encontrado');
            }
            
            // No permitir eliminar respaldos automáticos recientes
            if (strpos($filePath, 'automatic/') !== false) {
                $fileTime = filemtime($filePath);
                $daysSinceCreation = (time() - $fileTime) / (24 * 60 * 60);
                
                if ($daysSinceCreation < 7) {
                    throw new Exception('No se pueden eliminar respaldos automáticos de menos de 7 días');
                }
            }
            
            // Eliminar archivo
            if (unlink($filePath)) {
                $this->setFlashMessage('Respaldo eliminado exitosamente', 'success');
                
                // Log de actividad
                $this->logActivity('backup_deleted', [
                    'filename' => $filename
                ]);
            } else {
                throw new Exception('Error al eliminar archivo');
            }
            
        } catch (Exception $e) {
            error_log("Error eliminando respaldo: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
        }
        
        $this->redirect('/admin/system/backups');
    }
    
    /**
     * Restaurar desde respaldo (funcionalidad básica)
     */
    public function restore($filename)
    {
        try {
            // Esta es una funcionalidad muy peligrosa, requiere confirmación especial
            if (!isset($_POST['confirm_restore']) || $_POST['confirm_restore'] !== 'YES_RESTORE') {
                throw new Exception('Confirmación de restauración requerida');
            }
            
            // Validar CSRF
            $this->validateCsrfToken();
            
            // Solo permitir a super admin
            if ($this->user['role'] !== 'super_admin') {
                throw new Exception('Solo el super administrador puede restaurar respaldos');
            }
            
            // Validar archivo
            if (!$this->isValidBackupFilename($filename)) {
                throw new Exception('Nombre de archivo inválido');
            }
            
            $filePath = $this->backupPath . 'manual/' . $filename;
            if (!file_exists($filePath)) {
                $filePath = $this->backupPath . 'automatic/' . $filename;
            }
            
            if (!file_exists($filePath)) {
                throw new Exception('Archivo de respaldo no encontrado');
            }
            
            // Crear respaldo de seguridad antes de restaurar
            $securityBackup = $this->generateBackup('full', 'Pre-restauración automático');
            
            if (!$securityBackup['success']) {
                throw new Exception('Error creando respaldo de seguridad: ' . $securityBackup['error']);
            }
            
            // Intentar restaurar (implementación básica)
            $restoreResult = $this->performRestore($filePath);
            
            if ($restoreResult['success']) {
                $this->setFlashMessage('Restauración completada exitosamente', 'success');
                
                // Log de actividad crítica
                $this->logActivity('backup_restored', [
                    'filename' => $filename,
                    'security_backup' => $securityBackup['filename']
                ]);
            } else {
                throw new Exception($restoreResult['error']);
            }
            
        } catch (Exception $e) {
            error_log("Error en restauración: " . $e->getMessage());
            $this->setFlashMessage('Error en restauración: ' . $e->getMessage(), 'error');
        }
        
        $this->redirect('/admin/system/backups');
    }
    
    /**
     * Configurar respaldos automáticos
     */
    public function settings()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->updateSettings();
            }
            
            $config = $this->getBackupConfig();
            
            $data = [
                'config' => $config,
                'backup_frequencies' => $this->getBackupFrequencies(),
                'retention_options' => $this->getRetentionOptions()
            ];
            
            $this->render('admin/system/backup-settings', $data);
            
        } catch (Exception $e) {
            error_log("Error en configuración de respaldos: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar configuración', 'error');
            $this->redirect('/admin/system/backups');
        }
    }
    
    /**
     * Actualizar configuraciones de respaldo
     */
    private function updateSettings()
    {
        try {
            $this->validateCsrfToken();
            
            $settings = [
                'auto_backup_enabled' => !empty($_POST['auto_backup_enabled']),
                'backup_frequency' => $_POST['backup_frequency'] ?? 'daily',
                'retention_days' => max(7, (int)($_POST['retention_days'] ?? 30)),
                'compression_enabled' => !empty($_POST['compression_enabled']) && $this->compressionEnabled,
                'email_notifications' => !empty($_POST['email_notifications']),
                'notification_email' => $_POST['notification_email'] ?? ''
            ];
            
            // Guardar configuraciones
            $this->saveBackupConfig($settings);
            
            $this->setFlashMessage('Configuraciones de respaldo actualizadas', 'success');
            
            // Log de actividad
            $this->logActivity('backup_settings_updated', $settings);
            
            $this->redirect('/admin/system/backup-settings');
            
        } catch (Exception $e) {
            error_log("Error actualizando configuraciones: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/system/backup-settings');
        }
    }
    
    /**
     * Generar respaldo
     */
    private function generateBackup($type, $description = '')
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$type}_{$timestamp}.sql";
            
            // Determinar directorio según si es manual o automático
            $isManual = !empty($description) && $description !== 'Respaldo automático';
            $directory = $isManual ? 'manual' : 'automatic';
            $fullPath = $this->backupPath . $directory . '/' . $filename;
            
            // Generar dump de la base de datos
            $dumpResult = $this->createDatabaseDump($fullPath, $type);
            
            if (!$dumpResult['success']) {
                return ['success' => false, 'error' => $dumpResult['error']];
            }
            
            // Comprimir si está habilitado
            if ($this->compressionEnabled && $this->getBackupConfig()['compression_enabled']) {
                $compressedPath = $this->compressBackup($fullPath);
                if ($compressedPath) {
                    unlink($fullPath); // Eliminar archivo sin comprimir
                    $fullPath = $compressedPath;
                    $filename = basename($compressedPath);
                }
            }
            
            // Crear metadata del respaldo
            $metadata = [
                'filename' => $filename,
                'type' => $type,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->user['id'],
                'size' => filesize($fullPath),
                'company_id' => $this->user['company_id']
            ];
            
            $this->saveBackupMetadata($metadata);
            
            // Limpiar respaldos antiguos
            $this->cleanOldBackups();
            
            return [
                'success' => true,
                'filename' => $filename,
                'size' => $metadata['size']
            ];
            
        } catch (Exception $e) {
            error_log("Error generando respaldo: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Crear dump de base de datos
     */
    private function createDatabaseDump($outputPath, $type)
    {
        try {
            $database = Database::getInstance();
            $connection = $database->getConnection();
            
            // Obtener configuración de la base de datos
            $dbConfig = require __DIR__ . '/../config/database_config.php';
            
            $content = "-- Respaldo Base de Datos HERCO\n";
            $content .= "-- Generado: " . date('Y-m-d H:i:s') . "\n";
            $content .= "-- Tipo: {$type}\n\n";
            
            $content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            
            // Obtener lista de tablas
            $tables = $this->getDatabaseTables($connection);
            
            foreach ($tables as $table) {
                // Filtrar tablas según el tipo de respaldo
                if (!$this->shouldIncludeTable($table, $type)) {
                    continue;
                }
                
                $content .= $this->dumpTable($connection, $table, $type);
            }
            
            $content .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            
            // Guardar archivo
            if (file_put_contents($outputPath, $content) === false) {
                throw new Exception('Error escribiendo archivo de respaldo');
            }
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error creando dump: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener tablas de la base de datos
     */
    private function getDatabaseTables($connection)
    {
        try {
            $stmt = $connection->query("SHOW TABLES");
            $tables = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            
            return $tables;
            
        } catch (Exception $e) {
            throw new Exception("Error obteniendo tablas: " . $e->getMessage());
        }
    }
    
    /**
     * Determinar si incluir tabla en el respaldo
     */
    private function shouldIncludeTable($table, $type)
    {
        // Tablas del sistema que siempre se incluyen
        $systemTables = [
            'users', 'companies', 'surveys', 'questions', 
            'question_categories', 'question_types'
        ];
        
        // Tablas de datos que se incluyen según el tipo
        $dataTables = [
            'participants', 'responses', 'activity_logs'
        ];
        
        switch ($type) {
            case 'structure':
                return in_array($table, $systemTables);
                
            case 'data':
                return in_array($table, $dataTables);
                
            case 'full':
            default:
                return true;
        }
    }
    
    /**
     * Hacer dump de una tabla específica
     */
    private function dumpTable($connection, $table, $type)
    {
        try {
            $content = "-- Tabla: {$table}\n";
            
            // Estructura de la tabla
            $stmt = $connection->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $content .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $content .= $row['Create Table'] . ";\n\n";
            
            // Datos de la tabla (si corresponde según el tipo)
            if ($type !== 'structure') {
                $stmt = $connection->query("SELECT * FROM `{$table}`");
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    
                    $content .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                }
                
                $content .= "\n";
            }
            
            return $content;
            
        } catch (Exception $e) {
            error_log("Error dumping tabla {$table}: " . $e->getMessage());
            return "-- Error dumping tabla {$table}: " . $e->getMessage() . "\n\n";
        }
    }
    
    /**
     * Comprimir respaldo
     */
    private function compressBackup($filePath)
    {
        if (!$this->compressionEnabled) {
            return null;
        }
        
        try {
            $zipPath = $filePath . '.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($filePath, basename($filePath));
                $zip->close();
                
                return $zipPath;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error comprimiendo respaldo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Realizar restauración (implementación básica)
     */
    private function performRestore($backupPath)
    {
        try {
            // ADVERTENCIA: Esta es una operación muy peligrosa
            // En producción debería tener múltiples confirmaciones
            
            if (!file_exists($backupPath)) {
                throw new Exception('Archivo de respaldo no existe');
            }
            
            // Leer contenido del respaldo
            $content = file_get_contents($backupPath);
            
            if ($content === false) {
                throw new Exception('Error leyendo archivo de respaldo');
            }
            
            // Si es un archivo comprimido, descomprimir primero
            if (pathinfo($backupPath, PATHINFO_EXTENSION) === 'zip') {
                // Implementación básica de descompresión
                $content = $this->extractBackupContent($backupPath);
            }
            
            // Ejecutar restauración
            $database = Database::getInstance();
            $connection = $database->getConnection();
            
            // Dividir en statements
            $statements = explode(';', $content);
            
            $connection->beginTransaction();
            
            try {
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $connection->exec($statement);
                    }
                }
                
                $connection->commit();
                return ['success' => true];
                
            } catch (Exception $e) {
                $connection->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error en restauración: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener lista de respaldos
     */
    private function getBackupList()
    {
        try {
            $backups = [];
            
            // Respaldos manuales
            $manualPath = $this->backupPath . 'manual/';
            if (is_dir($manualPath)) {
                $files = glob($manualPath . 'backup_*.{sql,zip}', GLOB_BRACE);
                foreach ($files as $file) {
                    $backups[] = $this->getBackupInfo($file, 'manual');
                }
            }
            
            // Respaldos automáticos
            $autoPath = $this->backupPath . 'automatic/';
            if (is_dir($autoPath)) {
                $files = glob($autoPath . 'backup_*.{sql,zip}', GLOB_BRACE);
                foreach ($files as $file) {
                    $backups[] = $this->getBackupInfo($file, 'automatic');
                }
            }
            
            // Ordenar por fecha (más recientes primero)
            usort($backups, function($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });
            
            return $backups;
            
        } catch (Exception $e) {
            error_log("Error obteniendo lista de respaldos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener información de un archivo de respaldo
     */
    private function getBackupInfo($filePath, $source)
    {
        $filename = basename($filePath);
        $filesize = filesize($filePath);
        $created = filemtime($filePath);
        
        // Extraer tipo del nombre del archivo
        $type = 'full';
        if (preg_match('/backup_(\w+)_/', $filename, $matches)) {
            $type = $matches[1];
        }
        
        return [
            'filename' => $filename,
            'type' => $type,
            'source' => $source,
            'size' => $filesize,
            'size_formatted' => $this->formatFileSize($filesize),
            'created_at' => date('Y-m-d H:i:s', $created),
            'age_days' => floor((time() - $created) / (24 * 60 * 60))
        ];
    }
    
    /**
     * Obtener estadísticas de respaldos
     */
    private function getBackupStats()
    {
        try {
            $stats = [
                'total_backups' => 0,
                'manual_backups' => 0,
                'automatic_backups' => 0,
                'total_size' => 0,
                'oldest_backup' => null,
                'newest_backup' => null
            ];
            
            $backups = $this->getBackupList();
            $stats['total_backups'] = count($backups);
            
            foreach ($backups as $backup) {
                if ($backup['source'] === 'manual') {
                    $stats['manual_backups']++;
                } else {
                    $stats['automatic_backups']++;
                }
                
                $stats['total_size'] += $backup['size'];
                
                if (!$stats['oldest_backup'] || $backup['created_at'] < $stats['oldest_backup']) {
                    $stats['oldest_backup'] = $backup['created_at'];
                }
                
                if (!$stats['newest_backup'] || $backup['created_at'] > $stats['newest_backup']) {
                    $stats['newest_backup'] = $backup['created_at'];
                }
            }
            
            $stats['total_size_formatted'] = $this->formatFileSize($stats['total_size']);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'total_backups' => 0,
                'manual_backups' => 0,
                'automatic_backups' => 0,
                'total_size' => 0,
                'total_size_formatted' => '0 B',
                'oldest_backup' => null,
                'newest_backup' => null
            ];
        }
    }
    
    /**
     * Crear directorios de respaldo
     */
    private function createBackupDirectories()
    {
        $directories = [
            $this->backupPath,
            $this->backupPath . 'manual/',
            $this->backupPath . 'automatic/',
            $this->backupPath . 'config/'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        // Crear archivo .htaccess para proteger el directorio
        $htaccessPath = $this->backupPath . '.htaccess';
        if (!file_exists($htaccessPath)) {
            file_put_contents($htaccessPath, "Deny from all\n");
        }
    }
    
    /**
     * Validar nombre de archivo de respaldo
     */
    private function isValidBackupFilename($filename)
    {
        // Solo permitir nombres de archivo seguros
        return preg_match('/^backup_[a-zA-Z0-9_-]+\.(sql|zip)$/', $filename);
    }
    
    /**
     * Formatear tamaño de archivo
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Obtener configuración de respaldos
     */
    private function getBackupConfig()
    {
        $configPath = $this->backupPath . 'config/backup_config.json';
        
        $defaultConfig = [
            'auto_backup_enabled' => true,
            'backup_frequency' => 'daily',
            'retention_days' => 30,
            'compression_enabled' => $this->compressionEnabled,
            'email_notifications' => false,
            'notification_email' => ''
        ];
        
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            return array_merge($defaultConfig, $config ?: []);
        }
        
        return $defaultConfig;
    }
    
    /**
     * Guardar configuración de respaldos
     */
    private function saveBackupConfig($config)
    {
        $configPath = $this->backupPath . 'config/backup_config.json';
        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
    }
    
    /**
     * Guardar metadata de respaldo
     */
    private function saveBackupMetadata($metadata)
    {
        $metadataPath = $this->backupPath . 'config/metadata.json';
        
        $allMetadata = [];
        if (file_exists($metadataPath)) {
            $allMetadata = json_decode(file_get_contents($metadataPath), true) ?: [];
        }
        
        $allMetadata[] = $metadata;
        
        file_put_contents($metadataPath, json_encode($allMetadata, JSON_PRETTY_PRINT));
    }
    
    /**
     * Limpiar respaldos antiguos
     */
    private function cleanOldBackups()
    {
        try {
            $config = $this->getBackupConfig();
            $retentionDays = $config['retention_days'];
            $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
            
            // Limpiar respaldos automáticos antiguos
            $autoPath = $this->backupPath . 'automatic/';
            if (is_dir($autoPath)) {
                $files = glob($autoPath . 'backup_*');
                foreach ($files as $file) {
                    if (filemtime($file) < $cutoffTime) {
                        unlink($file);
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Error limpiando respaldos antiguos: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener tipos de respaldo
     */
    private function getBackupTypes()
    {
        return [
            'full' => 'Completo (Estructura + Datos)',
            'structure' => 'Solo Estructura',
            'data' => 'Solo Datos'
        ];
    }
    
    /**
     * Obtener frecuencias de respaldo
     */
    private function getBackupFrequencies()
    {
        return [
            'daily' => 'Diario',
            'weekly' => 'Semanal',
            'monthly' => 'Mensual'
        ];
    }
    
    /**
     * Obtener opciones de retención
     */
    private function getRetentionOptions()
    {
        return [
            '7' => '7 días',
            '15' => '15 días',
            '30' => '30 días',
            '60' => '60 días',
            '90' => '90 días'
        ];
    }
    
    /**
     * Logging de actividad
     */
    private function logActivity($action, $data = [])
    {
        try {
            if (class_exists('ActivityLog')) {
                $activityLog = new ActivityLog();
                $activityLog->create([
                    'user_id' => $this->user['id'],
                    'action' => $action,
                    'data' => json_encode($data),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (Exception $e) {
            error_log("Error logging actividad: " . $e->getMessage());
        }
    }
}
?>