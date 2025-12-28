<?php
// views/admin/questions/create.php - Modal/Página para crear preguntas
require_once __DIR__ . '/../../layouts/admin.php';
?>

<!-- Modal para crear pregunta -->
<div class="modal fade" id="createQuestionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Header del Modal -->
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title d-flex align-items-center">
                    <i class="fas fa-plus-circle me-2"></i>
                    <span id="modal-title">Nueva Pregunta</span>
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Cuerpo del Modal -->
            <div class="modal-body p-0">
                <!-- Progress Steps -->
                <div class="create-question-steps">
                    <div class="steps-header px-4 py-3 bg-light border-bottom">
                        <div class="steps-progress">
                            <div class="step active" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-label">Tipo</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-label">Contenido</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-label">Opciones</div>
                            </div>
                            <div class="step" data-step="4">
                                <div class="step-number">4</div>
                                <div class="step-label">Configuración</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Container -->
                    <div class="px-4 py-3">
                        <form id="create-question-form" class="needs-validation" novalidate>
                            <input type="hidden" name="survey_id" value="<?= $survey['id'] ?? '' ?>">
                            <input type="hidden" name="question_id" id="edit-question-id">
                            
                            <!-- Paso 1: Selección de Tipo y Categoría -->
                            <div class="step-content active" data-step="1">
                                <h5 class="step-title mb-3">
                                    <i class="fas fa-list-ul text-primary me-2"></i>
                                    Selecciona el tipo de pregunta
                                </h5>
                                
                                <!-- Categoría -->
                                <div class="form-group mb-4">
                                    <label for="step1-category" class="form-label fw-bold">
                                        Categoría <span class="text-danger">*</span>
                                    </label>
                                    <select name="category_id" id="step1-category" class="form-select form-select-lg" required>
                                        <option value="">Seleccionar categoría...</option>
                                        <?php if (isset($categories)): ?>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>" 
                                                        data-description="<?= htmlspecialchars($category['description']) ?>">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor selecciona una categoría.</div>
                                    <div id="step1-category-description" class="form-text mt-2" style="display: none;"></div>
                                </div>
                                
                                <!-- Tipos de pregunta con cards -->
                                <div class="form-group">
                                    <label class="form-label fw-bold mb-3">
                                        Tipo de pregunta <span class="text-danger">*</span>
                                    </label>
                                    <div class="question-types-grid">
                                        <?php if (isset($types)): ?>
                                            <?php foreach ($types as $type): ?>
                                                <div class="question-type-card" 
                                                     data-type="<?= $type['name'] ?>" 
                                                     data-type-id="<?= $type['id'] ?>"
                                                     onclick="selectQuestionType('<?= $type['id'] ?>', '<?= $type['name'] ?>')">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <div class="type-icon mb-2">
                                                                <?php
                                                                $icons = [
                                                                    'likert_5' => 'fas fa-star-half-alt',
                                                                    'likert_7' => 'fas fa-star',
                                                                    'multiple_choice' => 'fas fa-check-square',
                                                                    'single_choice' => 'fas fa-dot-circle',
                                                                    'text' => 'fas fa-keyboard',
                                                                    'numeric' => 'fas fa-calculator',
                                                                    'yes_no' => 'fas fa-toggle-on',
                                                                    'rating' => 'fas fa-star'
                                                                ];
                                                                $icon = $icons[$type['name']] ?? 'fas fa-question';
                                                                ?>
                                                                <i class="<?= $icon ?> fa-2x text-primary"></i>
                                                            </div>
                                                            <h6 class="card-title"><?= htmlspecialchars($type['description']) ?></h6>
                                                            <div class="type-examples">
                                                                <?php
                                                                $examples = [
                                                                    'likert_5' => '1-2-3-4-5',
                                                                    'likert_7' => '1-2-3-4-5-6-7',
                                                                    'multiple_choice' => '☑ ☑ ☑',
                                                                    'single_choice' => '○ ● ○',
                                                                    'text' => '[_____]',
                                                                    'numeric' => '[123]',
                                                                    'yes_no' => 'Sí / No',
                                                                    'rating' => '★★★★★'
                                                                ];
                                                                echo $examples[$type['name']] ?? '?';
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="question_type_id" id="selected-question-type" required>
                                    <div class="invalid-feedback">Por favor selecciona un tipo de pregunta.</div>
                                </div>
                            </div>
                            
                            <!-- Paso 2: Contenido de la Pregunta -->
                            <div class="step-content" data-step="2">
                                <h5 class="step-title mb-3">
                                    <i class="fas fa-edit text-primary me-2"></i>
                                    Escribe el contenido de la pregunta
                                </h5>
                                
                                <!-- Texto de la pregunta -->
                                <div class="form-group mb-4">
                                    <label for="step2-question-text" class="form-label fw-bold">
                                        Texto de la pregunta <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="question_text" 
                                              id="step2-question-text" 
                                              class="form-control" 
                                              rows="4" 
                                              placeholder="¿Cuál es tu pregunta?"
                                              maxlength="1000"
                                              required></textarea>
                                    <div class="form-text d-flex justify-content-between">
                                        <span>Sé claro y específico en tu pregunta</span>
                                        <span id="step2-char-counter">0/1000</span>
                                    </div>
                                    <div class="invalid-feedback">El texto de la pregunta es obligatorio.</div>
                                </div>
                                
                                <!-- Descripción opcional -->
                                <div class="form-group mb-4">
                                    <label for="step2-description" class="form-label fw-bold">
                                        Descripción o ayuda <span class="text-muted">(opcional)</span>
                                    </label>
                                    <textarea name="description" 
                                              id="step2-description" 
                                              class="form-control" 
                                              rows="2" 
                                              placeholder="Proporciona contexto adicional o instrucciones..."
                                              maxlength="500"></textarea>
                                    <div class="form-text">
                                        Ayuda a los participantes a entender mejor la pregunta
                                    </div>
                                </div>
                                
                                <!-- Vista previa -->
                                <div class="preview-section">
                                    <label class="form-label fw-bold">Vista previa</label>
                                    <div class="question-preview border rounded p-3 bg-light">
                                        <div class="preview-content">
                                            <div id="preview-question-text" class="mb-2">
                                                <em class="text-muted">El texto de la pregunta aparecerá aquí...</em>
                                            </div>
                                            <div id="preview-description" class="small text-muted" style="display: none;"></div>
                                            <div id="preview-options" class="mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Paso 3: Opciones de Respuesta -->
                            <div class="step-content" data-step="3">
                                <h5 class="step-title mb-3">
                                    <i class="fas fa-list text-primary me-2"></i>
                                    Configura las opciones de respuesta
                                </h5>
                                
                                <div id="step3-options-container">
                                    <!-- Contenido generado dinámicamente según el tipo de pregunta -->
                                    <div class="options-info text-center py-4 text-muted">
                                        <i class="fas fa-arrow-left fa-2x mb-2"></i>
                                        <p>Primero selecciona el tipo de pregunta en el paso anterior</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Paso 4: Configuración Avanzada -->
                            <div class="step-content" data-step="4">
                                <h5 class="step-title mb-3">
                                    <i class="fas fa-cogs text-primary me-2"></i>
                                    Configuración final
                                </h5>
                                
                                <!-- Configuraciones -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="required" 
                                                       id="step4-required"
                                                       value="1">
                                                <label class="form-check-label fw-bold" for="step4-required">
                                                    <i class="fas fa-asterisk text-danger me-1"></i>
                                                    Pregunta obligatoria
                                                </label>
                                            </div>
                                            <div class="form-text">
                                                Los participantes deben responder esta pregunta para continuar
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mb-3" id="step4-allow-na" style="display: none;">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="allow_na" 
                                                       id="step4-allow-na-input"
                                                       value="1">
                                                <label class="form-check-label" for="step4-allow-na-input">
                                                    Incluir opción "No aplica"
                                                </label>
                                            </div>
                                            <div class="form-text">
                                                Permite que los participantes indiquen que la pregunta no les aplica
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="step4-order" class="form-label fw-bold">
                                                Posición en la encuesta
                                            </label>
                                            <input type="number" 
                                                   name="order_position" 
                                                   id="step4-order" 
                                                   class="form-control"
                                                   min="1" 
                                                   placeholder="Automático">
                                            <div class="form-text">
                                                Déjalo vacío para posición automática
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Resumen de la pregunta -->
                                <div class="question-summary">
                                    <h6 class="fw-bold mb-2">Resumen de tu pregunta:</h6>
                                    <div class="summary-card border rounded p-3 bg-light">
                                        <div class="d-flex align-items-start">
                                            <div class="summary-icon me-3">
                                                <i id="summary-icon" class="fas fa-question fa-2x text-primary"></i>
                                            </div>
                                            <div class="summary-content flex-grow-1">
                                                <div class="summary-type">
                                                    <span id="summary-category" class="badge bg-secondary">Categoría</span>
                                                    <span id="summary-type" class="badge bg-info ms-1">Tipo</span>
                                                    <span id="summary-required" class="badge bg-danger ms-1" style="display: none;">
                                                        <i class="fas fa-asterisk"></i> Obligatoria
                                                    </span>
                                                </div>
                                                <div id="summary-text" class="mt-2 fw-bold">Texto de la pregunta</div>
                                                <div id="summary-description" class="text-muted small mt-1" style="display: none;"></div>
                                                <div id="summary-options" class="mt-2" style="display: none;">
                                                    <small class="text-muted">Opciones: <span id="summary-options-count">0</span></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Footer del Modal -->
            <div class="modal-footer bg-light">
                <div class="d-flex justify-content-between w-100">
                    <button type="button" class="btn btn-outline-secondary" id="prev-step-btn" style="display: none;">
                        <i class="fas fa-arrow-left me-2"></i>
                        Anterior
                    </button>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" id="next-step-btn">
                            Siguiente
                            <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        <button type="submit" class="btn btn-success" id="save-question-btn" style="display: none;" form="create-question-form">
                            <span id="save-btn-text">
                                <i class="fas fa-save me-2"></i>
                                Guardar Pregunta
                            </span>
                            <span id="save-btn-loading" style="display: none;">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class QuestionCreator {
    constructor() {
        this.currentStep = 1;
        this.maxSteps = 4;
        this.questionData = {};
        this.isEditing = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateStepUI();
    }
    
    bindEvents() {
        // Navegación entre pasos
        document.getElementById('next-step-btn').addEventListener('click', () => this.nextStep());
        document.getElementById('prev-step-btn').addEventListener('click', () => this.prevStep());
        
        // Form submission
        document.getElementById('create-question-form').addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Step 1 events
        document.getElementById('step1-category').addEventListener('change', (e) => this.handleCategoryChange(e));
        
        // Step 2 events
        document.getElementById('step2-question-text').addEventListener('input', (e) => this.updatePreview());
        document.getElementById('step2-description').addEventListener('input', (e) => this.updatePreview());
        
        // Step 4 events
        document.getElementById('step4-required').addEventListener('change', (e) => this.updateSummary());
        
        // Modal events
        document.getElementById('createQuestionModal').addEventListener('hidden.bs.modal', () => this.resetForm());
    }
    
    nextStep() {
        if (this.validateCurrentStep()) {
            if (this.currentStep < this.maxSteps) {
                this.currentStep++;
                this.updateStepUI();
                
                if (this.currentStep === 3) {
                    this.generateOptionsForStep3();
                } else if (this.currentStep === 4) {
                    this.updateSummary();
                }
            }
        }
    }
    
    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepUI();
        }
    }
    
    updateStepUI() {
        // Actualizar indicadores de pasos
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNumber = index + 1;
            step.classList.toggle('active', stepNumber === this.currentStep);
            step.classList.toggle('completed', stepNumber < this.currentStep);
        });
        
        // Mostrar/ocultar contenido de pasos
        document.querySelectorAll('.step-content').forEach((content, index) => {
            const stepNumber = index + 1;
            content.classList.toggle('active', stepNumber === this.currentStep);
        });
        
        // Actualizar botones de navegación
        const prevBtn = document.getElementById('prev-step-btn');
        const nextBtn = document.getElementById('next-step-btn');
        const saveBtn = document.getElementById('save-question-btn');
        
        prevBtn.style.display = this.currentStep > 1 ? 'inline-block' : 'none';
        
        if (this.currentStep < this.maxSteps) {
            nextBtn.style.display = 'inline-block';
            saveBtn.style.display = 'none';
        } else {
            nextBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        }
    }
    
    validateCurrentStep() {
        const currentContent = document.querySelector(`[data-step="${this.currentStep}"]`);
        const requiredFields = currentContent.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Validaciones específicas por paso
        if (this.currentStep === 1) {
            const questionType = document.getElementById('selected-question-type');
            if (!questionType.value) {
                this.showAlert('Por favor selecciona un tipo de pregunta', 'warning');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    handleCategoryChange(e) {
        const selectedOption = e.target.selectedOptions[0];
        const description = selectedOption?.dataset?.description || '';
        const descriptionDiv = document.getElementById('step1-category-description');
        
        if (description) {
            descriptionDiv.textContent = description;
            descriptionDiv.style.display = 'block';
        } else {
            descriptionDiv.style.display = 'none';
        }
    }
    
    selectQuestionType(typeId, typeName) {
        // Limpiar selecciones anteriores
        document.querySelectorAll('.question-type-card .card').forEach(card => {
            card.classList.remove('border-primary', 'bg-light');
        });
        
        // Marcar como seleccionado
        const selectedCard = document.querySelector(`[data-type-id="${typeId}"] .card`);
        if (selectedCard) {
            selectedCard.classList.add('border-primary', 'bg-light');
        }
        
        // Actualizar campo oculto
        document.getElementById('selected-question-type').value = typeId;
        
        // Guardar tipo para uso posterior
        this.questionData.questionType = typeName;
        this.questionData.questionTypeId = typeId;
    }
    
    updatePreview() {
        const questionText = document.getElementById('step2-question-text').value;
        const description = document.getElementById('step2-description').value;
        const charCounter = document.getElementById('step2-char-counter');
        
        // Actualizar contador de caracteres
        charCounter.textContent = `${questionText.length}/1000`;
        
        // Actualizar vista previa
        const previewText = document.getElementById('preview-question-text');
        const previewDescription = document.getElementById('preview-description');
        
        if (questionText) {
            previewText.innerHTML = `<strong>${questionText}</strong>`;
        } else {
            previewText.innerHTML = '<em class="text-muted">El texto de la pregunta aparecerá aquí...</em>';
        }
        
        if (description) {
            previewDescription.textContent = description;
            previewDescription.style.display = 'block';
        } else {
            previewDescription.style.display = 'none';
        }
        
        this.generatePreviewOptions();
    }
    
    generatePreviewOptions() {
        const previewOptions = document.getElementById('preview-options');
        const questionType = this.questionData.questionType;
        
        let optionsHTML = '';
        
        switch (questionType) {
            case 'likert_5':
                optionsHTML = `
                    <div class="d-flex justify-content-between">
                        <label><input type="radio" disabled> Totalmente en desacuerdo</label>
                        <label><input type="radio" disabled> En desacuerdo</label>
                        <label><input type="radio" disabled> Neutral</label>
                        <label><input type="radio" disabled> De acuerdo</label>
                        <label><input type="radio" disabled> Totalmente de acuerdo</label>
                    </div>
                `;
                break;
            case 'yes_no':
                optionsHTML = `
                    <div>
                        <label class="me-4"><input type="radio" disabled> Sí</label>
                        <label><input type="radio" disabled> No</label>
                    </div>
                `;
                break;
            case 'text':
                optionsHTML = '<textarea class="form-control" disabled placeholder="Campo de respuesta libre..."></textarea>';
                break;
            case 'numeric':
                optionsHTML = '<input type="number" class="form-control" style="max-width: 200px;" disabled placeholder="Respuesta numérica">';
                break;
            case 'rating':
                optionsHTML = `
                    <div>
                        <span class="rating-stars">
                            ☆☆☆☆☆
                        </span>
                        <small class="text-muted ms-2">Califica del 1 al 5</small>
                    </div>
                `;
                break;
        }
        
        previewOptions.innerHTML = optionsHTML;
    }
    
    generateOptionsForStep3() {
        const container = document.getElementById('step3-options-container');
        const questionType = this.questionData.questionType;
        
        let optionsHTML = '';
        
        switch (questionType) {
            case 'likert_5':
                optionsHTML = this.generateLikertOptions(5);
                break;
            case 'likert_7':
                optionsHTML = this.generateLikertOptions(7);
                break;
            case 'yes_no':
                optionsHTML = this.generateYesNoOptions();
                break;
            case 'multiple_choice':
            case 'single_choice':
                optionsHTML = this.generateCustomOptions();
                break;
            case 'rating':
                optionsHTML = this.generateRatingOptions();
                break;
            case 'text':
            case 'numeric':
                optionsHTML = this.generateNoOptionsMessage();
                break;
        }
        
        container.innerHTML = optionsHTML;
        
        // Mostrar/ocultar opción "No aplica" según el tipo
        const allowNaContainer = document.getElementById('step4-allow-na');
        if (['likert_5', 'likert_7', 'multiple_choice', 'single_choice', 'rating'].includes(questionType)) {
            allowNaContainer.style.display = 'block';
        } else {
            allowNaContainer.style.display = 'none';
        }
    }
    
    generateLikertOptions(scale) {
        const labels = scale === 5 
            ? ['Totalmente en desacuerdo', 'En desacuerdo', 'Neutral', 'De acuerdo', 'Totalmente de acuerdo']
            : ['Totalmente en desacuerdo', 'En desacuerdo', 'Algo en desacuerdo', 'Neutral', 'Algo de acuerdo', 'De acuerdo', 'Totalmente de acuerdo'];
        
        let optionsHTML = `
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Escala Likert de ${scale} puntos</h6>
                <p class="mb-0">Esta escala está predefinida y no requiere configuración adicional.</p>
            </div>
            <div class="options-preview">
        `;
        
        labels.forEach((label, index) => {
            optionsHTML += `
                <div class="option-item d-flex align-items-center mb-2">
                    <span class="option-value badge bg-primary me-3">${index + 1}</span>
                    <span class="option-text">${label}</span>
                </div>
            `;
        });
        
        optionsHTML += '</div>';
        return optionsHTML;
    }
    
    generateYesNoOptions() {
        return `
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Pregunta Sí/No</h6>
                <p class="mb-0">Esta pregunta tiene opciones predefinidas.</p>
            </div>
            <div class="options-preview">
                <div class="option-item d-flex align-items-center mb-2">
                    <span class="option-value badge bg-success me-3">1</span>
                    <span class="option-text">Sí</span>
                </div>
                <div class="option-item d-flex align-items-center mb-2">
                    <span class="option-value badge bg-secondary me-3">0</span>
                    <span class="option-text">No</span>
                </div>
            </div>
        `;
    }
    
    generateCustomOptions() {
        return `
            <div class="custom-options-section">
                <h6><i class="fas fa-list me-2"></i>Opciones personalizadas</h6>
                <p class="text-muted">Define las opciones que estarán disponibles para esta pregunta.</p>
                
                <div id="custom-options-list">
                    <div class="option-input mb-3">
                        <div class="input-group">
                            <span class="input-group-text">1.</span>
                            <input type="text" class="form-control" name="option_text[]" placeholder="Texto de la opción" required>
                            <input type="number" class="form-control" name="option_value[]" placeholder="Valor" style="max-width: 100px;" required>
                            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.option-input').remove()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="option-input mb-3">
                        <div class="input-group">
                            <span class="input-group-text">2.</span>
                            <input type="text" class="form-control" name="option_text[]" placeholder="Texto de la opción" required>
                            <input type="number" class="form-control" name="option_value[]" placeholder="Valor" style="max-width: 100px;" required>
                            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.option-input').remove()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="questionCreator.addCustomOption()">
                    <i class="fas fa-plus me-2"></i>Agregar Opción
                </button>
            </div>
        `;
    }
    
    generateRatingOptions() {
        return `
            <div class="alert alert-info">
                <h6><i class="fas fa-star me-2"></i>Calificación con estrellas</h6>
                <p class="mb-0">Calificación del 1 al 5 con estrellas. Las opciones están predefinidas.</p>
            </div>
            <div class="options-preview">
                <div class="rating-preview text-center py-3">
                    <div class="rating-stars" style="font-size: 1.5em; color: #ffc107;">
                        ☆☆☆☆☆
                    </div>
                    <small class="text-muted d-block mt-2">Los participantes pueden calificar del 1 al 5</small>
                </div>
            </div>
        `;
    }
    
    generateNoOptionsMessage() {
        const messages = {
            'text': {
                icon: 'fas fa-keyboard',
                title: 'Respuesta de texto libre',
                description: 'Los participantes pueden escribir su respuesta libremente. No requiere opciones predefinidas.'
            },
            'numeric': {
                icon: 'fas fa-calculator',
                title: 'Respuesta numérica',
                description: 'Los participantes ingresan un valor numérico. No requiere opciones predefinidas.'
            }
        };
        
        const config = messages[this.questionData.questionType];
        
        return `
            <div class="alert alert-info text-center">
                <i class="${config.icon} fa-3x mb-3"></i>
                <h6>${config.title}</h6>
                <p class="mb-0">${config.description}</p>
            </div>
        `;
    }
    
    addCustomOption() {
        const optionsList = document.getElementById('custom-options-list');
        const optionCount = optionsList.children.length + 1;
        
        const optionHTML = `
            <div class="option-input mb-3">
                <div class="input-group">
                    <span class="input-group-text">${optionCount}.</span>
                    <input type="text" class="form-control" name="option_text[]" placeholder="Texto de la opción" required>
                    <input type="number" class="form-control" name="option_value[]" placeholder="Valor" style="max-width: 100px;" required>
                    <button type="button" class="btn btn-outline-danger" onclick="this.closest('.option-input').remove()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        optionsList.insertAdjacentHTML('beforeend', optionHTML);
    }
    
    updateSummary() {
        const categorySelect = document.getElementById('step1-category');
        const questionText = document.getElementById('step2-question-text').value;
        const description = document.getElementById('step2-description').value;
        const isRequired = document.getElementById('step4-required').checked;
        
        // Actualizar elementos del resumen
        document.getElementById('summary-category').textContent = categorySelect.selectedOptions[0]?.textContent || 'Categoría';
        document.getElementById('summary-type').textContent = this.getQuestionTypeDescription();
        document.getElementById('summary-text').textContent = questionText || 'Texto de la pregunta';
        
        const summaryDescription = document.getElementById('summary-description');
        if (description) {
            summaryDescription.textContent = description;
            summaryDescription.style.display = 'block';
        } else {
            summaryDescription.style.display = 'none';
        }
        
        const requiredBadge = document.getElementById('summary-required');
        requiredBadge.style.display = isRequired ? 'inline-block' : 'none';
        
        // Actualizar icono según el tipo
        const summaryIcon = document.getElementById('summary-icon');
        const icons = {
            'likert_5': 'fas fa-star-half-alt',
            'likert_7': 'fas fa-star',
            'multiple_choice': 'fas fa-check-square',
            'single_choice': 'fas fa-dot-circle',
            'text': 'fas fa-keyboard',
            'numeric': 'fas fa-calculator',
            'yes_no': 'fas fa-toggle-on',
            'rating': 'fas fa-star'
        };
        summaryIcon.className = icons[this.questionData.questionType] || 'fas fa-question';
    }
    
    getQuestionTypeDescription() {
        const descriptions = {
            'likert_5': 'Likert 5 puntos',
            'likert_7': 'Likert 7 puntos',
            'multiple_choice': 'Opción múltiple',
            'single_choice': 'Selección única',
            'text': 'Texto libre',
            'numeric': 'Numérica',
            'yes_no': 'Sí/No',
            'rating': 'Calificación'
        };
        
        return descriptions[this.questionData.questionType] || 'Tipo';
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateCurrentStep()) {
            return;
        }
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        // Procesar opciones personalizadas si existen
        if (document.getElementById('custom-options-list')) {
            const optionTexts = formData.getAll('option_text[]');
            const optionValues = formData.getAll('option_value[]');
            
            data.options = [];
            optionTexts.forEach((text, index) => {
                if (text.trim()) {
                    data.options.push({
                        text: text.trim(),
                        value: parseInt(optionValues[index]) || index + 1
                    });
                }
            });
        }
        
        this.saveQuestion(data);
    }
    
    saveQuestion(data) {
        const saveBtn = document.getElementById('save-question-btn');
        const saveBtnText = document.getElementById('save-btn-text');
        const saveBtnLoading = document.getElementById('save-btn-loading');
        
        // Mostrar estado de carga
        saveBtnText.style.display = 'none';
        saveBtnLoading.style.display = 'inline-flex';
        saveBtn.disabled = true;
        
        const url = this.isEditing ? `/admin/questions/${data.question_id}` : '/admin/questions';
        const method = this.isEditing ? 'PUT' : 'POST';
        
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
                this.showAlert(`Pregunta ${this.isEditing ? 'actualizada' : 'creada'} exitosamente`, 'success');
                
                // Cerrar modal y recargar página
                const modal = bootstrap.Modal.getInstance(document.getElementById('createQuestionModal'));
                modal.hide();
                
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                this.showAlert(result.message || 'Error al guardar la pregunta', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving question:', error);
            this.showAlert('Error al guardar la pregunta', 'error');
        })
        .finally(() => {
            // Restaurar estado del botón
            saveBtnText.style.display = 'inline-flex';
            saveBtnLoading.style.display = 'none';
            saveBtn.disabled = false;
        });
    }
    
    resetForm() {
        this.currentStep = 1;
        this.questionData = {};
        this.isEditing = false;
        
        const form = document.getElementById('create-question-form');
        form.reset();
        form.classList.remove('was-validated');
        
        // Limpiar selecciones
        document.querySelectorAll('.question-type-card .card').forEach(card => {
            card.classList.remove('border-primary', 'bg-light');
        });
        
        this.updateStepUI();
        
        // Restaurar título del modal
        document.getElementById('modal-title').textContent = 'Nueva Pregunta';
    }
    
    loadQuestionForEdit(questionData) {
        this.isEditing = true;
        this.questionData = questionData;
        
        // Cambiar título del modal
        document.getElementById('modal-title').textContent = 'Editar Pregunta';
        
        // Llenar formulario con datos existentes
        document.getElementById('edit-question-id').value = questionData.id;
        document.getElementById('step1-category').value = questionData.category_id;
        document.getElementById('step2-question-text').value = questionData.question_text;
        document.getElementById('step2-description').value = questionData.description || '';
        document.getElementById('step4-required').checked = questionData.required;
        document.getElementById('step4-order').value = questionData.order_position || '';
        
        // Seleccionar tipo de pregunta
        this.selectQuestionType(questionData.question_type_id, questionData.question_type_name);
        
        // Disparar eventos para actualizar UI
        document.getElementById('step1-category').dispatchEvent(new Event('change'));
        this.updatePreview();
    }
    
    showAlert(message, type = 'info') {
        // Implementar sistema de alertas (toast, modal, etc.)
        if (typeof showAlert === 'function') {
            showAlert(message, type);
        } else {
            alert(message);
        }
    }
}

// Instanciar el creador de preguntas cuando el DOM esté listo
let questionCreator;
document.addEventListener('DOMContentLoaded', function() {
    questionCreator = new QuestionCreator();
    
    // Hacer la función selectQuestionType global
    window.selectQuestionType = (typeId, typeName) => questionCreator.selectQuestionType(typeId, typeName);
});

// Funciones globales para abrir el modal
function openCreateQuestionModal(surveyId = null) {
    if (surveyId) {
        document.querySelector('[name="survey_id"]').value = surveyId;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('createQuestionModal'));
    modal.show();
}

function openEditQuestionModal(questionData) {
    questionCreator.loadQuestionForEdit(questionData);
    
    const modal = new bootstrap.Modal(document.getElementById('createQuestionModal'));
    modal.show();
}
</script>

<style>
/* Estilos para el modal de creación de preguntas */
.create-question-steps {
    background: #fff;
}

.steps-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.steps-progress {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2rem;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.step.active,
.step.completed {
    opacity: 1;
}

.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: #007bff;
    transform: scale(1.1);
}

.step.completed .step-number {
    background: #28a745;
}

.step-label {
    font-size: 0.85rem;
    font-weight: 500;
    text-align: center;
}

.step-content {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}

.step-content.active {
    display: block;
}

.question-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.question-type-card {
    cursor: pointer;
    transition: all 0.2s ease;
}

.question-type-card:hover .card {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.question-type-card .card {
    transition: all 0.2s ease;
    height: 100%;
}

.question-type-card .card.border-primary {
    border-width: 2px;
}

.type-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.type-examples {
    font-family: monospace;
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 0.5rem;
}

.question-preview {
    min-height: 100px;
}

.option-input-group {
    position: relative;
}

.options-preview .option-item {
    padding: 0.5rem;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    background: #f8f9fa;
}

.question-summary .summary-card {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.rating-stars {
    font-size: 1.5em;
    color: #ffc107;
    letter-spacing: 0.2em;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .question-types-grid {
        grid-template-columns: 1fr;
    }
    
    .steps-progress {
        gap: 1rem;
    }
    
    .step-label {
        font-size: 0.75rem;
    }
}
</style>

<?php if (!isset($modal_only) || !$modal_only): ?>
<?php require_once __DIR__ . '/../../layouts/admin_footer.php'; ?>
<?php endif; ?>