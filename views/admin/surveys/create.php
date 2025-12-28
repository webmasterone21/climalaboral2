<?php
/**
 * Vista: Crear Nueva Encuesta - Sistema HERCO v2.0
 * 
 * Formulario wizard para creación de encuestas con:
 * - Configuración básica
 * - Selección de plantillas HERCO
 * - Configuración de participantes
 * - Previsualización
 */

// Configuración de la página
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Encuestas', 'url' => '/admin/surveys'],
    ['label' => 'Nueva Encuesta', 'url' => '/admin/surveys/create', 'active' => true]
];

// Obtener datos de la vista
$templates = $templates ?? [];
$categories = $categories ?? [];
$participants = $participants ?? [];
$survey_types = $survey_types ?? [];
$default_settings = $default_settings ?? [];
?>

<!-- Encabezado de página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">
            <i class="fas fa-plus-circle text-primary me-2"></i>
            Nueva Encuesta de Clima Laboral
        </h1>
        <p class="text-muted mb-0">
            Cree una nueva encuesta utilizando las plantillas HERCO 2024
        </p>
    </div>
    
    <div class="d-flex gap-2">
        <a href="/admin/surveys" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver
        </a>
        
        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#templateModal">
            <i class="fas fa-eye me-1"></i>
            Ver Plantillas
        </button>
    </div>
</div>

<!-- Indicador de progreso del wizard -->
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="progress-wizard">
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 25%" id="wizard-progress"></div>
                    </div>
                    <div class="wizard-steps d-flex justify-content-between mt-2">
                        <div class="wizard-step active" data-step="1">
                            <div class="step-circle">1</div>
                            <div class="step-label">Información Básica</div>
                        </div>
                        <div class="wizard-step" data-step="2">
                            <div class="step-circle">2</div>
                            <div class="step-label">Configuración</div>
                        </div>
                        <div class="wizard-step" data-step="3">
                            <div class="step-circle">3</div>
                            <div class="step-label">Participantes</div>
                        </div>
                        <div class="wizard-step" data-step="4">
                            <div class="step-circle">4</div>
                            <div class="step-label">Revisión</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulario principal -->
<form id="create-survey-form" action="/admin/surveys/create" method="POST" novalidate>
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <!-- PASO 1: Información Básica -->
    <div class="wizard-content" id="step-1">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Información Básica de la Encuesta
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Título de la encuesta -->
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
                                placeholder="Ej: Encuesta de Clima Laboral 2024 - Primer Semestre"
                                maxlength="200"
                                required
                                autofocus
                            >
                            <div class="form-text">
                                Un título claro y descriptivo ayuda a los participantes a entender el propósito
                            </div>
                            <div class="invalid-feedback"></div>
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
                                placeholder="Describa el objetivo de esta encuesta y por qué es importante la participación..."
                                maxlength="1000"
                                required
                            ></textarea>
                            <div class="form-text">
                                <span id="description-count">0</span>/1000 caracteres
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <!-- Tipo de encuesta -->
                        <div class="mb-4">
                            <label for="type" class="form-label required">
                                <i class="fas fa-tag me-1"></i>
                                Tipo de Encuesta
                            </label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Seleccione el tipo de encuesta</option>
                                <?php foreach ($survey_types as $type_key => $type_label): ?>
                                <option value="<?= $type_key ?>">
                                    <?= htmlspecialchars($type_label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <!-- Plantilla base -->
                        <div class="mb-4">
                            <label for="template_id" class="form-label">
                                <i class="fas fa-layer-group me-1"></i>
                                Plantilla Base
                            </label>
                            <select class="form-select" id="template_id" name="template_id">
                                <option value="blank">Encuesta en Blanco</option>
                                <option value="herco_standard">Plantilla HERCO Estándar (18 Categorías)</option>
                                <option value="herco_quick">Plantilla HERCO Rápida (10 Categorías)</option>
                                <option value="herco_comprehensive">Plantilla HERCO Completa (25 Categorías)</option>
                                <?php foreach ($templates as $template): ?>
                                <option value="<?= $template['id'] ?>">
                                    <?= htmlspecialchars($template['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Las plantillas HERCO incluyen preguntas predefinidas basadas en metodología oficial
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Panel de ayuda -->
                <div class="card bg-light">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            Consejos para el Título
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Use nombres descriptivos y fechas
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Incluya el período (ej: Q1 2024)
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Mantenga entre 30-80 caracteres
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                Evite caracteres especiales
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Vista previa del título -->
                <div class="card mt-3" id="title-preview" style="display: none;">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-eye text-info me-2"></i>
                            Vista Previa
                        </h6>
                    </div>
                    <div class="card-body">
                        <h5 id="preview-title" class="text-primary"></h5>
                        <p id="preview-description" class="text-muted small"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PASO 2: Configuración -->
    <div class="wizard-content d-none" id="step-2">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs text-primary me-2"></i>
                            Configuración de la Encuesta
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Fechas de la encuesta -->
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
                                    min="<?= date('Y-m-d\TH:i') ?>"
                                    required
                                >
                                <div class="invalid-feedback"></div>
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
                                    required
                                >
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Configuraciones de acceso -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-shield-alt text-warning me-2"></i>
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
                                            checked
                                        >
                                        <label class="form-check-label" for="is_anonymous">
                                            <strong>Encuesta Anónima</strong>
                                            <div class="text-muted small">Las respuestas no se asociarán con participantes específicos</div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            id="allow_multiple_responses" 
                                            name="allow_multiple_responses"
                                        >
                                        <label class="form-check-label" for="allow_multiple_responses">
                                            <strong>Múltiples Respuestas</strong>
                                            <div class="text-muted small">Permitir que un participante responda más de una vez</div>
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
                                            id="show_progress" 
                                            name="show_progress"
                                            checked
                                        >
                                        <label class="form-check-label" for="show_progress">
                                            <strong>Mostrar Progreso</strong>
                                            <div class="text-muted small">Barra de progreso visible para participantes</div>
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
                                            checked
                                        >
                                        <label class="form-check-label" for="auto_save">
                                            <strong>Guardado Automático</strong>
                                            <div class="text-muted small">Guardar respuestas automáticamente mientras se completa</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categorías HERCO -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-tags text-primary me-2"></i>
                                Categorías HERCO a Evaluar
                                <span class="badge bg-info ms-2" id="selected-categories-count">0 de 18</span>
                            </h6>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Seleccione las categorías HERCO que desea incluir en esta encuesta. Se recomienda usar al menos 8-12 categorías para un análisis completo.
                            </div>
                            
                            <div class="row" id="herco-categories">
                                <?php foreach ($categories as $category): ?>
                                <div class="col-lg-6 mb-2">
                                    <div class="form-check category-item">
                                        <input 
                                            class="form-check-input category-checkbox" 
                                            type="checkbox" 
                                            id="category_<?= $category['id'] ?>" 
                                            name="categories[]" 
                                            value="<?= $category['id'] ?>"
                                            data-category-name="<?= htmlspecialchars($category['name']) ?>"
                                        >
                                        <label class="form-check-label d-flex align-items-center" for="category_<?= $category['id'] ?>">
                                            <div 
                                                class="category-color me-2" 
                                                style="background-color: <?= $category['color'] ?>; width: 16px; height: 16px; border-radius: 50%;"
                                            ></div>
                                            <div>
                                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                                                <div class="text-muted small"><?= htmlspecialchars($category['description']) ?></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Acciones rápidas para categorías -->
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllCategories()">
                                    <i class="fas fa-check-double me-1"></i>
                                    Seleccionar Todas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllCategories()">
                                    <i class="fas fa-times me-1"></i>
                                    Limpiar Selección
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="selectRecommendedCategories()">
                                    <i class="fas fa-star me-1"></i>
                                    Selección Recomendada
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Configuración recomendada -->
                <div class="card bg-light">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-thumbs-up text-success me-2"></i>
                            Configuración Recomendada
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <strong>Duración:</strong> 2-4 semanas
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-user-secret text-warning me-2"></i>
                                <strong>Anonimato:</strong> Activado
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-chart-bar text-info me-2"></i>
                                <strong>Progreso:</strong> Visible
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-save text-success me-2"></i>
                                <strong>Auto-guardado:</strong> Activado
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-tags text-primary me-2"></i>
                                <strong>Categorías:</strong> 8-12 seleccionadas
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Calculadora de tiempo -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-clock text-info me-2"></i>
                            Estimación de Tiempo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="h4 text-primary mb-1" id="estimated-time">5-8 min</div>
                            <div class="text-muted small">Tiempo estimado por participante</div>
                        </div>
                        <hr>
                        <div class="small">
                            <div class="d-flex justify-content-between">
                                <span>Categorías seleccionadas:</span>
                                <span id="time-categories">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Preguntas estimadas:</span>
                                <span id="time-questions">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PASO 3: Participantes -->
    <div class="wizard-content d-none" id="step-3">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users text-primary me-2"></i>
                            Selección de Participantes
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Modo de selección -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">¿Quién puede participar en esta encuesta?</h6>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="radio" 
                                            id="all_participants" 
                                            name="participant_mode" 
                                            value="all"
                                            checked
                                        >
                                        <label class="form-check-label" for="all_participants">
                                            <strong>Todos los Empleados</strong>
                                            <div class="text-muted small">Toda la organización</div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="radio" 
                                            id="selected_participants" 
                                            name="participant_mode" 
                                            value="selected"
                                        >
                                        <label class="form-check-label" for="selected_participants">
                                            <strong>Selección Manual</strong>
                                            <div class="text-muted small">Elegir participantes específicos</div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="radio" 
                                            id="department_participants" 
                                            name="participant_mode" 
                                            value="department"
                                        >
                                        <label class="form-check-label" for="department_participants">
                                            <strong>Por Departamento</strong>
                                            <div class="text-muted small">Filtrar por área</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Panel de selección específica -->
                        <div id="specific-selection" class="d-none">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-search me-1"></i>
                                    Buscar Participantes
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="participant-search"
                                    placeholder="Buscar por nombre, email o departamento..."
                                >
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" class="form-check-input" id="select-all-participants">
                                            </th>
                                            <th>Participante</th>
                                            <th>Departamento</th>
                                            <th>Posición</th>
                                        </tr>
                                    </thead>
                                    <tbody id="participants-list">
                                        <?php foreach ($participants as $participant): ?>
                                        <tr>
                                            <td>
                                                <input 
                                                    type="checkbox" 
                                                    class="form-check-input participant-checkbox" 
                                                    name="participants[]" 
                                                    value="<?= $participant['id'] ?>"
                                                >
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($participant['name']) ?></strong>
                                                    <div class="text-muted small"><?= htmlspecialchars($participant['email']) ?></div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($participant['department'] ?? 'Sin asignar') ?></td>
                                            <td><?= htmlspecialchars($participant['position'] ?? 'Sin especificar') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Resumen de participantes -->
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-3"></i>
                                <div>
                                    <strong>Participantes seleccionados: <span id="participant-count">0</span></strong>
                                    <div class="small">Se enviarán invitaciones por email una vez que la encuesta esté activa</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Estadísticas de participantes -->
                <div class="card bg-light">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie text-primary me-2"></i>
                            Estadísticas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4 text-primary mb-1"><?= count($participants) ?></div>
                                <div class="text-muted small">Total Empleados</div>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-success mb-1" id="selected-participants">0</div>
                                <div class="text-muted small">Seleccionados</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recordatorios -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bell text-warning me-2"></i>
                            Configuración de Recordatorios
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="send_reminders" 
                                name="send_reminders"
                                checked
                            >
                            <label class="form-check-label" for="send_reminders">
                                <strong>Enviar Recordatorios</strong>
                                <div class="text-muted small">Emails automáticos para aumentar participación</div>
                            </label>
                        </div>
                        
                        <div id="reminder-settings">
                            <div class="mb-2">
                                <label for="reminder_frequency" class="form-label small">Frecuencia</label>
                                <select class="form-select form-select-sm" id="reminder_frequency" name="reminder_frequency">
                                    <option value="3">Cada 3 días</option>
                                    <option value="7" selected>Cada 7 días</option>
                                    <option value="14">Cada 14 días</option>
                                </select>
                            </div>
                            
                            <div class="mb-2">
                                <label for="max_reminders" class="form-label small">Máximo de recordatorios</label>
                                <select class="form-select form-select-sm" id="max_reminders" name="max_reminders">
                                    <option value="1">1 recordatorio</option>
                                    <option value="2" selected>2 recordatorios</option>
                                    <option value="3">3 recordatorios</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PASO 4: Revisión -->
    <div class="wizard-content d-none" id="step-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Revisión Final
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Resumen de la encuesta -->
                        <div id="survey-summary">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                        
                        <!-- Acciones finales -->
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Antes de crear la encuesta
                            </h6>
                            <ul class="mb-0">
                                <li>Verifique que toda la información sea correcta</li>
                                <li>Una vez creada, algunas configuraciones no podrán modificarse</li>
                                <li>Los participantes recibirán invitaciones automáticamente si la encuesta se activa</li>
                            </ul>
                        </div>
                        
                        <!-- Opciones de creación -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">¿Qué desea hacer después de crear la encuesta?</h6>
                            
                            <div class="form-check mb-2">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    id="create_draft" 
                                    name="initial_status" 
                                    value="draft"
                                    checked
                                >
                                <label class="form-check-label" for="create_draft">
                                    <strong>Guardar como Borrador</strong>
                                    <div class="text-muted small">Permitir ediciones antes de activar</div>
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    id="create_active" 
                                    name="initial_status" 
                                    value="active"
                                >
                                <label class="form-check-label" for="create_active">
                                    <strong>Activar Inmediatamente</strong>
                                    <div class="text-muted small">Comenzar a recibir respuestas de inmediato</div>
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    id="create_scheduled" 
                                    name="initial_status" 
                                    value="scheduled"
                                >
                                <label class="form-check-label" for="create_scheduled">
                                    <strong>Programar Activación</strong>
                                    <div class="text-muted small">Activar automáticamente en la fecha de inicio</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Checklist final -->
                <div class="card bg-light">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-list-check text-success me-2"></i>
                            Checklist Final
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="checklist">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check-title">
                                <label class="form-check-label small" for="check-title">
                                    Título y descripción completados
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check-dates">
                                <label class="form-check-label small" for="check-dates">
                                    Fechas de inicio y fin configuradas
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check-categories">
                                <label class="form-check-label small" for="check-categories">
                                    Al menos 3 categorías seleccionadas
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="check-participants">
                                <label class="form-check-label small" for="check-participants">
                                    Participantes definidos
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Próximos pasos -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-forward text-info me-2"></i>
                            Próximos Pasos
                        </h6>
                    </div>
                    <div class="card-body">
                        <ol class="mb-0 small">
                            <li>Agregar preguntas personalizadas</li>
                            <li>Revisar configuraciones avanzadas</li>
                            <li>Probar la encuesta con usuarios piloto</li>
                            <li>Activar y monitorear participación</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botones de navegación -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="prev-step" style="display: none;">
                    <i class="fas fa-arrow-left me-1"></i>
                    Anterior
                </button>
                
                <div class="ms-auto">
                    <button type="button" class="btn btn-primary" id="next-step">
                        Siguiente
                        <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    
                    <button type="submit" class="btn btn-success d-none" id="create-survey">
                        <i class="fas fa-plus me-1"></i>
                        Crear Encuesta
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal de plantillas -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group me-2"></i>
                    Plantillas Disponibles
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">HERCO Estándar</h6>
                                <p class="card-text small">18 categorías oficiales con 3-5 preguntas por categoría. Ideal para evaluaciones completas.</p>
                                <span class="badge bg-primary">54-90 preguntas</span>
                                <span class="badge bg-info">10-15 min</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">HERCO Rápida</h6>
                                <p class="card-text small">10 categorías principales con 2-3 preguntas por categoría. Para evaluaciones ágiles.</p>
                                <span class="badge bg-primary">20-30 preguntas</span>
                                <span class="badge bg-info">5-8 min</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">HERCO Completa</h6>
                                <p class="card-text small">25 categorías extendidas con preguntas detalladas. Para análisis exhaustivos.</p>
                                <span class="badge bg-primary">75-125 preguntas</span>
                                <span class="badge bg-info">15-25 min</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS específico del wizard -->
<style>
.wizard-step {
    text-align: center;
    position: relative;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin: 0 auto 8px;
    transition: all 0.3s ease;
}

.wizard-step.active .step-circle {
    background: var(--herco-primary);
    color: white;
}

.wizard-step.completed .step-circle {
    background: var(--herco-success);
    color: white;
}

.step-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.wizard-step.active .step-label {
    color: var(--herco-primary);
    font-weight: 600;
}

.category-item {
    padding: 8px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.category-item:hover {
    background-color: rgba(40, 167, 69, 0.05);
}

.category-item .form-check-input:checked + .form-check-label {
    color: var(--herco-primary);
}

.required::after {
    content: " *";
    color: #dc3545;
}
</style>

<!-- JavaScript del wizard -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSurveyWizard();
    console.log('📝 Formulario de Creación HERCO v2.0 Cargado');
});

function initializeSurveyWizard() {
    let currentStep = 1;
    const totalSteps = 4;
    
    // Elementos del DOM
    const prevBtn = document.getElementById('prev-step');
    const nextBtn = document.getElementById('next-step');
    const createBtn = document.getElementById('create-survey');
    const progressBar = document.getElementById('wizard-progress');
    
    // Inicializar validación
    const validator = new FormValidator('create-survey-form', {
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
        type: {
            required: true,
            message: 'Seleccione el tipo de encuesta'
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
    
    // Event listeners
    nextBtn.addEventListener('click', () => nextStep());
    prevBtn.addEventListener('click', () => prevStep());
    
    // Inicializar funcionalidades específicas
    initializeStepOne();
    initializeStepTwo();
    initializeStepThree();
    initializeStepFour();
    
    function nextStep() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizard();
            }
        }
    }
    
    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateWizard();
        }
    }
    
    function updateWizard() {
        // Ocultar todos los pasos
        document.querySelectorAll('.wizard-content').forEach(content => {
            content.classList.add('d-none');
        });
        
        // Mostrar paso actual
        document.getElementById(`step-${currentStep}`).classList.remove('d-none');
        
        // Actualizar indicadores
        document.querySelectorAll('.wizard-step').forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index + 1 === currentStep) {
                step.classList.add('active');
            } else if (index + 1 < currentStep) {
                step.classList.add('completed');
            }
        });
        
        // Actualizar barra de progreso
        const progress = (currentStep / totalSteps) * 100;
        progressBar.style.width = `${progress}%`;
        
        // Actualizar botones
        prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
        
        if (currentStep === totalSteps) {
            nextBtn.classList.add('d-none');
            createBtn.classList.remove('d-none');
        } else {
            nextBtn.classList.remove('d-none');
            createBtn.classList.add('d-none');
        }
        
        // Scroll al top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    function validateCurrentStep() {
        switch (currentStep) {
            case 1:
                return validateStepOne();
            case 2:
                return validateStepTwo();
            case 3:
                return validateStepThree();
            case 4:
                return true; // Paso de revisión
            default:
                return true;
        }
    }
    
    function validateStepOne() {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const type = document.getElementById('type').value;
        
        if (!title || title.length < 5) {
            window.notifications?.show('El título debe tener al menos 5 caracteres', 'error');
            document.getElementById('title').focus();
            return false;
        }
        
        if (!description || description.length < 10) {
            window.notifications?.show('La descripción debe tener al menos 10 caracteres', 'error');
            document.getElementById('description').focus();
            return false;
        }
        
        if (!type) {
            window.notifications?.show('Seleccione el tipo de encuesta', 'error');
            document.getElementById('type').focus();
            return false;
        }
        
        return true;
    }
    
    function validateStepTwo() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const selectedCategories = document.querySelectorAll('.category-checkbox:checked');
        
        if (!startDate) {
            window.notifications?.show('Seleccione la fecha de inicio', 'error');
            document.getElementById('start_date').focus();
            return false;
        }
        
        if (!endDate) {
            window.notifications?.show('Seleccione la fecha de finalización', 'error');
            document.getElementById('end_date').focus();
            return false;
        }
        
        // Validar fechas
        const start = new Date(startDate);
        const end = new Date(endDate);
        const now = new Date();
        
        if (start >= end) {
            window.notifications?.show('La fecha de inicio debe ser anterior a la fecha de fin', 'error');
            document.getElementById('start_date').focus();
            return false;
        }
        
        if (selectedCategories.length < 3) {
            window.notifications?.show('Seleccione al menos 3 categorías HERCO', 'error');
            document.querySelector('#herco-categories').scrollIntoView({ behavior: 'smooth' });
            return false;
        }
        
        return true;
    }
    
    function validateStepThree() {
        const participantMode = document.querySelector('input[name="participant_mode"]:checked').value;
        
        if (participantMode === 'selected') {
            const selectedParticipants = document.querySelectorAll('.participant-checkbox:checked');
            if (selectedParticipants.length === 0) {
                window.notifications?.show('Seleccione al menos un participante', 'error');
                return false;
            }
        }
        
        return true;
    }
}

// Inicializar funcionalidades del paso 1
function initializeStepOne() {
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const previewTitle = document.getElementById('preview-title');
    const previewDescription = document.getElementById('preview-description');
    const titlePreview = document.getElementById('title-preview');
    const descriptionCount = document.getElementById('description-count');
    
    // Vista previa en tiempo real
    titleInput.addEventListener('input', function() {
        const title = this.value.trim();
        if (title) {
            previewTitle.textContent = title;
            titlePreview.style.display = 'block';
        } else {
            titlePreview.style.display = 'none';
        }
    });
    
    descriptionInput.addEventListener('input', function() {
        const description = this.value.trim();
        const count = description.length;
        
        descriptionCount.textContent = count;
        
        if (description && previewTitle.textContent) {
            previewDescription.textContent = description.substring(0, 150) + (description.length > 150 ? '...' : '');
        }
    });
}

// Inicializar funcionalidades del paso 2
function initializeStepTwo() {
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const selectedCount = document.getElementById('selected-categories-count');
    const timeCategories = document.getElementById('time-categories');
    const timeQuestions = document.getElementById('time-questions');
    const estimatedTime = document.getElementById('estimated-time');
    
    // Actualizar contador de categorías
    function updateCategoryCount() {
        const selected = document.querySelectorAll('.category-checkbox:checked').length;
        selectedCount.textContent = `${selected} de 18`;
        timeCategories.textContent = selected;
        
        // Estimar preguntas y tiempo
        const estimatedQuestions = selected * 3; // Promedio 3 preguntas por categoría
        timeQuestions.textContent = estimatedQuestions;
        
        const minutes = Math.ceil(estimatedQuestions * 0.3); // 0.3 min por pregunta
        estimatedTime.textContent = `${Math.max(3, minutes - 2)}-${minutes + 2} min`;
    }
    
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCategoryCount);
    });
    
    // Validación de fechas
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.addEventListener('change', function() {
        if (this.value) {
            const minEndDate = new Date(this.value);
            minEndDate.setDate(minEndDate.getDate() + 1);
            endDateInput.min = minEndDate.toISOString().slice(0, 16);
        }
    });
    
    updateCategoryCount();
}

// Inicializar funcionalidades del paso 3
function initializeStepThree() {
    const participantModeRadios = document.querySelectorAll('input[name="participant_mode"]');
    const specificSelection = document.getElementById('specific-selection');
    const participantCheckboxes = document.querySelectorAll('.participant-checkbox');
    const participantCount = document.getElementById('participant-count');
    const selectedParticipants = document.getElementById('selected-participants');
    
    // Cambio de modo de participantes
    participantModeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'selected') {
                specificSelection.classList.remove('d-none');
            } else {
                specificSelection.classList.add('d-none');
            }
            updateParticipantCount();
        });
    });
    
    // Contador de participantes
    function updateParticipantCount() {
        const mode = document.querySelector('input[name="participant_mode"]:checked').value;
        let count = 0;
        
        if (mode === 'all') {
            count = <?= count($participants) ?>;
        } else if (mode === 'selected') {
            count = document.querySelectorAll('.participant-checkbox:checked').length;
        }
        
        participantCount.textContent = count;
        selectedParticipants.textContent = count;
    }
    
    participantCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateParticipantCount);
    });
    
    // Seleccionar todos los participantes
    document.getElementById('select-all-participants')?.addEventListener('change', function() {
        participantCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateParticipantCount();
    });
    
    updateParticipantCount();
}

// Inicializar funcionalidades del paso 4
function initializeStepFour() {
    // Se implementará la generación del resumen dinámico
}

// Funciones de utilidad para categorías
function selectAllCategories() {
    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selected-categories-count').textContent = '18 de 18';
}

function clearAllCategories() {
    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selected-categories-count').textContent = '0 de 18';
}

function selectRecommendedCategories() {
    // Categorías recomendadas HERCO (las más importantes)
    const recommended = ['1', '2', '3', '6', '7', '8', '12', '15', '16', '18']; // IDs de ejemplo
    
    clearAllCategories();
    
    recommended.forEach(categoryId => {
        const checkbox = document.getElementById(`category_${categoryId}`);
        if (checkbox) checkbox.checked = true;
    });
    
    document.getElementById('selected-categories-count').textContent = `${recommended.length} de 18`;
}
</script>