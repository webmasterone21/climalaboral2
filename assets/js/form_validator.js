/**
 * ================================================================================
 * SISTEMA HERCO v2.0 - VALIDADOR DE FORMULARIOS
 * ================================================================================
 * Sistema avanzado de validación de formularios con:
 * - Validación en tiempo real
 * - Múltiples reglas de validación
 * - Integración con Bootstrap
 * - Mensajes personalizables
 * - Soporte para campos complejos
 * ================================================================================
 */

class FormValidator {
    constructor(formId, rules = {}, options = {}) {
        this.form = document.getElementById(formId);
        this.rules = rules;
        this.options = {
            showTooltips: true,
            realTimeValidation: false,
            submitOnValid: false,
            focusFirstError: true,
            validateOnBlur: true,
            showSuccessIcons: true,
            debounceTime: 300,
            ...options
        };
        
        this.errors = {};
        this.isValid = false;
        this.debounceTimers = {};
        
        if (!this.form) {
            console.error(`FormValidator: Formulario con ID "${formId}" no encontrado`);
            return;
        }
        
        this.init();
    }
    
    /**
     * Inicializar el validador
     */
    init() {
        this.setupEventListeners();
        this.setupTooltips();
        console.log('FormValidator inicializado para:', this.form.id);
    }
    
    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Validar al enviar formulario
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.validateForm();
        });
        
        // Validar campos individuales
        Object.keys(this.rules).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (!field) return;
            
            // Validar al perder el foco
            if (this.options.validateOnBlur) {
                field.addEventListener('blur', () => {
                    this.validateField(fieldName);
                });
            }
            
            // Validar al escribir (con debounce)
            if (this.options.realTimeValidation) {
                field.addEventListener('input', () => {
                    this.debounceValidation(fieldName);
                });
            }
            
            // Limpiar errores al empezar a escribir
            field.addEventListener('focus', () => {
                this.clearFieldError(fieldName);
            });
        });
    }
    
    /**
     * Configurar tooltips para campos con errores
     */
    setupTooltips() {
        if (!this.options.showTooltips) return;
        
        // Inicializar Bootstrap tooltips si está disponible
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(
                this.form.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }
    
    /**
     * Validación con debounce
     */
    debounceValidation(fieldName) {
        clearTimeout(this.debounceTimers[fieldName]);
        this.debounceTimers[fieldName] = setTimeout(() => {
            this.validateField(fieldName);
        }, this.options.debounceTime);
    }
    
    /**
     * Habilitar validación en tiempo real
     */
    enableRealTimeValidation() {
        this.options.realTimeValidation = true;
        this.setupEventListeners();
    }
    
    /**
     * Validar formulario completo
     */
    validateForm() {
        this.errors = {};
        let isFormValid = true;
        
        // Validar todos los campos
        Object.keys(this.rules).forEach(fieldName => {
            if (!this.validateField(fieldName)) {
                isFormValid = false;
            }
        });
        
        this.isValid = isFormValid;
        
        if (isFormValid) {
            this.onValidationSuccess();
        } else {
            this.onValidationError();
        }
        
        return isFormValid;
    }
    
    /**
     * Validar campo individual
     */
    validateField(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const rules = this.rules[fieldName];
        
        if (!field || !rules) return true;
        
        const value = this.getFieldValue(field);
        const errors = [];
        
        // Aplicar reglas de validación
        if (rules.required && this.isEmpty(value)) {
            errors.push(rules.message || 'Este campo es requerido');
        } else if (!this.isEmpty(value)) {
            // Solo validar formato si el campo no está vacío
            
            // Validación de tipo email
            if (rules.email && !this.isValidEmail(value)) {
                errors.push('Ingrese un email válido');
            }
            
            // Validación de longitud mínima
            if (rules.minLength && value.length < rules.minLength) {
                errors.push(`Mínimo ${rules.minLength} caracteres`);
            }
            
            // Validación de longitud máxima
            if (rules.maxLength && value.length > rules.maxLength) {
                errors.push(`Máximo ${rules.maxLength} caracteres`);
            }
            
            // Validación de número
            if (rules.number && !this.isValidNumber(value)) {
                errors.push('Ingrese un número válido');
            }
            
            // Validación de patrón regex
            if (rules.pattern && !rules.pattern.test(value)) {
                errors.push(rules.patternMessage || 'Formato inválido');
            }
            
            // Validación personalizada
            if (rules.custom && typeof rules.custom === 'function') {
                const customError = rules.custom(value, field);
                if (customError) {
                    errors.push(customError);
                }
            }
            
            // Validación de confirmación de contraseña
            if (rules.confirm) {
                const confirmField = this.form.querySelector(`[name="${rules.confirm}"]`);
                if (confirmField && value !== this.getFieldValue(confirmField)) {
                    errors.push('Las contraseñas no coinciden');
                }
            }
            
            // Validación de fuerza de contraseña
            if (rules.passwordStrength) {
                const strengthError = this.validatePasswordStrength(value, rules.passwordStrength);
                if (strengthError) {
                    errors.push(strengthError);
                }
            }
        }
        
        // Mostrar/ocultar errores
        if (errors.length > 0) {
            this.showFieldError(fieldName, errors[0]);
            this.errors[fieldName] = errors;
            return false;
        } else {
            this.showFieldSuccess(fieldName);
            delete this.errors[fieldName];
            return true;
        }
    }
    
    /**
     * Obtener valor de campo
     */
    getFieldValue(field) {
        if (field.type === 'checkbox' || field.type === 'radio') {
            if (field.type === 'checkbox') {
                return field.checked;
            } else {
                const checkedRadio = this.form.querySelector(`[name="${field.name}"]:checked`);
                return checkedRadio ? checkedRadio.value : '';
            }
        }
        return field.value.trim();
    }
    
    /**
     * Verificar si un valor está vacío
     */
    isEmpty(value) {
        if (typeof value === 'boolean') return false;
        return value === '' || value === null || value === undefined;
    }
    
    /**
     * Validar email
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * Validar número
     */
    isValidNumber(value) {
        return !isNaN(value) && !isNaN(parseFloat(value));
    }
    
    /**
     * Validar fuerza de contraseña
     */
    validatePasswordStrength(password, requirements) {
        const checks = {
            minLength: password.length >= (requirements.minLength || 8),
            hasUpper: /[A-Z]/.test(password),
            hasLower: /[a-z]/.test(password),
            hasNumber: /\d/.test(password),
            hasSpecial: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
        
        const requiredChecks = requirements.require || ['minLength'];
        const failedChecks = requiredChecks.filter(check => !checks[check]);
        
        if (failedChecks.length > 0) {
            const messages = {
                minLength: `Mínimo ${requirements.minLength || 8} caracteres`,
                hasUpper: 'Al menos una mayúscula',
                hasLower: 'Al menos una minúscula',
                hasNumber: 'Al menos un número',
                hasSpecial: 'Al menos un carácter especial'
            };
            
            return messages[failedChecks[0]];
        }
        
        return null;
    }
    
    /**
     * Mostrar error en campo
     */
    showFieldError(fieldName, message) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        // Agregar clases de error
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        // Mostrar mensaje de error
        let errorElement = this.form.querySelector(`#${fieldName}-error`);
        if (!errorElement) {
            errorElement = this.createErrorElement(fieldName);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        
        // Agregar tooltip si está habilitado
        if (this.options.showTooltips && typeof bootstrap !== 'undefined') {
            field.setAttribute('data-bs-toggle', 'tooltip');
            field.setAttribute('data-bs-placement', 'top');
            field.setAttribute('title', message);
        }
    }
    
    /**
     * Mostrar éxito en campo
     */
    showFieldSuccess(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        // Agregar clases de éxito
        field.classList.remove('is-invalid');
        if (this.options.showSuccessIcons) {
            field.classList.add('is-valid');
        }
        
        // Ocultar mensaje de error
        const errorElement = this.form.querySelector(`#${fieldName}-error`);
        if (errorElement) {
            errorElement.style.display = 'none';
            errorElement.textContent = '';
        }
        
        // Remover tooltip
        if (this.options.showTooltips) {
            field.removeAttribute('data-bs-toggle');
            field.removeAttribute('title');
        }
    }
    
    /**
     * Limpiar error de campo
     */
    clearFieldError(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        field.classList.remove('is-invalid', 'is-valid');
        
        const errorElement = this.form.querySelector(`#${fieldName}-error`);
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
    
    /**
     * Crear elemento de error
     */
    createErrorElement(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const errorElement = document.createElement('div');
        
        errorElement.id = `${fieldName}-error`;
        errorElement.className = 'invalid-feedback d-block';
        errorElement.style.display = 'none';
        
        // Insertar después del campo o su contenedor
        const container = field.closest('.form-group') || field.closest('.auth-form-group') || field.parentNode;
        container.appendChild(errorElement);
        
        return errorElement;
    }
    
    /**
     * Enfocar primer campo con error
     */
    focusFirstError() {
        if (!this.options.focusFirstError) return;
        
        const firstErrorField = Object.keys(this.errors)[0];
        if (firstErrorField) {
            const field = this.form.querySelector(`[name="${firstErrorField}"]`);
            if (field) {
                field.focus();
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
    
    /**
     * Callback para validación exitosa
     */
    onValidationSuccess() {
        console.log('Formulario válido');
        
        // Disparar evento personalizado
        this.form.dispatchEvent(new CustomEvent('form:valid', {
            detail: { validator: this }
        }));
        
        // Auto-enviar si está habilitado
        if (this.options.submitOnValid) {
            this.submitForm();
        }
    }
    
    /**
     * Callback para error de validación
     */
    onValidationError() {
        console.log('Errores de validación:', this.errors);
        
        // Enfocar primer error
        this.focusFirstError();
        
        // Disparar evento personalizado
        this.form.dispatchEvent(new CustomEvent('form:invalid', {
            detail: { 
                validator: this,
                errors: this.errors
            }
        }));
        
        // Mostrar notificación si está disponible
        if (window.notifications) {
            const errorCount = Object.keys(this.errors).length;
            window.notifications.show(
                `Corrija ${errorCount} error${errorCount > 1 ? 'es' : ''} en el formulario`,
                'error',
                3000
            );
        }
    }
    
    /**
     * Enviar formulario
     */
    submitForm() {
        if (this.isValid) {
            this.form.submit();
        }
    }
    
    /**
     * Obtener datos del formulario
     */
    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }
    
    /**
     * Limpiar todos los errores
     */
    clearAllErrors() {
        this.errors = {};
        
        Object.keys(this.rules).forEach(fieldName => {
            this.clearFieldError(fieldName);
        });
    }
    
    /**
     * Agregar regla de validación
     */
    addRule(fieldName, rule) {
        this.rules[fieldName] = { ...this.rules[fieldName], ...rule };
    }
    
    /**
     * Remover regla de validación
     */
    removeRule(fieldName) {
        delete this.rules[fieldName];
    }
    
    /**
     * Habilitar/deshabilitar campo
     */
    setFieldEnabled(fieldName, enabled) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.disabled = !enabled;
        }
    }
    
    /**
     * Establecer valor de campo
     */
    setFieldValue(fieldName, value) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = Boolean(value);
            } else {
                field.value = value;
            }
            
            // Validar campo después de establecer valor
            if (this.options.realTimeValidation) {
                this.validateField(fieldName);
            }
        }
    }
    
    /**
     * Obtener valor de campo
     */
    getFieldValue(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        return field ? this.getFieldValue(field) : null;
    }
    
    /**
     * Destruir validador
     */
    destroy() {
        this.clearAllErrors();
        
        // Remover event listeners
        Object.keys(this.debounceTimers).forEach(timer => {
            clearTimeout(this.debounceTimers[timer]);
        });
        
        console.log('FormValidator destruido para:', this.form.id);
    }
}

/**
 * ================================================================================
 * VALIDACIONES ESPECÍFICAS PARA SISTEMA HERCO
 * ================================================================================
 */

// Validador de formato HERCO para emails corporativos
FormValidator.validateCorporateEmail = function(email, allowedDomains = []) {
    if (!FormValidator.prototype.isValidEmail(email)) {
        return 'Formato de email inválido';
    }
    
    if (allowedDomains.length > 0) {
        const domain = email.split('@')[1];
        if (!allowedDomains.includes(domain)) {
            return `Email debe ser de uno de estos dominios: ${allowedDomains.join(', ')}`;
        }
    }
    
    return null;
};

// Validador de códigos de encuesta HERCO
FormValidator.validateSurveyCode = function(code) {
    const hercoCodeRegex = /^HERCO-[A-Z0-9]{6,12}$/;
    if (!hercoCodeRegex.test(code)) {
        return 'Código debe tener formato HERCO-XXXXXX';
    }
    return null;
};

// Validador de fechas de encuesta
FormValidator.validateSurveyDates = function(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const now = new Date();
    
    if (start >= end) {
        return 'La fecha de inicio debe ser anterior a la fecha de fin';
    }
    
    if (start < now.setHours(0, 0, 0, 0)) {
        return 'La fecha de inicio no puede ser en el pasado';
    }
    
    const maxDuration = 90; // días
    const durationDays = (end - start) / (1000 * 60 * 60 * 24);
    
    if (durationDays > maxDuration) {
        return `La duración máxima de una encuesta es ${maxDuration} días`;
    }
    
    return null;
};

// Validador de configuraciones HERCO
FormValidator.validateHercoConfig = function(config) {
    const requiredKeys = ['company_id', 'survey_type', 'categories'];
    
    for (let key of requiredKeys) {
        if (!config[key]) {
            return `Configuración requerida faltante: ${key}`;
        }
    }
    
    if (config.categories && config.categories.length < 3) {
        return 'Debe seleccionar al menos 3 categorías HERCO';
    }
    
    return null;
};

/**
 * ================================================================================
 * INICIALIZACIÓN GLOBAL
 * ================================================================================
 */

// Hacer disponible globalmente
window.FormValidator = FormValidator;

// Auto-inicializar validadores basados en atributos data
document.addEventListener('DOMContentLoaded', function() {
    const formsWithValidation = document.querySelectorAll('[data-validate]');
    
    formsWithValidation.forEach(form => {
        try {
            const rules = JSON.parse(form.getAttribute('data-validation-rules') || '{}');
            const options = JSON.parse(form.getAttribute('data-validation-options') || '{}');
            
            new FormValidator(form.id, rules, options);
        } catch (error) {
            console.error('Error inicializando validador automático:', error);
        }
    });
    
    console.log('🔧 Sistema de Validaciones HERCO v2.0 Cargado');
});

/**
 * ================================================================================
 * UTILIDADES DE EXPORTACIÓN
 * ================================================================================
 */

// Configuraciones predefinidas para diferentes tipos de formularios
window.HercoValidators = {
    login: {
        email: {
            required: true,
            email: true,
            message: 'Ingrese un email válido'
        },
        password: {
            required: true,
            minLength: 6,
            message: 'La contraseña debe tener al menos 6 caracteres'
        }
    },
    
    register: {
        name: {
            required: true,
            minLength: 2,
            maxLength: 50,
            message: 'El nombre debe tener entre 2 y 50 caracteres'
        },
        email: {
            required: true,
            email: true,
            message: 'Ingrese un email válido'
        },
        password: {
            required: true,
            minLength: 8,
            passwordStrength: {
                minLength: 8,
                require: ['minLength', 'hasUpper', 'hasLower', 'hasNumber']
            },
            message: 'La contraseña debe cumplir los requisitos de seguridad'
        },
        password_confirmation: {
            required: true,
            confirm: 'password',
            message: 'Las contraseñas no coinciden'
        }
    },
    
    survey: {
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
            message: 'Seleccione la fecha de fin'
        }
    }
};