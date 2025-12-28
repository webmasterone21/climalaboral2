<?php
/**
 * Vista: Configuraciones de Empresa
 * views/admin/companies/settings.php
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuraciones - Sistema HERCO</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard">
            <i class="fas fa-building me-2"></i>Sistema HERCO
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="/admin/dashboard">Dashboard</a>
            <a class="nav-link" href="/admin/companies/profile">Mi Empresa</a>
            <a class="nav-link" href="/logout">Salir</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/companies/profile">Mi Empresa</a></li>
            <li class="breadcrumb-item active">Configuraciones</li>
        </ol>
    </nav>

    <!-- Mensajes Flash -->
    <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $message): ?>
            <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show">
                <?= htmlspecialchars($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h2">
                <i class="fas fa-cog me-2"></i>Configuraciones del Sistema
            </h1>
            <p class="text-muted">Personalice el comportamiento y preferencias del sistema</p>
        </div>
    </div>

    <!-- Formulario de Configuraciones -->
    <div class="row">
        <div class="col-md-8">
            <form action="/admin/companies/settings" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                
                <!-- Configuraciones Generales -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-sliders-h me-2"></i>Configuraciones Generales
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label">Zona Horaria</label>
                            <select name="timezone" class="form-select">
                                <?php foreach ($time_zones as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" 
                                            <?= ($settings['timezone'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Idioma</label>
                            <select name="language" class="form-select">
                                <?php foreach ($languages as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" 
                                            <?= ($settings['language'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Moneda</label>
                            <select name="currency" class="form-select">
                                <?php foreach ($currencies as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" 
                                            <?= ($settings['currency'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Formato de Fecha</label>
                            <select name="date_format" class="form-select">
                                <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/AAAA</option>
                                <option value="m/d/Y" <?= ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/AAAA</option>
                                <option value="Y-m-d" <?= ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' ?>>AAAA-MM-DD</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Configuraciones de Encuestas -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Configuraciones de Encuestas
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label">Días de Expiración de Encuestas</label>
                            <input type="number" 
                                   name="survey_expiration_days" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($settings['survey_expiration_days'] ?? '30') ?>" 
                                   min="1" 
                                   max="365">
                            <small class="text-muted">Días antes de que expire una encuesta activa</small>
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" 
                                   name="require_login" 
                                   class="form-check-input" 
                                   id="requireLogin"
                                   value="1"
                                   <?= !empty($settings['require_login']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="requireLogin">
                                Requerir Login para Responder Encuestas
                            </label>
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" 
                                   name="allow_anonymous" 
                                   class="form-check-input" 
                                   id="allowAnonymous"
                                   value="1"
                                   <?= !empty($settings['allow_anonymous']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="allowAnonymous">
                                Permitir Respuestas Anónimas
                            </label>
                        </div>

                    </div>
                </div>

                <!-- Configuraciones del Sistema -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-server me-2"></i>Configuraciones del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" 
                                   name="auto_backup" 
                                   class="form-check-input" 
                                   id="autoBackup"
                                   value="1"
                                   <?= !empty($settings['auto_backup']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="autoBackup">
                                Respaldos Automáticos
                            </label>
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" 
                                   name="email_notifications" 
                                   class="form-check-input" 
                                   id="emailNotifications"
                                   value="1"
                                   <?= !empty($settings['email_notifications']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="emailNotifications">
                                Notificaciones por Email
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tamaño Máximo de Archivo (MB)</label>
                            <input type="number" 
                                   name="max_file_size" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($settings['max_file_size'] ?? '10') ?>" 
                                   min="1" 
                                   max="100">
                        </div>

                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="text-end">
                    <a href="/admin/companies/profile" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Configuraciones
                    </button>
                </div>

            </form>
        </div>

        <!-- Panel de Ayuda -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Zona Horaria</h6>
                    <p class="small">Afecta cómo se muestran las fechas y horas en todo el sistema.</p>

                    <h6>Expiración de Encuestas</h6>
                    <p class="small">Tiempo después del cual las encuestas se cierran automáticamente.</p>

                    <h6>Respuestas Anónimas</h6>
                    <p class="small">Permite que los participantes respondan sin identificarse.</p>

                    <h6>Respaldos Automáticos</h6>
                    <p class="small">El sistema creará copias de seguridad automáticamente.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>