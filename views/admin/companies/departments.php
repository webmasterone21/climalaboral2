<?php
/**
 * Vista: Gestión de Departamentos
 * views/admin/companies/departments.php
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departamentos - Sistema HERCO</title>
    
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
            <li class="breadcrumb-item active">Departamentos</li>
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
                <i class="fas fa-sitemap me-2"></i>Estructura Organizacional
            </h1>
            <p class="text-muted">Gestión de departamentos y áreas de la empresa</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                <i class="fas fa-plus me-1"></i> Nuevo Departamento
            </button>
        </div>
    </div>

    <!-- Listado de Departamentos -->
    <div class="row">
        <?php if (empty($departments)): ?>
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-sitemap fa-4x text-muted mb-3"></i>
                        <h4>No hay departamentos registrados</h4>
                        <p class="text-muted">Comience agregando los departamentos de su organización</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                            <i class="fas fa-plus me-1"></i> Agregar Primer Departamento
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($departments as $deptName => $count): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <i class="fas fa-users text-primary me-2"></i>
                                        <?= htmlspecialchars($deptName) ?>
                                    </h5>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Editar</a></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i>Eliminar</a></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h3 class="mb-0"><?= number_format($count) ?></h3>
                                        <small class="text-muted">Empleados</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h3 class="mb-0"><?= number_format($department_stats[$deptName]['surveys'] ?? 0) ?></h3>
                                        <small class="text-muted">Encuestas</small>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (isset($department_stats[$deptName])): ?>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Participación</span>
                                        <span><?= number_format($department_stats[$deptName]['responses'] ?? 0) ?> respuestas</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <?php 
                                        $participation = $department_stats[$deptName]['surveys'] > 0 
                                            ? ($department_stats[$deptName]['responses'] / $department_stats[$deptName]['surveys'] * 100) 
                                            : 0;
                                        ?>
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: <?= min(100, $participation) ?>%">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- Modal: Agregar Departamento -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Nuevo Departamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/admin/companies/departments" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Departamento *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jefe de Departamento</label>
                        <input type="text" name="manager_name" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email del Jefe</label>
                        <input type="email" name="manager_email" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Número de Empleados</label>
                        <input type="number" name="employee_count" class="form-control" min="0" value="0">
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>