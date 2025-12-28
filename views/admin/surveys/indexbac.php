<?php
/**
 * Vista de Lista de Encuestas
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * 
 * Lista principal de encuestas con filtros avanzados, búsqueda,
 * acciones masivas y estadísticas en tiempo real.
 */

// Datos por defecto
$surveys = $surveys ?? [];
$stats = $stats ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0];
$survey_types = $survey_types ?? [];
$survey_statuses = $survey_statuses ?? [];
?>

<!-- Header de la página -->
<div class="page-header">
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">
                <i class="fas fa-poll"></i>
                Gestión de Encuestas
            </h1>
            <p class="page-subtitle">
                Administra las encuestas de clima laboral HERCO de tu organización
            </p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline-primary" onclick="exportSurveys()">
                <i class="fas fa-download"></i>
                Exportar
            </button>
            <a href="/admin/surveys/create" class="btn btn-success">
                <i class="fas fa-plus"></i>
                Nueva Encuesta
            </a>
        </div>
    </div>
</div>

<!-- Estadísticas generales -->
<?php if (!empty($stats)): ?>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-poll"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['total_surveys'] ?? 0) ?></h3>
            <p>Total Encuestas</p>
        </div>
    </div>
    
    <div class="stat-card active">
        <div class="stat-icon">
            <i class="fas fa-play-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['active_surveys'] ?? 0) ?></h3>
            <p>Encuestas Activas</p>
        </div>
    </div>
    
    <div class="stat-card completed">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['completed_surveys'] ?? 0) ?></h3>
            <p>Completadas</p>
        </div>
    </div>
    
    <div class="stat-card participation">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['total_participants'] ?? 0) ?></h3>
            <p>Participantes</p>
            <small><?= number_format($stats['avg_completion_rate'] ?? 0, 1) ?>% promedio</small>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filtros y búsqueda -->
<div class="filters-section">
    <form method="GET" action="/admin/surveys" class="filters-form" id="filtersForm">
        <div class="filters-row">
            <!-- Búsqueda -->
            <div class="filter-group">
                <label for="search">Buscar encuestas</label>
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search" name="search" 
                           placeholder="Título, descripción..."
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>
            
            <!-- Estado -->
            <div class="filter-group">
                <label for="status">Estado</label>
                <select name="status" id="status">
                    <option value="">Todos los estados</option>
                    <?php foreach ($survey_statuses as $value => $label): ?>
                        <option value="<?= $value ?>" 
                                <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Tipo -->
            <div class="filter-group">
                <label for="type">Tipo de Encuesta</label>
                <select name="type" id="type">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($survey_types as $value => $label): ?>
                        <option value="<?= $value ?>" 
                                <?= ($filters['type'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Fecha desde -->
            <div class="filter-group">
                <label for="date_from">Desde</label>
                <input type="date" id="date_from" name="date_from" 
                       value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            
            <!-- Fecha hasta -->
            <div class="filter-group">
                <label for="date_to">Hasta</label>
                <input type="date" id="date_to" name="date_to" 
                       value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            
            <!-- Acciones de filtros -->
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    Filtrar
                </button>
                <a href="/admin/surveys" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Lista de encuestas -->
<div class="surveys-list">
    <?php if (!empty($surveys)): ?>
        <!-- Acciones masivas -->
        <div class="bulk-actions" id="bulkActions" style="display: none;">
            <div class="bulk-content">
                <span id="selectedCount">0</span> encuestas seleccionadas
                <div class="bulk-buttons">
                    <button class="btn btn-sm btn-warning" onclick="bulkAction('pause')">
                        <i class="fas fa-pause"></i>
                        Pausar
                    </button>
                    <button class="btn btn-sm btn-success" onclick="bulkAction('activate')">
                        <i class="fas fa-play"></i>
                        Activar
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                        <i class="fas fa-trash"></i>
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tabla de encuestas -->
        <div class="table-container">
            <table class="surveys-table">
                <thead>
                    <tr>
                        <th class="select-all">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Encuesta</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Participación</th>
                        <th>Fechas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($surveys as $survey): ?>
                        <tr class="survey-row" data-survey-id="<?= $survey['id'] ?>">
                            <!-- Checkbox selección -->
                            <td class="select-cell">
                                <input type="checkbox" class="survey-checkbox" 
                                       value="<?= $survey['id'] ?>" 
                                       onchange="updateBulkActions()">
                            </td>
                            
                            <!-- Información de la encuesta -->
                            <td class="survey-info">
                                <div class="survey-main">
                                    <h4 class="survey-title">
                                        <a href="/admin/surveys/<?= $survey['id'] ?>">
                                            <?= htmlspecialchars($survey['title']) ?>
                                        </a>
                                    </h4>
                                    <p class="survey-description">
                                        <?= htmlspecialchars(substr($survey['description'], 0, 100)) ?>
                                        <?= strlen($survey['description']) > 100 ? '...' : '' ?>
                                    </p>
                                </div>
                                <div class="survey-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-user"></i>
                                        Creada por: <?= htmlspecialchars($survey['created_by_name'] ?? 'Usuario') ?>
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('d/m/Y', strtotime($survey['created_at'])) ?>
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Tipo de encuesta -->
                            <td class="survey-type">
                                <span class="type-badge type-<?= $survey['type'] ?>">
                                    <?= $survey_types[$survey['type']] ?? $survey['type'] ?>
                                </span>
                            </td>
                            
                            <!-- Estado -->
                            <td class="survey-status">
                                <span class="status-badge status-<?= $survey['status'] ?>">
                                    <i class="fas fa-<?= $this->getStatusIcon($survey['status']) ?>"></i>
                                    <?= $survey_statuses[$survey['status']] ?? $survey['status'] ?>
                                </span>
                            </td>
                            
                            <!-- Participación -->
                            <td class="survey-participation">
                                <div class="participation-info">
                                    <div class="participation-numbers">
                                        <strong><?= number_format($survey['total_responses'] ?? 0) ?></strong>
                                        / <?= number_format($survey['total_participants'] ?? 0) ?>
                                    </div>
                                    <div class="participation-bar">
                                        <?php 
                                        $completionRate = $survey['completion_rate'] ?? 0;
                                        $barColor = $completionRate >= 75 ? '#10b981' : ($completionRate >= 50 ? '#f59e0b' : '#ef4444');
                                        ?>
                                        <div class="progress-bar">
                                            <div class="progress-fill" 
                                                 style="width: <?= $completionRate ?>%; background: <?= $barColor ?>"></div>
                                        </div>
                                        <span class="participation-percentage"><?= number_format($completionRate, 1) ?>%</span>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Fechas -->
                            <td class="survey-dates">
                                <div class="dates-info">
                                    <?php if (!empty($survey['start_date'])): ?>
                                        <div class="date-item">
                                            <small>Inicio:</small>
                                            <span><?= date('d/m/Y', strtotime($survey['start_date'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($survey['end_date'])): ?>
                                        <div class="date-item">
                                            <small>Fin:</small>
                                            <span><?= date('d/m/Y', strtotime($survey['end_date'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($survey['start_date']) && empty($survey['end_date'])): ?>
                                        <span class="no-dates">Sin fechas definidas</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Acciones -->
                            <td class="survey-actions">
                                <div class="action-buttons">
                                    <!-- Ver detalles -->
                                    <a href="/admin/surveys/<?= $survey['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <!-- Editar (solo si es borrador o pausada) -->
                                    <?php if (in_array($survey['status'], ['draft', 'paused'])): ?>
                                        <a href="/admin/surveys/<?= $survey['id'] ?>/edit" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Constructor (solo HERCO) -->
                                    <?php if (strpos($survey['type'], 'herco') === 0): ?>
                                        <a href="/admin/surveys/<?= $survey['id'] ?>/builder" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Constructor HERCO">
                                            <i class="fas fa-tools"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Activar/Pausar -->
                                    <?php if ($survey['status'] === 'draft' || $survey['status'] === 'paused'): ?>
                                        <button class="btn btn-sm btn-success" 
                                                onclick="activateSurvey(<?= $survey['id'] ?>)" 
                                                title="Activar">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    <?php elseif ($survey['status'] === 'active'): ?>
                                        <button class="btn btn-sm btn-warning" 
                                                onclick="pauseSurvey(<?= $survey['id'] ?>)" 
                                                title="Pausar">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Menú de más acciones -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown" 
                                                title="Más acciones">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" 
                                                   href="/admin/surveys/<?= $survey['id'] ?>/reports">
                                                    <i class="fas fa-chart-bar"></i>
                                                    Ver Reportes
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" 
                                                        onclick="duplicateSurvey(<?= $survey['id'] ?>)">
                                                    <i class="fas fa-copy"></i>
                                                    Duplicar
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" 
                                                        onclick="exportSurvey(<?= $survey['id'] ?>)">
                                                    <i class="fas fa-download"></i>
                                                    Exportar
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" 
                                                        onclick="deleteSurvey(<?= $survey['id'] ?>, '<?= htmlspecialchars($survey['title']) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                    Eliminar
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="pagination-section">
                <div class="pagination-info">
                    Mostrando <?= number_format($pagination['items_per_page']) ?> de <?= number_format($pagination['total_items']) ?> encuestas
                </div>
                
                <nav class="pagination-nav">
                    <ul class="pagination">
                        <!-- Página anterior -->
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $this->buildPaginationUrl($pagination['current_page'] - 1) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Números de página -->
                        <?php 
                        $startPage = max(1, $pagination['current_page'] - 2);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $this->buildPaginationUrl($i) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Página siguiente -->
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $this->buildPaginationUrl($pagination['current_page'] + 1) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Estado vacío -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-poll"></i>
            </div>
            <h3>No hay encuestas</h3>
            <p>
                <?php if (!empty(array_filter($filters))): ?>
                    No se encontraron encuestas que coincidan con los filtros aplicados.
                    <br>
                    <a href="/admin/surveys" class="btn btn-link">Limpiar filtros</a>
                <?php else: ?>
                    Aún no has creado ninguna encuesta de clima laboral.
                    <br>
                    ¡Comienza creando tu primera encuesta HERCO!
                <?php endif; ?>
            </p>
            <a href="/admin/surveys/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Crear Primera Encuesta
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmMessage">
                <!-- Mensaje dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmAction">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Estilos CSS -->
<style>
/* ====================================
   ESTILOS PÁGINA DE ENCUESTAS
   ==================================== */

.page-header {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-subtitle {
    color: #6b7280;
    margin: 0.5rem 0 0 0;
    font-size: 1.1rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

/* Estadísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    border-left: 4px solid #e5e7eb;
}

.stat-card.active {
    border-left-color: #10b981;
}

.stat-card.completed {
    border-left-color: #3b82f6;
}

.stat-card.participation {
    border-left-color: #f59e0b;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #6b7280;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.stat-content p {
    color: #6b7280;
    margin: 0;
    font-weight: 500;
}

.stat-content small {
    color: #9ca3af;
    font-size: 0.8rem;
}

/* Filtros */
.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.filters-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

.search-input {
    position: relative;
}

.search-input i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input input {
    padding-left: 2.5rem;
}

.filter-group input,
.filter-group select {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

/* Lista de encuestas */
.surveys-list {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

/* Acciones masivas */
.bulk-actions {
    background: #eff6ff;
    border-bottom: 1px solid #dbeafe;
    padding: 1rem 1.5rem;
}

.bulk-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bulk-buttons {
    display: flex;
    gap: 0.5rem;
}

/* Tabla */
.table-container {
    overflow-x: auto;
}

.surveys-table {
    width: 100%;
    border-collapse: collapse;
}

.surveys-table th {
    background: #f9fafb;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}

.surveys-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}

.survey-row:hover {
    background: #f9fafb;
}

/* Información de encuesta */
.survey-info {
    min-width: 300px;
}

.survey-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
}

.survey-title a {
    color: #1f2937;
    text-decoration: none;
    font-weight: 600;
}

.survey-title a:hover {
    color: #3b82f6;
}

.survey-description {
    color: #6b7280;
    margin: 0 0 1rem 0;
    font-size: 0.9rem;
    line-height: 1.4;
}

.survey-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.meta-item {
    color: #9ca3af;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Badges */
.type-badge,
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.type-badge {
    background: #f3f4f6;
    color: #374151;
}

.type-herco_complete {
    background: #dbeafe;
    color: #1e40af;
}

.type-herco_express {
    background: #d1fae5;
    color: #065f46;
}

.status-draft {
    background: #f3f4f6;
    color: #374151;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-paused {
    background: #fef3c7;
    color: #92400e;
}

.status-completed {
    background: #dbeafe;
    color: #1e40af;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

/* Participación */
.participation-info {
    min-width: 120px;
}

.participation-numbers {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.25rem;
}

.progress-fill {
    height: 100%;
    transition: width 0.3s ease;
}

.participation-percentage {
    font-size: 0.8rem;
    color: #6b7280;
    font-weight: 600;
}

/* Fechas */
.dates-info {
    min-width: 100px;
}

.date-item {
    margin-bottom: 0.5rem;
}

.date-item small {
    color: #9ca3af;
    display: block;
    font-size: 0.7rem;
}

.date-item span {
    color: #374151;
    font-weight: 500;
    font-size: 0.8rem;
}

.no-dates {
    color: #9ca3af;
    font-style: italic;
    font-size: 0.8rem;
}

/* Acciones */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.dropdown-menu {
    min-width: 180px;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
}

/* Paginación */
.pagination-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.pagination-info {
    color: #6b7280;
    font-size: 0.9rem;
}

.pagination {
    display: flex;
    gap: 0.25rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.page-item {
    display: flex;
}

.page-link {
    padding: 0.5rem 0.75rem;
    color: #6b7280;
    text-decoration: none;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    transition: all 0.2s;
}

.page-link:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.page-item.active .page-link {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #374151;
    margin-bottom: 1rem;
}

.empty-state p {
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .filters-row {
        grid-template-columns: 1fr;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .surveys-table {
        font-size: 0.9rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .pagination-section {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    initializeFilters();
    initializeBulkActions();
});

/**
 * Inicializar filtros
 */
function initializeFilters() {
    // Auto-submit en cambio de filtros
    const filterInputs = document.querySelectorAll('#filtersForm select, #filtersForm input[type="date"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Delay para evitar submissions múltiples
            setTimeout(() => {
                document.getElementById('filtersForm').submit();
            }, 100);
        });
    });
    
    // Búsqueda con delay
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filtersForm').submit();
        }, 500);
    });
}

/**
 * Inicializar acciones masivas
 */
function initializeBulkActions() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.survey-checkbox');
    
    // Seleccionar todos
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
}

/**
 * Toggle selección total
 */
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.survey-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

/**
 * Actualizar acciones masivas
 */
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.survey-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (checkboxes.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = checkboxes.length;
    } else {
        bulkActions.style.display = 'none';
    }
    
    // Actualizar estado del checkbox "Seleccionar todos"
    const allCheckboxes = document.querySelectorAll('.survey-checkbox');
    const selectAll = document.getElementById('selectAll');
    
    if (checkboxes.length === allCheckboxes.length) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else if (checkboxes.length > 0) {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    } else {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
}

/**
 * Acción masiva
 */
function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.survey-checkbox:checked');
    const surveyIds = Array.from(checkboxes).map(cb => cb.value);
    
    if (surveyIds.length === 0) {
        alert('Por favor selecciona al menos una encuesta');
        return;
    }
    
    const actionMessages = {
        'pause': '¿Estás seguro de pausar las encuestas seleccionadas?',
        'activate': '¿Estás seguro de activar las encuestas seleccionadas?',
        'delete': '¿Estás seguro de eliminar las encuestas seleccionadas? Esta acción no se puede deshacer.'
    };
    
    if (confirm(actionMessages[action])) {
        // Implementar llamada AJAX para acción masiva
        console.log(`Acción masiva: ${action}`, surveyIds);
        // TODO: Implementar
    }
}

/**
 * Activar encuesta
 */
function activateSurvey(surveyId) {
    if (confirm('¿Estás seguro de activar esta encuesta?')) {
        // Crear formulario para envío POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/surveys/${surveyId}/activate`;
        
        // Token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Pausar encuesta
 */
function pauseSurvey(surveyId) {
    if (confirm('¿Estás seguro de pausar esta encuesta?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/surveys/${surveyId}/pause`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Duplicar encuesta
 */
function duplicateSurvey(surveyId) {
    if (confirm('¿Deseas crear una copia de esta encuesta?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/surveys/${surveyId}/duplicate`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Eliminar encuesta
 */
function deleteSurvey(surveyId, surveyTitle) {
    const message = `¿Estás seguro de eliminar la encuesta "${surveyTitle}"?\n\nEsta acción no se puede deshacer.`;
    
    if (confirm(message)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/surveys/${surveyId}/delete`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfToken);
        
        // Método DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Exportar encuesta
 */
function exportSurvey(surveyId) {
    window.open(`/admin/surveys/${surveyId}/export`, '_blank');
}

/**
 * Exportar todas las encuestas
 */
function exportSurveys() {
    window.open('/admin/surveys/export', '_blank');
}
</script>

<?php
// Funciones auxiliares para la vista
if (!function_exists('getStatusIcon')) {
    function getStatusIcon($status) {
        $icons = [
            'draft' => 'edit',
            'active' => 'play-circle',
            'paused' => 'pause-circle',
            'completed' => 'check-circle',
            'cancelled' => 'times-circle'
        ];
        
        return $icons[$status] ?? 'question-circle';
    }
}

if (!function_exists('buildPaginationUrl')) {
    function buildPaginationUrl($page) {
        $params = $_GET;
        $params['page'] = $page;
        return '/admin/surveys?' . http_build_query($params);
    }
}
?>