<?php
/**
 * Configuración Principal del Sistema
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * Este archivo contiene la configuración principal de la aplicación.
 * Se genera automáticamente durante la instalación.
 * 
 * @package EncuestasHERCO
 * @version 2.0.0
 */

// Prevenir acceso directo
if (!defined('SISTEMA_HERCO') && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Acceso directo no permitido');
}

return [
    
    // ====================================
    // INFORMACIÓN GENERAL DE LA APLICACIÓN
    // ====================================
    
    'app_name' => 'Sistema de Encuestas HERCO',
    'app_description' => 'Sistema profesional de encuestas de clima laboral basado en metodología HERCO',
    'app_version' => '2.0.0',
    'app_author' => 'HERCO Systems',
    'app_url' => 'https://www.herco.com',
    
    // ====================================
    // CONFIGURACIÓN DEL ENTORNO
    // ====================================
    
    'environment' => 'production', // production, development, testing
    'debug' => false, // Cambiar a true solo para desarrollo
    'timezone' => 'America/Tegucigalpa',
    'locale' => 'es_HN',
    'charset' => 'UTF-8',
    
    // ====================================
    // CONFIGURACIÓN DE SEGURIDAD
    // ====================================
    
    'security' => [
        // Clave secreta para JWT y encriptación
        'app_key' => 'HERCO_' . bin2hex(random_bytes(32)), // Se genera durante instalación
        
        // Configuración de sesiones
        'session' => [
            'lifetime' => 7200, // 2 horas en segundos
            'name' => 'HERCO_SESSION',
            'secure' => isset($_SERVER['HTTPS']), // Solo HTTPS en producción
            'httponly' => true,
            'samesite' => 'Strict'
        ],
        
        // Protección CSRF
        'csrf' => [
            'enabled' => true,
            'token_name' => '_token',
            'regenerate_on_login' => true
        ],
        
        // Rate limiting
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 5, // Intentos por minuto
            'lockout_time' => 300, // 5 minutos en segundos
            'login_attempts' => 3 // Intentos de login antes de bloqueo
        ],
        
        // Configuración de contraseñas
        'password' => [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => false,
            'hash_algorithm' => PASSWORD_BCRYPT,
            'hash_cost' => 12
        ],
        
        // Headers de seguridad
        'headers' => [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com fonts.googleapis.com; font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self'"
        ]
    ],
    
    // ====================================
    // CONFIGURACIÓN DE BASE DE DATOS
    // ====================================
    
    'database' => [
        'default_connection' => 'mysql',
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => 'localhost', // Se configura durante instalación
                'port' => '3306',
                'database' => 'encuestas_herco', // Se configura durante instalación
                'username' => 'root', // Se configura durante instalación
                'password' => '', // Se configura durante instalación
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            ]
        ],
        
        // Pool de conexiones
        'pool' => [
            'enabled' => true,
            'max_connections' => 10,
            'idle_timeout' => 300 // 5 minutos
        ]
    ],
    
    // ====================================
    // CONFIGURACIÓN DE CORREO ELECTRÓNICO
    // ====================================
    
    'mail' => [
        'driver' => 'smtp', // smtp, sendmail, mail
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls', // tls, ssl, null
        'username' => '', // Se configura en settings
        'password' => '', // Se configura en settings
        'from' => [
            'address' => 'noreply@herco.com',
            'name' => 'Sistema HERCO'
        ],
        'timeout' => 30
    ],
    
    // ====================================
    // CONFIGURACIÓN DE ARCHIVOS Y UPLOADS
    // ====================================
    
    'files' => [
        'upload_path' => __DIR__ . '/../uploads/',
        'max_file_size' => 10485760, // 10MB en bytes
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'],
        'image_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'document_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'],
        
        // Configuración de imágenes
        'image' => [
            'max_width' => 2048,
            'max_height' => 2048,
            'quality' => 85, // Para JPEG
            'thumbnail_width' => 150,
            'thumbnail_height' => 150
        ]
    ],
    
    // ====================================
    // CONFIGURACIÓN DE CACHE
    // ====================================
    
    'cache' => [
        'driver' => 'file', // file, redis, memcached
        'enabled' => true,
        'default_ttl' => 3600, // 1 hora en segundos
        'path' => __DIR__ . '/../storage/cache/',
        
        // Configuración Redis (si está disponible)
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 0
        ]
    ],
    
    // ====================================
    // CONFIGURACIÓN DE LOGGING
    // ====================================
    
    'logging' => [
        'enabled' => true,
        'level' => 'INFO', // DEBUG, INFO, WARNING, ERROR, CRITICAL
        'path' => __DIR__ . '/../logs/',
        'max_file_size' => 10485760, // 10MB
        'max_files' => 30, // Mantener 30 archivos de log
        'channels' => [
            'app' => 'logs/app.log',
            'security' => 'logs/security/security.log',
            'error' => 'logs/error.log',
            'survey' => 'logs/survey.log',
            'performance' => 'logs/performance.log'
        ]
    ],
    
    // ====================================
    // CONFIGURACIÓN DE RESPALDOS
    // ====================================
    
    'backup' => [
        'enabled' => true,
        'path' => __DIR__ . '/../backups/',
        'schedule' => [
            'daily' => true,
            'weekly' => true,
            'monthly' => true
        ],
        'retention' => [
            'daily' => 7, // Mantener 7 días
            'weekly' => 4, // Mantener 4 semanas
            'monthly' => 12 // Mantener 12 meses
        ],
        'compress' => true,
        'include_uploads' => true
    ],
    
    // ====================================
    // CONFIGURACIÓN HERCO ESPECÍFICA
    // ====================================
    
    'herco' => [
        'version' => '2024',
        'categories_count' => 18,
        'scale_type' => 'likert_5', // Escala Likert 1-5
        'report_format' => 'standard', // standard, detailed, summary
        
        // Configuración de reportes
        'reports' => [
            'default_chart_type' => 'bar',
            'show_percentages' => true,
            'show_averages' => true,
            'include_comments' => true,
            'export_formats' => ['pdf', 'excel', 'csv'],
            'charts' => [
                'colors' => [
                    '#28a745', '#17a2b8', '#6f42c1', '#fd7e14', '#e83e8c',
                    '#ffc107', '#20c997', '#6610f2', '#6c757d', '#dc3545',
                    '#495057', '#198754', '#0dcaf0', '#f8f9fa', '#0d6efd',
                    '#712cf9', '#d63384', '#fd6c6c'
                ],
                'font_family' => 'Arial, sans-serif',
                'font_size' => 12
            ]
        ],
        
        // Escalas de evaluación
        'scales' => [
            'likert_5' => [
                1 => 'Muy en desacuerdo',
                2 => 'En desacuerdo',
                3 => 'Neutral',
                4 => 'De acuerdo',
                5 => 'Muy de acuerdo'
            ],
            'satisfaction_5' => [
                1 => 'Muy insatisfecho',
                2 => 'Insatisfecho',
                3 => 'Neutral',
                4 => 'Satisfecho',
                5 => 'Muy satisfecho'
            ]
        ]
    ],
    
    // ====================================
    // CONFIGURACIÓN DE PAGINACIÓN
    // ====================================
    
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
        'show_pagination_info' => true
    ],
    
    // ====================================
    // CONFIGURACIÓN DE API
    // ====================================
    
    'api' => [
        'enabled' => true,
        'version' => 'v1',
        'rate_limit' => [
            'requests_per_minute' => 60,
            'burst_limit' => 10
        ],
        'authentication' => [
            'driver' => 'jwt', // jwt, api_key
            'jwt_expiry' => 3600, // 1 hora
            'refresh_token_expiry' => 86400 // 24 horas
        ],
        'cors' => [
            'enabled' => true,
            'allowed_origins' => ['*'], // Configurar según necesidades
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With']
        ]
    ],
    
    // ====================================
    // CONFIGURACIÓN DE NOTIFICACIONES
    // ====================================
    
    'notifications' => [
        'enabled' => true,
        'channels' => ['email', 'database'],
        'queue' => true, // Procesar en cola para mejor rendimiento
        'templates' => [
            'survey_invitation' => 'emails/survey_invitation',
            'survey_reminder' => 'emails/survey_reminder',
            'survey_completed' => 'emails/survey_completed',
            'password_reset' => 'emails/password_reset'
        ]
    ],
    
    // ====================================
    // CONFIGURACIÓN DE MANTENIMIENTO
    // ====================================
    
    'maintenance' => [
        'enabled' => false,
        'secret' => null, // Token para acceso durante mantenimiento
        'allowed_ips' => [], // IPs permitidas durante mantenimiento
        'template' => 'maintenance',
        'retry_after' => 3600 // Tiempo en segundos para retry
    ],
    
    // ====================================
    // CONFIGURACIÓN DE RENDIMIENTO
    // ====================================
    
    'performance' => [
        'output_compression' => true,
        'css_minification' => true,
        'js_minification' => true,
        'image_optimization' => true,
        'lazy_loading' => true,
        'query_optimization' => true
    ],
    
    // ====================================
    // CONFIGURACIÓN DE INTEGRACIÓN
    // ====================================
    
    'integrations' => [
        'analytics' => [
            'google_analytics_id' => '', // GA4 Measurement ID
            'track_survey_completions' => true
        ],
        'webhooks' => [
            'enabled' => false,
            'endpoints' => []
        ],
        'sso' => [
            'enabled' => false,
            'providers' => []
        ]
    ]
];
?>