/**
 * Sistema de progreso de encuestas
 * assets/js/survey-progress.js
 * 
 * Maneja el guardado automático, validación y navegación en encuestas
 * - Guardado automático de respuestas
 * - Validación en tiempo real
 * - Navegación entre secciones
 * - Indicadores de progreso
 * - Recuperación de sesiones
 */

class SurveyProgress {
    constructor(surveyId, participantToken, options = {}) {
        this.surveyId = surveyId;
        this.participantToken = participantToken;
        this.currentAnswers = {};
        this.currentSectionIndex = 0;
        this.sections = [];
        this.totalQuestions = 0;
        this.requiredQuestions = [];
        
        // Configuración
        this.options = {
            autoSaveInterval: options.autoSaveInterval || 30000, // 30 segundos
            validationDelay: options.validationDelay || 500, // 0.5 segundos
            animationDuration: options.animationDuration || 300,
            debugMode: options.debugMode || false,
            ...options
        };
        
        // Estado interno
        this.isLoading = false;
        this.hasUnsavedChanges = false;
        this.lastSaveTime = null;
        this.saveInProgress = false;
        this.validationTimeouts = new Map();
        
        this.init();
    }
    
    /**
     * Inicializar el sistema de progreso
     */
    init() {
        try {
            this.log('Inicializando sistema de progreso de encuesta');
            
            // Identificar secciones y preguntas
            this.identifyElements();
            
            // Configurar eventos
            this.bindEvents();
            
            // Cargar progreso guardado
            this.loadSavedProgress();
            
            // Iniciar guardado automático
            this.startAutoSave();
            
            // Configurar navegación
            this.setupNavigation();
            
            // Actualizar progreso inicial
            this.updateProgress();
            
            this.log('Sistema de progreso inicializado correctamente');
            
        } catch (error) {
            this.logError('Error al inicializar el sistema de progreso:', error);
        }
    }
    
    /**
     * Identificar elementos del DOM
     */
    identifyElements() {
        // Identificar secciones
        this.sections = Array.from(document.querySelectorAll('.question-section'));
        
        // Contar preguntas totales
        this.totalQuestions = document.querySelectorAll('[name^="question_"]').length;
        
        // Identificar preguntas requeridas
        this.requiredQuestions = Array.from(document.querySelectorAll('[name^="question_"][required]'))
            .map(input => this.extractQuestionId(input.name));
        
        // Elementos de navegación
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.submitBtn = document.getElementById('submitBtn');
        
        // Elementos de progreso
        this.progressBar = document.getElementById('progressBar');
        this.progressText = document.getElementById('progressText');
        this.currentSectionEl = document.getElementById('currentSection');
        this.totalSectionsEl = document.getElementById('totalSections');
        this.answeredCounterEl = document.getElementById('answeredQuestions');
        
        this.log(`Identificados ${this.sections.length} secciones y ${this.totalQuestions} preguntas`);
    }
    
    /**
     * Configurar eventos del DOM
     */
    bindEvents() {
        // Eventos de cambio en respuestas
        document.addEventListener('change', (e) => {
            if (this.isQuestionInput(e.target)) {
                this.handleAnswerChange(e.target);
            }
        });
        
        // Eventos de input para campos de texto
        document.addEventListener('input', (e) => {
            if (this.isQuestionInput(e.target) && (e.target.type === 'text' || e.target.tagName === 'TEXTAREA')) {
                this.handleTextInput(e.target);
            }
        });
        
        // Clicks en opciones
        document.addEventListener('click', (e) => {
            if (e.target.matches('.choice-option, .choice-option *')) {
                const option = e.target.closest('.choice-option');
                if (option) {
                    this.handleOptionClick(option);
                }
            }
        });
        
        // Prevenir pérdida de datos
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges) {
                this.saveProgress(true); // Guardado síncrono
                e.preventDefault();
                e.returnValue = '¿Estás seguro de que quieres salir? Hay cambios sin guardar.';
                return e.returnValue;
            }
        });
        
        // Visibilidad de la página
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.hasUnsavedChanges) {
                this.saveProgress(true);
            }
        });
        
        // Teclas de navegación
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 's':
                        e.preventDefault();
                        this.saveProgress();
                        break;
                    case 'ArrowLeft':
                        if (this.currentSectionIndex > 0) {
                            e.preventDefault();
                            this.previousSection();
                        }
                        break;
                    case 'ArrowRight':
                        if (this.currentSectionIndex < this.sections.length - 1) {
                            e.preventDefault();
                            this.nextSection();
                        }
                        break;
                }
            }
        });
    }
    
    /**
     * Manejar cambio en respuesta
     */
    handleAnswerChange(element) {
        const questionId = this.extractQuestionId(element.name);
        
        if (!questionId) return;
        
        this.clearValidationError(questionId);
        this.updateAnswer(questionId, element);
        this.updateProgress();
        this.markUnsavedChanges();
        
        // Validación con delay
        this.scheduleValidation(questionId);
    }
    
    /**
     * Manejar input de texto con debounce
     */
    handleTextInput(element) {
        const questionId = this.extractQuestionId(element.name);
        
        if (!questionId) return;
        
        // Debounce para inputs de texto
        clearTimeout(this.textInputTimeout);
        this.textInputTimeout = setTimeout(() => {
            this.handleAnswerChange(element);
        }, this.options.validationDelay);
    }
    
    /**
     * Manejar click en opción
     */
    handleOptionClick(option) {
        const input = option.querySelector('input');
        
        if (!input) return;
        
        if (input.type === 'radio') {
            input.checked = true;
            this.handleAnswerChange(input);
            
            // Remover selección de otras opciones
            const name = input.name;
            document.querySelectorAll(`.choice-option`).forEach(opt => {
                const otherInput = opt.querySelector(`input[name="${name}"]`);
                if (otherInput && otherInput !== input) {
                    opt.classList.remove('selected');
                }
            });
            
            option.classList.add('selected');
        } else if (input.type === 'checkbox') {
            input.checked = !input.checked;
            this.handleAnswerChange(input);
            option.classList.toggle('selected', input.checked);
        }
    }
    
    /**
     * Actualizar respuesta interna
     */
    updateAnswer(questionId, element) {
        if (!this.currentAnswers[questionId]) {
            this.currentAnswers[questionId] = {
                values: [],
                text: null,
                timestamp: Date.now()
            };
        }
        
        const answer = this.currentAnswers[questionId];
        
        if (element.type === 'radio') {
            if (element.checked) {
                answer.values = [element.value];
            }
        } else if (element.type === 'checkbox') {
            const currentValues = answer.values || [];
            if (element.checked) {
                if (!currentValues.includes(element.value)) {
                    currentValues.push(element.value);
                }
            } else {
                const index = currentValues.indexOf(element.value);
                if (index > -1) {
                    currentValues.splice(index, 1);
                }
            }
            answer.values = currentValues;
        } else {
            // Text, textarea, select, etc.
            answer.text = element.value;
            answer.values = element.value ? [element.value] : [];
        }
        
        answer.timestamp = Date.now();
        
        this.log(`Respuesta actualizada para pregunta ${questionId}:`, answer);
    }
    
    /**
     * Validar pregunta específica
     */
    validateQuestion(questionId) {
        const question = this.getQuestionElement(questionId);
        
        if (!question) return true;
        
        const isRequired = this.requiredQuestions.includes(questionId);
        const answer = this.currentAnswers[questionId];
        
        if (isRequired && (!answer || this.isAnswerEmpty(answer))) {
            this.showValidationError(questionId, 'Esta pregunta es obligatoria');
            return false;
        }
        
        // Validaciones específicas por tipo
        const questionType = this.getQuestionType(questionId);
        
        switch (questionType) {
            case 'email':
                if (answer && answer.text && !this.isValidEmail(answer.text)) {
                    this.showValidationError(questionId, 'Por favor ingresa un email válido');
                    return false;
                }
                break;
                
            case 'number':
                if (answer && answer.text && isNaN(answer.text)) {
                    this.showValidationError(questionId, 'Por favor ingresa un número válido');
                    return false;
                }
                break;
                
            case 'text':
                if (answer && answer.text && answer.text.length > 5000) {
                    this.showValidationError(questionId, 'El texto es muy largo (máximo 5000 caracteres)');
                    return false;
                }
                break;
        }
        
        this.clearValidationError(questionId);
        return true;
    }
    
    /**
     * Programar validación con delay
     */
    scheduleValidation(questionId) {
        // Cancelar validación anterior
        if (this.validationTimeouts.has(questionId)) {
            clearTimeout(this.validationTimeouts.get(questionId));
        }
        
        // Programar nueva validación
        const timeout = setTimeout(() => {
            this.validateQuestion(questionId);
            this.validationTimeouts.delete(questionId);
        }, this.options.validationDelay);
        
        this.validationTimeouts.set(questionId, timeout);
    }
    
    /**
     * Guardar progreso
     */
    async saveProgress(synchronous = false) {
        if (this.saveInProgress || Object.keys(this.currentAnswers).length === 0) {
            return;
        }
        
        this.saveInProgress = true;
        
        try {
            const data = {
                survey_id: this.surveyId,
                participant_token: this.participantToken,
                answers: this.currentAnswers,
                current_section: this.currentSectionIndex,
                progress_percentage: this.calculateProgress()
            };
            
            this.log('Guardando progreso:', data);
            
            const options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            };
            
            if (synchronous) {
                // Guardado síncrono usando XMLHttpRequest
                await this.saveSynchronously(data);
            } else {
                // Guardado asíncrono con fetch
                const response = await fetch('/api/save-progress', options);
                const result = await response.json();
                
                if (result.success) {
                    this.hasUnsavedChanges = false;
                    this.lastSaveTime = Date.now();
                    this.showSaveIndicator();
                    this.log('Progreso guardado exitosamente');
                } else {
                    throw new Error(result.message || 'Error desconocido');
                }
            }
            
        } catch (error) {
            this.logError('Error al guardar progreso:', error);
            this.showError('Error al guardar progreso. Por favor intenta nuevamente.');
        } finally {
            this.saveInProgress = false;
        }
    }
    
    /**
     * Guardado síncrono para beforeunload
     */
    saveSynchronously(data) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/save-progress', false); // false = síncrono
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const result = JSON.parse(xhr.responseText);
                        if (result.success) {
                            resolve(result);
                        } else {
                            reject(new Error(result.message));
                        }
                    } catch (e) {
                        reject(new Error('Error parsing response'));
                    }
                } else {
                    reject(new Error('HTTP ' + xhr.status));
                }
            };
            
            xhr.onerror = function() {
                reject(new Error('Network error'));
            };
            
            xhr.send(JSON.stringify(data));
        });
    }
    
    /**
     * Cargar progreso guardado
     */
    async loadSavedProgress() {
        if (!this.participantToken) return;
        
        try {
            this.isLoading = true;
            
            const response = await fetch(`/api/get-progress?survey_id=${this.surveyId}&participant_token=${this.participantToken}`);
            const data = await response.json();
            
            if (data.success && data.progress) {
                this.log('Cargando progreso guardado:', data.progress);
                
                this.currentAnswers = data.progress.answers || {};
                
                if (data.progress.current_section !== undefined) {
                    this.currentSectionIndex = data.progress.current_section;
                }
                
                this.restoreAnswersToDOM();
                this.showSection(this.currentSectionIndex);
                this.updateProgress();
                
                this.showInfo('Se ha cargado tu progreso anterior');
            }
            
        } catch (error) {
            this.logError('Error al cargar progreso:', error);
        } finally {
            this.isLoading = false;
        }
    }
    
    /**
     * Restaurar respuestas en el DOM
     */
    restoreAnswersToDOM() {
        Object.keys(this.currentAnswers).forEach(questionId => {
            const answer = this.currentAnswers[questionId];
            const elements = document.querySelectorAll(`[name^="question_${questionId}"]`);
            
            elements.forEach(element => {
                if (element.type === 'radio' || element.type === 'checkbox') {
                    element.checked = answer.values.includes(element.value);
                    
                    // Actualizar visual de opciones
                    const option = element.closest('.choice-option');
                    if (option) {
                        option.classList.toggle('selected', element.checked);
                    }
                } else {
                    element.value = answer.text || (answer.values[0] || '');
                }
            });
        });
    }
    
    /**
     * Navegación entre secciones
     */
    showSection(index) {
        if (index < 0 || index >= this.sections.length) return;
        
        // Ocultar sección actual
        this.sections.forEach(section => section.classList.remove('active'));
        
        // Mostrar nueva sección
        this.sections[index].classList.add('active');
        this.currentSectionIndex = index;
        
        // Actualizar navegación
        this.updateNavigationButtons();
        this.updateSectionCounter();
        
        // Scroll suave al top
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Marcar cambios
        this.markUnsavedChanges();
    }
    
    /**
     * Ir a sección anterior
     */
    previousSection() {
        if (this.currentSectionIndex > 0) {
            this.showSection(this.currentSectionIndex - 1);
        }
    }
    
    /**
     * Ir a siguiente sección
     */
    nextSection() {
        // Validar sección actual
        if (!this.validateCurrentSection()) {
            this.showError('Por favor completa todos los campos obligatorios antes de continuar');
            return;
        }
        
        if (this.currentSectionIndex < this.sections.length - 1) {
            this.showSection(this.currentSectionIndex + 1);
        }
    }
    
    /**
     * Validar sección actual
     */
    validateCurrentSection() {
        const currentSection = this.sections[this.currentSectionIndex];
        if (!currentSection) return true;
        
        const questionInputs = currentSection.querySelectorAll('[name^="question_"]');
        let isValid = true;
        
        const processedQuestions = new Set();
        
        questionInputs.forEach(input => {
            const questionId = this.extractQuestionId(input.name);
            
            if (!questionId || processedQuestions.has(questionId)) return;
            
            processedQuestions.add(questionId);
            
            if (!this.validateQuestion(questionId)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * Actualizar botones de navegación
     */
    updateNavigationButtons() {
        if (this.prevBtn) {
            this.prevBtn.disabled = (this.currentSectionIndex === 0);
        }
        
        if (this.nextBtn && this.submitBtn) {
            const isLastSection = (this.currentSectionIndex === this.sections.length - 1);
            
            this.nextBtn.style.display = isLastSection ? 'none' : 'inline-flex';
            this.submitBtn.style.display = isLastSection ? 'inline-flex' : 'none';
        }
    }
    
    /**
     * Actualizar contador de sección
     */
    updateSectionCounter() {
        if (this.currentSectionEl) {
            this.currentSectionEl.textContent = this.currentSectionIndex + 1;
        }
        
        if (this.totalSectionsEl) {
            this.totalSectionsEl.textContent = this.sections.length;
        }
    }
    
    /**
     * Calcular progreso
     */
    calculateProgress() {
        if (this.totalQuestions === 0) return 0;
        
        const answeredQuestions = Object.keys(this.currentAnswers).filter(questionId => {
            const answer = this.currentAnswers[questionId];
            return !this.isAnswerEmpty(answer);
        }).length;
        
        return Math.round((answeredQuestions / this.totalQuestions) * 100);
    }
    
    /**
     * Actualizar barra de progreso
     */
    updateProgress() {
        const percentage = this.calculateProgress();
        const answeredCount = Object.keys(this.currentAnswers).filter(questionId => {
            const answer = this.currentAnswers[questionId];
            return !this.isAnswerEmpty(answer);
        }).length;
        
        if (this.progressBar) {
            this.progressBar.style.width = percentage + '%';
        }
        
        if (this.progressText) {
            this.progressText.textContent = percentage + '% completado';
        }
        
        if (this.answeredCounterEl) {
            this.answeredCounterEl.textContent = answeredCount;
        }
    }
    
    /**
     * Configurar navegación
     */
    setupNavigation() {
        // Mostrar primera sección
        this.showSection(0);
        
        // Configurar eventos de botones
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.previousSection());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.nextSection());
        }
    }
    
    /**
     * Iniciar guardado automático
     */
    startAutoSave() {
        setInterval(() => {
            if (this.hasUnsavedChanges && !this.saveInProgress) {
                this.saveProgress();
            }
        }, this.options.autoSaveInterval);
    }
    
    /**
     * Marcar cambios sin guardar
     */
    markUnsavedChanges() {
        this.hasUnsavedChanges = true;
    }
    
    /**
     * Utilities
     */
    extractQuestionId(name) {
        const match = name.match(/question_(\d+)/);
        return match ? match[1] : null;
    }
    
    isQuestionInput(element) {
        return element.name && element.name.startsWith('question_');
    }
    
    isAnswerEmpty(answer) {
        if (!answer) return true;
        if (answer.values && answer.values.length > 0) return false;
        if (answer.text && answer.text.trim() !== '') return false;
        return true;
    }
    
    getQuestionElement(questionId) {
        return document.querySelector(`[name="question_${questionId}"]`);
    }
    
    getQuestionType(questionId) {
        const element = this.getQuestionElement(questionId);
        return element ? element.type : 'text';
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * UI Feedback
     */
    showSaveIndicator() {
        const indicator = document.getElementById('saveIndicator');
        if (indicator) {
            indicator.classList.add('show');
            setTimeout(() => {
                indicator.classList.remove('show');
            }, 2000);
        }
    }
    
    showValidationError(questionId, message) {
        const errorDiv = document.getElementById(`error-${questionId}`);
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }
    
    clearValidationError(questionId) {
        const errorDiv = document.getElementById(`error-${questionId}`);
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }
    
    showError(message) {
        // Implementar notificación de error
        console.error(message);
        
        // Toast notification simple
        this.showToast(message, 'error');
    }
    
    showInfo(message) {
        // Implementar notificación informativa
        console.info(message);
        
        this.showToast(message, 'info');
    }
    
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            background: ${type === 'error' ? '#dc3545' : '#17a2b8'};
            color: white;
            border-radius: 4px;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        // Fade in
        setTimeout(() => toast.style.opacity = '1', 10);
        
        // Fade out and remove
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    }
    
    /**
     * Logging
     */
    log(...args) {
        if (this.options.debugMode) {
            console.log('[SurveyProgress]', ...args);
        }
    }
    
    logError(...args) {
        console.error('[SurveyProgress]', ...args);
    }
    
    /**
     * API Pública
     */
    
    // Obtener respuestas actuales
    getCurrentAnswers() {
        return { ...this.currentAnswers };
    }
    
    // Obtener progreso
    getProgress() {
        return this.calculateProgress();
    }
    
    // Forzar guardado
    forceSave() {
        return this.saveProgress();
    }
    
    // Ir a sección específica
    goToSection(index) {
        if (index >= 0 && index < this.sections.length) {
            this.showSection(index);
        }
    }
    
    // Validar toda la encuesta
    validateAll() {
        let isValid = true;
        
        for (let i = 0; i < this.sections.length; i++) {
            this.currentSectionIndex = i;
            if (!this.validateCurrentSection()) {
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Destruir instancia
    destroy() {
        // Limpiar timers
        this.validationTimeouts.forEach(timeout => clearTimeout(timeout));
        this.validationTimeouts.clear();
        
        if (this.textInputTimeout) {
            clearTimeout(this.textInputTimeout);
        }
        
        // Guardar antes de destruir
        if (this.hasUnsavedChanges) {
            this.saveProgress(true);
        }
        
        this.log('Sistema de progreso destruido');
    }
}

// Auto-inicialización
document.addEventListener('DOMContentLoaded', function() {
    const surveyContainer = document.querySelector('[data-survey-id]');
    
    if (surveyContainer) {
        const surveyId = surveyContainer.dataset.surveyId;
        const participantToken = surveyContainer.dataset.participantToken || 
                                document.querySelector('[data-participant-token]')?.dataset.participantToken;
        
        if (surveyId && participantToken) {
            window.surveyProgress = new SurveyProgress(surveyId, participantToken, {
                debugMode: window.location.hostname === 'localhost'
            });
            
            console.log('Sistema de progreso de encuesta inicializado');
        }
    }
});