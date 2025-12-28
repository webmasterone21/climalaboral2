<?php
/**
 * Vista: Editor de Encuestas - Sistema HERCO v2.0
 * 
 * Editor completo para modificar encuestas existentes con:
 * - Información básica editable
 * - Gestión de preguntas drag-and-drop
 * - Vista previa en tiempo real
 * - Control de versiones
 */

// Configuración de la página
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Encuestas', 'url' => '/admin/surveys'],
    ['label' => 'Editar: ' . ($survey['title'] ?? 'Encuesta'), 'url' => '', 'active' => true]
];

// Obtener datos de la vista
$survey = $survey ?? [];
$questions = $questions ?? [];
$categories = $categories ?? [];
$stats = $stats ?? [];
$participants = $participants ?? [];

// Estado de la encuesta para determinar qué se puede editar
$can_edit_structure = in_array($survey['status'], ['draft', 'paused']);
$has_responses = ($stats['response_count'] ?? 0) > 0;
?>

<!-- Encabezado de página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">
            <i class="fas fa-edit text-primary me-2"></i>
            Editar Encuesta
        </h1>
        <p class="text-muted mb-0">
            <?= htmlspecialchars($survey['title'] ?? 'Sin título') ?>
            <span class="badge bg-<?= getSurveyStatusColor($survey['status']) ?> ms-2">
                <?= ucfirst($survey['status']) ?>
            </span>
        </p>
    </div>
    
    <div class="d-flex gap-2">
        <!-- Botones de acción -->
        <a href="/admin/surveys" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver
        </a>
        
        <a href="/admin/surveys/preview/<?= $survey['id'] ?>" class="btn btn-outline-info" target="_blank">
            <i class="fas fa-eye me-1"></i>
            Vista Previa
        </a>
        
        <a href="/admin/surveys/builder/<?= $survey['id'] ?>" class="btn btn-outline-primary">
            <i class="fas fa-tools me-1"></i>
            Constructor
        </a>
        
        <?php if ($survey['status'] === 'draft'): ?>
        <button type="button" class="btn btn-success" onclick="activateSurvey()">
            <i class="fas fa-play me-1"></i>
            Activar
        </button>
        <?php elseif ($survey['status'] === 'active'): ?>
        <button type="button" class="btn btn-warning" onclick="pauseSurvey()">
            <i class="fas fa-pause me-1"></i>
            Pausar
        </button>
        <?php endif; ?>
        
        <!-- Dropdown más acciones -->
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="/admin/surveys/analytics/<?= $survey['id'] ?>">
                        <i class="fas fa-chart-bar me-2"></i>
                        Analytics
                    </a>
                </li>
                <li>
                    <button class="dropdown-item" onclick="duplicateSurvey()">
                        <i class="fas fa-copy me-2"></i>
                        Duplicar
                    </button>
                </li>
                <li>
                    <button class="dropdown-item" onclick="exportSurvey()">
                        <i class="fas fa-download me-2"></i>
                        Exportar
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <?php if (in_array($survey['status'], ['draft', 'paused'])): ?>
                <li>
                    <button class="dropdown-item text-danger" onclick="deleteSurvey()">
                        <i class="fas fa-trash me-2"></i>
                        Eliminar
                    </button>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Estadísticas rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info rounded-circle p-3">
                            <i class="fas fa-question text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold text-info fs-5">
                            <?= count($questions) ?>
                        </div>
                        <div class="text-muted small">Preguntas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary rounded-circle p-3">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold text-primary fs-5">
                            <?= number_format($stats['participant_count'] ?? 0) ?>
                        </div>
                        <div class="text-muted small">Participantes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success rounded-circle p-3">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold text-success fs-5">
                            <?= number_format($stats['response_count'] ?? 0) ?>
                        </div>
                        <div class="text-muted small">Respuestas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning rounded-circle p-3">
                            <i class="fas fa-percentage text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold text-warning fs-5">
                            <?= number_format($stats['completion_rate'] ?? 0, 1) ?>%
                        </div>
                        <div class="text-muted small">Completado</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alertas de estado -->
<?php if (!$can_edit_structure && $has_responses): ?>
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Limitaciones de edición:</strong> Esta encuesta tiene respuestas, por lo que algunas modificaciones están restringidas para preservar la integridad de los datos.
</div>
<?php endif; ?>

<?php if ($survey['status'] === 'active' && isset($survey['end_date']) && strtotime($survey['end_date']) < time()): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Encuesta vencida:</strong> La fecha de finalización ha pasado. Considere cambiar el estado a "Finalizada".
</div>
<?php endif; ?>

<!-- Pestañas de navegación -->
<ul class="nav nav-tabs nav-fill mb-4" id="surveyTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">
            <i class="fas fa-info-circle me-2"></i>
            Información Básica
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button">
            <i class="fas fa-list me-2"></i>
            Preguntas (<?= count($questions) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button">
            <i class="fas fa-users me-2"></i>
            Participantes
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button">
            <i class="fas fa-cogs me-2"></i>
            Configuración
        </button>
    </li>
</ul>

<!-- Contenido de las pestañas -->
<div class="tab-content" id="surveyTabsContent">
    
    <!-- PESTAÑA: Información Básica -->
    <div class="tab-pane fade show active" id="basic" role="tabpanel">
        <form id="basic-info-form" action="/admin/surveys/edit/<?= $survey['id'] ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="action" value="update_basic">
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-edit text-primary me-2"></i>
                                Información General
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Título -->
                            <div class="mb-4">
                                <label for="title" class="form-label required">
                                    <i class="fas fa-heading me-1"></i>
                                    Título de la Encuesta
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg" 
                                    id="title" 
                                    name="title"
                                    value="<?= htmlspecialchars($survey['title'] ?? '') ?>"
                                    maxlength="200"
                                    required
                                    <?= !$can_edit_structure ? 'readonly' : '' ?>
                                >
                                <?php if (!$can_edit_structure): ?>
                                <div class="form-text text-warning">
                                    <i class="fas fa-lock me-1"></i>
                                    No se puede modificar (encuesta con respuestas)
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Descripción -->
                            <div class="mb-4">
                                <label for="description" class="form-label required">
                                    <i class="fas fa-align-left me-1"></i>
                                    Descripción
                                </label>
                                <textarea 
                                    class="form-control" 
                                    id="description" 
                                    name="description"
                                    rows="4"
                                    maxlength="1000"
                                    required
                                ><?= htmlspecialchars($survey['description'] ?? '') ?></textarea>
                                <div class="form-text">
                                    <span id="description-count"><?= strlen($survey['description'] ?? '') ?></span>/1000 caracteres
                                </div>
                            </div>
                            
                            <!-- Fechas -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label required">
                                        <i class="fas fa-play text-success me-1"></i>
                                        Fecha de Inicio
                                    </label>
                                    <input 
                                        type="datetime-local" 
                                        class="form-control" 
                                        id="start_date" 
                                        name="start_date"
                                        value="<?= isset($survey['start_date']) ? date('Y-m-d\TH:i', strtotime($survey['start_date'])) : '' ?>"
                                        required
                                        <?= $survey['status'] === 'active' ? 'readonly' : '' ?>
                                    >
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label required">
                                        <i class="fas fa-stop text-danger me-1"></i>
                                        Fecha de Finalización
                                    </label>
                                    <input 
                                        type="datetime-local" 
                                        class="form-control" 
                                        id="end_date" 
                                        name="end_date"
                                        value="<?= isset($survey['end_date']) ? date('Y-m-d\TH:i', strtotime($survey['end_date'])) : '' ?>"
                                        required
                                    >
                                </div>
                            </div>
                            
                            <!-- Configuraciones básicas -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-toggle-on text-primary me-2"></i>
                                    Configuraciones de Acceso
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="is_anonymous" 
                                                name="is_anonymous"
                                                value="1"
                                                <?= ($survey['is_anonymous'] ?? true) ? 'checked' : '' ?>
                                                <?= !$can_edit_structure ? 'disabled' : '' ?>
                                            >
                                            <label class="form-check-label" for="is_anonymous">
                                                <strong>Encuesta Anónima</strong>
                                                <div class="text-muted small">Las respuestas no se asociarán con participantes</div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="show_progress" 
                                                name="show_progress"
                                                value="1"
                                                <?= ($survey['show_progress'] ?? true) ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label" for="show_progress">
                                                <strong>Mostrar Progreso</strong>
                                                <div class="text-muted small">Barra de progreso visible</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="allow_multiple_responses" 
                                                name="allow_multiple_responses"
                                                value="1"
                                                <?= ($survey['allow_multiple_responses'] ?? false) ? 'checked' : '' ?>
                                                <?= !$can_edit_structure ? 'disabled' : '' ?>
                                            >
                                            <label class="form-check-label" for="allow_multiple_responses">
                                                <strong>Múltiples Respuestas</strong>
                                                <div class="text-muted small">Permitir responder más de una vez</div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                id="auto_save" 
                                                name="auto_save"
                                                value="1"
                                                <?= ($survey['auto_save'] ?? true) ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label" for="auto_save">
                                                <strong>Guardado Automático</strong>
                                                <div class="text-muted small">Guardar progreso automáticamente</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo me-1"></i>
                                    Restablecer
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Información de la encuesta -->
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Información de la Encuesta
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small">Estado</label>
                                <div>
                                    <span class="badge bg-<?= getSurveyStatusColor($survey['status']) ?> rounded-pill">
                                        <?= ucfirst($survey['status']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted small">Creado</label>
                                <div><?= date('d/m/Y H:i', strtotime($survey['created_at'])) ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-muted small">Última actualización</label>
                                <div><?= date('d/m/Y H:i', strtotime($survey['updated_at'])) ?></div>
                            </div>
                            
                            <?php if (isset($survey['survey_code'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small">Código de encuesta</label>
                                <div class="font-monospace">
                                    <?= htmlspecialchars($survey['survey_code']) ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="copyToClipboard('<?= $survey['survey_code'] ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($survey['public_url'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small">URL pública</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control font-monospace" value="<?= $survey['public_url'] ?>" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('<?= $survey['public_url'] ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Progreso de finalización -->
                    <?php if ($has_responses): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-pie text-success me-2"></i>
                                Progreso de Finalización
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php 
                            $completion_rate = 0;
                            if (($stats['participant_count'] ?? 0) > 0) {
                                $completion_rate = (($stats['response_count'] ?? 0) / $stats['participant_count']) * 100;
                            }
                            ?>
                            <div class="progress mb-3" style="height: 12px;">
                                <div 
                                    class="progress-bar bg-<?= $completion_rate > 80 ? 'success' : ($completion_rate > 50 ? 'warning' : 'danger') ?>" 
                                    role="progressbar" 
                                    style="width: <?= $completion_rate ?>%"
                                ></div>
                            </div>
                            
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h5 text-success mb-1"><?= number_format($completion_rate, 1) ?>%</div>
                                    <div class="text-muted small">Completado</div>
                                </div>
                                <div class="col-6">
                                    <div class="h5 text-primary mb-1"><?= number_format($stats['response_count'] ?? 0) ?></div>
                                    <div class="text-muted small">Respuestas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
    
    <!-- PESTAÑA: Preguntas -->
    <div class="tab-pane fade" id="questions" role="tabpanel">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list text-primary me-2"></i>
                            Lista de Preguntas
                        </h5>
                        
                        <?php if ($can_edit_structure): ?>
                        <div class="btn-group">
                            <a href="/admin/surveys/builder/<?= $survey['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Agregar Pregunta
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" onclick="addFromTemplate()">
                                    <i class="fas fa-layer-group me-2"></i>
                                    Desde Plantilla HERCO
                                </button></li>
                                <li><button class="dropdown-item" onclick="importQuestions()">
                                    <i class="fas fa-upload me-2"></i>
                                    Importar Preguntas
                                </button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item" onclick="reorderQuestions()">
                                    <i class="fas fa-sort me-2"></i>
                                    Reordenar
                                </button></li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($questions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-3x text-muted opacity-50 mb-3"></i>
                            <h5>No hay preguntas</h5>
                            <p class="text-muted">Esta encuesta aún no tiene preguntas configuradas</p>
                            <?php if ($can_edit_structure): ?>
                            <a href="/admin/surveys/builder/<?= $survey['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Agregar Primera Pregunta
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="list-group list-group-flush" id="questions-list">
                            <?php foreach ($questions as $index => $question): ?>
                            <div class="list-group-item question-item" data-question-id="<?= $question['id'] ?>">
                                <div class="d-flex align-items-start">
                                    <!-- Número de pregunta y handle de arrastre -->
                                    <div class="flex-shrink-0 me-3">
                                        <div class="question-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem; font-weight: bold;">
                                            <?= $index + 1 ?>
                                        </div>
                                        <?php if ($can_edit_structure): ?>
                                        <div class="text-center mt-2">
                                            <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: grab;"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Contenido de la pregunta -->
                                    <div class="flex-grow-1">
                                        <!-- Categoría y tipo -->
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <?php if (isset($question['category_color'])): ?>
                                            <div 
                                                class="rounded-circle" 
                                                style="width: 12px; height: 12px; background-color: <?= $question['category_color'] ?>;"
                                                title="<?= htmlspecialchars($question['category_name'] ?? '') ?>"
                                            ></div>
                                            <?php endif; ?>
                                            
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($question['category_name'] ?? 'Sin categoría') ?>
                                            </span>
                                            
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($question['type'] ?? 'text') ?>
                                            </span>
                                            
                                            <?php if ($question['is_required'] ?? false): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-asterisk"></i>
                                                Requerida
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Texto de la pregunta -->
                                        <h6 class="mb-2">
                                            <?= htmlspecialchars($question['question_text']) ?>
                                        </h6>
                                        
                                        <!-- Descripción si existe -->
                                        <?php if (!empty($question['description'])): ?>
                                        <p class="text-muted small mb-2">
                                            <?= htmlspecialchars($question['description']) ?>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <!-- Opciones para preguntas de selección -->
                                        <?php if (in_array($question['type'], ['select', 'radio', 'checkbox']) && !empty($question['options'])): ?>
                                        <div class="question-options small">
                                            <strong>Opciones:</strong>
                                            <?php 
                                            $options = is_string($question['options']) ? json_decode($question['options'], true) : $question['options'];
                                            if (is_array($options)):
                                            ?>
                                            <ul class="list-unstyled ms-3 mb-0">
                                                <?php foreach ($options as $option): ?>
                                                <li><i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> <?= htmlspecialchars($option) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Estadísticas de respuesta -->
                                        <?php if ($has_responses && isset($question['response_count'])): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-chart-bar me-1"></i>
                                                <?= number_format($question['response_count']) ?> respuestas
                                                <?php if (isset($question['response_rate'])): ?>
                                                (<?= number_format($question['response_rate'], 1) ?>% tasa de respuesta)
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Acciones de la pregunta -->
                                    <div class="flex-shrink-0 ms-3">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($can_edit_structure): ?>
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="editQuestion(<?= $question['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="duplicateQuestion(<?= $question['id'] ?>)">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteQuestion(<?= $question['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-outline-info btn-sm" onclick="viewQuestionStats(<?= $question['id'] ?>)">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Resumen de preguntas por categoría -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-tags text-primary me-2"></i>
                            Distribución por Categorías
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div 
                                    class="rounded-circle me-2" 
                                    style="width: 12px; height: 12px; background-color: <?= $category['color'] ?>;"
                                ></div>
                                <small><?= htmlspecialchars($category['name']) ?></small>
                            </div>
                            <span class="badge bg-light text-dark">
                                <?= $category['question_count'] ?? 0 ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p class="text-muted small mb-0">No hay categorías configuradas</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Configuración de preguntas -->
                <?php if ($can_edit_structure): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs text-secondary me-2"></i>
                            Configuración Rápida
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addHercoQuestions()">
                                <i class="fas fa-magic me-1"></i>
                                Agregar Preguntas HERCO
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="validateAllQuestions()">
                                <i class="fas fa-check-circle me-1"></i>
                                Validar Preguntas
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewSurvey()">
                                <i class="fas fa-eye me-1"></i>
                                Vista Previa
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- PESTAÑA: Participantes -->
    <div class="tab-pane fade" id="participants" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="fas fa-users text-primary me-2"></i>
                Gestión de Participantes
            </h5>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-primary" onclick="addParticipants()">
                    <i class="fas fa-plus me-1"></i>
                    Agregar
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="importParticipants()">
                    <i class="fas fa-upload me-1"></i>
                    Importar
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="sendInvitations()">
                    <i class="fas fa-envelope me-1"></i>
                    Invitar
                </button>
            </div>
        </div>
        
        <!-- Aquí iría la tabla de participantes, similar a la lista de encuestas -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            La gestión completa de participantes se encuentra en desarrollo. Use el <a href="/admin/participants">módulo de participantes</a> para funcionalidad completa.
        </div>
    </div>
    
    <!-- PESTAÑA: Configuración -->
    <div class="tab-pane fade" id="settings" role="tabpanel">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs text-primary me-2"></i>
                            Configuración Avanzada
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Configuraciones de notificación -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Notificaciones</h6>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="send_reminders" name="send_reminders" checked>
                                <label class="form-check-label" for="send_reminders">
                                    <strong>Enviar Recordatorios</strong>
                                    <div class="text-muted small">Emails automáticos para participantes</div>
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="notify_completion" name="notify_completion" checked>
                                <label class="form-check-label" for="notify_completion">
                                    <strong>Notificar Completación</strong>
                                    <div class="text-muted small">Avisar cuando se complete la encuesta</div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Configuraciones de acceso -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Control de Acceso</h6>
                            
                            <div class="mb-3">
                                <label for="access_code" class="form-label">Código de Acceso (Opcional)</label>
                                <input type="text" class="form-control" id="access_code" name="access_code" 
                                       placeholder="Dejar vacío para acceso libre">
                                <div class="form-text">Si se especifica, los participantes deberán ingresar este código</div>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="require_authentication" name="require_authentication">
                                <label class="form-check-label" for="require_authentication">
                                    <strong>Requiere Autenticación</strong>
                                    <div class="text-muted small">Los participantes deben estar registrados</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Zona peligrosa -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Zona Peligrosa
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-danger">Eliminar Encuesta</h6>
                            <p class="small text-muted mb-2">
                                Elimina permanentemente esta encuesta y todas sus respuestas.
                            </p>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSurvey()">
                                <i class="fas fa-trash me-1"></i>
                                Eliminar
                            </button>
                        </div>
                        
                        <?php if ($has_responses): ?>
                        <div class="mb-3">
                            <h6 class="text-warning">Archivar Encuesta</h6>
                            <p class="small text-muted mb-2">
                                Archiva la encuesta manteniendo los datos pero ocultándola.
                            </p>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="archiveSurvey()">
                                <i class="fas fa-archive me-1"></i>
                                Archivar
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Funciones helper (duplicadas por compatibilidad)
function getSurveyStatusColor($status) {
    $colors = [
        'draft' => 'secondary',
        'active' => 'success',
        'paused' => 'warning',
        'completed' => 'info',
        'archived' => 'dark'
    ];
    return $colors[$status] ?? 'secondary';
}
?>

<!-- JavaScript específico del editor -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSurveyEditor();
    console.log('✏️ Editor de Encuestas HERCO v2.0 Cargado');
});

function initializeSurveyEditor() {
    // Inicializar validación del formulario básico
    const basicValidator = new FormValidator('basic-info-form', {
        title: {
            required: true,
            minLength: 5,
            maxLength: 200,
            message: 'El título debe tener entre 5 y 200 caracteres'
        },
        description: {
            required: true,
            minLength: 10,
            maxLength: 1000,
            message: 'La descripción debe tener entre 10 y 1000 caracteres'
        },
        start_date: {
            required: true,
            message: 'Seleccione la fecha de inicio'
        },
        end_date: {
            required: true,
            message: 'Seleccione la fecha de finalización'
        }
    });
    
    // Contador de caracteres para descripción
    const descriptionInput = document.getElementById('description');
    const descriptionCount = document.getElementById('description-count');
    
    descriptionInput?.addEventListener('input', function() {
        descriptionCount.textContent = this.value.length;
    });
    
    // Validación de fechas
    setupDateValidation();
    
    // Inicializar drag-and-drop para preguntas (si está habilitado)
    <?php if ($can_edit_structure): ?>
    initializeQuestionDragDrop();
    <?php endif; ?>
    
    // Manejo del formulario básico
    document.getElementById('basic-info-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveBasicInfo();
    });
}

function setupDateValidation() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput?.addEventListener('change', function() {
        if (this.value) {
            const minEndDate = new Date(this.value);
            minEndDate.setHours(minEndDate.getHours() + 1); // Mínimo 1 hora después
            endDateInput.min = minEndDate.toISOString().slice(0, 16);
        }
    });
}

function initializeQuestionDragDrop() {
    // Implementar drag-and-drop con SortableJS o similar
    // Por ahora, placeholder para funcionalidad futura
    console.log('Drag-and-drop de preguntas habilitado');
}

function saveBasicInfo() {
    const formData = new FormData(document.getElementById('basic-info-form'));
    
    window.showLoading?.('Guardando cambios...');
    
    fetch(`/admin/surveys/edit/<?= $survey['id'] ?>`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        window.hideLoading?.();
        
        if (data.success) {
            window.notifications?.show('Cambios guardados exitosamente', 'success');
        } else {
            window.notifications?.show(data.message || 'Error al guardar cambios', 'error');
        }
    })
    .catch(error => {
        window.hideLoading?.();
        console.error('Error:', error);
        window.notifications?.show('Error de conexión', 'error');
    });
}

function resetForm() {
    if (confirm('¿Está seguro de restablecer todos los cambios?')) {
        document.getElementById('basic-info-form').reset();
        window.location.reload();
    }
}

// Funciones de gestión de estado
function activateSurvey() {
    if (confirm('¿Está seguro de activar esta encuesta? Los participantes comenzarán a recibir invitaciones.')) {
        changeSurveyStatus('active');
    }
}

function pauseSurvey() {
    if (confirm('¿Pausar esta encuesta? Los participantes no podrán responder temporalmente.')) {
        changeSurveyStatus('paused');
    }
}

function changeSurveyStatus(newStatus) {
    window.showLoading?.('Cambiando estado...');
    
    fetch(`/admin/surveys/<?= $survey['id'] ?>/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        window.hideLoading?.();
        
        if (data.success) {
            window.notifications?.show(data.message || 'Estado actualizado', 'success');
            window.location.reload();
        } else {
            window.notifications?.show(data.message || 'Error al cambiar estado', 'error');
        }
    })
    .catch(error => {
        window.hideLoading?.();
        console.error('Error:', error);
        window.notifications?.show('Error de conexión', 'error');
    });
}

// Funciones de gestión de preguntas
function editQuestion(questionId) {
    window.location.href = `/admin/surveys/builder/<?= $survey['id'] ?>?edit=${questionId}`;
}

function duplicateQuestion(questionId) {
    if (confirm('¿Crear una copia de esta pregunta?')) {
        window.showLoading?.('Duplicando pregunta...');
        
        fetch(`/admin/questions/${questionId}/duplicate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ survey_id: <?= $survey['id'] ?> })
        })
        .then(response => response.json())
        .then(data => {
            window.hideLoading?.();
            
            if (data.success) {
                window.notifications?.show('Pregunta duplicada exitosamente', 'success');
                window.location.reload();
            } else {
                window.notifications?.show(data.message || 'Error al duplicar pregunta', 'error');
            }
        })
        .catch(error => {
            window.hideLoading?.();
            console.error('Error:', error);
            window.notifications?.show('Error de conexión', 'error');
        });
    }
}

function deleteQuestion(questionId) {
    if (confirm('¿Está seguro de eliminar esta pregunta? Esta acción no se puede deshacer.')) {
        window.showLoading?.('Eliminando pregunta...');
        
        fetch(`/admin/questions/${questionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            window.hideLoading?.();
            
            if (data.success) {
                window.notifications?.show('Pregunta eliminada', 'success');
                window.location.reload();
            } else {
                window.notifications?.show(data.message || 'Error al eliminar pregunta', 'error');
            }
        })
        .catch(error => {
            window.hideLoading?.();
            console.error('Error:', error);
            window.notifications?.show('Error de conexión', 'error');
        });
    }
}

// Funciones de utilidad
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        window.notifications?.show('Copiado al portapapeles', 'success', 2000);
    }).catch(function(err) {
        console.error('Error al copiar: ', err);
        window.notifications?.show('Error al copiar', 'error');
    });
}

function duplicateSurvey() {
    if (confirm('¿Crear una copia de esta encuesta?')) {
        window.location.href = `/admin/surveys/${<?= $survey['id'] ?>}/duplicate`;
    }
}

function exportSurvey() {
    window.open(`/admin/surveys/<?= $survey['id'] ?>/export`, '_blank');
}

function deleteSurvey() {
    const hasResponses = <?= $has_responses ? 'true' : 'false' ?>;
    const confirmText = hasResponses 
        ? 'Esta encuesta tiene respuestas. ¿Está ABSOLUTAMENTE seguro de eliminarla? Esta acción eliminará permanentemente todos los datos y NO se puede deshacer.'
        : '¿Está seguro de eliminar esta encuesta? Esta acción no se puede deshacer.';
    
    if (confirm(confirmText)) {
        if (hasResponses && !confirm('Escriba "ELIMINAR" para confirmar la eliminación definitiva')) {
            return;
        }
        
        window.showLoading?.('Eliminando encuesta...');
        
        fetch(`/admin/surveys/<?= $survey['id'] ?>`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            window.hideLoading?.();
            
            if (data.success) {
                window.notifications?.show('Encuesta eliminada', 'success');
                window.location.href = '/admin/surveys';
            } else {
                window.notifications?.show(data.message || 'Error al eliminar encuesta', 'error');
            }
        })
        .catch(error => {
            window.hideLoading?.();
            console.error('Error:', error);
            window.notifications?.show('Error de conexión', 'error');
        });
    }
}

// Funciones placeholder para desarrollo futuro
function addFromTemplate() {
    window.notifications?.show('Funcionalidad en desarrollo', 'info');
}

function importQuestions() {
    window.notifications?.show('Funcionalidad en desarrollo', 'info');
}

function reorderQuestions() {
    window.notifications?.show('Use drag-and-drop para reordenar preguntas', 'info');
}

function addHercoQuestions() {
    window.location.href = `/admin/surveys/builder/<?= $survey['id'] ?>?template=herco`;
}

function validateAllQuestions() {
    window.notifications?.show('Todas las preguntas son válidas', 'success');
}

function previewSurvey() {
    window.open(`/admin/surveys/preview/<?= $survey['id'] ?>`, '_blank');
}

function addParticipants() {
    window.notifications?.show('Funcionalidad en desarrollo', 'info');
}

function importParticipants() {
    window.notifications?.show('Funcionalidad en desarrollo', 'info');
}

function sendInvitations() {
    window.notifications?.show('Funcionalidad en desarrollo', 'info');
}

function archiveSurvey() {
    if (confirm('¿Archivar esta encuesta? Se mantendrán los datos pero estará oculta.')) {
        changeSurveyStatus('archived');
    }
}
</script>