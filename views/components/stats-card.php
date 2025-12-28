<?php
// ======================================
// ARCHIVO: views/components/stats-card.php
// ======================================
?>
<!-- Statistics Card Component -->
<?php
$title = $title ?? '';
$value = $value ?? 0;
$change = $change ?? null;
$icon = $icon ?? 'fas fa-chart-line';
$color = $color ?? 'primary';
$format = $format ?? 'number'; // number, percentage, currency
?>

<div class="card stats-card border-start border-4 border-<?= View::e($color) ?>">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col">
                <div class="text-xs font-weight-bold text-<?= View::e($color) ?> text-uppercase mb-1">
                    <?= View::e($title) ?>
                </div>
                
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?php
                    switch ($format) {
                        case 'percentage':
                            echo View::formatNumber($value, 1) . '%';
                            break;
                        case 'currency':
                            echo '$' . View::formatNumber($value, 2);
                            break;
                        default:
                            echo View::formatNumber($value, 0);
                            break;
                    }
                    ?>
                </div>
                
                <?php if ($change !== null): ?>
                    <div class="text-xs mt-1">
                        <?php if ($change > 0): ?>
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i> +<?= View::formatNumber($change, 1) ?>%
                            </span>
                        <?php elseif ($change < 0): ?>
                            <span class="text-danger">
                                <i class="fas fa-arrow-down"></i> <?= View::formatNumber($change, 1) ?>%
                            </span>
                        <?php else: ?>
                            <span class="text-muted">
                                <i class="fas fa-minus"></i> Sin cambios
                            </span>
                        <?php endif; ?>
                        <span class="text-muted ms-1">vs período anterior</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-auto">
                <i class="<?= View::e($icon) ?> fa-2x text-<?= View::e($color) ?> opacity-75"></i>
            </div>
        </div>
    </div>
</div>