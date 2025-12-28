<?php
/**
 * Layout Público - Sistema HERCO v2.0
 * 
 * Layout base para todas las páginas públicas del sistema de encuestas
 * Diseño limpio, responsive y optimizado para la experiencia del usuario
 * 
 * @package HERCO\Views\Layouts
 * @version 2.0.0
 * @author Sistema HERCO
 */

// Datos del layout
$pageTitle = $pageTitle ?? 'Encuesta de Clima Laboral - Sistema HERCO';
$pageDescription = $pageDescription ?? 'Participe en nuestra encuesta de clima laboral';
$canonicalUrl = $canonicalUrl ?? '';
$bodyClass = $bodyClass ?? 'public-page';
$noIndex = $noIndex ?? true;

// Configuración de la empresa/organización
$organization = $organization ?? [
    'name' => 'Sistema HERCO',
    'logo' => '/assets/images/herco-logo.png',
    'primaryColor' => '#667eea',
    'secondaryColor' => '#764ba2',
    'supportEmail' => 'soporte@herco.com',
    'privacyUrl' => '/privacy',
    'termsUrl' => '/terms'
];

// Meta tags adicionales
$additionalMeta = $additionalMeta ?? [];
$additionalCSS = $additionalCSS ?? [];
$additionalJS = $additionalJS ?? [];

// Configuración de idioma
$locale = $locale ?? 'es_HN';
$language = $language ?? 'es';
?>
<!DOCTYPE html>
<html lang="<?= $language ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Seguridad y privacidad -->
    <?php if ($noIndex): ?>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <?php endif; ?>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <?php if ($canonicalUrl): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    <?php endif; ?>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= $locale ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($organization['name']) ?>">
    <?php if ($canonicalUrl): ?>
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <?php endif; ?>
    <meta property="og:image" content="<?= htmlspecialchars($organization['logo']) ?>">
    <meta property="og:image:alt" content="Logo de <?= htmlspecialchars($organization['name']) ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($organization['logo']) ?>">
    
    <!-- Meta tags adicionales -->
    <?php foreach ($additionalMeta as $name => $content): ?>
    <meta name="<?= htmlspecialchars($name) ?>" content="<?= htmlspecialchars($content) ?>">
    <?php endforeach; ?>
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/apple-touch-icon.png">
    <link rel="manifest" href="/assets/images/site.webmanifest">
    <meta name="theme-color" content="<?= $organization['primaryColor'] ?>">
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa/n0+LNwXH1tH24B7uBl4w8r/4F2w7H7l3V5l3iKvPh/vvHt2xzjxKRnhj" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">
    
    <!-- Custom CSS -->
    <link href="/assets/css/public.css" rel="stylesheet">
    <link href="/assets/css/survey.css" rel="stylesheet">
    
    <!-- CSS adicional -->
    <?php foreach ($additionalCSS as $cssFile): ?>
    <link href="<?= htmlspecialchars($cssFile) ?>" rel="stylesheet">
    <?php endforeach; ?>
    
    <!-- Variables CSS personalizadas -->
    <style>
        :root {
            --organization-primary: <?= $organization['primaryColor'] ?>;
            --organization-secondary: <?= $organization['secondaryColor'] ?>;
            --organization-gradient: linear-gradient(135deg, <?= $organization['primaryColor'] ?> 0%, <?= $organization['secondaryColor'] ?> 100%);
        }
    </style>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?= htmlspecialchars($organization['name']) ?>",
        "url": "<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? '') ?>",
        "logo": "<?= htmlspecialchars($organization['logo']) ?>",
        "contactPoint": {
            "@type": "ContactPoint",
            "email": "<?= htmlspecialchars($organization['supportEmail']) ?>",
            "contactType": "customer support"
        }
    }
    </script>
</head>

<body class="<?= htmlspecialchars($bodyClass) ?>">
    <!-- Skip to main content (accesibilidad) -->
    <a href="#main-content" class="skip-link">Saltar al contenido principal</a>
    
    <!-- Indicador de carga inicial -->
    <div id="initial-loader" class="initial-loader">
        <div class="loader-content">
            <div class="loader-logo">
                <img src="<?= htmlspecialchars($organization['logo']) ?>" 
                     alt="<?= htmlspecialchars($organization['name']) ?>"
                     style="max-height: 60px;">
            </div>
            <div class="loader-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <div class="loader-text">Cargando encuesta...</div>
        </div>
    </div>

    <!-- Header minimalista para páginas públicas -->
    <header class="public-header" role="banner">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="/">
                    <img src="<?= htmlspecialchars($organization['logo']) ?>" 
                         alt="<?= htmlspecialchars($organization['name']) ?>"
                         height="40" 
                         class="me-2">
                    <span class="fw-semibold"><?= htmlspecialchars($organization['name']) ?></span>
                </a>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" 
                                type="button" 
                                id="helpDropdown" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="fas fa-question-circle"></i>
                            <span class="d-none d-md-inline ms-1">Ayuda</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="helpDropdown">
                            <li>
                                <a class="dropdown-item" href="#" onclick="showHelpModal()">
                                    <i class="fas fa-info-circle me-2"></i>
                                    ¿Cómo responder?
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showPrivacyModal()">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Privacidad
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="mailto:<?= htmlspecialchars($organization['supportEmail']) ?>">
                                    <i class="fas fa-envelope me-2"></i>
                                    Contactar Soporte
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main id="main-content" role="main">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer minimalista -->
    <footer class="public-footer bg-light py-4 mt-auto" role="contentinfo">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <img src="<?= htmlspecialchars($organization['logo']) ?>" 
                             alt="<?= htmlspecialchars($organization['name']) ?>"
                             height="30" 
                             class="me-2">
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($organization['name']) ?></div>
                            <small class="text-muted">Sistema de Encuestas v2.0</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <div class="footer-links">
                        <a href="<?= htmlspecialchars($organization['privacyUrl']) ?>" 
                           class="text-muted text-decoration-none me-3">
                            <i class="fas fa-shield-alt me-1"></i>
                            Privacidad
                        </a>
                        <a href="<?= htmlspecialchars($organization['termsUrl']) ?>" 
                           class="text-muted text-decoration-none me-3">
                            <i class="fas fa-file-contract me-1"></i>
                            Términos
                        </a>
                        <a href="mailto:<?= htmlspecialchars($organization['supportEmail']) ?>" 
                           class="text-muted text-decoration-none">
                            <i class="fas fa-envelope me-1"></i>
                            Soporte
                        </a>
                    </div>
                    <div class="copyright mt-2">
                        <small class="text-muted">
                            © <?= date('Y') ?> <?= htmlspecialchars($organization['name']) ?>. 
                            Todos los derechos reservados.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Sistema de notificaciones -->
    <div id="notifications-container" class="notifications-container"></div>

    <!-- Modal: Ayuda -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">
                        <i class="fas fa-question-circle text-primary"></i>
                        ¿Cómo responder la encuesta?
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-mouse-pointer text-success"></i> Navegación</h6>
                            <ul class="list-unstyled ms-3">
                                <li class="mb-2">
                                    <i class="fas fa-chevron-right text-primary me-2"></i>
                                    Use los botones "Anterior" y "Siguiente" para navegar
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-keyboard text-primary me-2"></i>
                                    También puede usar las teclas de flecha
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-save text-primary me-2"></i>
                                    Su progreso se guarda automáticamente
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-edit text-info"></i> Tipos de Respuesta</h6>
                            <ul class="list-unstyled ms-3">
                                <li class="mb-2">
                                    <i class="fas fa-dot-circle text-primary me-2"></i>
                                    <strong>Opción única:</strong> Seleccione una respuesta
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-square text-primary me-2"></i>
                                    <strong>Múltiple:</strong> Puede seleccionar varias
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-star text-primary me-2"></i>
                                    <strong>Calificación:</strong> Haga clic en las estrellas
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-sliders-h text-primary me-2"></i>
                                    <strong>Escala:</strong> Arrastre el deslizador
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Consejo:</strong> Las preguntas marcadas con <span class="text-danger">*</span> son obligatorias.
                        Puede pausar y continuar la encuesta más tarde desde donde la dejó.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check"></i>
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Privacidad -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">
                        <i class="fas fa-shield-alt text-success"></i>
                        Privacidad y Confidencialidad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <h6><i class="fas fa-lock text-primary"></i> Sus datos están protegidos</h6>
                            <ul class="list-unstyled ms-3 mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Sus respuestas son completamente <strong>anónimas</strong>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    No recopilamos información personal identificable
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Los datos se procesan únicamente para análisis estadísticos
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    La información se almacena de forma segura y encriptada
                                </li>
                            </ul>

                            <h6><i class="fas fa-chart-bar text-info"></i> Uso de la información</h6>
                            <ul class="list-unstyled ms-3 mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Generar reportes agregados de clima laboral
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Identificar áreas de mejora organizacional
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    Desarrollar planes de acción basados en evidencia
                                </li>
                            </ul>

                            <div class="alert alert-success">
                                <i class="fas fa-certificate"></i>
                                <strong>Garantía de confidencialidad:</strong> 
                                Cumplimos con estándares internacionales de protección de datos 
                                y las mejores prácticas de la industria.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?= htmlspecialchars($organization['privacyUrl']) ?>" 
                       class="btn btn-outline-primary" 
                       target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        Política Completa
                    </a>
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        <i class="fas fa-check"></i>
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts esenciales -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" 
            crossorigin="anonymous"></script>
    
    <!-- Sistema de notificaciones -->
    <script src="/assets/js/notifications.js"></script>
    
    <!-- Scripts adicionales -->
    <?php foreach ($additionalJS as $jsFile): ?>
    <script src="<?= htmlspecialchars($jsFile) ?>"></script>
    <?php endforeach; ?>

    <!-- Scripts específicos del layout público -->
    <script>
        'use strict';

        // Configuración global
        window.publicLayout = {
            organization: <?= json_encode($organization) ?>,
            csrfToken: '<?= $csrf_token ?? '' ?>',
            locale: '<?= $locale ?>',
            language: '<?= $language ?>'
        };

        // Inicialización cuando el DOM está listo
        document.addEventListener('DOMContentLoaded', function() {
            initializePublicLayout();
        });

        /**
         * Inicializar funcionalidades del layout público
         */
        function initializePublicLayout() {
            hideInitialLoader();
            setupAccessibility();
            setupHelpModals();
            setupErrorHandling();
            logPageView();
        }

        /**
         * Ocultar indicador de carga inicial
         */
        function hideInitialLoader() {
            const loader = document.getElementById('initial-loader');
            if (loader) {
                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => {
                        loader.style.display = 'none';
                    }, 300);
                }, 1000);
            }
        }

        /**
         * Configurar características de accesibilidad
         */
        function setupAccessibility() {
            // Skip link functionality
            const skipLink = document.querySelector('.skip-link');
            if (skipLink) {
                skipLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.getElementById('main-content');
                    if (target) {
                        target.focus();
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            }

            // Mejorar navegación por teclado
            document.addEventListener('keydown', function(e) {
                // Escape para cerrar modales
                if (e.key === 'Escape') {
                    const openModals = document.querySelectorAll('.modal.show');
                    openModals.forEach(modal => {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    });
                }
            });
        }

        /**
         * Configurar modales de ayuda
         */
        function setupHelpModals() {
            // Modal de ayuda
            window.showHelpModal = function() {
                const helpModal = new bootstrap.Modal(document.getElementById('helpModal'));
                helpModal.show();
            };

            // Modal de privacidad
            window.showPrivacyModal = function() {
                const privacyModal = new bootstrap.Modal(document.getElementById('privacyModal'));
                privacyModal.show();
            };
        }

        /**
         * Configurar manejo de errores global
         */
        function setupErrorHandling() {
            // Manejar errores de JavaScript
            window.addEventListener('error', function(e) {
                console.error('Error global capturado:', e.error);
                
                // Mostrar notificación amigable al usuario
                if (window.notifications) {
                    window.notifications.show(
                        'Ha ocurrido un error inesperado. Por favor, recargue la página.',
                        'error',
                        10000
                    );
                }
            });

            // Manejar errores de red
            window.addEventListener('unhandledrejection', function(e) {
                console.error('Promise rechazada:', e.reason);
                
                if (window.notifications) {
                    window.notifications.show(
                        'Error de conexión. Verifique su conexión a internet.',
                        'warning',
                        8000
                    );
                }
            });
        }

        /**
         * Registrar vista de página para analytics
         */
        function logPageView() {
            // Analytics básico sin cookies
            if (navigator.sendBeacon) {
                const data = {
                    page: window.location.pathname,
                    timestamp: new Date().toISOString(),
                    userAgent: navigator.userAgent,
                    language: navigator.language
                };

                navigator.sendBeacon('/analytics/pageview', JSON.stringify(data));
            }
        }

        /**
         * Mostrar notificación global
         */
        window.showNotification = function(message, type = 'info', duration = 5000) {
            if (window.notifications) {
                window.notifications.show(message, type, duration);
            } else {
                // Fallback simple
                alert(message);
            }
        };

        /**
         * Función utilitaria para hacer requests AJAX
         */
        window.makeRequest = async function(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            // Agregar CSRF token si está disponible
            if (window.publicLayout.csrfToken) {
                defaultOptions.headers['X-CSRF-TOKEN'] = window.publicLayout.csrfToken;
            }

            const finalOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...options.headers
                }
            };

            try {
                const response = await fetch(url, finalOptions);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Request failed:', error);
                throw error;
            }
        };

        /**
         * Función para formatear fechas
         */
        window.formatDate = function(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };

            return new Intl.DateTimeFormat(window.publicLayout.language, {
                ...defaultOptions,
                ...options
            }).format(new Date(date));
        };

        /**
         * Función para detectar si es dispositivo móvil
         */
        window.isMobile = function() {
            return window.innerWidth <= 768;
        };

        /**
         * Función para scroll suave a elemento
         */
        window.scrollToElement = function(element, offset = 0) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            
            if (element) {
                const elementPosition = element.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        };

        // Configurar manejo de resize para responsive
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Disparar evento personalizado
                window.dispatchEvent(new CustomEvent('responsiveBreakpoint', {
                    detail: {
                        width: window.innerWidth,
                        isMobile: window.isMobile()
                    }
                }));
            }, 250);
        });

        console.log('✅ Layout público inicializado correctamente');
    </script>

    <!-- CSS adicional para este layout -->
    <style>
        /* Loader inicial */
        .initial-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--organization-primary) 0%, var(--organization-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.3s ease-out;
        }

        .loader-content {
            text-align: center;
            color: white;
        }

        .loader-logo {
            margin-bottom: 20px;
        }

        .loader-logo img {
            filter: brightness(0) invert(1);
        }

        .loader-spinner {
            margin-bottom: 15px;
        }

        .loader-text {
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
        }

        /* Skip link para accesibilidad */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--organization-primary);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 1000;
            transition: top 0.3s;
        }

        .skip-link:focus {
            top: 6px;
            color: white;
        }

        /* Header público */
        .public-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        /* Footer público */
        .public-footer {
            border-top: 1px solid #e9ecef;
        }

        .footer-links a:hover {
            color: var(--organization-primary) !important;
        }

        /* Contenedor de notificaciones */
        .notifications-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 400px;
        }

        /* Layout general */
        body.public-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
        }

        main {
            flex: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .public-header .navbar-brand span {
                display: none;
            }
            
            .footer-links {
                text-align: center !important;
            }
            
            .footer-links a {
                display: block;
                margin: 5px 0;
            }
            
            .notifications-container {
                left: 10px;
                right: 10px;
                max-width: none;
            }
        }

        /* Accesibilidad */
        @media (prefers-reduced-motion: reduce) {
            .initial-loader,
            .skip-link,
            * {
                transition: none !important;
                animation: none !important;
            }
        }

        /* Modo oscuro */
        @media (prefers-color-scheme: dark) {
            .public-page.auto-dark {
                background-color: #1a202c;
                color: #e2e8f0;
            }
            
            .auto-dark .public-header {
                background: rgba(26, 32, 44, 0.95);
            }
            
            .auto-dark .navbar-light .navbar-brand,
            .auto-dark .navbar-light .navbar-nav .nav-link {
                color: #e2e8f0;
            }
        }
    </style>
</body>
</html>