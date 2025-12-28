<?php
// views/admin/reports/index.php
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Reportes de Encuestas</h1>
                        <p class="text-muted">Análisis y estadísticas de clima laboral</p>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>admin/surveys" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Volver a Encuestas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="company-filter">Empresa</label>
                                <select id="company-filter" class="form-control">
                                    <option value="">Todas las empresas</option>
                                    <?php if (isset($companies)): ?>
                                        <?php foreach ($companies as $company): ?>
                                            <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status-filter">Estado</label>
                                <select id="status-filter" class="form-control">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activa</option>
                                    <option value="closed">Cerrada</option>
                                    <option value="draft">Borrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search-input">Buscar encuesta</label>
                                <div class="input-group">
                                    <input type="text" id="search-input" class="form-control" placeholder="Nombre de encuesta...">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button id="clear-filters" class="btn btn-outline-danger btn-block">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de encuestas con reportes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Encuestas Disponibles para Reportes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="surveys-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Encuesta</th>
                                    <th>Empresa</th>
                                    <th>Estado</th>
                                    <th>Participantes</th>
                                    <th>Completadas</th>
                                    <th>% Participación</th>
                                    <th>Fecha Cierre</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($surveys) && !empty($surveys)): ?>
                                    <?php foreach ($surveys as $survey): ?>
                                        <?php 
                                        $participation_rate = $survey['total_participants'] > 0 
                                            ? round(($survey['completed_responses'] / $survey['total_participants']) * 100, 1) 
                                            : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($survey['logo']): ?>
                                                        <img src="<?= BASE_URL ?>uploads/logos/<?= $survey['logo'] ?>" 
                                                             alt="Logo" class="rounded-circle me-3" width="40" height="40">
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($survey['title']) ?></h6>
                                                        <small class="text-muted">
                                                            Creada: <?= date('d/m/Y', strtotime($survey['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-outline-info">
                                                    <?= htmlspecialchars($survey['company_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'active' => 'badge-success',
                                                    'closed' => 'badge-secondary',
                                                    'draft' => 'badge-warning'
                                                ];
                                                $status_text = [
                                                    'active' => 'Activa',
                                                    'closed' => 'Cerrada',
                                                    'draft' => 'Borrador'
                                                ];
                                                ?>
                                                <span class="badge <?= $status_class[$survey['status']] ?>">
                                                    <?= $status_text[$survey['status']] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold"><?= $survey['total_participants'] ?></span>
                                                <small class="text-muted">invitados</small>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-success"><?= $survey['completed_responses'] ?></span>
                                                <small class="text-muted">completadas</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: <?= $participation_rate ?>%"
                                                             aria-valuenow="<?= $participation_rate ?>" 
                                                             aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="font-weight-bold"><?= $participation_rate ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($survey['end_date']): ?>
                                                    <?= date('d/m/Y', strtotime($survey['end_date'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin fecha</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <!-- Dashboard de reporte -->
                                                    <a href="<?= BASE_URL ?>admin/reports/dashboard/<?= $survey['id'] ?>" 
                                                       class="btn btn-primary" title="Ver Dashboard">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                    
                                                    <!-- Reporte detallado -->
                                                    <a href="<?= BASE_URL ?>admin/reports/detailed/<?= $survey['id'] ?>" 
                                                       class="btn btn-info" title="Reporte Detallado">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                    
                                                    <!-- Menú de exportación -->
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" 
                                                                data-bs-toggle="dropdown" title="Exportar">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/pdf/<?= $survey['id'] ?>">
                                                                <i class="fas fa-file-pdf text-danger"></i> Exportar PDF
                                                            </a>
                                                            <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/excel/<?= $survey['id'] ?>">
                                                                <i class="fas fa-file-excel text-success"></i> Exportar Excel
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/raw/<?= $survey['id'] ?>">
                                                                <i class="fas fa-database text-info"></i> Datos Brutos
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay encuestas con datos para reportes</h5>
                                                <p class="text-muted">Crea una encuesta y recolecta respuestas para generar reportes.</p>
                                                <a href="<?= BASE_URL ?>admin/surveys/create" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Crear Nueva Encuesta
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas generales -->
    <?php if (isset($surveys) && !empty($surveys)): ?>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-poll fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h4 mb-0"><?= count($surveys) ?></div>
                            <div class="small">Encuestas Totales</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <?php 
                            $total_participants = array_sum(array_column($surveys, 'total_participants'));
                            ?>
                            <div class="h4 mb-0"><?= $total_participants ?></div>
                            <div class="small">Participantes Totales</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <?php 
                            $total_completed = array_sum(array_column($surveys, 'completed_responses'));
                            ?>
                            <div class="h4 mb-0"><?= $total_completed ?></div>
                            <div class="small">Respuestas Completadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-percentage fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <?php 
                            $avg_participation = $total_participants > 0 
                                ? round(($total_completed / $total_participants) * 100, 1) 
                                : 0;
                            ?>
                            <div class="h4 mb-0"><?= $avg_participation ?>%</div>
                            <div class="small">Participación Promedio</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtros
    const companyFilter = document.getElementById('company-filter');
    const statusFilter = document.getElementById('status-filter');
    const searchInput = document.getElementById('search-input');
    const clearFilters = document.getElementById('clear-filters');
    const table = document.getElementById('surveys-table');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const companyValue = companyFilter.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();

        rows.forEach(row => {
            if (row.cells.length < 8) return; // Skip empty state row

            const company = row.cells[1].textContent.toLowerCase();
            const status = row.cells[2].textContent.toLowerCase();
            const title = row.cells[0].textContent.toLowerCase();

            const companyMatch = !companyValue || company.includes(companyValue);
            const statusMatch = !statusValue || status.includes(statusValue);
            const searchMatch = !searchValue || title.includes(searchValue);

            if (companyMatch && statusMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Event listeners
    companyFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);

    clearFilters.addEventListener('click', function() {
        companyFilter.value = '';
        statusFilter.value = '';
        searchInput.value = '';
        filterTable();
    });

    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.empty-state {
    padding: 3rem 2rem;
}

.badge-outline-info {
    color: #17a2b8;
    border: 1px solid #17a2b8;
    background: transparent;
}

.progress {
    border-radius: 4px;
}

.btn-group .dropdown-toggle-split {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}

.card {
    transition: transform 0.15s ease-in-out;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}

.opacity-75 {
    opacity: 0.75;
}
</style>