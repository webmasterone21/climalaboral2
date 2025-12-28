<?php
/**
 * Motor de Vistas
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * @package EncuestasHERCO\Core
 * @version 2.0.0
 */

class View
{
    private $viewPath;
    private $layoutPath;
    private $globals = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->viewPath = __DIR__ . '/../views';
        $this->layoutPath = __DIR__ . '/../views/layouts';
        $this->setupGlobals();
    }
    
    /**
     * Configurar variables globales
     */
    private function setupGlobals()
    {
        // Cargar configuración si existe
        $appConfig = [];
        if (file_exists(__DIR__ . '/../config/app.php')) {
            $appConfig = require __DIR__ . '/../config/app.php';
        }
        
        $this->globals = [
            'app_name' => $appConfig['app_name'] ?? 'Sistema HERCO',
            'app_version' => $appConfig['app_version'] ?? '2.0.0',
            'base_url' => $appConfig['app_url'] ?? '',
            'current_user' => $_SESSION['user_name'] ?? null,
            'flash_messages' => $_SESSION['flash_messages'] ?? []
        ];
        
        // Limpiar mensajes flash después de cargarlos
        if (isset($_SESSION['flash_messages'])) {
            unset($_SESSION['flash_messages']);
        }
    }
    
    /**
     * Renderizar vista con layout
     * 
     * @param string $template Nombre de la plantilla (ej: 'admin/dashboard')
     * @param array $data Datos para la vista
     * @param string $layout Nombre del layout (default: 'app')
     * @return void
     */
    public function render($template, $data = [], $layout = 'app')
    {
        try {
            // Normalizar nombre de template
            $template = str_replace('.', '/', $template);
            $viewFile = $this->viewPath . '/' . $template . '.php';
            
            if (!file_exists($viewFile)) {
                throw new Exception("Vista no encontrada: {$template} (buscando en: {$viewFile})");
            }
            
            // Combinar datos globales con datos específicos
            $viewData = array_merge($this->globals, $data);
            
            // Renderizar contenido de la vista
            $content = $this->renderFile($viewFile, $viewData);
            
            // Si hay layout, renderizar con él
            if ($layout !== false && $layout !== null) {
                $layoutFile = $this->layoutPath . '/' . $layout . '.php';
                
                if (file_exists($layoutFile)) {
                    $viewData['content'] = $content;
                    $this->renderFile($layoutFile, $viewData, true);
                } else {
                    // Si no existe el layout, mostrar solo el contenido
                    echo $content;
                }
            } else {
                // Sin layout, solo contenido
                echo $content;
            }
            
        } catch (Exception $e) {
            $this->handleRenderError($e, $template);
        }
    }
    
    /**
     * Renderizar vista sin layout (parcial)
     * 
     * @param string $template Nombre de la plantilla
     * @param array $data Datos para la vista
     * @return string HTML renderizado
     */
    public function renderPartial($template, $data = [])
    {
        try {
            $template = str_replace('.', '/', $template);
            $viewFile = $this->viewPath . '/' . $template . '.php';
            
            if (!file_exists($viewFile)) {
                throw new Exception("Vista parcial no encontrada: {$template}");
            }
            
            $viewData = array_merge($this->globals, $data);
            return $this->renderFile($viewFile, $viewData);
            
        } catch (Exception $e) {
            error_log("Error renderizando vista parcial: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Renderizar archivo PHP
     * 
     * @param string $file Ruta del archivo
     * @param array $data Datos para la vista
     * @param bool $output Si debe hacer echo o retornar
     * @return string|void HTML renderizado
     */
    private function renderFile($file, $data = [], $output = false)
    {
        // Extraer variables para uso en la vista
        extract($data, EXTR_SKIP);
        
        // Capturar salida
        ob_start();
        
        try {
            include $file;
            $content = ob_get_clean();
            
            if ($output) {
                echo $content;
            } else {
                return $content;
            }
            
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
    
    /**
     * Escapar HTML (helper)
     * 
     * @param string $string Cadena a escapar
     * @return string Cadena escapada
     */
    public static function escape($string)
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Helper para generar URLs
     * 
     * @param string $path Ruta
     * @return string URL completa
     */
    public static function url($path)
    {
        $baseUrl = rtrim($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], '/');
        $path = '/' . ltrim($path, '/');
        return $baseUrl . $path;
    }
    
    /**
     * Manejar errores de renderizado
     * 
     * @param Exception $e Excepción
     * @param string $template Template que falló
     * @return void
     */
    private function handleRenderError($e, $template)
    {
        error_log("Error renderizando vista '{$template}': " . $e->getMessage());
        
        // En producción, mostrar mensaje genérico
        if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 20px; margin: 20px; border-radius: 4px;'>";
            echo "<h3>Error al cargar la página</h3>";
            echo "<p>Lo sentimos, ha ocurrido un error. Por favor, contacte al administrador.</p>";
            echo "</div>";
        } else {
            // En desarrollo, mostrar detalles del error
            echo "<div style='background: #ffebee; border: 2px solid #f44336; padding: 20px; margin: 20px; border-radius: 4px; font-family: monospace;'>";
            echo "<h3 style='color: #c62828; margin-top: 0;'>Error de Vista</h3>";
            echo "<p><strong>Template:</strong> {$template}</p>";
            echo "<p><strong>Mensaje:</strong> {$e->getMessage()}</p>";
            echo "<p><strong>Archivo:</strong> {$e->getFile()}:{$e->getLine()}</p>";
            echo "<pre style='background: #fff; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
            echo $e->getTraceAsString();
            echo "</pre>";
            echo "</div>";
        }
    }
    
    /**
     * Agregar variable global
     * 
     * @param string $key Clave
     * @param mixed $value Valor
     * @return void
     */
    public function addGlobal($key, $value)
    {
        $this->globals[$key] = $value;
    }
    
    /**
     * Obtener variable global
     * 
     * @param string $key Clave
     * @param mixed $default Valor por defecto
     * @return mixed Valor
     */
    public function getGlobal($key, $default = null)
    {
        return $this->globals[$key] ?? $default;
    }
}
