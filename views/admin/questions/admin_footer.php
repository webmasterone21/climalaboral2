</div>
    </div>
    
    <!-- JavaScript Files -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js for reports -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.min.js"></script>
    
    <!-- SortableJS for drag & drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <!-- Main admin JavaScript -->
    <script src="/assets/js/admin.js"></script>
    
    <!-- Page specific scripts -->
    <?php if (isset($scripts) && is_array($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="/assets/js/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline scripts -->
    <?php if (isset($inline_scripts) && !empty($inline_scripts)): ?>
        <script>
            <?= $inline_scripts ?>
        </script>
    <?php endif; ?>
    
    <!-- Common utility functions -->
    <script>
        // Global configuration
        window.AppConfig = {
            baseUrl: '<?= BASE_URL ?>',
            csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>',
            userId: '<?= $_SESSION['user_id'] ?? '' ?>',
            userRole: '<?= $_SESSION['role'] ?? '' ?>',
            language: 'es'
        };
        
        // Toast notification system
        function showToast(message, type = 'info', duration = 5000) {
            const toastContainer = document.getElementById('toast-container');
            const toastId = 'toast-' + Date.now();
            
            const toastHTML = `
                <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${duration}">
                    <div class="toast-header">
                        <i class="fas ${getToastIcon(type)} me-2 text-${type}"></i>
                        <strong class="me-auto">Sistema de Encuestas</strong>
                        <small>${new Date().toLocaleTimeString()}</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            const toast = new bootstrap.Toast(document.getElementById(toastId));
            toast.show();
            
            // Auto remove toast after it's hidden
            document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        }
        
        function getToastIcon(type) {
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle',
                'primary': 'fa-info-circle',
                'secondary': 'fa-info-circle',
                'danger': 'fa-exclamation-circle'
            };
            return icons[type] || 'fa-info-circle';
        }
        
        // Global alert function (compatible with existing code)
        function showAlert(message, type = 'info', duration = 5000) {
            showToast(message, type, duration);
        }
        
        // Loading overlay functions
        function showLoading(message = 'Procesando...') {
            const overlay = document.getElementById('loading-overlay');
            const loadingText = overlay.querySelector('.loading-text');
            
            if (loadingText) {
                loadingText.textContent = message;
            }
            
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }
        
        // AJAX utility function with CSRF protection
        async function apiCall(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.AppConfig.csrfToken
                }
            };
            
            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...options.headers
                }
            };
            
            try {
                const response = await fetch(url, mergedOptions);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('API call failed:', error);
                throw error;
            }
        }
        
        // Confirmation dialog utility
        function confirmAction(message, callback, title = 'Confirmar acción') {
            const modalId = 'confirm-modal-' + Date.now();
            const modalHTML = `
                <div class="modal fade" id="${modalId}" tabindex="-1">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-danger" id="${modalId}-confirm">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            const modal = new bootstrap.Modal(document.getElementById(modalId));
            const confirmBtn = document.getElementById(modalId + '-confirm');
            
            confirmBtn.addEventListener('click', function() {
                modal.hide();
                callback();
            });
            
            // Clean up modal after it's hidden
            document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
            
            modal.show();
        }
        
        // Format numbers for display
        function formatNumber(number, decimals = 0) {
            return new Intl.NumberFormat('es-ES', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        }
        
        // Format percentage
        function formatPercentage(value, decimals = 1) {
            return new Intl.NumberFormat('es-ES', {
                style: 'percent',
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(value / 100);
        }
        
        // Format date
        function formatDate(dateString, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            
            const mergedOptions = { ...defaultOptions, ...options };
            
            return new Intl.DateTimeFormat('es-ES', mergedOptions).format(new Date(dateString));
        }
        
        // Auto-resize textareas
        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
        
        // Initialize auto-resize for all textareas
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('textarea[data-auto-resize]');
            textareas.forEach(textarea => {
                textarea.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });
                
                // Initial resize
                autoResizeTextarea(textarea);
            });
        });
        
        // Table utilities
        function initDataTable(tableId, options = {}) {
            const table = document.getElementById(tableId);
            if (!table) return;
            
            const defaultOptions = {
                searchable: true,
                sortable: true,
                pagination: true,
                pageSize: 10
            };
            
            const mergedOptions = { ...defaultOptions, ...options };
            
            // Add search functionality
            if (mergedOptions.searchable) {
                const searchInput = table.querySelector('.table-search');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        filterTable(table, this.value);
                    });
                }
            }
            
            // Add sorting functionality
            if (mergedOptions.sortable) {
                const headers = table.querySelectorAll('th[data-sortable]');
                headers.forEach(header => {
                    header.style.cursor = 'pointer';
                    header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
                    
                    header.addEventListener('click', function() {
                        sortTable(table, this.cellIndex, this.dataset.type || 'string');
                    });
                });
            }
        }
        
        function filterTable(table, searchTerm) {
            const tbody = table.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const match = text.includes(searchTerm.toLowerCase());
                row.style.display = match ? '' : 'none';
            });
        }
        
        function sortTable(table, columnIndex, dataType = 'string') {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                let aVal = a.cells[columnIndex].textContent.trim();
                let bVal = b.cells[columnIndex].textContent.trim();
                
                if (dataType === 'number') {
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                } else if (dataType === 'date') {
                    aVal = new Date(aVal);
                    bVal = new Date(bVal);
                }
                
                return aVal > bVal ? 1 : -1;
            });
            
            rows.forEach(row => tbody.appendChild(row));
        }
        
        // Form validation utilities
        function validateForm(form) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else if (field.value) {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // File upload utilities
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Copy to clipboard
        async function copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                showToast('Copiado al portapapeles', 'success', 2000);
            } catch (err) {
                console.error('Failed to copy to clipboard:', err);
                showToast('Error al copiar al portapapeles', 'error');
            }
        }
        
        // Auto-save functionality
        class AutoSave {
            constructor(formId, saveUrl, interval = 30000) {
                this.form = document.getElementById(formId);
                this.saveUrl = saveUrl;
                this.interval = interval;
                this.timeoutId = null;
                this.lastSaveData = null;
                
                if (this.form) {
                    this.init();
                }
            }
            
            init() {
                this.form.addEventListener('input', () => {
                    clearTimeout(this.timeoutId);
                    this.timeoutId = setTimeout(() => {
                        this.save();
                    }, this.interval);
                });
                
                // Save before page unload
                window.addEventListener('beforeunload', () => {
                    this.save();
                });
            }
            
            async save() {
                const formData = new FormData(this.form);
                const data = Object.fromEntries(formData.entries());
                const jsonData = JSON.stringify(data);
                
                // Check if data has changed
                if (jsonData === this.lastSaveData) {
                    return;
                }
                
                try {
                    await apiCall(this.saveUrl, {
                        method: 'POST',
                        body: jsonData
                    });
                    
                    this.lastSaveData = jsonData;
                    this.showSaveIndicator();
                } catch (error) {
                    console.error('Auto-save failed:', error);
                }
            }
            
            showSaveIndicator() {
                const indicator = document.querySelector('.auto-save-indicator');
                if (indicator) {
                    indicator.classList.add('show');
                    setTimeout(() => {
                        indicator.classList.remove('show');
                    }, 2000);
                }
            }
        }
        
        // Initialize common functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Add loading state to buttons with data-loading attribute
            const loadingButtons = document.querySelectorAll('[data-loading]');
            loadingButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.disabled) {
                        this.disabled = true;
                        const originalText = this.innerHTML;
                        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Procesando...';
                        
                        setTimeout(() => {
                            this.disabled = false;
                            this.innerHTML = originalText;
                        }, 3000);
                    }
                });
            });
            
            // Auto-focus first input in modals
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    const firstInput = this.querySelector('input, select, textarea');
                    if (firstInput) {
                        firstInput.focus();
                    }
                });
            });
            
            // Confirm delete actions
            const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const message = this.dataset.confirmDelete || '¿Estás seguro de que deseas eliminar este elemento?';
                    const href = this.href || this.dataset.href;
                    
                    confirmAction(message, () => {
                        if (href) {
                            window.location.href = href;
                        } else {
                            const form = this.closest('form');
                            if (form) {
                                form.submit();
                            }
                        }
                    }, 'Confirmar eliminación');
                });
            });
        });
    </script>
    
    <!-- Development mode scripts -->
    <?php if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE): ?>
        <script>
            // Development utilities
            console.log('Sistema de Encuestas - Development Mode');
            console.log('App Config:', window.AppConfig);
            
            // Live reload (if needed)
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                setInterval(() => {
                    fetch('/api/dev/ping').catch(() => {
                        // Development server might be down, reload page
                        location.reload();
                    });
                }, 30000);
            }
        </script>
    <?php endif; ?>
    
</body>
</html>