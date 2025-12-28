<?php
// ======================================
// ARCHIVO: views/components/data-table.php
// ======================================
?>
<!-- Data Table Component -->
<?php
$headers = $headers ?? [];
$rows = $rows ?? [];
$actions = $actions ?? [];
$searchable = $searchable ?? true;
$sortable = $sortable ?? true;
$striped = $striped ?? true;
$hover = $hover ?? true;
$responsive = $responsive ?? true;
$emptyMessage = $emptyMessage ?? 'No hay datos para mostrar';
?>

<div class="data-table-wrapper">
    
    <?php if ($searchable): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Buscar..." id="tableSearch">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    Mostrando <span id="visibleRows"><?= count($rows) ?></span> de <?= count($rows) ?> registros
                </small>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="<?= $responsive ? 'table-responsive' : '' ?>">
        <table class="table <?= $striped ? 'table-striped' : '' ?> <?= $hover ? 'table-hover' : '' ?>" id="dataTable">
            
            <?php if (!empty($headers)): ?>
                <thead class="table-dark">
                    <tr>
                        <?php foreach ($headers as $header): ?>
                            <th <?= $sortable ? 'class="sortable cursor-pointer"' : '' ?>>
                                <?= View::e($header) ?>
                                <?php if ($sortable): ?>
                                    <i class="fas fa-sort ms-1 text-muted"></i>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                        
                        <?php if (!empty($actions)): ?>
                            <th width="120">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
            <?php endif; ?>
            
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="<?= count($headers) + (!empty($actions) ? 1 : 0) ?>" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            <?= View::e($emptyMessage) ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                                <td><?= $cell ?></td>
                            <?php endforeach; ?>
                            
                            <?php if (!empty($actions)): ?>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php foreach ($actions as $action): ?>
                                            <a href="<?= View::e($action['url']) ?>" 
                                               class="btn btn-outline-<?= $action['color'] ?? 'secondary' ?>"
                                               title="<?= View::e($action['title'] ?? $action['label']) ?>"
                                               <?= isset($action['confirm']) ? 'data-confirm="' . View::e($action['confirm']) . '"' : '' ?>>
                                                <i class="<?= View::e($action['icon']) ?>"></i>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            
        </table>
    </div>
    
</div>
<!-- JavaScript para funcionalidad de tabla -->
<script>
$(document).ready(function() {
    // Búsqueda en tabla
    $('#tableSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        let visibleCount = 0;
        
        $('#dataTable tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.includes(searchTerm)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        $('#visibleRows').text(visibleCount);
    });
    
    // Ordenamiento de tabla
    $('.sortable').on('click', function() {
        const table = $(this).closest('table');
        const columnIndex = $(this).index();
        const rows = table.find('tbody tr').toArray();
        const isAscending = !$(this).hasClass('sort-asc');
        
        // Limpiar iconos de ordenamiento
        $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        
        // Actualizar icono
        $(this).find('i').removeClass('fa-sort fa-sort-up fa-sort-down')
               .addClass(isAscending ? 'fa-sort-up' : 'fa-sort-down');
        
        // Actualizar clase
        $('.sortable').removeClass('sort-asc sort-desc');
        $(this).addClass(isAscending ? 'sort-asc' : 'sort-desc');
        
        // Ordenar filas
        rows.sort((a, b) => {
            const aText = $(a).find('td').eq(columnIndex).text().trim();
            const bText = $(b).find('td').eq(columnIndex).text().trim();
            
            // Detectar si es número
            const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
            const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            } else {
                return isAscending ? 
                    aText.localeCompare(bText) : 
                    bText.localeCompare(aText);
            }
        });
        
        // Reordenar tabla
        table.find('tbody').empty().append(rows);
    });
});
</script>