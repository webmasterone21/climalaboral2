<?php
/**
 * Vista: Listado de Empresas (Solo Super Admin)
 * views/admin/companies/index.php
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas - Sistema HERCO</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .company-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .company-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .company-logo-small {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 5px;
        }
        .status-badge {
            font-size: 0.85rem;
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
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="/admin/dashboard">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link" href="/logout">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Empresas</li>
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

    <!-- Header y Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2">
                <i class="fas fa-building me-2"></i>Gestión de Empresas
            </h1>
            <p class="text-muted">Administración de todas las empresas del sistema</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/companies/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nueva Empresa
            </a>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-muted">Total Empresas</h6>
                    <h3 class="mb-0"><?= number_format($stats['total_companies'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted">Activas</h6>
                    <h3 class="mb-0 text-success"><?= number_format($stats['active_companies'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-muted">Inactivas</h6>
                    <h3 class="mb-0 text-warning"><?= number_format($stats['inactive_companies'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted">Total Empleados</h6>
                    <h3 class="mb-0 text-info"><?= number_format($stats['total_employees'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/companies" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Nombre, industria..." 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Activas</option>
                        <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactivas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ordenar por</label>
                    <select name="order_by" class="form-select">
                        <option value="name" <?= ($filters['order_by'] ?? '') === 'name' ? 'selected' : '' ?>>Nombre</option>
                        <option value="created_at" <?= ($filters['order_by'] ?? '') === 'created_at' ? 'selected' : '' ?>>Fecha creación</option>
                        <option value="employee_count" <?= ($filters['order_by'] ?? '') === 'employee_count' ? 'selected' : '' ?>>Empleados</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Listado de Empresas -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Empresas Registradas
                <span class="badge bg-primary"><?= count($companies ?? []) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($companies)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-building fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No hay empresas registradas</p>
                    <a href="/admin/companies/create" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Crear Primera Empresa
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="80">Logo</th>
                                <th>Empresa</th>
                                <th>Industria</th>
                                <th>Empleados</th>
                                <th>Encuestas</th>
                                <th>Departamentos</th>
                                <th>Estado</th>
                                <th width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $company): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if (!empty($company['logo'])): ?>
                                            <img src="/uploads/companies/<?= htmlspecialchars($company['logo']) ?>" 
                                                 alt="Logo" 
                                                 class="company-logo-small">
                                        <?php else: ?>
                                            <div class="company-logo-small d-flex align-items-center justify-content-center bg-light">
                                                <i class="fas fa-building text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($company['name']) ?></strong>
                                        <?php if (!empty($company['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($company['description'], 0, 60)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($company['industry'] ?? '-') ?></td>
                                    <td>
                                        <i class="fas fa-users text-muted me-1"></i>
                                        <?= number_format($company['employee_count'] ?? 0) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= number_format($company['total_surveys'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= number_format($company['total_departments'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($company['status'] === 'active'): ?>
                                            <span class="badge bg-success status-badge">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary status-badge">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/companies/<?= $company['id'] ?>/edit" 
                                               class="btn btn-outline-primary" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    onclick="deleteCompany(<?= $company['id'] ?>)"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Paginación -->
    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_previous']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">Anterior</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($pagination['has_next']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">Siguiente</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function deleteCompany(companyId) {
    if (confirm('¿Está seguro de eliminar esta empresa? Esta acción no se puede deshacer.')) {
        fetch(`/admin/companies/${companyId}/delete`, {
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
                location.reload();
            } else {
                alert('Error al eliminar: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error de conexión');
            console.error(error);
        });
    }
}
</script>

</body>
</html>