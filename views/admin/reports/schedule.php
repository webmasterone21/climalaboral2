<?php
// views/admin/reports/schedule.php - Programación de reportes automáticos
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Reportes Programados</h1>
                        <p class="text-muted">Configuración de generación y envío automático de reportes</p>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item active">Programados</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newScheduleModal">
                            <i class="fas fa-plus"></i> Nueva Programación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado del sistema de programación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-server fa-lg me-3"></i>
                        <div>
                            <h5 class="card-title mb-1">Estado del Sistema de Programación</h5>
                            <small class="opacity-75">Monitor de tareas automáticas</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="system-metric">
                                <div class="metric-icon">
                                    <?php 
                                    $system_status = $scheduler_status['active'] ?? false;
                                    ?>
                                    <i class="fas fa-power-off fa-2x <?= $system_status ? 'text-success' : 'text-danger' ?>"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value">
                                        <span class="<?= $system_status ? 'text-success' : 'text-danger' ?>">
                                            <?= $system_status ? 'ACTIVO' : 'INACTIVO' ?>
                                        </span>
                                    </div>
                                    <div class="metric-label">Estado del Sistema</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="system-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-clock fa-2x text-info"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value text-info">
                                        <?= $scheduler_status['next_execution'] ?? 'N/A' ?>
                                    </div>
                                    <div class="metric-label">Próxima Ejecución</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="system-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-tasks fa-2x text-warning"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value text-warning">
                                        <?= $scheduler_status['pending_jobs'] ?? 0 ?>
                                    </div>
                                    <div class="metric-label">Tareas Pendientes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="system-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value text-success">
                                        <?= $scheduler_status['completed_today'] ?? 0 ?>
                                    </div>
                                    <div class="metric-label">Completadas Hoy</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-sync-alt"></i>
                                Última verificación: <?= $scheduler_status['last_check'] ?? 'Nunca' ?>
                            </small>
                            <div class="system-controls">
                                <?php if ($system_status): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="stopScheduler()">
                                        <i class="fas fa-stop"></i> Pausar Sistema
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="startScheduler()">
                                        <i class="fas fa-play"></i> Activar Sistema
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="refreshSchedulerStatus()">
                                    <i class="fas fa-sync"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de reportes programados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt text-primary"></i>
                            Programaciones Activas
                        </h5>
                        <div class="card-actions">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="refreshSchedules()">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                                <button class="btn btn-outline-info" onclick="testAllSchedules()">
                                    <i class="fas fa-vial"></i> Probar Todas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Encuesta</th>
                                    <th>Tipo</th>
                                    <th>Frecuencia</th>
                                    <th>Destinatarios</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Próxima Ejecución</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($scheduled_reports) && !empty($scheduled_reports)): ?>
                                    <?php foreach ($scheduled_reports as $schedule): ?>
                                        <tr data-schedule-id="<?= $schedule['id'] ?>">
                                            <td>
                                                <div class="schedule-name">
                                                    <strong><?= htmlspecialchars($schedule['name']) ?></strong>
                                                    <?php if ($schedule['description']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($schedule['description']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <div class="survey-info">
                                                    <div class="font-weight-bold"><?= htmlspecialchars($schedule['survey_title']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($schedule['company_name']) ?></small>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <span class="badge badge-<?= $schedule['report_type'] == 'executive' ? 'primary' : ($schedule['report_type'] == 'detailed' ? 'info' : 'secondary') ?>">
                                                    <?= ucfirst($schedule['report_type']) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <div class="frequency-info">
                                                    <div class="frequency-text">
                                                        <?php
                                                        $frequency_labels = [
                                                            'daily' => 'Diario',
                                                            'weekly' => 'Semanal',
                                                            'monthly' => 'Mensual',
                                                            'quarterly' => 'Trimestral'
                                                        ];
                                                        ?>
                                                        <?= $frequency_labels[$schedule['frequency']] ?? $schedule['frequency'] ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= $schedule['schedule_time'] ? 'a las ' . $schedule['schedule_time'] : '' ?>
                                                    </small>
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <div class="recipients-preview">
                                                    <?php 
                                                    $recipients = explode(',', $schedule['recipients']);
                                                    $recipient_count = count($recipients);
                                                    ?>
                                                    <span class="badge badge-light">
                                                        <i class="fas fa-users"></i> <?= $recipient_count ?>
                                                    </span>
                                                    <?php if ($recipient_count <= 2): ?>
                                                        <br><small class="text-muted">
                                                            <?= implode(', ', array_map('trim', $recipients)) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <br><small class="text-muted" title="<?= htmlspecialchars($schedule['recipients']) ?>">
                                                            <?= trim($recipients[0]) ?> y <?= $recipient_count - 1 ?> más...
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                <?php
                                                $status_config = [
                                                    'active' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Activa'],
                                                    'paused' => ['class' => 'warning', 'icon' => 'pause-circle', 'text' => 'Pausada'],
                                                    'error' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Error'],
                                                    'inactive' => ['class' => 'secondary', 'icon' => 'circle', 'text' => 'Inactiva']
                                                ];
                                                $status = $status_config[$schedule['status']] ?? $status_config['inactive'];
                                                ?>
                                                <span class="status-badge text-<?= $status['class'] ?>" title="<?= $status['text'] ?>">
                                                    <i class="fas fa-<?= $status['icon'] ?>"></i>
                                                    <?= $status['text'] ?>
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <div class="next-execution">
                                                    <?php if ($schedule['next_execution']): ?>
                                                        <div class="font-weight-bold">
                                                            <?= date('d/m/Y', strtotime($schedule['next_execution'])) ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?= date('H:i', strtotime($schedule['next_execution'])) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">No programada</small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#" onclick="editSchedule(<?= $schedule['id'] ?>)">
                                                            <i class="fas fa-edit text-primary"></i> Editar
                                                        </a>
                                                        <a class="dropdown-item" href="#" onclick="duplicateSchedule(<?= $schedule['id'] ?>)">
                                                            <i class="fas fa-copy text-info"></i> Duplicar
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="#" onclick="testSchedule(<?= $schedule['id'] ?>)">
                                                            <i class="fas fa-vial text-warning"></i> Probar
                                                        </a>
                                                        <a class="dropdown-item" href="#" onclick="viewHistory(<?= $schedule['id'] ?>)">
                                                            <i class="fas fa-history text-secondary"></i> Historial
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <?php if ($schedule['status'] == 'active'): ?>
                                                            <a class="dropdown-item" href="#" onclick="pauseSchedule(<?= $schedule['id'] ?>)">
                                                                <i class="fas fa-pause text-warning"></i> Pausar
                                                            </a>
                                                        <?php else: ?>
                                                            <a class="dropdown-item" href="#" onclick="activateSchedule(<?= $schedule['id'] ?>)">
                                                                <i class="fas fa-play text-success"></i> Activar
                                                            </a>
                                                        <?php endif; ?>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteSchedule(<?= $schedule['id'] ?>)">
                                                            <i class="fas fa-trash"></i> Eliminar
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay reportes programados</h5>
                                                <p class="text-muted">Crea tu primera programación automática.</p>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newScheduleModal">
                                                    <i class="fas fa-plus"></i> Nueva Programación
                                                </button>
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

    <!-- Log de ejecuciones recientes -->
    <?php if (isset($recent_executions) && !empty($recent_executions)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list text-secondary"></i>
                        Ejecuciones Recientes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Programación</th>
                                    <th>Estado</th>
                                    <th>Destinatarios</th>
                                    <th>Tiempo Ejecución</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_executions as $execution): ?>
                                    <tr>
                                        <td>
                                            <small>
                                                <?= date('d/m/Y H:i:s', strtotime($execution['executed_at'])) ?>
                                            </small>
                                        </td>
                                        <td><?= htmlspecialchars($execution['schedule_name']) ?></td>
                                        <td>
                                            <?php
                                            $exec_status = [
                                                'success' => ['class' => 'success', 'icon' => 'check'],
                                                'error' => ['class' => 'danger', 'icon' => 'times'],
                                                'partial' => ['class' => 'warning', 'icon' => 'exclamation']
                                            ];
                                            $status = $exec_status[$execution['status']] ?? $exec_status['error'];
                                            ?>
                                            <small>
                                                <i class="fas fa-<?= $status['icon'] ?> text-<?= $status['class'] ?>"></i>
                                                <?= ucfirst($execution['status']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small><?= $execution['recipients_count'] ?></small>
                                        </td>
                                        <td>
                                            <small><?= number_format($execution['execution_time'], 1) ?>s</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($execution['message']) ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal para nueva programación -->
<div class="modal fade" id="newScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Programación de Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newScheduleForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="schedule-name">Nombre de la Programación</label>
                                <input type="text" id="schedule-name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="schedule-survey">Encuesta</label>
                                <select id="schedule-survey" name="survey_id" class="form-control" required>
                                    <option value="">Seleccionar encuesta...</option>
                                    <?php if (isset($surveys)): ?>
                                        <?php foreach ($surveys as $survey): ?>
                                            <option value="<?= $survey['id'] ?>"><?= htmlspecialchars($survey['title']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="schedule-description">Descripción (opcional)</label>
                        <textarea id="schedule-description" name="description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="schedule-type">Tipo de Reporte</label>
                                <select id="schedule-type" name="report_type" class="form-control" required>
                                    <option value="executive">Ejecutivo</option>
                                    <option value="detailed">Detallado</option>
                                    <option value="dashboard">Dashboard</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="schedule-format">Formato</label>
                                <select id="schedule-format" name="format" class="form-control" required>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="html">HTML</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="schedule-frequency">Frecuencia</label>
                                <select id="schedule-frequency" name="frequency" class="form-control" required>
                                    <option value="daily">Diario</option>
                                    <option value="weekly">Semanal</option>
                                    <option value="monthly">Mensual</option>
                                    <option value="quarterly">Trimestral</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row frequency-details" id="weekly-options" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Día de la Semana</label>
                                <select name="weekly_day" class="form-control">
                                    <option value="1">Lunes</option>
                                    <option value="2">Martes</option>
                                    <option value="3">Miércoles</option>
                                    <option value="4">Jueves</option>
                                    <option value="5">Viernes</option>
                                    <option value="6">Sábado</option>
                                    <option value="0">Domingo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row frequency-details" id="monthly-options" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Día del Mes</label>
                                <select name="monthly_day" class="form-control">
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                    <option value="last">Último día del mes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="schedule-time">Hora de Ejecución</label>
                                <input type="time" id="schedule-time" name="schedule_time" class="form-control" value="08:00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="schedule-timezone">Zona Horaria</label>
                                <select id="schedule-timezone" name="timezone" class="form-control">
                                    <option value="UTC">Honduras (UTC-6)</option>
                                    <option value="America/Guatemala">Guatemala (UTC-6)</option>
                                    <option value="America/El_Salvador">El Salvador (UTC-6)</option>
                                    <option value="America/Managua">Nicaragua (UTC-6)</option>
                                    <option value="America/Costa_Rica">Costa Rica (UTC-6)</option>
                                    <option value="America/Panama">Panamá (UTC-5)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="schedule-recipients">Destinatarios</label>
                        <textarea id="schedule-recipients" name="recipients" class="form-control" rows="3" 
                                  placeholder="email1@empresa.com&#10;email2@empresa.com" required></textarea>
                        <small class="form-text text-muted">Un email por línea</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="schedule-active" name="active" class="form-check-input" checked>
                            <label class="form-check-label" for="schedule-active">
                                Activar inmediatamente
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Programación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar Programación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar esta programación?</p>
                <p class="text-muted">Esta acción no se puede deshacer y cancelará todos los envíos futuros.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSchedule">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeScheduleForm();
    initializeFrequencyControls();
});

function initializeScheduleForm() {
    const form = document.getElementById('newScheduleForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveNewSchedule();
        });
    }
}

function initializeFrequencyControls() {
    const frequencySelect = document.getElementById('schedule-frequency');
    if (frequencySelect) {
        frequencySelect.addEventListener('change', function() {
            toggleFrequencyOptions(this.value);
        });
    }
}

function toggleFrequencyOptions(frequency) {
    // Ocultar todas las opciones de frecuencia
    document.querySelectorAll('.frequency-details').forEach(el => {
        el.style.display = 'none';
    });
    
    // Mostrar las opciones correspondientes
    if (frequency === 'weekly') {
        document.getElementById('weekly-options').style.display = 'block';
    } else if (frequency === 'monthly') {
        document.getElementById('monthly-options').style.display = 'block';
    }
}

function saveNewSchedule() {
    const form = document.getElementById('newScheduleForm');
    const formData = new FormData(form);
    
    fetch('<?= BASE_URL ?>admin/reports/schedule/create', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Programación creada exitosamente', 'success');
            $('#newScheduleModal').modal('hide');
            refreshSchedules();
        } else {
            showNotification('Error al crear la programación: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function editSchedule(scheduleId) {
    // Implementar edición de programación
    alert('Función de edición - Por implementar');
}

function duplicateSchedule(scheduleId) {
    if (!confirm('¿Deseas crear una copia de esta programación?')) {
        return;
    }
    
    fetch(`<?= BASE_URL ?>admin/reports/schedule/duplicate/${scheduleId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Programación duplicada exitosamente', 'success');
            refreshSchedules();
        } else {
            showNotification('Error al duplicar la programación: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function testSchedule(scheduleId) {
    if (!confirm('¿Deseas ejecutar una prueba de esta programación ahora?')) {
        return;
    }
    
    fetch(`<?= BASE_URL ?>admin/reports/schedule/test/${scheduleId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Prueba ejecutada exitosamente', 'success');
        } else {
            showNotification('Error en la prueba: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function pauseSchedule(scheduleId) {
    updateScheduleStatus(scheduleId, 'paused');
}

function activateSchedule(scheduleId) {
    updateScheduleStatus(scheduleId, 'active');
}

function updateScheduleStatus(scheduleId, status) {
    fetch(`<?= BASE_URL ?>admin/reports/schedule/status/${scheduleId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Programación ${status === 'active' ? 'activada' : 'pausada'} exitosamente`, 'success');
            refreshSchedules();
        } else {
            showNotification('Error al actualizar el estado: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function deleteSchedule(scheduleId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteScheduleModal'));
    
    document.getElementById('confirmDeleteSchedule').onclick = function() {
        fetch(`<?= BASE_URL ?>admin/reports/schedule/delete/${scheduleId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Programación eliminada exitosamente', 'success');
                refreshSchedules();
                modal.hide();
            } else {
                showNotification('Error al eliminar la programación: ' + (data.message || 'Error desconocido'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
    };
    
    modal.show();
}

function viewHistory(scheduleId) {
    // Abrir modal o página de historial
    window.open(`<?= BASE_URL ?>admin/reports/schedule/history/${scheduleId}`, '_blank');
}

function startScheduler() {
    updateSchedulerStatus('start');
}

function stopScheduler() {
    updateSchedulerStatus('stop');
}

function updateSchedulerStatus(action) {
    fetch(`<?= BASE_URL ?>admin/reports/scheduler/${action}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Sistema ${action === 'start' ? 'activado' : 'pausado'} exitosamente`, 'success');
            refreshSchedulerStatus();
        } else {
            showNotification('Error al actualizar el sistema: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function refreshSchedulerStatus() {
    window.location.reload();
}

function refreshSchedules() {
    window.location.reload();
}

function testAllSchedules() {
    if (!confirm('¿Deseas ejecutar una prueba de todas las programaciones activas?')) {
        return;
    }
    
    fetch('<?= BASE_URL ?>admin/reports/schedule/test-all', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${data.tested_count} programaciones probadas exitosamente`, 'success');
        } else {
            showNotification('Error al probar las programaciones: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
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
</script>

<style>
.system-metric {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.375rem;
    height: 100%;
}

.metric-icon {
    margin-right: 1rem;
    flex-shrink: 0;
}

.metric-content {
    flex-grow: 1;
}

.metric-value {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.9rem;
    color: #6c757d;
}

.system-controls {
    display: flex;
    gap: 0.5rem;
}

.schedule-name,
.survey-info {
    line-height: 1.4;
}

.frequency-info {
    min-width: 120px;
}

.recipients-preview {
    min-width: 140px;
}

.status-badge {
    font-weight: 500;
    font-size: 0.85rem;
}

.next-execution {
    min-width: 100px;
}

.frequency-details {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}

.empty-state {
    padding: 3rem 2rem;
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
    .system-metric {
        flex-direction: column;
        text-align: center;
        padding: 0.75rem;
    }
    
    .metric-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.375rem;
    }
    
    .system-controls {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>