<?php
// views/admin/reports/trends.php - Análisis de tendencias en el tiempo
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Análisis de Tendencias</h1>
                        <p class="text-muted">Evolución del clima laboral a través del tiempo</p>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item active">Tendencias</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar"></i> Período
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item period-selector" data-period="6months">Últimos 6 meses</a>
                                <a class="dropdown-item period-selector" data-period="1year">Último año</a>
                                <a class="dropdown-item period-selector" data-period="2years">Últimos 2 años</a>
                                <a class="dropdown-item period-selector" data-period="all">Todo el historial</a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success" onclick="exportTrendsReport()">
                            <i class="fas fa-download"></i> Exportar Análisis
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuración del análisis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog text-primary"></i>
                        Configuración del Análisis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="company-select">Empresa</label>
                                <select id="company-select" class="form-control">
                                    <option value="">Todas las empresas</option>
                                    <?php if (isset($companies)): ?>
                                        <?php foreach ($companies as $company): ?>
                                            <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="department-select">Departamento</label>
                                <select id="department-select" class="form-control">
                                    <option value="">Todos los departamentos</option>
                                    <?php if (isset($departments)): ?>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="metric-select">Métrica Principal</label>
                                <select id="metric-select" class="form-control">
                                    <option value="overall_satisfaction">Satisfacción General</option>
                                    <option value="participation_rate">Tasa de Participación</option>
                                    <option value="category_average">Promedio por Categoría</option>
                                    <option value="employee_engagement">Compromiso</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block" onclick="updateTrendsAnalysis()">
                                    <i class="fas fa-chart-line"></i> Actualizar Análisis
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de tendencias -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="trend-metric">
                                <div class="trend-value">
                                    <?php
                                    $current_score = $trends_data['current_period']['overall_satisfaction'] ?? 0;
                                    $previous_score = $trends_data['previous_period']['overall_satisfaction'] ?? 0;
                                    $trend_change = $current_score - $previous_score;
                                    ?>
                                    <span class="h4 <?= $trend_change >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= ($trend_change >= 0 ? '+' : '') . number_format($trend_change, 2) ?>
                                    </span>
                                </div>
                                <div class="trend-label">Cambio Período Actual</div>
                                <small class="text-muted">vs período anterior</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-percentage fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="trend-metric">
                                <div class="trend-value">
                                    <span class="h4 text-info">
                                        <?= number_format($trends_data['volatility_index'] ?? 0, 1) ?>%
                                    </span>
                                </div>
                                <div class="trend-label">Índice de Volatilidad</div>
                                <small class="text-muted">variabilidad en el tiempo</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-arrow-trend-up fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="trend-metric">
                                <div class="trend-value">
                                    <span class="h4 text-success">
                                        <?= $trends_data['improving_categories'] ?? 0 ?>
                                    </span>
                                </div>
                                <div class="trend-label">Categorías Mejorando</div>
                                <small class="text-muted">tendencia positiva</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="trend-metric">
                                <div class="trend-value">
                                    <span class="h4 text-warning">
                                        <?= $trends_data['declining_categories'] ?? 0 ?>
                                    </span>
                                </div>
                                <div class="trend-label">Categorías en Declive</div>
                                <small class="text-muted">requieren atención</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico principal de tendencias -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-area text-primary"></i>
                            Evolución Temporal - Satisfacción General
                        </h5>
                        <div class="chart-controls">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary chart-type-btn active" data-type="line">
                                    <i class="fas fa-chart-line"></i> Línea
                                </button>
                                <button class="btn btn-outline-secondary chart-type-btn" data-type="area">
                                    <i class="fas fa-chart-area"></i> Área
                                </button>
                                <button class="btn btn-outline-secondary chart-type-btn" data-type="bar">
                                    <i class="fas fa-chart-bar"></i> Barras
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="trendsMainChart" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de análisis detallado -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-layer-group text-success"></i>
                        Tendencias por Categoría
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building text-info"></i>
                        Comparación Departamental
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis estadístico -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator text-warning"></i>
                        Análisis Estadístico
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Métricas de Tendencia</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Coeficiente de Correlación:</strong></td>
                                    <td class="text-end">
                                        <?php
                                        $correlation = $trends_data['statistics']['correlation'] ?? 0;
                                        $correlation_class = abs($correlation) > 0.7 ? 'success' : (abs($correlation) > 0.4 ? 'warning' : 'danger');
                                        ?>
                                        <span class="text-<?= $correlation_class ?>">
                                            <?= number_format($correlation, 3) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Pendiente de Tendencia:</strong></td>
                                    <td class="text-end">
                                        <?php
                                        $slope = $trends_data['statistics']['slope'] ?? 0;
                                        $slope_class = $slope > 0 ? 'success' : ($slope < 0 ? 'danger' : 'secondary');
                                        ?>
                                        <span class="text-<?= $slope_class ?>">
                                            <?= number_format($slope, 4) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>R² (Bondad de Ajuste):</strong></td>
                                    <td class="text-end">
                                        <?php
                                        $r_squared = $trends_data['statistics']['r_squared'] ?? 0;
                                        $r_squared_class = $r_squared > 0.7 ? 'success' : ($r_squared > 0.4 ? 'warning' : 'danger');
                                        ?>
                                        <span class="text-<?= $r_squared_class ?>">
                                            <?= number_format($r_squared, 3) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Desviación Estándar:</strong></td>
                                    <td class="text-end">
                                        <span class="text-info">
                                            <?= number_format($trends_data['statistics']['std_deviation'] ?? 0, 3) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success mb-3">Proyección Futura</h6>
                            <div class="projection-container">
                                <?php if (isset($trends_data['projection'])): ?>
                                    <div class="projection-item">
                                        <label>Próximo trimestre:</label>
                                        <div class="projection-value">
                                            <?php
                                            $projection_3m = $trends_data['projection']['next_quarter'] ?? 0;
                                            $projection_class = $projection_3m > $current_score ? 'success' : ($projection_3m < $current_score ? 'danger' : 'secondary');
                                            ?>
                                            <span class="h6 text-<?= $projection_class ?>">
                                                <?= number_format($projection_3m, 2) ?>
                                                <small class="trend-arrow">
                                                    <?php if ($projection_3m > $current_score): ?>
                                                        <i class="fas fa-arrow-up text-success"></i>
                                                    <?php elseif ($projection_3m < $current_score): ?>
                                                        <i class="fas fa-arrow-down text-danger"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-arrow-right text-secondary"></i>
                                                    <?php endif; ?>
                                                </small>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="projection-item">
                                        <label>Próximo año:</label>
                                        <div class="projection-value">
                                            <?php
                                            $projection_12m = $trends_data['projection']['next_year'] ?? 0;
                                            $projection_class = $projection_12m > $current_score ? 'success' : ($projection_12m < $current_score ? 'danger' : 'secondary');
                                            ?>
                                            <span class="h6 text-<?= $projection_class ?>">
                                                <?= number_format($projection_12m, 2) ?>
                                                <small class="trend-arrow">
                                                    <?php if ($projection_12m > $current_score): ?>
                                                        <i class="fas fa-arrow-up text-success"></i>
                                                    <?php elseif ($projection_12m < $current_score): ?>
                                                        <i class="fas fa-arrow-down text-danger"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-arrow-right text-secondary"></i>
                                                    <?php endif; ?>
                                                </small>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="confidence-interval mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Intervalo de confianza: 95%<br>
                                            Margen de error: ±<?= number_format($trends_data['projection']['margin_error'] ?? 0.1, 2) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-circle text-danger"></i>
                        Alertas de Tendencia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alerts-container">
                        <?php if (isset($trends_data['alerts']) && !empty($trends_data['alerts'])): ?>
                            <?php foreach ($trends_data['alerts'] as $alert): ?>
                                <div class="alert alert-<?= $alert['severity'] == 'high' ? 'danger' : ($alert['severity'] == 'medium' ? 'warning' : 'info') ?> alert-sm">
                                    <div class="d-flex">
                                        <div class="alert-icon">
                                            <?php if ($alert['severity'] == 'high'): ?>
                                                <i class="fas fa-exclamation-triangle"></i>
                                            <?php elseif ($alert['severity'] == 'medium'): ?>
                                                <i class="fas fa-exclamation-circle"></i>
                                            <?php else: ?>
                                                <i class="fas fa-info-circle"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="alert-content">
                                            <div class="alert-title"><?= htmlspecialchars($alert['title']) ?></div>
                                            <div class="alert-description"><?= htmlspecialchars($alert['description']) ?></div>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($alert['detected_at'])) ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <div>No hay alertas activas</div>
                                <small>Las tendencias están dentro de los parámetros normales</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de datos históricos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table text-secondary"></i>
                            Datos Históricos Detallados
                        </h5>
                        <div class="table-controls">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleHistoricalTable()">
                                <i class="fas fa-eye"></i> Mostrar/Ocultar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0" id="historical-table-container" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Período</th>
                                    <th class="text-center">Satisfacción</th>
                                    <th class="text-center">Participación</th>
                                    <th class="text-center">Respuestas</th>
                                    <th class="text-center">Cambio</th>
                                    <th class="text-center">Tendencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($trends_data['historical_data'])): ?>
                                    <?php foreach ($trends_data['historical_data'] as $period): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($period['period_name']) ?></strong><br>
                                                <small class="text-muted"><?= date('d/m/Y', strtotime($period['date'])) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="font-weight-bold">
                                                    <?= number_format($period['satisfaction'], 2) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?= number_format($period['participation_rate'], 1) ?>%
                                            </td>
                                            <td class="text-center">
                                                <?= $period['total_responses'] ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $change = $period['change_from_previous'] ?? 0;
                                                $change_class = $change > 0 ? 'success' : ($change < 0 ? 'danger' : 'secondary');
                                                ?>
                                                <span class="text-<?= $change_class ?>">
                                                    <?= ($change > 0 ? '+' : '') . number_format($change, 2) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $trend = $period['trend_direction'] ?? 'stable';
                                                if ($trend == 'up'):
                                                ?>
                                                    <i class="fas fa-arrow-up text-success" title="Tendencia positiva"></i>
                                                <?php elseif ($trend == 'down'): ?>
                                                    <i class="fas fa-arrow-down text-danger" title="Tendencia negativa"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-arrow-right text-secondary" title="Tendencia estable"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeTrendsCharts();
    initializeControls();
});

let trendsChart, categoryChart, departmentChart;
let currentChartType = 'line';

function initializeTrendsCharts() {
    createMainTrendsChart();
    createCategoryTrendsChart();
    createDepartmentTrendsChart();
}

function createMainTrendsChart() {
    const ctx = document.getElementById('trendsMainChart').getContext('2d');
    const trendsData = <?= json_encode($trends_data['timeline'] ?? []) ?>;
    
    trendsChart = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: trendsData.map(item => item.period),
            datasets: [{
                label: 'Satisfacción General',
                data: trendsData.map(item => item.satisfaction),
                borderColor: '#007bff',
                backgroundColor: currentChartType === 'area' ? 'rgba(0, 123, 255, 0.1)' : '#007bff',
                fill: currentChartType === 'area',
                tension: 0.4
            }, {
                label: 'Línea de Tendencia',
                data: trendsData.map(item => item.trend_line),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        afterBody: function(context) {
                            const dataIndex = context[0].dataIndex;
                            const data = trendsData[dataIndex];
                            return [
                                `Participación: ${data.participation_rate}%`,
                                `Respuestas: ${data.responses}`,
                                `Cambio: ${data.change > 0 ? '+' : ''}${data.change.toFixed(2)}`
                            ];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 0.5
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45
                    }
                }
            }
        }
    });
}

function createCategoryTrendsChart() {
    const ctx = document.getElementById('categoryTrendsChart').getContext('2d');
    const categoryData = <?= json_encode($trends_data['categories'] ?? []) ?>;
    
    const datasets = categoryData.map((category, index) => {
        const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997'];
        return {
            label: category.name,
            data: category.timeline.map(item => item.value),
            borderColor: colors[index % colors.length],
            backgroundColor: colors[index % colors.length] + '20',
            fill: false,
            tension: 0.4
        };
    });
    
    categoryChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: categoryData[0]?.timeline.map(item => item.period) || [],
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5
                }
            }
        }
    });
}

function createDepartmentTrendsChart() {
    const ctx = document.getElementById('departmentTrendsChart').getContext('2d');
    const departmentData = <?= json_encode($trends_data['departments'] ?? []) ?>;
    
    departmentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: departmentData.map(dept => dept.name),
            datasets: [{
                label: 'Actual',
                data: departmentData.map(dept => dept.current_score),
                backgroundColor: 'rgba(54, 162, 235, 0.8)'
            }, {
                label: 'Período Anterior',
                data: departmentData.map(dept => dept.previous_score),
                backgroundColor: 'rgba(255, 99, 132, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5
                }
            }
        }
    });
}

function initializeControls() {
    // Controles de tipo de gráfico
    const chartTypeButtons = document.querySelectorAll('.chart-type-btn');
    chartTypeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            chartTypeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const newType = this.dataset.type;
            changeChartType(newType);
        });
    });

    // Selectores de período
    const periodSelectors = document.querySelectorAll('.period-selector');
    periodSelectors.forEach(selector => {
        selector.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.dataset.period;
            updatePeriod(period);
        });
    });
}

function changeChartType(type) {
    currentChartType = type;
    
    if (trendsChart) {
        trendsChart.destroy();
        createMainTrendsChart();
    }
}

function updateTrendsAnalysis() {
    const company = document.getElementById('company-select').value;
    const department = document.getElementById('department-select').value;
    const metric = document.getElementById('metric-select').value;
    
    // Mostrar indicador de carga
    showLoadingIndicator();
    
    // Hacer petición AJAX para actualizar datos
    fetch(`<?= BASE_URL ?>admin/reports/trends/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            company_id: company,
            department_id: department,
            metric: metric
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar gráficos con nuevos datos
            updateChartsWithNewData(data.trends_data);
            hideLoadingIndicator();
        } else {
            showNotification('Error al actualizar el análisis: ' + (data.message || 'Error desconocido'), 'error');
            hideLoadingIndicator();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
        hideLoadingIndicator();
    });
}

function updatePeriod(period) {
    const params = new URLSearchParams(window.location.search);
    params.set('period', period);
    window.location.search = params.toString();
}

function toggleHistoricalTable() {
    const container = document.getElementById('historical-table-container');
    if (container.style.display === 'none') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function exportTrendsReport() {
    const company = document.getElementById('company-select').value;
    const department = document.getElementById('department-select').value;
    const metric = document.getElementById('metric-select').value;
    
    const params = new URLSearchParams({
        company_id: company,
        department_id: department,
        metric: metric,
        format: 'excel'
    });
    
    window.location.href = `<?= BASE_URL ?>admin/reports/trends/export?${params.toString()}`;
}

function showLoadingIndicator() {
    // Implementar indicador de carga
    document.body.classList.add('loading');
}

function hideLoadingIndicator() {
    document.body.classList.remove('loading');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show notification-toast`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>

<style>
.border-left-primary { border-left: 4px solid #007bff !important; }
.border-left-info { border-left: 4px solid #17a2b8 !important; }
.border-left-success { border-left: 4px solid #28a745 !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }

.trend-metric {
    text-align: center;
}

.trend-value {
    margin-bottom: 0.25rem;
}

.trend-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.projection-container {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}

.projection-item {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.projection-item label {
    flex-grow: 1;
    margin-bottom: 0;
    font-weight: 500;
}

.projection-value {
    text-align: right;
}

.trend-arrow {
    margin-left: 0.25rem;
}

.confidence-interval {
    padding-top: 0.75rem;
    border-top: 1px solid #dee2e6;
}

.alerts-container {
    max-height: 400px;
    overflow-y: auto;
}

.alert-sm {
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.5rem;
}

.alert-icon {
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.alert-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.alert-description {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.chart-controls {
    display: flex;
    gap: 0.5rem;
}

.chart-type-btn.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.table-controls {
    margin-bottom: 1rem;
}

.notification-toast {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.loading::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    z-index: 9998;
}

.loading::after {
    content: '';
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 9999;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

@media (max-width: 768px) {
    .chart-controls {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .projection-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .projection-value {
        text-align: left;
        width: 100%;
        margin-top: 0.25rem;
    }
}
</style>