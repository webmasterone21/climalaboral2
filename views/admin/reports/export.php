<?php
// views/admin/reports/export.php - Centro de exportaciones y descargas
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Centro de Exportaciones</h1>
                        <p class="text-muted">Generar y descargar reportes en diferentes formatos</p>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item active">Exportaciones</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-history"></i> Exportaciones Recientes
                            </button>
                            <div class="dropdown-menu">
                                <?php if (isset($recent_exports) && !empty($recent_exports)): ?>
                                    <?php foreach (array_slice($recent_exports, 0, 5) as $export): ?>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/download-export/<?= $export['id'] ?>">
                                            <i class="fas fa-file-<?= $export['format'] ?>"></i>
                                            <?= htmlspecialchars($export['filename']) ?>
                                            <small class="text-muted">(<?= date('d/m H:i', strtotime($export['created_at'])) ?>)</small>
                                        </a>
                                    <?php endforeach; ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/history">
                                        <i class="fas fa-list"></i> Ver Todas
                                    </a>
                                <?php else: ?>
                                    <span class="dropdown-item-text text-muted">No hay exportaciones recientes</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="button" class="btn btn-info" onclick="showExportWizard()">
                            <i class="fas fa-magic"></i> Asistente de Exportación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado del sistema de exportación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-server fa-lg me-3"></i>
                            <div>
                                <h5 class="card-title mb-0">Estado del Sistema de Exportación</h5>
                                <small class="opacity-75">Monitor de procesos y cola de exportación</small>
                            </div>
                        </div>
                        <div class="system-status">
                            <?php 
                            $export_system_status = $export_status['system_active'] ?? true;
                            $queue_count = $export_status['queue_count'] ?? 0;
                            ?>
                            <div class="status-indicator <?= $export_system_status ? 'active' : 'inactive' ?>">
                                <i class="fas fa-circle"></i>
                                <?= $export_system_status ? 'OPERATIVO' : 'MANTENIMIENTO' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="export-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value text-warning"><?= $queue_count ?></div>
                                    <div class="metric-label">En Cola</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="export-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-cog fa-spin fa-2x text-info"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value text-info"><?= $export_status['processing'] ?? 0 ?></div>
                                    <div class="metric-label">Procesando</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="export-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value text-success"><?= $export_status['completed_today'] ?? 0 ?></div>
                                    <div class="metric-label">Completadas Hoy</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="export-metric">
                                <div class="metric-icon">
                                    <i class="fas fa-tachometer-alt fa-2x text-primary"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-value text-primary"><?= number_format($export_status['avg_time'] ?? 0, 1) ?>s</div>
                                    <div class="metric-label">Tiempo Promedio</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Opciones de exportación rápida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-warning"></i>
                        Exportación Rápida
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-export-options">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="export-option-card">
                                    <div class="export-option-icon">
                                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                    </div>
                                    <div class="export-option-content">
                                        <h6 class="export-option-title">Reportes PDF</h6>
                                        <p class="export-option-description">
                                            Genera reportes ejecutivos y detallados en formato PDF para presentaciones y archivos
                                        </p>
                                        <div class="export-option-actions">
                                            <div class="btn-group btn-group-sm w-100">
                                                <button class="btn btn-danger" onclick="showExportModal('pdf', 'executive')">
                                                    Ejecutivo
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="showExportModal('pdf', 'detailed')">
                                                    Detallado
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="showExportModal('pdf', 'custom')">
                                                    Personalizado
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="export-option-card">
                                    <div class="export-option-icon">
                                        <i class="fas fa-file-excel fa-3x text-success"></i>
                                    </div>
                                    <div class="export-option-content">
                                        <h6 class="export-option-title">Hojas de Cálculo</h6>
                                        <p class="export-option-description">
                                            Exporta datos y análisis en Excel para manipulación y análisis adicional
                                        </p>
                                        <div class="export-option-actions">
                                            <div class="btn-group btn-group-sm w-100">
                                                <button class="btn btn-success" onclick="showExportModal('excel', 'data')">
                                                    Datos
                                                </button>
                                                <button class="btn btn-outline-success" onclick="showExportModal('excel', 'charts')">
                                                    Con Gráficos
                                                </button>
                                                <button class="btn btn-outline-success" onclick="showExportModal('excel', 'pivot')">
                                                    Tabla Dinámica
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="export-option-card">
                                    <div class="export-option-icon">
                                        <i class="fas fa-file-code fa-3x text-info"></i>
                                    </div>
                                    <div class="export-option-content">
                                        <h6 class="export-option-title">Datos Estructurados</h6>
                                        <p class="export-option-description">
                                            Obtén datos en CSV, JSON o XML para integración con otras herramientas
                                        </p>
                                        <div class="export-option-actions">
                                            <div class="btn-group btn-group-sm w-100">
                                                <button class="btn btn-info" onclick="showExportModal('csv', 'raw')">
                                                    CSV
                                                </button>
                                                <button class="btn btn-outline-info" onclick="showExportModal('json', 'api')">
                                                    JSON
                                                </button>
                                                <button class="btn btn-outline-info" onclick="showExportModal('xml', 'structured')">
                                                    XML
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exportaciones masivas y programadas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-layer-group text-primary"></i>
                        Exportación Masiva
                    </h5>
                </div>
                <div class="card-body">
                    <div class="bulk-export-form">
                        <div class="form-group">
                            <label>Seleccionar Encuestas</label>
                            <div class="surveys-checkbox-list" style="max-height: 200px; overflow-y: auto;">
                                <?php if (isset($surveys)): ?>
                                    <?php foreach ($surveys as $survey): ?>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input survey-checkbox" 
                                                   value="<?= $survey['id'] ?>" id="survey-<?= $survey['id'] ?>">
                                            <label class="form-check-label" for="survey-<?= $survey['id'] ?>">
                                                <?= htmlspecialchars($survey['title']) ?>
                                                <small class="text-muted">(<?= htmlspecialchars($survey['company_name']) ?>)</small>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Formato</label>
                                    <select class="form-control form-control-sm" id="bulk-format">
                                        <option value="pdf">PDF</option>
                                        <option value="excel">Excel</option>
                                        <option value="csv">CSV</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <select class="form-control form-control-sm" id="bulk-type">
                                        <option value="executive">Ejecutivo</option>
                                        <option value="detailed">Detallado</option>
                                        <option value="dashboard">Dashboard</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="bulk-zip">
                                <label class="form-check-label" for="bulk-zip">
                                    Comprimir en archivo ZIP
                                </label>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-primary btn-block" onclick="startBulkExport()">
                            <i class="fas fa-download"></i> Iniciar Exportación Masiva
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check text-success"></i>
                        Exportaciones Programadas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="scheduled-exports">
                        <?php if (isset($scheduled_exports) && !empty($scheduled_exports)): ?>
                            <?php foreach ($scheduled_exports as $export): ?>
                                <div class="scheduled-export-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="export-info">
                                            <div class="export-name font-weight-bold">
                                                <?= htmlspecialchars($export['name']) ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($export['survey_title']) ?> - 
                                                <?= strtoupper($export['format']) ?>
                                            </small>
                                        </div>
                                        <div class="export-schedule">
                                            <small class="text-info">
                                                Próxima: <?= date('d/m H:i', strtotime($export['next_run'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                                <div>No hay exportaciones programadas</div>
                                <small>
                                    <a href="<?= BASE_URL ?>admin/reports/schedule">Configurar programaciones</a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cola de exportaciones activas -->
    <?php if (isset($export_queue) && !empty($export_queue)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tasks text-warning"></i>
                            Cola de Exportación
                        </h5>
                        <div class="queue-controls">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshQueue()">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearQueue()">
                                <i class="fas fa-trash"></i> Limpiar Cola
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Posición</th>
                                    <th>Encuesta</th>
                                    <th>Formato</th>
                                    <th>Tipo</th>
                                    <th>Solicitado por</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Progreso</th>
                                    <th class="text-center">Tiempo Estimado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($export_queue as $index => $job): ?>
                                    <tr data-job-id="<?= $job['id'] ?>">
                                        <td>
                                            <span class="queue-position">#<?= $index + 1 ?></span>
                                        </td>
                                        <td>
                                            <div class="survey-info">
                                                <div class="font-weight-bold"><?= htmlspecialchars($job['survey_title']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($job['company_name']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="format-badge format-<?= strtolower($job['format']) ?>">
                                                <i class="fas fa-file-<?= $job['format'] ?>"></i>
                                                <?= strtoupper($job['format']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary"><?= ucfirst($job['type']) ?></span>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($job['requested_by']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status_config = [
                                                'queued' => ['class' => 'secondary', 'icon' => 'clock', 'text' => 'En Cola'],
                                                'processing' => ['class' => 'info', 'icon' => 'cog fa-spin', 'text' => 'Procesando'],
                                                'completed' => ['class' => 'success', 'icon' => 'check', 'text' => 'Completado'],
                                                'failed' => ['class' => 'danger', 'icon' => 'times', 'text' => 'Falló']
                                            ];
                                            $status = $status_config[$job['status']] ?? $status_config['queued'];
                                            ?>
                                            <span class="status-indicator text-<?= $status['class'] ?>">
                                                <i class="fas fa-<?= $status['icon'] ?>"></i>
                                                <?= $status['text'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 8px; width: 80px;">
                                                <div class="progress-bar progress-bar-striped <?= $job['status'] == 'processing' ? 'progress-bar-animated' : '' ?>" 
                                                     style="width: <?= $job['progress'] ?? 0 ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $job['progress'] ?? 0 ?>%</small>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                <?php if ($job['status'] == 'processing'): ?>
                                                    ~<?= $job['estimated_time'] ?? 'N/A' ?>
                                                <?php elseif ($job['status'] == 'queued'): ?>
                                                    <?= $job['estimated_wait_time'] ?? 'N/A' ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($job['status'] == 'completed'): ?>
                                                <a href="<?= BASE_URL ?>admin/reports/download-job/<?= $job['id'] ?>" 
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php elseif ($job['status'] == 'queued'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="cancelJob(<?= $job['id'] ?>)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php elseif ($job['status'] == 'failed'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="retryJob(<?= $job['id'] ?>)">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?>
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

<!-- Modal de exportación personalizada -->
<div class="modal fade" id="customExportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exportación Personalizada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="customExportForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="export-survey">Encuesta</label>
                                <select id="export-survey" name="survey_id" class="form-control" required>
                                    <option value="">Seleccionar encuesta...</option>
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
                                <label for="export-format">Formato</label>
                                <select id="export-format" name="format" class="form-control" required>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                    <option value="html">HTML</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="export-type">Tipo de Reporte</label>
                                <select id="export-type" name="report_type" class="form-control" required>
                                    <option value="executive">Ejecutivo</option>
                                    <option value="detailed">Detallado</option>
                                    <option value="dashboard">Dashboard</option>
                                    <option value="custom">Personalizado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Secciones a Incluir</label>
                        <div class="sections-grid">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sections[]" value="executive_summary" checked>
                                        <label class="form-check-label">Resumen Ejecutivo</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sections[]" value="kpis" checked>
                                        <label class="form-check-label">KPIs Principales</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sections[]" value="category_analysis" checked>
                                        <label class="form-check-label">Análisis por Categorías</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sections[]" value="department_comparison">
                                        <label class="form-check-label">Comparación por Departamentos</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sections[]" value="detailed_questions">
                                        <label class="form-check-label">Análisis Detallado de Preguntas</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sections[]" value="comments_analysis">
                                        <label class="form-check-label">Análisis de Comentarios</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sections[]" value="recommendations" checked>
                                        <label class="form-check-label">Recomendaciones</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sections[]" value="raw_data">
                                        <label class="form-check-label">Datos Brutos</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="export-filename">Nombre del Archivo</label>
                                <input type="text" id="export-filename" name="filename" class="form-control" 
                                       placeholder="reporte_clima_laboral">
                                <small class="form-text text-muted">Se agregará automáticamente la fecha y extensión</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="export-password">Protección con Contraseña (Opcional)</label>
                                <input type="password" id="export-password" name="password" class="form-control" 
                                       placeholder="Contraseña para PDF/Excel">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="export-watermark" name="watermark">
                            <label class="form-check-label" for="export-watermark">
                                Incluir marca de agua
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="export-email" name="send_email">
                            <label class="form-check-label" for="export-email">
                                Enviar por correo electrónico cuando esté listo
                            </label>
                        </div>
                    </div>
                    
                    <div class="email-options" id="email-options" style="display: none;">
                        <div class="form-group">
                            <label for="export-recipients">Destinatarios</label>
                            <textarea id="export-recipients" name="recipients" class="form-control" rows="2" 
                                      placeholder="email1@empresa.com, email2@empresa.com"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="export-subject">Asunto</label>
                            <input type="text" id="export-subject" name="email_subject" class="form-control" 
                                   value="Reporte de Clima Laboral">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download"></i> Generar Exportación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal del asistente de exportación -->
<div class="modal fade" id="exportWizardModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asistente de Exportación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Contenido del wizard se cargará dinámicamente -->
                <div id="wizard-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeExportCenter();
    
    // Auto-refresh de la cola cada 30 segundos
    setInterval(refreshQueue, 30000);
});

function initializeExportCenter() {
    // Manejar cambios en el checkbox de email
    const emailCheckbox = document.getElementById('export-email');
    const emailOptions = document.getElementById('email-options');
    
    if (emailCheckbox && emailOptions) {
        emailCheckbox.addEventListener('change', function() {
            emailOptions.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Manejar envío del formulario personalizado
    const customForm = document.getElementById('customExportForm');
    if (customForm) {
        customForm.addEventListener('submit', function(e) {
            e.preventDefault();
            processCustomExport();
        });
    }
}

function showExportModal(format, type) {
    const modal = new bootstrap.Modal(document.getElementById('customExportModal'));
    
    // Pre-configurar el formulario
    document.getElementById('export-format').value = format;
    document.getElementById('export-type').value = type;
    
    modal.show();
}

function processCustomExport() {
    const form = document.getElementById('customExportForm');
    const formData = new FormData(form);
    
    // Mostrar indicador de carga
    showLoadingIndicator('Iniciando exportación...');
    
    fetch('<?= BASE_URL ?>admin/reports/export/custom', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingIndicator();
        
        if (data.success) {
            $('#customExportModal').modal('hide');
            showNotification('Exportación iniciada exitosamente. Se agregó a la cola de procesamiento.', 'success');
            refreshQueue();
        } else {
            showNotification('Error al iniciar la exportación: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        hideLoadingIndicator();
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function startBulkExport() {
    const selectedSurveys = Array.from(document.querySelectorAll('.survey-checkbox:checked')).map(cb => cb.value);
    
    if (selectedSurveys.length === 0) {
        showNotification('Selecciona al menos una encuesta para la exportación masiva', 'warning');
        return;
    }
    
    const format = document.getElementById('bulk-format').value;
    const type = document.getElementById('bulk-type').value;
    const zipFile = document.getElementById('bulk-zip').checked;
    
    if (!confirm(`¿Deseas iniciar la exportación masiva de ${selectedSurveys.length} encuestas?`)) {
        return;
    }
    
    showLoadingIndicator('Iniciando exportación masiva...');
    
    fetch('<?= BASE_URL ?>admin/reports/export/bulk', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            surveys: selectedSurveys,
            format: format,
            type: type,
            zip: zipFile
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingIndicator();
        
        if (data.success) {
            showNotification(`${data.jobs_created} trabajos de exportación creados exitosamente`, 'success');
            refreshQueue();
            
            // Limpiar selección
            document.querySelectorAll('.survey-checkbox').forEach(cb => cb.checked = false);
        } else {
            showNotification('Error en la exportación masiva: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        hideLoadingIndicator();
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function refreshQueue() {
    fetch('<?= BASE_URL ?>admin/reports/export/queue-status', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateQueueDisplay(data.queue);
        }
    })
    .catch(error => {
        console.error('Error refreshing queue:', error);
    });
}

function updateQueueDisplay(queueData) {
    // Implementar actualización de la interfaz de cola
    // Esta función actualizaría la tabla de cola con los nuevos datos
    location.reload(); // Solución simple - en producción implementar actualización AJAX
}

function cancelJob(jobId) {
    if (!confirm('¿Estás seguro de que deseas cancelar este trabajo?')) {
        return;
    }
    
    fetch(`<?= BASE_URL ?>admin/reports/export/cancel-job/${jobId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Trabajo cancelado exitosamente', 'success');
            refreshQueue();
        } else {
            showNotification('Error al cancelar el trabajo: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function retryJob(jobId) {
    fetch(`<?= BASE_URL ?>admin/reports/export/retry-job/${jobId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Trabajo reencolado exitosamente', 'success');
            refreshQueue();
        } else {
            showNotification('Error al reintentar el trabajo: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function clearQueue() {
    if (!confirm('¿Estás seguro de que deseas limpiar toda la cola? Esto cancelará todos los trabajos pendientes.')) {
        return;
    }
    
    fetch('<?= BASE_URL ?>admin/reports/export/clear-queue', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${data.cleared_count} trabajos cancelados`, 'success');
            refreshQueue();
        } else {
            showNotification('Error al limpiar la cola: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

function showExportWizard() {
    const modal = new bootstrap.Modal(document.getElementById('exportWizardModal'));
    const wizardContent = document.getElementById('wizard-content');
    
    // Cargar contenido del wizard
    fetch('<?= BASE_URL ?>admin/reports/export/wizard', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        wizardContent.innerHTML = html;
        modal.show();
    })
    .catch(error => {
        console.error('Error:', error);
        wizardContent.innerHTML = '<div class="alert alert-danger">Error al cargar el asistente</div>';
    });
}

function showLoadingIndicator(message = 'Procesando...') {
    // Crear overlay de carga
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-content">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="loading-message">${message}</div>
        </div>
    `;
    
    document.body.appendChild(overlay);
}

function hideLoadingIndicator() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')} alert-dismissible fade show notification-toast`;
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
.export-metric {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
}

.metric-icon {
    margin-right: 1rem;
    flex-shrink: 0;
}

.metric-content {
    flex-grow: 1;
    text-align: center;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.9rem;
    color: #6c757d;
}

.system-status {
    text-align: right;
}

.status-indicator {
    font-weight: 600;
    font-size: 0.9rem;
}

.status-indicator.active {
    color: #28a745;
}

.status-indicator.inactive {
    color: #dc3545;
}

.status-indicator i {
    margin-right: 0.5rem;
}

.export-option-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.5rem;
    text-align: center;
    height: 100%;
    transition: all 0.3s ease;
}

.export-option-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.export-option-icon {
    margin-bottom: 1rem;
}

.export-option-title {
    margin-bottom: 0.75rem;
    color: #495057;
    font-weight: 600;
}

.export-option-description {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
    line-height: 1.4;
}

.export-option-actions {
    margin-top: auto;
}

.bulk-export-form {
    max-height: 400px;
}

.surveys-checkbox-list {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #e9ecef;
}

.scheduled-export-item {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 0.75rem;
    border-left: 3px solid #007bff;
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

.format-pdf { background-color: #dc3545; color: white; }
.format-excel { background-color: #28a745; color: white; }
.format-csv { background-color: #ffc107; color: #212529; }
.format-html { background-color: #17a2b8; color: white; }
.format-json { background-color: #6f42c1; color: white; }
.format-xml { background-color: #fd7e14; color: white; }

.queue-position {
    font-weight: 600;
    color: #007bff;
}

.sections-grid {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #e9ecef;
}

.email-options {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #e9ecef;
    margin-top: 1rem;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-content {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.loading-message {
    margin-top: 1rem;
    font-weight: 500;
    color: #495057;
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
    .export-metric {
        flex-direction: column;
        text-align: center;
        padding: 0.75rem;
    }
    
    .metric-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .export-option-card {
        margin-bottom: 1rem;
        padding: 1rem;
    }
    
    .bulk-export-form {
        max-height: none;
    }
    
    .surveys-checkbox-list {
        max-height: 150px;
    }
}
</style>