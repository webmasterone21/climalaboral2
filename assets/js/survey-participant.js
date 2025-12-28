/**
 * Sistema de Participación en Encuestas
 * Maneja navegación, validación, guardado automático y envío
 */

class SurveyParticipant {
    constructor() {
        this.surveyId = document.body.dataset.surveyId;
        this.participantToken = document.body.dataset.participantToken;
        this.currentSection = 0;
        this.totalSections = 0;
        this.sections = [];
        this.answers = {};
        this.autoSaveInterval = null;
        this.isSubmitting = false;
        this.hasUnsavedChanges = false;
        
        this.init();
    }
    
    init() {
        this.setupSections();
        this.bindEvents();
        this.setupAutoSave();
        this.setupBeforeUnload();
        this.updateProgress();
        
        // Mostrar navegación si no estamos en la introducción
        if (!document.getElementById('introduction-section') || document.querySelector('.survey-form').style.display !== 'none') {
            this.showNavigation();
            this.showCurrentSection();
        }
    }
    
    setupSections() {
        this.sections = Array.from(document.querySelectorAll('.question-section'));
        this.totalSections = this.sections.length;
        
        // Ocultar todas las secciones excepto la primera
        this.sections.forEach((section, index) => {
            if (index > 0) {
                section.style.display = 'none';
            }
        });
        
        // Configurar sección de completado
        this.completionSection = document.querySelector('.completion-section');
        if (this.completionSection) {
            this.completionSection.style.display = 'none';
        }
    }
    
    bindEvents() {
        // Eventos de formulario
        const form = document.getElementById('survey-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.showConfirmationModal();
            });
            
            // Detectar cambios en respuestas
            form.addEventListener('change', (e) => {
                if (e.target.matches('input, select, textarea')) {
                    this.handleAnswerChange(e.target);
                }
            });
            
            form.addEventListener('input', (e) => {
                if (e.target.matches('textarea, input[type="text"]')) {
                    this.handleAnswerChange(e.target);
                }
            });
        }
        
        // Eventos de teclado para navegación
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) return;
            
            switch (e.key) {
                case 'ArrowRight':
                case 'ArrowDown':
                    if (e.target.tagName !== 'TEXTAREA' && e.target.type !== 'text') {
                        e.preventDefault();
                        this.nextSection();
                    }
                    break;
                case 'ArrowLeft':
                case 'ArrowUp':
                    if (e.target.tagName !== 'TEXTAREA' && e.target.type !== 'text') {
                        e.preventDefault();
                        this.previousSection();
                    }
                    break;
            }
        });
        
        // Mejorar interacción con estrellas
        this.setupRatingInteraction();
    }
    
    setupRatingInteraction() {
        document.querySelectorAll('.rating-scale').forEach(scale => {
            const options = scale.querySelectorAll('.rating-option');
            
            options.forEach((option, index) => {
                option.addEventListener('mouseenter', () => {
                    // Resaltar estrellas hasta la actual
                    options.forEach((opt, i) => {
                        const star = opt.querySelector('.rating-star .fas');
                        if (i <= index) {
                            star.style.opacity = '1';
                        } else {
                            star.style.opacity = '0';
                        }
                    });
                });
                
                option.addEventListener('mouseleave', () => {
                    // Restaurar estado basado en selección
                    const checkedInput = scale.querySelector('input:checked');
                    if (checkedInput) {
                        const checkedIndex = Array.from(options).indexOf(checkedInput.closest('.rating-option'));
                        options.forEach((opt, i) => {
                            const star = opt.querySelector('.rating-star .fas');
                            star.style.opacity = i <= checkedIndex ? '1' : '0';
                        });
                    } else {
                        options.forEach(opt => {
                            opt.querySelector('.rating-star .fas').style.opacity = '0';
                        });
                    }
                });
                
                option.addEventListener('click', () => {
                    const input = option.querySelector('input');
                    input.checked = true;
                    this.handleAnswerChange(input);
                });
            });
        });
    }
    
    setupAutoSave() {
        // Guardar cada 30 segundos si hay cambios
        this.autoSaveInterval = setInterval(() => {
            if (this.hasUnsavedChanges && !this.isSubmitting) {
                this.saveProgress();
            }
        }, 30000);
    }
    
    setupBeforeUnload() {
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges && !this.isSubmitting) {
                e.preventDefault();
                e.returnValue = '¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.';
                return e.returnValue;
            }
        });
    }
    
    handleAnswerChange(element) {
        const questionId = this.extractQuestionId(element.name);
        if (!questionId) return;
        
        let value = null;
        
        if (element.type === 'radio') {
            if (element.checked) {
                value = element.value;
            }
        } else if (element.type === 'checkbox') {
            // Manejar checkboxes múltiples
            const checkboxes = document.querySelectorAll(`input[name="${element.name}"]`);
            const checkedValues = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            value = checkedValues.length > 0 ? checkedValues.join(',') : null;
        } else {
            value = element.value.trim() || null;
        }
        
        if (value !== null && value !== '') {
            this.answers[questionId] = {
                value: value,
                timestamp: new Date().toISOString()
            };
        } else {
            delete this.answers[questionId];
        }
        
        this.hasUnsavedChanges = true;
        this.updateProgress();
        
        // Validar sección actual
        this.validateCurrentSection();
    }
    
    extractQuestionId(name) {
        const match = name.match(/question_(\d+)/);
        return match ? match[1] : null;
    }
    
    validateCurrentSection() {
        if (this.currentSection >= this.totalSections) return true;
        
        const currentSectionElement = this.sections[this.currentSection];
        const requiredFields = currentSectionElement.querySelectorAll('input[required], select[required], textarea[required]');
        
        let allValid = true;
        const fieldGroups = new Map();
        
        // Agrupar campos por nombre (para radio buttons)
        requiredFields.forEach(field => {
            if (!fieldGroups.has(field.name)) {
                fieldGroups.set(field.name, []);
            }
            fieldGroups.get(field.name).push(field);
        });
        
        // Validar cada grupo
        for (const [name, fields] of fieldGroups) {
            let groupValid = false;
            
            if (fields[0].type === 'radio') {
                groupValid = fields.some(field => field.checked);
            } else {
                groupValid = fields.every(field => {
                    return field.value.trim() !== '';
                });
            }
            
            if (!groupValid) {
                allValid = false;
                break;
            }
        }
        
        // Actualizar estado del botón siguiente
        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) {
            if (allValid) {
                nextBtn.disabled = false;
                nextBtn.textContent = this.currentSection === this.totalSections - 1 ? 'Finalizar' : 'Siguiente';
            } else {
                nextBtn.disabled = true;
                nextBtn.textContent = 'Completa todas las preguntas requeridas';
            }
        }
        
        return allValid;
    }
    
    updateProgress() {
        const totalQuestions = Object.keys(this.getAllQuestions()).length;
        const answeredQuestions = Object.keys(this.answers).length;
        const percentage = totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0;
        
        // Actualizar barra de progreso
        const progressFill = document.querySelector('.progress-fill');
        const progressText = document.querySelector('.progress-text');
        const questionsCount = document.querySelector('.questions-count');
        
        if (progressFill) {
            progressFill.style.width = percentage + '%';
        }
        
        if (progressText) {
            progressText.textContent = `${percentage}% completado`;
        }
        
        if (questionsCount) {
            questionsCount.textContent = `${this.currentSection + 1} de ${this.totalSections} secciones`;
        }
        
        // Actualizar navegación
        this.updateNavigation();
    }
    
    updateNavigation() {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const currentSectionSpan = document.querySelector('.current-section');
        const currentSpan = document.querySelector('.current');
        const totalSpan = document.querySelector('.total');
        
        if (prevBtn) {
            prevBtn.disabled = this.currentSection === 0;
        }
        
        if (currentSectionSpan) {
            if (this.currentSection < this.totalSections) {
                const sectionName = this.sections[this.currentSection]?.querySelector('h2')?.textContent || `Sección ${this.currentSection + 1}`;
                currentSectionSpan.textContent = sectionName;
            }
        }
        
        if (currentSpan) {
            currentSpan.textContent = this.currentSection + 1;
        }
        
        if (totalSpan) {
            totalSpan.textContent = this.totalSections;
        }
        
        // Validar sección actual para habilitar/deshabilitar botón siguiente
        this.validateCurrentSection();
    }
    
    getAllQuestions() {
        const questions = {};
        document.querySelectorAll('input[name^="question_"], select[name^="question_"], textarea[name^="question_"]').forEach(element => {
            const questionId = this.extractQuestionId(element.name);
            if (questionId) {
                questions[questionId] = element;
            }
        });
        return questions;
    }
    
    showCurrentSection() {
        // Ocultar todas las secciones
        this.sections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Mostrar sección actual
        if (this.currentSection < this.totalSections) {
            const currentSectionElement = this.sections[this.currentSection];
            currentSectionElement.style.display = 'block';
            currentSectionElement.classList.add('slide-in-right');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Focus en el primer campo
            const firstInput = currentSectionElement.querySelector('input, select, textarea');
            if (firstInput && firstInput.type !== 'hidden') {
                setTimeout(() => firstInput.focus(), 300);
            }
        } else {
            // Mostrar sección de completado
            this.showCompletionSection();
        }
        
        this.updateProgress();
    }
    
    showCompletionSection() {
        this.sections.forEach(section => {
            section.style.display = 'none';
        });
        
        if (this.completionSection) {
            this.completionSection.style.display = 'block';
            this.completionSection.classList.add('slide-in-right');
        }
        
        // Ocultar navegación normal
        const navigation = document.getElementById('survey-navigation');
        if (navigation) {
            navigation.style.display = 'none';
        }
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    nextSection() {
        if (!this.validateCurrentSection()) {
            this.showAlert('Por favor completa todas las preguntas requeridas antes de continuar.', 'warning');
            return;
        }
        
        // Guardar progreso antes de continuar
        this.saveProgress();
        
        this.currentSection++;
        
        if (this.currentSection >= this.totalSections) {
            this.showCompletionSection();
        } else {
            this.showCurrentSection();
        }
    }
    
    previousSection() {
        if (this.currentSection > 0) {
            this.currentSection--;
            this.showCurrentSection();
            
            const navigation = document.getElementById('survey-navigation');
            if (navigation) {
                navigation.style.display = 'block';
            }
        }
    }
    
    showNavigation() {
        const navigation = document.getElementById('survey-navigation');
        if (navigation) {
            navigation.style.display = 'block';
        }
    }
    
    async saveProgress() {
        if (Object.keys(this.answers).length === 0) return;
        
        try {
            this.showSaveIndicator('saving');
            
            const data = {
                survey_id: this.surveyId,
                participant_token: this.participantToken,
                answers: this.answers,
                current_question: this.getCurrentQuestionId(),
                progress_percentage: this.calculateProgressPercentage()
            };
            
            const response = await fetch(`${window.location.origin}/api/save-progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.hasUnsavedChanges = false;
                if (result.token && !this.participantToken) {
                    this.participantToken = result.token;
                    document.body.dataset.participantToken = result.token;
                }
                this.showSaveIndicator('success');
            } else {
                throw new Error(result.message || 'Error al guardar');
            }
            
        } catch (error) {
            console.error('Error saving progress:', error);
            this.showSaveIndicator('error');
        }
    }
    
    calculateProgressPercentage() {
        const totalQuestions = Object.keys(this.getAllQuestions()).length;
        const answeredQuestions = Object.keys(this.answers).length;
        return totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0;
    }
    
    getCurrentQuestionId() {
        if (this.currentSection < this.totalSections) {
            const currentSectionElement = this.sections[this.currentSection];
            const firstQuestion = currentSectionElement.querySelector('[data-question-id]');
            return firstQuestion ? firstQuestion.dataset.questionId : null;
        }
        return null;
    }
    
    showSaveIndicator(type = 'success') {
        const indicator = document.getElementById('save-indicator');
        if (!indicator) return;
        
        // Remover clases anteriores
        indicator.classList.remove('saving', 'error', 'show');
        
        // Configurar según tipo
        let message = '';
        switch (type) {
            case 'saving':
                message = 'Guardando...';
                indicator.classList.add('saving');
                break;
            case 'error':
                message = 'Error al guardar';
                indicator.classList.add('error');
                break;
            default:
                message = 'Guardado automáticamente';
                break;
        }
        
        const span = indicator.querySelector('span');
        if (span) {
            span.textContent = message;
        }
        
        // Mostrar indicador
        indicator.classList.add('show');
        
        // Ocultar después de 3 segundos (excepto si está guardando)
        if (type !== 'saving') {
            setTimeout(() => {
                indicator.classList.remove('show');
            }, 3000);
        }
    }
    
    showConfirmationModal() {
        const modal = document.getElementById('confirm-modal');
        if (modal) {
            modal.classList.add('show');
        }
    }
    
    closeModal() {
        const modal = document.getElementById('confirm-modal');
        if (modal) {
            modal.classList.remove('show');
        }
    }
    
    async confirmSubmission() {
        this.closeModal();
        
        if (this.isSubmitting) return;
        this.isSubmitting = true;
        
        try {
            // Preparar datos finales
            const formData = new FormData();
            formData.append('survey_id', this.surveyId);
            formData.append('participant_token', this.participantToken);
            formData.append('complete', '1');
            
            // Agregar todas las respuestas
            for (const [questionId, answer] of Object.entries(this.answers)) {
                formData.append(`question_${questionId}`, answer.value);
            }
            
            // Agregar información del participante si existe
            const employeeCode = document.getElementById('employee_code');
            const departmentId = document.getElementById('department_id');
            
            if (employeeCode && employeeCode.value) {
                formData.append('employee_code', employeeCode.value);
            }
            
            if (departmentId && departmentId.value) {
                formData.append('department_id', departmentId.value);
            }
            
            const response = await fetch(`${window.location.origin}/survey/${this.surveyId}/participate`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.hasUnsavedChanges = false;
                if (result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    this.showAlert('¡Encuesta enviada exitosamente! Gracias por tu participación.', 'success');
                    setTimeout(() => {
                        window.location.href = `${window.location.origin}/survey/${this.surveyId}/thank-you`;
                    }, 2000);
                }
            } else {
                throw new Error(result.message || 'Error al enviar la encuesta');
            }
            
        } catch (error) {
            console.error('Error submitting survey:', error);
            this.showAlert('Error al enviar la encuesta. Por favor inténtalo de nuevo.', 'error');
            this.isSubmitting = false;
        }
    }
    
    loadSavedProgress(savedAnswers) {
        if (!savedAnswers || typeof savedAnswers !== 'object') return;
        
        this.answers = savedAnswers;
        
        // Restaurar valores en el formulario
        for (const [questionId, answer] of Object.entries(savedAnswers)) {
            const elements = document.querySelectorAll(`[name="question_${questionId}"]`);
            
            elements.forEach(element => {
                if (element.type === 'radio') {
                    if (element.value === answer.value) {
                        element.checked = true;
                    }
                } else if (element.type === 'checkbox') {
                    const values = answer.value.split(',');
                    if (values.includes(element.value)) {
                        element.checked = true;
                    }
                } else {
                    element.value = answer.value || '';
                }
            });
        }
        
        this.updateProgress();
        this.showAlert('Se ha restaurado tu progreso anterior.', 'info');
    }
    
    reviewAnswers() {
        // Implementar funcionalidad de revisión
        this.currentSection = 0;
        this.showCurrentSection();
        this.showAlert('Puedes revisar y modificar tus respuestas navegando por las secciones.', 'info');
    }
    
    showAlert(message, type = 'info') {
        // Crear elemento de alerta
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <div class="alert-content">
                <i class="fas fa-${this.getAlertIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Estilos para la alerta
        Object.assign(alert.style, {
            position: 'fixed',
            top: '20px',
            left: '50%',
            transform: 'translateX(-50%)',
            zIndex: '1060',
            padding: '1rem 1.5rem',
            borderRadius: '0.5rem',
            color: 'white',
            fontWeight: '500',
            boxShadow: '0 0.5rem 1rem rgba(0, 0, 0, 0.15)',
            opacity: '0',
            transition: 'all 0.3s ease'
        });
        
        // Color según tipo
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        
        alert.style.backgroundColor = colors[type] || colors.info;
        
        document.body.appendChild(alert);
        
        // Mostrar con animación
        setTimeout(() => {
            alert.style.opacity = '1';
            alert.style.transform = 'translateX(-50%) translateY(0)';
        }, 10);
        
        // Ocultar después de 5 segundos
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(-50%) translateY(-10px)';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    }
    
    getAlertIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }
}

// Funciones globales para uso en HTML
function startSurvey() {
    const introduction = document.getElementById('introduction-section');
    const form = document.querySelector('.survey-form');
    const navigation = document.getElementById('survey-navigation');
    
    if (introduction) {
        introduction.style.display = 'none';
    }
    
    if (form) {
        form.style.display = 'block';
    }
    
    if (navigation) {
        navigation.style.display = 'block';
    }
    
    // Inicializar participante si no existe
    if (!window.surveyParticipant) {
        window.surveyParticipant = new SurveyParticipant();
    } else {
        window.surveyParticipant.showCurrentSection();
    }
}

function nextSection() {
    if (window.surveyParticipant) {
        window.surveyParticipant.nextSection();
    }
}

function previousSection() {
    if (window.surveyParticipant) {
        window.surveyParticipant.previousSection();
    }
}

function submitSurvey() {
    if (window.surveyParticipant) {
        window.surveyParticipant.showConfirmationModal();
    }
}

function confirmSubmission() {
    if (window.surveyParticipant) {
        window.surveyParticipant.confirmSubmission();
    }
}

function closeModal() {
    if (window.surveyParticipant) {
        window.surveyParticipant.closeModal();
    }
}

function reviewAnswers() {
    if (window.surveyParticipant) {
        window.surveyParticipant.reviewAnswers();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar si no estamos en la página de introducción
    if (!document.getElementById('introduction-section') || document.querySelector('.survey-form').style.display !== 'none') {
        window.surveyParticipant = new SurveyParticipant();
    }
});