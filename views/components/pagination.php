<?php
// ======================================
// ARCHIVO: views/components/pagination.php
// ======================================
?>
<!-- Pagination Component -->
<?php
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$baseUrl = $baseUrl ?? '';
$showInfo = $showInfo ?? true;
$maxLinks = $maxLinks ?? 5;

if ($totalPages <= 1) return;

// Calcular rango de páginas a mostrar
$start = max(1, $currentPage - floor($maxLinks / 2));
$end = min($totalPages, $start + $maxLinks - 1);
$start = max(1, $end - $maxLinks + 1);
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    
    <?php if ($showInfo): ?>
        <div class="pagination-info mb-2 mb-md-0">
            <small class="text-muted">
                Página <?= $currentPage ?> de <?= $totalPages ?>
            </small>
        </div>
    <?php endif; ?>
    
    <nav aria-label="Navegación de páginas">
        <ul class="pagination pagination-sm mb-0">
            
            <!-- Primera página -->
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=1" aria-label="Primera">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage - 1 ?>" aria-label="Anterior">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-angle-double-left"></i></span>
                </li>
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-angle-left"></i></span>
                </li>
            <?php endif; ?>
            
            <!-- Páginas numeradas -->
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <?php if ($i === $currentPage): ?>
                        <span class="page-link"><?= $i ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?= $baseUrl ?>?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>
            
            <!-- Última página -->
            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage + 1 ?>" aria-label="Siguiente">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="<?= $baseUrl ?>?page=<?= $totalPages ?>" aria-label="Última">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-angle-right"></i></span>
                </li>
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-angle-double-right"></i></span>
                </li>
            <?php endif; ?>
            
        </ul>
    </nav>
</div>