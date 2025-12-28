<?php
/**
 * Sistema de Enrutamiento - VERSIÓN FINAL v2.0.3
 * Sistema de Encuestas de Clima Laboral HERCO
 * 
 * SOLUCIÓN DEFINITIVA - Enfoque completamente reescrito
 * ✅ Sin problemas de regex
 * ✅ Código simple y directo
 * ✅ Funciona en TODOS los servidores
 * 
 * Version: 2.0.3
 */

class Router
{
    private $routes = [];
    
    /**
     * Agregar una ruta
     */
    public function add($method, $path, $controller, $action = null, $middleware = [])
    {
        // Extraer parámetros de la ruta ANTES de crear el patrón
        $parameters = $this->extractParameters($path);
        
        // Convertir a patrón regex
        $pattern = $this->convertToRegex($path);
        
        // Si controller es una función callable y no se proporciona action
        if (is_callable($controller) && $action === null) {
            $this->routes[] = [
                'method' => strtoupper($method),
                'path' => $this->normalizePath($path),
                'pattern' => $pattern,
                'callable' => $controller,
                'controller' => null,
                'action' => null,
                'middleware' => $middleware,
                'parameters' => $parameters
            ];
        } else {
            // Ruta normal con controlador y acción
            $this->routes[] = [
                'method' => strtoupper($method),
                'path' => $this->normalizePath($path),
                'pattern' => $pattern,
                'callable' => null,
                'controller' => $controller,
                'action' => $action,
                'middleware' => $middleware,
                'parameters' => $parameters
            ];
        }
    }
    
    /**
     * Procesar solicitud HTTP
     */
    public function dispatch($method, $uri)
    {
        $method = strtoupper($method);
        $uri = $this->normalizePath($uri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            // Intentar hacer match
            $matchResult = @preg_match($route['pattern'], $uri, $matches);
            
            // Si hay error en la regex, registrarlo y continuar
            if ($matchResult === false) {
                error_log("❌ Error en regex pattern: " . $route['pattern'] . " para ruta: " . $route['path']);
                continue;
            }
            
            if ($matchResult === 1) {
                $parameters = $this->extractRouteParameters($route, $matches);
                
                // Ejecutar middleware si existe
                if (!empty($route['middleware'])) {
                    if (!$this->executeMiddleware($route['middleware'])) {
                        return;
                    }
                }
                
                // Ejecutar ruta
                if (isset($route['callable']) && $route['callable']) {
                    // Es una función callable
                    $result = call_user_func_array($route['callable'], array_values($parameters));
                    if ($result !== null) {
                        echo $result;
                    }
                } else {
                    // Es un controlador normal
                    $this->executeController(
                        $route['controller'],
                        $route['action'],
                        $parameters
                    );
                }
                return;
            }
        }
        
        // Ruta no encontrada
        $this->handleNotFound();
    }
    
    /**
     * Normalizar ruta
     */
    private function normalizePath($path)
    {
        // Remover query string
        if (strpos($path, '?') !== false) {
            $path = substr($path, 0, strpos($path, '?'));
        }
        
        // Asegurar que inicie con /
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // Remover / al final (excepto para /)
        if (strlen($path) > 1 && substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }
        
        return $path;
    }
    
    /**
     * ✅ NUEVO ENFOQUE - Convertir ruta a expresión regular
     * Método simplificado sin problemas de escaping
     */
    private function convertToRegex($path)
    {
        // Construir patrón manualmente sin preg_replace complicados
        $result = '';
        $segments = explode('/', $path);
        
        foreach ($segments as $segment) {
            if (empty($segment)) {
                continue;
            }
            
            // Detectar parámetros :id o {id}
            if (strlen($segment) > 0 && $segment[0] === ':') {
                // Formato :id
                $result .= '/([^/]+)';
            } elseif (strlen($segment) > 2 && $segment[0] === '{' && $segment[strlen($segment)-1] === '}') {
                // Formato {id}
                $result .= '/([^/]+)';
            } else {
                // Segmento literal - escapar caracteres especiales
                $escaped = str_replace(['/', '.', '+', '*', '?', '[', ']', '(', ')', '{', '}', '$', '^'], 
                                      ['\\/', '\\.', '\\+', '\\*', '\\?', '\\[', '\\]', '\\(', '\\)', '\\{', '\\}', '\\$', '\\^'], 
                                      $segment);
                $result .= '/' . $escaped;
            }
        }
        
        // Si la ruta es solo '/', manejar especialmente
        if ($path === '/') {
            $result = '/';
        }
        
        return '#^' . $result . '$#';
    }
    
    /**
     * Extraer nombres de parámetros de la ruta
     */
    private function extractParameters($path)
    {
        $params = [];
        $segments = explode('/', $path);
        
        foreach ($segments as $segment) {
            if (empty($segment)) {
                continue;
            }
            
            // Detectar :parametro
            if (strlen($segment) > 0 && $segment[0] === ':') {
                $params[] = substr($segment, 1);
            }
            // Detectar {parametro}
            elseif (strlen($segment) > 2 && $segment[0] === '{' && $segment[strlen($segment)-1] === '}') {
                $params[] = substr($segment, 1, -1);
            }
        }
        
        return $params;
    }
    
    /**
     * Extraer valores de parámetros de la coincidencia
     */
    private function extractRouteParameters($route, $matches)
    {
        $parameters = [];
        array_shift($matches); // Remover coincidencia completa
        
        foreach ($route['parameters'] as $index => $name) {
            $parameters[$name] = isset($matches[$index]) ? $matches[$index] : null;
        }
        
        return $parameters;
    }
    
    /**
     * Ejecutar middleware
     */
    private function executeMiddleware($middleware)
    {
        foreach ($middleware as $middlewareClass) {
            if (!class_exists($middlewareClass)) {
                continue;
            }
            
            $middlewareInstance = new $middlewareClass();
            
            if (method_exists($middlewareInstance, 'handle')) {
                $result = $middlewareInstance->handle();
                if ($result === false) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Ejecutar controlador
     */
    private function executeController($controllerName, $actionName, $parameters = [])
    {
        try {
            if (!class_exists($controllerName)) {
                throw new Exception("Controlador no encontrado: {$controllerName}");
            }
            
            $controller = new $controllerName();
            
            if (!method_exists($controller, $actionName)) {
                throw new Exception("Acción no encontrada: {$controllerName}::{$actionName}");
            }
            
            if (!($controller instanceof Controller)) {
                throw new Exception("Clase no es un controlador válido: {$controllerName}");
            }
            
            // Ejecutar acción
            $result = call_user_func_array([$controller, $actionName], array_values($parameters));
            
            if ($result !== null) {
                echo $result;
            }
            
        } catch (Exception $e) {
            $this->handleControllerError($e);
        }
    }
    
    /**
     * Manejar error del controlador
     */
    private function handleControllerError($e)
    {
        error_log("Error en controlador: " . $e->getMessage());
        
        http_response_code(500);
        
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Error interno del servidor',
                'message' => 'Ha ocurrido un error inesperado'
            ]);
        } else {
            echo "<h1>Error 500</h1>";
            echo "<p>Error interno del servidor</p>";
            if (ini_get('display_errors')) {
                echo "<pre>" . $e->getMessage() . "</pre>";
            }
        }
    }
    
    /**
     * Manejar ruta no encontrada
     */
    private function handleNotFound()
    {
        http_response_code(404);
        
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Ruta no encontrada',
                'message' => 'La ruta solicitada no existe'
            ]);
        } else {
            echo "<h1>Error 404</h1>";
            echo "<p>Página no encontrada</p>";
        }
    }
    
    /**
     * Determinar si es una solicitud de API
     */
    private function isApiRequest()
    {
        $uri = $_SERVER['REQUEST_URI'];
        return strpos($uri, '/api/') === 0 || 
               (isset($_SERVER['HTTP_ACCEPT']) && 
                strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
    
    /**
     * Redireccionar a una URL
     */
    public function redirect($url, $code = 302)
    {
        $pattern = '#^https?://#';
        if (!preg_match($pattern, $url)) {
            if ($url[0] !== '/') {
                $url = '/' . $url;
            }
        }
        
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Obtener todas las rutas registradas
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
