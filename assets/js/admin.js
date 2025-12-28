/**
 * JAVASCRIPT ADMINISTRATIVO - SISTEMA HERCO v2.0
 * 
 * Funcionalidades principales para el panel administrativo
 * Incluye AJAX, validaciones, UI/UX y utilidades
 * 
 * @package HERCO\Assets
 * @version 2.0
 * @author Sistema HERCO
 */

// ===================================
// CONFIGURACIÓN GLOBAL
// ===================================

const HercoAdmin = {
    // Configuración
    config: {
        baseUrl: window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/'),
        apiUrl: '/api/v1',
        csrfToken: $('meta[name="csrf-token"]').attr('content'),
        language: 'es',
        dateFormat: 'DD/MM/YYYY',
        timeFormat: 'HH:mm',
        timezone: 'America/Tegucigalpa'
    },
    
    // Estado global
    state: {
        user: null,
        notifications: [],
        activeRequests: 0,
        isLoading: false
    },
    
    // Cache
    cache: new Map(),
    
    // Timers
    timers: {
        notifications: null,
        autosave: null,
        sessionCheck: null
    }
};

// ===================================
// INICIALIZACIÓN
// ===================================

$(document).ready(function() {
    HercoAdmin.init();
});

HercoAdmin.init = function() {
    console.log('🚀 Inicializando HERCO Admin v2.0...');
    
    // Configurar AJAX
    this.setupAjax();
    
    // Inicializar componentes
    this.initComponents();
    
    // Configurar eventos globales
    this.setupGlobalEvents();
    
    // Cargar datos iniciales
    this.loadInitialData();
    
    // Iniciar timers
    this.startTimers();
    
    console.log('✅ HERCO Admin inicializado correctamente');
};

// ===================================
// CONFIGURACIÓN AJAX
// ===================================

HercoAdmin.setupAjax = function() {
    // Configuración global de AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': this.config.csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 30000,
        cache: false
    });
    
    // Interceptor de requests
    $(document).ajaxSend(function(event, jqXHR, settings) {
        HercoAdmin.state.activeRequests++;
        HercoAdmin.showLoading();
        
        // Log para desarrollo
        console.log('📤 AJAX Request:', settings.type, settings.url);
    });
    
    // Interceptor de respuestas exitosas
    $(document).ajaxSuccess(function(event, jqXHR, settings, data) {
        HercoAdmin.state.activeRequests--;
        
        if (HercoAdmin.state.activeRequests === 0) {
            HercoAdmin.hideLoading();
        }
        
        console.log('✅ AJAX Success:', settings.url, data);
    });
    
    // Interceptor de errores
    $(document).ajaxError(function(event, jqXHR, settings, thrownError) {
        HercoAdmin.state.activeRequests--;
        
        if (HercoAdmin.state.activeRequests === 0) {
            HercoAdmin.hideLoading();
        }
        
        console.error('❌ AJAX Error:', settings.url, jqXHR.status, thrownError);
        HercoAdmin.handleAjaxError(jqXHR, settings);
    });
};

// ===================================
// MANEJO DE ERRORES AJAX
// ===================================

HercoAdmin.handleAjaxError = function(jqXHR, settings) {
    const status = jqXHR.status;
    let message = 'Error desconocido';
    let type = 'danger';
    
    switch (status) {
        case 0:
            message = 'Sin conexión a internet. Verifique su conexión.';
            break;
        case 401:
            message = 'Su sesión ha expirado. Recargando página...';
            setTimeout(() => window.location.reload(), 2000);
            break;
        case 403:
            message = 'No tiene permisos para realizar esta acción.';
            break;
        case 404:
            message = 'El recurso solicitado no existe.';
            break;
        case 419:
            message = 'Token de seguridad expirado. Recargando página...';
            setTimeout(() => window.location.reload(), 2000);
            break;
        case 422:
            // Errores de validación
            const errors = jqXHR.responseJSON?.errors;
            if (errors) {
                this.displayValidationErrors(errors);
                return;
            }
            message = jqXHR.responseJSON?.message || 'Datos inválidos';
            break;
        case 429:
            message = 'Demasiadas solicitudes. Intente nuevamente en unos minutos.';
            break;
        case 500:
            message = 'Error interno del servidor. Contacte al administrador.';
            break;
        case 503:
            message = 'Sistema en mantenimiento. Intente más tarde.';
            break;
        default:
            message = jqXHR.responseJSON?.message || `Error ${status}: ${jqXHR.statusText}`;
    }
    
    this.showAlert(type, message);
};

// ===================================
// INICIALIZACIÓN DE COMPONENTES
// ===================================

HercoAdmin.initComponents = function() {
    // Tooltips
    this.initTooltips();
    
    // Modales
    this.initModals();
    
    // Formularios
    this.initForms();
    
    // Tablas
    this.initTables();
    
    // Dropdowns
    this.initDropdowns();
    
    // File uploads
    this.initFileUploads();
    
    // Charts
    this.initCharts();
    
    // Fecha y hora
    this.initDateTime();
    
    // Sidebar
    this.initSidebar();
};

// ===================================
// TOOLTIPS
// ===================================

HercoAdmin.initTooltips = function() {
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 500, hide: 100 }
        });
    });
    
    // Tooltips dinámicos para elementos truncados
    $('[data-toggle="tooltip-truncate"]').each(function() {
        const $this = $(this);
        if (this.offsetWidth < this.scrollWidth) {
            $this.attr('title', $this.text()).tooltip();
        }
    });
};

// ===================================
// MODALES
// ===================================

HercoAdmin.initModals = function() {
    // Auto-focus en modales
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('[autofocus]').focus();
    });
    
    // Limpiar formularios al cerrar modales
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0]?.reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();
    });
    
    // Modales AJAX
    $(document).on('click', '[data-modal-url]', function(e) {
        e.preventDefault();
        const url = $(this).data('modal-url');
        const target = $(this).data('modal-target') || '#dynamicModal';
        
        HercoAdmin.loadModal(url, target);
    });
};

// ===================================
// FORMULARIOS
// ===================================

HercoAdmin.initForms = function() {
    // Validación en tiempo real
    $(document).on('input change', '.form-control, .form-select', function() {
        HercoAdmin.validateField($(this));
    });
    
    // Submit con AJAX
    $(document).on('submit', 'form[data-ajax="true"]', function(e) {
        e.preventDefault();
        HercoAdmin.submitForm($(this));
    });
    
    // Auto-save
    $(document).on('input', '[data-autosave]', function() {
        HercoAdmin.scheduleAutosave($(this));
    });
    
    // Confirmación antes de enviar
    $(document).on('submit', 'form[data-confirm]', function(e) {
        const message = $(this).data('confirm');
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Character counter
    $(document).on('input', '[data-max-length]', function() {
        HercoAdmin.updateCharacterCounter($(this));
    });
};

// ===================================
// VALIDACIÓN DE CAMPOS
// ===================================

HercoAdmin.validateField = function($field) {
    const value = $field.val();
    const rules = $field.data('validate');
    
    if (!rules) return true;
    
    let isValid = true;
    let errorMessage = '';
    
    // Required
    if (rules.includes('required') && !value.trim()) {
        isValid = false;
        errorMessage = 'Este campo es obligatorio';
    }
    
    // Email
    else if (rules.includes('email') && value && !this.isValidEmail(value)) {
        isValid = false;
        errorMessage = 'Ingrese un email válido';
    }
    
    // Min length
    else if (rules.includes('min:')) {
        const minLength = parseInt(rules.match(/min:(\d+)/)[1]);
        if (value && value.length < minLength) {
            isValid = false;
            errorMessage = `Mínimo ${minLength} caracteres`;
        }
    }
    
    // Max length
    else if (rules.includes('max:')) {
        const maxLength = parseInt(rules.match(/max:(\d+)/)[1]);
        if (value && value.length > maxLength) {
            isValid = false;
            errorMessage = `Máximo ${maxLength} caracteres`;
        }
    }
    
    // Numeric
    else if (rules.includes('numeric') && value && isNaN(value)) {
        isValid = false;
        errorMessage = 'Debe ser un número';
    }
    
    // Actualizar UI
    if (isValid) {
        $field.removeClass('is-invalid').addClass('is-valid');
        $field.siblings('.invalid-feedback').remove();
    } else {
        $field.removeClass('is-valid').addClass('is-invalid');
        $field.siblings('.invalid-feedback').remove();
        $field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
    }
    
    return isValid;
};

// ===================================
// SUBMIT DE FORMULARIOS
// ===================================

HercoAdmin.submitForm = function($form) {
    const url = $form.attr('action');
    const method = $form.attr('method') || 'POST';
    const formData = new FormData($form[0]);
    
    // Validar formulario
    let isValid = true;
    $form.find('[data-validate]').each(function() {
        if (!HercoAdmin.validateField($(this))) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        HercoAdmin.showAlert('warning', 'Por favor corrija los errores en el formulario');
        return;
    }
    
    // Deshabilitar botón de submit
    const $submitBtn = $form.find('[type="submit"]');
    const originalText = $submitBtn.text();
    $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Procesando...');
    
    $.ajax({
        url: url,
        method: method,
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Mensaje de éxito
            if (response.message) {
                HercoAdmin.showAlert('success', response.message);
            }
            
            // Redirección
            if (response.redirect) {
                setTimeout(() => {
                    window.location.href = response.redirect;
                }, 1500);
            }
            
            // Callback personalizado
            if (response.callback && typeof window[response.callback] === 'function') {
                window[response.callback](response);
            }
            
            // Cerrar modal si está en uno
            $form.closest('.modal').modal('hide');
            
            // Reset del formulario
            if (response.reset !== false) {
                $form[0].reset();
            }
        },
        error: function(jqXHR) {
            // Los errores se manejan globalmente
        },
        complete: function() {
            // Rehabilitar botón
            $submitBtn.prop('disabled', false).text(originalText);
        }
    });
};

// ===================================
// TABLAS
// ===================================

HercoAdmin.initTables = function() {
    // Búsqueda en tabla
    $(document).on('keyup', '[data-table-search]', function() {
        const searchTerm = $(this).val().toLowerCase();
        const targetTable = $($(this).data('table-search'));
        
        targetTable.find('tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
        
        // Actualizar contador
        const visibleRows = targetTable.find('tbody tr:visible').length;
        $('[data-table-count]').text(visibleRows);
    });
    
    // Ordenamiento de tabla
    $(document).on('click', '.sortable', function() {
        HercoAdmin.sortTable($(this));
    });
    
    // Selección múltiple
    $(document).on('change', '.table-select-all', function() {
        const isChecked = $(this).prop('checked');
        $(this).closest('table').find('.table-select-row').prop('checked', isChecked);
        HercoAdmin.updateBulkActions();
    });
    
    $(document).on('change', '.table-select-row', function() {
        HercoAdmin.updateBulkActions();
    });
};

// ===================================
// ORDENAMIENTO DE TABLAS
// ===================================

HercoAdmin.sortTable = function($header) {
    const $table = $header.closest('table');
    const columnIndex = $header.index();
    const isAscending = !$header.hasClass('sort-asc');
    
    // Limpiar iconos de ordenamiento
    $table.find('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
    $table.find('.sortable').removeClass('sort-asc sort-desc');
    
    // Actualizar icono y clase
    $header.find('i').removeClass('fa-sort').addClass(isAscending ? 'fa-sort-up' : 'fa-sort-down');
    $header.addClass(isAscending ? 'sort-asc' : 'sort-desc');
    
    // Obtener filas
    const rows = $table.find('tbody tr').toArray();
    
    // Ordenar
    rows.sort((a, b) => {
        const aText = $(a).find('td').eq(columnIndex).text().trim();
        const bText = $(b).find('td').eq(columnIndex).text().trim();
        
        // Detectar si es número
        const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        } else {
            return isAscending ? 
                aText.localeCompare(bText) : 
                bText.localeCompare(aText);
        }
    });
    
    // Reordenar tabla
    $table.find('tbody').empty().append(rows);
};

// ===================================
// ACCIONES MASIVAS
// ===================================

HercoAdmin.updateBulkActions = function() {
    const selectedCount = $('.table-select-row:checked').length;
    const $bulkActions = $('.bulk-actions');
    
    if (selectedCount > 0) {
        $bulkActions.show();
        $bulkActions.find('.selected-count').text(selectedCount);
    } else {
        $bulkActions.hide();
    }
};

// ===================================
// FILE UPLOADS
// ===================================

HercoAdmin.initFileUploads = function() {
    // Drag and drop
    $(document).on('dragover dragenter', '.file-drop-zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });
    
    $(document).on('dragleave dragend drop', '.file-drop-zone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });
    
    $(document).on('drop', '.file-drop-zone', function(e) {
        const files = e.originalEvent.dataTransfer.files;
        const $input = $(this).find('input[type="file"]');
        
        if ($input.length && files.length) {
            $input[0].files = files;
            HercoAdmin.handleFileSelect($input[0]);
        }
    });
    
    // File input change
    $(document).on('change', 'input[type="file"][data-preview]', function() {
        HercoAdmin.handleFileSelect(this);
    });
};

// ===================================
// MANEJO DE ARCHIVOS
// ===================================

HercoAdmin.handleFileSelect = function(input) {
    const files = input.files;
    const $preview = $(input.data('preview'));
    
    if (!files.length || !$preview.length) return;
    
    $preview.empty();
    
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const isImage = file.type.startsWith('image/');
            let previewHtml;
            
            if (isImage) {
                previewHtml = `
                    <div class="file-preview-item">
                        <img src="${e.target.result}" alt="${file.name}" class="img-thumbnail">
                        <div class="file-info">
                            <small>${file.name}</small>
                            <small class="text-muted">${HercoAdmin.formatFileSize(file.size)}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-file" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            } else {
                previewHtml = `
                    <div class="file-preview-item">
                        <div class="file-icon">
                            <i class="fas fa-file"></i>
                        </div>
                        <div class="file-info">
                            <small>${file.name}</small>
                            <small class="text-muted">${HercoAdmin.formatFileSize(file.size)}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-file" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }
            
            $preview.append(previewHtml);
        };
        
        reader.readAsDataURL(file);
    });
};

// ===================================
// GRÁFICOS
// ===================================

HercoAdmin.initCharts = function() {
    // Configuración global de Chart.js
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6b7280';
    
    // Colores HERCO para gráficos
    this.chartColors = [
        '#2c5aa0', '#ff6b35', '#2ecc71', '#e74c3c', '#f39c12',
        '#9b59b6', '#1abc9c', '#34495e', '#e67e22', '#3498db'
    ];
    
    // Inicializar gráficos automáticamente
    $('[data-chart]').each(function() {
        const type = $(this).data('chart');
        const data = $(this).data('chart-data');
        
        if (data) {
            HercoAdmin.createChart(this, type, data);
        }
    });
};

// ===================================
// CREAR GRÁFICO
// ===================================

HercoAdmin.createChart = function(canvas, type, data, options = {}) {
    const ctx = canvas.getContext('2d');
    
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f3f4f6'
                }
            },
            x: {
                grid: {
                    color: '#f3f4f6'
                }
            }
        }
    };
    
    // Aplicar colores HERCO
    if (data.datasets) {
        data.datasets.forEach((dataset, index) => {
            if (!dataset.backgroundColor) {
                dataset.backgroundColor = this.chartColors[index % this.chartColors.length];
            }
            if (!dataset.borderColor) {
                dataset.borderColor = this.chartColors[index % this.chartColors.length];
            }
        });
    }
    
    const config = {
        type: type,
        data: data,
        options: $.extend(true, {}, defaultOptions, options)
    };
    
    return new Chart(ctx, config);
};

// ===================================
// SIDEBAR
// ===================================

HercoAdmin.initSidebar = function() {
    // Collapse/expand sidebar
    $(document).on('click', '.sidebar-toggle', function() {
        $('body').toggleClass('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', $('body').hasClass('sidebar-collapsed'));
    });
    
    // Restaurar estado del sidebar
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        $('body').addClass('sidebar-collapsed');
    }
    
    // Submenu toggle
    $(document).on('click', '.sidebar-nav .nav-link[data-toggle="collapse"]', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        $(target).collapse('toggle');
    });
};

// ===================================
// EVENTOS GLOBALES
// ===================================

HercoAdmin.setupGlobalEvents = function() {
    // Confirmación de acciones
    $(document).on('click', '[data-confirm]', function(e) {
        const message = $(this).data('confirm');
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-hide alerts
    $('.alert:not(.alert-permanent)').delay(5000).fadeOut(300);
    
    // Actualizar tiempo relativo
    setInterval(() => {
        $('[data-time]').each(function() {
            const timestamp = $(this).data('time');
            $(this).text(HercoAdmin.timeAgo(timestamp));
        });
    }, 60000);
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl + S para guardar
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('form:visible [type="submit"]').first().click();
        }
        
        // Escape para cerrar modales
        if (e.key === 'Escape') {
            $('.modal:visible').modal('hide');
        }
    });
};

// ===================================
// CARGAR DATOS INICIALES
// ===================================

HercoAdmin.loadInitialData = function() {
    // Cargar información del usuario
    this.loadUserInfo();
    
    // Cargar notificaciones
    this.loadNotifications();
    
    // Verificar sesión
    this.checkSession();
};

// ===================================
// TIMERS
// ===================================

HercoAdmin.startTimers = function() {
    // Notificaciones cada 30 segundos
    this.timers.notifications = setInterval(() => {
        this.loadNotifications();
    }, 30000);
    
    // Verificar sesión cada 5 minutos
    this.timers.sessionCheck = setInterval(() => {
        this.checkSession();
    }, 300000);
};

// ===================================
// UTILIDADES
// ===================================

// Mostrar loading
HercoAdmin.showLoading = function() {
    if (!this.state.isLoading) {
        $('#loadingOverlay').fadeIn(200);
        this.state.isLoading = true;
    }
};

// Ocultar loading
HercoAdmin.hideLoading = function() {
    if (this.state.isLoading) {
        $('#loadingOverlay').fadeOut(200);
        this.state.isLoading = false;
    }
};

// Mostrar alerta
HercoAdmin.showAlert = function(type, message, autoHide = true) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show ${autoHide ? '' : 'alert-permanent'}" role="alert">
            <i class="fas fa-${this.getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#systemAlerts').prepend(alertHtml);
    
    if (autoHide) {
        setTimeout(() => {
            $('#systemAlerts .alert:first').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
};

// Obtener icono para alerta
HercoAdmin.getAlertIcon = function(type) {
    const icons = {
        success: 'check-circle',
        danger: 'exclamation-triangle',
        warning: 'exclamation-circle',
        info: 'info-circle',
        primary: 'bell'
    };
    return icons[type] || 'info-circle';
};

// Validar email
HercoAdmin.isValidEmail = function(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

// Formatear tamaño de archivo
HercoAdmin.formatFileSize = function(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Tiempo transcurrido
HercoAdmin.timeAgo = function(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diffInSeconds = Math.floor((now - time) / 1000);
    
    const intervals = {
        año: 31536000,
        mes: 2592000,
        semana: 604800,
        día: 86400,
        hora: 3600,
        minuto: 60
    };
    
    for (let [unit, seconds] of Object.entries(intervals)) {
        const interval = Math.floor(diffInSeconds / seconds);
        if (interval >= 1) {
            return `hace ${interval} ${unit}${interval > 1 ? 's' : ''}`;
        }
    }
    
    return 'hace unos segundos';
};

// Cargar modal dinámico
HercoAdmin.loadModal = function(url, target) {
    $.get(url)
        .done(function(html) {
            $(target).html(html).modal('show');
        })
        .fail(function() {
            HercoAdmin.showAlert('danger', 'Error al cargar el modal');
        });
};

// Mostrar errores de validación
HercoAdmin.displayValidationErrors = function(errors) {
    Object.keys(errors).forEach(field => {
        const $field = $(`[name="${field}"]`);
        const message = errors[field][0];
        
        $field.addClass('is-invalid');
        $field.siblings('.invalid-feedback').remove();
        $field.after(`<div class="invalid-feedback">${message}</div>`);
    });
};

// Auto-save
HercoAdmin.scheduleAutosave = function($field) {
    if (this.timers.autosave) {
        clearTimeout(this.timers.autosave);
    }
    
    this.timers.autosave = setTimeout(() => {
        this.performAutosave($field);
    }, 2000);
};

HercoAdmin.performAutosave = function($field) {
    const form = $field.closest('form');
    const url = form.data('autosave-url');
    
    if (!url) return;
    
    const data = {
        field: $field.attr('name'),
        value: $field.val(),
        id: form.find('[name="id"]').val()
    };
    
    $.post(url, data)
        .done(function() {
            $field.addClass('autosave-success');
            setTimeout(() => $field.removeClass('autosave-success'), 1000);
        })
        .fail(function() {
            $field.addClass('autosave-error');
            setTimeout(() => $field.removeClass('autosave-error'), 1000);
        });
};

// Character counter
HercoAdmin.updateCharacterCounter = function($field) {
    const maxLength = parseInt($field.data('max-length'));
    const currentLength = $field.val().length;
    const remaining = maxLength - currentLength;
    
    let $counter = $field.siblings('.character-counter');
    if (!$counter.length) {
        $counter = $('<small class="character-counter text-muted"></small>');
        $field.after($counter);
    }
    
    $counter.text(`${currentLength}/${maxLength}`);
    
    if (remaining < 0) {
        $counter.removeClass('text-muted text-warning').addClass('text-danger');
    } else if (remaining < 20) {
        $counter.removeClass('text-muted text-danger').addClass('text-warning');
    } else {
        $counter.removeClass('text-warning text-danger').addClass('text-muted');
    }
};

// Cargar información del usuario
HercoAdmin.loadUserInfo = function() {
    // Implementar según necesidades
};

// Cargar notificaciones
HercoAdmin.loadNotifications = function() {
    $.get('/admin/notifications/count')
        .done(function(data) {
            const count = data.count || 0;
            const $badge = $('#notificationCount');
            
            if (count > 0) {
                $badge.text(count).show();
            } else {
                $badge.hide();
            }
        })
        .fail(function() {
            console.log('Error al cargar notificaciones');
        });
};

// Verificar sesión
HercoAdmin.checkSession = function() {
    $.get('/admin/session/check')
        .fail(function(jqXHR) {
            if (jqXHR.status === 401) {
                HercoAdmin.showAlert('warning', 'Su sesión ha expirado. Recargando página...');
                setTimeout(() => window.location.reload(), 2000);
            }
        });
};

// Exportar para uso global
window.HercoAdmin = HercoAdmin;