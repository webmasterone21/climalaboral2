<?php
// views/admin/reports/settings.php - Configuración de reportes
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Configuración de Reportes</h1>
                        <p class="text-muted">Personaliza la generación y distribución de reportes</p>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item active">Configuración</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <button type="button" class="btn btn-success" id="save-settings">
                            <i class="fas fa-save"></i> Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Configuración general -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog text-primary"></i>
                        Configuración General
                    </h5>
                </div>
                <div class="card-body">
                    <form id="reports-settings-form">
                        <!-- Configuración de formato -->
                        <div class="settings-section">
                            <h6 class="settings-section-title">
                                <i class="fas fa-file-alt text-info"></i>
                                Formatos de Exportación
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Formato por defecto</label>
                                        <select name="default_format" class="form-control">
                                            <option value="pdf" <?= ($settings['default_format'] ?? 'pdf') == 'pdf' ? 'selected' : '' ?>>PDF</option>
                                            <option value="excel" <?= ($settings['default_format'] ?? '') == 'excel' ? 'selected' : '' ?>>Excel</option>
                                            <option value="html" <?= ($settings['default_format'] ?? '') == 'html' ? 'selected' : '' ?>>HTML</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Calidad de imágenes</label>
                                        <select name="image_quality" class="form-control">
                                            <option value="low" <?= ($settings['image_quality'] ?? 'medium') == 'low' ? 'selected' : '' ?>>Baja (más rápido)</option>
                                            <option value="medium" <?= ($settings['image_quality'] ?? 'medium') == 'medium' ? 'selected' : '' ?>>Media</option>
                                            <option value="high" <?= ($settings['image_quality'] ?? 'medium') == 'high' ? 'selected' : '' ?>>Alta</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="include_logo" 
                                                   <?= ($settings['include_logo'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label">Incluir logo de empresa</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="include_watermark" 
                                                   <?= ($settings['include_watermark'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label">Incluir marca de agua</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Configuración de contenido -->
                        <div class="settings-section">
                            <h6 class="settings-section-title">
                                <i class="fas fa-list-ul text-success"></i>
                                Contenido de Reportes
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Secciones incluidas por defecto</label>
                                        <div class="sections-checklist">
                                            <?php
                                            $available_sections = [
                                                'executive_summary' => 'Resumen Ejecutivo',
                                                'kpis' => 'KPIs Principales',
                                                'category_analysis' => 'Análisis por Categorías',
                                                'department_comparison' => 'Comparación por Departamentos',
                                                'detailed_questions' => 'Análisis Detallado de Preguntas',
                                                'comments_analysis' => 'Análisis de Comentarios',
                                                'recommendations' => 'Recomendaciones',
                                                'action_plan' => 'Plan de Acción'
                                            ];
                                            $included_sections = $settings['included_sections'] ?? array_keys($available_sections);
                                            ?>
                                            <?php foreach ($available_sections as $key => $label): ?>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" 
                                                           name="included_sections[]" value="<?= $key ?>"
                                                           <?= in_array($key, $included_sections) ? 'checked' : '' ?>>
                                                    <label class="form-check-label"><?= $label ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nivel de detalle</label>
                                        <select name="detail_level" class="form-control">
                                            <option value="executive" <?= ($settings['detail_level'] ?? 'standard') == 'executive' ? 'selected' : '' ?>>Ejecutivo (resumen)</option>
                                            <option value="standard" <?= ($settings['detail_level'] ?? 'standard') == 'standard' ? 'selected' : '' ?>>Estándar</option>
                                            <option value="detailed" <?= ($settings['detail_level'] ?? 'standard') == 'detailed' ? 'selected' : '' ?>>Detallado (completo)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Máximo de comentarios por pregunta</label>
                                        <input type="number" name="max_comments_per_question" class="form-control" 
                                               value="<?= $settings['max_comments_per_question'] ?? 5 ?>" min="0" max="50">
                                        <small class="form-text text-muted">0 = sin límite</small>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="show_statistical_significance" 
                                                   <?= ($settings['show_statistical_significance'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label">Mostrar significancia estadística</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Configuración de distribución automática -->
                        <div class="settings-section">
                            <h6 class="settings-section-title">
                                <i class="fas fa-paper-plane text-warning"></i>
                                Distribución Automática
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="auto_distribution_enabled" 
                                                   <?= ($settings['auto_distribution_enabled'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label">Activar distribución automática</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Frecuencia de distribución</label>
                                        <select name="distribution_frequency" class="form-control">
                                            <option value="immediate" <?= ($settings['distribution_frequency'] ?? 'immediate') == 'immediate' ? 'selected' : '' ?>>Inmediatamente al cerrar encuesta</option>
                                            <option value="daily" <?= ($settings['distribution_frequency'] ?? 'immediate') == 'daily' ? 'selected' : '' ?>>Diario</option>
                                            <option value="weekly" <?= ($settings['distribution_frequency'] ?? 'immediate') == 'weekly' ? 'selected' : '' ?>>Semanal</option>
                                            <option value="monthly" <?= ($settings['distribution_frequency'] ?? 'immediate') == 'monthly' ? 'selected' : '' ?>>Mensual</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lista de destinatarios por defecto</label>
                                        <textarea name="default_recipients" class="form-control" rows="4" 
                                                  placeholder="email1@empresa.com&#10;email2@empresa.com"><?= $settings['default_recipients'] ?? '' ?></textarea>
                                        <small class="form-text text-muted">Un email por línea</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Asunto del email</label>
                                        <input type="text" name="email_subject_template" class="form-control" 
                                               value="<?= $settings['email_subject_template'] ?? 'Reporte de Clima Laboral - {SURVEY_TITLE}' ?>"
                                               placeholder="Reporte de Clima Laboral - {SURVEY_TITLE}">
                                        <small class="form-text text-muted">Variables: {SURVEY_TITLE}, {COMPANY_NAME}, {DATE}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Configuración de personalización -->
                        <div class="settings-section">
                            <h6 class="settings-section-title">
                                <i class="fas fa-paint-brush text-purple"></i>
                                Personalización Visual
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Esquema de colores</label>
                                        <select name="color_scheme" class="form-control">
                                            <option value="default" <?= ($settings['color_scheme'] ?? 'default') == 'default' ? 'selected' : '' ?>>Por defecto</option>
                                            <option value="corporate" <?= ($settings['color_scheme'] ?? 'default') == 'corporate' ? 'selected' : '' ?>>Corporativo</option>
                                            <option value="modern" <?= ($settings['color_scheme'] ?? 'default') == 'modern' ? 'selected' : '' ?>>Moderno</option>
                                            <option value="custom" <?= ($settings['color_scheme'] ?? 'default') == 'custom' ? 'selected' : '' ?>>Personalizado</option>
                                        </select>
                                    </div>
                                    <div class="form-group color-custom-section" style="display: none;">
                                        <label>Color primario</label>
                                        <input type="color" name="primary_color" class="form-control" 
                                               value="<?= $settings['primary_color'] ?? '#007bff' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tipo de gráficos</label>
                                        <select name="chart_style" class="form-control">
                                            <option value="standard" <?= ($settings['chart_style'] ?? 'standard') == 'standard' ? 'selected' : '' ?>>Estándar</option>
                                            <option value="modern" <?= ($settings['chart_style'] ?? 'standard') == 'modern' ? 'selected' : '' ?>>Moderno</option>
                                            <option value="minimal" <?= ($settings['chart_style'] ?? 'standard') == 'minimal' ? 'selected' : '' ?>>Minimalista</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="show_data_labels" 
                                                   <?= ($settings['show_data_labels'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label">Mostrar etiquetas en gráficos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de vista previa y acciones -->
        <div class="col-lg-4">
            <!-- Vista previa -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye text-info"></i>
                        Vista Previa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="preview-container">
                        <div class="preview-report">
                            <div class="preview-header" style="background: <?= $settings['primary_color'] ?? '#007bff' ?>;">
                                <h6 class="text-white mb-1">Reporte de Clima Laboral</h6>
                                <small class="text-white opacity-75">Vista previa del formato</small>
                            </div>
                            <div class="preview-content">
                                <div class="preview-section">
                                    <div class="preview-kpi">
                                        <div class="preview-kpi-value" style="color: <?= $settings['primary_color'] ?? '#007bff' ?>;">4.2</div>
                                        <div class="preview-kpi-label">Satisfacción</div>
                                    </div>
                                </div>
                                <div class="preview-chart" style="background: linear-gradient(90deg, <?= $settings['primary_color'] ?? '#007bff' ?>, rgba(0,123,255,0.3));">
                                    Gráfico
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="refresh-preview">
                            <i class="fas fa-sync-alt"></i> Actualizar Vista Previa
                        </button>
                    </div>
                </div>
            </div>

            <!-- Plantillas predefinidas -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-layer-group text-success"></i>
                        Plantillas Predefinidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="template-list">
                        <div class="template-item">
                            <div class="template-info">
                                <h6 class="template-name">Ejecutivo</h6>
                                <small class="text-muted">Resumen de alto nivel para dirección</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary load-template" data-template="executive">
                                Cargar
                            </button>
                        </div>
                        <div class="template-item">
                            <div class="template-info">
                                <h6 class="template-name">Detallado</h6>
                                <small class="text-muted">Análisis completo para RRHH</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary load-template" data-template="detailed">
                                Cargar
                            </button>
                        </div>
                        <div class="template-item">
                            <div class="template-info">
                                <h6 class="template-name">Gerencial</h6>
                                <small class="text-muted">Equilibrio entre detalle y resumen</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary load-template" data-template="managerial">
                                Cargar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de configuración -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info"></i>
                        Estado de Configuración
                    </h5>
                </div>
                <div class="card-body">
                    <div class="config-status">
                        <div class="status-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Configuración básica completada</span>
                        </div>
                        <div class="status-item">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Formatos de exportación configurados</span>
                        </div>
                        <div class="status-item">
                            <i class="fas fa-clock text-warning"></i>
                            <span>Distribución automática pendiente</span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Última actualización: <?= date('d/m/Y H:i') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="saveConfirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Cambios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas guardar estos cambios en la configuración de reportes?</p>
                    <p class="text-muted">Los cambios afectarán todos los reportes futuros que se generen.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirm-save">
                        <i class="fas fa-save"></i> Confirmar y Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSettingsForm();
    initializeTemplateLoading();
    initializePreview();
});

function initializeSettingsForm() {
    const form = document.getElementById('reports-settings-form');
    const saveButton = document.getElementById('save-settings');
    const colorSchemeSelect = document.querySelector('select[name="color_scheme"]');
    const customColorSection = document.querySelector('.color-custom-section');
    const primaryColorInput = document.querySelector('input[name="primary_color"]');

    // Mostrar/ocultar sección de color personalizado
    colorSchemeSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customColorSection.style.display = 'block';
        } else {
            customColorSection.style.display = 'none';
        }
    });

    // Actualizar vista previa cuando cambia el color
    primaryColorInput.addEventListener('change', function() {
        updatePreviewColors(this.value);
    });

    // Guardar configuración
    saveButton.addEventListener('click', function() {
        $('#saveConfirmationModal').modal('show');
    });

    document.getElementById('confirm-save').addEventListener('click', function() {
        saveSettings();
        $('#saveConfirmationModal').modal('hide');
    });

    // Trigger inicial para color scheme
    if (colorSchemeSelect.value === 'custom') {
        customColorSection.style.display = 'block';
    }
}

function initializeTemplateLoading() {
    const templateButtons = document.querySelectorAll('.load-template');
    
    templateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const template = this.dataset.template;
            loadTemplate(template);
        });
    });
}

function initializePreview() {
    const refreshButton = document.getElementById('refresh-preview');
    
    refreshButton.addEventListener('click', function() {
        updatePreview();
    });
}

function loadTemplate(templateName) {
    const templates = {
        executive: {
            detail_level: 'executive',
            included_sections: ['executive_summary', 'kpis', 'recommendations'],
            max_comments_per_question: 0,
            show_statistical_significance: false,
            color_scheme: 'corporate'
        },
        detailed: {
            detail_level: 'detailed',
            included_sections: ['executive_summary', 'kpis', 'category_analysis', 'department_comparison', 'detailed_questions', 'comments_analysis', 'recommendations', 'action_plan'],
            max_comments_per_question: 10,
            show_statistical_significance: true,
            color_scheme: 'default'
        },
        managerial: {
            detail_level: 'standard',
            included_sections: ['executive_summary', 'kpis', 'category_analysis', 'department_comparison', 'recommendations'],
            max_comments_per_question: 5,
            show_statistical_significance: true,
            color_scheme: 'modern'
        }
    };

    const template = templates[templateName];
    if (!template) return;

    // Aplicar configuración de plantilla
    Object.keys(template).forEach(key => {
        const element = document.querySelector(`[name="${key}"]`);
        if (!element) return;

        if (element.type === 'checkbox') {
            element.checked = template[key];
        } else if (element.type === 'select-one') {
            element.value = template[key];
        } else if (key === 'included_sections') {
            // Manejar checkboxes múltiples
            const checkboxes = document.querySelectorAll(`[name="${key}[]"]`);
            checkboxes.forEach(checkbox => {
                checkbox.checked = template[key].includes(checkbox.value);
            });
        } else {
            element.value = template[key];
        }
    });

    // Trigger eventos para actualizar UI
    document.querySelector('select[name="color_scheme"]').dispatchEvent(new Event('change'));
    updatePreview();

    // Mostrar notificación
    showNotification(`Plantilla "${templateName}" cargada exitosamente`, 'success');
}

function updatePreview() {
    const colorScheme = document.querySelector('select[name="color_scheme"]').value;
    const primaryColor = document.querySelector('input[name="primary_color"]').value;
    
    let previewColor = '#007bff';
    
    switch (colorScheme) {
        case 'corporate':
            previewColor = '#2c3e50';
            break;
        case 'modern':
            previewColor = '#6f42c1';
            break;
        case 'custom':
            previewColor = primaryColor;
            break;
    }
    
    updatePreviewColors(previewColor);
}

function updatePreviewColors(color) {
    const previewHeader = document.querySelector('.preview-header');
    const previewKpiValue = document.querySelector('.preview-kpi-value');
    const previewChart = document.querySelector('.preview-chart');
    
    if (previewHeader) {
        previewHeader.style.background = color;
    }
    
    if (previewKpiValue) {
        previewKpiValue.style.color = color;
    }
    
    if (previewChart) {
        previewChart.style.background = `linear-gradient(90deg, ${color}, ${color}33)`;
    }
}

function saveSettings() {
    const form = document.getElementById('reports-settings-form');
    const formData = new FormData(form);
    
    // Agregar secciones incluidas manualmente (checkboxes múltiples)
    const includedSections = [];
    document.querySelectorAll('[name="included_sections[]"]:checked').forEach(checkbox => {
        includedSections.push(checkbox.value);
    });
    
    // Convertir FormData a objeto regular
    const settings = {};
    for (let [key, value] of formData.entries()) {
        if (key !== 'included_sections[]') {
            settings[key] = value;
        }
    }
    settings.included_sections = includedSections;

    // Enviar datos al servidor
    fetch('<?= BASE_URL ?>admin/reports/save-settings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Configuración guardada exitosamente', 'success');
        } else {
            showNotification('Error al guardar la configuración: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al conectar con el servidor', 'error');
    });
}

function showNotification(message, type) {
    // Crear y mostrar notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show notification-toast`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>

<style>
.settings-section {
    margin-bottom: 2rem;
}

.settings-section-title {
    color: #495057;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

.sections-checklist {
    max-height: 200px;
    overflow-y: auto;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}

.sections-checklist .form-check {
    margin-bottom: 0.5rem;
}

.color-custom-section {
    transition: all 0.3s ease;
}

.preview-container {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}

.preview-report {
    background: white;
    border-radius: 0.375rem;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.preview-header {
    padding: 1rem;
    background: #007bff;
    color: white;
}

.preview-content {
    padding: 1rem;
}

.preview-section {
    margin-bottom: 1rem;
}

.preview-kpi {
    text-align: center;
    padding: 0.5rem;
}

.preview-kpi-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.preview-kpi-label {
    font-size: 0.8rem;
    color: #6c757d;
}

.preview-chart {
    height: 60px;
    background: linear-gradient(90deg, #007bff, rgba(0,123,255,0.3));
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
}

.template-list {
    space-y: 1rem;
}

.template-item {
    display: flex;
    align-items: center;
    justify-content: between;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    margin-bottom: 0.75rem;
}

.template-info {
    flex-grow: 1;
}

.template-name {
    margin-bottom: 0.25rem;
    color: #495057;
}

.config-status {
    space-y: 0.5rem;
}

.status-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.status-item i {
    margin-right: 0.5rem;
    width: 16px;
}

.text-purple {
    color: #6f42c1 !important;
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
    .preview-container {
        padding: 0.5rem;
    }
    
    .template-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .template-item .btn {
        margin-top: 0.5rem;
        align-self: flex-end;
    }
}
</style>