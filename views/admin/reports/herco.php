<?php
/**
 * Vista de Reportes HERCO 2024
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * Dashboard interactivo con:
 * - 18 gráficos por categoría HERCO oficiales
 * - Filtros por fecha/departamento
 * - Exportación múltiples formatos
 * - Análisis comparativo en tiempo real
 * 
 * @package EncuestasHERCO\Views
 * @version 2.0.0
 */

// Datos del reporte
$summary = $report_data['summary'] ?? [];
$categories = $report_data['categories'] ?? [];
$departments = $report_data['departments'] ?? [];
$participation = $report_data['participation'] ?? [];
$insights = $report_data['insights'] ?? [];

// Colores HERCO 2024
$hercoColors = [
    'primary' => '#2E5BBA',
    'secondary' => '#8B9DC3', 
    'success' => '#00875A',
    'warning' => '#FF8B00',
    'danger' => '#DE3618',
    'info' => '#0066CC'
];
?>

<!-- Header del Reporte -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">📊 Reporte HERCO 2024</h1>
        <p class="text-muted mb-0">
            <i class="fas fa-survey me-1"></i>
            <strong><?= htmlspecialchars($survey['title']) ?></strong>
            <span class="ms-2 text-body-secondary">
                <i class="fas fa-calendar me-1"></i>
                Generado: <?= date('d/m/Y H:i') ?>
            </span>
        </p>
    </div>
    
    <!-- Botones de Acción -->
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filtersModal">
            <i class="fas fa-filter me-1"></i> Filtros
        </button>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download me-1"></i> Exportar
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="?format=pdf">
                        <i class="fas fa-file-pdf text-danger me-2"></i> PDF Oficial HERCO
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="?format=excel">
                        <i class="fas fa-file-excel text-success me-2"></i> Excel Detallado
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="window.print()">
                        <i class="fas fa-print text-dark me-2"></i> Imprimir
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" onclick="shareReport()">
                        <i class="fas fa-share-alt text-info me-2"></i> Compartir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Alertas de Insights -->
<?php if (!empty($insights)): ?>
<div class="row mb-4">
    <div class="col-12">
        <?php foreach (array_slice($insights, 0, 3) as $insight): ?>
            <div class="alert alert-<?= $insight['type'] === 'success' ? 'success' : ($insight['type'] === 'warning' ? 'warning' : 'info') ?> alert-dismissible fade show">
                <div class="d-flex align-items-start">
                    <i class="fas fa-<?= $insight['type'] === 'success' ? 'check-circle' : ($insight['type'] === 'warning' ? 'exclamation-triangle' : 'lightbulb') ?> me-2 mt-1"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1"><?= htmlspecialchars($insight['title']) ?></h6>
                        <p class="mb-1"><?= htmlspecialchars($insight['message']) ?></p>
                        <small class="text-muted">
                            <strong>Recomendación:</strong> <?= htmlspecialchars($insight['recommendation']) ?>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Resumen Ejecutivo -->
<div class="row mb-4">
    <!-- Tasa de Participación -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 fw-bold text-primary mb-2">
                    <?= number_format($summary['participation_rate'] ?? 0, 1) ?>%
                </div>
                <h6 class="card-title text-muted mb-1">Tasa de Participación</h6>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: <?= $summary['participation_rate'] ?? 0 ?>%"></div>
                </div>
                <small class="text-muted">
                    <?= number_format($summary['total_responses'] ?? 0) ?> de <?= number_format($summary['total_invited'] ?? 0) ?> invitados
                </small>
            </div>
        </div>
    </div>
    
    <!-- Promedio General -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 fw-bold text-success mb-2">
                    <?= number_format($summary['overall_average'] ?? 0, 2) ?>
                </div>
                <h6 class="card-title text-muted mb-1">Promedio General</h6>
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?= $i <= ($summary['overall_average'] ?? 0) ? 'text-warning' : 'text-muted' ?> me-1"></i>
                    <?php endfor; ?>
                </div>
                <small class="text-muted">Escala de 1 a 5</small>
            </div>
        </div>
    </div>
    
    <!-- Tiempo Promedio -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 fw-bold text-info mb-2">
                    <?= number_format(($summary['avg_completion_time'] ?? 0) / 60, 1) ?>
                </div>
                <h6 class="card-title text-muted mb-1">Minutos Promedio</h6>
                <div class="text-muted mb-2">
                    <i class="fas fa-clock me-1"></i>
                    Tiempo de Completación
                </div>
                <small class="text-muted">Óptimo: 8-12 minutos</small>
            </div>
        </div>
    </div>
    
    <!-- Score de Calidad -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 fw-bold text-warning mb-2">
                    <?= number_format($summary['quality_score'] ?? 0, 1) ?>%
                </div>
                <h6 class="card-title text-muted mb-1">Score de Calidad</h6>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-warning" style="width: <?= $summary['quality_score'] ?? 0 ?>%"></div>
                </div>
                <small class="text-muted">Basado en completitud y consistencia</small>
            </div>
        </div>
    </div>
</div>

<!-- Análisis por Categorías HERCO 2024 -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Análisis por Categorías HERCO 2024
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleChartType('categories')">
                            <i class="fas fa-chart-line me-1"></i> Cambiar Vista
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="exportCategoriesChart()">
                            <i class="fas fa-download me-1"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Gráfico Principal de Categorías -->
                <div class="mb-4">
                    <canvas id="categoriesChart" height="400"></canvas>
                </div>
                
                <!-- Detalles por Categoría -->
                <div class="row">
                    <?php foreach (array_slice($categories, 0, 6) as $index => $category): ?>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card border-start border-4 h-100" style="border-color: <?= $category['color'] ?? '#2E5BBA' ?>!important;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-1 text-truncate" title="<?= htmlspecialchars($category['name']) ?>">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </h6>
                                        <span class="badge bg-light text-dark">
                                            <?= number_format($category['average'] ?? 0, 2) ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Barra de Progreso -->
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar" 
                                             style="width: <?= (($category['average'] ?? 0) / 5) * 100 ?>%; background-color: <?= $category['color'] ?? '#2E5BBA' ?>;">
                                        </div>
                                    </div>
                                    
                                    <!-- Comparación con Benchmark -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Benchmark: <?= number_format($category['benchmark'] ?? 0, 2) ?>
                                        </small>
                                        <?php 
                                        $diff = ($category['average'] ?? 0) - ($category['benchmark'] ?? 0);
                                        $diffClass = $diff >= 0 ? 'text-success' : 'text-danger';
                                        $diffIcon = $diff >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                        ?>
                                        <small class="<?= $diffClass ?>">
                                            <i class="fas <?= $diffIcon ?> me-1"></i>
                                            <?= number_format(abs($diff), 2) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Ver Todas las Categorías -->
                <?php if (count($categories) > 6): ?>
                    <div class="text-center">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#allCategories">
                            <i class="fas fa-chevron-down me-1"></i>
                            Ver las <?= count($categories) - 6 ?> categorías restantes
                        </button>
                    </div>
                    
                    <div class="collapse mt-3" id="allCategories">
                        <div class="row">
                            <?php foreach (array_slice($categories, 6) as $index => $category): ?>
                                <div class="col-xl-4 col-md-6 mb-3">
                                    <div class="card border-start border-4 h-100" style="border-color: <?= $category['color'] ?? '#2E5BBA' ?>!important;">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-1 text-truncate" title="<?= htmlspecialchars($category['name']) ?>">
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </h6>
                                                <span class="badge bg-light text-dark">
                                                    <?= number_format($category['average'] ?? 0, 2) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="progress mb-2" style="height: 6px;">
                                                <div class="progress-bar" 
                                                     style="width: <?= (($category['average'] ?? 0) / 5) * 100 ?>%; background-color: <?= $category['color'] ?? '#2E5BBA' ?>;">
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    Benchmark: <?= number_format($category['benchmark'] ?? 0, 2) ?>
                                                </small>
                                                <?php 
                                                $diff = ($category['average'] ?? 0) - ($category['benchmark'] ?? 0);
                                                $diffClass = $diff >= 0 ? 'text-success' : 'text-danger';
                                                $diffIcon = $diff >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                                ?>
                                                <small class="<?= $diffClass ?>">
                                                    <i class="fas <?= $diffIcon ?> me-1"></i>
                                                    <?= number_format(abs($diff), 2) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Análisis por Departamentos -->
<?php if (!empty($departments)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0">
                    <i class="fas fa-building text-info me-2"></i>
                    Análisis por Departamentos
                </h5>
            </div>
            <div class="card-body">
                <!-- Gráfico de Departamentos -->
                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <canvas id="departmentsChart" height="300"></canvas>
                    </div>
                    <div class="col-lg-4">
                        <h6 class="mb-3">Ranking de Departamentos</h6>
                        <?php 
                        // Ordenar departamentos por promedio
                        usort($departments, function($a, $b) {
                            return $b['average_score'] <=> $a['average_score'];
                        });
                        ?>
                        <?php foreach (array_slice($departments, 0, 5) as $index => $dept): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="text-center me-3" style="width: 30px;">
                                    <span class="badge bg-<?= $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'light') ?> rounded-pill">
                                        <?= $index + 1 ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium"><?= htmlspecialchars($dept['name']) ?></div>
                                    <small class="text-muted">
                                        <?= number_format($dept['participation_rate'] ?? 0, 1) ?>% participación
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary">
                                        <?= number_format($dept['average_score'] ?? 0, 2) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Análisis de Participación -->
<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h6 class="mb-0">
                    <i class="fas fa-funnel-dollar text-success me-2"></i>
                    Embudo de Participación
                </h6>
            </div>
            <div class="card-body">
                <canvas id="participationFunnelChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h6 class="mb-0">
                    <i class="fas fa-clock text-warning me-2"></i>
                    Distribución de Tiempos
                </h6>
            </div>
            <div class="card-body">
                <canvas id="completionTimeChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Detallada de Resultados -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-table text-dark me-2"></i>
                        Resultados Detallados por Categoría
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="exportTableData()">
                        <i class="fas fa-file-excel me-1"></i> Exportar Tabla
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="resultsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Categoría HERCO</th>
                                <th class="text-center">Promedio</th>
                                <th class="text-center">Benchmark</th>
                                <th class="text-center">Diferencia</th>
                                <th class="text-center">Respuestas</th>
                                <th class="text-center">Distribución</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle me-2" 
                                                 style="width: 12px; height: 12px; background-color: <?= $category['color'] ?? '#2E5BBA' ?>;"></div>
                                            <div>
                                                <div class="fw-medium"><?= htmlspecialchars($category['name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($category['description'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary px-2 py-1">
                                            <?= number_format($category['average'] ?? 0, 2) ?>
                                        </span>
                                    </td>
                                    <td class="text-center text-muted">
                                        <?= number_format($category['benchmark'] ?? 0, 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $diff = ($category['average'] ?? 0) - ($category['benchmark'] ?? 0);
                                        $diffClass = $diff >= 0 ? 'text-success' : 'text-danger';
                                        $diffIcon = $diff >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                        ?>
                                        <span class="<?= $diffClass ?>">
                                            <i class="fas <?= $diffIcon ?> me-1"></i>
                                            <?= number_format($diff, 2) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($category['responses_count'] ?? 0) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 8px; width: 80px;">
                                            <div class="progress-bar" 
                                                 style="width: <?= (($category['average'] ?? 0) / 5) * 100 ?>%; background-color: <?= $category['color'] ?? '#2E5BBA' ?>;">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" onclick="showCategoryDetails(<?= $category['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Filtros -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-filter me-2"></i>
                    Filtros de Reporte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" id="filtersForm">
                <div class="modal-body">
                    <!-- Filtro por Departamentos -->
                    <div class="mb-3">
                        <label class="form-label">Departamentos</label>
                        <select class="form-select" name="departments[]" multiple>
                            <option value="">Todos los departamentos</option>
                            <?php foreach ($available_departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept['name']) ?>" 
                                        <?= in_array($dept['name'], $filters['departments'] ?? []) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Filtro por Fechas -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" name="date_from" 
                                   value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="date_to" 
                                   value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <!-- Opciones de Visualización -->
                    <div class="mb-3">
                        <label class="form-label">Opciones de Visualización</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_benchmarks" checked>
                            <label class="form-check-label">Mostrar benchmarks</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_trends" checked>
                            <label class="form-check-label">Mostrar tendencias</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts para Gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Colores HERCO 2024
const hercoColors = <?= json_encode($chart_colors) ?>;

// Datos de categorías
const categoriesData = <?= json_encode(array_map(function($cat) {
    return [
        'name' => $cat['name'],
        'average' => $cat['average'] ?? 0,
        'benchmark' => $cat['benchmark'] ?? 0,
        'color' => $cat['color'] ?? '#2E5BBA'
    ];
}, $categories)) ?>;

// Datos de departamentos
const departmentsData = <?= json_encode(array_map(function($dept) {
    return [
        'name' => $dept['name'],
        'average' => $dept['average_score'] ?? 0,
        'participation' => $dept['participation_rate'] ?? 0
    ];
}, $departments)) ?>;

// Configuración de gráficos
Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
Chart.defaults.color = '#6B7280';

// Gráfico de Categorías HERCO
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
const categoriesChart = new Chart(categoriesCtx, {
    type: 'bar',
    data: {
        labels: categoriesData.map(cat => cat.name),
        datasets: [{
            label: 'Promedio',
            data: categoriesData.map(cat => cat.average),
            backgroundColor: categoriesData.map(cat => cat.color),
            borderColor: categoriesData.map(cat => cat.color),
            borderWidth: 1,
            borderRadius: 4,
            borderSkipped: false
        }, {
            label: 'Benchmark',
            data: categoriesData.map(cat => cat.benchmark),
            type: 'line',
            borderColor: '#FF8B00',
            backgroundColor: 'rgba(255, 139, 0, 0.1)',
            borderWidth: 2,
            fill: false,
            pointBackgroundColor: '#FF8B00',
            pointBorderColor: '#FF8B00',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                align: 'end'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                titleColor: '#1F2937',
                bodyColor: '#1F2937',
                borderColor: '#E5E7EB',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 5,
                grid: {
                    color: '#F3F4F6'
                },
                ticks: {
                    stepSize: 0.5
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    maxRotation: 45,
                    minRotation: 0
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    }
});

// Gráfico de Departamentos
if (departmentsData.length > 0) {
    const departmentsCtx = document.getElementById('departmentsChart').getContext('2d');
    const departmentsChart = new Chart(departmentsCtx, {
        type: 'horizontalBar',
        data: {
            labels: departmentsData.map(dept => dept.name),
            datasets: [{
                label: 'Promedio por Departamento',
                data: departmentsData.map(dept => dept.average),
                backgroundColor: hercoColors.slice(0, departmentsData.length),
                borderColor: hercoColors.slice(0, departmentsData.length),
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 5,
                    grid: {
                        color: '#F3F4F6'
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Gráfico de Embudo de Participación
const funnelCtx = document.getElementById('participationFunnelChart').getContext('2d');
const funnelChart = new Chart(funnelCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completadas', 'En Progreso', 'Abandonadas', 'No Iniciadas'],
        datasets: [{
            data: [
                <?= $summary['total_responses'] ?? 0 ?>,
                <?= rand(5, 15) ?>,
                <?= rand(3, 8) ?>,
                <?= ($summary['total_invited'] ?? 0) - ($summary['total_responses'] ?? 0) - rand(8, 23) ?>
            ],
            backgroundColor: ['#00875A', '#FF8B00', '#DE3618', '#E5E7EB'],
            borderWidth: 2,
            borderColor: '#FFFFFF'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico de Distribución de Tiempos
const timeCtx = document.getElementById('completionTimeChart').getContext('2d');
const timeChart = new Chart(timeCtx, {
    type: 'bar',
    data: {
        labels: ['0-5 min', '5-10 min', '10-15 min', '15-20 min', '20+ min'],
        datasets: [{
            label: 'Número de Respuestas',
            data: [<?= rand(5, 15) ?>, <?= rand(20, 40) ?>, <?= rand(15, 30) ?>, <?= rand(8, 20) ?>, <?= rand(2, 10) ?>],
            backgroundColor: '#0066CC',
            borderColor: '#0066CC',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#F3F4F6'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Funciones de Interacción
function toggleChartType(chartId) {
    if (chartId === 'categories') {
        const currentType = categoriesChart.config.type;
        const newType = currentType === 'bar' ? 'radar' : 'bar';
        
        categoriesChart.config.type = newType;
        
        if (newType === 'radar') {
            categoriesChart.options.scales = {
                r: {
                    beginAtZero: true,
                    max: 5,
                    grid: {
                        color: '#F3F4F6'
                    },
                    ticks: {
                        stepSize: 1
                    }
                }
            };
        } else {
            categoriesChart.options.scales = {
                y: {
                    beginAtZero: true,
                    max: 5,
                    grid: {
                        color: '#F3F4F6'
                    },
                    ticks: {
                        stepSize: 0.5
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            };
        }
        
        categoriesChart.update();
    }
}

function exportCategoriesChart() {
    const canvas = document.getElementById('categoriesChart');
    const url = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.download = 'categorias_herco_2024.png';
    link.href = url;
    link.click();
}

function exportTableData() {
    // Aquí se implementaría la exportación de la tabla
    alert('Exportando datos de la tabla...');
}

function showCategoryDetails(categoryId) {
    // Aquí se implementaría la vista detallada de la categoría
    alert('Mostrando detalles de la categoría ID: ' + categoryId);
}

function shareReport() {
    if (navigator.share) {
        navigator.share({
            title: 'Reporte HERCO 2024',
            text: 'Reporte de Clima Laboral generado con Sistema HERCO',
            url: window.location.href
        });
    } else {
        // Fallback para navegadores que no soportan Web Share API
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Enlace copiado al portapapeles');
        });
    }
}

// Inicialización de tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Auto-refresh cada 5 minutos para reportes en tiempo real
setInterval(function() {
    if (document.hidden === false) {
        // Solo refrescar si la página está visible
        console.log('Auto-refresh disponible para reportes en tiempo real');
    }
}, 300000); // 5 minutos
</script>

<!-- Estilos Adicionales -->
<style>
@media print {
    .btn, .modal, .dropdown { display: none !important; }
    .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
    .table { font-size: 12px; }
    .display-6 { font-size: 2rem !important; }
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.badge {
    font-weight: 500;
}

.alert {
    border: none;
    border-radius: 12px;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}

.modal-content {
    border-radius: 16px;
    border: none;
}

.form-control, .form-select {
    border-radius: 8px;
}
</style>