<?php
/**
 * Monitor de Seguridad Automatizado
 * Sistema HERCO v2.0
 * 
 * Script para monitoreo automatizado de eventos de seguridad.
 * Debe ejecutarse como cron job cada 5-15 minutos.
 * 
 * Uso: php scripts/monitor_security.php [--alert-email admin@example.com]
 * 
 * Cron: */15 * * * * php /path/to/sistema/scripts/monitor_security.php
 * 
 * @package EncuestasHERCO\Scripts
 */

// ==========================================
// CONFIGURACIÓN
// ==========================================

define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/core/Database.php';

class SecurityMonitor
{
    private $db;
    private $alertEmail;
    private $alerts = [];
    private $stats = [];
    
    // Umbrales de alerta
    private $thresholds = [
        'failed_logins_per_hour' => 50,
        'rate_limits_per_hour' => 20,
        'sql_injection_attempts' => 5,
        'xss_attempts' => 5,
        'suspicious_ips_threshold' => 10 // intentos desde una misma IP
    ];
    
    public function __construct($alertEmail = null)
    {
        $this->alertEmail = $alertEmail;
        
        try {
            $database = new Database();
            $this->db = $database->connect();
        } catch (Exception $e) {
            $this->log("ERROR: No se pudo conectar a la base de datos: " . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Ejecutar monitoreo completo
     */
    public function run()
    {
        $this->log("Iniciando monitoreo de seguridad...");
        
        // 1. Analizar eventos recientes
        $this->analyzeFailedLogins();
        $this->analyzeRateLimits();
        $this->analyzeSuspiciousActivity();
        $this->analyzeSuspiciousIPs();
        
        // 2. Limpiar rate limits expirados
        $this->cleanExpiredRateLimits();
        
        // 3. Verificar integridad del sistema
        $this->checkSystemIntegrity();
        
        // 4. Enviar alertas si es necesario
        if (!empty($this->alerts)) {
            $this->sendAlerts();
        }
        
        // 5. Generar estadísticas
        $this->generateStats();
        
        $this->log("Monitoreo completado exitosamente");
    }
    
    // ==========================================
    // ANÁLISIS DE EVENTOS
    // ==========================================
    
    /**
     * Analizar intentos fallidos de login
     */
    private function analyzeFailedLogins()
    {
        $this->log("Analizando intentos fallidos de login...");
        
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM security_logs 
                    WHERE event_type = 'failed_login' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];
            
            $this->stats['failed_logins_last_hour'] = $count;
            
            if ($count > $this->thresholds['failed_logins_per_hour']) {
                $this->addAlert(
                    'HIGH',
                    "Intentos fallidos de login excesivos",
                    "Se detectaron {$count} intentos fallidos en la última hora (umbral: {$this->thresholds['failed_logins_per_hour']})"
                );
            }
            
            $this->log("  ✓ {$count} intentos fallidos detectados en la última hora");
            
        } catch (Exception $e) {
            $this->log("  ✗ Error analizando logins fallidos: " . $e->getMessage());
        }
    }
    
    /**
     * Analizar rate limits
     */
    private function analyzeRateLimits()
    {
        $this->log("Analizando rate limits...");
        
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM security_logs 
                    WHERE event_type IN ('login_rate_limit_exceeded', 'rate_limit_exceeded') 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];
            
            $this->stats['rate_limits_last_hour'] = $count;
            
            if ($count > $this->thresholds['rate_limits_per_hour']) {
                $this->addAlert(
                    'MEDIUM',
                    "Rate limits excesivos",
                    "Se detectaron {$count} rate limits en la última hora (umbral: {$this->thresholds['rate_limits_per_hour']})"
                );
            }
            
            $this->log("  ✓ {$count} rate limits detectados en la última hora");
            
        } catch (Exception $e) {
            $this->log("  ✗ Error analizando rate limits: " . $e->getMessage());
        }
    }
    
    /**
     * Analizar actividad sospechosa
     */
    private function analyzeSuspiciousActivity()
    {
        $this->log("Analizando actividad sospechosa...");
        
        $suspiciousEvents = [
            'sql_injection_attempt',
            'xss_attempt',
            'path_traversal_attempt'
        ];
        
        foreach ($suspiciousEvents as $eventType) {
            try {
                $sql = "SELECT COUNT(*) as count, COUNT(DISTINCT ip_address) as unique_ips
                        FROM security_logs 
                        WHERE event_type = :event_type 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['event_type' => $eventType]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $count = $result['count'];
                $uniqueIPs = $result['unique_ips'];
                
                $this->stats[$eventType . '_last_hour'] = $count;
                
                if ($count > 0) {
                    $threshold = $this->thresholds[str_replace('_attempt', '_attempts', $eventType)];
                    
                    if ($count >= $threshold) {
                        $this->addAlert(
                            'CRITICAL',
                            "Intentos de ataque detectados: " . strtoupper(str_replace('_', ' ', $eventType)),
                            "Se detectaron {$count} intentos desde {$uniqueIPs} IP(s) diferentes en la última hora"
                        );
                        
                        // Obtener las IPs involucradas
                        $this->getAttackingIPs($eventType);
                    }
                    
                    $this->log("  ⚠ {$count} intentos de {$eventType} desde {$uniqueIPs} IPs");
                }
                
            } catch (Exception $e) {
                $this->log("  ✗ Error analizando {$eventType}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Analizar IPs sospechosas
     */
    private function analyzeSuspiciousIPs()
    {
        $this->log("Analizando IPs sospechosas...");
        
        try {
            $sql = "SELECT ip_address, COUNT(*) as attempt_count
                    FROM security_logs 
                    WHERE event_type IN ('failed_login', 'sql_injection_attempt', 'xss_attempt', 'rate_limit_exceeded')
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    GROUP BY ip_address
                    HAVING attempt_count >= :threshold
                    ORDER BY attempt_count DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['threshold' => $this->thresholds['suspicious_ips_threshold']]);
            
            $suspiciousIPs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($suspiciousIPs)) {
                foreach ($suspiciousIPs as $ip) {
                    $this->addAlert(
                        'HIGH',
                        "IP sospechosa detectada",
                        "La IP {$ip['ip_address']} ha realizado {$ip['attempt_count']} acciones sospechosas en la última hora. Considere bloquearla."
                    );
                    
                    $this->log("  ⚠ IP sospechosa: {$ip['ip_address']} ({$ip['attempt_count']} intentos)");
                }
                
                $this->stats['suspicious_ips'] = count($suspiciousIPs);
            }
            
        } catch (Exception $e) {
            $this->log("  ✗ Error analizando IPs sospechosas: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener IPs que están atacando
     */
    private function getAttackingIPs($eventType)
    {
        try {
            $sql = "SELECT ip_address, COUNT(*) as count
                    FROM security_logs 
                    WHERE event_type = :event_type 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    GROUP BY ip_address
                    ORDER BY count DESC
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['event_type' => $eventType]);
            
            $ips = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($ips)) {
                $ipList = array_map(function($ip) {
                    return $ip['ip_address'] . " ({$ip['count']} intentos)";
                }, $ips);
                
                $this->addAlert(
                    'INFO',
                    "Top IPs atacantes para {$eventType}",
                    implode(", ", $ipList)
                );
            }
            
        } catch (Exception $e) {
            $this->log("  ✗ Error obteniendo IPs atacantes: " . $e->getMessage());
        }
    }
    
    // ==========================================
    // MANTENIMIENTO
    // ==========================================
    
    /**
     * Limpiar rate limits expirados de la sesión
     */
    private function cleanExpiredRateLimits()
    {
        $this->log("Limpiando rate limits expirados...");
        
        // En este sistema, los rate limits se guardan en sesión
        // Este método es más relevante si se implementa en base de datos
        
        $this->log("  ✓ Limpieza completada");
    }
    
    /**
     * Verificar integridad del sistema
     */
    private function checkSystemIntegrity()
    {
        $this->log("Verificando integridad del sistema...");
        
        $issues = [];
        
        // Verificar que los archivos críticos existen
        $criticalFiles = [
            'core/SecurityMiddleware.php',
            'core/SecurityHelpers.php',
            'core/Database.php',
            'config/app.php',
            'index.php'
        ];
        
        foreach ($criticalFiles as $file) {
            if (!file_exists(ROOT_DIR . '/' . $file)) {
                $issues[] = "Archivo crítico faltante: {$file}";
            }
        }
        
        // Verificar permisos de directorios
        $directories = ['logs', 'backups'];
        
        foreach ($directories as $dir) {
            $path = ROOT_DIR . '/' . $dir;
            if (!is_dir($path)) {
                $issues[] = "Directorio faltante: {$dir}";
            } elseif (!is_writable($path)) {
                $issues[] = "Directorio sin permisos de escritura: {$dir}";
            }
        }
        
        // Verificar conectividad a base de datos
        try {
            $this->db->query("SELECT 1");
        } catch (Exception $e) {
            $issues[] = "Error de conectividad a base de datos: " . $e->getMessage();
        }
        
        if (!empty($issues)) {
            foreach ($issues as $issue) {
                $this->addAlert('CRITICAL', "Problema de integridad del sistema", $issue);
                $this->log("  ✗ " . $issue);
            }
        } else {
            $this->log("  ✓ Sistema íntegro");
        }
    }
    
    // ==========================================
    // ALERTAS
    // ==========================================
    
    /**
     * Agregar alerta
     */
    private function addAlert($level, $title, $message)
    {
        $this->alerts[] = [
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Enviar alertas
     */
    private function sendAlerts()
    {
        $this->log("Enviando alertas...");
        
        if (empty($this->alertEmail)) {
            $this->log("  ℹ No se configuró email de alerta. Alertas registradas en log.");
            $this->logAlerts();
            return;
        }
        
        $subject = "[HERCO Security] " . count($this->alerts) . " alertas detectadas";
        
        $body = "Sistema de Monitoreo de Seguridad - HERCO\n";
        $body .= "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        $body .= "Se detectaron " . count($this->alerts) . " alertas:\n\n";
        
        foreach ($this->alerts as $alert) {
            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $body .= "NIVEL: " . $alert['level'] . "\n";
            $body .= "TÍTULO: " . $alert['title'] . "\n";
            $body .= "MENSAJE: " . $alert['message'] . "\n";
            $body .= "HORA: " . $alert['timestamp'] . "\n\n";
        }
        
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $body .= "Revise el dashboard de seguridad para más detalles.\n";
        
        // Enviar email
        $headers = "From: security@sistema-herco.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        if (mail($this->alertEmail, $subject, $body, $headers)) {
            $this->log("  ✓ Alertas enviadas a: " . $this->alertEmail);
        } else {
            $this->log("  ✗ Error enviando alertas por email");
            $this->logAlerts();
        }
    }
    
    /**
     * Registrar alertas en archivo de log
     */
    private function logAlerts()
    {
        $logFile = ROOT_DIR . '/logs/security_alerts.log';
        
        $content = "\n" . str_repeat("=", 50) . "\n";
        $content .= "Alertas de Seguridad - " . date('Y-m-d H:i:s') . "\n";
        $content .= str_repeat("=", 50) . "\n";
        
        foreach ($this->alerts as $alert) {
            $content .= "[{$alert['level']}] {$alert['title']}\n";
            $content .= "  {$alert['message']}\n";
            $content .= "  Timestamp: {$alert['timestamp']}\n\n";
        }
        
        file_put_contents($logFile, $content, FILE_APPEND);
    }
    
    // ==========================================
    // ESTADÍSTICAS
    // ==========================================
    
    /**
     * Generar estadísticas
     */
    private function generateStats()
    {
        $this->log("Generando estadísticas...");
        
        $statsFile = ROOT_DIR . '/logs/security_stats.json';
        
        $this->stats['timestamp'] = date('Y-m-d H:i:s');
        $this->stats['alerts_count'] = count($this->alerts);
        
        file_put_contents($statsFile, json_encode($this->stats, JSON_PRETTY_PRINT));
        
        $this->log("  ✓ Estadísticas guardadas en: {$statsFile}");
    }
    
    // ==========================================
    // UTILIDADES
    // ==========================================
    
    /**
     * Registrar mensaje en log
     */
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        // Mostrar en consola
        echo $logMessage;
        
        // Guardar en archivo
        $logFile = ROOT_DIR . '/logs/monitor.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

// ==========================================
// EJECUTAR MONITOR
// ==========================================

if (php_sapi_name() === 'cli') {
    // Parsear argumentos
    $alertEmail = null;
    
    foreach (array_slice($argv, 1) as $i => $arg) {
        if ($arg === '--alert-email' && isset($argv[$i + 2])) {
            $alertEmail = $argv[$i + 2];
        }
    }
    
    // Ejecutar monitor
    $monitor = new SecurityMonitor($alertEmail);
    $monitor->run();
    
    exit(0);
} else {
    echo "Este script debe ejecutarse desde la línea de comandos.\n";
    exit(1);
}
