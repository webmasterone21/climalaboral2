/**
 * SISTEMA DE NOTIFICACIONES - HERCO v2.0
 * 
 * Sistema completo de notificaciones en tiempo real
 * Incluye toast, push notifications, y gestión de estado
 * 
 * @package HERCO\Assets
 * @version 2.0
 * @author Sistema HERCO
 */

// ===================================
// CONFIGURACIÓN DEL SISTEMA
// ===================================

const HercoNotifications = {
    // Configuración
    config: {
        apiUrl: '/admin/notifications',
        checkInterval: 30000, // 30 segundos
        maxNotifications: 50,
        defaultDuration: 5000,
        sounds: {
            success: '/assets/sounds/success.mp3',
            error: '/assets/sounds/error.mp3',
            warning: '/assets/sounds/warning.mp3',
            info: '/assets/sounds/info.mp3'
        },
        enableSounds: true,
        enablePush: true,
        enableDesktop: true
    },
    
    // Estado
    state: {
        notifications: [],
        unreadCount: 0,
        isInitialized: false,
        lastCheck: null,
        isOnline: navigator.onLine,
        subscription: null
    },
    
    // Elementos DOM
    elements: {
        container: null,
        dropdown: null,
        badge: null,
        list: null
    },
    
    // Timers
    timers: {
        check: null,
        cleanup: null
    }
};

// ===================================
// INICIALIZACIÓN
// ===================================

$(document).ready(function() {
    HercoNotifications.init();
});

HercoNotifications.init = function() {
    console.log('🔔 Inicializando sistema de notificaciones HERCO...');
    
    // Inicializar elementos DOM
    this.initElements();
    
    // Configurar eventos
    this.setupEvents();
    
    // Solicitar permisos
    this.requestPermissions();
    
    // Cargar notificaciones iniciales
    this.loadNotifications();
    
    // Iniciar timers
    this.startTimers();
    
    // Configurar Service Worker para push notifications
    this.setupServiceWorker();
    
    this.state.isInitialized = true;
    console.log('✅ Sistema de notificaciones inicializado');
};

// ===================================
// INICIALIZAR ELEMENTOS DOM
// ===================================

HercoNotifications.initElements = function() {
    this.elements.container = $('#notificationContainer') || this.createContainer();
    this.elements.dropdown = $('#notificationDropdown');
    this.elements.badge = $('#notificationCount');
    this.elements.list = $('#notificationList') || this.elements.dropdown.find('.notification-list');
    
    // Crear contenedor de toast si no existe
    if (!$('#toastContainer').length) {
        $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>');
    }
};

// ===================================
// CREAR CONTENEDOR
// ===================================

HercoNotifications.createContainer = function() {
    const containerHtml = `
        <div id="notificationContainer" class="notification-system">
            <!-- Toast container -->
            <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>
            
            <!-- Dropdown de notificaciones -->
            <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="notificationDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notificaciones</h6>
                    <div class="notification-actions">
                        <button class="btn btn-sm btn-link text-primary" onclick="HercoNotifications.markAllAsRead()">
                            <i class="fas fa-check-double"></i> Marcar todas
                        </button>
                        <button class="btn btn-sm btn-link text-secondary" onclick="HercoNotifications.clearAll()">
                            <i class="fas fa-trash"></i> Limpiar
                        </button>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <div class="notification-list" id="notificationList">
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No hay notificaciones
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-item text-center">
                    <a href="/admin/notifications" class="text-decoration-none">Ver todas las notificaciones</a>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(containerHtml);
    return $('#notificationContainer');
};

// ===================================
// CONFIGURAR EVENTOS
// ===================================

HercoNotifications.setupEvents = function() {
    // Evento de conexión/desconexión
    window.addEventListener('online', () => {
        this.state.isOnline = true;
        this.loadNotifications();
        this.show('info', 'Conexión restaurada', 'Se reanudó la sincronización de notificaciones');
    });
    
    window.addEventListener('offline', () => {
        this.state.isOnline = false;
        this.show('warning', 'Sin conexión', 'Las notificaciones se pausarán hasta reconectar');
    });
    
    // Click en notificación
    $(document).on('click', '.notification-item', function() {
        const id = $(this).data('id');
        HercoNotifications.markAsRead(id);
        
        // Redirigir si tiene URL
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        }
    });
    
    // Botón de cerrar notificación
    $(document).on('click', '.notification-close', function(e) {
        e.stopPropagation();
        const id = $(this).closest('.notification-item').data('id');
        HercoNotifications.dismiss(id);
    });
    
    // Actualizar badge cuando se abre el dropdown
    $(document).on('shown.bs.dropdown', '.notification-toggle', function() {
        HercoNotifications.loadNotifications();
    });
};

// ===================================
// SOLICITAR PERMISOS
// ===================================

HercoNotifications.requestPermissions = function() {
    // Notificaciones del navegador
    if ('Notification' in window && this.config.enableDesktop) {
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('Permiso de notificaciones:', permission);
            });
        }
    }
    
    // Permisos de sonido (automático al reproducir)
    if (this.config.enableSounds) {
        // Los navegadores modernos requieren interacción del usuario
        $(document).one('click', () => {
            this.testSound('info');
        });
    }
};

// ===================================
// CONFIGURAR SERVICE WORKER
// ===================================

HercoNotifications.setupServiceWorker = function() {
    if ('serviceWorker' in navigator && this.config.enablePush) {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('Service Worker registrado:', registration);
                return this.subscribeToPush(registration);
            })
            .catch(error => {
                console.error('Error registrando Service Worker:', error);
            });
    }
};

// ===================================
// SUSCRIBIRSE A PUSH NOTIFICATIONS
// ===================================

HercoNotifications.subscribeToPush = function(registration) {
    const vapidPublicKey = $('meta[name="vapid-public-key"]').attr('content');
    
    if (!vapidPublicKey) {
        console.warn('VAPID public key no encontrada');
        return;
    }
    
    return registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey)
    }).then(subscription => {
        this.state.subscription = subscription;
        
        // Enviar suscripción al servidor
        return $.post('/admin/notifications/subscribe', {
            subscription: JSON.stringify(subscription)
        });
    }).catch(error => {
        console.error('Error suscribiéndose a push notifications:', error);
    });
};

// ===================================
// CARGAR NOTIFICACIONES
// ===================================

HercoNotifications.loadNotifications = function() {
    if (!this.state.isOnline) return;
    
    const lastCheck = this.state.lastCheck || new Date(0).toISOString();
    
    $.get(`${this.config.apiUrl}?since=${lastCheck}`)
        .done(response => {
            this.processNotifications(response.notifications || []);
            this.updateBadge(response.unread_count || 0);
            this.state.lastCheck = new Date().toISOString();
        })
        .fail(error => {
            console.error('Error cargando notificaciones:', error);
        });
};

// ===================================
// PROCESAR NOTIFICACIONES
// ===================================

HercoNotifications.processNotifications = function(notifications) {
    notifications.forEach(notification => {
        // Verificar si es nueva
        const exists = this.state.notifications.find(n => n.id === notification.id);
        
        if (!exists) {
            // Es una notificación nueva
            this.state.notifications.unshift(notification);
            
            // Mostrar toast si no está leída
            if (!notification.read_at) {
                this.showToast(notification);
                
                // Reproducir sonido
                if (this.config.enableSounds) {
                    this.playSound(notification.type || 'info');
                }
                
                // Mostrar notificación del navegador
                if (this.config.enableDesktop && document.hidden) {
                    this.showDesktopNotification(notification);
                }
            }
        }
    });
    
    // Limitar cantidad de notificaciones en memoria
    if (this.state.notifications.length > this.config.maxNotifications) {
        this.state.notifications = this.state.notifications.slice(0, this.config.maxNotifications);
    }
    
    // Actualizar lista en dropdown
    this.updateNotificationList();
};

// ===================================
// MOSTRAR TOAST
// ===================================

HercoNotifications.showToast = function(notification) {
    const toastId = `toast-${notification.id || Date.now()}`;
    const typeClass = this.getTypeClass(notification.type);
    const icon = this.getTypeIcon(notification.type);
    
    const toastHtml = `
        <div class="toast align-items-center border-0 ${typeClass}" role="alert" id="${toastId}" data-bs-autohide="true" data-bs-delay="${this.config.defaultDuration}">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="fas ${icon} me-2"></i>
                    <div>
                        <div class="fw-bold">${this.escapeHtml(notification.title || notification.data?.title || 'Notificación')}</div>
                        <div class="text-muted small">${this.escapeHtml(notification.message || notification.data?.message || '')}</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('#toastContainer').append(toastHtml);
    
    const toast = new bootstrap.Toast(document.getElementById(toastId));
    toast.show();
    
    // Limpiar después de ocultar
    $(`#${toastId}`).on('hidden.bs.toast', function() {
        $(this).remove();
    });
};

// ===================================
// MOSTRAR NOTIFICACIÓN DEL NAVEGADOR
// ===================================

HercoNotifications.showDesktopNotification = function(notification) {
    if (Notification.permission !== 'granted') return;
    
    const options = {
        body: notification.message || notification.data?.message || '',
        icon: '/assets/images/logo-icon.png',
        badge: '/assets/images/notification-badge.png',
        tag: notification.id || 'herco-notification',
        requireInteraction: false,
        silent: false
    };
    
    const desktopNotification = new Notification(
        notification.title || notification.data?.title || 'HERCO - Clima Laboral',
        options
    );
    
    // Manejar click
    desktopNotification.onclick = function() {
        window.focus();
        
        if (notification.data?.url) {
            window.location.href = notification.data.url;
        }
        
        HercoNotifications.markAsRead(notification.id);
        desktopNotification.close();
    };
    
    // Auto cerrar después de 10 segundos
    setTimeout(() => {
        desktopNotification.close();
    }, 10000);
};

// ===================================
// ACTUALIZAR LISTA DE NOTIFICACIONES
// ===================================

HercoNotifications.updateNotificationList = function() {
    const $list = this.elements.list;
    
    if (!this.state.notifications.length) {
        $list.html(`
            <div class="text-center text-muted py-3">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                No hay notificaciones
            </div>
        `);
        return;
    }
    
    let html = '';
    
    this.state.notifications.slice(0, 10).forEach(notification => {
        const isRead = !!notification.read_at;
        const icon = this.getTypeIcon(notification.type);
        const timeAgo = this.timeAgo(notification.created_at);
        
        html += `
            <div class="notification-item dropdown-item d-flex align-items-start p-3 ${isRead ? 'read' : 'unread'}" 
                 data-id="${notification.id}" 
                 data-url="${notification.data?.url || ''}"
                 style="cursor: pointer; border-left: 3px solid ${this.getTypeColor(notification.type)};">
                
                <div class="notification-icon me-3 mt-1">
                    <i class="fas ${icon} text-${notification.type || 'primary'}"></i>
                </div>
                
                <div class="notification-content flex-grow-1">
                    <div class="notification-title fw-bold ${isRead ? 'text-muted' : 'text-dark'}">
                        ${this.escapeHtml(notification.title || notification.data?.title || 'Notificación')}
                    </div>
                    <div class="notification-message text-muted small mb-1">
                        ${this.escapeHtml(notification.message || notification.data?.message || '')}
                    </div>
                    <div class="notification-time text-muted" style="font-size: 0.75rem;">
                        <i class="fas fa-clock me-1"></i>
                        ${timeAgo}
                    </div>
                </div>
                
                <div class="notification-actions">
                    ${!isRead ? '<span class="badge bg-primary rounded-pill">•</span>' : ''}
                    <button class="btn btn-sm btn-link text-muted notification-close" title="Descartar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    $list.html(html);
};

// ===================================
// ACTUALIZAR BADGE
// ===================================

HercoNotifications.updateBadge = function(count) {
    this.state.unreadCount = count;
    
    if (count > 0) {
        this.elements.badge.text(count > 99 ? '99+' : count).show();
        
        // Actualizar título del documento
        const originalTitle = document.title.replace(/^\(\d+\)\s/, '');
        document.title = `(${count}) ${originalTitle}`;
    } else {
        this.elements.badge.hide();
        
        // Restaurar título original
        document.title = document.title.replace(/^\(\d+\)\s/, '');
    }
};

// ===================================
// MARCAR COMO LEÍDA
// ===================================

HercoNotifications.markAsRead = function(id) {
    $.post(`${this.config.apiUrl}/${id}/read`)
        .done(() => {
            // Actualizar estado local
            const notification = this.state.notifications.find(n => n.id === id);
            if (notification) {
                notification.read_at = new Date().toISOString();
            }
            
            // Actualizar UI
            $(`.notification-item[data-id="${id}"]`).addClass('read').removeClass('unread');
            $(`.notification-item[data-id="${id}"] .badge`).remove();
            
            // Decrementar contador
            this.updateBadge(Math.max(0, this.state.unreadCount - 1));
        })
        .fail(error => {
            console.error('Error marcando notificación como leída:', error);
        });
};

// ===================================
// MARCAR TODAS COMO LEÍDAS
// ===================================

HercoNotifications.markAllAsRead = function() {
    $.post(`${this.config.apiUrl}/mark-all-read`)
        .done(() => {
            // Actualizar estado local
            this.state.notifications.forEach(notification => {
                notification.read_at = new Date().toISOString();
            });
            
            // Actualizar UI
            $('.notification-item').addClass('read').removeClass('unread');
            $('.notification-item .badge').remove();
            
            // Resetear contador
            this.updateBadge(0);
            
            this.show('success', 'Notificaciones marcadas', 'Todas las notificaciones han sido marcadas como leídas');
        })
        .fail(error => {
            console.error('Error marcando todas como leídas:', error);
            this.show('error', 'Error', 'No se pudieron marcar las notificaciones como leídas');
        });
};

// ===================================
// DESCARTAR NOTIFICACIÓN
// ===================================

HercoNotifications.dismiss = function(id) {
    $.delete(`${this.config.apiUrl}/${id}`)
        .done(() => {
            // Remover del estado local
            this.state.notifications = this.state.notifications.filter(n => n.id !== id);
            
            // Remover de UI
            $(`.notification-item[data-id="${id}"]`).fadeOut(300, function() {
                $(this).remove();
                HercoNotifications.updateNotificationList();
            });
            
            // Actualizar contador si no estaba leída
            const wasUnread = !$(`.notification-item[data-id="${id}"]`).hasClass('read');
            if (wasUnread) {
                this.updateBadge(Math.max(0, this.state.unreadCount - 1));
            }
        })
        .fail(error => {
            console.error('Error descartando notificación:', error);
        });
};

// ===================================
// LIMPIAR TODAS
// ===================================

HercoNotifications.clearAll = function() {
    if (!confirm('¿Está seguro que desea eliminar todas las notificaciones?')) {
        return;
    }
    
    $.post(`${this.config.apiUrl}/clear-all`)
        .done(() => {
            this.state.notifications = [];
            this.updateBadge(0);
            this.updateNotificationList();
            
            this.show('success', 'Notificaciones eliminadas', 'Todas las notificaciones han sido eliminadas');
        })
        .fail(error => {
            console.error('Error limpiando notificaciones:', error);
            this.show('error', 'Error', 'No se pudieron eliminar las notificaciones');
        });
};

// ===================================
// INICIAR TIMERS
// ===================================

HercoNotifications.startTimers = function() {
    // Timer principal de verificación
    this.timers.check = setInterval(() => {
        if (this.state.isOnline) {
            this.loadNotifications();
        }
    }, this.config.checkInterval);
    
    // Timer de limpieza (cada 5 minutos)
    this.timers.cleanup = setInterval(() => {
        this.cleanup();
    }, 300000);
};

// ===================================
// LIMPIEZA
// ===================================

HercoNotifications.cleanup = function() {
    // Remover toasts antiguos
    $('.toast').each(function() {
        if (!$(this).hasClass('show')) {
            $(this).remove();
        }
    });
    
    // Limitar notificaciones en memoria
    if (this.state.notifications.length > this.config.maxNotifications) {
        this.state.notifications = this.state.notifications.slice(0, this.config.maxNotifications);
        this.updateNotificationList();
    }
};

// ===================================
// API PÚBLICA PARA MOSTRAR NOTIFICACIONES
// ===================================

HercoNotifications.show = function(type, title, message, options = {}) {
    const notification = {
        id: Date.now(),
        type: type,
        title: title,
        message: message,
        data: options,
        created_at: new Date().toISOString(),
        read_at: null
    };
    
    this.showToast(notification);
    
    if (options.sound !== false && this.config.enableSounds) {
        this.playSound(type);
    }
    
    if (options.desktop && this.config.enableDesktop && document.hidden) {
        this.showDesktopNotification(notification);
    }
};

// Métodos de conveniencia
HercoNotifications.success = function(title, message, options = {}) {
    this.show('success', title, message, options);
};

HercoNotifications.error = function(title, message, options = {}) {
    this.show('danger', title, message, options);
};

HercoNotifications.warning = function(title, message, options = {}) {
    this.show('warning', title, message, options);
};

HercoNotifications.info = function(title, message, options = {}) {
    this.show('info', title, message, options);
};

// ===================================
// REPRODUCIR SONIDO
// ===================================

HercoNotifications.playSound = function(type) {
    if (!this.config.enableSounds) return;
    
    const soundUrl = this.config.sounds[type] || this.config.sounds.info;
    
    try {
        const audio = new Audio(soundUrl);
        audio.volume = 0.3;
        audio.play().catch(error => {
            console.log('No se pudo reproducir sonido:', error);
        });
    } catch (error) {
        console.log('Error reproduciendo sonido:', error);
    }
};

HercoNotifications.testSound = function(type) {
    this.playSound(type);
};

// ===================================
// UTILIDADES
// ===================================

HercoNotifications.getTypeClass = function(type) {
    const classes = {
        success: 'text-bg-success',
        danger: 'text-bg-danger',
        error: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-info',
        primary: 'text-bg-primary'
    };
    return classes[type] || classes.info;
};

HercoNotifications.getTypeIcon = function(type) {
    const icons = {
        success: 'fa-check-circle',
        danger: 'fa-exclamation-triangle',
        error: 'fa-exclamation-triangle',
        warning: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        primary: 'fa-bell'
    };
    return icons[type] || icons.info;
};

HercoNotifications.getTypeColor = function(type) {
    const colors = {
        success: '#28a745',
        danger: '#dc3545',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8',
        primary: '#2c5aa0'
    };
    return colors[type] || colors.info;
};

HercoNotifications.timeAgo = function(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diffInSeconds = Math.floor((now - time) / 1000);
    
    if (diffInSeconds < 60) return 'hace unos segundos';
    if (diffInSeconds < 3600) return `hace ${Math.floor(diffInSeconds / 60)} min`;
    if (diffInSeconds < 86400) return `hace ${Math.floor(diffInSeconds / 3600)} h`;
    if (diffInSeconds < 604800) return `hace ${Math.floor(diffInSeconds / 86400)} días`;
    
    return time.toLocaleDateString('es-ES', { 
        day: 'numeric', 
        month: 'short',
        year: time.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
};

HercoNotifications.escapeHtml = function(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

HercoNotifications.urlBase64ToUint8Array = function(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
    
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
};

// ===================================
// CONFIGURACIÓN DINÁMICA
// ===================================

HercoNotifications.configure = function(options) {
    Object.assign(this.config, options);
    
    // Reiniciar timers si cambió el intervalo
    if (options.checkInterval && this.timers.check) {
        clearInterval(this.timers.check);
        this.timers.check = setInterval(() => {
            if (this.state.isOnline) {
                this.loadNotifications();
            }
        }, this.config.checkInterval);
    }
};

// ===================================
// DESTRUCTOR
// ===================================

HercoNotifications.destroy = function() {
    // Limpiar timers
    Object.values(this.timers).forEach(timer => {
        if (timer) clearInterval(timer);
    });
    
    // Limpiar eventos
    $(document).off('.herco-notifications');
    window.removeEventListener('online');
    window.removeEventListener('offline');
    
    // Limpiar estado
    this.state.notifications = [];
    this.state.isInitialized = false;
    
    console.log('🔔 Sistema de notificaciones destruido');
};

// Exportar para uso global
window.HercoNotifications = HercoNotifications;

// Plugin jQuery para facilidad de uso
$.fn.hercoNotify = function(type, message, options) {
    HercoNotifications.show(type, '', message, options);
    return this;
};

// Aliases globales para facilidad de uso
window.notify = {
    success: (title, message, options) => HercoNotifications.success(title, message, options),
    error: (title, message, options) => HercoNotifications.error(title, message, options),
    warning: (title, message, options) => HercoNotifications.warning(title, message, options),
    info: (title, message, options) => HercoNotifications.info(title, message, options)
};