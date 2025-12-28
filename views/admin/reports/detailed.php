<?php
// views/admin/reports/detailed.php (Vista alternativa más tabular)
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="page-title">Análisis Tabular Detallado</h1>
                        <h5 class="text-muted"><?= htmlspecialchars($survey['title']) ?></h5>
                        <div class="page-breadcrumb">
                            <nav>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports">Reportes</a></li>
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/reports/dashboard/<?= $survey['id'] ?>">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Tabular</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>admin/reports/show/<?= $survey['id'] ?>" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> Vista Gráfica
                            </a>
                            <button onclick="exportToCSV()" class="btn btn-success">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros avanzados -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Filtros y Configuración</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="department-filter">Departamento</label>
                                <select id="department-filter" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <?php if (isset($demographics['departments'])): ?>
                                        <?php foreach ($demographics['departments'] as $dept): ?>
                                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="category-filter">Categoría</label>
                                <select id="category-filter" class="form-control form-control-sm">
                                    <option value="">Todas</option>
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
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="score-filter">Puntuación</label>
                                <select id="score-filter" class="form-control form-control-sm">
                                    <option value="">Todas</option>
                                    <option value="high">Alta (4.0+)</option>
                                    <option value="medium">Media (3.0-3.9)</option>
                                    <option value="low">Baja (&lt;3.0)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="sort-by">Ordenar por</label>
                                <select id="sort-by" class="form-control form-control-sm">
                                    <option value="order">Orden original</option>
                                    <option value="score-desc">Puntuación (desc)</option>
                                    <option value="score-asc">Puntuación (asc)</option>
                                    <option value="responses-desc">Respuestas (desc)</option>
                                    <option value="category">Categoría</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="view-mode">Vista</label>
                                <select id="view-mode" class="form-control form-control-sm">
                                    <option value="summary">Resumen</option>
                                    <option value="detailed">Detallado</option>
                                    <option value="comparison">Comparación</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button id="apply-filters" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-filter"></i> Aplicar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla resumen por categorías -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table text-primary"></i>
                        Resumen por Categorías
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="categories-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Categoría</th>
                                    <th class="text-center">Preguntas</th>
                                    <th class="text-center">Respuestas</th>
                                    <th class="text-center">Promedio</th>
                                    <th class="text-center">Desv. Estándar</th>
                                    <th class="text-center">Satisfacción</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($questions)): ?>
                                    <?php
                                    // Agrupar por categorías
                                    $categories_grouped = [];
                                    foreach ($questions as $question) {
                                        $cat_name = $question['category_name'];
                                        if (!isset($categories_grouped[$cat_name])) {
                                            $categories_grouped[$cat_name] = [
                                                'name' => $cat_name,
                                                'questions' => [],
                                                'total_responses' => 0,
                                                'total_score' => 0,
                                                'scores' => []
                                            ];
                                        }
                                        $categories_grouped[$cat_name]['questions'][] = $question;
                                        $categories_grouped[$cat_name]['total_responses'] += $question['response_count'];
                                        $categories_grouped[$cat_name]['total_score'] += $question['average'] * $question['response_count'];
                                        $categories_grouped[$cat_name]['scores'][] = $question['average'];
                                    }
                                    ?>
                                    <?php foreach ($categories_grouped as $category): ?>
                                        <?php
                                        $cat_average = $category['total_responses'] > 0 ? $category['total_score'] / $category['total_responses'] : 0;
                                        $cat_std_dev = count($category['scores']) > 1 ? sqrt(array_sum(array_map(function($x) use ($cat_average) { return pow($x - $cat_average, 2); }, $category['scores'])) / count($category['scores'])) : 0;
                                        $satisfaction_level = $cat_average >= 4 ? 'Excelente' : ($cat_average >= 3.5 ? 'Buena' : ($cat_average >= 3 ? 'Regular' : 'Necesita Mejora'));
                                        $satisfaction_class = $cat_average >= 4 ? 'success' : ($cat_average >= 3.5 ? 'info' : ($cat_average >= 3 ? 'warning' : 'danger'));
                                        ?>
                                        <tr data-category="<?= htmlspecialchars($category['name']) ?>" data-score="<?= $cat_average ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="category-indicator bg-<?= $satisfaction_class ?> me-2"></div>
                                                    <strong><?= htmlspecialchars($category['name']) ?></strong>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= count($category['questions']) ?></td>
                                            <td class="text-center"><?= $category['total_responses'] ?></td>
                                            <td class="text-center">
                                                <span class="font-weight-bold text-<?= $satisfaction_class ?>">
                                                    <?= number_format($cat_average, 2) ?>
                                                </span>
                                            </td>
                                            <td class="text-center"><?= number_format($cat_std_dev, 2) ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $satisfaction_class ?>">
                                                    <?= $satisfaction_level ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary" onclick="showCategoryDetails('<?= htmlspecialchars($category['name']) ?>')">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
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

    <!-- Tabla detallada por preguntas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list-alt text-info"></i>
                            Análisis Detallado por Pregunta
                        </h5>
                        <div class="table-controls">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick="toggleColumnVisibility('stats')">
                                    <i class="fas fa-chart-bar"></i> Estadísticas
                                </button>
                                <button class="btn btn-outline-secondary" onclick="toggleColumnVisibility('departments')">
                                    <i class="fas fa-building"></i> Departamentos
                                </button>
                                <button class="btn btn-outline-secondary" onclick="toggleColumnVisibility('distribution')">
                                    <i class="fas fa-chart-pie"></i> Distribución
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" id="questions-table">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th style="width: 30%">Pregunta</th>
                                    <th class="text-center">Categoría</th>
                                    <th class="text-center">Respuestas</th>
                                    <th class="text-center">Promedio</th>
                                    <th class="text-center stats-column">Mediana</th>
                                    <th class="text-center stats-column">Desv. Est.</th>
                                    <th class="text-center">Positivas %</th>
                                    <th class="text-center">Negativas %</th>
                                    <th class="text-center departments-column">Mejor Depto.</th>
                                    <th class="text-center departments-column">Peor Depto.</th>
                                    <th class="text-center distribution-column">Distribución</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($questions) && !empty($questions)): ?>
                                    <?php foreach ($questions as $question): ?>
                                        <tr class="question-row" 
                                            data-category="<?= htmlspecialchars($question['category_name']) ?>"
                                            data-score="<?= $question['average'] ?>"
                                            data-responses="<?= $question['response_count'] ?>">
                                            
                                            <td>
                                                <div class="question-cell">
                                                    <div class="question-text" title="<?= htmlspecialchars($question['question_text']) ?>">
                                                        <?= mb_strlen($question['question_text']) > 60 
                                                            ? mb_substr(htmlspecialchars($question['question_text']), 0, 60) . '...'
                                                            : htmlspecialchars($question['question_text']) ?>
                                                    </div>
                                                    <small class="text-muted">ID: <?= $question['id'] ?></small>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="badge badge-outline-primary badge-sm">
                                                    <?= htmlspecialchars($question['category_name']) ?>
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="font-weight-bold"><?= $question['response_count'] ?></span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <?php
                                                $score_class = $question['average'] >= 4 ? 'success' : ($question['average'] >= 3 ? 'warning' : 'danger');
                                                ?>
                                                <span class="font-weight-bold text-<?= $score_class ?>">
                                                    <?= number_format($question['average'], 2) ?>
                                                </span>
                                                <div class="score-bar">
                                                    <div class="progress" style="height: 4px;">
                                                        <div class="progress-bar bg-<?= $score_class ?>" 
                                                             style="width: <?= ($question['average'] / 5) * 100 ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="text-center stats-column">
                                                <?= isset($question['median']) ? number_format($question['median'], 1) : 'N/A' ?>
                                            </td>
                                            
                                            <td class="text-center stats-column">
                                                <?= isset($question['std_dev']) ? number_format($question['std_dev'], 2) : 'N/A' ?>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="text-success font-weight-bold">
                                                    <?= $question['positive_responses'] ?? 0 ?>%
                                                </span>
                                            </td>
                                            
                                            <td class="text-center">
                                                <span class="text-danger font-weight-bold">
                                                    <?= $question['negative_responses'] ?? 0 ?>%
                                                </span>
                                            </td>
                                            
                                            <td class="text-center departments-column">
                                                <?php if (isset($question['department_breakdown']) && !empty($question['department_breakdown'])): ?>
                                                    <?php
                                                    $best_dept = array_reduce($question['department_breakdown'], function($carry, $item) {
                                                        return (!$carry || $item['average'] > $carry['average']) ? $item : $carry;
                                                    });
                                                    ?>
                                                    <small class="text-success">
                                                        <?= htmlspecialchars($best_dept['name']) ?>
                                                        <br>(<?= number_format($best_dept['average'], 1) ?>)
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">N/A</small>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="text-center departments-column">
                                                <?php if (isset($question['department_breakdown']) && !empty($question['department_breakdown'])): ?>
                                                    <?php
                                                    $worst_dept = array_reduce($question['department_breakdown'], function($carry, $item) {
                                                        return (!$carry || $item['average'] < $carry['average']) ? $item : $carry;
                                                    });
                                                    ?>
                                                    <small class="text-danger">
                                                        <?= htmlspecialchars($worst_dept['name']) ?>
                                                        <br>(<?= number_format($worst_dept['average'], 1) ?>)
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">N/A</small>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="text-center distribution-column">
                                                <?php if (isset($question['distribution'])): ?>
                                                    <div class="mini-chart">
                                                        <?php foreach ($question['distribution']['values'] as $i => $value): ?>
                                                            <?php
                                                            $colors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
                                                            $percentage = $question['response_count'] > 0 ? ($value / $question['response_count']) * 100 : 0;
                                                            ?>
                                                            <div class="chart-bar" 
                                                                 style="height: <?= $percentage ?>%; background-color: <?= $colors[$i] ?>"
                                                                 title="<?= $question['distribution']['labels'][$i] ?>: <?= $value ?>"></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <small class="text-muted">N/A</small>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="text-center">
                                                <?php
                                                if ($question['average'] >= 4) {
                                                    echo '<i class="fas fa-check-circle text-success" title="Excelente"></i>';
                                                } elseif ($question['average'] >= 3.5) {
                                                    echo '<i class="fas fa-thumbs-up text-info" title="Buena"></i>';
                                                } elseif ($question['average'] >= 3) {
                                                    echo '<i class="fas fa-minus-circle text-warning" title="Regular"></i>';
                                                } else {
                                                    echo '<i class="fas fa-exclamation-triangle text-danger" title="Necesita atención"></i>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center py-4">
                                            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                            <div class="h6 text-muted">No hay datos disponibles</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de comparación entre departamentos -->
    <?php if (isset($demographics['departments']) && count($demographics['departments']) > 1): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-balance-scale text-warning"></i>
                        Matriz de Comparación entre Departamentos
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="comparison-table">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th>Categoría</th>
                                    <?php foreach ($demographics['departments'] as $dept): ?>
                                        <th class="text-center"><?= htmlspecialchars($dept['name']) ?></th>
                                    <?php endforeach; ?>
                                    <th class="text-center">Promedio General</th>
                                    <th class="text-center">Diferencia Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($categories_grouped)): ?>
                                    <?php foreach ($categories_grouped as $category): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= htmlspecialchars($category['name']) ?></td>
                                            <?php
                                            $dept_scores = [];
                                            foreach ($demographics['departments'] as $dept):
                                                // Calcular promedio de la categoría para este departamento
                                                $dept_score = 3.5; // Placeholder - implementar lógica real
                                                $dept_scores[] = $dept_score;
                                                $score_class = $dept_score >= 4 ? 'success' : ($dept_score >= 3 ? 'warning' : 'danger');
                                            ?>
                                                <td class="text-center">
                                                    <span class="badge badge-<?= $score_class ?>">
                                                        <?= number_format($dept_score, 1) ?>
                                                    </span>
                                                </td>
                                            <?php endforeach; ?>
                                            
                                            <?php 
                                            $general_avg = array_sum($dept_scores) / count($dept_scores);
                                            $max_diff = max($dept_scores) - min($dept_scores);
                                            ?>
                                            <td class="text-center font-weight-bold">
                                                <?= number_format($general_avg, 2) ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="<?= $max_diff > 1 ? 'text-danger' : ($max_diff > 0.5 ? 'text-warning' : 'text-success') ?>">
                                                    <?= number_format($max_diff, 2) ?>
                                                </span>
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
    <?php endif; ?>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeTableControls();
});

function initializeFilters() {
    const applyBtn = document.getElementById('apply-filters');
    const departmentFilter = document.getElementById('department-filter');
    const categoryFilter = document.getElementById('category-filter');
    const scoreFilter = document.getElementById('score-filter');
    const sortBy = document.getElementById('sort-by');
    
    applyBtn.addEventListener('click', function() {
        filterAndSortTable();
    });
    
    // Auto-aplicar algunos filtros
    [departmentFilter, categoryFilter, scoreFilter].forEach(filter => {
        filter.addEventListener('change', filterAndSortTable);
    });
    
    sortBy.addEventListener('change', filterAndSortTable);
}

function filterAndSortTable() {
    const departmentValue = document.getElementById('department-filter').value;
    const categoryValue = document.getElementById('category-filter').value.toLowerCase();
    const scoreValue = document.getElementById('score-filter').value;
    const sortValue = document.getElementById('sort-by').value;
    
    const rows = Array.from(document.querySelectorAll('#questions-table .question-row'));
    
    // Filtrar
    rows.forEach(row => {
        const category = row.dataset.category.toLowerCase();
        const score = parseFloat(row.dataset.score);
        
        let showRow = true;
        
        // Filtro de categoría
        if (categoryValue && !category.includes(categoryValue)) {
            showRow = false;
        }
        
        // Filtro de puntuación
        if (scoreValue === 'high' && score < 4.0) showRow = false;
        if (scoreValue === 'medium' && (score < 3.0 || score >= 4.0)) showRow = false;
        if (scoreValue === 'low' && score >= 3.0) showRow = false;
        
        row.style.display = showRow ? '' : 'none';
    });
    
    // Ordenar
    const tbody = document.querySelector('#questions-table tbody');
    const visibleRows = rows.filter(row => row.style.display !== 'none');
    
    visibleRows.sort((a, b) => {
        switch (sortValue) {
            case 'score-desc':
                return parseFloat(b.dataset.score) - parseFloat(a.dataset.score);
            case 'score-asc':
                return parseFloat(a.dataset.score) - parseFloat(b.dataset.score);
            case 'responses-desc':
                return parseInt(b.dataset.responses) - parseInt(a.dataset.responses);
            case 'category':
                return a.dataset.category.localeCompare(b.dataset.category);
            default:
                return 0;
        }
    });
    
    // Reordenar en el DOM
    visibleRows.forEach(row => {
        tbody.appendChild(row);
    });
}

function initializeTableControls() {
    // Inicializar tooltips
    const tooltips = document.querySelectorAll('[title]');
    tooltips.forEach(element => {
        element.setAttribute('data-bs-toggle', 'tooltip');
    });
}

function toggleColumnVisibility(columnType) {
    const columns = document.querySelectorAll(`.${columnType}-column`);
    columns.forEach(col => {
        col.style.display = col.style.display === 'none' ? '' : 'none';
    });
}

function showCategoryDetails(categoryName) {
    // Filtrar tabla por categoría
    document.getElementById('category-filter').value = categoryName;
    filterAndSortTable();
    
    // Scroll a la tabla
    document.getElementById('questions-table').scrollIntoView({ behavior: 'smooth' });
}

function exportToCSV() {
    const table = document.getElementById('questions-table');
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');
    
    let csv = '';
    rows.forEach((row, index) => {
        const cols = row.querySelectorAll(index === 0 ? 'th' : 'td');
        const rowData = Array.from(cols).map(col => {
            return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
        }).join(',');
        csv += rowData + '\n';
    });
    
    // Descargar archivo
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `reporte_detallado_<?= date('Y-m-d') ?>.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>

<style>
.category-indicator {
    width: 4px;
    height: 20px;
    border-radius: 2px;
    display: inline-block;
}

.question-cell {
    max-width: 300px;
}

.question-text {
    font-size: 0.9rem;
    line-height: 1.3;
}

.badge-sm {
    font-size: 0.7rem;
    padding: 0.25em 0.5em;
}

.badge-outline-primary {
    color: #007bff;
    border: 1px solid #007bff;
    background: transparent;
}

.score-bar {
    margin-top: 2px;
}

.mini-chart {
    display: flex;
    height: 30px;
    width: 50px;
    align-items: end;
    gap: 1px;
    margin: 0 auto;
}

.chart-bar {
    flex: 1;
    min-height: 2px;
    border-radius: 1px 1px 0 0;
}

.table-controls {
    margin-bottom: 1rem;
}

.stats-column,
.departments-column,
.distribution-column {
    transition: all 0.3s ease;
}

@media print {
    .page-actions,
    .card-header,
    .btn,
    .table-controls {
        display: none !important;
    }
    
    .table {
        font-size: 0.8rem;
    }
    
    .card {
        border: none;
        box-shadow: none;
    }
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .question-cell {
        max-width: 200px;
    }
    
    .badge-sm {
        font-size: 0.6rem;
    }
}
</style>