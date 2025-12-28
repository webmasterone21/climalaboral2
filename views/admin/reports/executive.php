<?php
// views/admin/reports/executive.php - Reporte ejecutivo simplificado
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Reporte Ejecutivo</h1>
                        <h5 class="text-muted"><?= htmlspecialchars($survey['title']) ?></h5>
                        <p class="text-muted mb-0">Resumen de alto nivel para dirección ejecutiva</p>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports/dashboard/<?= $survey['id'] ?>">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Ejecutivo</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>admin/reports/show/<?= $survey['id'] ?>" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> Reporte Completo
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/executive-pdf/<?= $survey['id'] ?>">
                                        <i class="fas fa-file-pdf text-danger"></i> PDF Ejecutivo
                                    </a>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/executive-ppt/<?= $survey['id'] ?>">
                                        <i class="fas fa-file-powerpoint text-warning"></i> PowerPoint
                                    </a>
                                </div>
                            </div>
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen ejecutivo principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary executive-summary-card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-line fa-2x me-3"></i>
                        <div>
                            <h4 class="card-title mb-0">Resumen Ejecutivo</h4>
                            <small class="opacity-75">Estado general del clima laboral</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="executive-metrics">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="metric-large">
                                            <div class="metric-icon">
                                                <?php
                                                $overall_score = $executive_summary['overall_satisfaction'] ?? 0;
                                                if ($overall_score >= 4.0) {
                                                    echo '<i class="fas fa-smile text-success"></i>';
                                                    $status_text = 'EXCELENTE';
                                                    $status_class = 'success';
                                                } elseif ($overall_score >= 3.5) {
                                                    echo '<i class="fas fa-meh text-info"></i>';
                                                    $status_text = 'BUENO';
                                                    $status_class = 'info';
                                                } elseif ($overall_score >= 3.0) {
                                                    echo '<i class="fas fa-meh text-warning"></i>';
                                                    $status_text = 'REGULAR';
                                                    $status_class = 'warning';
                                                } else {
                                                    echo '<i class="fas fa-frown text-danger"></i>';
                                                    $status_text = 'CRÍTICO';
                                                    $status_class = 'danger';
                                                }
                                                ?>
                                            </div>
                                            <div class="metric-value display-3 text-<?= $status_class ?>">
                                                <?= number_format($overall_score, 1) ?><span class="h4">/5.0</span>
                                            </div>
                                            <div class="metric-label h5 text-<?= $status_class ?>">
                                                <?= $status_text ?>
                                            </div>
                                            <div class="metric-description text-muted">
                                                Índice General de Satisfacción Laboral
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="participation-summary">
                                            <h6 class="text-primary mb-3">Participación</h6>
                                            <?php
                                            $participation_rate = ($executive_summary['completed'] / $executive_summary['total_invited']) * 100;
                                            ?>
                                            <div class="progress mb-2" style="height: 15px;">
                                                <div class="progress-bar bg-info" role="progressbar" 
                                                     style="width: <?= $participation_rate ?>%"
                                                     aria-valuenow="<?= $participation_rate ?>" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    <?= number_format($participation_rate, 1) ?>%
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    <?= $executive_summary['completed'] ?> completadas
                                                </small>
                                                <small class="text-muted">
                                                    de <?= $executive_summary['total_invited'] ?> invitadas
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="survey-info">
                                <h6 class="text-primary mb-3">Información de la Encuesta</h6>
                                <div class="info-item">
                                    <i class="fas fa-building text-primary"></i>
                                    <strong><?= htmlspecialchars($survey['company_name']) ?></strong>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar text-primary"></i>
                                    <?= date('d/m/Y', strtotime($survey['start_date'])) ?> - 
                                    <?= date('d/m/Y', strtotime($survey['end_date'])) ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-users text-primary"></i>
                                    <?= count($executive_summary['departments'] ?? []) ?> departamentos evaluados
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-list-alt text-primary"></i>
                                    <?= $executive_summary['total_questions'] ?? 0 ?> preguntas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs principales en formato ejecutivo -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card executive-kpi-card border-left-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon">
                            <i class="fas fa-trophy fa-2x text-primary"></i>
                        </div>
                        <div class="kpi-content ms-3">
                            <div class="kpi-title">Mejor Categoría</div>
                            <div class="kpi-value text-success">
                                <?= isset($executive_summary['best_category']) ? htmlspecialchars($executive_summary['best_category']['name']) : 'N/A' ?>
                            </div>
                            <div class="kpi-score">
                                <?= isset($executive_summary['best_category']) ? number_format($executive_summary['best_category']['score'], 1) : '0.0' ?>/5.0
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card executive-kpi-card border-left-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                        <div class="kpi-content ms-3">
                            <div class="kpi-title">Área Crítica</div>
                            <div class="kpi-value text-danger">
                                <?= isset($executive_summary['worst_category']) ? htmlspecialchars($executive_summary['worst_category']['name']) : 'N/A' ?>
                            </div>
                            <div class="kpi-score">
                                <?= isset($executive_summary['worst_category']) ? number_format($executive_summary['worst_category']['score'], 1) : '0.0' ?>/5.0
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card executive-kpi-card border-left-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon">
                            <i class="fas fa-building fa-2x text-info"></i>
                        </div>
                        <div class="kpi-content ms-3">
                            <div class="kpi-title">Mejor Departamento</div>
                            <div class="kpi-value text-success">
                                <?= isset($executive_summary['best_department']) ? htmlspecialchars($executive_summary['best_department']['name']) : 'N/A' ?>
                            </div>
                            <div class="kpi-score">
                                <?= isset($executive_summary['best_department']) ? number_format($executive_summary['best_department']['score'], 1) : '0.0' ?>/5.0
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card executive-kpi-card border-left-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon">
                            <i class="fas fa-percentage fa-2x text-success"></i>
                        </div>
                        <div class="kpi-content ms-3">
                            <div class="kpi-title">Índice Positivo</div>
                            <div class="kpi-value text-success">
                                <?= number_format($executive_summary['positive_responses'] ?? 0, 1) ?>%
                            </div>
                            <div class="kpi-score">
                                Respuestas Favorables
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Matriz de evaluación por categorías -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar text-primary"></i>
                        Matriz de Evaluación por Categorías
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="executiveChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-ul text-info"></i>
                        Rankings
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Top 3 mejores -->
                    <h6 class="text-success mb-3">
                        <i class="fas fa-thumbs-up"></i>
                        Fortalezas Principales
                    </h6>
                    <div class="ranking-list">
                        <?php if (isset($executive_summary['top_categories'])): ?>
                            <?php foreach (array_slice($executive_summary['top_categories'], 0, 3) as $index => $category): ?>
                                <div class="ranking-item">
                                    <div class="ranking-number bg-success text-white"><?= $index + 1 ?></div>
                                    <div class="ranking-content">
                                        <div class="ranking-name"><?= htmlspecialchars($category['name']) ?></div>
                                        <div class="ranking-score text-success"><?= number_format($category['score'], 1) ?>/5.0</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <hr class="my-3">

                    <!-- Bottom 3 -->
                    <h6 class="text-danger mb-3">
                        <i class="fas fa-exclamation-circle"></i>
                        Oportunidades de Mejora
                    </h6>
                    <div class="ranking-list">
                        <?php if (isset($executive_summary['bottom_categories'])): ?>
                            <?php foreach (array_slice($executive_summary['bottom_categories'], 0, 3) as $index => $category): ?>
                                <div class="ranking-item">
                                    <div class="ranking-number bg-danger text-white"><?= $index + 1 ?></div>
                                    <div class="ranking-content">
                                        <div class="ranking-name"><?= htmlspecialchars($category['name']) ?></div>
                                        <div class="ranking-score text-danger"><?= number_format($category['score'], 1) ?>/5.0</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recomendaciones ejecutivas -->
    <div class="row">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i>
                        Recomendaciones Estratégicas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Recomendaciones inmediatas -->
                        <div class="col-md-4">
                            <div class="recommendation-section">
                                <h6 class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Acción Inmediata
                                </h6>
                                <div class="recommendation-list">
                                    <?php
                                    $immediate_actions = [
                                        'Abordar la categoría más baja: ' . ($executive_summary['worst_category']['name'] ?? 'N/A'),
                                        'Implementar plan de comunicación',
                                        'Establecer reuniones departamentales'
                                    ];
                                    ?>
                                    <?php foreach ($immediate_actions as $action): ?>
                                        <div class="recommendation-item urgent">
                                            <i class="fas fa-clock text-danger"></i>
                                            <span><?= $action ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recomendaciones a mediano plazo -->
                        <div class="col-md-4">
                            <div class="recommendation-section">
                                <h6 class="text-warning">
                                    <i class="fas fa-calendar-alt"></i>
                                    Mediano Plazo (1-3 meses)
                                </h6>
                                <div class="recommendation-list">
                                    <?php
                                    $medium_term_actions = [
                                        'Programa de desarrollo de liderazgo',
                                        'Revisión de políticas internas',
                                        'Implementar feedback 360°'
                                    ];
                                    ?>
                                    <?php foreach ($medium_term_actions as $action): ?>
                                        <div class="recommendation-item medium">
                                            <i class="fas fa-calendar text-warning"></i>
                                            <span><?= $action ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recomendaciones estratégicas -->
                        <div class="col-md-4">
                            <div class="recommendation-section">
                                <h6 class="text-info">
                                    <i class="fas fa-chess"></i>
                                    Estratégico (3-12 meses)
                                </h6>
                                <div class="recommendation-list">
                                    <?php
                                    $strategic_actions = [
                                        'Rediseño de cultura organizacional',
                                        'Programa de bienestar integral',
                                        'Sistema de reconocimiento formal'
                                    ];
                                    ?>
                                    <?php foreach ($strategic_actions as $action): ?>
                                        <div class="recommendation-item strategic">
                                            <i class="fas fa-flag text-info"></i>
                                            <span><?= $action ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROI esperado -->
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="alert-heading mb-2">
                                        <i class="fas fa-chart-line"></i>
                                        Impacto Esperado de las Acciones
                                    </h6>
                                    <p class="mb-0">
                                        La implementación de estas recomendaciones puede generar una mejora esperada de 
                                        <strong>0.5-0.8 puntos</strong> en la satisfacción general en un período de 6-12 meses, 
                                        lo que se traduce en mayor retención de talento y productividad.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="roi-metric">
                                        <div class="roi-value h3 text-success mb-0">15-25%</div>
                                        <div class="roi-label small">Mejora esperada en retención</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximos pasos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks"></i>
                        Plan de Acción Propuesto
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Semana 1-2</h6>
                                <p class="timeline-description">
                                    Presentación de resultados a equipos directivos y comunicación inicial a colaboradores.
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Mes 1</h6>
                                <p class="timeline-description">
                                    Implementación de acciones inmediatas en las áreas más críticas identificadas.
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Mes 2-3</h6>
                                <p class="timeline-description">
                                    Desarrollo e implementación de programas de mediano plazo y capacitaciones.
                                </p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Mes 6</h6>
                                <p class="timeline-description">
                                    Evaluación de progreso y ajustes. Preparación para la siguiente medición.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    createExecutiveChart();
});

function createExecutiveChart() {
    const ctx = document.getElementById('executiveChart').getContext('2d');
    
    // Datos de ejemplo - reemplazar con datos reales
    const categoryData = <?= json_encode($executive_summary['categories'] ?? []) ?>;
    
    new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: categoryData.map(cat => cat.name.length > 20 ? cat.name.substring(0, 20) + '...' : cat.name),
            datasets: [{
                label: 'Satisfacción',
                data: categoryData.map(cat => cat.score),
                backgroundColor: categoryData.map(cat => {
                    if (cat.score >= 4) return 'rgba(40, 167, 69, 0.8)';
                    if (cat.score >= 3.5) return 'rgba(23, 162, 184, 0.8)';
                    if (cat.score >= 3) return 'rgba(255, 193, 7, 0.8)';
                    return 'rgba(220, 53, 69, 0.8)';
                }),
                borderColor: categoryData.map(cat => {
                    if (cat.score >= 4) return 'rgba(40, 167, 69, 1)';
                    if (cat.score >= 3.5) return 'rgba(23, 162, 184, 1)';
                    if (cat.score >= 3) return 'rgba(255, 193, 7, 1)';
                    return 'rgba(220, 53, 69, 1)';
                }),
                borderWidth: 1
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
                x: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}
</script>

<style>
.executive-summary-card {
    margin-bottom: 2rem;
}

.metric-large {
    text-align: center;
    padding: 1.5rem;
}

.metric-icon i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.participation-summary {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}

.survey-info {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.375rem;
    height: 100%;
}

.info-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.info-item i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

.executive-kpi-card {
    margin-bottom: 1.5rem;
    transition: transform 0.2s;
}

.executive-kpi-card:hover {
    transform: translateY(-2px);
}

.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.kpi-icon {
    flex-shrink: 0;
}

.kpi-content {
    flex-grow: 1;
}

.kpi-title {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.kpi-value {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.kpi-score {
    font-size: 0.875rem;
    color: #6c757d;
}

.ranking-list {
    margin-bottom: 1rem;
}

.ranking-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}

.ranking-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.875rem;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.ranking-content {
    flex-grow: 1;
}

.ranking-name {
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.ranking-score {
    font-size: 0.8rem;
    font-weight: 600;
}

.recommendation-section {
    margin-bottom: 1.5rem;
}

.recommendation-list {
    margin-top: 1rem;
}

.recommendation-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 0.75rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    border-left: 3px solid;
}

.recommendation-item.urgent {
    border-left-color: #dc3545;
}

.recommendation-item.medium {
    border-left-color: #ffc107;
}

.recommendation-item.strategic {
    border-left-color: #17a2b8;
}

.recommendation-item i {
    margin-right: 0.75rem;
    margin-top: 0.125rem;
}

.roi-metric {
    text-align: center;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-left: 1rem;
}

.timeline-title {
    margin-bottom: 0.5rem;
    color: #495057;
}

.timeline-description {
    margin-bottom: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

@media print {
    .page-actions,
    .btn,
    .dropdown {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        margin-bottom: 1rem;
        box-shadow: none;
        border: 1px solid #dee2e6;
    }
    
    .executive-summary-card,
    .executive-kpi-card {
        margin-bottom: 1rem;
    }
    
    .timeline:before {
        display: none;
    }
    
    .timeline-marker {
        position: relative;
        left: 0;
        margin-right: 0.5rem;
        display: inline-block;
    }
    
    .timeline-content {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .metric-large {
        padding: 1rem;
    }
    
    .metric-icon i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .executive-kpi-card .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .kpi-icon {
        margin-bottom: 1rem;
    }
}
</style>