<?php
/**
 * Vista: Perfil de Empresa
 * views/admin/companies/profile.php
 */

// Datos disponibles:
// $company - Información de la empresa
// $stats - Estadísticas de la empresa
// $settings - Configuraciones actuales
// $upload_path - Ruta de uploads
// $max_logo_size - Tamaño máximo de logo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Empresa - <?= htmlspecialchars($company['name'] ?? 'Sistema HERCO') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .company-logo {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
        }
        .stat-card {
            border-left: 4px solid #007bff;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .section-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard">
            <i class="fas fa-building me-2"></i>Sistema HERCO
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/dashboard">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/companies/profile">
                        <i class="fas fa-building"></i> Mi Empresa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/logout">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Perfil de Empresa</li>
        </ol>
    </nav>

    <!-- Mensajes Flash -->
    <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $message): ?>
            <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <?php if (!empty($company['logo'])): ?>
                                <img src="<?= htmlspecialchars($upload_path . $company['logo']) ?>" 
                                     alt="Logo" 
                                     class="company-logo img-fluid">
                            <?php else: ?>
                                <div class="company-logo d-flex align-items-center justify-content-center">
                                    <i class="fas fa-building fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h1 class="h2 mb-2"><?= htmlspecialchars($company['name'] ?? 'Mi Empresa') ?></h1>
                            <?php if (!empty($company['industry'])): ?>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-industry me-2"></i>
                                    <?= htmlspecialchars($company['industry']) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($company['description'])): ?>
                                <p class="mb-0"><?= htmlspecialchars($company['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="/admin/companies/settings" class="btn btn-outline-primary mb-2">
                                <i class="fas fa-cog me-1"></i> Configuración
                            </a>
                            <a href="/admin/companies/branding" class="btn btn-outline-secondary">
                                <i class="fas fa-palette me-1"></i> Branding
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="section-header">
                <i class="fas fa-chart-bar me-2"></i>Estadísticas
            </h3>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Usuarios Totales</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_users'] ?? 0) ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Encuestas Activas</h6>
                            <h3 class="mb-0"><?= number_format($stats['active_surveys'] ?? 0) ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Participantes</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_participants'] ?? 0) ?></h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Respuestas</h6>
                            <h3 class="mb-0"><?= number_format($stats['total_responses'] ?? 0) ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de la Empresa -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información General
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/admin/companies/update" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Empresa *</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($company['name'] ?? '') ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" 
                                   name="logo" 
                                   class="form-control" 
                                   accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted">Máximo <?= round($max_logo_size / 1024 / 1024, 1) ?>MB - JPG, PNG, GIF</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Industria</label>
                            <input type="text" 
                                   name="industry" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($company['industry'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Número de Empleados</label>
                            <input type="number" 
                                   name="employee_count" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($company['employee_count'] ?? '') ?>" 
                                   min="0">
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-address-card me-2"></i>Información de Contacto
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/admin/companies/update" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" 
                                   name="contact_email" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($company['contact_email'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" 
                                   name="contact_phone" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($company['contact_phone'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <textarea name="address" 
                                      class="form-control" 
                                      rows="2"><?= htmlspecialchars($company['address'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sitio Web</label>
                            <input type="url" 
                                   name="website" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($company['website'] ?? '') ?>" 
                                   placeholder="https://ejemplo.com">
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h3 class="section-header">
                <i class="fas fa-link me-2"></i>Accesos Rápidos
            </h3>
        </div>
        
        <div class="col-md-3 mb-3">
            <a href="/admin/companies/departments" class="text-decoration-none">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-sitemap fa-3x text-primary mb-3"></i>
                        <h5>Departamentos</h5>
                        <p class="text-muted mb-0">Gestionar estructura organizacional</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="/admin/companies/branding" class="text-decoration-none">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-palette fa-3x text-success mb-3"></i>
                        <h5>Branding</h5>
                        <p class="text-muted mb-0">Personalizar colores y diseño</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="/admin/companies/settings" class="text-decoration-none">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-cog fa-3x text-info mb-3"></i>
                        <h5>Configuraciones</h5>
                        <p class="text-muted mb-0">Ajustes del sistema</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="/admin/users" class="text-decoration-none">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-users-cog fa-3x text-warning mb-3"></i>
                        <h5>Usuarios</h5>
                        <p class="text-muted mb-0">Gestionar usuarios del sistema</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>