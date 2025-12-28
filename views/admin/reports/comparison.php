<?php
// views/admin/reports/comparison.php - Comparación entre encuestas
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Comparación de Encuestas</h1>
                        <p class="text-muted">Análisis comparativo entre diferentes encuestas de clima laboral</p>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item active">Comparación</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <button onclick="exportComparison()" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Exportar Comparación
                        </button>
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de encuestas para comparar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog text-primary"></i>
                        Configuración de Comparación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="survey1-select">Encuesta Base</label>
                                <select id="survey1-select" class="form-control">
                                    <option value="">Seleccionar encuesta...</option>
                                    <?php if (isset($surveys)): ?>
                                        <?php foreach ($surveys as $survey): ?>
                                            <option value="<?= $survey['id'] ?>" <?= isset($survey1) && $survey1['id'] == $survey['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($survey['title']) ?> 
                                                (<?= date('m/Y', strtotime($survey['created_at'])) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="survey2-select">Encuesta Comparativa</label>
                                <select id="survey2-select" class="form-control">
                                    <option value="">Seleccionar encuesta...</option>
                                    <?php if (isset($surveys)): ?>
                                        <?php foreach ($surveys as $survey): ?>
                                            <option value="<?= $survey['id'] ?>" <?= isset($survey2) && $survey2['id'] == $survey['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($survey['title']) ?>
                                                (<?= date('m/Y', strtotime($survey['created_at'])) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="comparison-type">Tipo</label>
                                <select id="comparison-type" class="form-control">
                                    <option value="categories">Por Categorías</option>
                                    <option value="departments">Por Departamentos</option>
                                    <option value="questions">Por Preguntas</option>
                                    <option value="timeline">Línea de Tiempo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button id="compare-btn" class="btn btn-primary btn-block">
                                    <i class="fas fa-balance-scale"></i> Comparar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($survey1) && isset($survey2)): ?>
    <!-- Información de las encuestas comparadas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-poll"></i>
                        Encuesta Base
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="mb-2"><?= htmlspecialchars($survey1['title']) ?></h5>
                    <div class="survey-meta">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Empresa:</small><br>
                                <strong><?= htmlspecialchars($survey1['company_name']) ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Período:</small><br>
                                <strong><?= date('d/m/Y', strtotime($survey1['start_date'])) ?> - <?= date('d/m/Y', strtotime($survey1['end_date'])) ?></strong>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-4">
                                <small class="text-muted">Participantes:</small><br>
                                <strong class="text-info"><?= $comparison_data['survey1']['participants'] ?></strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Completadas:</small><br>
                                <strong class="text-success"><?= $comparison_data['survey1']['completed'] ?></strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Satisfacción:</small><br>
                                <strong class="text-primary"><?= number_format($comparison_data['survey1']['overall_satisfaction'], 1) ?>/5</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-poll"></i>
                        Encuesta Comparativa
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="mb-2"><?= htmlspecialchars($survey2['title']) ?></h5>
                    <div class="survey-meta">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Empresa:</small><br>
                                <strong><?= htmlspecialchars($survey2['company_name']) ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Período:</small><br>
                                <strong><?= date('d/m/Y', strtotime($survey2['start_date'])) ?> - <?= date('d/m/Y', strtotime($survey2['end_date'])) ?></strong>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-4">
                                <small class="text-muted">Participantes:</small><br>
                                <strong class="text-info"><?= $comparison_data['survey2']['participants'] ?></strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Completadas:</small><br>
                                <strong class="text-success"><?= $comparison_data['survey2']['completed'] ?></strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Satisfacción:</small><br>
                                <strong class="text-success"><?= number_format($comparison_data['survey2']['overall_satisfaction'], 1) ?>/5</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas de comparación rápida -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card <?= $comparison_data['metrics']['satisfaction_change'] > 0 ? 'border-success' : 'border-danger' ?>">
                <div class="card-body text-center">
                    <i class="fas fa-heart fa-2x mb-2 <?= $comparison_data['metrics']['satisfaction_change'] > 0 ? 'text-success' : 'text-danger' ?>"></i>
                    <h4 class="<?= $comparison_data['metrics']['satisfaction_change'] > 0 ? 'text-success' : 'text-danger' ?>">
                        <?= ($comparison_data['metrics']['satisfaction_change'] > 0 ? '+' : '') . number_format($comparison_data['metrics']['satisfaction_change'], 2) ?>
                    </h4>
                    <small class="text-muted">Cambio en Satisfacción</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card <?= $comparison_data['metrics']['participation_change'] > 0 ? 'border-success' : 'border-warning' ?>">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2 <?= $comparison_data['metrics']['participation_change'] > 0 ? 'text-success' : 'text-warning' ?>"></i>
                    <h4 class="<?= $comparison_data['metrics']['participation_change'] > 0 ? 'text-success' : 'text-warning' ?>">
                        <?= ($comparison_data['metrics']['participation_change'] > 0 ? '+' : '') . number_format($comparison_data['metrics']['participation_change'], 1) ?>%
                    </h4>
                    <small class="text-muted">Cambio en Participación</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                    <h4 class="text-info">
                        <?= $comparison_data['metrics']['improved_categories'] ?>
                    </h4>
                    <small class="text-muted">Categorías Mejoradas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">
                        <?= $comparison_data['metrics']['declined_categories'] ?>
                    </h4>
                    <small class="text-muted">Categorías Declinadas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de comparación principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar text-primary"></i>
                        Comparación por Categorías
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="comparisonChart" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de comparación detallada -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table text-info"></i>
                        Análisis Detallado de Cambios
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Categoría</th>
                                    <th class="text-center">Encuesta Base</th>
                                    <th class="text-center">Encuesta Comparativa</th>
                                    <th class="text-center">Diferencia</th>
                                    <th class="text-center">% Cambio</th>
                                    <th class="text-center">Tendencia</th>
                                    <th class="text-center">Significancia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($comparison_data['categories'])): ?>
                                    <?php foreach ($comparison_data['categories'] as $category): ?>
                                        <?php
                                        $difference = $category['survey2_avg'] - $category['survey1_avg'];
                                        $percentage_change = $category['survey1_avg'] > 0 ? ($difference / $category['survey1_avg']) * 100 : 0;
                                        $is_significant = abs($difference) >= 0.3; // Considerar significativo si la diferencia es >= 0.3 puntos
                                        ?>
                                        <tr class="<?= abs($difference) >= 0.5 ? ($difference > 0 ? 'table-success' : 'table-danger') : '' ?>">
                                            <td class="font-weight-bold"><?= htmlspecialchars($category['name']) ?></td>
                                            
                                            <td class="text-center">
                                                <span class="badge badge-primary">
                                                    <?= number_format($category['survey1_avg'], 2) ?>
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="badge badge-success">
                                                    <?= number_format($category['survey2_avg'], 2) ?>
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="font-weight-bold <?= $difference > 0 ? 'text-success' : ($difference < 0 ? 'text-danger' : 'text-muted') ?>">
                                                    <?= ($difference > 0 ? '+' : '') . number_format($difference, 2) ?>
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="<?= $percentage_change > 0 ? 'text-success' : ($percentage_change < 0 ? 'text-danger' : 'text-muted') ?>">
                                                    <?= ($percentage_change > 0 ? '+' : '') . number_format($percentage_change, 1) ?>%
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <?php if ($difference > 0.3): ?>
                                                    <i class="fas fa-arrow-up text-success fa-lg" title="Mejora significativa"></i>
                                                <?php elseif ($difference > 0.1): ?>
                                                    <i class="fas fa-arrow-up text-info" title="Mejora ligera"></i>
                                                <?php elseif ($difference < -0.3): ?>
                                                    <i class="fas fa-arrow-down text-danger fa-lg" title="Declive significativo"></i>
                                                <?php elseif ($difference < -0.1): ?>
                                                    <i class="fas fa-arrow-down text-warning" title="Declive ligero"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-minus text-muted" title="Sin cambio significativo"></i>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="text-center">
                                                <?php if ($is_significant): ?>
                                                    <span class="badge badge-warning">Significativo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light">No significativo</span>
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

    <!-- Análisis de insights -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i>
                        Insights y Recomendaciones de la Comparación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">
                                <i class="fas fa-thumbs-up"></i>
                                Principales Mejoras
                            </h6>
                            <ul class="list-unstyled">
                                <?php if (isset($comparison_data['insights']['improvements'])): ?>
                                    <?php foreach ($comparison_data['insights']['improvements'] as $improvement): ?>
                                        <li class="mb-2">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                                                <div>
                                                    <strong><?= htmlspecialchars($improvement['category']) ?></strong>
                                                    <div class="small text-muted">
                                                        Mejora de <?= number_format($improvement['improvement'], 2) ?> puntos 
                                                        (<?= number_format($improvement['percentage'], 1) ?>%)
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                Áreas de Atención
                            </h6>
                            <ul class="list-unstyled">
                                <?php if (isset($comparison_data['insights']['declines'])): ?>
                                    <?php foreach ($comparison_data['insights']['declines'] as $decline): ?>
                                        <li class="mb-2">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-exclamation-circle text-danger mt-1 me-2"></i>
                                                <div>
                                                    <strong><?= htmlspecialchars($decline['category']) ?></strong>
                                                    <div class="small text-muted">
                                                        Declive de <?= number_format(abs($decline['decline']), 2) ?> puntos 
                                                        (<?= number_format($decline['percentage'], 1) ?>%)
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="text-primary">
                            <i class="fas fa-chart-line"></i>
                            Resumen Ejecutivo
                        </h6>
                        <div class="alert alert-light">
                            <?php
                            $overall_trend = $comparison_data['metrics']['satisfaction_change'];
                            if ($overall_trend > 0.3) {
                                echo '<i class="fas fa-thumbs-up text-success"></i> <strong>Tendencia Positiva:</strong> La satisfacción laboral ha mejorado significativamente entre las dos encuestas. ';
                                echo 'Se observan mejoras consistentes en la mayoría de categorías evaluadas.';
                            } elseif ($overall_trend < -0.3) {
                                echo '<i class="fas fa-thumbs-down text-danger"></i> <strong>Tendencia Negativa:</strong> La satisfacción laboral ha declinado entre las dos encuestas. ';
                                echo 'Se recomienda implementar acciones correctivas inmediatas.';
                            } else {
                                echo '<i class="fas fa-minus text-warning"></i> <strong>Tendencia Estable:</strong> La satisfacción laboral se mantiene relativamente estable entre las dos encuestas. ';
                                echo 'Hay oportunidades específicas de mejora en algunas categorías.';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeComparison();
    
    <?php if (isset($comparison_data)): ?>
    createComparisonChart();
    <?php endif; ?>
});

function initializeComparison() {
    const compareBtn = document.getElementById('compare-btn');
    const survey1Select = document.getElementById('survey1-select');
    const survey2Select = document.getElementById('survey2-select');
    const comparisonType = document.getElementById('comparison-type');
    
    compareBtn.addEventListener('click', function() {
        const survey1 = survey1Select.value;
        const survey2 = survey2Select.value;
        const type = comparisonType.value;
        
        if (!survey1 || !survey2) {
            alert('Por favor selecciona ambas encuestas para comparar');
            return;
        }
        
        if (survey1 === survey2) {
            alert('Debes seleccionar encuestas diferentes para comparar');
            return;
        }
        
        // Redirigir con parámetros
        window.location.href = `<?= BASE_URL ?>admin/reports/comparison?survey1=${survey1}&survey2=${survey2}&type=${type}`;
    });
}

<?php if (isset($comparison_data)): ?>
function createComparisonChart() {
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    const categoryData = <?= json_encode($comparison_data['categories']) ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categoryData.map(cat => cat.name),
            datasets: [
                {
                    label: '<?= htmlspecialchars($survey1['title']) ?>',
                    data: categoryData.map(cat => cat.survey1_avg),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: '<?= htmlspecialchars($survey2['title']) ?>',
                    data: categoryData.map(cat => cat.survey2_avg),
                    backgroundColor: 'rgba(75, 192, 192, 0.8)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
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
                        afterLabel: function(context) {
                            const categoryIndex = context.dataIndex;
                            const category = categoryData[categoryIndex];
                            const difference = category.survey2_avg - category.survey1_avg;
                            const trend = difference > 0 ? '↗' : (difference < 0 ? '↘' : '→');
                            return `Diferencia: ${trend} ${difference.toFixed(2)}`;
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
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            }
        }
    });
}
<?php endif; ?>

function exportComparison() {
    // Implementar exportación de comparación
    const data = {
        survey1: '<?= isset($survey1) ? htmlspecialchars($survey1['title']) : '' ?>',
        survey2: '<?= isset($survey2) ? htmlspecialchars($survey2['title']) : '' ?>',
        comparison_data: <?= isset($comparison_data) ? json_encode($comparison_data) : 'null' ?>
    };
    
    if (!data.comparison_data) {
        alert('No hay datos de comparación para exportar');
        return;
    }
    
    // Crear y descargar archivo Excel (implementar según necesidades)
    alert('Función de exportación - Por implementar');
}
</script>

<style>
.survey-meta {
    font-size: 0.9rem;
}

.table-success {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.table-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.card.border-success {
    border-color: #28a745 !important;
}

.card.border-danger {
    border-color: #dc3545 !important;
}

.card.border-warning {
    border-color: #ffc107 !important;
}

.card.border-info {
    border-color: #17a2b8 !important;
}

.badge-primary {
    background-color: #007bff;
}

.badge-success {
    background-color: #28a745;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-light {
    background-color: #f8f9fa;
    color: #6c757d;
}

@media print {
    .page-actions,
    .card-header button,
    .btn {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        margin-bottom: 1rem;
    }
}
</style>