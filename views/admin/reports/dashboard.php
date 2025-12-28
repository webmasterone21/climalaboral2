<?php
// views/admin/reports/dashboard.php
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Dashboard - <?= htmlspecialchars($survey['title']) ?></h1>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item active">Dashboard</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>admin/reports/detailed/<?= $survey['id'] ?>" class="btn btn-info">
                                <i class="fas fa-file-alt"></i> Reporte Detallado
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/pdf/<?= $survey['id'] ?>">
                                        <i class="fas fa-file-pdf text-danger"></i> PDF
                                    </a>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/excel/<?= $survey['id'] ?>">
                                        <i class="fas fa-file-excel text-success"></i> Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de la encuesta -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <?php if ($survey['logo']): ?>
                                <img src="<?= BASE_URL ?>uploads/logos/<?= $survey['logo'] ?>" 
                                     alt="Logo" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                    <i class="fas fa-building fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="mb-1"><?= htmlspecialchars($survey['title']) ?></h5>
                                    <p class="text-muted mb-2"><?= htmlspecialchars($survey['description']) ?></p>
                                    <div class="survey-meta">
                                        <span class="badge badge-outline-info me-2">
                                            <i class="fas fa-building"></i> <?= htmlspecialchars($survey['company_name']) ?>
                                        </span>
                                        <span class="badge badge-outline-secondary me-2">
                                            <i class="fas fa-calendar"></i> 
                                            <?= date('d/m/Y', strtotime($survey['start_date'])) ?> - 
                                            <?= date('d/m/Y', strtotime($survey['end_date'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <?php
                                    $participation_rate = $stats['total_participants'] > 0 
                                        ? round(($stats['completed_responses'] / $stats['total_participants']) * 100, 1) 
                                        : 0;
                                    ?>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= $participation_rate ?>%"
                                             aria-valuenow="<?= $participation_rate ?>" 
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?= $stats['completed_responses'] ?> de <?= $stats['total_participants'] ?> completadas (<?= $participation_rate ?>%)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h3 mb-0"><?= $stats['total_participants'] ?></div>
                            <div class="small">Total Participantes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h3 mb-0"><?= $stats['completed_responses'] ?></div>
                            <div class="small">Completadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h3 mb-0"><?= $stats['in_progress'] ?></div>
                            <div class="small">En Progreso</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-star fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="h3 mb-0"><?= number_format($stats['overall_satisfaction'], 1) ?></div>
                            <div class="small">Satisfacción General</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de barras por categorías -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar text-primary"></i>
                        Satisfacción por Categoría
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="400"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribución de respuestas -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-pie-chart text-success"></i>
                        Distribución de Estado
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Comparación por departamentos -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building text-info"></i>
                        Comparación por Departamentos
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top categorías mejor y peor calificadas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-thumbs-up"></i>
                        Mejores Aspectos
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (isset($categoryStats)): ?>
                            <?php 
                            // Ordenar por promedio más alto
                            usort($categoryStats, function($a, $b) {
                                return $b['average'] <=> $a['average'];
                            });
                            $topCategories = array_slice($categoryStats, 0, 5);
                            ?>
                            <?php foreach ($topCategories as $index => $category): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($category['name']) ?></div>
                                            <small class="text-muted"><?= $category['response_count'] ?> respuestas</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="h5 mb-0 text-success">
                                                <?= number_format($category['average'], 2) ?> 
                                                <small class="text-muted">/ 5</small>
                                            </div>
                                            <div class="progress" style="width: 100px; height: 6px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: <?= ($category['average'] / 5) * 100 ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-thumbs-down"></i>
                        Áreas de Mejora
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (isset($categoryStats)): ?>
                            <?php 
                            // Ordenar por promedio más bajo
                            usort($categoryStats, function($a, $b) {
                                return $a['average'] <=> $b['average'];
                            });
                            $bottomCategories = array_slice($categoryStats, 0, 5);
                            ?>
                            <?php foreach ($bottomCategories as $category): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($category['name']) ?></div>
                                            <small class="text-muted"><?= $category['response_count'] ?> respuestas</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="h5 mb-0 text-danger">
                                                <?= number_format($category['average'], 2) ?> 
                                                <small class="text-muted">/ 5</small>
                                            </div>
                                            <div class="progress" style="width: 100px; height: 6px;">
                                                <div class="progress-bar bg-danger" 
                                                     style="width: <?= ($category['average'] / 5) * 100 ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de comentarios y respuestas abiertas -->
    <?php if (isset($stats['text_responses']) && $stats['text_responses'] > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments text-warning"></i>
                        Comentarios y Respuestas Abiertas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Se recibieron <strong><?= $stats['text_responses'] ?></strong> comentarios adicionales. 
                        <a href="<?= BASE_URL ?>admin/reports/detailed/<?= $survey['id'] ?>">
                            Ver análisis detallado de comentarios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Scripts para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración común
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#6c757d';

    // Gráfico de categorías
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = <?= json_encode($categoryStats) ?>;
    
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryData.map(cat => cat.name),
            datasets: [{
                label: 'Promedio de Satisfacción',
                data: categoryData.map(cat => cat.average),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Promedio: ' + context.parsed.y.toFixed(2) + '/5';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            }
        }
    });

    // Gráfico de estado de participación
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completadas', 'En Progreso', 'Sin Iniciar'],
            datasets: [{
                data: [
                    <?= $stats['completed_responses'] ?>,
                    <?= $stats['in_progress'] ?>,
                    <?= $stats['total_participants'] - $stats['completed_responses'] - $stats['in_progress'] ?>
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#6c757d'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Gráfico de departamentos
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    const departmentData = <?= json_encode($departmentStats) ?>;
    
    new Chart(departmentCtx, {
        type: 'horizontalBar',
        data: {
            labels: departmentData.map(dept => dept.name),
            datasets: [{
                label: 'Participación',
                data: departmentData.map(dept => dept.participation_rate),
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 1
            }, {
                label: 'Satisfacción Promedio',
                data: departmentData.map(dept => dept.average_satisfaction * 20), // Escalar para visualización
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 0) {
                                return 'Participación: ' + context.parsed.x + '%';
                            } else {
                                return 'Satisfacción: ' + (context.parsed.x / 20).toFixed(2) + '/5';
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
});

// Actualizar datos cada 30 segundos
setInterval(function() {
    fetch(`<?= BASE_URL ?>admin/reports/api/dashboard-data/<?= $survey['id'] ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar KPIs
                location.reload(); // Recargar para simplicidad, se puede mejorar con AJAX
            }
        })
        .catch(error => console.error('Error updating data:', error));
}, 30000);
</script>

<style>
.badge-outline-info {
    color: #17a2b8;
    border: 1px solid #17a2b8;
    background: transparent;
}

.badge-outline-secondary {
    color: #6c757d;
    border: 1px solid #6c757d;
    background: transparent;
}

.opacity-75 {
    opacity: 0.75;
}

.survey-meta .badge {
    font-size: 0.75rem;
}

.progress {
    border-radius: 4px;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>