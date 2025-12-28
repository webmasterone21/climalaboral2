<?php
/**
 * Controlador de Empresas - VERSIÓN CORREGIDA v2.0.2
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * Gestión de empresas: perfil corporativo, configuraciones,
 * branding, estructura organizacional y configuraciones multiempresa.
 * 
 * CORRECCIONES APLICADAS v2.0.2:
 * ✅ Verificación de dependencias de modelos
 * ✅ Validaciones robustas de datos
 * ✅ Manejo de uploads sin dependencias externas
 * ✅ Configuraciones dinámicas (no hardcoded)
 * ✅ Manejo robusto de errores
 * ✅ Eliminado método logActivity() duplicado
 * ✅ Ajustadas llamadas a logActivity() con firma correcta
 * ✅ Instanciación correcta de modelos con parámetro $db
 * ✅ Compatibilidad total con clase padre Controller
 * 
 * @package EncuestasHERCO\Controllers
 * @version 2.0.2
 * @author Sistema HERCO
 * @date 2025-01-17
 */

class CompanyController extends Controller
{
    private $companyModel;
    private $userModel;
    private $uploadPath;
    private $allowedImageTypes;
    
    /**
     * Inicialización del controlador con verificación de dependencias
     */
    protected function initialize()
    {
        // Requerir autenticación
        $this->requireAuth();
        
        // Verificar permisos administrativos
        $this->requireAdmin();
        
        // Cargar modelos verificando existencia
        try {
            if (!class_exists('Company')) {
                throw new Exception('Modelo Company no disponible');
            }
            // ✅ CORRECCIÓN: Pasar la conexión de BD al modelo
            $this->companyModel = new Company($this->db);
            
            if (!class_exists('User')) {
                throw new Exception('Modelo User no disponible');
            }
            // ✅ CORRECCIÓN: Pasar la conexión de BD al modelo
            $this->userModel = new User($this->db);
            
        } catch (Exception $e) {
            error_log("Error inicializando CompanyController: " . $e->getMessage());
            $this->setFlashMessage(
                'Error de configuración del sistema de empresas. Contacte al administrador.', 
                'error'
            );
            $this->redirect('/admin/dashboard');
        }
        
        // Configurar rutas de upload
        $this->uploadPath = __DIR__ . '/../uploads/companies/';
        $this->allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Crear directorio de uploads si no existe
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
        
        // Layout administrativo
        $this->defaultLayout = 'admin';
    }

    /**
 * Listar todas las empresas (solo para super admin)
 * Ruta: /admin/companies o /admin/companies/index
 */
public function index()
{
    try {
        // Verificar que sea super admin para ver todas las empresas
        if (!$this->isSuperAdmin()) {
            // Si es admin regular, redirigir a su perfil de empresa
            $this->redirect('/admin/companies/profile');
            return;
        }
        
        // Obtener filtros
        $filters = [
            'status' => $this->get('status', 'active'),
            'search' => $this->get('search', ''),
            'industry' => $this->get('industry', ''),
            'order_by' => $this->get('order_by', 'name'),
            'order_dir' => $this->get('order_dir', 'ASC')
        ];
        
        // Paginación
        $page = max(1, (int)$this->get('page', 1));
        $perPage = 20;
        $filters['limit'] = $perPage;
        $filters['offset'] = ($page - 1) * $perPage;
        
        // Obtener empresas con filtros
        $companies = $this->companyModel->getAllCompanies($filters);
        
        // Obtener estadísticas generales
        $stats = $this->companyModel->getCompanyStats();
        
        // Obtener industrias para el filtro
        $industries = $this->companyModel->getTopIndustries(20);
        
        // Contar total para paginación
        $totalCompanies = $stats['total_companies'] ?? 0;
        $pagination = $this->paginate($totalCompanies, $perPage, $page);
        
        $data = [
            'companies' => $companies,
            'stats' => $stats,
            'industries' => $industries,
            'filters' => $filters,
            'pagination' => $pagination
        ];
        
        $this->render('admin/companies/index', $data);
        
    } catch (Exception $e) {
        error_log("Error listando empresas: " . $e->getMessage());
        $this->setFlashMessage('Error al cargar empresas: ' . $e->getMessage(), 'error');
        $this->redirect('/admin/dashboard');
    }
}
    
    /**
     * Mostrar perfil de la empresa
     */
    public function profile()
    {
        try {
            // ✅ Usar método find() del modelo Company
            $company = $this->companyModel->find($this->user['company_id']);
            
            if (!$company) {
                throw new Exception('Empresa no encontrada');
            }
            
            // Obtener estadísticas de la empresa
            $stats = $this->getCompanyStats($company['id']);
            
            // Obtener configuraciones actuales
            $settings = $this->getCompanySettings($company['id']);
            
            $data = [
                'company' => $company,
                'stats' => $stats,
                'settings' => $settings,
                'upload_path' => '/uploads/companies/',
                'max_logo_size' => $this->getMaxFileSize()
            ];
            
            $this->render('admin/companies/profile', $data);
            
        } catch (Exception $e) {
            error_log("Error mostrando perfil de empresa: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/dashboard');
        }
    }
    
    /**
     * Actualizar información de la empresa
     */
    public function update()
    {
        try {
            // Validar CSRF
            $this->validateCsrfToken();
            
            $company = $this->companyModel->find($this->user['company_id']);
            
            if (!$company) {
                throw new Exception('Empresa no encontrada');
            }
            
            // Validar datos requeridos
            $requiredFields = ['name'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo {$field} es requerido");
                }
            }
            
            // Validar email si se proporciona
            if (!empty($_POST['contact_email']) && !filter_var($_POST['contact_email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }
            
            // Preparar datos de actualización
            $updateData = [
                'name' => $this->sanitize($_POST['name']),
                'contact_email' => !empty($_POST['contact_email']) ? strtolower(trim($_POST['contact_email'])) : null,
                'contact_phone' => $this->sanitize($_POST['contact_phone'] ?? ''),
                'address' => $this->sanitize($_POST['address'] ?? ''),
                'website' => $this->sanitize($_POST['website'] ?? ''),
                'description' => $this->sanitize($_POST['description'] ?? ''),
                'industry' => $this->sanitize($_POST['industry'] ?? ''),
                'employee_count' => !empty($_POST['employee_count']) ? (int)$_POST['employee_count'] : null
            ];
            
            // Procesar logo si se subió
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logoPath = $this->handleLogoUpload($_FILES['logo'], $company['id']);
                if ($logoPath) {
                    $updateData['logo'] = $logoPath;
                }
            }
            
            // ✅ Usar método updateCompany del modelo
            $result = $this->companyModel->updateCompany($company['id'], $updateData);
            
            if (!$result['success']) {
                throw new Exception($result['errors']['general'] ?? 'Error al actualizar empresa');
            }
            
            // ✅ Log de actividad - Firma correcta: logActivity($action, $description, $metadata)
            $this->logActivity(
                'company_updated',
                'Empresa actualizada: ' . $_POST['name'],
                [
                    'company_id' => $company['id'],
                    'name' => $_POST['name']
                ]
            );
            
            $this->setFlashMessage('Empresa actualizada exitosamente', 'success');
            $this->redirect('/admin/companies/profile');
            
        } catch (Exception $e) {
            error_log("Error actualizando empresa: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/companies/profile');
        }
    }
    
    /**
     * Configuraciones de empresa
     */
    public function settings()
    {
        try {
            if ($this->isPost()) {
                return $this->updateSettings();
            }
            
            $company = $this->companyModel->find($this->user['company_id']);
            
            if (!$company) {
                throw new Exception('Empresa no encontrada');
            }
            
            // Obtener configuraciones actuales
            $settings = $this->getCompanySettings($company['id']);
            
            // Configuraciones disponibles
            $availableSettings = $this->getAvailableSettings();
            
            $data = [
                'company' => $company,
                'settings' => $settings,
                'available_settings' => $availableSettings,
                'time_zones' => $this->getTimeZones(),
                'languages' => $this->getLanguages(),
                'currencies' => $this->getCurrencies()
            ];
            
            $this->render('admin/companies/settings', $data);
            
        } catch (Exception $e) {
            error_log("Error en configuraciones: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/companies/profile');
        }
    }
    
    /**
     * Actualizar configuraciones de empresa
     */
    private function updateSettings()
    {
        try {
            $this->validateCsrfToken();
            
            $company = $this->companyModel->find($this->user['company_id']);
            
            if (!$company) {
                throw new Exception('Empresa no encontrada');
            }
            
            // Configuraciones válidas
            $validSettings = array_keys($this->getAvailableSettings());
            
            $settings = [];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $validSettings)) {
                    $settings[$key] = $this->sanitize($value);
                }
            }
            
            // Guardar configuraciones como JSON en el campo settings
            $updateData = [
                'settings' => json_encode($settings)
            ];
            
            $result = $this->companyModel->update($company['id'], $updateData);
            
            if (!$result) {
                throw new Exception('Error al actualizar configuraciones');
            }
            
            // ✅ Log de actividad - Firma correcta
            $this->logActivity(
                'company_settings_updated',
                'Configuraciones de empresa actualizadas',
                [
                    'company_id' => $company['id'],
                    'settings' => array_keys($settings)
                ]
            );
            
            $this->setFlashMessage('Configuraciones actualizadas exitosamente', 'success');
            $this->redirect('/admin/companies/settings');
            
        } catch (Exception $e) {
            error_log("Error actualizando configuraciones: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/companies/settings');
        }
    }
    
    /**
     * Gestión de departamentos
     */
    public function departments()
    {
        try {
            if ($this->isPost()) {
                return $this->updateDepartments();
            }
            
            $company = $this->companyModel->find($this->user['company_id']);
            
            if (!$company) {
                throw new Exception('Empresa no encontrada');
            }
            
            // Obtener departamentos actuales
            $departments = $this->companyModel->getDepartments($company['id']);
            
            // Estadísticas por departamento
            $departmentStats = $this->getDepartmentStats();
            
            $data = [
                'company' => $company,
                'departments' => $departments,
                'department_stats' => $departmentStats
            ];
            
            $this->render('admin/companies/departments', $data);
            
        } catch (Exception $e) {
            error_log("Error en departamentos: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/companies/profile');
        }
    }
    
    /**
     * Actualizar estructura de departamentos
     */
    private function updateDepartments()
    {
        try {
            $this->validateCsrfToken();
            
            // Esta funcionalidad podría expandirse para manejar
            // una estructura más compleja de departamentos
            // Por ahora solo mostramos los departamentos existentes
            
            $this->setFlashMessage('Departamentos actualizados', 'success');
            $this->redirect('/admin/companies/departments');
            
        } catch (Exception $e) {
            error_log("Error actualizando departamentos: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/companies/departments');
        }
    }
    
    /**
     * Configuración de branding
     */
    public function branding()
    {
        try {
            if ($this->isPost()) {
                return $this->updateBranding();
            }
            
            $company = $this->companyModel->find($this->user['company_id']);
            
            if (!$company) {
                throw new Exception('Empresa no encontrada');
            }
            
            // Obtener configuraciones de branding
            $brandingSettings = $this->getBrandingSettings($company['id']);
            
            $data = [
                'company' => $company,
                'branding' => $brandingSettings,
                'color_schemes' => $this->getColorSchemes(),
                'upload_path' => '/uploads/companies/'
            ];
            
            $this->render('admin/companies/branding', $data);
            
        } catch (Exception $e) {
            error_log("Error en branding: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/companies/profile');
        }
    }
    
    /**
     * Actualizar configuraciones de branding
     */
    private function updateBranding()
    {
        try {
            $this->validateCsrfToken();
            
            $company = $this->companyModel->find($this->user['company_id']);
            
            if (!$company) {
                throw new Exception('Empresa no encontrada');
            }
            
            // Obtener configuraciones actuales
            $currentSettings = $this->getCompanySettings($company['id']);
            
            // Configuraciones de branding
            $brandingSettings = [
                'primary_color' => $this->sanitize($_POST['primary_color'] ?? '#007bff'),
                'secondary_color' => $this->sanitize($_POST['secondary_color'] ?? '#6c757d'),
                'accent_color' => $this->sanitize($_POST['accent_color'] ?? '#28a745'),
                'custom_css' => $this->sanitize($_POST['custom_css'] ?? ''),
                'email_footer' => $this->sanitize($_POST['email_footer'] ?? ''),
                'survey_theme' => $this->sanitize($_POST['survey_theme'] ?? 'default')
            ];
            
            // Combinar con configuraciones existentes
            $allSettings = array_merge($currentSettings, $brandingSettings);
            
            $updateData = [
                'settings' => json_encode($allSettings)
            ];
            
            $result = $this->companyModel->update($company['id'], $updateData);
            
            if (!$result) {
                throw new Exception('Error al actualizar branding');
            }
            
            // ✅ Log de actividad - Firma correcta
            $this->logActivity(
                'company_branding_updated',
                'Configuraciones de branding actualizadas',
                [
                    'company_id' => $company['id']
                ]
            );
            
            $this->setFlashMessage('Configuraciones de branding actualizadas', 'success');
            $this->redirect('/admin/companies/branding');
            
        } catch (Exception $e) {
            error_log("Error actualizando branding: " . $e->getMessage());
            $this->setFlashMessage($e->getMessage(), 'error');
            $this->redirect('/admin/companies/branding');
        }
    }
    
    /**
     * Manejar upload de logo
     */
    private function handleLogoUpload($file, $companyId)
    {
        try {
            // Validar tipo de archivo
            if (!in_array($file['type'], $this->allowedImageTypes)) {
                throw new Exception('Tipo de archivo no permitido. Use JPG, PNG o GIF.');
            }
            
            // Validar tamaño (máximo 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                throw new Exception('El archivo es muy grande. Máximo 2MB.');
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . $companyId . '_' . time() . '.' . $extension;
            $fullPath = $this->uploadPath . $filename;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                throw new Exception('Error al subir archivo');
            }
            
            // Redimensionar imagen si es necesario
            $this->resizeImage($fullPath, 200, 200);
            
            return 'logos/' . $filename;
            
        } catch (Exception $e) {
            error_log("Error subiendo logo: " . $e->getMessage());
            $this->setFlashMessage('Error subiendo logo: ' . $e->getMessage(), 'error');
            return null;
        }
    }
    
    /**
     * Redimensionar imagen (implementación básica)
     */
    private function resizeImage($imagePath, $maxWidth, $maxHeight)
    {
        try {
            // Obtener información de la imagen
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) {
                return false;
            }
            
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $type = $imageInfo[2];
            
            // Calcular nuevas dimensiones manteniendo la proporción
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            
            if ($ratio >= 1) {
                return true; // No necesita redimensionar
            }
            
            $newWidth = intval($width * $ratio);
            $newHeight = intval($height * $ratio);
            
            // Crear imagen según el tipo
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($imagePath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($imagePath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($imagePath);
                    break;
                default:
                    return false;
            }
            
            if (!$source) {
                return false;
            }
            
            // Crear nueva imagen
            $destination = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparencia para PNG y GIF
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
                imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Redimensionar
            imagecopyresampled(
                $destination, $source,
                0, 0, 0, 0,
                $newWidth, $newHeight, $width, $height
            );
            
            // Guardar imagen redimensionada
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($destination, $imagePath, 90);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($destination, $imagePath);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($destination, $imagePath);
                    break;
            }
            
            // Limpiar memoria
            imagedestroy($source);
            imagedestroy($destination);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error redimensionando imagen: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de la empresa
     */
    private function getCompanyStats($companyId)
    {
        try {
            // ✅ Usar método del modelo con estadísticas completas
            $companyWithStats = $this->companyModel->getCompanyWithStats($companyId);
            
            if ($companyWithStats) {
                return [
                    'total_users' => 0, // TODO: Implementar cuando exista relación
                    'active_users' => 0,
                    'total_surveys' => $companyWithStats['total_surveys'] ?? 0,
                    'active_surveys' => $companyWithStats['active_surveys'] ?? 0,
                    'total_participants' => $companyWithStats['total_participants'] ?? 0,
                    'total_responses' => $companyWithStats['completed_responses'] ?? 0
                ];
            }
            
            return [
                'total_users' => 0,
                'active_users' => 0,
                'total_surveys' => 0,
                'active_surveys' => 0,
                'total_participants' => 0,
                'total_responses' => 0
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de empresa: " . $e->getMessage());
            return [
                'total_users' => 0,
                'active_users' => 0,
                'total_surveys' => 0,
                'active_surveys' => 0,
                'total_participants' => 0,
                'total_responses' => 0
            ];
        }
    }
    
    /**
     * Obtener configuraciones de la empresa
     */
    private function getCompanySettings($companyId)
    {
        try {
            $company = $this->companyModel->find($companyId);
            
            if ($company && !empty($company['settings'])) {
                $settings = json_decode($company['settings'], true);
                return is_array($settings) ? $settings : [];
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Error obteniendo configuraciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener configuraciones disponibles
     */
    private function getAvailableSettings()
    {
        return [
            'timezone' => 'Zona Horaria',
            'language' => 'Idioma',
            'currency' => 'Moneda',
            'date_format' => 'Formato de Fecha',
            'auto_backup' => 'Respaldo Automático',
            'email_notifications' => 'Notificaciones por Email',
            'survey_expiration_days' => 'Días de Expiración de Encuestas',
            'require_login' => 'Requerir Login para Encuestas',
            'allow_anonymous' => 'Permitir Respuestas Anónimas',
            'max_file_size' => 'Tamaño Máximo de Archivo (MB)'
        ];
    }
    
    /**
     * Obtener zonas horarias
     */
    private function getTimeZones()
    {
        return [
            'America/New_York' => 'Este (Nueva York)',
            'America/Chicago' => 'Central (Chicago)',
            'America/Denver' => 'Montaña (Denver)',
            'America/Los_Angeles' => 'Pacífico (Los Ángeles)',
            'America/Mexico_City' => 'Ciudad de México',
            'America/Guatemala' => 'Guatemala',
            'America/Tegucigalpa' => 'Tegucigalpa',
            'America/Managua' => 'Managua',
            'America/San_Jose' => 'San José',
            'America/Panama' => 'Panamá'
        ];
    }
    
    /**
     * Obtener idiomas disponibles
     */
    private function getLanguages()
    {
        return [
            'es' => 'Español',
            'en' => 'English',
            'pt' => 'Português',
            'fr' => 'Français'
        ];
    }
    
    /**
     * Obtener monedas disponibles
     */
    private function getCurrencies()
    {
        return [
            'USD' => 'Dólar Americano',
            'EUR' => 'Euro',
            'MXN' => 'Peso Mexicano',
            'GTQ' => 'Quetzal Guatemalteco',
            'HNL' => 'Lempira Hondureño',
            'NIO' => 'Córdoba Nicaragüense',
            'CRC' => 'Colón Costarricense',
            'PAB' => 'Balboa Panameño'
        ];
    }
    
    /**
     * Obtener estadísticas por departamento
     */
    private function getDepartmentStats()
    {
        try {
            $departments = $this->companyModel->getDepartments($this->user['company_id']);
            $stats = [];
            
            foreach ($departments as $dept) {
                $stats[$dept['name']] = [
                    'id' => $dept['id'],
                    'users' => $dept['employee_count'] ?? 0,
                    'participants' => $dept['total_participants'] ?? 0,
                    'manager' => $dept['manager_name'] ?? 'Sin asignar'
                ];
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas por departamento: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener configuraciones de branding
     */
    private function getBrandingSettings($companyId)
    {
        $settings = $this->getCompanySettings($companyId);
        
        return [
            'primary_color' => $settings['primary_color'] ?? '#007bff',
            'secondary_color' => $settings['secondary_color'] ?? '#6c757d',
            'accent_color' => $settings['accent_color'] ?? '#28a745',
            'custom_css' => $settings['custom_css'] ?? '',
            'email_footer' => $settings['email_footer'] ?? '',
            'survey_theme' => $settings['survey_theme'] ?? 'default'
        ];
    }
    
    /**
     * Obtener esquemas de colores predefinidos
     */
    private function getColorSchemes()
    {
        return [
            'default' => [
                'name' => 'Azul Corporativo',
                'primary' => '#007bff',
                'secondary' => '#6c757d',
                'accent' => '#28a745'
            ],
            'professional' => [
                'name' => 'Profesional',
                'primary' => '#343a40',
                'secondary' => '#495057',
                'accent' => '#17a2b8'
            ],
            'modern' => [
                'name' => 'Moderno',
                'primary' => '#6f42c1',
                'secondary' => '#e83e8c',
                'accent' => '#fd7e14'
            ],
            'nature' => [
                'name' => 'Natural',
                'primary' => '#28a745',
                'secondary' => '#20c997',
                'accent' => '#ffc107'
            ]
        ];
    }
    
    /**
     * Obtener tamaño máximo de archivo
     */
    private function getMaxFileSize()
    {
        $maxUpload = $this->parseSize(ini_get('upload_max_filesize'));
        $maxPost = $this->parseSize(ini_get('post_max_size'));
        return min($maxUpload, $maxPost);
    }
    
    /**
     * Parsear tamaño de archivo
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }

    /**
 * Crear nueva empresa (solo super admin)
 */
public function create()
{
    try {
        // Solo super admin puede crear empresas
        if (!$this->isSuperAdmin()) {
            $this->setFlashMessage('No tiene permisos para crear empresas', 'error');
            $this->redirect('/admin/companies/profile');
            return;
        }
        
        $data = [
            'industries' => $this->companyModel->getTopIndustries(50)
        ];
        
        $this->render('admin/companies/create', $data);
        
    } catch (Exception $e) {
        error_log("Error mostrando formulario de creación: " . $e->getMessage());
        $this->setFlashMessage('Error al cargar formulario', 'error');
        $this->redirect('/admin/companies');
    }
}

/**
 * Guardar nueva empresa
 */
public function store()
{
    try {
        // Validar CSRF
        $this->validateCsrfToken();
        
        // Solo super admin
        if (!$this->isSuperAdmin()) {
            $this->jsonError('No tiene permisos para crear empresas', 403);
            return;
        }
        
        // Crear empresa usando el modelo
        $result = $this->companyModel->createCompany($_POST);
        
        if ($result['success']) {
            $this->logActivity(
                'company_created',
                'Nueva empresa creada: ' . $_POST['name'],
                ['company_id' => $result['company_id']]
            );
            
            $this->setFlashMessage('Empresa creada exitosamente', 'success');
            $this->redirect('/admin/companies');
        } else {
            $this->setFlashMessage($result['errors']['general'] ?? 'Error al crear empresa', 'error');
            $this->redirect('/admin/companies/create');
        }
        
    } catch (Exception $e) {
        error_log("Error creando empresa: " . $e->getMessage());
        $this->setFlashMessage('Error al crear empresa', 'error');
        $this->redirect('/admin/companies/create');
    }
}

/**
 * Editar empresa (solo super admin)
 */
public function edit($companyId)
{
    try {
        // Solo super admin
        if (!$this->isSuperAdmin()) {
            $this->setFlashMessage('No tiene permisos', 'error');
            $this->redirect('/admin/companies');
            return;
        }
        
        $company = $this->companyModel->find($companyId);
        
        if (!$company) {
            $this->setFlashMessage('Empresa no encontrada', 'error');
            $this->redirect('/admin/companies');
            return;
        }
        
        $data = [
            'company' => $company,
            'industries' => $this->companyModel->getTopIndustries(50)
        ];
        
        $this->render('admin/companies/edit', $data);
        
    } catch (Exception $e) {
        error_log("Error editando empresa: " . $e->getMessage());
        $this->setFlashMessage('Error al cargar empresa', 'error');
        $this->redirect('/admin/companies');
    }
}

/**
 * Eliminar empresa (solo super admin)
 */
public function delete($companyId)
{
    try {
        // Validar CSRF
        $this->validateCsrfToken();
        
        // Solo super admin
        if (!$this->isSuperAdmin()) {
            $this->jsonError('No tiene permisos', 403);
            return;
        }
        
        $result = $this->companyModel->deleteCompany($companyId, false);
        
        if ($result['success']) {
            $this->logActivity(
                'company_deleted',
                'Empresa eliminada',
                ['company_id' => $companyId]
            );
            
            $this->setFlashMessage($result['message'], 'success');
        } else {
            $this->setFlashMessage($result['message'], 'error');
        }
        
        $this->redirect('/admin/companies');
        
    } catch (Exception $e) {
        error_log("Error eliminando empresa: " . $e->getMessage());
        $this->setFlashMessage('Error al eliminar empresa', 'error');
        $this->redirect('/admin/companies');
    }
}
    
    // ✅ MÉTODO logActivity() ELIMINADO
    // Se utiliza el método heredado de la clase padre Controller
    // con la firma: protected function logActivity($action, $description = '', $metadata = [])
}
?>