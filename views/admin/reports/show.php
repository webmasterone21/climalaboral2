<?php
// views/admin/reports/show.php (Reporte Detallado)
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Reporte Detallado</h1>
                        <h5 class="text-muted"><?= htmlspecialchars($survey['title']) ?></h5>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports/dashboard/<?= $survey['id'] ?>">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Detallado</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="btn-group">
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/pdf/<?= $survey['id'] ?>">
                                        <i class="fas fa-file-pdf text-danger"></i> Exportar PDF
                                    </a>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/excel/<?= $survey['id'] ?>">
                                        <i class="fas fa-file-excel text-success"></i> Exportar Excel
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>admin/reports/export/raw/<?= $survey['id'] ?>">
                                        <i class="fas fa-database text-info"></i> Datos Brutos (CSV)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen ejecutivo -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i>
                        Resumen Ejecutivo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-3">Hallazgos Principales</h6>
                            <div class="executive-summary">
                                <?php
                                $overall_score = $demographics['overall_satisfaction'] ?? 0;
                                $participation_rate = ($demographics['completed'] / $demographics['total_invited']) * 100;
                                ?>
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="metric">
                                                <div class="metric-value text-primary h3 mb-0">
                                                    <?= number_format($overall_score, 1) ?>/5.0
                                                </div>
                                                <div class="metric-label text-muted small">
                                                    Satisfacción General
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="metric">
                                                <div class="metric-value text-success h3 mb-0">
                                                    <?= number_format($participation_rate, 1) ?>%
                                                </div>
                                                <div class="metric-label text-muted small">
                                                    Tasa de Participación
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-light">
                                    <i class="fas fa-info-circle text-info"></i>
                                    <strong>Interpretación:</strong>
                                    <?php if ($overall_score >= 4.0): ?>
                                        La satisfacción laboral es <strong>excelente</strong>. Los empleados muestran un alto nivel de compromiso y bienestar.
                                    <?php elseif ($overall_score >= 3.5): ?>
                                        La satisfacción laboral es <strong>buena</strong>. Hay oportunidades de mejora en algunas áreas específicas.
                                    <?php elseif ($overall_score >= 3.0): ?>
                                        La satisfacción laboral es <strong>moderada</strong>. Se requiere atención en múltiples aspectos del clima laboral.
                                    <?php else: ?>
                                        La satisfacción laboral es <strong>baja</strong>. Se recomienda implementar medidas correctivas urgentes.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="satisfaction-gauge">
                                <canvas id="satisfactionGauge" width="200" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de análisis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="department-filter">Filtrar por Departamento</label>
                                <select id="department-filter" class="form-control">
                                    <option value="">Todos los departamentos</option>
                                    <?php if (isset($demographics['departments'])): ?>
                                        <?php foreach ($demographics['departments'] as $dept): ?>
                                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category-filter">Filtrar por Categoría</label>
                                <select id="category-filter" class="form-control">
                                    <option value="">Todas las categorías</option>
                                    <?php if (isset($questions)): ?>
                                        <?php
                                        $categories = array_unique(array_column($questions, 'category_name'));
                                        foreach ($categories as $category):
                                        ?>
                                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="question-search">Buscar Pregunta</label>
                                <input type="text" id="question-search" class="form-control" placeholder="Buscar en preguntas...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button id="clear-filters" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis por preguntas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-alt text-info"></i>
                        Análisis Detallado por Pregunta
                    </h5>
                </div>
                <div class="card-body">
                    <div id="questions-analysis">
                        <?php if (isset($questions) && !empty($questions)): ?>
                            <?php foreach ($questions as $question): ?>
                                <div class="question-analysis-item" 
                                     data-category="<?= htmlspecialchars($question['category_name']) ?>"
                                     data-department="<?= $question['department_id'] ?? '' ?>">
                                    
                                    <div class="question-header">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h6 class="question-title"><?= htmlspecialchars($question['question_text']) ?></h6>
                                                <div class="question-meta">
                                                    <span class="badge badge-outline-primary">
                                                        <?= htmlspecialchars($question['category_name']) ?>
                                                    </span>
                                                    <span class="badge badge-outline-secondary">
                                                        <?= $question['response_count'] ?> respuestas
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="question-score">
                                                    <div class="score-value h4 mb-0">
                                                        <?= number_format($question['average'], 2) ?>/5
                                                    </div>
                                                    <div class="score-bar">
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar bg-<?= $question['average'] >= 4 ? 'success' : ($question['average'] >= 3 ? 'warning' : 'danger') ?>" 
                                                                 style="width: <?= ($question['average'] / 5) * 100 ?>%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="question-details">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <!-- Gráfico de distribución -->
                                                <div class="distribution-chart">
                                                    <h6>Distribución de Respuestas</h6>
                                                    <canvas id="chart-<?= $question['id'] ?>" height="200"></canvas>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <!-- Estadísticas -->
                                                <div class="question-stats">
                                                    <h6>Estadísticas</h6>
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td>Promedio:</td>
                                                            <td class="text-end"><strong><?= number_format($question['average'], 2) ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Mediana:</td>
                                                            <td class="text-end"><strong><?= $question['median'] ?? 'N/A' ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Desviación Estándar:</td>
                                                            <td class="text-end"><strong><?= number_format($question['std_dev'] ?? 0, 2) ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Respuestas Positivas:</td>
                                                            <td class="text-end">
                                                                <strong class="text-success">
                                                                    <?= $question['positive_responses'] ?>%
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Respuestas Negativas:</td>
                                                            <td class="text-end">
                                                                <strong class="text-danger">
                                                                    <?= $question['negative_responses'] ?>%
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Análisis por departamento si hay más de uno -->
                                        <?php if (isset($question['department_breakdown']) && count($question['department_breakdown']) > 1): ?>
                                        <div class="department-breakdown mt-3">
                                            <h6>Análisis por Departamento</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Departamento</th>
                                                            <th class="text-center">Respuestas</th>
                                                            <th class="text-center">Promedio</th>
                                                            <th class="text-center">Comparación</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($question['department_breakdown'] as $dept): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($dept['name']) ?></td>
                                                            <td class="text-center"><?= $dept['responses'] ?></td>
                                                            <td class="text-center">
                                                                <strong><?= number_format($dept['average'], 2) ?></strong>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php 
                                                                $diff = $dept['average'] - $question['average'];
                                                                $arrow = $diff > 0 ? '↗' : ($diff < 0 ? '↘' : '→');
                                                                $color = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted');
                                                                ?>
                                                                <span class="<?= $color ?>">
                                                                    <?= $arrow ?> <?= number_format(abs($diff), 2) ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Comentarios asociados si existen -->
                                        <?php if (isset($question['comments']) && !empty($question['comments'])): ?>
                                        <div class="question-comments mt-3">
                                            <h6>Comentarios Asociados <span class="badge badge-secondary"><?= count($question['comments']) ?></span></h6>
                                            <div class="comments-container">
                                                <?php foreach (array_slice($question['comments'], 0, 3) as $comment): ?>
                                                    <div class="comment-item">
                                                        <blockquote class="blockquote-sm">
                                                            <p class="mb-1">"<?= htmlspecialchars($comment['text']) ?>"</p>
                                                            <footer class="blockquote-footer">
                                                                Participante <?= $comment['participant_id'] ?> - 
                                                                <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                                                            </footer>
                                                        </blockquote>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($question['comments']) > 3): ?>
                                                    <button class="btn btn-link btn-sm" onclick="showAllComments(<?= $question['id'] ?>)">
                                                        Ver todos los comentarios (<?= count($question['comments']) ?>)
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <hr class="my-4">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay datos suficientes para el análisis</h5>
                                <p class="text-muted">Se necesitan más respuestas para generar estadísticas significativas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis de comentarios libres -->
    <?php if (isset($demographics['text_responses']) && !empty($demographics['text_responses'])): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments text-warning"></i>
                        Análisis de Comentarios Libres
                    </h5>
                </div>
                <div class="card-body">
                    <div class="comments-analysis">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="metric-card">
                                    <div class="metric-value h3 text-info"><?= count($demographics['text_responses']) ?></div>
                                    <div class="metric-label">Total Comentarios</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card">
                                    <div class="metric-value h3 text-success">
                                        <?= isset($demographics['positive_comments']) ? $demographics['positive_comments'] : '0' ?>
                                    </div>
                                    <div class="metric-label">Comentarios Positivos</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card">
                                    <div class="metric-value h3 text-danger">
                                        <?= isset($demographics['negative_comments']) ? $demographics['negative_comments'] : '0' ?>
                                    </div>
                                    <div class="metric-label">Comentarios Negativos</div>
                                </div>
                            </div>
                        </div>

                        <!-- Muestra de comentarios -->
                        <div class="comments-sample">
                            <h6>Muestra de Comentarios</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-success">Comentarios Positivos</h6>
                                    <div class="comments-list">
                                        <?php 
                                        $positive_comments = array_filter($demographics['text_responses'], function($comment) {
                                            return isset($comment['sentiment']) && $comment['sentiment'] === 'positive';
                                        });
                                        $positive_sample = array_slice($positive_comments, 0, 3);
                                        ?>
                                        <?php foreach ($positive_sample as $comment): ?>
                                            <div class="comment-card border-left-success">
                                                <p class="mb-1"><?= htmlspecialchars($comment['text']) ?></p>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($comment['created_at'])) ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-danger">Áreas de Mejora Mencionadas</h6>
                                    <div class="comments-list">
                                        <?php 
                                        $negative_comments = array_filter($demographics['text_responses'], function($comment) {
                                            return isset($comment['sentiment']) && $comment['sentiment'] === 'negative';
                                        });
                                        $negative_sample = array_slice($negative_comments, 0, 3);
                                        ?>
                                        <?php foreach ($negative_sample as $comment): ?>
                                            <div class="comment-card border-left-danger">
                                                <p class="mb-1"><?= htmlspecialchars($comment['text']) ?></p>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($comment['created_at'])) ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recomendaciones -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i>
                        Recomendaciones y Acciones Sugeridas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="recommendations">
                        <?php
                        // Generar recomendaciones basadas en los datos
                        $recommendations = [];
                        
                        if ($overall_score < 3.5) {
                            $recommendations[] = [
                                'type' => 'urgent',
                                'title' => 'Acción Inmediata Requerida',
                                'description' => 'El nivel de satisfacción general está por debajo del promedio. Se recomienda implementar un plan de acción inmediato para abordar las principales áreas de preocupación.'
                            ];
                        }
                        
                        if ($participation_rate < 70) {
                            $recommendations[] = [
                                'type' => 'important',
                                'title' => 'Mejorar Participación',
                                'description' => 'La tasa de participación es baja. Considere estrategias de comunicación más efectivas y incentivos para aumentar la participación en futuras encuestas.'
                            ];
                        }
                        
                        $recommendations[] = [
                            'type' => 'general',
                            'title' => 'Seguimiento Regular',
                            'description' => 'Implemente encuestas de seguimiento trimestrales para monitorear el progreso y mantener el pulso del clima laboral.'
                        ];
                        
                        $recommendations[] = [
                            'type' => 'general',
                            'title' => 'Comunicación de Resultados',
                            'description' => 'Comparta los resultados principales con todos los empleados y comunique las acciones que se tomarán basadas en sus comentarios.'
                        ];
                        ?>
                        
                        <?php foreach ($recommendations as $rec): ?>
                            <div class="recommendation-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <?php if ($rec['type'] === 'urgent'): ?>
                                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                                        <?php elseif ($rec['type'] === 'important'): ?>
                                            <i class="fas fa-exclamation-circle text-warning fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-info-circle text-info fa-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?= $rec['title'] ?></h6>
                                        <p class="mb-0 text-muted"><?= $rec['description'] ?></p>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración de gráficos
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#6c757d';

    // Gauge de satisfacción general
    const gaugeCtx = document.getElementById('satisfactionGauge');
    if (gaugeCtx) {
        const satisfactionScore = <?= $overall_score ?>;
        createSatisfactionGauge(gaugeCtx, satisfactionScore);
    }

    // Gráficos para cada pregunta
    <?php if (isset($questions)): ?>
        <?php foreach ($questions as $question): ?>
            <?php if (isset($question['distribution'])): ?>
                createDistributionChart('chart-<?= $question['id'] ?>', <?= json_encode($question['distribution']) ?>);
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    // Filtros
    setupFilters();
});

function createSatisfactionGauge(ctx, score) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [score, 5 - score],
                backgroundColor: [
                    score >= 4 ? '#28a745' : score >= 3 ? '#ffc107' : '#dc3545',
                    '#e9ecef'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            }
        },
        plugins: [{
            beforeDraw: function(chart) {
                const width = chart.width;
                const height = chart.height;
                const ctx = chart.ctx;
                
                ctx.restore();
                const fontSize = (height / 114).toFixed(2);
                ctx.font = fontSize + "em Inter";
                ctx.textBaseline = "middle";
                ctx.fillStyle = "#333";
                
                const text = score.toFixed(1);
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 2;
                
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
    });
}

function createDistributionChart(canvasId, distribution) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: distribution.labels,
            datasets: [{
                data: distribution.values,
                backgroundColor: [
                    '#dc3545', // Muy en desacuerdo
                    '#fd7e14', // En desacuerdo
                    '#ffc107', // Neutral
                    '#20c997', // De acuerdo
                    '#28a745'  // Muy de acuerdo
                ],
                borderWidth: 1,
                borderColor: '#fff'
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
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function setupFilters() {
    const departmentFilter = document.getElementById('department-filter');
    const categoryFilter = document.getElementById('category-filter');
    const questionSearch = document.getElementById('question-search');
    const clearFilters = document.getElementById('clear-filters');
    
    function applyFilters() {
        const departmentValue = departmentFilter.value;
        const categoryValue = categoryFilter.value.toLowerCase();
        const searchValue = questionSearch.value.toLowerCase();
        
        const questions = document.querySelectorAll('.question-analysis-item');
        
        questions.forEach(question => {
            const questionCategory = question.dataset.category.toLowerCase();
            const questionDept = question.dataset.department;
            const questionText = question.querySelector('.question-title').textContent.toLowerCase();
            
            const categoryMatch = !categoryValue || questionCategory.includes(categoryValue);
            const departmentMatch = !departmentValue || questionDept === departmentValue;
            const searchMatch = !searchValue || questionText.includes(searchValue);
            
            if (categoryMatch && departmentMatch && searchMatch) {
                question.style.display = 'block';
            } else {
                question.style.display = 'none';
            }
        });
    }
    
    departmentFilter.addEventListener('change', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);
    questionSearch.addEventListener('input', applyFilters);
    
    clearFilters.addEventListener('click', function() {
        departmentFilter.value = '';
        categoryFilter.value = '';
        questionSearch.value = '';
        applyFilters();
    });
}

function showAllComments(questionId) {
    // Implementar modal o expandir comentarios
    alert('Función para mostrar todos los comentarios - Por implementar');
}
</script>

<style>
.badge-outline-primary {
    color: #007bff;
    border: 1px solid #007bff;
    background: transparent;
}

.badge-outline-secondary {
    color: #6c757d;
    border: 1px solid #6c757d;
    background: transparent;
}

.question-analysis-item {
    margin-bottom: 2rem;
}

.question-header {
    margin-bottom: 1rem;
}

.question-title {
    color: #495057;
    font-weight: 500;
}

.question-meta .badge {
    margin-right: 0.5rem;
}

.question-score {
    text-align: center;
}

.score-value {
    font-weight: 600;
}

.score-bar {
    margin-top: 0.5rem;
}

.distribution-chart {
    height: 200px;
}

.question-stats {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}

.comment-item {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
}

.blockquote-sm {
    font-size: 0.9rem;
}

.metric-card {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
}

.metric-value {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.metric-label {
    color: #6c757d;
    font-size: 0.875rem;
}

.comment-card {
    background: #fff;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 0.75rem;
    border-left: 3px solid;
}

.border-left-success {
    border-left-color: #28a745 !important;
}

.border-left-danger {
    border-left-color: #dc3545 !important;
}

.recommendation-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.executive-summary .metric {
    text-align: center;
    padding: 1rem;
}

@media print {
    .page-actions,
    .btn-group,
    button {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        margin-bottom: 1rem;
    }
    
    .question-analysis-item {
        break-inside: avoid;
        margin-bottom: 1.5rem;
    }
}
</style>