<?php
/**
 * SecurityMiddleware - VERSIÓN LIMPIA Y SIMPLE
 * Solo lo esencial para que el sistema funcione
 * 
 * @package EncuestasHERCO\Core
 * @version 2.0.0
 */

class SecurityMiddleware
{
    /**
     * Procesar middleware de seguridad
     */
    public function process()
    {
        // Generar token CSRF si no existe
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Headers de seguridad básicos
        if (!headers_sent()) {
            header('X-Frame-Options: DENY');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
        }
        
        // Validar CSRF solo en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            
            // No validar en API
            if (strpos($uri, '/api/') !== 0) {
                $submittedToken = $_POST['_token'] ?? '';
                $sessionToken = $_SESSION['csrf_token'] ?? '';
                
                if (empty($submittedToken) || !hash_equals($sessionToken, $submittedToken)) {
                    http_response_code(403);
                    die('Error de seguridad: Token CSRF inválido');
                }
            }
        }
        
        return true;
    }
    
    /**
     * Verificar rate limiting para login
     */
    public function checkLoginRateLimit($identifier)
    {
        $key = 'login_' . md5($identifier);
        
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }
        
        $attempts = $_SESSION['rate_limits'][$key] ?? [];
        $now = time();
        
        // Limpiar intentos mayores a 15 minutos
        $attempts = array_filter($attempts, function($time) use ($now) {
            return ($now - $time) < 900;
        });
        
        // Máximo 5 intentos
        if (count($attempts) >= 5) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Registrar intento fallido de login
     */
    public function recordFailedLogin($identifier)
    {
        $key = 'login_' . md5($identifier);
        
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }
        
        if (!isset($_SESSION['rate_limits'][$key])) {
            $_SESSION['rate_limits'][$key] = [];
        }
        
        $_SESSION['rate_limits'][$key][] = time();
    }
    
    /**
     * Limpiar intentos de login
     */
    public function clearLoginAttempts($identifier)
    {
        $key = 'login_' . md5($identifier);
        
        if (isset($_SESSION['rate_limits'][$key])) {
            unset($_SESSION['rate_limits'][$key]);
        }
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Obtener campo CSRF para formularios
     */
    public static function getCsrfField()
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
    }
}
