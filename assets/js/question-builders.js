/**
 * Sistema de Constructor Visual de Preguntas - HERCO v2.0
 * 
 * JavaScript avanzado para drag-and-drop, edición en tiempo real
 * y gestión completa del constructor de encuestas HERCO
 * 
 * @package HERCO\Assets\JS
 * @version 2.0.0
 * @author Sistema HERCO
 * @requires SortableJS, Bootstrap 5
 */

'use strict';

/**
 * Clase principal del constructor de preguntas
 */
class QuestionBuilder {
    constructor(options = {}) {
        this.options = {
            surveyId: null,
            autoSaveDelay: 2000,
            maxQuestions: 100,
            enableAutoSave: true,
            enableDragDrop: true,
            enablePreview: true,
            ...options
        };
        
        // Estado del constructor
        this.state = {
            currentEditingQuestion: null,
            questionCounter: 0,
            isDirty: false,
            isAutoSaving: false,
            lastSaveTime: null,
            questions: new Map()
        };
        
        // Referencias DOM
        this.elements = {};
        
        // Timers
        this.autoSaveTimer = null;
        this.previewTimer = null;
        
        // Instancias Sortable
        this.sortableInstances = new Map();
        
        // Configuraciones HERCO
        this.hercoConfig = this.initializeHercoConfig();
        
        // Inicializar
        this.init();
    }
    
    /**
     * Inicializar el constructor
     */
    init() {
        this.setupDOM();
        this.initializeSortable();
        this.bindEvents();
        this.loadExistingQuestions();
        this.setupAutoSave();
        
        console.log('🎯 QuestionBuilder inicializado correctamente');
    }
    
    /**
     * Configurar referencias DOM
     */
    setupDOM() {
        this.elements = {
            // Contenedores principales
            questionsList: document.getElementById('questionsList'),
            questionTypes: document.getElementById('questionTypes'),
            questionEditor: document.getElementById('questionEditor'),
            previewPanel: document.getElementById('previewPanel'),
            
            // Formulario del editor
            questionForm: document.getElementById('questionForm'),
            questionText: document.getElementById('questionText'),
            questionDescription: document.getElementById('questionDescription'),
            questionType: document.getElementById('questionType'),
            questionCategory: document.getElementById('questionCategory'),
            questionRequired: document.getElementById('questionRequired'),
            questionOptions: document.getElementById('questionOptions'),
            
            // Elementos de estado
            questionCounter: document.getElementById('questionCount'),
            autoSaveIndicator: document.getElementById('autoSaveIndicator'),
            emptyState: document.getElementById('emptyState'),
            
            // Paneles de pestañas
            typesPanel: document.getElementById('types-panel'),
            templatesPanel: document.getElementById('templates-panel'),
            categoriesPanel: document.getElementById('categories-panel')
        };
        
        // Verificar elementos críticos
        if (!this.elements.questionsList || !this.elements.questionTypes) {
            throw new Error('Elementos DOM críticos no encontrados');
        }
    }
    
    /**
     * Inicializar configuración HERCO
     */
    initializeHercoConfig() {
        return {
            categories: {
                'satisfaccion': { name: 'Satisfacción Laboral', color: '#e74c3c', icon: 'fas fa-heart' },
                'participacion': { name: 'Participación y Autonomía', color: '#3498db', icon: 'fas fa-users' },
                'comunicacion': { name: 'Comunicación y Objetivos', color: '#2ecc71', icon: 'fas fa-comments' },
                'equilibrio': { name: 'Equilibrio y Evaluación', color: '#f39c12', icon: 'fas fa-balance-scale' },
                'distribucion': { name: 'Distribución y Carga de Trabajo', color: '#9b59b6', icon: 'fas fa-tasks' },
                'reconocimiento': { name: 'Reconocimiento y Promoción', color: '#1abc9c', icon: 'fas fa-trophy' },
                'ambiente': { name: 'Ambiente de Trabajo', color: '#34495e', icon: 'fas fa-building' },
                'capacitacion': { name: 'Capacitación', color: '#e67e22', icon: 'fas fa-graduation-cap' },
                'tecnologia': { name: 'Tecnología y Recursos', color: '#95a5a6', icon: 'fas fa-laptop' },
                'colaboracion': { name: 'Colaboración y Compañerismo', color: '#f1c40f', icon: 'fas fa-handshake' },
                'normativas': { name: 'Normativas y Regulaciones', color: '#8e44ad', icon: 'fas fa-gavel' },
                'compensacion': { name: 'Compensación y Beneficios', color: '#27ae60', icon: 'fas fa-dollar-sign' },
                'bienestar': { name: 'Bienestar y Salud', color: '#e91e63', icon: 'fas fa-spa' },
                'seguridad': { name: 'Seguridad en el Trabajo', color: '#ff5722', icon: 'fas fa-shield-alt' },
                'informacion': { name: 'Información y Comunicación', color: '#607d8b', icon: 'fas fa-info-circle' },
                'supervisores': { name: 'Relaciones con Supervisores', color: '#795548', icon: 'fas fa-user-tie' },
                'feedback': { name: 'Feedback y Reconocimiento', color: '#009688', icon: 'fas fa-comment-dots' },
                'diversidad': { name: 'Diversidad e Inclusión', color: '#673ab7', icon: 'fas fa-globe' }
            },
            
            questionTypes: {
                'likert_5': {
                    name: 'Escala Likert 1-5',
                    icon: 'fas fa-star',
                    description: 'Muy en desacuerdo a Muy de acuerdo',
                    defaultOptions: ['Muy en desacuerdo', 'En desacuerdo', 'Neutral', 'De acuerdo', 'Muy de acuerdo'],
                    hasOptions: true,
                    validation: { min: 3, max: 7 }
                },
                'likert_7': {
                    name: 'Escala Likert 1-7',
                    icon: 'fas fa-chart-bar',
                    description: 'Escala extendida 1-7',
                    defaultOptions: ['Completamente en desacuerdo', 'Muy en desacuerdo', 'En desacuerdo', 'Neutral', 'De acuerdo', 'Muy de acuerdo', 'Completamente de acuerdo'],
                    hasOptions: true,
                    validation: { min: 5, max: 9 }
                },
                'likert_3': {
                    name: 'Escala Likert 1-3',
                    icon: 'fas fa-thumbs-up',
                    description: 'Básica: Malo, Regular, Bueno',
                    defaultOptions: ['Malo', 'Regular', 'Bueno'],
                    hasOptions: true,
                    validation: { min: 2, max: 5 }
                },
                'multiple_choice': {
                    name: 'Opción Múltiple',
                    icon: 'fas fa-list-ul',
                    description: 'Selección única',
                    defaultOptions: ['Opción 1', 'Opción 2', 'Opción 3'],
                    hasOptions: true,
                    validation: { min: 2, max: 10 }
                },
                'checkbox': {
                    name: 'Casillas de Verificación',
                    icon: 'fas fa-check-square',
                    description: 'Selección múltiple',
                    defaultOptions: ['Opción 1', 'Opción 2', 'Opción 3'],
                    hasOptions: true,
                    validation: { min: 2, max: 10 }
                },
                'text': {
                    name: 'Texto Corto',
                    icon: 'fas fa-font',
                    description: 'Respuesta de texto',
                    hasOptions: false,
                    validation: { maxLength: 500 }
                },
                'textarea': {
                    name: 'Texto Largo',
                    icon: 'fas fa-align-left',
                    description: 'Comentarios extensos',
                    hasOptions: false,
                    validation: { maxLength: 2000 }
                },
                'rating': {
                    name: 'Calificación',
                    icon: 'fas fa-star-half-alt',
                    description: 'Estrellas 1-5',
                    hasOptions: false,
                    validation: { min: 1, max: 10 }
                },
                'slider': {
                    name: 'Deslizador',
                    icon: 'fas fa-sliders-h',
                    description: 'Valor en rango',
                    hasOptions: false,
                    validation: { min: 0, max: 100 }
                },
                'yes_no': {
                    name: 'Sí/No',
                    icon: 'fas fa-toggle-on',
                    description: 'Respuesta binaria',
                    defaultOptions: ['Sí', 'No'],
                    hasOptions: false
                },
                'date': {
                    name: 'Fecha',
                    icon: 'fas fa-calendar',
                    description: 'Selector de fecha',
                    hasOptions: false
                },
                'number': {
                    name: 'Número',
                    icon: 'fas fa-hashtag',
                    description: 'Valor numérico',
                    hasOptions: false,
                    validation: { min: -999999, max: 999999 }
                },
                'nps': {
                    name: 'Net Promoter Score',
                    icon: 'fas fa-chart-line',
                    description: 'Escala 0-10 NPS',
                    hasOptions: false,
                    validation: { min: 0, max: 10 }
                }
            },
            
            templates: {
                'herco_express': {
                    name: 'HERCO Express',
                    description: '5 categorías • 15 preguntas',
                    icon: 'fas fa-bolt',
                    questions: [
                        { type: 'likert_5', text: '¿Qué tan satisfecho está con su trabajo actual?', category: 'satisfaccion', required: true },
                        { type: 'likert_5', text: '¿Siente que puede tomar decisiones en su trabajo?', category: 'participacion', required: true },
                        { type: 'likert_5', text: '¿La comunicación en su empresa es efectiva?', category: 'comunicacion', required: true },
                        { type: 'likert_5', text: '¿Tiene un buen equilibrio trabajo-vida?', category: 'equilibrio', required: true },
                        { type: 'likert_5', text: '¿Su carga de trabajo es adecuada?', category: 'distribucion', required: true }
                    ]
                },
                'herco_basica': {
                    name: 'HERCO Básica',
                    description: '9 categorías • 27 preguntas',
                    icon: 'fas fa-star',
                    questions: [] // Se cargaría dinámicamente
                },
                'herco_completa': {
                    name: 'HERCO Completa',
                    description: '18 categorías • 54 preguntas',
                    icon: 'fas fa-crown',
                    questions: [] // Se cargaría dinámicamente
                }
            }
        };
    }
    
    /**
     * Inicializar funcionalidad Sortable
     */
    initializeSortable() {
        if (!this.options.enableDragDrop) return;
        
        // Sortable para lista de preguntas (reordenar)
        if (this.elements.questionsList) {
            const questionsListSortable = new Sortable(this.elements.questionsList, {
                group: 'questions',
                animation: 300,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                handle: '.action-btn.drag, .question-header',
                onStart: (evt) => this.onDragStart(evt),
                onEnd: (evt) => this.onDragEnd(evt),
                onAdd: (evt) => this.onQuestionAdd(evt),
                onUpdate: (evt) => this.onQuestionReorder(evt)
            });
            
            this.sortableInstances.set('questionsList', questionsListSortable);
        }
        
        // Sortable para tipos de preguntas (clonar)
        if (this.elements.questionTypes) {
            const questionTypesSortable = new Sortable(this.elements.questionTypes, {
                group: {
                    name: 'questions',
                    pull: 'clone',
                    put: false
                },
                animation: 300,
                sort: false,
                onClone: (evt) => this.onQuestionTypeClone(evt)
            });
            
            this.sortableInstances.set('questionTypes', questionTypesSortable);
        }
        
        console.log('✅ Sortable inicializado correctamente');
    }
    
    /**
     * Vincular eventos
     */
    bindEvents() {
        // Eventos del formulario de edición
        if (this.elements.questionForm) {
            this.elements.questionForm.addEventListener('input', (e) => this.onFormInput(e));
            this.elements.questionForm.addEventListener('change', (e) => this.onFormChange(e));
        }
        
        // Eventos de tipos de pregunta
        if (this.elements.questionType) {
            this.elements.questionType.addEventListener('change', () => this.updateQuestionOptions());
        }
        
        // Eventos de plantillas
        document.querySelectorAll('.template-item').forEach(item => {
            item.addEventListener('click', (e) => this.onTemplateClick(e));
        });
        
        // Eventos de categorías
        document.querySelectorAll('.category-badge').forEach(badge => {
            badge.addEventListener('click', (e) => this.onCategoryClick(e));
        });
        
        // Eventos delegados para preguntas
        if (this.elements.questionsList) {
            this.elements.questionsList.addEventListener('click', (e) => this.onQuestionListClick(e));
        }
        
        // Eventos de teclado
        document.addEventListener('keydown', (e) => this.onKeyDown(e));
        
        // Eventos de ventana
        window.addEventListener('beforeunload', (e) => this.onBeforeUnload(e));
        
        console.log('✅ Eventos vinculados correctamente');
    }
    
    /**
     * Manejar inicio de arrastre
     */
    onDragStart(evt) {
        evt.item.classList.add('dragging');
        document.body.classList.add('is-dragging');
    }
    
    /**
     * Manejar fin de arrastre
     */
    onDragEnd(evt) {
        evt.item.classList.remove('dragging');
        document.body.classList.remove('is-dragging');
        
        // Si es un elemento de tipo de pregunta, crear nueva pregunta
        if (evt.from.id === 'questionTypes' && evt.to.id === 'questionsList') {
            const questionType = evt.item.dataset.type;
            if (questionType) {
                this.addQuestionFromType(questionType, evt.newIndex);
                evt.item.remove(); // Remover el clon
            }
        }
    }
    
    /**
     * Manejar adición de pregunta via drag & drop
     */
    onQuestionAdd(evt) {
        const questionType = evt.item.dataset.type;
        if (questionType) {
            this.addQuestionFromType(questionType, evt.newIndex);
            evt.item.remove();
        }
    }
    
    /**
     * Manejar reordenamiento de preguntas
     */
    onQuestionReorder(evt) {
        this.updateQuestionOrder();
        this.markDirty();
        this.scheduleAutoSave();
    }
    
    /**
     * Manejar clonación de tipo de pregunta
     */
    onQuestionTypeClone(evt) {
        const clone = evt.clone;
        clone.classList.add('question-type-clone');
    }
    
    /**
     * Agregar pregunta desde tipo
     */
    addQuestionFromType(questionType, position = -1) {
        if (!this.hercoConfig.questionTypes[questionType]) {
            console.error('Tipo de pregunta no válido:', questionType);
            return null;
        }
        
        if (this.state.questions.size >= this.options.maxQuestions) {
            this.showNotification('Máximo de preguntas alcanzado', 'warning');
            return null;
        }
        
        const questionConfig = this.hercoConfig.questionTypes[questionType];
        const questionId = this.generateQuestionId();
        
        const questionData = {
            id: questionId,
            type: questionType,
            text: `Nueva pregunta ${questionConfig.name}`,
            description: '',
            category: '',
            required: false,
            options: questionConfig.defaultOptions ? [...questionConfig.defaultOptions] : [],
            order: this.state.questions.size + 1,
            validation: { ...questionConfig.validation }
        };
        
        // Crear elemento DOM
        const questionElement = this.createQuestionElement(questionData);
        
        // Insertar en posición
        const questionsContainer = this.elements.questionsList;
        if (position >= 0 && position < questionsContainer.children.length) {
            questionsContainer.insertBefore(questionElement, questionsContainer.children[position]);
        } else {
            questionsContainer.appendChild(questionElement);
        }
        
        // Actualizar estado
        this.state.questions.set(questionId, questionData);
        this.updateQuestionCounter();
        this.hideEmptyState();
        this.markDirty();
        this.scheduleAutoSave();
        
        // Editar automáticamente la nueva pregunta
        setTimeout(() => this.editQuestion(questionId), 100);
        
        this.showNotification(`Pregunta ${questionConfig.name} agregada`, 'success');
        
        return questionData;
    }
    
    /**
     * Crear elemento DOM de pregunta
     */
    createQuestionElement(questionData) {
        const questionElement = document.createElement('div');
        questionElement.className = 'question-item mb-3 p-4';
        questionElement.dataset.questionId = questionData.id;
        questionElement.dataset.order = questionData.order;
        questionElement.dataset.type = questionData.type;
        
        questionElement.innerHTML = this.generateQuestionHTML(questionData);
        
        return questionElement;
    }
    
    /**
     * Generar HTML de pregunta
     */
    generateQuestionHTML(questionData) {
        const categoryInfo = this.hercoConfig.categories[questionData.category];
        const typeInfo = this.hercoConfig.questionTypes[questionData.type];
        
        return `
            <!-- Acciones de la pregunta -->
            <div class="question-actions">
                <button class="action-btn edit" data-action="edit" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-btn duplicate" data-action="duplicate" title="Duplicar">
                    <i class="fas fa-copy"></i>
                </button>
                <button class="action-btn delete" data-action="delete" title="Eliminar">
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
                        <span class="badge bg-primary me-2 question-number">${questionData.order}</span>
                        <span class="badge bg-info me-2">
                            <i class="${typeInfo.icon}"></i>
                            ${typeInfo.name}
                        </span>
                        ${categoryInfo ? `
                        <span class="category-badge" style="background-color: ${categoryInfo.color}">
                            <i class="${categoryInfo.icon}"></i>
                            ${categoryInfo.name}
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
                    ${this.escapeHtml(questionData.text)}
                </h6>
                
                ${questionData.description ? `
                <p class="question-description text-muted small mb-3">
                    ${this.escapeHtml(questionData.description)}
                </p>
                ` : ''}

                <!-- Preview de opciones según el tipo -->
                <div class="question-preview">
                    ${this.renderQuestionPreview(questionData)}
                </div>
            </div>
        `;
    }
    
    /**
     * Renderizar preview de pregunta
     */
    renderQuestionPreview(questionData) {
        const type = questionData.type;
        const options = questionData.options || [];
        
        switch (type) {
            case 'likert_5':
            case 'likert_7':
            case 'likert_3':
                return `
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        ${options.map((option, index) => `
                            <div class="text-center mb-2" style="min-width: ${100/options.length}%">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="radio" disabled>
                                </div>
                                <small class="text-muted">${this.escapeHtml(option)}</small>
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
                                <label class="form-check-label">${this.escapeHtml(option)}</label>
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
                                <label class="form-check-label">${this.escapeHtml(option)}</label>
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
                return `
                    <div class="d-flex align-items-center gap-3">
                        <small>0</small>
                        <input type="range" class="form-range flex-grow-1" min="0" max="100" disabled>
                        <small>100</small>
                    </div>
                `;
                
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
                
            case 'nps':
                return `
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Muy poco probable</small>
                        <div class="d-flex gap-1">
                            ${[0,1,2,3,4,5,6,7,8,9,10].map(i => `
                                <button class="btn btn-outline-primary btn-sm" disabled style="min-width: 35px">${i}</button>
                            `).join('')}
                        </div>
                        <small class="text-muted">Muy probable</small>
                    </div>
                `;
                
            default:
                return '<div class="text-muted"><i class="fas fa-question-circle"></i> Vista previa no disponible</div>';
        }
    }
    
    /**
     * Manejar click en lista de preguntas
     */
    onQuestionListClick(evt) {
        const questionItem = evt.target.closest('.question-item');
        if (!questionItem) return;
        
        const questionId = questionItem.dataset.questionId;
        const action = evt.target.closest('[data-action]')?.dataset.action;
        
        switch (action) {
            case 'edit':
                this.editQuestion(questionId);
                break;
            case 'duplicate':
                this.duplicateQuestion(questionId);
                break;
            case 'delete':
                this.deleteQuestion(questionId);
                break;
            default:
                // Click en el contenido de la pregunta
                if (!evt.target.closest('.question-actions')) {
                    this.editQuestion(questionId);
                }
        }
    }
    
    /**
     * Editar pregunta
     */
    editQuestion(questionId) {
        const questionData = this.state.questions.get(questionId);
        if (!questionData) return;
        
        // Marcar pregunta como editando
        this.clearEditingState();
        const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
        if (questionElement) {
            questionElement.classList.add('editing');
        }
        
        this.state.currentEditingQuestion = questionId;
        
        // Mostrar editor y cargar datos
        this.showEditor();
        this.loadQuestionInEditor(questionData);
    }
    
    /**
     * Cargar pregunta en editor
     */
    loadQuestionInEditor(questionData) {
        if (this.elements.questionText) this.elements.questionText.value = questionData.text;
        if (this.elements.questionDescription) this.elements.questionDescription.value = questionData.description || '';
        if (this.elements.questionType) this.elements.questionType.value = questionData.type;
        if (this.elements.questionCategory) this.elements.questionCategory.value = questionData.category || '';
        if (this.elements.questionRequired) this.elements.questionRequired.checked = questionData.required;
        
        // Actualizar opciones del tipo
        this.updateQuestionOptions();
        
        // Cargar opciones específicas si las hay
        if (questionData.options && questionData.options.length > 0) {
            setTimeout(() => this.loadOptionsInEditor(questionData.options), 100);
        }
    }
    
    /**
     * Cargar opciones en editor
     */
    loadOptionsInEditor(options) {
        const optionInputs = this.elements.questionOptions.querySelectorAll('input[type="text"]');
        options.forEach((option, index) => {
            if (optionInputs[index]) {
                optionInputs[index].value = option;
            }
        });
    }
    
    /**
     * Actualizar opciones de pregunta según tipo
     */
    updateQuestionOptions() {
        if (!this.elements.questionOptions || !this.elements.questionType) return;
        
        const questionType = this.elements.questionType.value;
        const typeConfig = this.hercoConfig.questionTypes[questionType];
        
        if (!typeConfig || !typeConfig.hasOptions) {
            this.elements.questionOptions.innerHTML = '';
            return;
        }
        
        const options = typeConfig.defaultOptions || ['Opción 1', 'Opción 2', 'Opción 3'];
        
        this.elements.questionOptions.innerHTML = `
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-list"></i>
                    Opciones de respuesta
                </label>
                <div id="optionsList" class="options-list">
                    ${options.map((option, index) => this.generateOptionHTML(option, index)).join('')}
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="questionBuilder.addOption()">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="questionBuilder.resetOptions()">
                        <i class="fas fa-undo"></i> Restablecer
                    </button>
                </div>
            </div>
            
            ${typeConfig.validation ? `
            <div class="mb-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Mínimo: ${typeConfig.validation.min || 'Sin límite'} • 
                    Máximo: ${typeConfig.validation.max || 'Sin límite'} opciones
                </small>
            </div>
            ` : ''}
        `;
        
        this.setupOptionsSortable();
    }
    
    /**
     * Generar HTML de opción
     */
    generateOptionHTML(option, index) {
        return `
            <div class="input-group mb-2 option-item" data-option-index="${index}">
                <span class="input-group-text option-handle" title="Arrastrar para reordenar">
                    <i class="fas fa-grip-vertical"></i>
                </span>
                <input type="text" 
                       class="form-control option-input" 
                       value="${this.escapeHtml(option)}" 
                       placeholder="Escriba la opción..."
                       onchange="questionBuilder.onOptionChange(this)">
                <button class="btn btn-outline-danger" 
                        type="button" 
                        onclick="questionBuilder.removeOption(this)"
                        title="Eliminar opción">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        `;
    }
    
    /**
     * Configurar sortable para opciones
     */
    setupOptionsSortable() {
        const optionsList = document.getElementById('optionsList');
        if (!optionsList) return;
        
        new Sortable(optionsList, {
            handle: '.option-handle',
            animation: 200,
            onEnd: () => {
                this.updateOptionIndices();
                this.markDirty();
            }
        });
    }
    
    /**
     * Agregar opción
     */
    addOption() {
        const optionsList = document.getElementById('optionsList');
        if (!optionsList) return;
        
        const currentOptions = optionsList.children.length;
        const questionType = this.elements.questionType?.value;
        const typeConfig = this.hercoConfig.questionTypes[questionType];
        
        if (typeConfig?.validation?.max && currentOptions >= typeConfig.validation.max) {
            this.showNotification(`Máximo ${typeConfig.validation.max} opciones permitidas`, 'warning');
            return;
        }
        
        const optionHTML = this.generateOptionHTML('Nueva opción', currentOptions);
        optionsList.insertAdjacentHTML('beforeend', optionHTML);
        
        // Enfocar nueva opción
        const newOption = optionsList.lastElementChild?.querySelector('.option-input');
        if (newOption) {
            newOption.focus();
            newOption.select();
        }
        
        this.markDirty();
    }
    
    /**
     * Remover opción
     */
    removeOption(button) {
        const optionItem = button.closest('.option-item');
        const optionsList = optionItem.parentElement;
        
        if (optionsList.children.length <= 2) {
            this.showNotification('Mínimo 2 opciones requeridas', 'warning');
            return;
        }
        
        optionItem.remove();
        this.updateOptionIndices();
        this.markDirty();
    }
    
    /**
     * Manejar cambio en opción
     */
    onOptionChange(input) {
        this.markDirty();
        this.scheduleAutoSave();
    }
    
    /**
     * Actualizar índices de opciones
     */
    updateOptionIndices() {
        const optionItems = document.querySelectorAll('.option-item');
        optionItems.forEach((item, index) => {
            item.dataset.optionIndex = index;
        });
    }
    
    /**
     * Restablecer opciones
     */
    resetOptions() {
        if (!confirm('¿Restablecer opciones a valores por defecto?')) return;
        
        this.updateQuestionOptions();
        this.markDirty();
    }
    
    /**
     * Duplicar pregunta
     */
    duplicateQuestion(questionId) {
        const questionData = this.state.questions.get(questionId);
        if (!questionData) return;
        
        const duplicatedData = {
            ...questionData,
            id: this.generateQuestionId(),
            text: `${questionData.text} (Copia)`,
            order: this.state.questions.size + 1
        };
        
        // Crear elemento DOM
        const questionElement = this.createQuestionElement(duplicatedData);
        
        // Insertar después de la pregunta original
        const originalElement = document.querySelector(`[data-question-id="${questionId}"]`);
        if (originalElement) {
            originalElement.insertAdjacentElement('afterend', questionElement);
        } else {
            this.elements.questionsList.appendChild(questionElement);
        }
        
        // Actualizar estado
        this.state.questions.set(duplicatedData.id, duplicatedData);
        this.updateQuestionCounter();
        this.updateQuestionOrder();
        this.markDirty();
        this.scheduleAutoSave();
        
        this.showNotification('Pregunta duplicada exitosamente', 'success');
        
        // Editar la pregunta duplicada
        setTimeout(() => this.editQuestion(duplicatedData.id), 200);
    }
    
    /**
     * Eliminar pregunta
     */
    deleteQuestion(questionId) {
        if (!confirm('¿Está seguro de eliminar esta pregunta? Esta acción no se puede deshacer.')) {
            return;
        }
        
        const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
        if (questionElement) {
            questionElement.remove();
        }
        
        this.state.questions.delete(questionId);
        
        // Si era la pregunta que se estaba editando, cerrar editor
        if (this.state.currentEditingQuestion === questionId) {
            this.hideEditor();
        }
        
        this.updateQuestionCounter();
        this.updateQuestionOrder();
        this.markDirty();
        this.scheduleAutoSave();
        
        // Mostrar estado vacío si no hay preguntas
        if (this.state.questions.size === 0) {
            this.showEmptyState();
        }
        
        this.showNotification('Pregunta eliminada', 'success');
    }
    
    /**
     * Guardar cambios de pregunta editada
     */
    saveQuestionEdit() {
        if (!this.state.currentEditingQuestion) return;
        
        const questionData = this.collectQuestionDataFromForm();
        if (!questionData) return;
        
        // Validar datos
        if (!this.validateQuestionData(questionData)) return;
        
        // Actualizar estado
        this.state.questions.set(this.state.currentEditingQuestion, questionData);
        
        // Actualizar DOM
        this.updateQuestionElement(this.state.currentEditingQuestion, questionData);
        
        // Ocultar editor
        this.hideEditor();
        
        this.markDirty();
        this.scheduleAutoSave();
        
        this.showNotification('Pregunta actualizada exitosamente', 'success');
    }
    
    /**
     * Recopilar datos de pregunta desde formulario
     */
    collectQuestionDataFromForm() {
        if (!this.elements.questionText?.value.trim()) {
            this.showNotification('El texto de la pregunta es obligatorio', 'error');
            return null;
        }
        
        const questionData = {
            id: this.state.currentEditingQuestion,
            text: this.elements.questionText.value.trim(),
            description: this.elements.questionDescription?.value.trim() || '',
            type: this.elements.questionType?.value || 'text',
            category: this.elements.questionCategory?.value || '',
            required: this.elements.questionRequired?.checked || false,
            options: this.collectOptionsFromForm(),
            order: this.state.questions.get(this.state.currentEditingQuestion)?.order || 1
        };
        
        return questionData;
    }
    
    /**
     * Recopilar opciones desde formulario
     */
    collectOptionsFromForm() {
        const optionInputs = document.querySelectorAll('.option-input');
        const options = [];
        
        optionInputs.forEach(input => {
            const value = input.value.trim();
            if (value) {
                options.push(value);
            }
        });
        
        return options;
    }
    
    /**
     * Validar datos de pregunta
     */
    validateQuestionData(questionData) {
        const typeConfig = this.hercoConfig.questionTypes[questionData.type];
        if (!typeConfig) {
            this.showNotification('Tipo de pregunta no válido', 'error');
            return false;
        }
        
        // Validar opciones si las requiere
        if (typeConfig.hasOptions) {
            const minOptions = typeConfig.validation?.min || 2;
            const maxOptions = typeConfig.validation?.max || 10;
            
            if (questionData.options.length < minOptions) {
                this.showNotification(`Mínimo ${minOptions} opciones requeridas`, 'error');
                return false;
            }
            
            if (questionData.options.length > maxOptions) {
                this.showNotification(`Máximo ${maxOptions} opciones permitidas`, 'error');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Actualizar elemento de pregunta en DOM
     */
    updateQuestionElement(questionId, questionData) {
        const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
        if (!questionElement) return;
        
        questionElement.innerHTML = this.generateQuestionHTML(questionData);
    }
    
    /**
     * Mostrar/ocultar editor
     */
    showEditor() {
        if (this.elements.questionEditor) {
            this.elements.questionEditor.style.display = 'block';
        }
        if (this.elements.previewPanel) {
            this.elements.previewPanel.style.display = 'none';
        }
    }
    
    hideEditor() {
        if (this.elements.questionEditor) {
            this.elements.questionEditor.style.display = 'none';
        }
        if (this.elements.previewPanel) {
            this.elements.previewPanel.style.display = 'block';
        }
        
        this.clearEditingState();
        this.state.currentEditingQuestion = null;
    }
    
    /**
     * Limpiar estado de edición
     */
    clearEditingState() {
        document.querySelectorAll('.question-item.editing').forEach(el => {
            el.classList.remove('editing');
        });
    }
    
    /**
     * Manejar eventos de entrada en formulario
     */
    onFormInput(evt) {
        this.markDirty();
        
        // Preview en tiempo real
        if (this.options.enablePreview) {
            clearTimeout(this.previewTimer);
            this.previewTimer = setTimeout(() => {
                this.updateLivePreview();
            }, 300);
        }
    }
    
    /**
     * Manejar eventos de cambio en formulario
     */
    onFormChange(evt) {
        this.markDirty();
        this.scheduleAutoSave();
    }
    
    /**
     * Actualizar vista previa en tiempo real
     */
    updateLivePreview() {
        if (!this.state.currentEditingQuestion) return;
        
        const questionData = this.collectQuestionDataFromForm();
        if (questionData) {
            // Actualizar preview en panel derecho
            // Implementar según necesidad
        }
    }
    
    /**
     * Programar auto-guardado
     */
    scheduleAutoSave() {
        if (!this.options.enableAutoSave || this.state.isAutoSaving) return;
        
        clearTimeout(this.autoSaveTimer);
        
        this.showAutoSaveIndicator('saving');
        
        this.autoSaveTimer = setTimeout(() => {
            this.autoSave();
        }, this.options.autoSaveDelay);
    }
    
    /**
     * Ejecutar auto-guardado
     */
    async autoSave() {
        if (this.state.isAutoSaving) return;
        
        this.state.isAutoSaving = true;
        
        try {
            const success = await this.saveQuestions(true);
            
            if (success) {
                this.state.isDirty = false;
                this.state.lastSaveTime = new Date();
                this.showAutoSaveIndicator('saved');
            } else {
                this.showAutoSaveIndicator('error');
            }
        } catch (error) {
            console.error('Error en auto-guardado:', error);
            this.showAutoSaveIndicator('error');
        } finally {
            this.state.isAutoSaving = false;
        }
    }
    
    /**
     * Guardar preguntas
     */
    async saveQuestions(isAutoSave = false) {
        const questionsData = this.collectAllQuestionsData();
        
        try {
            const response = await fetch(`/admin/surveys/builder/${this.options.surveyId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ 
                    questions: questionsData,
                    auto_save: isAutoSave 
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (!isAutoSave) {
                    this.showNotification('Preguntas guardadas exitosamente', 'success');
                }
                return true;
            } else {
                if (!isAutoSave) {
                    this.showNotification(data.message || 'Error al guardar', 'error');
                }
                return false;
            }
        } catch (error) {
            console.error('Error guardando:', error);
            if (!isAutoSave) {
                this.showNotification('Error de conexión al guardar', 'error');
            }
            return false;
        }
    }
    
    /**
     * Recopilar datos de todas las preguntas
     */
    collectAllQuestionsData() {
        const questionsArray = Array.from(this.state.questions.values());
        
        // Actualizar orden basado en DOM
        const questionElements = document.querySelectorAll('.question-item');
        questionElements.forEach((element, index) => {
            const questionId = element.dataset.questionId;
            const questionData = this.state.questions.get(questionId);
            if (questionData) {
                questionData.order = index + 1;
            }
        });
        
        return questionsArray.sort((a, b) => a.order - b.order);
    }
    
    /**
     * Actualizar orden de preguntas
     */
    updateQuestionOrder() {
        const questionElements = document.querySelectorAll('.question-item');
        questionElements.forEach((element, index) => {
            const questionId = element.dataset.questionId;
            const questionData = this.state.questions.get(questionId);
            
            if (questionData) {
                questionData.order = index + 1;
            }
            
            // Actualizar número en badge
            const badge = element.querySelector('.question-number');
            if (badge) {
                badge.textContent = index + 1;
            }
            
            element.dataset.order = index + 1;
        });
    }
    
    /**
     * Actualizar contador de preguntas
     */
    updateQuestionCounter() {
        const count = this.state.questions.size;
        if (this.elements.questionCounter) {
            this.elements.questionCounter.textContent = count;
        }
    }
    
    /**
     * Mostrar/ocultar estado vacío
     */
    showEmptyState() {
        if (this.elements.emptyState) {
            this.elements.emptyState.style.display = 'block';
        }
    }
    
    hideEmptyState() {
        if (this.elements.emptyState) {
            this.elements.emptyState.style.display = 'none';
        }
    }
    
    /**
     * Mostrar indicador de auto-guardado
     */
    showAutoSaveIndicator(state) {
        if (!this.elements.autoSaveIndicator) return;
        
        const indicator = this.elements.autoSaveIndicator;
        indicator.className = `auto-save-indicator ${state}`;
        
        switch (state) {
            case 'saving':
                indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                indicator.style.display = 'block';
                break;
            case 'saved':
                indicator.innerHTML = '<i class="fas fa-check"></i> Guardado';
                indicator.style.display = 'block';
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 3000);
                break;
            case 'error':
                indicator.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al guardar';
                indicator.style.display = 'block';
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 5000);
                break;
        }
    }
    
    /**
     * Marcar como modificado
     */
    markDirty() {
        this.state.isDirty = true;
    }
    
    /**
     * Generar ID único para pregunta
     */
    generateQuestionId() {
        return `q_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }
    
    /**
     * Escapar HTML
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    /**
     * Mostrar notificación
     */
    showNotification(message, type = 'info', duration = 5000) {
        if (window.notifications) {
            window.notifications.show(message, type, duration);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }
    
    /**
     * Manejar eventos de teclado
     */
    onKeyDown(evt) {
        // Ctrl+S para guardar
        if (evt.ctrlKey && evt.key === 's') {
            evt.preventDefault();
            this.saveQuestions();
            return;
        }
        
        // Escape para cancelar edición
        if (evt.key === 'Escape' && this.state.currentEditingQuestion) {
            this.hideEditor();
            return;
        }
    }
    
    /**
     * Manejar antes de salir de la página
     */
    onBeforeUnload(evt) {
        if (this.state.isDirty) {
            evt.preventDefault();
            evt.returnValue = '¿Está seguro de salir? Hay cambios sin guardar.';
            return evt.returnValue;
        }
    }
    
    /**
     * Cargar preguntas existentes
     */
    loadExistingQuestions() {
        // Esta función cargaría las preguntas existentes del servidor
        // Por ahora, leer desde DOM
        const questionElements = document.querySelectorAll('.question-item');
        
        questionElements.forEach((element, index) => {
            const questionId = element.dataset.questionId;
            if (questionId && !this.state.questions.has(questionId)) {
                // Crear datos básicos de la pregunta desde DOM
                const questionData = {
                    id: questionId,
                    order: index + 1,
                    text: element.querySelector('.question-title')?.textContent || 'Pregunta sin título',
                    type: element.dataset.type || 'text',
                    category: '',
                    required: false,
                    options: []
                };
                
                this.state.questions.set(questionId, questionData);
            }
        });
        
        this.state.questionCounter = this.state.questions.size;
        this.updateQuestionCounter();
    }
    
    /**
     * Configurar auto-guardado
     */
    setupAutoSave() {
        if (!this.options.enableAutoSave) return;
        
        // Guardar cada minuto si hay cambios
        setInterval(() => {
            if (this.state.isDirty && !this.state.isAutoSaving) {
                this.autoSave();
            }
        }, 60000);
    }
    
    /**
     * Agregar plantilla HERCO
     */
    addHercoTemplate(templateType) {
        const template = this.hercoConfig.templates[templateType];
        if (!template) {
            this.showNotification('Plantilla no encontrada', 'error');
            return;
        }
        
        this.showNotification('Cargando plantilla HERCO...', 'info');
        
        // Simular carga (en producción sería una llamada AJAX)
        setTimeout(() => {
            // Limpiar estado vacío
            this.hideEmptyState();
            
            // Agregar preguntas de la plantilla
            template.questions.forEach((questionTemplate, index) => {
                const questionData = {
                    ...questionTemplate,
                    id: this.generateQuestionId(),
                    order: this.state.questions.size + index + 1
                };
                
                // Crear elemento DOM
                const questionElement = this.createQuestionElement(questionData);
                this.elements.questionsList.appendChild(questionElement);
                
                // Actualizar estado
                this.state.questions.set(questionData.id, questionData);
            });
            
            this.updateQuestionCounter();
            this.markDirty();
            this.scheduleAutoSave();
            
            this.showNotification(`Plantilla ${template.name} agregada exitosamente`, 'success');
        }, 1000);
    }
    
    /**
     * Manejar click en plantilla
     */
    onTemplateClick(evt) {
        const templateItem = evt.currentTarget;
        const templateType = templateItem.dataset.template;
        
        if (templateType) {
            this.addHercoTemplate(templateType);
        }
    }
    
    /**
     * Manejar click en categoría
     */
    onCategoryClick(evt) {
        const categoryBadge = evt.currentTarget;
        const categoryKey = categoryBadge.dataset.category;
        
        if (categoryKey && this.elements.questionCategory) {
            this.elements.questionCategory.value = categoryKey;
            this.markDirty();
            
            this.showNotification(`Categoría ${this.hercoConfig.categories[categoryKey].name} seleccionada`, 'info');
        }
    }
}

// Inicializar constructor cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si estamos en la página del constructor
    if (document.getElementById('questionsList') && document.getElementById('questionTypes')) {
        // Obtener configuración desde data attributes o variables globales
        const surveyId = window.surveyId || document.querySelector('[data-survey-id]')?.dataset.surveyId;
        
        if (surveyId) {
            window.questionBuilder = new QuestionBuilder({
                surveyId: surveyId,
                enableAutoSave: true,
                enableDragDrop: true,
                enablePreview: true,
                autoSaveDelay: 2000,
                maxQuestions: 100
            });
            
            console.log('✅ QuestionBuilder inicializado globalmente');
        } else {
            console.warn('⚠️ Survey ID no encontrado, QuestionBuilder no inicializado');
        }
    }
});