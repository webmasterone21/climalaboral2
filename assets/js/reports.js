/**
 * Sistema de Reportes - Visualización de Datos
 */

class ReportsManager {
    constructor() {
        this.charts = {};
        this.init();
    }
    
    init() {
        this.initializeCategoryChart();
        this.setupEventListeners();
        this.loadChartData();
    }
    
    initializeCategoryChart() {
        const ctx = document.getElementById('categoryChart');
        if (!ctx) return;
        
        this.charts.categoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Puntuación Promedio',
                    data: [],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(50, 205, 50, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 99, 255, 1)',
                        'rgba(50, 205, 50, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Puntuación Promedio por Categoría',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Promedio: ${context.parsed.y.toFixed(2)}/5.00`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 0.5,
                            callback: function(value) {
                                return value.toFixed(1);
                            }
                        },
                        title: {
                            display: true,
                            text: 'Puntuación (1-5)'
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const categoryName = this.charts.categoryChart.data.labels[index];
                        this.showCategoryDetails(categoryName);
                    }
                }
            }
        });
    }
    
    setupEventListeners() {
        // Botón de actualizar datos
        const refreshBtn = document.querySelector('[onclick="refreshData()"]');
        if (refreshBtn) {
            refreshBtn.onclick = (e) => {
                e.preventDefault();
                this.refreshData();
            };
        }
        
        // Botón de compartir
        const shareBtn = document.querySelector('[onclick="shareReport()"]');
        if (shareBtn) {
            shareBtn.onclick = (e) => {
                e.preventDefault();
                this.shareReport();
            };
        }
        
        // Enlaces de exportación
        this.setupExportHandlers();
    }
    
    setupExportHandlers() {
        const exportLinks = document.querySelectorAll('a[href*="/export/"]');
        exportLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                this.showExportProgress();
            });
        });
    }
    
    async loadChartData() {
        try {
            this.showLoadingIndicator();
            
            const response = await fetch(`${baseUrl}api/chart-data/${surveyId}/category_bars`);
            const result = await response.json();
            
            if (result.success && result.data) {
                this.updateCategoryChart(result.data);
            }
            
            this.hideLoadingIndicator();
        } catch (error) {
            console.error('Error loading chart data:', error);
            this.showError('Error al cargar los datos del gráfico');
        }
    }
    
    updateCategoryChart(data) {
        if (!this.charts.categoryChart) return;
        
        this.charts.categoryChart.data.labels = data.labels;
        this.charts.categoryChart.data.datasets[0].data = data.datasets[0].data;
        this.charts.categoryChart.update('active');
    }
    
    async refreshData() {
        this.showLoadingIndicator('Actualizando datos...');
        
        try {
            await this.loadChartData();
            this.updateProgressBars();
            this.showSuccess('Datos actualizados correctamente');
        } catch (error) {
            this.showError('Error al actualizar los datos');
        }
    }
    
    updateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    }
    
    shareReport() {
        const shareData = {
            title: document.querySelector('h2').textContent,
            text: 'Reporte de Clima Laboral generado con nuestro sistema',
            url: window.location.href
        };
        
        if (navigator.share) {
            navigator.share(shareData);
        } else {
            // Fallback: copiar URL al portapapeles
            navigator.clipboard.writeText(window.location.href).then(() => {
                this.showSuccess('Enlace copiado al portapapeles');
            });
        }
    }
    
    showCategoryDetails(categoryName) {
        const modal = this.createModal('Detalles de Categoría', `
            <div class="category-details">
                <h5>${categoryName}</h5>
                <p>Cargando detalles...</p>
            </div>
        `);
        
        modal.show();
        
        // Cargar detalles de la categoría
        this.loadCategoryDetails(categoryName, modal);
    }
    
    async loadCategoryDetails(categoryName, modal) {
        try {
            // Aquí podrías hacer una llamada AJAX para obtener más detalles
            const content = `
                <div class="category-details">
                    <h5>${categoryName}</h5>
                    <p>Esta funcionalidad se puede expandir para mostrar:</p>
                    <ul>
                        <li>Preguntas específicas de la categoría</li>
                        <li>Distribución detallada de respuestas</li>
                        <li>Comparación con otras categorías</li>
                        <li>Recomendaciones específicas</li>
                    </ul>
                </div>
            `;
            
            modal._element.querySelector('.modal-body').innerHTML = content;
        } catch (error) {
            console.error('Error loading category details:', error);
        }
    }
    
    showExportProgress() {
        const progressModal = this.createModal('Generando Reporte', `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3">Generando su reporte, por favor espere...</p>
            </div>
        `);
        
        progressModal.show();
        
        // Cerrar modal después de 3 segundos
        setTimeout(() => {
            progressModal.hide();
        }, 3000);
    }
    
    createModal(title, content) {
        const modalId = 'dynamicModal' + Date.now();
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modalElement = document.getElementById(modalId);
        
        // Limpiar modal cuando se cierre
        modalElement.addEventListener('hidden.bs.modal', () => {
            modalElement.remove();
        });
        
        return new bootstrap.Modal(modalElement);
    }
    
    showLoadingIndicator(message = 'Cargando...') {
        const indicator = document.createElement('div');
        indicator.id = 'loadingIndicator';
        indicator.className = 'loading-overlay';
        indicator.innerHTML = `
            <div class="loading-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">${message}</p>
            </div>
        `;
        
        document.body.appendChild(indicator);
    }
    
    hideLoadingIndicator() {
        const indicator = document.getElementById('loadingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    showSuccess(message) {
        this.showAlert('success', message);
    }
    
    showError(message) {
        this.showAlert('danger', message);
    }
    
    showAlert(type, message) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto-dismiss después de 5 segundos
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
}

// Funciones globales para compatibilidad
function refreshData() {
    if (window.reportsManager) {
        window.reportsManager.refreshData();
    }
}

function shareReport() {
    if (window.reportsManager) {
        window.reportsManager.shareReport();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.reportsManager = new ReportsManager();
});