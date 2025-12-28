/**
 * Motor de Encuestas Público - Sistema HERCO v2.0
 * 
 * JavaScript para la interfaz pública de encuestas con:
 * - Navegación fluida entre preguntas
 * - Validación en tiempo real
 * - Guardado automático de progreso
 * - Indicador de progreso visual
 * - Experiencia responsive y accesible
 * 
 * @package HERCO\Assets\JS
 * @version 2.0.0
 * @author Sistema HERCO
 * @requires Bootstrap 5
 */

'use strict';

/**
 * Clase principal del motor de encuestas público
 */
class SurveyEngine {
    constructor(options = {}) {
        this.options = {
            surveyId: null,
            participantId: null,
            autoSaveDelay: 3000,
            enableAutoSave: true,
            enableValidation: true,
            showProgress: true,
            allowBackNavigation: true,
            enableAccessibility: true,
            animationDuration: 300,
            ...options
        };

        // Estado de la encuesta
        this.state = {
            currentQuestionIndex: 0,
            totalQuestions: 0,
            responses: new Map(),
            startTime: new Date(),
            lastActivityTime: new Date(),
            isComplete: false,
            isDirty: false,
            isSubmitting: false,
            validationErrors: new Map()
        };

        // Referencias DOM
        this.elements = {};

        // Timers
        this.autoSaveTimer = null;
        this.inactivityTimer = null;

        // Configuración de validación
        this.validators = new Map();

        // Inicializar
        this.init();
    }

    /**
     * Inicializar el motor
     */
    init() {
        this.setupDOM();
        this.setupValidators();
        this.bindEvents();
        this.setupKeyboardNavigation();
        this.setupAccessibility();
        this.loadSavedProgress();
        this.updateProgress();
        this.startActivityTracking();

        console.log('🎯 SurveyEngine inicializado correctamente');
    }

    /**
     * Configurar referencias DOM
     */
    setupDOM() {
        this.elements = {
            // Contenedores principales
            surveyContainer: document.getElementById('surveyContainer'),
            questionsContainer: document.getElementById('questionsContainer'),
            currentQuestion: document.querySelector('.question-card.active'),
            
            // Elementos de navegación
            prevButton: document.getElementById('prevButton'),
            nextButton: document.getElementById('nextButton'),
            submitButton: document.getElementById('submitButton'),
            
            // Indicadores de progreso
            progressBar: document.getElementById('progressBar'),
            progressText: document.getElementById('progressText'),
            progressPercentage: document.getElementById('progressPercentage'),
            
            // Elementos de estado
            saveIndicator: document.getElementById('saveIndicator'),
            validationSummary: document.getElementById('validationSummary'),
            timeRemaining: document.getElementById('timeRemaining'),
            
            // Formulario
            surveyForm: document.getElementById('surveyForm'),
            
            // Modales y overlays
            exitModal: document.getElementById('exitModal'),
            warningModal: document.getElementById('warningModal')
        };

        // Obtener todas las preguntas
        this.questions = document.querySelectorAll('.question-card');
        this.state.totalQuestions = this.questions.length;

        // Verificar elementos críticos
        if (!this.elements.surveyContainer || this.questions.length === 0) {
            throw new Error('Elementos DOM críticos no encontrados');
        }
    }

    /**
     * Configurar validadores por tipo de pregunta
     */
    setupValidators() {
        this.validators.set('required', (question, value) => {
            if (!value || (Array.isArray(value) && value.length === 0)) {
                return 'Esta pregunta es obligatoria';
            }
            return null;
        });

        this.validators.set('email', (question, value) => {
            if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return 'Por favor ingrese un email válido';
            }
            return null;
        });

        this.validators.set('number', (question, value) => {
            if (value && isNaN(value)) {
                return 'Por favor ingrese un número válido';
            }
            const min = question.dataset.min;
            const max = question.dataset.max;
            if (min && value < parseFloat(min)) {
                return `El valor mínimo es ${min}`;
            }
            if (max && value > parseFloat(max)) {
                return `El valor máximo es ${max}`;
            }
            return null;
        });

        this.validators.set('text', (question, value) => {
            const minLength = question.dataset.minLength;
            const maxLength = question.dataset.maxLength;
            if (minLength && value && value.length < parseInt(minLength)) {
                return `Mínimo ${minLength} caracteres requeridos`;
            }
            if (maxLength && value && value.length > parseInt(maxLength)) {
                return `Máximo ${maxLength} caracteres permitidos`;
            }
            return null;
        });

        this.validators.set('date', (question, value) => {
            if (value && !this.isValidDate(value)) {
                return 'Por favor ingrese una fecha válida';
            }
            return null;
        });

        this.validators.set('phone', (question, value) => {
            if (value && !/^\+?[\d\s\-\(\)]+$/.test(value)) {
                return 'Por favor ingrese un número de teléfono válido';
            }
            return null;
        });

        this.validators.set('url', (question, value) => {
            if (value && !/^https?:\/\/.+/.test(value)) {
                return 'Por favor ingrese una URL válida (ej: https://ejemplo.com)';
            }
            return null;
        });
    }

    /**
     * Vincular eventos
     */
    bindEvents() {
        // Navegación
        if (this.elements.nextButton) {
            this.elements.nextButton.addEventListener('click', () => this.nextQuestion());
        }
        
        if (this.elements.prevButton) {
            this.elements.prevButton.addEventListener('click', () => this.prevQuestion());
        }

        if (this.elements.submitButton) {
            this.elements.submitButton.addEventListener('click', () => this.submitSurvey());
        }

        // Eventos de respuesta
        if (this.elements.surveyForm) {
            this.elements.surveyForm.addEventListener('change', (e) => this.onResponseChange(e));
            this.elements.surveyForm.addEventListener('input', (e) => this.onResponseInput(e));
        }

        // Eventos de teclado
        document.addEventListener('keydown', (e) => this.onKeyDown(e));

        // Eventos de ventana
        window.addEventListener('beforeunload', (e) => this.onBeforeUnload(e));
        window.addEventListener('visibilitychange', () => this.onVisibilityChange());

        // Eventos de botones de salir
        document.querySelectorAll('[data-action="exit"]').forEach(btn => {
            btn.addEventListener('click', () => this.showExitConfirmation());
        });

        // Eventos de modales
        if (this.elements.exitModal) {
            this.elements.exitModal.addEventListener('hidden.bs.modal', () => this.onModalHidden());
        }
    }

    /**
     * Configurar navegación por teclado
     */
    setupKeyboardNavigation() {
        if (!this.options.enableAccessibility) return;

        // Tab order y focus management
        this.questions.forEach((question, index) => {
            const inputs = question.querySelectorAll('input, textarea, select, button');
            inputs.forEach(input => {
                input.addEventListener('focus', () => this.onInputFocus(input));
                input.addEventListener('blur', () => this.onInputBlur(input));
            });
        });
    }

    /**
     * Configurar accesibilidad
     */
    setupAccessibility() {
        if (!this.options.enableAccessibility) return;

        // ARIA labels y roles
        this.questions.forEach((question, index) => {
            question.setAttribute('role', 'group');
            question.setAttribute('aria-labelledby', `question-${index}-title`);
            
            const title = question.querySelector('.question-title');
            if (title) {
                title.id = `question-${index}-title`;
            }
        });

        // Live regions para anuncios
        const liveRegion = document.createElement('div');
        liveRegion.id = 'live-region';
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.style.position = 'absolute';
        liveRegion.style.left = '-10000px';
        document.body.appendChild(liveRegion);
        this.elements.liveRegion = liveRegion;
    }

    /**
     * Navegar a la siguiente pregunta
     */
    nextQuestion() {
        if (this.state.currentQuestionIndex >= this.state.totalQuestions - 1) {
            this.showSubmitConfirmation();
            return;
        }

        // Validar pregunta actual
        if (this.options.enableValidation && !this.validateCurrentQuestion()) {
            return;
        }

        // Guardar respuesta actual
        this.saveCurrentResponse();

        // Navegar
        this.navigateToQuestion(this.state.currentQuestionIndex + 1);
    }

    /**
     * Navegar a la pregunta anterior
     */
    prevQuestion() {
        if (!this.options.allowBackNavigation || this.state.currentQuestionIndex <= 0) {
            return;
        }

        // Guardar respuesta actual
        this.saveCurrentResponse();

        // Navegar
        this.navigateToQuestion(this.state.currentQuestionIndex - 1);
    }

    /**
     * Navegar a pregunta específica
     */
    navigateToQuestion(index) {
        if (index < 0 || index >= this.state.totalQuestions) {
            return;
        }

        const currentQuestion = this.questions[this.state.currentQuestionIndex];
        const nextQuestion = this.questions[index];

        // Animación de salida
        this.animateQuestionOut(currentQuestion, () => {
            // Ocultar pregunta actual
            currentQuestion.classList.remove('active');
            currentQuestion.style.display = 'none';

            // Mostrar nueva pregunta
            nextQuestion.style.display = 'block';
            
            // Actualizar estado
            this.state.currentQuestionIndex = index;
            this.elements.currentQuestion = nextQuestion;

            // Animación de entrada
            this.animateQuestionIn(nextQuestion);

            // Actualizar UI
            this.updateProgress();
            this.updateNavigationButtons();
            this.focusFirstInput(nextQuestion);
            this.announceQuestionChange();

            // Guardar progreso
            this.scheduleAutoSave();
        });
    }

    /**
     * Animación de salida de pregunta
     */
    animateQuestionOut(question, callback) {
        question.style.transition = `opacity ${this.options.animationDuration}ms ease-out, transform ${this.options.animationDuration}ms ease-out`;
        question.style.opacity = '0';
        question.style.transform = 'translateX(-50px)';

        setTimeout(callback, this.options.animationDuration);
    }

    /**
     * Animación de entrada de pregunta
     */
    animateQuestionIn(question) {
        question.style.opacity = '0';
        question.style.transform = 'translateX(50px)';
        question.classList.add('active');

        // Forzar reflow
        question.offsetHeight;

        question.style.transition = `opacity ${this.options.animationDuration}ms ease-in, transform ${this.options.animationDuration}ms ease-in`;
        question.style.opacity = '1';
        question.style.transform = 'translateX(0)';
    }

    /**
     * Validar pregunta actual
     */
    validateCurrentQuestion() {
        const question = this.questions[this.state.currentQuestionIndex];
        const questionId = question.dataset.questionId;
        const isRequired = question.dataset.required === 'true';
        const questionType = question.dataset.type;

        // Limpiar errores previos
        this.clearQuestionErrors(question);

        // Obtener valor de respuesta
        const response = this.getQuestionResponse(question);
        let isValid = true;
        const errors = [];

        // Validación de campo obligatorio
        if (isRequired) {
            const requiredError = this.validators.get('required')(question, response);
            if (requiredError) {
                errors.push(requiredError);
                isValid = false;
            }
        }

        // Validación específica por tipo
        if (response && this.validators.has(questionType)) {
            const typeError = this.validators.get(questionType)(question, response);
            if (typeError) {
                errors.push(typeError);
                isValid = false;
            }
        }

        // Mostrar errores si los hay
        if (!isValid) {
            this.showQuestionErrors(question, errors);
            this.state.validationErrors.set(questionId, errors);
        } else {
            this.state.validationErrors.delete(questionId);
        }

        return isValid;
    }

    /**
     * Obtener respuesta de pregunta
     */
    getQuestionResponse(question) {
        const questionType = question.dataset.type;
        const questionId = question.dataset.questionId;

        switch (questionType) {
            case 'multiple_choice':
            case 'likert_5':
            case 'likert_7':
            case 'likert_3':
            case 'yes_no':
            case 'rating':
                const radioInput = question.querySelector('input[type="radio"]:checked');
                return radioInput ? radioInput.value : null;

            case 'checkbox':
                const checkboxes = question.querySelectorAll('input[type="checkbox"]:checked');
                return Array.from(checkboxes).map(cb => cb.value);

            case 'text':
            case 'textarea':
            case 'email':
            case 'number':
            case 'phone':
            case 'url':
                const textInput = question.querySelector('input, textarea');
                return textInput ? textInput.value.trim() : null;

            case 'date':
                const dateInput = question.querySelector('input[type="date"]');
                return dateInput ? dateInput.value : null;

            case 'slider':
                const sliderInput = question.querySelector('input[type="range"]');
                return sliderInput ? parseFloat(sliderInput.value) : null;

            case 'file':
                const fileInput = question.querySelector('input[type="file"]');
                return fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;

            case 'nps':
                const npsInput = question.querySelector('input[name^="nps"]:checked');
                return npsInput ? parseInt(npsInput.value) : null;

            default:
                console.warn(`Tipo de pregunta no soportado: ${questionType}`);
                return null;
        }
    }

    /**
     * Guardar respuesta actual
     */
    saveCurrentResponse() {
        const question = this.questions[this.state.currentQuestionIndex];
        const questionId = question.dataset.questionId;
        const response = this.getQuestionResponse(question);

        if (response !== null && response !== undefined) {
            this.state.responses.set(questionId, {
                questionId: questionId,
                value: response,
                timestamp: new Date(),
                questionIndex: this.state.currentQuestionIndex
            });

            this.state.isDirty = true;
            this.state.lastActivityTime = new Date();
        }
    }

    /**
     * Mostrar errores de pregunta
     */
    showQuestionErrors(question, errors) {
        // Crear contenedor de errores si no existe
        let errorContainer = question.querySelector('.validation-errors');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'validation-errors mt-2';
            errorContainer.setAttribute('role', 'alert');
            
            const questionContent = question.querySelector('.question-content');
            if (questionContent) {
                questionContent.appendChild(errorContainer);
            }
        }

        // Mostrar errores
        errorContainer.innerHTML = errors.map(error => 
            `<div class="alert alert-danger alert-sm mb-1">
                <i class="fas fa-exclamation-triangle me-1"></i>
                ${error}
             </div>`
        ).join('');

        // Resaltar inputs con error
        question.classList.add('has-error');
        const inputs = question.querySelectorAll('input, textarea, select');
        inputs.forEach(input => input.classList.add('is-invalid'));

        // Anunciar error para lectores de pantalla
        if (this.elements.liveRegion) {
            this.elements.liveRegion.textContent = `Error en pregunta: ${errors[0]}`;
        }
    }

    /**
     * Limpiar errores de pregunta
     */
    clearQuestionErrors(question) {
        const errorContainer = question.querySelector('.validation-errors');
        if (errorContainer) {
            errorContainer.remove();
        }

        question.classList.remove('has-error');
        const inputs = question.querySelectorAll('input, textarea, select');
        inputs.forEach(input => input.classList.remove('is-invalid'));
    }

    /**
     * Actualizar indicador de progreso
     */
    updateProgress() {
        if (!this.options.showProgress) return;

        const progress = ((this.state.currentQuestionIndex + 1) / this.state.totalQuestions) * 100;
        const currentNum = this.state.currentQuestionIndex + 1;

        // Actualizar barra de progreso
        if (this.elements.progressBar) {
            this.elements.progressBar.style.width = `${progress}%`;
            this.elements.progressBar.setAttribute('aria-valuenow', progress);
        }

        // Actualizar texto de progreso
        if (this.elements.progressText) {
            this.elements.progressText.textContent = `Pregunta ${currentNum} de ${this.state.totalQuestions}`;
        }

        // Actualizar porcentaje
        if (this.elements.progressPercentage) {
            this.elements.progressPercentage.textContent = `${Math.round(progress)}%`;
        }
    }

    /**
     * Actualizar botones de navegación
     */
    updateNavigationButtons() {
        // Botón anterior
        if (this.elements.prevButton) {
            this.elements.prevButton.disabled = !this.options.allowBackNavigation || this.state.currentQuestionIndex === 0;
        }

        // Botón siguiente
        if (this.elements.nextButton) {
            const isLastQuestion = this.state.currentQuestionIndex >= this.state.totalQuestions - 1;
            this.elements.nextButton.style.display = isLastQuestion ? 'none' : 'inline-block';
        }

        // Botón enviar
        if (this.elements.submitButton) {
            const isLastQuestion = this.state.currentQuestionIndex >= this.state.totalQuestions - 1;
            this.elements.submitButton.style.display = isLastQuestion ? 'inline-block' : 'none';
        }
    }

    /**
     * Enfocar primer input de pregunta
     */
    focusFirstInput(question) {
        setTimeout(() => {
            const firstInput = question.querySelector('input, textarea, select, button');
            if (firstInput && this.options.enableAccessibility) {
                firstInput.focus();
            }
        }, this.options.animationDuration + 50);
    }

    /**
     * Anunciar cambio de pregunta para accesibilidad
     */
    announceQuestionChange() {
        if (!this.elements.liveRegion) return;

        const currentNum = this.state.currentQuestionIndex + 1;
        const question = this.questions[this.state.currentQuestionIndex];
        const questionTitle = question.querySelector('.question-title')?.textContent || '';

        this.elements.liveRegion.textContent = `Pregunta ${currentNum} de ${this.state.totalQuestions}: ${questionTitle}`;
    }

    /**
     * Programar auto-guardado
     */
    scheduleAutoSave() {
        if (!this.options.enableAutoSave || this.state.isSubmitting) return;

        clearTimeout(this.autoSaveTimer);

        this.showSaveIndicator('saving');

        this.autoSaveTimer = setTimeout(() => {
            this.autoSave();
        }, this.options.autoSaveDelay);
    }

    /**
     * Ejecutar auto-guardado
     */
    async autoSave() {
        if (!this.state.isDirty || this.state.isSubmitting) return;

        try {
            const success = await this.saveProgress();
            
            if (success) {
                this.state.isDirty = false;
                this.showSaveIndicator('saved');
            } else {
                this.showSaveIndicator('error');
            }
        } catch (error) {
            console.error('Error en auto-guardado:', error);
            this.showSaveIndicator('error');
        }
    }

    /**
     * Guardar progreso
     */
    async saveProgress() {
        const progressData = {
            surveyId: this.options.surveyId,
            participantId: this.options.participantId,
            currentQuestionIndex: this.state.currentQuestionIndex,
            responses: Object.fromEntries(this.state.responses),
            startTime: this.state.startTime,
            lastActivityTime: this.state.lastActivityTime,
            isComplete: this.state.isComplete
        };

        try {
            const response = await fetch('/survey/save-progress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(progressData)
            });

            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Error guardando progreso:', error);
            return false;
        }
    }

    /**
     * Cargar progreso guardado
     */
    async loadSavedProgress() {
        try {
            const response = await fetch(`/survey/load-progress/${this.options.surveyId}/${this.options.participantId}`);
            const data = await response.json();

            if (data.success && data.progress) {
                const progress = data.progress;
                
                // Restaurar respuestas
                if (progress.responses) {
                    Object.entries(progress.responses).forEach(([questionId, responseData]) => {
                        this.state.responses.set(questionId, responseData);
                        this.restoreQuestionResponse(questionId, responseData);
                    });
                }

                // Restaurar posición si no es la primera carga
                if (progress.currentQuestionIndex > 0) {
                    this.navigateToQuestion(progress.currentQuestionIndex);
                }

                this.showNotification('Progreso restaurado', 'info', 3000);
            }
        } catch (error) {
            console.error('Error cargando progreso:', error);
        }
    }

    /**
     * Restaurar respuesta de pregunta
     */
    restoreQuestionResponse(questionId, responseData) {
        const question = document.querySelector(`[data-question-id="${questionId}"]`);
        if (!question) return;

        const questionType = question.dataset.type;
        const value = responseData.value;

        switch (questionType) {
            case 'multiple_choice':
            case 'likert_5':
            case 'likert_7':
            case 'likert_3':
            case 'yes_no':
            case 'rating':
                const radio = question.querySelector(`input[type="radio"][value="${value}"]`);
                if (radio) radio.checked = true;
                break;

            case 'checkbox':
                if (Array.isArray(value)) {
                    value.forEach(val => {
                        const checkbox = question.querySelector(`input[type="checkbox"][value="${val}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
                break;

            case 'text':
            case 'textarea':
            case 'email':
            case 'number':
            case 'phone':
            case 'url':
            case 'date':
                const input = question.querySelector('input, textarea');
                if (input) input.value = value;
                break;

            case 'slider':
                const slider = question.querySelector('input[type="range"]');
                if (slider) {
                    slider.value = value;
                    // Actualizar display del valor si existe
                    const display = question.querySelector('.slider-value');
                    if (display) display.textContent = value;
                }
                break;

            case 'nps':
                const npsRadio = question.querySelector(`input[name^="nps"][value="${value}"]`);
                if (npsRadio) npsRadio.checked = true;
                break;
        }
    }

    /**
     * Mostrar confirmación de envío
     */
    showSubmitConfirmation() {
        // Validar todas las preguntas obligatorias
        const missingRequired = this.validateAllRequiredQuestions();
        
        if (missingRequired.length > 0) {
            this.showIncompleteWarning(missingRequired);
            return;
        }

        // Mostrar modal de confirmación
        const confirmModal = new bootstrap.Modal(document.getElementById('submitConfirmModal'));
        confirmModal.show();
    }

    /**
     * Validar todas las preguntas obligatorias
     */
    validateAllRequiredQuestions() {
        const missingRequired = [];

        this.questions.forEach((question, index) => {
            const isRequired = question.dataset.required === 'true';
            if (isRequired) {
                const questionId = question.dataset.questionId;
                const response = this.state.responses.get(questionId);
                
                if (!response || !response.value || 
                    (Array.isArray(response.value) && response.value.length === 0)) {
                    missingRequired.push({
                        index: index + 1,
                        title: question.querySelector('.question-title')?.textContent || `Pregunta ${index + 1}`
                    });
                }
            }
        });

        return missingRequired;
    }

    /**
     * Mostrar advertencia de preguntas incompletas
     */
    showIncompleteWarning(missingQuestions) {
        const warningModal = document.getElementById('incompleteWarningModal');
        const questionsList = warningModal.querySelector('.missing-questions-list');
        
        questionsList.innerHTML = missingQuestions.map(q => 
            `<li class="list-group-item d-flex justify-content-between align-items-center">
                ${q.title}
                <button class="btn btn-sm btn-outline-primary" onclick="surveyEngine.navigateToQuestion(${q.index - 1})">
                    Ir a pregunta
                </button>
             </li>`
        ).join('');

        const modal = new bootstrap.Modal(warningModal);
        modal.show();
    }

    /**
     * Enviar encuesta
     */
    async submitSurvey() {
        if (this.state.isSubmitting) return;

        this.state.isSubmitting = true;
        this.showSubmitProgress();

        try {
            // Guardar respuesta actual
            this.saveCurrentResponse();

            // Preparar datos para envío
            const submissionData = {
                surveyId: this.options.surveyId,
                participantId: this.options.participantId,
                responses: Object.fromEntries(this.state.responses),
                startTime: this.state.startTime,
                endTime: new Date(),
                totalTime: new Date() - this.state.startTime,
                isComplete: true
            };

            const response = await fetch('/survey/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(submissionData)
            });

            const data = await response.json();

            if (data.success) {
                this.state.isComplete = true;
                this.redirectToCompletion();
            } else {
                throw new Error(data.message || 'Error al enviar encuesta');
            }

        } catch (error) {
            console.error('Error enviando encuesta:', error);
            this.showNotification('Error al enviar la encuesta. Por favor intente nuevamente.', 'error');
            this.state.isSubmitting = false;
            this.hideSubmitProgress();
        }
    }

    /**
     * Mostrar progreso de envío
     */
    showSubmitProgress() {
        if (this.elements.submitButton) {
            this.elements.submitButton.disabled = true;
            this.elements.submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
        }

        // Mostrar overlay de carga
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'submitLoadingOverlay';
        loadingOverlay.className = 'loading-overlay';
        loadingOverlay.innerHTML = `
            <div class="loading-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Enviando...</span>
                </div>
                <p class="mt-3">Enviando su encuesta...</p>
                <small class="text-muted">Por favor no cierre esta ventana</small>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }

    /**
     * Ocultar progreso de envío
     */
    hideSubmitProgress() {
        if (this.elements.submitButton) {
            this.elements.submitButton.disabled = false;
            this.elements.submitButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Enviar Encuesta';
        }

        const loadingOverlay = document.getElementById('submitLoadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    }

    /**
     * Redirigir a página de completado
     */
    redirectToCompletion() {
        window.location.href = `/survey/completed/${this.options.surveyId}`;
    }

    /**
     * Mostrar indicador de guardado
     */
    showSaveIndicator(state) {
        if (!this.elements.saveIndicator) return;

        const indicator = this.elements.saveIndicator;
        indicator.className = `save-indicator ${state}`;

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
                }, 2000);
                break;
            case 'error':
                indicator.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error al guardar';
                indicator.style.display = 'block';
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 4000);
                break;
        }
    }

    /**
     * Mostrar notificación
     */
    showNotification(message, type = 'info', duration = 5000) {
        if (window.notifications) {
            window.notifications.show(message, type, duration);
        } else {
            // Fallback simple
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} notification-toast`;
            notification.innerHTML = `<i class="fas fa-info-circle me-2"></i>${message}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
                opacity: 0;
                transition: opacity 0.3s ease-in-out;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => notification.style.opacity = '1', 100);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
    }

    /**
     * Manejar cambios en respuestas
     */
    onResponseChange(evt) {
        this.state.isDirty = true;
        this.state.lastActivityTime = new Date();
        this.scheduleAutoSave();

        // Limpiar errores de validación
        const question = evt.target.closest('.question-card');
        if (question) {
            this.clearQuestionErrors(question);
        }
    }

    /**
     * Manejar entrada en respuestas
     */
    onResponseInput(evt) {
        this.state.lastActivityTime = new Date();
        
        // Para sliders, actualizar valor mostrado
        if (evt.target.type === 'range') {
            const valueDisplay = evt.target.parentElement.querySelector('.slider-value');
            if (valueDisplay) {
                valueDisplay.textContent = evt.target.value;
            }
        }
    }

    /**
     * Manejar eventos de teclado
     */
    onKeyDown(evt) {
        // Prevenir envío accidental con Enter
        if (evt.key === 'Enter' && evt.target.tagName !== 'TEXTAREA' && evt.target.tagName !== 'BUTTON') {
            evt.preventDefault();
            return;
        }

        // Navegación con teclas de flecha (solo si no hay input enfocado)
        if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            if (evt.key === 'ArrowRight' || evt.key === 'PageDown') {
                evt.preventDefault();
                this.nextQuestion();
            } else if (evt.key === 'ArrowLeft' || evt.key === 'PageUp') {
                evt.preventDefault();
                this.prevQuestion();
            }
        }
    }

    /**
     * Manejar antes de salir de la página
     */
    onBeforeUnload(evt) {
        if (this.state.isDirty && !this.state.isComplete) {
            evt.preventDefault();
            evt.returnValue = '¿Está seguro de salir? Su progreso se guardará automáticamente.';
            return evt.returnValue;
        }
    }

    /**
     * Manejar cambio de visibilidad de la página
     */
    onVisibilityChange() {
        if (document.hidden) {
            // Página oculta, guardar progreso
            if (this.state.isDirty) {
                this.autoSave();
            }
        } else {
            // Página visible, reanudar actividad
            this.state.lastActivityTime = new Date();
        }
    }

    /**
     * Iniciar seguimiento de actividad
     */
    startActivityTracking() {
        // Detectar inactividad
        setInterval(() => {
            const inactiveTime = new Date() - this.state.lastActivityTime;
            const maxInactiveTime = 30 * 60 * 1000; // 30 minutos

            if (inactiveTime > maxInactiveTime && !this.state.isComplete) {
                this.showInactivityWarning();
            }
        }, 60000); // Verificar cada minuto
    }

    /**
     * Mostrar advertencia de inactividad
     */
    showInactivityWarning() {
        const warningModal = new bootstrap.Modal(document.getElementById('inactivityWarningModal'));
        warningModal.show();

        // Auto-cerrar después de 5 minutos
        setTimeout(() => {
            warningModal.hide();
        }, 5 * 60 * 1000);
    }

    /**
     * Mostrar confirmación de salida
     */
    showExitConfirmation() {
        if (this.elements.exitModal) {
            const modal = new bootstrap.Modal(this.elements.exitModal);
            modal.show();
        }
    }

    /**
     * Validar fecha
     */
    isValidDate(dateString) {
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    }

    /**
     * Manejar focus en input
     */
    onInputFocus(input) {
        const question = input.closest('.question-card');
        if (question) {
            question.classList.add('input-focused');
        }
    }

    /**
     * Manejar blur en input
     */
    onInputBlur(input) {
        const question = input.closest('.question-card');
        if (question) {
            question.classList.remove('input-focused');
        }
    }

    /**
     * Manejar cierre de modal
     */
    onModalHidden() {
        // Restaurar focus al elemento apropiado
        const currentQuestion = this.questions[this.state.currentQuestionIndex];
        if (currentQuestion) {
            this.focusFirstInput(currentQuestion);
        }
    }
}

// Inicializar motor cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si estamos en una página de encuesta pública
    if (document.getElementById('surveyContainer') && document.querySelector('.question-card')) {
        // Obtener configuración desde data attributes
        const container = document.getElementById('surveyContainer');
        const surveyId = container?.dataset.surveyId;
        const participantId = container?.dataset.participantId;

        if (surveyId) {
            window.surveyEngine = new SurveyEngine({
                surveyId: surveyId,
                participantId: participantId,
                enableAutoSave: true,
                enableValidation: true,
                showProgress: true,
                allowBackNavigation: true,
                enableAccessibility: true,
                autoSaveDelay: 3000,
                animationDuration: 300
            });

            console.log('✅ SurveyEngine inicializado correctamente');
        } else {
            console.warn('⚠️ Survey ID no encontrado, SurveyEngine no inicializado');
        }
    }
});