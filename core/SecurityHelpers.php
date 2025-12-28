<?php
/**
 * Security Helpers - VERSIÓN LIMPIA Y SIMPLE
 * Solo las funciones esenciales
 * 
 * @package EncuestasHERCO\Core
 * @version 2.0.0
 */

if (!defined('SISTEMA_HERCO')) {
    die('Acceso directo no permitido');
}

/**
 * Generar token CSRF
 */
function csrf_token()
{
    return SecurityMiddleware::generateCsrfToken();
}

/**
 * Obtener campo CSRF para formularios
 */
function csrf_field()
{
    return SecurityMiddleware::getCsrfField();
}

/**
 * Escapar HTML (prevenir XSS)
 */
function e($value)
{
    if (is_null($value)) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Hash de contraseña
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verificar contraseña
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Verificar si está autenticado
 */
function is_authenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar si es admin
 */
function is_admin()
{
    $role = $_SESSION['user_role'] ?? '';
    return $role === 'admin' || $role === 'superadmin';
}

/**
 * Redirigir
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Respuesta JSON
 */
function json_response($data, $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
