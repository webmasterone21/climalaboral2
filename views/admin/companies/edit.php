<?php
/**
 * Vista: Editar Empresa (Solo Super Admin)
 * views/admin/companies/edit.php
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empresa - Sistema HERCO</title>
    
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
            <a class="nav-link" href="/admin/companies">Empresas</a>
            <a class="nav-link" href="/logout">Salir</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/companies">Empresas</a></li>
            <li class="breadcrumb-item active">Editar: <?= htmlspecialchars($company['name'] ?? '') ?></li>
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
        <div class="col-md-8">
            <h1 class="h2">
                <i class="fas fa-edit me-2"></i>Editar Empresa
            </h1>
            <p class="text-muted">Actualice la información de: <strong><?= htmlspecialchars($company['name'] ?? '') ?></strong></p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-danger" onclick="deleteCompany()">
                <i class="fas fa-trash me-1"></i> Eliminar Empresa
            </button>
        </div>
    </div>

    <!-- Formulario -->
    <form action="/admin/companies/<?= $company['id'] ?>/update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        
        <div class="row">
            <!-- Información Básica -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información Básica
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Logo Actual -->
                        <?php if (!empty($company['logo'])): ?>
                            <div class="mb-3 text-center">
                                <img src="/uploads/companies/<?= htmlspecialchars($company['logo']) ?>" 
                                     alt="Logo actual" 
                                     class="img-thumbnail"
                                     style="max-width: 200px; max-height: 200px;">
                                <p class="small text-muted mt-2">Logo actual</p>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nombre de la Empresa *</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   required
                                   value="<?= htmlspecialchars($company['name'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Industria</label>
                                <select name="industry" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <?php 
                                    $industries_list = ['Tecnología', 'Manufactura', 'Servicios', 'Retail', 'Salud', 'Educación', 'Finanzas', 'Construcción', 'Otro'];
                                    foreach ($industries_list as $industry): 
                                    ?>
                                        <option value="<?= htmlspecialchars($industry) ?>"
                                                <?= ($company['industry'] ?? '') === $industry ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($industry) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Número de Empleados</label>
                                <input type="number" 
                                       name="employee_count" 
                                       class="form-control" 
                                       min="0"
                                       value="<?= htmlspecialchars($company['employee_count'] ?? '0') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Actualizar Logo</label>
                            <input type="file" 
                                   name="logo" 
                                   class="form-control" 
                                   accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted">Formatos aceptados: JPG, PNG, GIF (Máximo 2MB)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($company['status'] ?? '') === 'active' ? 'selected' : '' ?>>Activa</option>
                                <option value="inactive" <?= ($company['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactiva</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-address-card me-2"></i>Información de Contacto
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email de Contacto</label>
                                <input type="email" 
                                       name="contact_email" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($company['contact_email'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" 
                                       name="contact_phone" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($company['contact_phone'] ?? '') ?>">
                            </div>
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
                                   value="<?= htmlspecialchars($company['website'] ?? '') ?>">
                        </div>

                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="text-end mb-4">
                    <a href="/admin/companies" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>

            <!-- Panel Lateral de Información -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Estadísticas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Encuestas:</span>
                                <strong><?= number_format($company['total_surveys'] ?? 0) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Departamentos:</span>
                                <strong><?= number_format($company['total_departments'] ?? 0) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Creada:</span>
                                <strong><?= date('d/m/Y', strtotime($company['created_at'] ?? 'now')) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Zona de Peligro
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Eliminar Empresa</h6>
                        <p class="small text-muted">Una vez eliminada, esta acción no se puede deshacer. Todos los datos asociados se perderán.</p>
                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="deleteCompany()">
                            <i class="fas fa-trash me-1"></i> Eliminar Permanentemente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function deleteCompany() {
    if (confirm('⚠️ ADVERTENCIA: ¿Está seguro de eliminar esta empresa?\n\nEsta acción eliminará:\n- Todos los usuarios asociados\n- Todas las encuestas\n- Todos los departamentos\n- Todos los datos relacionados\n\n¿Desea continuar?')) {
        if (confirm('Esta es su última oportunidad. ¿Confirma la eliminación PERMANENTE?')) {
            fetch('/admin/companies/<?= $company['id'] ?>/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: '<?= htmlspecialchars($csrf_token ?? '') ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/admin/companies';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error de conexión');
                console.error(error);
            });
        }
    }
}
</script>

</body>
</html>