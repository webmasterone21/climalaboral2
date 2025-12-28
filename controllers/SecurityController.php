<?php
/**
 * SecurityController - Controlador del Dashboard de Seguridad
 * Sistema HERCO v2.0
 * 
 * Maneja todas las operaciones del dashboard de seguridad:
 * - Visualización de eventos
 * - Bloqueo/desbloqueo de IPs
 * - Exportación de reportes
 * - Análisis de actividad sospechosa
 * 
 * @package EncuestasHERCO\Controllers
 */

require_once 'core/Controller.php';
require_once 'core/Database.php';

class SecurityController extends Controller
{
  // ✅ NO declarar $db aquí - se hereda de Controller

 
    
    public function __construct()
    {
        parent::__construct();
        
        // Requiere rol de administrador para todo el controlador
        require_role('admin');
        
        $database = new Database();
        $this->db = $database->connect();
    }
    
    // ==========================================
    // DASHBOARD PRINCIPAL
    // ==========================================
    
    /**
     * Mostrar dashboard de seguridad
     */
    public function dashboard()
    {
        $data = [
            'securityLogs' => $this->getRecentSecurityEvents(50),
            'stats' => $this->getSecurityStats(),
            'failedLogins' => $this->getRecentFailedLogins(20),
            'suspiciousActivity' => $this->getSuspiciousActivity(),
            'blockedIPs' => $this->getBlockedIPs()
        ];
        
        $this->view('admin/security/dashboard', $data);
    }
    
    // ==========================================
    // OBTENCIÓN DE DATOS
    // ==========================================
    
    /**
     * Obtener eventos de seguridad recientes
     */
    private function getRecentSecurityEvents($limit = 50)
    {
        try {
            $sql = "SELECT * FROM security_logs 
                    ORDER BY created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo eventos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de seguridad
     */
    private function getSecurityStats()
    {
        $stats = [
            'failed_logins_24h' => $this->countEventsByType('failed_login', 24),
            'rate_limits_24h' => $this->countEventsByType('login_rate_limit_exceeded', 24),
            'total_events_24h' => $this->countTotalEvents(24),
            'successful_logins_24h' => $this->countEventsByType('successful_login', 24),
            'failed_logins_change' => $this->getPercentageChange('failed_login', 24),
            'events_by_day' => $this->getEventsGroupedByDay(7),
            'events_by_type' => $this->getEventsGroupedByType(7)
        ];
        
        return $stats;
    }
    
    /**
     * Contar eventos por tipo en las últimas X horas
     */
    private function countEventsByType($eventType, $hours = 24)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM security_logs 
                    WHERE event_type = :event_type 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'event_type' => $eventType,
                'hours' => $hours
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Contar eventos totales en las últimas X horas
     */
    private function countTotalEvents($hours = 24)
    {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM security_logs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['hours' => $hours]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Calcular cambio porcentual respecto al día anterior
     */
    private function getPercentageChange($eventType, $hours = 24)
    {
        $current = $this->countEventsByType($eventType, $hours);
        
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM security_logs 
                    WHERE event_type = :event_type 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :hours_end HOUR)
                    AND created_at < DATE_SUB(NOW(), INTERVAL :hours_start HOUR)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'event_type' => $eventType,
                'hours_start' => $hours,
                'hours_end' => $hours * 2
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $previous = $result['count'] ?? 0;
            
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }
            
            return round((($current - $previous) / $previous) * 100, 1);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obtener eventos agrupados por día
     */
    private function getEventsGroupedByDay($days = 7)
    {
        try {
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                    FROM security_logs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['days' => $days]);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $labels = [];
            $data = [];
            
            foreach ($results as $row) {
                $labels[] = date('d/m', strtotime($row['date']));
                $data[] = $row['count'];
            }
            
            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (Exception $e) {
            return ['labels' => [], 'data' => []];
        }
    }
    
    /**
     * Obtener eventos agrupados por tipo
     */
    private function getEventsGroupedByType($days = 7)
    {
        try {
            $sql = "SELECT event_type, COUNT(*) as count 
                    FROM security_logs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                    GROUP BY event_type
                    ORDER BY count DESC
                    LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['days' => $days]);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $labels = [];
            $data = [];
            
            foreach ($results as $row) {
                $labels[] = $this->getEventTitle($row['event_type']);
                $data[] = $row['count'];
            }
            
            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (Exception $e) {
            return ['labels' => [], 'data' => []];
        }
    }
    
    /**
     * Obtener intentos de login fallidos recientes
     */
    private function getRecentFailedLogins($limit = 20)
    {
        try {
            $sql = "SELECT * FROM security_logs 
                    WHERE event_type IN ('failed_login', 'login_rate_limit_exceeded')
                    ORDER BY created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener actividad sospechosa
     */
    private function getSuspiciousActivity()
    {
        try {
            $sql = "SELECT * FROM security_logs 
                    WHERE event_type IN (
                        'sql_injection_attempt',
                        'xss_attempt',
                        'path_traversal_attempt',
                        'csrf_token_invalid'
                    )
                    ORDER BY created_at DESC 
                    LIMIT 20";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener IPs bloqueadas
     */
    private function getBlockedIPs()
    {
        try {
            // Verificar si la tabla existe
            $checkTable = "SHOW TABLES LIKE 'blocked_ips'";
            $stmt = $this->db->query($checkTable);
            
            if ($stmt->rowCount() === 0) {
                // Crear tabla si no existe
                $this->createBlockedIPsTable();
            }
            
            $sql = "SELECT * FROM blocked_ips 
                    WHERE is_active = 1 
                    ORDER BY blocked_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo IPs bloqueadas: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // ACCIONES DE BLOQUEO
    // ==========================================
    
    /**
     * Bloquear una IP
     */
    public function blockIP()
    {
        header('Content-Type: application/json');
        
        // Leer JSON del body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!isset($data['ip'])) {
            echo json_encode([
                'success' => false,
                'message' => 'IP no proporcionada'
            ]);
            exit;
        }
        
        $ip = sanitize_input($data['ip']);
        $reason = $data['reason'] ?? 'Bloqueado manualmente desde dashboard';
        
        try {
            // Verificar si ya está bloqueada
            $checkSql = "SELECT id FROM blocked_ips WHERE ip_address = :ip AND is_active = 1";
            $stmt = $this->db->prepare($checkSql);
            $stmt->execute(['ip' => $ip]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La IP ya está bloqueada'
                ]);
                exit;
            }
            
            // Insertar bloqueo
            $insertSql = "INSERT INTO blocked_ips (ip_address, reason, blocked_by, blocked_at, is_active) 
                         VALUES (:ip, :reason, :user_id, NOW(), 1)";
            
            $stmt = $this->db->prepare($insertSql);
            $stmt->execute([
                'ip' => $ip,
                'reason' => $reason,
                'user_id' => current_user_id()
            ]);
            
            // Registrar evento
            log_security_event('ip_blocked', [
                'ip' => $ip,
                'reason' => $reason,
                'blocked_by' => current_user_id()
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => "IP {$ip} bloqueada exitosamente"
            ]);
            
        } catch (Exception $e) {
            error_log("Error bloqueando IP: " . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al bloquear la IP'
            ]);
        }
    }
    
    /**
     * Desbloquear una IP
     */
    public function unblockIP()
    {
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!isset($data['ip'])) {
            echo json_encode([
                'success' => false,
                'message' => 'IP no proporcionada'
            ]);
            exit;
        }
        
        $ip = sanitize_input($data['ip']);
        
        try {
            $sql = "UPDATE blocked_ips 
                    SET is_active = 0, 
                        unblocked_by = :user_id,
                        unblocked_at = NOW()
                    WHERE ip_address = :ip 
                    AND is_active = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ip' => $ip,
                'user_id' => current_user_id()
            ]);
            
            if ($stmt->rowCount() > 0) {
                log_security_event('ip_unblocked', [
                    'ip' => $ip,
                    'unblocked_by' => current_user_id()
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => "IP {$ip} desbloqueada exitosamente"
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'IP no encontrada o ya estaba desbloqueada'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error desbloqueando IP: " . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al desbloquear la IP'
            ]);
        }
    }
    
    /**
     * Verificar si una IP está bloqueada
     */
    public function isIPBlocked($ip)
    {
        try {
            $sql = "SELECT id FROM blocked_ips 
                    WHERE ip_address = :ip 
                    AND is_active = 1 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['ip' => $ip]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // ==========================================
    // EXPORTACIÓN DE REPORTES
    // ==========================================
    
    /**
     * Exportar reporte de seguridad
     */
    public function exportReport()
    {
        $format = $this->get('format', 'pdf'); // pdf, excel, csv
        
        $data = [
            'stats' => $this->getSecurityStats(),
            'events' => $this->getRecentSecurityEvents(1000),
            'failedLogins' => $this->getRecentFailedLogins(100),
            'suspicious' => $this->getSuspiciousActivity(),
            'blockedIPs' => $this->getBlockedIPs()
        ];
        
        switch ($format) {
            case 'excel':
                $this->exportToExcel($data);
                break;
            case 'csv':
                $this->exportToCSV($data);
                break;
            case 'pdf':
            default:
                $this->exportToPDF($data);
                break;
        }
    }
    
    /**
     * Exportar a PDF
     */
    private function exportToPDF($data)
    {
        // Requiere TCPDF o similar
        require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';
        
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        
        $pdf->SetCreator('Sistema HERCO');
        $pdf->SetAuthor('Sistema HERCO');
        $pdf->SetTitle('Reporte de Seguridad');
        $pdf->SetSubject('Reporte de Seguridad');
        
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        
        $pdf->AddPage();
        
        $html = $this->generateReportHTML($data);
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $filename = 'security_report_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        
        exit;
    }
    
    /**
     * Exportar a Excel
     */
    private function exportToExcel($data)
    {
        // Requiere PhpSpreadsheet
        require_once 'vendor/autoload.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'Reporte de Seguridad - Sistema HERCO');
        $sheet->mergeCells('A1:E1');
        
        // Estadísticas
        $sheet->setCellValue('A3', 'Estadísticas (24 horas)');
        $sheet->setCellValue('A4', 'Logins Fallidos:');
        $sheet->setCellValue('B4', $data['stats']['failed_logins_24h']);
        $sheet->setCellValue('A5', 'Rate Limits:');
        $sheet->setCellValue('B5', $data['stats']['rate_limits_24h']);
        $sheet->setCellValue('A6', 'Total Eventos:');
        $sheet->setCellValue('B6', $data['stats']['total_events_24h']);
        
        // Eventos recientes
        $row = 8;
        $sheet->setCellValue('A' . $row, 'Eventos Recientes');
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Fecha/Hora');
        $sheet->setCellValue('B' . $row, 'Tipo');
        $sheet->setCellValue('C' . $row, 'IP');
        $sheet->setCellValue('D' . $row, 'Usuario');
        $row++;
        
        foreach ($data['events'] as $event) {
            $sheet->setCellValue('A' . $row, $event['created_at']);
            $sheet->setCellValue('B' . $row, $event['event_type']);
            $sheet->setCellValue('C' . $row, $event['ip_address']);
            $sheet->setCellValue('D' . $row, $event['user_id'] ?? 'N/A');
            $row++;
            
            if ($row > 1000) break; // Limitar a 1000 filas
        }
        
        $filename = 'security_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        
        exit;
    }
    
    /**
     * Exportar a CSV
     */
    private function exportToCSV($data)
    {
        $filename = 'security_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, ['Fecha/Hora', 'Tipo', 'IP', 'Usuario', 'Datos']);
        
        // Datos
        foreach ($data['events'] as $event) {
            fputcsv($output, [
                $event['created_at'],
                $event['event_type'],
                $event['ip_address'],
                $event['user_id'] ?? 'N/A',
                $event['event_data'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Generar HTML para el reporte
     */
    private function generateReportHTML($data)
    {
        $html = '<h1>Reporte de Seguridad - Sistema HERCO</h1>';
        $html .= '<p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>';
        
        $html .= '<h2>Estadísticas (24 horas)</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><td>Logins Fallidos:</td><td>' . $data['stats']['failed_logins_24h'] . '</td></tr>';
        $html .= '<tr><td>Rate Limits:</td><td>' . $data['stats']['rate_limits_24h'] . '</td></tr>';
        $html .= '<tr><td>Total Eventos:</td><td>' . $data['stats']['total_events_24h'] . '</td></tr>';
        $html .= '<tr><td>Logins Exitosos:</td><td>' . $data['stats']['successful_logins_24h'] . '</td></tr>';
        $html .= '</table>';
        
        $html .= '<h2>Eventos Recientes</h2>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><th>Fecha/Hora</th><th>Tipo</th><th>IP</th></tr>';
        
        foreach (array_slice($data['events'], 0, 50) as $event) {
            $html .= '<tr>';
            $html .= '<td>' . $event['created_at'] . '</td>';
            $html .= '<td>' . $event['event_type'] . '</td>';
            $html .= '<td>' . $event['ip_address'] . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        return $html;
    }
    
    // ==========================================
    // UTILIDADES
    // ==========================================
    
    /**
     * Crear tabla de IPs bloqueadas si no existe
     */
    private function createBlockedIPsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS blocked_ips (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            reason TEXT,
            blocked_by INT,
            blocked_at DATETIME NOT NULL,
            unblocked_by INT NULL,
            unblocked_at DATETIME NULL,
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_ip (ip_address),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->exec($sql);
    }
    
    /**
     * Obtener título amigable para un evento
     */
    private function getEventTitle($eventType)
    {
        $titles = [
            'successful_login' => 'Login Exitoso',
            'failed_login' => 'Login Fallido',
            'user_logout' => 'Logout',
            'csrf_token_invalid' => 'CSRF Inválido',
            'sql_injection_attempt' => 'SQL Injection',
            'xss_attempt' => 'XSS',
            'path_traversal_attempt' => 'Path Traversal',
            'rate_limit_exceeded' => 'Rate Limit',
            'login_rate_limit_exceeded' => 'Login Rate Limit',
            'ip_blocked' => 'IP Bloqueada',
            'ip_unblocked' => 'IP Desbloqueada'
        ];
        
        return $titles[$eventType] ?? ucfirst(str_replace('_', ' ', $eventType));
    }
    
    /**
     * Limpiar logs antiguos (ejecutar periódicamente)
     */
    public function cleanOldLogs()
    {
        require_role('admin');
        
        $days = $this->get('days', 90); // Por defecto, eliminar logs mayores a 90 días
        
        try {
            $sql = "DELETE FROM security_logs 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['days' => $days]);
            
            $deleted = $stmt->rowCount();
            
            log_security_event('logs_cleaned', [
                'days' => $days,
                'deleted_count' => $deleted
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => "Se eliminaron {$deleted} registros de logs antiguos"
            ]);
            
        } catch (Exception $e) {
            error_log("Error limpiando logs: " . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al limpiar logs'
            ]);
        }
    }
}
