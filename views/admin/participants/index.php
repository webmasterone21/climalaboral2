<?php
// views/admin/participants/index.php
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="container-xl">
    <!-- Header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    Gestión de Participantes
                </div>
                <h2 class="page-title">
                    <i class="fas fa-users me-2"></i>
                    Participantes
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="/admin/participants/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Nuevo Participante
                    </a>
                    <a href="/admin/participants/import" class="btn btn-success">
                        <i class="fas fa-file-import me-2"></i>
                        Importar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total</div>
                    </div>
                    <div class="h1 mb-0"><?= $stats['total'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Pendientes</div>
                    </div>
                    <div class="h1 mb-0"><?= $stats['pending'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Completados</div>
                    </div>
                    <div class="h1 mb-0"><?= $stats['completed'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Tasa de Completitud</div>
                    </div>
                    <div class="h1 mb-0"><?= $stats['completion_rate'] ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="/admin/participants" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Encuesta</label>
                    <select name="survey_id" class="form-select">
                        <option value="">Todas las encuestas</option>
                        <?php foreach ($surveys as $survey): ?>
                            <option value="<?= $survey['id'] ?>" <?= $filters['survey_id'] == $survey['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($survey['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pending" <?= $filters['status'] == 'pending' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="invited" <?= $filters['status'] == 'invited' ? 'selected' : '' ?>>Invitado</option>
                        <option value="started" <?= $filters['status'] == 'started' ? 'selected' : '' ?>>Iniciado</option>
                        <option value="completed" <?= $filters['status'] == 'completed' ? 'selected' : '' ?>>Completado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nombre, email..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Participantes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Participantes</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Encuesta</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participants)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No hay participantes registrados</p>
                                <a href="/admin/participants/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Crear Primer Participante
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($participants as $participant): ?>
                            <tr>
                                <td><?= $participant['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($participant['name'] ?? 'Sin nombre') ?></div>
                                    <?php if (!empty($participant['position'])): ?>
                                        <div class="text-muted small"><?= htmlspecialchars($participant['position']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($participant['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($participant['phone'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        Survey #<?= $participant['survey_id'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'invited' => 'info',
                                        'started' => 'primary',
                                        'completed' => 'success',
                                        'expired' => 'danger'
                                    ];
                                    $color = $statusColors[$participant['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= ucfirst($participant['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($participant['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <a href="/admin/participants/<?= $participant['id'] ?>/edit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $participant['id'] ?>)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($participants) && $pagination['last_page'] > 1): ?>
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-muted">
                    Mostrando <?= $pagination['from'] ?> a <?= $pagination['to'] ?> de <?= $pagination['total'] ?> registros
                </p>
                <ul class="pagination m-0 ms-auto">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?><?= $filters['survey_id'] ? '&survey_id='.$filters['survey_id'] : '' ?><?= $filters['status'] ? '&status='.$filters['status'] : '' ?><?= $filters['search'] ? '&search='.$filters['search'] : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= $filters['survey_id'] ? '&survey_id='.$filters['survey_id'] : '' ?><?= $filters['status'] ? '&status='.$filters['status'] : '' ?><?= $filters['search'] ? '&search='.$filters['search'] : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?><?= $filters['survey_id'] ? '&survey_id='.$filters['survey_id'] : '' ?><?= $filters['status'] ? '&status='.$filters['status'] : '' ?><?= $filters['search'] ? '&search='.$filters['search'] : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('¿Está seguro de eliminar este participante?')) {
        // Crear form y enviar
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/participants/' + id + '/delete';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>