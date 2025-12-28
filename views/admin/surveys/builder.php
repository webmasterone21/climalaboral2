<?php
/**
 * Vista: Constructor Visual de Encuestas - Sistema HERCO v2.0
 * 
 * Constructor drag-and-drop profesional para crear encuestas HERCO
 * con 18 categorías oficiales, tipos de preguntas avanzados y preview en tiempo real
 * 
 * @package HERCO\Views\Admin\Surveys
 * @version 2.0.0
 * @author Sistema HERCO
 */

// Configuración de la página
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Encuestas', 'url' => '/admin/surveys'],
    ['label' => 'Editar', 'url' => "/admin/surveys/edit/{$survey['id']}"],
    ['label' => 'Constructor', 'url' => "/admin/surveys/builder/{$survey['id']}", 'active' => true]
];

// Datos de la vista
$survey = $survey ?? ['id' => 0, 'title' => 'Nueva Encuesta'];
$questions = $questions ?? [];
$categories = $categories ?? [];
$question_types = $question_types ?? [];
$templates = $templates ?? [];

// Configuraciones HERCO 2024
$herco_categories = [
    'satisfaccion' => ['name' => 'Satisfacción Laboral', 'color' => '#e74c3c', 'icon' => 'fas fa-heart'],
    'participacion' => ['name' => 'Participación y Autonomía', 'color' => '#3498db', 'icon' => 'fas fa-users'],
    'comunicacion' => ['name' => 'Comunicación y Objetivos', 'color' => '#2ecc71', 'icon' => 'fas fa-comments'],
    'equilibrio' => ['name' => 'Equilibrio y Evaluación', 'color' => '#f39c12', 'icon' => 'fas fa-balance-scale'],
    'distribucion' => ['name' => 'Distribución y Carga de Trabajo', 'color' => '#9b59b6', 'icon' => 'fas fa-tasks'],
    'reconocimiento' => ['name' => 'Reconocimiento y Promoción', 'color' => '#1abc9c', 'icon' => 'fas fa-trophy'],
    'ambiente' => ['name' => 'Ambiente de Trabajo', 'color' => '#34495e', 'icon' => 'fas fa-building'],
    'capacitacion' => ['name' => 'Capacitación', 'color' => '#e67e22', 'icon' => 'fas fa-graduation-cap'],
    'tecnologia' => ['name' => 'Tecnología y Recursos', 'color' => '#95a5a6', 'icon' => 'fas fa-laptop'],
    'colaboracion' => ['name' => 'Colaboración y Compañerismo', 'color' => '#f1c40f', 'icon' => 'fas fa-handshake'],
    'normativas' => ['name' => 'Normativas y Regulaciones', 'color' => '#8e44ad', 'icon' => 'fas fa-gavel'],
    'compensacion' => ['name' => 'Compensación y Beneficios', 'color' => '#27ae60', 'icon' => 'fas fa-dollar-sign'],
    'bienestar' => ['name' => 'Bienestar y Salud', 'color' => '#e91e63', 'icon' => 'fas fa-spa'],
    'seguridad' => ['name' => 'Seguridad en el Trabajo', 'color' => '#ff5722', 'icon' => 'fas fa-shield-alt'],
    'informacion' => ['name' => 'Información y Comunicación', 'color' => '#607d8b', 'icon' => 'fas fa-info-circle'],
    'supervisores' => ['name' => 'Relaciones con Supervisores', 'color' => '#795548', 'icon' => 'fas fa-user-tie'],
    'feedback' => ['name' => 'Feedback y Reconocimiento', 'color' => '#009688', 'icon' => 'fas fa-comment-dots'],
    'diversidad' => ['name' => 'Diversidad e Inclusión', 'color' => '#673ab7', 'icon' => 'fas fa-globe']
];

$question_types_config = [
    'likert_5' => ['name' => 'Escala Likert 1-5', 'icon' => 'fas fa-star', 'description' => 'Muy en desacuerdo a Muy de acuerdo'],
    'likert_7' => ['name' => 'Escala Likert 1-7', 'icon' => 'fas fa-chart-bar', 'description' => 'Escala extendida 1-7'],
    'likert_3' => ['name' => 'Escala Likert 1-3', 'icon' => 'fas fa-thumbs-up', 'description' => 'Básica: Malo, Regular, Bueno'],
    'multiple_choice' => ['name' => 'Opción Múltiple', 'icon' => 'fas fa-list-ul', 'description' => 'Selección única'],
    'checkbox' => ['name' => 'Casillas de Verificación', 'icon' => 'fas fa-check-square', 'description' => 'Selección múltiple'],
    'text' => ['name' => 'Texto Corto', 'icon' => 'fas fa-font', 'description' => 'Respuesta de texto'],
    'textarea' => ['name' => 'Texto Largo', 'icon' => 'fas fa-align-left', 'description' => 'Comentarios extensos'],
    'rating' => ['name' => 'Calificación', 'icon' => 'fas fa-star-half-alt', 'description' => 'Estrellas 1-5'],
    'slider' => ['name' => 'Deslizador', 'icon' => 'fas fa-sliders-h', 'description' => 'Valor en rango'],
    'yes_no' => ['name' => 'Sí/No', 'icon' => 'fas fa-toggle-on', 'description' => 'Respuesta binaria'],
    'date' => ['name' => 'Fecha', 'icon' => 'fas fa-calendar', 'description' => 'Selector de fecha'],
    'time' => ['name' => 'Hora', 'icon' => 'fas fa-clock', 'description' => 'Selector de hora'],
    'email' => ['name' => 'Email', 'icon' => 'fas fa-envelope', 'description' => 'Correo electrónico'],
    'number' => ['name' => 'Número', 'icon' => 'fas fa-hashtag', 'description' => 'Valor numérico'],
    'phone' => ['name' => 'Teléfono', 'icon' => 'fas fa-phone', 'description' => 'Número telefónico'],
    'url' => ['name' => 'URL', 'icon' => 'fas fa-link', 'description' => 'Dirección web'],
    'file' => ['name' => 'Archivo', 'icon' => 'fas fa-file-upload', 'description' => 'Subir archivo'],
    'matrix' => ['name' => 'Matriz', 'icon' => 'fas fa-table', 'description' => 'Tabla de respuestas'],
    'ranking' => ['name' => 'Ranking', 'icon' => 'fas fa-sort-numeric-down', 'description' => 'Ordenar por prioridad'],
    'nps' => ['name' => 'Net Promoter Score', 'icon' => 'fas fa-chart-line', 'description' => 'Escala 0-10 NPS']
];
?>

<!-- Meta tags específicos para el constructor -->
<meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.css">
<style>
/* Estilos específicos del constructor */
.builder-container {
    min-height: calc(100vh - 200px);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-attachment: fixed;
}

.builder-sidebar {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
}

.builder-canvas {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    min-height: 600px;
}

.question-type-item {
    cursor: grab;
    transition: all 0.3s ease;
    border: 2px dashed transparent;
}

.question-type-item:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}

.question-item {
    border: 2px solid #e3e6f0;
    border-radius: 10px;
    background: white;
    transition: all 0.3s ease;
    position: relative;
}

.question-item:hover {
    border-color: #667eea;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

.question-item.editing {
    border-color: #28a745;
    box-shadow: 0 5px 20px rgba(40, 167, 69, 0.2);
}

.sortable-ghost {
    opacity: 0.5;
    background: #f8f9fc;
    border: 2px dashed #667eea;
}

.sortable-chosen {
    transform: rotate(5deg);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    color: white;
    margin: 2px;
    transition: all 0.3s ease;
}

.category-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.preview-panel {
    background: #f8f9fc;
    border-radius: 10px;
    border: 2px dashed #dee2e6;
    min-height: 300px;
    position: relative;
    overflow: hidden;
}

.preview-content {
    padding: 20px;
}

.builder-toolbar {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.question-counter {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 8px 16px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.template-item {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
}

.template-item:hover {
    border-color: #667eea;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.auto-save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(40, 167, 69, 0.9);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 0.9rem;
    display: none;
    z-index: 1000;
    backdrop-filter: blur(10px);
}

.auto-save-indicator.saving {
    background: rgba(255, 193, 7, 0.9);
    display: block;
}

.auto-save-indicator.saved {
    background: rgba(40, 167, 69, 0.9);
    display: block;
}

.question-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: none;
    gap: 5px;
}

.question-item:hover .question-actions {
    display: flex;
}

.action-btn {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.action-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.action-btn.edit { color: #007bff; }
.action-btn.duplicate { color: #28a745; }
.action-btn.delete { color: #dc3545; }
.action-btn.drag { color: #6c757d; cursor: grab; }

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .builder-sidebar {
        position: static;
        margin-bottom: 20px;
        max-height: none;
    }
    
    .question-actions {
        position: static;
        display: flex;
        justify-content: center;
        margin-top: 15px;
    }
}
</style>

<div class="container-fluid py-4 builder-container">
    <!-- Indicador de auto-guardado -->
    <div id="autoSaveIndicator" class="auto-save-indicator">
        <i class="fas fa-spinner fa-spin"></i> Guardando...
    </div>

    <div class="row">
        <!-- Sidebar: Herramientas y tipos de preguntas -->
        <div class="col-lg-3">
            <div class="builder-sidebar p-4">
                <!-- Header del constructor -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="mb-0">
                        <i class="fas fa-magic text-primary"></i>
                        Constructor HERCO
                    </h5>
                    <div class="question-counter">
                        <i class="fas fa-list"></i>
                        <span id="questionCount"><?= count($questions) ?></span>
                    </div>
                </div>

                <!-- Pestañas del sidebar -->
                <ul class="nav nav-pills nav-fill mb-4" id="builderTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="types-tab" data-bs-toggle="pill" data-bs-target="#types-panel" type="button" role="tab">
                            <i class="fas fa-puzzle-piece"></i><br>
                            <small>Tipos</small>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="templates-tab" data-bs-toggle="pill" data-bs-target="#templates-panel" type="button" role="tab">
                            <i class="fas fa-layer-group"></i><br>
                            <small>Plantillas</small>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="categories-tab" data-bs-toggle="pill" data-bs-target="#categories-panel" type="button" role="tab">
                            <i class="fas fa-tags"></i><br>
                            <small>Categorías</small>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="builderTabsContent">
                    <!-- Panel: Tipos de Preguntas -->
                    <div class="tab-pane fade show active" id="types-panel" role="tabpanel">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-info-circle"></i>
                            Arrastra para agregar
                        </h6>
                        
                        <div id="questionTypes" class="question-types-list">
                            <?php foreach ($question_types_config as $type_key => $type): ?>
                            <div class="question-type-item card mb-2 p-3" 
                                 data-type="<?= $type_key ?>"
                                 data-name="<?= htmlspecialchars($type['name']) ?>"
                                 data-description="<?= htmlspecialchars($type['description']) ?>">
                                <div class="d-flex align-items-center">
                                    <i class="<?= $type['icon'] ?> fa-lg me-3 text-primary"></i>
                                    <div>
                                        <div class="fw-bold"><?= $type['name'] ?></div>
                                        <small class="text-muted"><?= $type['description'] ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Panel: Plantillas HERCO -->
                    <div class="tab-pane fade" id="templates-panel" role="tabpanel">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-rocket"></i>
                            Plantillas HERCO 2024
                        </h6>
                        
                        <div class="template-item card mb-3 p-3" data-template="herco_completa">
                            <div class="text-center">
                                <i class="fas fa-crown fa-2x mb-2 text-warning"></i>
                                <h6>HERCO Completa</h6>
                                <small class="text-muted">18 categorías • 54 preguntas</small>
                            </div>
                        </div>
                        
                        <div class="template-item card mb-3 p-3" data-template="herco_basica">
                            <div class="text-center">
                                <i class="fas fa-star fa-2x mb-2 text-info"></i>
                                <h6>HERCO Básica</h6>
                                <small class="text-muted">9 categorías • 27 preguntas</small>
                            </div>
                        </div>
                        
                        <div class="template-item card mb-3 p-3" data-template="herco_express">
                            <div class="text-center">
                                <i class="fas fa-bolt fa-2x mb-2 text-success"></i>
                                <h6>HERCO Express</h6>
                                <small class="text-muted">5 categorías • 15 preguntas</small>
                            </div>
                        </div>
                    </div>

                    <!-- Panel: Categorías HERCO -->
                    <div class="tab-pane fade" id="categories-panel" role="tabpanel">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-bookmark"></i>
                            Categorías Oficiales
                        </h6>
                        
                        <div class="categories-list">
                            <?php foreach ($herco_categories as $cat_key => $category): ?>
                            <div class="category-badge mb-2" 
                                 style="background-color: <?= $category['color'] ?>; display: block;"
                                 data-category="<?= $cat_key ?>">
                                <i class="<?= $category['icon'] ?>"></i>
                                <?= $category['name'] ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-tools"></i>
                        Acciones Rápidas
                    </h6>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="previewSurvey()">
                            <i class="fas fa-eye"></i> Vista Previa
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="saveQuestions()">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="importQuestions()">
                            <i class="fas fa-upload"></i> Importar
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="exportQuestions()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas principal: Lista de preguntas -->
        <div class="col-lg-6">
            <div class="builder-canvas p-4">
                <!-- Toolbar del canvas -->
                <div class="builder-toolbar">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                <i class="fas fa-edit text-primary"></i>
                                <?= htmlspecialchars($survey['title']) ?>
                            </h5>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Arrastra preguntas para reordenar • Haz clic para editar
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearAllQuestions()" title="Limpiar todo">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="togglePreview()" title="Alternar vista previa">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de preguntas (sortable) -->
                <div id="questionsList" class="questions-list">
                    <?php if (empty($questions)): ?>
                    <div class="empty-state" id="emptyState">
                        <i class="fas fa-magic"></i>
                        <h4>¡Comienza a construir tu encuesta!</h4>
                        <p class="text-muted mb-4">
                            Arrastra tipos de preguntas desde el panel izquierdo<br>
                            o usa una plantilla HERCO para comenzar rápido
                        </p>
                        <button class="btn btn-primary" onclick="addHercoTemplate('herco_express')">
                            <i class="fas fa-rocket"></i>
                            Usar Plantilla Express
                        </button>
                    </div>
                    <?php else: ?>
                        <?php foreach ($questions as $index => $question): ?>
                        <div class="question-item mb-3 p-4" data-question-id="<?= $question['id'] ?>" data-order="<?= $index + 1 ?>">
                            <!-- Acciones de la pregunta -->
                            <div class="question-actions">
                                <button class="action-btn edit" onclick="editQuestion(<?= $question['id'] ?>)" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn duplicate" onclick="duplicateQuestion(<?= $question['id'] ?>)" title="Duplicar">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="action-btn delete" onclick="deleteQuestion(<?= $question['id'] ?>)" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="action-btn drag" title="Arrastrar">
                                    <i class="fas fa-grip-vertical"></i>
                                </button>
                            </div>

                            <!-- Header de la pregunta -->
                            <div class="question-header mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary me-2"><?= $index + 1 ?></span>
                                        <span class="badge bg-info me-2">
                                            <i class="fas fa-tag"></i>
                                            <?= ucfirst($question['type']) ?>
                                        </span>
                                        <?php if (!empty($question['category'])): ?>
                                        <span class="category-badge" style="background-color: <?= $herco_categories[$question['category']]['color'] ?? '#6c757d' ?>">
                                            <i class="<?= $herco_categories[$question['category']]['icon'] ?? 'fas fa-tag' ?>"></i>
                                            <?= $herco_categories[$question['category']]['name'] ?? $question['category'] ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="question-meta">
                                        <?php if ($question['required']): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-asterisk"></i> Obligatoria
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenido de la pregunta -->
                            <div class="question-content">
                                <h6 class="question-title mb-2">
                                    <?= htmlspecialchars($question['text']) ?>
                                </h6>
                                
                                <?php if (!empty($question['description'])): ?>
                                <p class="question-description text-muted small mb-3">
                                    <?= htmlspecialchars($question['description']) ?>
                                </p>
                                <?php endif; ?>

                                <!-- Preview de opciones según el tipo -->
                                <div class="question-preview">
                                    <?= $this->renderQuestionPreview($question) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel derecho: Editor y vista previa -->
        <div class="col-lg-3">
            <div class="builder-sidebar p-4">
                <!-- Editor de pregunta -->
                <div id="questionEditor" style="display: none;">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-edit"></i>
                        Editor de Pregunta
                    </h6>
                    
                    <form id="questionForm">
                        <input type="hidden" id="editingQuestionId">
                        
                        <div class="mb-3">
                            <label class="form-label">Texto de la pregunta *</label>
                            <textarea class="form-control" id="questionText" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción (opcional)</label>
                            <textarea class="form-control" id="questionDescription" rows="2"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="questionType" onchange="updateQuestionOptions()">
                                    <?php foreach ($question_types_config as $type_key => $type): ?>
                                    <option value="<?= $type_key ?>"><?= $type['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Categoría</label>
                                <select class="form-select" id="questionCategory">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($herco_categories as $cat_key => $category): ?>
                                    <option value="<?= $cat_key ?>"><?= $category['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="questionRequired">
                                <label class="form-check-label" for="questionRequired">
                                    Pregunta obligatoria
                                </label>
                            </div>
                        </div>
                        
                        <!-- Opciones específicas por tipo -->
                        <div id="questionOptions"></div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="saveQuestionEdit()">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="cancelQuestionEdit()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Vista previa -->
                <div id="previewPanel">
                    <h6 class="text-success mb-3">
                        <i class="fas fa-eye"></i>
                        Vista Previa
                    </h6>
                    
                    <div class="preview-panel">
                        <div class="preview-content" id="previewContent">
                            <div class="text-center text-muted">
                                <i class="fas fa-search fa-2x mb-3"></i>
                                <p>Selecciona una pregunta para ver la vista previa</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i>
                            <strong>Tip:</strong> Haz clic en una pregunta para editarla
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para el constructor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
// Variables globales del constructor
let questionsList;
let currentEditingQuestion = null;
let autoSaveTimeout;
let questionCounter = <?= count($questions) ?>;

// Inicializar constructor al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    initializeBuilder();
    setupEventListeners();
    updateQuestionCounter();
});

/**
 * Inicializar el constructor visual
 */
function initializeBuilder() {
    // Inicializar sortable para la lista de preguntas
    const questionsContainer = document.getElementById('questionsList');
    questionsList = new Sortable(questionsContainer, {
        animation: 300,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        handle: '.action-btn.drag',
        onEnd: function(evt) {
            updateQuestionOrder();
            scheduleAutoSave();
        }
    });

    // Inicializar drag-and-drop desde tipos de pregunta
    const questionTypesContainer = document.getElementById('questionTypes');
    new Sortable(questionTypesContainer, {
        group: {
            name: 'questions',
            pull: 'clone',
            put: false
        },
        animation: 300,
        sort: false,
        onEnd: function(evt) {
            if (evt.to.id === 'questionsList') {
                addQuestionFromType(evt.item.dataset.type, evt.newIndex);
                evt.item.remove(); // Remover el clon
            }
        }
    });

    // Configurar drag-and-drop en la lista de preguntas
    new Sortable(questionsContainer, {
        group: 'questions',
        animation: 300,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onAdd: function(evt) {
            const questionType = evt.item.dataset.type;
            addQuestionFromType(questionType, evt.newIndex);
            evt.item.remove();
        }
    });
}

/**
 * Configurar event listeners
 */
function setupEventListeners() {
    // Click en plantillas
    document.querySelectorAll('.template-item').forEach(item => {
        item.addEventListener('click', function() {
            const templateType = this.dataset.template;
            addHercoTemplate(templateType);
        });
    });

    // Click en preguntas para editar
    document.addEventListener('click', function(e) {
        const questionItem = e.target.closest('.question-item');
        if (questionItem && !e.target.closest('.question-actions')) {
            const questionId = questionItem.dataset.questionId;
            editQuestion(questionId);
        }
    });

    // Auto-save al escribir
    document.addEventListener('input', function(e) {
        if (e.target.closest('#questionForm')) {
            scheduleAutoSave();
        }
    });
}

/**
 * Agregar pregunta desde tipo
 */
function addQuestionFromType(questionType, position = -1) {
    const questionData = {
        type: questionType,
        text: 'Nueva pregunta',
        description: '',
        category: '',
        required: false,
        options: getDefaultOptionsForType(questionType),
        order: questionCounter + 1
    };

    createQuestionElement(questionData, position);
    questionCounter++;
    updateQuestionCounter();
    scheduleAutoSave();
    
    // Mostrar el estado vacío si era la primera pregunta
    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
        emptyState.style.display = 'none';
    }
}

/**
 * Crear elemento de pregunta en el DOM
 */
function createQuestionElement(questionData, position = -1) {
    const questionElement = document.createElement('div');
    questionElement.className = 'question-item mb-3 p-4';
    questionElement.dataset.questionId = questionData.id || 'new_' + Date.now();
    questionElement.dataset.order = questionData.order;

    questionElement.innerHTML = `
        <!-- Acciones de la pregunta -->
        <div class="question-actions">
            <button class="action-btn edit" onclick="editQuestion('${questionElement.dataset.questionId}')" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn duplicate" onclick="duplicateQuestion('${questionElement.dataset.questionId}')" title="Duplicar">
                <i class="fas fa-copy"></i>
            </button>
            <button class="action-btn delete" onclick="deleteQuestion('${questionElement.dataset.questionId}')" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
            <button class="action-btn drag" title="Arrastrar">
                <i class="fas fa-grip-vertical"></i>
            </button>
        </div>

        <!-- Header de la pregunta -->
        <div class="question-header mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2">${questionData.order}</span>
                    <span class="badge bg-info me-2">
                        <i class="fas fa-tag"></i>
                        ${questionData.type}
                    </span>
                    ${questionData.category ? `
                    <span class="category-badge" style="background-color: ${getCategoryColor(questionData.category)}">
                        <i class="${getCategoryIcon(questionData.category)}"></i>
                        ${getCategoryName(questionData.category)}
                    </span>
                    ` : ''}
                </div>
                <div class="question-meta">
                    ${questionData.required ? `
                    <span class="badge bg-danger">
                        <i class="fas fa-asterisk"></i> Obligatoria
                    </span>
                    ` : ''}
                </div>
            </div>
        </div>

        <!-- Contenido de la pregunta -->
        <div class="question-content">
            <h6 class="question-title mb-2">
                ${questionData.text}
            </h6>
            
            ${questionData.description ? `
            <p class="question-description text-muted small mb-3">
                ${questionData.description}
            </p>
            ` : ''}

            <!-- Preview de opciones según el tipo -->
            <div class="question-preview">
                ${renderQuestionPreview(questionData)}
            </div>
        </div>
    `;

    const questionsContainer = document.getElementById('questionsList');
    if (position >= 0 && position < questionsContainer.children.length) {
        questionsContainer.insertBefore(questionElement, questionsContainer.children[position]);
    } else {
        questionsContainer.appendChild(questionElement);
    }

    return questionElement;
}

/**
 * Obtener opciones por defecto para un tipo de pregunta
 */
function getDefaultOptionsForType(questionType) {
    const defaultOptions = {
        'likert_5': [
            'Muy en desacuerdo',
            'En desacuerdo', 
            'Neutral',
            'De acuerdo',
            'Muy de acuerdo'
        ],
        'likert_7': [
            'Completamente en desacuerdo',
            'Muy en desacuerdo',
            'En desacuerdo',
            'Neutral',
            'De acuerdo',
            'Muy de acuerdo',
            'Completamente de acuerdo'
        ],
        'likert_3': [
            'Malo',
            'Regular', 
            'Bueno'
        ],
        'multiple_choice': [
            'Opción 1',
            'Opción 2',
            'Opción 3'
        ],
        'checkbox': [
            'Opción 1',
            'Opción 2', 
            'Opción 3'
        ],
        'yes_no': [
            'Sí',
            'No'
        ]
    };

    return defaultOptions[questionType] || [];
}

/**
 * Renderizar preview de pregunta según tipo
 */
function renderQuestionPreview(questionData) {
    const type = questionData.type;
    const options = questionData.options || [];

    switch (type) {
        case 'likert_5':
        case 'likert_7':
        case 'likert_3':
            return `
                <div class="d-flex justify-content-between align-items-center">
                    ${options.map((option, index) => `
                        <div class="text-center">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" disabled>
                            </div>
                            <small>${option}</small>
                        </div>
                    `).join('')}
                </div>
            `;
            
        case 'multiple_choice':
            return `
                <div class="form-check-list">
                    ${options.map(option => `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" disabled>
                            <label class="form-check-label">${option}</label>
                        </div>
                    `).join('')}
                </div>
            `;
            
        case 'checkbox':
            return `
                <div class="form-check-list">
                    ${options.map(option => `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" disabled>
                            <label class="form-check-label">${option}</label>
                        </div>
                    `).join('')}
                </div>
            `;
            
        case 'text':
            return '<input type="text" class="form-control" placeholder="Respuesta de texto" disabled>';
            
        case 'textarea':
            return '<textarea class="form-control" rows="3" placeholder="Respuesta extensa" disabled></textarea>';
            
        case 'rating':
            return `
                <div class="d-flex gap-1">
                    ${[1,2,3,4,5].map(i => `<i class="fas fa-star text-warning"></i>`).join('')}
                </div>
            `;
            
        case 'slider':
            return '<input type="range" class="form-range" min="0" max="100" disabled>';
            
        case 'yes_no':
            return `
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" disabled>
                    <label class="btn btn-outline-success">Sí</label>
                    <input type="radio" class="btn-check" disabled>
                    <label class="btn btn-outline-danger">No</label>
                </div>
            `;
            
        case 'date':
            return '<input type="date" class="form-control" disabled>';
            
        case 'number':
            return '<input type="number" class="form-control" placeholder="Número" disabled>';
            
        default:
            return '<div class="text-muted"><i class="fas fa-question-circle"></i> Vista previa no disponible</div>';
    }
}

/**
 * Editar pregunta
 */
function editQuestion(questionId) {
    // Encontrar la pregunta
    const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
    if (!questionElement) return;

    // Marcar como editando
    document.querySelectorAll('.question-item').forEach(el => el.classList.remove('editing'));
    questionElement.classList.add('editing');
    
    currentEditingQuestion = questionId;

    // Mostrar editor
    document.getElementById('questionEditor').style.display = 'block';
    document.getElementById('previewPanel').style.display = 'none';

    // Cargar datos de la pregunta (aquí simularemos los datos)
    loadQuestionForEdit(questionId);
}

/**
 * Cargar datos de pregunta para edición
 */
function loadQuestionForEdit(questionId) {
    // Aquí deberías cargar los datos reales de la pregunta
    // Por ahora simularemos algunos datos
    document.getElementById('editingQuestionId').value = questionId;
    document.getElementById('questionText').value = 'Pregunta de ejemplo';
    document.getElementById('questionDescription').value = '';
    document.getElementById('questionType').value = 'likert_5';
    document.getElementById('questionCategory').value = '';
    document.getElementById('questionRequired').checked = false;
    
    updateQuestionOptions();
}

/**
 * Actualizar opciones según tipo de pregunta
 */
function updateQuestionOptions() {
    const questionType = document.getElementById('questionType').value;
    const optionsContainer = document.getElementById('questionOptions');
    
    if (['multiple_choice', 'checkbox', 'likert_5', 'likert_7', 'likert_3'].includes(questionType)) {
        const defaultOptions = getDefaultOptionsForType(questionType);
        
        optionsContainer.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Opciones</label>
                <div id="optionsList">
                    ${defaultOptions.map((option, index) => `
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" value="${option}" onchange="scheduleAutoSave()">
                            <button class="btn btn-outline-danger" type="button" onclick="removeOption(this)">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()">
                    <i class="fas fa-plus"></i> Agregar Opción
                </button>
            </div>
        `;
    } else {
        optionsContainer.innerHTML = '';
    }
}

/**
 * Agregar opción
 */
function addOption() {
    const optionsList = document.getElementById('optionsList');
    const newOption = document.createElement('div');
    newOption.className = 'input-group mb-2';
    newOption.innerHTML = `
        <input type="text" class="form-control" placeholder="Nueva opción" onchange="scheduleAutoSave()">
        <button class="btn btn-outline-danger" type="button" onclick="removeOption(this)">
            <i class="fas fa-minus"></i>
        </button>
    `;
    optionsList.appendChild(newOption);
}

/**
 * Remover opción
 */
function removeOption(button) {
    button.closest('.input-group').remove();
    scheduleAutoSave();
}

/**
 * Guardar cambios de pregunta
 */
function saveQuestionEdit() {
    // Aquí implementarías la lógica para guardar la pregunta editada
    window.notifications?.show('Pregunta actualizada', 'success');
    cancelQuestionEdit();
    scheduleAutoSave();
}

/**
 * Cancelar edición
 */
function cancelQuestionEdit() {
    document.getElementById('questionEditor').style.display = 'none';
    document.getElementById('previewPanel').style.display = 'block';
    document.querySelectorAll('.question-item').forEach(el => el.classList.remove('editing'));
    currentEditingQuestion = null;
}

/**
 * Duplicar pregunta
 */
function duplicateQuestion(questionId) {
    // Implementar lógica de duplicación
    questionCounter++;
    updateQuestionCounter();
    window.notifications?.show('Pregunta duplicada', 'success');
    scheduleAutoSave();
}

/**
 * Eliminar pregunta
 */
function deleteQuestion(questionId) {
    if (confirm('¿Estás seguro de eliminar esta pregunta?')) {
        const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
        if (questionElement) {
            questionElement.remove();
            updateQuestionCounter();
            scheduleAutoSave();
            
            // Mostrar estado vacío si no hay preguntas
            const questionsContainer = document.getElementById('questionsList');
            if (questionsContainer.children.length === 0) {
                showEmptyState();
            }
            
            window.notifications?.show('Pregunta eliminada', 'success');
        }
    }
}

/**
 * Actualizar orden de preguntas
 */
function updateQuestionOrder() {
    const questions = document.querySelectorAll('.question-item');
    questions.forEach((question, index) => {
        question.dataset.order = index + 1;
        const badge = question.querySelector('.badge.bg-primary');
        if (badge) badge.textContent = index + 1;
    });
}

/**
 * Actualizar contador de preguntas
 */
function updateQuestionCounter() {
    const count = document.querySelectorAll('.question-item').length;
    document.getElementById('questionCount').textContent = count;
    questionCounter = count;
}

/**
 * Mostrar estado vacío
 */
function showEmptyState() {
    const questionsContainer = document.getElementById('questionsList');
    questionsContainer.innerHTML = `
        <div class="empty-state" id="emptyState">
            <i class="fas fa-magic"></i>
            <h4>¡Comienza a construir tu encuesta!</h4>
            <p class="text-muted mb-4">
                Arrastra tipos de preguntas desde el panel izquierdo<br>
                o usa una plantilla HERCO para comenzar rápido
            </p>
            <button class="btn btn-primary" onclick="addHercoTemplate('herco_express')">
                <i class="fas fa-rocket"></i>
                Usar Plantilla Express
            </button>
        </div>
    `;
}

/**
 * Agregar plantilla HERCO
 */
function addHercoTemplate(templateType) {
    window.showLoading?.('Cargando plantilla HERCO...');
    
    // Simular carga de plantilla
    setTimeout(() => {
        window.hideLoading?.();
        
        const templates = {
            'herco_express': [
                { type: 'likert_5', text: '¿Qué tan satisfecho está con su trabajo actual?', category: 'satisfaccion' },
                { type: 'likert_5', text: '¿Siente que puede tomar decisiones en su trabajo?', category: 'participacion' },
                { type: 'likert_5', text: '¿La comunicación en su empresa es efectiva?', category: 'comunicacion' },
                { type: 'likert_5', text: '¿Tiene un buen equilibrio trabajo-vida?', category: 'equilibrio' },
                { type: 'likert_5', text: '¿Su carga de trabajo es adecuada?', category: 'distribucion' }
            ],
            'herco_basica': [], // Más preguntas...
            'herco_completa': [] // Todas las preguntas...
        };
        
        const templateQuestions = templates[templateType] || [];
        
        // Limpiar preguntas existentes si hay estado vacío
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            emptyState.remove();
        }
        
        // Agregar preguntas de la plantilla
        templateQuestions.forEach((questionData, index) => {
            questionData.order = questionCounter + index + 1;
            createQuestionElement(questionData);
        });
        
        questionCounter += templateQuestions.length;
        updateQuestionCounter();
        scheduleAutoSave();
        
        window.notifications?.show(`Plantilla ${templateType.replace('_', ' ')} agregada exitosamente`, 'success');
    }, 1500);
}

/**
 * Programar auto-guardado
 */
function scheduleAutoSave() {
    clearTimeout(autoSaveTimeout);
    
    const indicator = document.getElementById('autoSaveIndicator');
    indicator.className = 'auto-save-indicator saving';
    indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    autoSaveTimeout = setTimeout(() => {
        saveQuestions(true);
    }, 2000);
}

/**
 * Guardar preguntas
 */
function saveQuestions(isAutoSave = false) {
    const questions = collectQuestionsData();
    
    fetch(`/admin/surveys/builder/<?= $survey['id'] ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ questions: questions })
    })
    .then(response => response.json())
    .then(data => {
        const indicator = document.getElementById('autoSaveIndicator');
        
        if (data.success) {
            if (isAutoSave) {
                indicator.className = 'auto-save-indicator saved';
                indicator.innerHTML = '<i class="fas fa-check"></i> Guardado';
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 2000);
            } else {
                window.notifications?.show('Preguntas guardadas exitosamente', 'success');
            }
        } else {
            window.notifications?.show(data.message || 'Error al guardar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.notifications?.show('Error de conexión', 'error');
    });
}

/**
 * Recopilar datos de todas las preguntas
 */
function collectQuestionsData() {
    const questions = [];
    document.querySelectorAll('.question-item').forEach((questionElement, index) => {
        // Aquí recopilarías todos los datos de cada pregunta
        // Por ahora simulamos algunos datos básicos
        questions.push({
            id: questionElement.dataset.questionId,
            order: index + 1,
            text: questionElement.querySelector('.question-title').textContent,
            type: 'likert_5', // Deberías obtener el tipo real
            category: '', // Deberías obtener la categoría real
            required: false // Deberías obtener si es requerida
        });
    });
    return questions;
}

/**
 * Funciones auxiliares para categorías
 */
function getCategoryColor(categoryKey) {
    const colors = {
        'satisfaccion': '#e74c3c',
        'participacion': '#3498db',
        'comunicacion': '#2ecc71',
        'equilibrio': '#f39c12',
        'distribucion': '#9b59b6'
        // ... más categorías
    };
    return colors[categoryKey] || '#6c757d';
}

function getCategoryIcon(categoryKey) {
    const icons = {
        'satisfaccion': 'fas fa-heart',
        'participacion': 'fas fa-users',
        'comunicacion': 'fas fa-comments',
        'equilibrio': 'fas fa-balance-scale',
        'distribucion': 'fas fa-tasks'
        // ... más categorías
    };
    return icons[categoryKey] || 'fas fa-tag';
}

function getCategoryName(categoryKey) {
    const names = {
        'satisfaccion': 'Satisfacción Laboral',
        'participacion': 'Participación y Autonomía',
        'comunicacion': 'Comunicación y Objetivos',
        'equilibrio': 'Equilibrio y Evaluación',
        'distribucion': 'Distribución y Carga de Trabajo'
        // ... más categorías
    };
    return names[categoryKey] || categoryKey;
}

/**
 * Funciones de acciones rápidas
 */
function previewSurvey() {
    window.open(`/admin/surveys/preview/<?= $survey['id'] ?>`, '_blank');
}

function clearAllQuestions() {
    if (confirm('¿Estás seguro de eliminar todas las preguntas? Esta acción no se puede deshacer.')) {
        document.getElementById('questionsList').innerHTML = '';
        showEmptyState();
        updateQuestionCounter();
        window.notifications?.show('Todas las preguntas eliminadas', 'warning');
    }
}

function togglePreview() {
    const previewPanel = document.getElementById('previewPanel');
    const editor = document.getElementById('questionEditor');
    
    if (previewPanel.style.display === 'none') {
        previewPanel.style.display = 'block';
        editor.style.display = 'none';
    }
}

function importQuestions() {
    window.notifications?.show('Funcionalidad de importación en desarrollo', 'info');
}

function exportQuestions() {
    const questions = collectQuestionsData();
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(questions, null, 2));
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", "preguntas_encuesta.json");
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
    
    window.notifications?.show('Preguntas exportadas exitosamente', 'success');
}
</script>