<?php
// ======================================
// ARCHIVO: views/components/breadcrumb.php
// ======================================
?>
<!-- Breadcrumb Component -->
<?php
$breadcrumbs = $breadcrumbs ?? [];
$separator = $separator ?? '<i class="fas fa-chevron-right mx-2 text-muted"></i>';
?>

<?php if (!empty($breadcrumbs)): ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= View::url('admin/dashboard') ?>" class="text-decoration-none">
                    <i class="fas fa-home"></i>
                    <span class="d-none d-md-inline ms-1">Dashboard</span>
                </a>
            </li>
            
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <?php $isLast = ($index === count($breadcrumbs) - 1); ?>
                
                <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>" <?= $isLast ? 'aria-current="page"' : '' ?>>
                    <?php if (!$isLast && !empty($crumb['url'])): ?>
                        <a href="<?= View::e($crumb['url']) ?>" class="text-decoration-none">
                            <?= View::e($crumb['label']) ?>
                        </a>
                    <?php else: ?>
                        <?= View::e($crumb['label']) ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>