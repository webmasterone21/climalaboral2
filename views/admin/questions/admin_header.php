<?php
// views/layouts/admin_header.php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit();
}

$current_route = $current_route ?? '';
$page_title = $page_title ?? 'Sistema de Encuestas';
$title = $title ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $title ?></title>
    
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/admin.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Meta tags -->
    <meta name="description" content="Sistema de Encuestas de Clima Laboral - Panel Administrativo">
    <meta name="author" content="Sistema de Encuestas">
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="admin-layout">
    <!-- Page wrapper -->
    <div class="page">
        <!-- Navbar -->
        <header class="navbar navbar-expand-md navbar-light d-print-none">
            <div class="container-xl">
                <!-- Brand -->
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                    <a href="/admin" class="d-flex align-items-center">
                        <img src="/assets/images/logo.png" width="32" height="32" alt="Logo" class="navbar-brand-image me-2">
                        <span class="navbar-brand-text"><?= APP_NAME ?></span>
                    </a>
                </h1>
                
                <!-- Mobile menu toggle -->
                <div class="navbar-nav flex-row order-md-last">
                    <!-- User menu -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                            <span class="avatar avatar-sm" style="background-image: url('/assets/images/default-avatar.png')"></span>
                            <div class="d-none d-xl-block ps-2">
                                <div class="fw-bold"><?= htmlspecialchars($_SESSION['username']) ?></div>
                                <div class="mt-1 small text-muted"><?= ucfirst($_SESSION['role']) ?></div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="/admin/profile" class="dropdown-item">
                                <i class="fas fa-user me-2"></i>
                                Mi Perfil
                            </a>
                            <a href="/admin/settings" class="dropdown-item">
                                <i class="fas fa-cog me-2"></i>
                                Configuración
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="/help" class="dropdown-item" target="_blank">
                                <i class="fas fa-question-circle me-2"></i>
                                Ayuda
                            </a>
                            <a href="/logout" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                    
                    <!-- Mobile toggle -->
                    <div class="d-md-none">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" 
                                aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Sidebar -->
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <!-- Brand (mobile) -->
                <h1 class="navbar-brand navbar-brand-autodark d-md-none">
                    <a href="/admin">
                        <img src="/assets/images/logo-white.png" width="110" height="32" alt="Logo">
                    </a>
                </h1>
                
                <!-- Mobile toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" 
                        aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Sidebar menu -->
                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link <?= $current_route === 'dashboard' ? 'active' : '' ?>" href="/admin">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-home"></i>
                                </span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        
                        <!-- Encuestas -->
                        <li class="nav-item dropdown <?= in_array($current_route, ['surveys', 'questions']) ? 'active' : '' ?>">
                            <a class="nav-link dropdown-toggle" href="#navbar-surveys" data-bs-toggle="dropdown" 
                               data-bs-auto-close="false" role="button" aria-expanded="<?= in_array($current_route, ['surveys', 'questions']) ? 'true' : 'false' ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-poll"></i>
                                </span>
                                <span class="nav-link-title">Encuestas</span>
                            </a>
                            <div class="dropdown-menu <?= in_array($current_route, ['surveys', 'questions']) ? 'show' : '' ?>">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                        <a class="dropdown-item <?= $current_route === 'surveys' ? 'active' : '' ?>" href="/admin/surveys">
                                            <i class="fas fa-list me-2"></i>
                                            Mis Encuestas
                                        </a>
                                        <a class="dropdown-item" href="/admin/surveys/create">
                                            <i class="fas fa-plus me-2"></i>
                                            Nueva Encuesta
                                        </a>
                                        <a class="dropdown-item" href="/admin/templates">
                                            <i class="fas fa-copy me-2"></i>
                                            Plantillas
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/admin/categories">
                                            <i class="fas fa-folder me-2"></i>
                                            Categorías
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        
                        <!-- Reportes -->
                        <li class="nav-item dropdown <?= $current_route === 'reports' ? 'active' : '' ?>">
                            <a class="nav-link dropdown-toggle" href="#navbar-reports" data-bs-toggle="dropdown" 
                               data-bs-auto-close="false" role="button" aria-expanded="<?= $current_route === 'reports' ? 'true' : 'false' ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-chart-bar"></i>
                                </span>
                                <span class="nav-link-title">Reportes</span>
                            </a>
                            <div class="dropdown-menu <?= $current_route === 'reports' ? 'show' : '' ?>">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                        <a class="dropdown-item" href="/admin/reports">
                                            <i class="fas fa-chart-line me-2"></i>
                                            Dashboard de Reportes
                                        </a>
                                        <a class="dropdown-item" href="/admin/reports/comparative">
                                            <i class="fas fa-balance-scale me-2"></i>
                                            Comparativo
                                        </a>
                                        <a class="dropdown-item" href="/admin/reports/trends">
                                            <i class="fas fa-trending-up me-2"></i>
                                            Tendencias
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/admin/exports">
                                            <i class="fas fa-file-export me-2"></i>
                                            Exportaciones
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        
                        <!-- Participantes -->
                        <li class="nav-item">
                            <a class="nav-link <?= $current_route === 'participants' ? 'active' : '' ?>" href="/admin/participants">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-users"></i>
                                </span>
                                <span class="nav-link-title">Participantes</span>
                            </a>
                        </li>
                        
                        <!-- Empresas -->
                        <li class="nav-item">
                            <a class="nav-link <?= $current_route === 'companies' ? 'active' : '' ?>" href="/admin/companies">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-building"></i>
                                </span>
                                <span class="nav-link-title">Empresas</span>
                            </a>
                        </li>
                        
                        <!-- Separador -->
                        <li class="nav-item">
                            <div class="hr-text">Sistema</div>
                        </li>
                        
                        <!-- Respaldos -->
                        <li class="nav-item">
                            <a class="nav-link <?= $current_route === 'backups' ? 'active' : '' ?>" href="/admin/backups">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-database"></i>
                                </span>
                                <span class="nav-link-title">
                                    Respaldos
                                    <?php if (isset($pending_backups) && $pending_backups > 0): ?>
                                        <span class="badge bg-warning text-dark ms-2"><?= $pending_backups ?></span>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </li>
                        
                        <!-- Configuración -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-config" data-bs-toggle="dropdown" 
                               data-bs-auto-close="false" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="fas fa-cogs"></i>
                                </span>
                                <span class="nav-link-title">Configuración</span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                        <a class="dropdown-item" href="/admin/settings/general">
                                            <i class="fas fa-sliders-h me-2"></i>
                                            General
                                        </a>
                                        <a class="dropdown-item" href="/admin/settings/email">
                                            <i class="fas fa-envelope me-2"></i>
                                            Email
                                        </a>
                                        <a class="dropdown-item" href="/admin/settings/security">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            Seguridad
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/admin/logs">
                                            <i class="fas fa-file-alt me-2"></i>
                                            Logs del Sistema
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    
                    <!-- Footer del sidebar -->
                    <div class="mt-auto">
                        <div class="card card-sm">
                            <div class="card-body text-center">
                                <h4 class="h6">¿Necesitas ayuda?</h4>
                                <p class="text-muted small mb-2">
                                    Consulta nuestra documentación o contacta soporte.
                                </p>
                                <a href="/help" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-question-circle me-1"></i>
                                    Centro de Ayuda
                                </a>
                            </div>
                        </div>
                        
                        <!-- Version info -->
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                v<?= APP_VERSION ?> | 
                                <a href="/admin/about" class="text-muted">Acerca de</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Page header -->
        <div class="page-wrapper">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="container-xl mt-3">
                    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>
                                <?php
                                $icons = [
                                    'success' => 'fas fa-check-circle',
                                    'error' => 'fas fa-exclamation-circle',
                                    'warning' => 'fas fa-exclamation-triangle',
                                    'info' => 'fas fa-info-circle'
                                ];
                                $icon = $icons[$_SESSION['flash_type']] ?? 'fas fa-info-circle';
                                ?>
                                <i class="<?= $icon ?> me-2"></i>
                            </div>
                            <div>
                                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                <?php 
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']); 
                ?>
            <?php endif; ?>
            
            <!-- Loading overlay -->
            <div id="loading-overlay" class="loading-overlay" style="display: none;">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <div class="loading-text mt-3">Procesando...</div>
                </div>
            </div>
            
            <!-- Toast container -->
            <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11"></div>