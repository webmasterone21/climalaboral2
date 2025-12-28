<?php
// views/admin/reports/history.php - Historial de reportes generados y enviados
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Historial de Reportes</h1>
                        <p class="text-muted">Registro de todos los reportes generados y distribuidos</p>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item active">Historial</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-filter"></i> Filtrar Período
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item period-filter" data-period="today">Hoy</a>
                                <a class="dropdown-item period-filter" data-period="week">Esta Semana</a>
                                <a class="dropdown-item period-filter" data-period="month">Este Mes</a>
                                <a class="dropdown-item period-filter" data-period="quarter">Este Trimestre</a>
                                <a class="dropdown-item period-filter" data-period="year">Este Año</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item period-filter" data-period="all">Todos</a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="exportHistory()">
                            <i class="fas fa-download"></i> Exportar Historial
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del historial -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h4 mb-0"><?= $stats['total_reports'] ?? 0 ?></div>
                            <div class="small text-muted">Total Reportes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-paper-plane fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h4 mb-0"><?= $stats['sent_reports'] ?? 0 ?></div>
                            <div class="small text-muted">Reportes Enviados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-download fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h4 mb-0"><?= $stats['downloaded_reports'] ?? 0 ?></div>
                            <div class="small text-muted">Descargas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h4 mb-0"><?= $stats['avg_generation_time'] ?? '0' ?>s</div>
                            <div class="small text-muted">Tiempo Promedio</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros avanzados -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="survey-filter">Encuesta</label>
                                <select id="survey-filter" class="form-control">
                                    <option value="">Todas las encuestas</option>
                                    <?php if (isset($surveys)): ?>
                                        <?php foreach ($surveys as $survey): ?>
                                            <option value="<?= $survey['id'] ?>"><?= htmlspecialchars($survey['title']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="type-filter">Tipo de Reporte</label>
                                <select id="type-filter" class="form-control">
                                    <option value="">Todos los tipos</option>
                                    <option value="dashboard">Dashboard</option>
                                    <option value="detailed">Detallado</option>
                                    <option value="executive">Ejecutivo</option>
                                    <option value="comparison">Comparación</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="format-filter">Formato</label>
                                <select id="format-filter" class="form-control">
                                    <option value="">Todos los formatos</option>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="html">HTML</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status-filter">Estado</label>
                                <select id="status-filter" class="form-control">
                                    <option value="">Todos los estados</option>
                                    <option value="generated">Generado</option>
                                    <option value="sent">Enviado</option>
                                    <option value="downloaded">Descargado</option>
                                    <option value="error">Error</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de historial -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history text-primary"></i>
                            Historial de Reportes
                        </h5>
                        <div class="table-actions">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="refreshHistory()">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                                <button class="btn btn-outline-danger" onclick="clearOldReports()">
                                    <i class="fas fa-trash"></i> Limpiar Antiguos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="history-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Encuesta</th>
                                    <th>Tipo</th>
                                    <th>Formato</th>
                                    <th>Generado por</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Tamaño</th>
                                    <th class="text-center">Tiempo Gen.</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($history) && !empty($history)): ?>
                                    <?php foreach ($history as $report): ?>
                                        <tr data-survey="<?= $report['survey_id'] ?>" 
                                            data-type="<?= $report['report_type'] ?>"
                                            data-format="<?= $report['format'] ?>"
                                            data-status="<?= $report['status'] ?>">
                                            
                                            <td>
                                                <div class="datetime-cell">
                                                    <div class="font-weight-bold">
                                                        <?= date('d/m/Y', strtotime($report['created_at'])) ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('H:i:s', strtotime($report['created_at'])) ?>
                                                    </small>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <div class="survey-cell">
                                                    <div class="font-weight-bold">
                                                        <?= htmlspecialchars($report['survey_title']) ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($report['company_name']) ?>
                                                    </small>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <span class="badge badge-<?= $report['report_type'] == 'executive' ? 'primary' : ($report['report_type'] == 'detailed' ? 'info' : 'secondary') ?>">
                                                    <?= ucfirst($report['report_type']) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <span class="format-badge format-<?= strtolower($report['format']) ?>">
                                                    <i class="fas fa-file-<?= $report['format'] == 'pdf' ? 'pdf' : ($report['format'] == 'excel' ? 'excel' : 'alt') ?>"></i>
                                                    <?= strtoupper($report['format']) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <div class="user-cell">
                                                    <div><?= htmlspecialchars($report['generated_by']) ?></div>
                                                    <?php if ($report['generation_method'] == 'automatic'): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-robot"></i> Automático
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-user"></i> Manual
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                <?php
                                                $status_icons = [
                                                    'generated' => ['icon' => 'check-circle', 'class' => 'success'],
                                                    'sent' => ['icon' => 'paper-plane', 'class' => 'primary'],
                                                    'downloaded' => ['icon' => 'download', 'class' => 'info'],
                                                    'error' => ['icon' => 'exclamation-triangle', 'class' => 'danger'],
                                                    'expired' => ['icon' => 'clock', 'class' => 'warning']
                                                ];
                                                $status = $status_icons[$report['status']] ?? ['icon' => 'question', 'class' => 'secondary'];
                                                ?>
                                                <span class="status-indicator" title="<?= ucfirst($report['status']) ?>">
                                                    <i class="fas fa-<?= $status['icon'] ?> text-<?= $status['class'] ?>"></i>
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="file-size">
                                                    <?= formatFileSize($report['file_size'] ?? 0) ?>
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="generation-time">
                                                    <?= number_format($report['generation_time'] ?? 0, 1) ?>s
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($report['status'] == 'generated' || $report['status'] == 'sent' || $report['status'] == 'downloaded'): ?>
                                                        <a href="<?= BASE_URL ?>admin/reports/download/<?= $report['id'] ?>" 
                                                           class="btn btn-outline-primary" title="Descargar">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" class="btn btn-outline-info" 
                                                            onclick="showReportDetails(<?= $report['id'] ?>)" title="Detalles">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                    
                                                    <?php if ($report['status'] == 'generated'): ?>
                                                        <button type="button" class="btn btn-outline-success" 
                                                                onclick="resendReport(<?= $report['id'] ?>)" title="Reenviar">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteReport(<?= $report['id'] ?>)" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay reportes en el historial</h5>
                                                <p class="text-muted">Los reportes generados aparecerán aquí.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Paginación -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="pagination-info">
                            <small class="text-muted">
                                Mostrando <?= $pagination['start'] ?> - <?= $pagination['end'] ?> de <?= $pagination['total'] ?> reportes
                            </small>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">Anterior</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">Siguiente</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de detalles del reporte -->
<div class="modal fade" id="reportDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este reporte del historial?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializePeriodFilters();
});

function initializeFilters() {
    const filters = ['survey-filter', 'type-filter', 'format-filter', 'status-filter'];
    
    filters.forEach(filterId => {
        const filter = document.getElementById(filterId);
        if (filter) {
            filter.addEventListener('change', applyFilters);
        }
    });
}

function initializePeriodFilters() {
    const periodFilters = document.querySelectorAll('.period-filter');
    
    periodFilters.forEach(filter => {
        filter.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.dataset.period;
            filterByPeriod(period);
        });
    });
}

function applyFilters() {
    const surveyFilter = document.getElementById('survey-filter').value;
    const typeFilter = document.getElementById('type-filter').value;
    const formatFilter = document.getElementById('format-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    
    const rows = document.querySelectorAll('#history-table tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length < 9) return; // Skip empty state row
        
        const survey = row.dataset.survey;
        const type = row.dataset.type;
        const format = row.dataset.format;
        const status = row.dataset.status;
        
        const surveyMatch = !surveyFilter || survey === surveyFilter;
        const typeMatch = !typeFilter || type === typeFilter;
        const formatMatch = !formatFilter || format === formatFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        
        if (surveyMatch && typeMatch && formatMatch && statusMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterByPeriod(period) {
    // Implementar filtro por período
    const params = new URLSearchParams(window.location.search);
    params.set('period', period);
    window.location.search = params.toString();
}

function showReportDetails(reportId) {
    const modal = new bootstrap.Modal(document.getElementById('reportDetailsModal'));
    const content = document.getElementById('reportDetailsContent');
    
    // Mostrar spinner
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Cargar detalles
    fetch(`<?= BASE_URL ?>admin/reports/details/${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayReportDetails(data.report);
            } else {
                content.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles del reporte.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
        });
}

function displayReportDetails(report) {
    const content = document.getElementById('reportDetailsContent');
    
    content.innerHTML = `
        <div class="report-details">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Información Básica</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>${report.id}</td>
                        </tr>
                        <tr>
                            <td><strong>Encuesta:</strong></td>
                            <td>${report.survey_title}</td>
                        </tr>
                        <tr>
                            <td><strong>Tipo:</strong></td>
                            <td>${report.report_type}</td>
                        </tr>
                        <tr>
                            <td><strong>Formato:</strong></td>
                            <td>${report.format}</td>
                        </tr>
                        <tr>
                            <td><strong>Estado:</strong></td>
                            <td>${report.status}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success">Estadísticas</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Tamaño:</strong></td>
                            <td>${formatFileSize(report.file_size)}</td>
                        </tr>
                        <tr>
                            <td><strong>Tiempo Generación:</strong></td>
                            <td>${report.generation_time}s</td>
                        </tr>
                        <tr>
                            <td><strong>Descargas:</strong></td>
                            <td>${report.download_count || 0}</td>
                        </tr>
                        <tr>
                            <td><strong>Última Descarga:</strong></td>
                            <td>${report.last_download || 'Nunca'}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            ${report.recipients ? `
                <div class="mt-3">
                    <h6 class="text-info">Destinatarios</h6>
                    <div class="recipients-list">
                        ${report.recipients.split(',').map(email => `<span class="badge badge-light me-1">${email.trim()}</span>`).join('')}
                    </div>
                </div>
            ` : ''}
            
            ${report.error_message ? `
                <div class="mt-3">
                    <h6 class="text-danger">Error</h6>
                    <div class="alert alert-danger">
                        ${report.error_message}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

function resendReport(reportId) {
    if (!confirm('¿Deseas reenviar este reporte a los destinatarios configurados?')) {
        return;
    }
    
    fetch(`<?= BASE_URL ?>admin/reports/resend/${reportId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Reporte reenviado exitosamente', 'success');
            refreshHistory();
        } else {
            showNotification('Error al reenviar el reporte: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function deleteReport(reportId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    document.getElementById('confirmDelete').onclick = function() {
        fetch(`<?= BASE_URL ?>admin/reports/delete/${reportId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Reporte eliminado exitosamente', 'success');
                refreshHistory();
                modal.hide();
            } else {
                showNotification('Error al eliminar el reporte: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
    };
    
    modal.show();
}

function refreshHistory() {
    window.location.reload();
}

function clearOldReports() {
    if (!confirm('¿Deseas eliminar los reportes antiguos (más de 30 días)? Esta acción no se puede deshacer.')) {
        return;
    }
    
    fetch(`<?= BASE_URL ?>admin/reports/clear-old`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${data.deleted_count} reportes antiguos eliminados`, 'success');
            refreshHistory();
        } else {
            showNotification('Error al limpiar reportes antiguos: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function exportHistory() {
    const filters = {
        survey: document.getElementById('survey-filter').value,
        type: document.getElementById('type-filter').value,
        format: document.getElementById('format-filter').value,
        status: document.getElementById('status-filter').value
    };
    
    const params = new URLSearchParams(filters);
    window.location.href = `<?= BASE_URL ?>admin/reports/export-history?${params.toString()}`;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show notification-toast`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

<?php
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
</script>

<style>
.datetime-cell,
.survey-cell,
.user-cell {
    min-width: 120px;
}

.format-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 0.375rem;
}

.format-badge i {
    margin-right: 0.25rem;
}

.format-pdf {
    background-color: #dc3545;
    color: white;
}

.format-excel {
    background-color: #28a745;
    color: white;
}

.format-html {
    background-color: #17a2b8;
    color: white;
}

.format-csv {
    background-color: #ffc107;
    color: #212529;
}

.status-indicator {
    font-size: 1.25rem;
    cursor: pointer;
}

.file-size,
.generation-time {
    font-family: monospace;
    font-size: 0.9rem;
}

.empty-state {
    padding: 3rem 2rem;
}

.table-actions {
    margin-bottom: 1rem;
}

.report-details .table td:first-child {
    width: 40%;
    font-weight: 500;
}

.recipients-list {
    max-height: 100px;
    overflow-y: auto;
}

.recipients-list .badge {
    margin-bottom: 0.25rem;
}

.notification-toast {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.375rem;
    }
    
    .format-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    
    .datetime-cell,
    .survey-cell,
    .user-cell {
        min-width: 100px;
    }
}
</style>