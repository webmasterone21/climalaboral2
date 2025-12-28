<?php
// views/admin/questions/builder.php - Constructor de Preguntas
?>

<div class="card question-builder-card">
    <div class="card-header">
        <h4 class="card-title d-flex align-items-center">
            <i class="fas fa-wrench me-2 text-primary"></i>
            Constructor de Preguntas
        </h4>
        <div class="card-actions">
            <button class="btn btn-sm btn-outline-secondary" onclick="resetBuilder()" title="Limpiar formulario">
                <i class="fas fa-refresh"></i>
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Formulario principal -->
        <form id="question-form" class="needs-validation" novalidate>
            <input type="hidden" name="survey_id" value="<?= $survey['id'] ?>">
            <input type="hidden" name="question_id" id="question_id" value="">
            
            <!-- Selección de Categoría -->
            <div class="form-group mb-3">
                <label for="category_id" class="form-label">
                    <i class="fas fa-folder me-1"></i>
                    Categoría <span class="text-danger">*</span>
                </label>
                <select name="category_id" id="category_id" class="form-control form-select" required>
                    <option value="">Seleccionar categoría...</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" data-description="<?= htmlspecialchars($category['description']) ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Por favor selecciona una categoría.
                </div>
                <!-- Descripción de la categoría seleccionada -->
                <div id="category-description" class="form-text text-muted mt-1" style="display: none;"></div>
            </div>
            
            <!-- Selección de Tipo de Pregunta -->
            <div class="form-group mb-3">
                <label for="question_type_id" class="form-label">
                    <i class="fas fa-list-ul me-1"></i>
                    Tipo de Pregunta <span class="text-danger">*</span>
                </label>
                <select name="question_type_id" id="question_type_id" class="form-control form-select" required>
                    <option value="">Seleccionar tipo...</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>" 
                                data-type="<?= $type['name'] ?>"
                                data-description="<?= htmlspecialchars($type['description']) ?>">
                            <?= htmlspecialchars($type['description']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Por favor selecciona un tipo de pregunta.
                </div>
            </div>
            
            <!-- Vista previa del tipo de pregunta -->
            <div id="question-type-preview" class="mb-3" style="display: none;">
                <div class="alert alert-info">
                    <div class="d-flex">
                        <div class="alert-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="ms-2">
                            <h4 class="alert-title">Vista previa del tipo</h4>
                            <div id="preview-content"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Texto de la Pregunta -->
            <div class="form-group mb-3">
                <label for="question_text" class="form-label">
                    <i class="fas fa-question-circle me-1"></i>
                    Texto de la Pregunta <span class="text-danger">*</span>
                </label>
                <textarea name="question_text" 
                          id="question_text" 
                          class="form-control" 
                          rows="3" 
                          placeholder="Escribe aquí el texto de tu pregunta..."
                          maxlength="1000"
                          required></textarea>
                <div class="form-text d-flex justify-content-between">
                    <span class="text-muted">Sé claro y específico en tu pregunta</span>
                    <span id="char-counter" class="text-muted">0/1000</span>
                </div>
                <div class="invalid-feedback">
                    El texto de la pregunta es obligatorio.
                </div>
            </div>
            
            <!-- Descripción/Ayuda Opcional -->
            <div class="form-group mb-3">
                <label for="description" class="form-label">
                    <i class="fas fa-info me-1"></i>
                    Descripción o Ayuda <span class="text-muted">(opcional)</span>
                </label>
                <textarea name="description" 
                          id="description" 
                          class="form-control" 
                          rows="2" 
                          placeholder="Información adicional para ayudar a responder..."
                          maxlength="500"></textarea>
                <div class="form-text text-muted">
                    Proporciona contexto o instrucciones adicionales
                </div>
            </div>
            
            <!-- Configuración de Opciones (para preguntas de opción múltiple) -->
            <div id="options-container" class="mb-3" style="display: none;">
                <label class="form-label">
                    <i class="fas fa-list me-1"></i>
                    Opciones de Respuesta <span class="text-danger">*</span>
                </label>
                <div id="options-list">
                    <!-- Las opciones se generarán dinámicamente -->
                </div>
                <button type="button" 
                        class="btn btn-sm btn-outline-primary mt-2" 
                        onclick="addOption()">
                    <i class="fas fa-plus me-1"></i>
                    Agregar Opción
                </button>
            </div>
            
            <!-- Configuración Avanzada -->
            <div class="form-group mb-3">
                <div class="form-label mb-2">
                    <i class="fas fa-cogs me-1"></i>
                    Configuración
                </div>
                
                <!-- Pregunta Obligatoria -->
                <div class="form-check mb-2">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="required" 
                           id="required"
                           value="1">
                    <label class="form-check-label" for="required">
                        <i class="fas fa-asterisk text-danger me-1"></i>
                        Pregunta obligatoria
                    </label>
                </div>
                
                <!-- Permitir N/A -->
                <div class="form-check mb-2" id="allow-na-option" style="display: none;">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="allow_na" 
                           id="allow_na"
                           value="1">
                    <label class="form-check-label" for="allow_na">
                        Incluir opción "No Aplica"
                    </label>
                </div>
                
                <!-- Orden de la pregunta -->
                <div class="row">
                    <div class="col-6">
                        <label for="order_position" class="form-label">Posición</label>
                        <input type="number" 
                               name="order_position" 
                               id="order_position" 
                               class="form-control form-control-sm"
                               min="1" 
                               placeholder="Auto">
                        <div class="form-text text-muted">Déjalo vacío para orden automático</div>
                    </div>
                </div>
            </div>
            
            <!-- Botones de Acción -->
            <div class="form-actions mt-4">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="save-question-btn">
                        <span class="btn-text">
                            <i class="fas fa-save me-2"></i>
                            Guardar Pregunta
                        </span>
                        <span class="btn-loading" style="display: none;">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Guardando...
                        </span>
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary" onclick="resetBuilder()">
                        <i class="fas fa-times me-2"></i>
                        Cancelar
                    </button>
                </div>
            </div>
            
            <!-- Modo de edición -->
            <div id="edit-mode-banner" class="alert alert-warning mt-3" style="display: none;">
                <div class="d-flex">
                    <div class="alert-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="ms-2">
                        <h4 class="alert-title">Modo Edición</h4>
                        <div class="text-muted">Estás editando una pregunta existente</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Panel de Plantillas Rápidas -->
<div class="card mt-3">
    <div class="card-header">
        <h4 class="card-title">
            <i class="fas fa-magic me-2 text-success"></i>
            Plantillas Rápidas
        </h4>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12">
                <button type="button" 
                        class="btn btn-sm btn-outline-info w-100 text-start" 
                        onclick="loadQuestionTemplate('satisfaction')">
                    <i class="fas fa-smile me-2"></i>
                    <strong>Satisfacción Laboral</strong><br>
                    <small class="text-muted">Pregunta básica de satisfacción con escala Likert</small>
                </button>
            </div>
            <div class="col-12">
                <button type="button" 
                        class="btn btn-sm btn-outline-info w-100 text-start" 
                        onclick="loadQuestionTemplate('communication')">
                    <i class="fas fa-comments me-2"></i>
                    <strong>Comunicación</strong><br>
                    <small class="text-muted">Evaluación de comunicación interna</small>
                </button>
            </div>
            <div class="col-12">
                <button type="button" 
                        class="btn btn-sm btn-outline-info w-100 text-start" 
                        onclick="loadQuestionTemplate('leadership')">
                    <i class="fas fa-user-tie me-2"></i>
                    <strong>Liderazgo</strong><br>
                    <small class="text-muted">Evaluación de supervisión y liderazgo</small>
                </button>
            </div>
            <div class="col-12">
                <button type="button" 
                        class="btn btn-sm btn-outline-info w-100 text-start" 
                        onclick="loadQuestionTemplate('open')">
                    <i class="fas fa-comment-dots me-2"></i>
                    <strong>Pregunta Abierta</strong><br>
                    <small class="text-muted">Comentarios y sugerencias libres</small>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Ayuda -->
<div class="card mt-3">
    <div class="card-header">
        <h4 class="card-title">
            <i class="fas fa-lightbulb me-2 text-warning"></i>
            Consejos
        </h4>
    </div>
    <div class="card-body">
        <div class="tips-list">
            <div class="tip-item mb-2">
                <i class="fas fa-check text-success me-2"></i>
                <small>Usa preguntas claras y específicas</small>
            </div>
            <div class="tip-item mb-2">
                <i class="fas fa-check text-success me-2"></i>
                <small>Evita preguntas sesgadas o dirigidas</small>
            </div>
            <div class="tip-item mb-2">
                <i class="fas fa-check text-success me-2"></i>
                <small>Agrupa preguntas similares por categoría</small>
            </div>
            <div class="tip-item">
                <i class="fas fa-check text-success me-2"></i>
                <small>Limita el número de preguntas obligatorias</small>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="/help/question-builder" class="btn btn-sm btn-outline-secondary w-100" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i>
                Ver Guía Completa
            </a>
        </div>
    </div>
</div>

<script>
// Inicialización del constructor
document.addEventListener('DOMContentLoaded', function() {
    initializeQuestionBuilder();
});

function initializeQuestionBuilder() {
    // Event listeners
    document.getElementById('category_id').addEventListener('change', handleCategoryChange);
    document.getElementById('question_type_id').addEventListener('change', handleQuestionTypeChange);
    document.getElementById('question_text').addEventListener('input', updateCharCounter);
    document.getElementById('question-form').addEventListener('submit', handleFormSubmit);
    
    // Inicializar contador de caracteres
    updateCharCounter();
}

function handleCategoryChange(e) {
    const selectedOption = e.target.selectedOptions[0];
    const description = selectedOption?.dataset?.description || '';
    const descriptionDiv = document.getElementById('category-description');
    
    if (description) {
        descriptionDiv.textContent = description;
        descriptionDiv.style.display = 'block';
    } else {
        descriptionDiv.style.display = 'none';
    }
}

function handleQuestionTypeChange(e) {
    const selectedOption = e.target.selectedOptions[0];
    const questionType = selectedOption?.dataset?.type || '';
    const description = selectedOption?.dataset?.description || '';
    
    // Mostrar vista previa del tipo
    showQuestionTypePreview(questionType, description);
    
    // Mostrar/ocultar opciones según el tipo
    const optionsContainer = document.getElementById('options-container');
    const allowNaOption = document.getElementById('allow-na-option');
    
    if (['multiple_choice', 'single_choice', 'rating'].includes(questionType)) {
        optionsContainer.style.display = 'block';
        allowNaOption.style.display = 'block';
        generateOptionsForType(questionType);
    } else {
        optionsContainer.style.display = 'none';
        allowNaOption.style.display = 'none';
    }
}

function showQuestionTypePreview(type, description) {
    const previewContainer = document.getElementById('question-type-preview');
    const previewContent = document.getElementById('preview-content');
    
    let preview = '';
    
    switch (type) {
        case 'likert_5':
            preview = `
                <strong>${description}</strong><br>
                <small>Ejemplo: ○ Totalmente en desacuerdo ○ En desacuerdo ○ Neutral ○ De acuerdo ○ Totalmente de acuerdo</small>
            `;
            break;
        case 'likert_7':
            preview = `
                <strong>${description}</strong><br>
                <small>Escala de 7 puntos para mayor precisión en las respuestas</small>
            `;
            break;
        case 'yes_no':
            preview = `
                <strong>${description}</strong><br>
                <small>Ejemplo: ○ Sí ○ No</small>
            `;
            break;
        case 'multiple_choice':
            preview = `
                <strong>${description}</strong><br>
                <small>Los participantes pueden seleccionar múltiples opciones</small>
            `;
            break;
        case 'single_choice':
            preview = `
                <strong>${description}</strong><br>
                <small>Los participantes seleccionan solo una opción</small>
            `;
            break;
        case 'text':
            preview = `
                <strong>${description}</strong><br>
                <small>Ejemplo: [Campo de texto libre para comentarios]</small>
            `;
            break;
        case 'numeric':
            preview = `
                <strong>${description}</strong><br>
                <small>Ejemplo: [___] (solo números)</small>
            `;
            break;
        case 'rating':
            preview = `
                <strong>${description}</strong><br>
                <small>Ejemplo: ☆☆☆☆☆ (calificación con estrellas)</small>
            `;
            break;
        default:
            preview = description;
    }
    
    if (preview) {
        previewContent.innerHTML = preview;
        previewContainer.style.display = 'block';
    } else {
        previewContainer.style.display = 'none';
    }
}

function generateOptionsForType(type) {
    const optionsList = document.getElementById('options-list');
    optionsList.innerHTML = '';
    
    let defaultOptions = [];
    
    switch (type) {
        case 'likert_5':
            defaultOptions = [
                { text: 'Totalmente en desacuerdo', value: 1 },
                { text: 'En desacuerdo', value: 2 },
                { text: 'Neutral', value: 3 },
                { text: 'De acuerdo', value: 4 },
                { text: 'Totalmente de acuerdo', value: 5 }
            ];
            break;
        case 'likert_7':
            defaultOptions = [
                { text: 'Totalmente en desacuerdo', value: 1 },
                { text: 'En desacuerdo', value: 2 },
                { text: 'Algo en desacuerdo', value: 3 },
                { text: 'Neutral', value: 4 },
                { text: 'Algo de acuerdo', value: 5 },
                { text: 'De acuerdo', value: 6 },
                { text: 'Totalmente de acuerdo', value: 7 }
            ];
            break;
        case 'yes_no':
            defaultOptions = [
                { text: 'Sí', value: 1 },
                { text: 'No', value: 0 }
            ];
            break;
        case 'rating':
            defaultOptions = [
                { text: '1 estrella - Muy malo', value: 1 },
                { text: '2 estrellas - Malo', value: 2 },
                { text: '3 estrellas - Regular', value: 3 },
                { text: '4 estrellas - Bueno', value: 4 },
                { text: '5 estrellas - Excelente', value: 5 }
            ];
            break;
    }
    
    if (defaultOptions.length > 0) {
        defaultOptions.forEach((option, index) => {
            addOptionToList(option.text, option.value, index);
        });
    } else {
        // Para multiple_choice y single_choice, agregar opciones vacías
        addOptionToList('', '', 0);
        addOptionToList('', '', 1);
    }
}

function addOption() {
    const optionsList = document.getElementById('options-list');
    const optionIndex = optionsList.children.length;
    addOptionToList('', '', optionIndex);
}

function addOptionToList(text, value, index) {
    const optionsList = document.getElementById('options-list');
    
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-input-group mb-2';
    optionDiv.innerHTML = `
        <div class="input-group">
            <span class="input-group-text">${index + 1}.</span>
            <input type="text" 
                   name="options[${index}][text]" 
                   class="form-control" 
                   placeholder="Texto de la opción" 
                   value="${text}"
                   required>
            <input type="number" 
                   name="options[${index}][value]" 
                   class="form-control" 
                   style="max-width: 80px;"
                   placeholder="Val" 
                   title="Valor numérico"
                   value="${value}"
                   required>
            <button type="button" 
                    class="btn btn-outline-danger" 
                    onclick="removeOption(this)"
                    title="Eliminar opción">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    optionsList.appendChild(optionDiv);
}

function removeOption(button) {
    const optionGroup = button.closest('.option-input-group');
    optionGroup.remove();
    
    // Renumerar las opciones
    const optionsList = document.getElementById('options-list');
    Array.from(optionsList.children).forEach((option, index) => {
        const numberSpan = option.querySelector('.input-group-text');
        const textInput = option.querySelector('input[type="text"]');
        const valueInput = option.querySelector('input[type="number"]');
        
        numberSpan.textContent = `${index + 1}.`;
        textInput.name = `options[${index}][text]`;
        valueInput.name = `options[${index}][value]`;
    });
}

function updateCharCounter() {
    const textarea = document.getElementById('question_text');
    const counter = document.getElementById('char-counter');
    const currentLength = textarea.value.length;
    const maxLength = 1000;
    
    counter.textContent = `${currentLength}/${maxLength}`;
    
    if (currentLength > maxLength * 0.9) {
        counter.classList.add('text-warning');
    } else {
        counter.classList.remove('text-warning');
    }
}

function handleFormSubmit(e) {
    e.preventDefault();
    
    if (!e.target.checkValidity()) {
        e.target.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Procesar opciones si existen
    if (document.getElementById('options-container').style.display !== 'none') {
        data.options = [];
        const optionInputs = document.querySelectorAll('[name^="options["]');
        
        for (let i = 0; i < optionInputs.length; i += 2) {
            const textInput = optionInputs[i];
            const valueInput = optionInputs[i + 1];
            
            if (textInput && valueInput && textInput.value.trim()) {
                data.options.push({
                    text: textInput.value.trim(),
                    value: parseInt(valueInput.value) || 0
                });
            }
        }
    }
    
    saveQuestion(data);
}

function saveQuestion(data) {
    const saveBtn = document.getElementById('save-question-btn');
    const btnText = saveBtn.querySelector('.btn-text');
    const btnLoading = saveBtn.querySelector('.btn-loading');
    
    // Mostrar estado de carga
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-flex';
    saveBtn.disabled = true;
    
    const isEditing = document.getElementById('question_id').value !== '';
    const url = isEditing ? `/admin/questions/${data.question_id}` : '/admin/questions';
    const method = isEditing ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert(`Pregunta ${isEditing ? 'actualizada' : 'creada'} exitosamente`, 'success');
            resetBuilder();
            
            // Recargar la página para mostrar los cambios
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert(result.message || 'Error al guardar la pregunta', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving question:', error);
        showAlert('Error al guardar la pregunta', 'error');
    })
    .finally(() => {
        // Restaurar estado del botón
        btnText.style.display = 'inline-flex';
        btnLoading.style.display = 'none';
        saveBtn.disabled = false;
    });
}

function resetBuilder() {
    const form = document.getElementById('question-form');
    form.reset();
    form.classList.remove('was-validated');
    
    // Limpiar campos específicos
    document.getElementById('question_id').value = '';
    document.getElementById('category-description').style.display = 'none';
    document.getElementById('question-type-preview').style.display = 'none';
    document.getElementById('options-container').style.display = 'none';
    document.getElementById('allow-na-option').style.display = 'none';
    document.getElementById('edit-mode-banner').style.display = 'none';
    document.getElementById('options-list').innerHTML = '';
    
    // Actualizar contador de caracteres
    updateCharCounter();
    
    // Cambiar texto del botón
    const saveBtn = document.getElementById('save-question-btn');
    saveBtn.querySelector('.btn-text').innerHTML = '<i class="fas fa-save me-2"></i>Guardar Pregunta';
}

function populateQuestionForm(question) {
    // Llenar el formulario con los datos de la pregunta
    document.getElementById('question_id').value = question.id;
    document.getElementById('category_id').value = question.category_id;
    document.getElementById('question_type_id').value = question.question_type_id;
    document.getElementById('question_text').value = question.question_text;
    document.getElementById('description').value = question.description || '';
    document.getElementById('required').checked = question.required;
    document.getElementById('order_position').value = question.order_position || '';
    
    // Disparar eventos para actualizar la UI
    document.getElementById('category_id').dispatchEvent(new Event('change'));
    document.getElementById('question_type_id').dispatchEvent(new Event('change'));
    updateCharCounter();
    
    // Mostrar banner de edición
    document.getElementById('edit-mode-banner').style.display = 'block';
    
    // Cambiar texto del botón
    const saveBtn = document.getElementById('save-question-btn');
    saveBtn.querySelector('.btn-text').innerHTML = '<i class="fas fa-save me-2"></i>Actualizar Pregunta';
    
    // Cargar opciones si existen
    if (question.options && question.options.length > 0) {
        setTimeout(() => {
            const optionsList = document.getElementById('options-list');
            optionsList.innerHTML = '';
            
            question.options.forEach((option, index) => {
                addOptionToList(option.option_text, option.option_value, index);
            });
        }, 100);
    }
}

// Plantillas rápidas
function loadQuestionTemplate(templateType) {
    const templates = {
        satisfaction: {
            category_id: '1', // Satisfacción Laboral
            question_type_id: '1', // Likert 5
            question_text: '¿Qué tan satisfecho/a estás con tu trabajo actual en general?',
            description: 'Evalúa tu nivel de satisfacción considerando todos los aspectos de tu trabajo',
            required: true
        },
        communication: {
            category_id: '3', // Comunicación y Objetivos
            question_type_id: '1', // Likert 5
            question_text: 'La comunicación entre los diferentes niveles de la organización es efectiva',
            description: 'Considera la claridad y frecuencia de la comunicación interna',
            required: true
        },
        leadership: {
            category_id: '16', // Relaciones con Supervisores
            question_type_id: '1', // Likert 5
            question_text: 'Mi supervisor inmediato me proporciona la orientación y apoyo necesarios para realizar mi trabajo',
            description: 'Evalúa la calidad del liderazgo y supervisión que recibes',
            required: true
        },
        open: {
            category_id: '1', // Satisfacción Laboral
            question_type_id: '5', // Text
            question_text: '¿Qué sugerencias tienes para mejorar el clima laboral en la organización?',
            description: 'Comparte tus ideas y comentarios de manera abierta y constructiva',
            required: false
        }
    };
    
    const template = templates[templateType];
    if (template) {
        // Llenar el formulario con la plantilla
        Object.keys(template).forEach(key => {
            const input = document.getElementById(key) || document.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = template[key];
                } else {
                    input.value = template[key];
                }
                
                // Disparar evento change para actualizar la UI
                input.dispatchEvent(new Event('change'));
            }
        });
        
        updateCharCounter();
        showAlert('Plantilla cargada. Puedes modificarla según tus necesidades.', 'info');
    }
}

// Función de utilidad para mostrar alertas
function showAlert(message, type = 'info') {
    // Crear o actualizar el contenedor de alertas
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.style.position = 'fixed';
        alertContainer.style.top = '20px';
        alertContainer.style.right = '20px';
        alertContainer.style.zIndex = '9999';
        document.body.appendChild(alertContainer);
    }
    
    // Crear la alerta
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-dismiss después de 5 segundos
    setTimeout(() => {
        if (alert && alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>

<style>
.question-builder-card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.question-builder-card .card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.option-input-group .input-group {
    flex-wrap: nowrap;
}

.option-input-group .form-control:first-of-type {
    flex: 1;
}

.tips-list .tip-item {
    display: flex;
    align-items: flex-start;
}

.form-text {
    font-size: 0.875em;
}

.alert-icon {
    font-size: 1.2em;
    margin-top: 0.1rem;
}

#alert-container .alert {
    max-width: 400px;
    margin-bottom: 0.5rem;
}
</style>