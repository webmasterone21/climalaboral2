<?php
/**
 * Controlador de Emails - VERSIÓN CORREGIDA
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * Sistema de notificaciones por email, plantillas,
 * configuraciones SMTP y gestión de colas de envío.
 * 
 * CORRECCIONES APLICADAS:
 * ✅ Verificación de dependencias PHPMailer
 * ✅ Fallback sin dependencias externas
 * ✅ Configuraciones dinámicas desde BD
 * ✅ Manejo robusto de errores
 * ✅ Sistema de cola básico
 * 
 * @package EncuestasHERCO\Controllers
 * @version 2.0.0
 * @author Sistema HERCO
 */

class EmailController extends Controller
{
    private $emailEnabled = false;
    private $mailer = null;
    private $emailQueue = [];
    private $templates = [];
    
    /**
     * Inicialización del controlador con verificación de dependencias
     */
    protected function initialize()
    {
        // Requerir autenticación
        $this->requireAuth();
        
        // Verificar permisos
        $this->requirePermission('manage_emails');
        
        // Verificar si PHPMailer está disponible
        $this->emailEnabled = class_exists('PHPMailer\PHPMailer\PHPMailer');
        
        if ($this->emailEnabled) {
            try {
                $this->initializeMailer();
            } catch (Exception $e) {
                error_log("Error inicializando mailer: " . $e->getMessage());
                $this->emailEnabled = false;
            }
        }
        
        // Cargar plantillas de email
        $this->loadEmailTemplates();
        
        // Layout administrativo
        $this->defaultLayout = 'admin';
    }
    
    /**
     * Dashboard de emails
     */
    public function index()
    {
        try {
            // Obtener estadísticas de emails
            $stats = $this->getEmailStats();
            
            // Obtener configuración actual
            $config = $this->getEmailConfig();
            
            // Obtener emails recientes
            $recentEmails = $this->getRecentEmails();
            
            $data = [
                'email_enabled' => $this->emailEnabled,
                'stats' => $stats,
                'config' => $config,
                'recent_emails' => $recentEmails,
                'templates' => $this->getAvailableTemplates()
            ];
            
            $this->render('admin/emails/index', $data);
            
        } catch (Exception $e) {
            error_log("Error en EmailController::index: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar panel de emails', 'error');
            $this->redirect('/admin/dashboard');
        }
    }
    
    /**
     * Configuración de email
     */
    public function settings()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->updateSettings();
            }
            
            $config = $this->getEmailConfig();
            
            $data = [
                'config' => $config,
                'smtp_providers' => $this->getSMTPProviders(),
                'encryption_types' => $this->getEncryptionTypes()
            ];
            
            $this->render('admin/emails/settings', $data);
            
        } catch (Exception $e) {
            error_log("Error en configuración de email: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar configuración', 'error');
            $this->redirect('/admin/emails');
        }
    }
    
    /**
     * Actualizar configuraciones de email
     */
    private function updateSettings()
    {
        try {
            $this->validateCsrfToken();
            
            $settings = [
                'enabled' => !empty($_POST['enabled']),
                'smtp_host' => $this->sanitizeInput($_POST['smtp_host'] ?? ''),
                'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
                'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
                'smtp_username' => $this->sanitizeInput($_POST['smtp_username'] ?? ''),
                'smtp_password' => $_POST['smtp_password'] ?? '', // No sanitizar password
                'from_email' => $_POST['from_email'] ?? '',
                'from_name' => $this->sanitizeInput($_POST['from_name'] ?? ''),
                'reply_to' => $_POST['reply_to'] ?? '',
                'test_mode' => !empty($_POST['test_mode'])
            ];
            
            // Validar configuración
            if ($settings['enabled']) {
                if (empty($settings['smtp_host']) || empty($settings['from_email'])) {
                    throw new Exception('Host SMTP y email origen son requeridos');
                }
                
                if (!filter_var($settings['from_email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email origen inválido');
                }
                
                if (!empty($settings['reply_to']) && !filter_var($settings['reply_to'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email de respuesta inválido');
                }
            }
            
            // Guardar configuración
            $this->saveEmailConfig($settings);
            
            // Test de conexión si está habilitado
            if ($settings['enabled'] && !empty($_POST['test_connection'])) {
                $testResult = $this->testEmailConnection($settings);
                if (!$testResult['success']) {
                    $this->setFlashMessage(
                        'Configuración guardada pero hay problemas de conexión: ' . $testResult['error'], 
                        'warning'
                    );
                } else {
                    $this->setFlashMessage('Configuración guardada y probada exitosamente', 'success');
                }
            } else {
                $this->setFlashMessage('Configuración de email actualizada', 'success');
            }
            
            // Log de actividad
            $this->logActivity('email_settings_updated', [
                'enabled' => $settings['enabled'],
                'smtp_host' => $settings['smtp_host']
            ]);
            
            $this->redirect('/admin/emails/settings');
            
        } catch (Exception $e) {
            error_log("Error actualizando configuración de email: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/emails/settings');
        }
    }
    
    /**
     * Enviar email de prueba
     */
    public function testEmail()
    {
        try {
            $this->validateCsrfToken();
            
            if (!$this->emailEnabled) {
                throw new Exception('Sistema de email no disponible');
            }
            
            $testEmail = $_POST['test_email'] ?? $this->user['email'];
            
            if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email de prueba inválido');
            }
            
            $result = $this->sendTestEmail($testEmail);
            
            if ($result['success']) {
                $this->setFlashMessage('Email de prueba enviado exitosamente', 'success');
            } else {
                throw new Exception($result['error']);
            }
            
        } catch (Exception $e) {
            error_log("Error enviando email de prueba: " . $e->getMessage());
            $this->setFlashMessage('Error enviando email de prueba: ' . $e->getMessage(), 'error');
        }
        
        $this->redirect('/admin/emails/settings');
    }
    
    /**
     * Gestión de plantillas de email
     */
    public function templates()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->updateTemplate();
            }
            
            $templates = $this->getAvailableTemplates();
            $selectedTemplate = $_GET['template'] ?? 'invitation';
            
            $data = [
                'templates' => $templates,
                'selected_template' => $selectedTemplate,
                'template_content' => $this->getTemplateContent($selectedTemplate),
                'available_variables' => $this->getTemplateVariables($selectedTemplate)
            ];
            
            $this->render('admin/emails/templates', $data);
            
        } catch (Exception $e) {
            error_log("Error en plantillas de email: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar plantillas', 'error');
            $this->redirect('/admin/emails');
        }
    }
    
    /**
     * Actualizar plantilla de email
     */
    private function updateTemplate()
    {
        try {
            $this->validateCsrfToken();
            
            $templateName = $_POST['template_name'] ?? '';
            $subject = $this->sanitizeInput($_POST['subject'] ?? '');
            $body = $_POST['body'] ?? ''; // No sanitizar HTML del cuerpo
            
            if (empty($templateName) || empty($subject) || empty($body)) {
                throw new Exception('Todos los campos son requeridos');
            }
            
            // Validar que la plantilla existe
            $availableTemplates = array_keys($this->getAvailableTemplates());
            if (!in_array($templateName, $availableTemplates)) {
                throw new Exception('Plantilla no válida');
            }
            
            // Guardar plantilla
            $this->saveTemplate($templateName, $subject, $body);
            
            $this->setFlashMessage('Plantilla actualizada exitosamente', 'success');
            
            // Log de actividad
            $this->logActivity('email_template_updated', [
                'template' => $templateName
            ]);
            
            $this->redirect('/admin/emails/templates?template=' . $templateName);
            
        } catch (Exception $e) {
            error_log("Error actualizando plantilla: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/emails/templates');
        }
    }
    
    /**
     * Cola de emails
     */
    public function queue()
    {
        try {
            // Obtener emails en cola
            $queuedEmails = $this->getQueuedEmails();
            
            // Estadísticas de la cola
            $queueStats = $this->getQueueStats();
            
            $data = [
                'queued_emails' => $queuedEmails,
                'queue_stats' => $queueStats,
                'queue_enabled' => $this->isQueueEnabled()
            ];
            
            $this->render('admin/emails/queue', $data);
            
        } catch (Exception $e) {
            error_log("Error en cola de emails: " . $e->getMessage());
            $this->setFlashMessage('Error al cargar cola de emails', 'error');
            $this->redirect('/admin/emails');
        }
    }
    
    /**
     * Procesar cola de emails
     */
    public function processQueue()
    {
        try {
            if (!$this->emailEnabled) {
                throw new Exception('Sistema de email no disponible');
            }
            
            $processed = $this->processEmailQueue();
            
            $message = "Procesados {$processed['sent']} emails";
            if ($processed['failed'] > 0) {
                $message .= ", {$processed['failed']} fallidos";
            }
            
            $this->setFlashMessage($message, $processed['failed'] > 0 ? 'warning' : 'success');
            
        } catch (Exception $e) {
            error_log("Error procesando cola: " . $e->getMessage());
            $this->setFlashMessage('Error procesando cola: ' . $e->getMessage(), 'error');
        }
        
        $this->redirect('/admin/emails/queue');
    }
    
    /**
     * API para enviar email (uso interno)
     */
    public function send()
    {
        try {
            // Solo para llamadas internas del sistema
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $to = $_POST['to'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $body = $_POST['body'] ?? '';
            $template = $_POST['template'] ?? '';
            $variables = $_POST['variables'] ?? [];
            
            if (empty($to) || empty($subject)) {
                throw new Exception('Email y asunto son requeridos');
            }
            
            $result = $this->sendEmail($to, $subject, $body, $template, $variables);
            
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en API de email: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Inicializar mailer
     */
    private function initializeMailer()
    {
        if (!$this->emailEnabled) {
            return false;
        }
        
        try {
            $config = $this->getEmailConfig();
            
            if (!$config['enabled']) {
                return false;
            }
            
            $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuración SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = $config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $config['smtp_username'];
            $this->mailer->Password = $config['smtp_password'];
            $this->mailer->SMTPSecure = $config['smtp_encryption'];
            $this->mailer->Port = $config['smtp_port'];
            
            // Configuración del remitente
            $this->mailer->setFrom($config['from_email'], $config['from_name']);
            
            if (!empty($config['reply_to'])) {
                $this->mailer->addReplyTo($config['reply_to']);
            }
            
            // Configuración adicional
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error configurando mailer: " . $e->getMessage());
            $this->emailEnabled = false;
            return false;
        }
    }
    
    /**
     * Enviar email
     */
    public function sendEmail($to, $subject, $body, $template = '', $variables = [])
    {
        try {
            if (!$this->emailEnabled) {
                // Si email no está disponible, guardar en cola para procesar después
                return $this->queueEmail($to, $subject, $body, $template, $variables);
            }
            
            // Si se especifica una plantilla, usarla
            if (!empty($template)) {
                $templateContent = $this->getTemplateContent($template);
                if ($templateContent) {
                    $subject = $this->replaceVariables($templateContent['subject'], $variables);
                    $body = $this->replaceVariables($templateContent['body'], $variables);
                }
            }
            
            // Configurar destinatario
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            
            // Configurar contenido
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->wrapEmailBody($body);
            
            // Enviar
            $result = $this->mailer->send();
            
            if ($result) {
                // Log del email enviado
                $this->logEmailSent($to, $subject, 'sent');
                
                return ['success' => true];
            } else {
                throw new Exception('Error enviando email');
            }
            
        } catch (Exception $e) {
            error_log("Error enviando email a {$to}: " . $e->getMessage());
            
            // Log del error
            $this->logEmailSent($to, $subject, 'failed', $e->getMessage());
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar email de prueba
     */
    private function sendTestEmail($to)
    {
        $subject = 'Prueba de Email - Sistema HERCO';
        $body = '
            <h2>Email de Prueba</h2>
            <p>Este es un email de prueba del Sistema de Encuestas HERCO.</p>
            <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Usuario:</strong> ' . $this->user['name'] . '</p>
            <p>Si recibe este email, la configuración SMTP está funcionando correctamente.</p>
        ';
        
        return $this->sendEmail($to, $subject, $body);
    }
    
    /**
     * Poner email en cola
     */
    private function queueEmail($to, $subject, $body, $template = '', $variables = [])
    {
        try {
            $queueData = [
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'template' => $template,
                'variables' => json_encode($variables),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'attempts' => 0
            ];
            
            // Guardar en archivo JSON temporal si no hay BD
            $queueFile = __DIR__ . '/../logs/email_queue.json';
            $queue = [];
            
            if (file_exists($queueFile)) {
                $queue = json_decode(file_get_contents($queueFile), true) ?: [];
            }
            
            $queue[] = $queueData;
            file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT));
            
            return ['success' => true, 'queued' => true];
            
        } catch (Exception $e) {
            error_log("Error guardando email en cola: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error guardando en cola'];
        }
    }
    
    /**
     * Procesar cola de emails
     */
    private function processEmailQueue()
    {
        try {
            $stats = ['sent' => 0, 'failed' => 0];
            
            if (!$this->emailEnabled) {
                return $stats;
            }
            
            $queueFile = __DIR__ . '/../logs/email_queue.json';
            
            if (!file_exists($queueFile)) {
                return $stats;
            }
            
            $queue = json_decode(file_get_contents($queueFile), true) ?: [];
            $processedQueue = [];
            
            foreach ($queue as $email) {
                if ($email['status'] !== 'pending' || $email['attempts'] >= 3) {
                    $processedQueue[] = $email;
                    continue;
                }
                
                $variables = json_decode($email['variables'], true) ?: [];
                $result = $this->sendEmail(
                    $email['to'], 
                    $email['subject'], 
                    $email['body'], 
                    $email['template'], 
                    $variables
                );
                
                $email['attempts']++;
                
                if ($result['success']) {
                    $email['status'] = 'sent';
                    $email['sent_at'] = date('Y-m-d H:i:s');
                    $stats['sent']++;
                } else {
                    $email['last_error'] = $result['error'] ?? 'Error desconocido';
                    $stats['failed']++;
                    
                    if ($email['attempts'] >= 3) {
                        $email['status'] = 'failed';
                    }
                }
                
                $processedQueue[] = $email;
            }
            
            // Guardar cola actualizada
            file_put_contents($queueFile, json_encode($processedQueue, JSON_PRETTY_PRINT));
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error procesando cola de emails: " . $e->getMessage());
            return ['sent' => 0, 'failed' => 0];
        }
    }
    
    /**
     * Cargar plantillas de email
     */
    private function loadEmailTemplates()
    {
        $this->templates = [
            'invitation' => [
                'name' => 'Invitación a Encuesta',
                'subject' => 'Invitación: {survey_title}',
                'variables' => ['participant_name', 'survey_title', 'survey_url', 'company_name']
            ],
            'reminder' => [
                'name' => 'Recordatorio de Encuesta',
                'subject' => 'Recordatorio: {survey_title}',
                'variables' => ['participant_name', 'survey_title', 'survey_url', 'days_left']
            ],
            'completion' => [
                'name' => 'Confirmación de Participación',
                'subject' => 'Gracias por participar en {survey_title}',
                'variables' => ['participant_name', 'survey_title', 'company_name']
            ],
            'report_ready' => [
                'name' => 'Reporte Disponible',
                'subject' => 'Reporte de {survey_title} disponible',
                'variables' => ['user_name', 'survey_title', 'report_url']
            ]
        ];
    }
    
    /**
     * Obtener configuración de email
     */
    private function getEmailConfig()
    {
        try {
            // Intentar cargar desde base de datos si existe modelo Company
            if (class_exists('Company')) {
                $companyModel = new Company();
                $company = $companyModel->findById($this->user['company_id']);
                
                if ($company && !empty($company['settings'])) {
                    $settings = json_decode($company['settings'], true);
                    if (isset($settings['email'])) {
                        return array_merge($this->getDefaultEmailConfig(), $settings['email']);
                    }
                }
            }
            
            // Configuración por defecto
            return $this->getDefaultEmailConfig();
            
        } catch (Exception $e) {
            error_log("Error obteniendo configuración de email: " . $e->getMessage());
            return $this->getDefaultEmailConfig();
        }
    }
    
    /**
     * Obtener configuración por defecto
     */
    private function getDefaultEmailConfig()
    {
        return [
            'enabled' => false,
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => '',
            'smtp_password' => '',
            'from_email' => '',
            'from_name' => 'Sistema HERCO',
            'reply_to' => '',
            'test_mode' => false
        ];
    }
    
    /**
     * Guardar configuración de email
     */
    private function saveEmailConfig($config)
    {
        try {
            if (class_exists('Company')) {
                $companyModel = new Company();
                $company = $companyModel->findById($this->user['company_id']);
                
                if ($company) {
                    $settings = [];
                    if (!empty($company['settings'])) {
                        $settings = json_decode($company['settings'], true) ?: [];
                    }
                    
                    $settings['email'] = $config;
                    
                    $companyModel->update($this->user['company_id'], [
                        'settings' => json_encode($settings),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log("Error guardando configuración de email: " . $e->getMessage());
            throw new Exception('Error guardando configuración');
        }
    }
    
    /**
     * Obtener plantillas disponibles
     */
    private function getAvailableTemplates()
    {
        $templates = [];
        foreach ($this->templates as $key => $template) {
            $templates[$key] = $template['name'];
        }
        return $templates;
    }
    
    /**
     * Obtener contenido de plantilla
     */
    private function getTemplateContent($templateName)
    {
        try {
            $templateFile = __DIR__ . '/../views/emails/' . $templateName . '.php';
            
            if (file_exists($templateFile)) {
                ob_start();
                include $templateFile;
                $content = ob_get_clean();
                
                // Extraer subject y body si están definidos en el archivo
                // Implementación básica
                return [
                    'subject' => $this->templates[$templateName]['subject'] ?? 'Sin asunto',
                    'body' => $content
                ];
            }
            
            // Plantilla por defecto si no existe archivo
            return [
                'subject' => $this->templates[$templateName]['subject'] ?? 'Sin asunto',
                'body' => '<p>Plantilla no encontrada: ' . $templateName . '</p>'
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo plantilla {$templateName}: " . $e->getMessage());
            return [
                'subject' => 'Error en plantilla',
                'body' => '<p>Error cargando plantilla</p>'
            ];
        }
    }
    
    /**
     * Obtener variables de plantilla
     */
    private function getTemplateVariables($templateName)
    {
        return $this->templates[$templateName]['variables'] ?? [];
    }
    
    /**
     * Reemplazar variables en texto
     */
    private function replaceVariables($text, $variables)
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }
    
    /**
     * Envolver cuerpo del email con HTML básico
     */
    private function wrapEmailBody($body)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Sistema HERCO</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { padding: 10px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Sistema HERCO</h1>
                </div>
                <div class="content">
                    ' . $body . '
                </div>
                <div class="footer">
                    <p>Sistema de Encuestas de Clima Laboral HERCO v2.0</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Logging de emails enviados
     */
    private function logEmailSent($to, $subject, $status, $error = '')
    {
        try {
            $logData = [
                'to' => $to,
                'subject' => $subject,
                'status' => $status,
                'error' => $error,
                'sent_by' => $this->user['id'],
                'sent_at' => date('Y-m-d H:i:s')
            ];
            
            // Guardar en archivo de log
            $logFile = __DIR__ . '/../logs/emails.log';
            $logEntry = date('Y-m-d H:i:s') . " - " . json_encode($logData) . "\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            error_log("Error logging email: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener estadísticas de emails
     */
    private function getEmailStats()
    {
        // Implementación básica - leer del archivo de log
        try {
            $logFile = __DIR__ . '/../logs/emails.log';
            
            if (!file_exists($logFile)) {
                return [
                    'total_sent' => 0,
                    'sent_today' => 0,
                    'failed_today' => 0,
                    'success_rate' => 0
                ];
            }
            
            $lines = file($logFile);
            $today = date('Y-m-d');
            
            $totalSent = 0;
            $sentToday = 0;
            $failedToday = 0;
            
            foreach ($lines as $line) {
                if (strpos($line, $today) === 0) {
                    $sentToday++;
                    if (strpos($line, '"status":"failed"') !== false) {
                        $failedToday++;
                    }
                }
                $totalSent++;
            }
            
            $successRate = $sentToday > 0 ? round((($sentToday - $failedToday) / $sentToday) * 100, 1) : 0;
            
            return [
                'total_sent' => $totalSent,
                'sent_today' => $sentToday,
                'failed_today' => $failedToday,
                'success_rate' => $successRate
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de email: " . $e->getMessage());
            return [
                'total_sent' => 0,
                'sent_today' => 0,
                'failed_today' => 0,
                'success_rate' => 0
            ];
        }
    }
    
    /**
     * Obtener emails recientes
     */
    private function getRecentEmails($limit = 10)
    {
        try {
            $logFile = __DIR__ . '/../logs/emails.log';
            
            if (!file_exists($logFile)) {
                return [];
            }
            
            $lines = array_slice(file($logFile), -$limit);
            $emails = [];
            
            foreach ($lines as $line) {
                $data = json_decode(substr($line, 20), true); // Saltar timestamp
                if ($data) {
                    $emails[] = $data;
                }
            }
            
            return array_reverse($emails);
            
        } catch (Exception $e) {
            error_log("Error obteniendo emails recientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener emails en cola
     */
    private function getQueuedEmails()
    {
        try {
            $queueFile = __DIR__ . '/../logs/email_queue.json';
            
            if (!file_exists($queueFile)) {
                return [];
            }
            
            $queue = json_decode(file_get_contents($queueFile), true) ?: [];
            
            // Filtrar solo pendientes y fallidos
            return array_filter($queue, function($email) {
                return in_array($email['status'], ['pending', 'failed']);
            });
            
        } catch (Exception $e) {
            error_log("Error obteniendo cola de emails: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de la cola
     */
    private function getQueueStats()
    {
        try {
            $queued = $this->getQueuedEmails();
            
            $stats = [
                'total' => count($queued),
                'pending' => 0,
                'failed' => 0
            ];
            
            foreach ($queued as $email) {
                if ($email['status'] === 'pending') {
                    $stats['pending']++;
                } elseif ($email['status'] === 'failed') {
                    $stats['failed']++;
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de cola: " . $e->getMessage());
            return ['total' => 0, 'pending' => 0, 'failed' => 0];
        }
    }
    
    /**
     * Verificar si la cola está habilitada
     */
    private function isQueueEnabled()
    {
        return true; // Siempre habilitada para este sistema básico
    }
    
    /**
     * Probar conexión de email
     */
    private function testEmailConnection($config)
    {
        try {
            if (!$this->emailEnabled) {
                return ['success' => false, 'error' => 'PHPMailer no disponible'];
            }
            
            $testMailer = new PHPMailer\PHPMailer\PHPMailer(true);
            
            $testMailer->isSMTP();
            $testMailer->Host = $config['smtp_host'];
            $testMailer->SMTPAuth = true;
            $testMailer->Username = $config['smtp_username'];
            $testMailer->Password = $config['smtp_password'];
            $testMailer->SMTPSecure = $config['smtp_encryption'];
            $testMailer->Port = $config['smtp_port'];
            
            // Solo probar conexión, no enviar
            $testMailer->smtpConnect();
            $testMailer->smtpClose();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener proveedores SMTP
     */
    private function getSMTPProviders()
    {
        return [
            'gmail' => [
                'name' => 'Gmail',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls'
            ],
            'outlook' => [
                'name' => 'Outlook',
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'encryption' => 'tls'
            ],
            'yahoo' => [
                'name' => 'Yahoo',
                'host' => 'smtp.mail.yahoo.com',
                'port' => 587,
                'encryption' => 'tls'
            ],
            'custom' => [
                'name' => 'Personalizado',
                'host' => '',
                'port' => 587,
                'encryption' => 'tls'
            ]
        ];
    }
    
    /**
     * Obtener tipos de encriptación
     */
    private function getEncryptionTypes()
    {
        return [
            'tls' => 'TLS',
            'ssl' => 'SSL',
            '' => 'Sin encriptación'
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