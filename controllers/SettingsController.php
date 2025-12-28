<?php
/**
 * SettingsController - Configuraciones del Sistema
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * @version 2.0.0
 */

class SettingsController extends Controller
{
    /**
     * Inicialización del controlador
     */
    protected function initialize()
    {
        // Requerir autenticación y permisos de administrador
        $this->requireAuth();
        
        if (!$this->isAdmin()) {
            $this->setFlashMessage('No tiene permisos para acceder a configuraciones', 'error');
            $this->redirect('/admin/dashboard');
        }
    }
    
    /**
     * Mostrar página de configuraciones
     */
    public function index()
    {
        try {
            // Obtener configuraciones actuales del sistema
            $settings = $this->getCurrentSettings();
            
            $data = [
                'page_title' => 'Configuraciones del Sistema',
                'settings' => $settings,
                'csrf_token' => $this->generateCSRFToken()
            ];
            
            $this->render('admin/settings/index', $data, 'admin');
            
        } catch (Exception $e) {
            error_log("Error en SettingsController::index: " . $e->getMessage());
            
            // Mostrar vista con configuraciones por defecto
            $this->render('admin/settings/index', [
                'page_title' => 'Configuraciones del Sistema',
                'settings' => $this->getDefaultSettings(),
                'csrf_token' => $this->generateCSRFToken(),
                'error_message' => 'Error al cargar algunas configuraciones.'
            ], 'admin');
        }
    }
    
    /**
     * Actualizar configuraciones
     */
    public function update()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }
        
        // Validar token CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCSRFToken($token)) {
            $this->setFlashMessage('Token de seguridad inválido', 'error');
            $this->redirect('/admin/settings');
            return;
        }
        
        try {
            // Aquí irían las actualizaciones de configuración
            // Por ahora, solo un placeholder
            
            $this->setFlashMessage('Configuraciones actualizadas correctamente', 'success');
            $this->redirect('/admin/settings');
            
        } catch (Exception $e) {
            error_log("Error actualizando configuraciones: " . $e->getMessage());
            $this->setFlashMessage('Error al actualizar configuraciones', 'error');
            $this->redirect('/admin/settings');
        }
    }
    
    /**
     * Obtener configuraciones actuales
     * 
     * @return array
     */
    private function getCurrentSettings()
    {
        // Intentar cargar de BD o archivo de configuración
        $settings = [];
        
        try {
            // Intentar cargar desde archivo de configuración
            if (file_exists(__DIR__ . '/../config/app.php')) {
                $appConfig = require __DIR__ . '/../config/app.php';
                
                $settings = [
                    'app_name' => $appConfig['app_name'] ?? 'Sistema HERCO',
                    'app_version' => $appConfig['app_version'] ?? '2.0.0',
                    'app_environment' => $appConfig['app_environment'] ?? 'production',
                    'timezone' => $appConfig['timezone'] ?? 'America/Tegucigalpa',
                    'default_language' => $appConfig['default_language'] ?? 'es'
                ];
            } else {
                $settings = $this->getDefaultSettings();
            }
            
        } catch (Exception $e) {
            error_log("Error cargando configuraciones: " . $e->getMessage());
            $settings = $this->getDefaultSettings();
        }
        
        return $settings;
    }
    
    /**
     * Obtener configuraciones por defecto
     * 
     * @return array
     */
    private function getDefaultSettings()
    {
        return [
            'app_name' => 'Sistema HERCO',
            'app_version' => '2.0.0',
            'app_environment' => 'production',
            'timezone' => 'America/Tegucigalpa',
            'default_language' => 'es',
            'session_lifetime' => 7200,
            'upload_max_size' => 5242880,
            'password_min_length' => 8
        ];
    }
    
    /**
     * Configuración general
     */
    public function general()
    {
        $this->render('admin/settings/general', [
            'page_title' => 'Configuración General'
        ], 'admin');
    }
    
    /**
     * Configuración de seguridad
     */
    public function security()
    {
        $this->render('admin/settings/security', [
            'page_title' => 'Configuración de Seguridad'
        ], 'admin');
    }
    
    /**
     * Configuración de correo
     */
    public function email()
    {
        $this->render('admin/settings/email', [
            'page_title' => 'Configuración de Correo'
        ], 'admin');
    }
    
    /**
     * Información del sistema
     */
    public function system()
    {
        try {
            $systemInfo = [
                'php_version' => phpversion(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
                'database_version' => $this->getDatabaseVersion(),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ];
            
            $this->render('admin/settings/system', [
                'page_title' => 'Información del Sistema',
                'system_info' => $systemInfo
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Error obteniendo info del sistema: " . $e->getMessage());
            
            $this->render('admin/settings/system', [
                'page_title' => 'Información del Sistema',
                'system_info' => [],
                'error_message' => 'No se pudo obtener la información del sistema.'
            ], 'admin');
        }
    }
    
    /**
     * Obtener versión de la base de datos
     * 
     * @return string
     */
    private function getDatabaseVersion()
    {
        try {
            $result = $this->db->fetch('SELECT VERSION() as version');
            return $result['version'] ?? 'N/A';
        } catch (Exception $e) {
            return 'N/A';
        }
    }
}
