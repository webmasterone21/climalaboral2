<?php
// ======================================
// ARCHIVO: views/components/loading.php
// ======================================
?>
<!-- Loading Component -->
<?php
$size = $size ?? 'md'; // sm, md, lg
$text = $text ?? 'Cargando...';
$overlay = $overlay ?? false;
$color = $color ?? 'primary';

$sizeMap = [
    'sm' => 'spinner-border-sm',
    'md' => '',
    'lg' => 'spinner-border-lg'
];

$sizeClass = $sizeMap[$size] ?? '';
?>

<?php if ($overlay): ?>
    <div class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-light bg-opacity-75" style="z-index: 9999;">
<?php endif; ?>

<div class="text-center p-4">
    <div class="spinner-border text-<?= View::e($color) ?> <?= $sizeClass ?>" role="status">
        <span class="visually-hidden"><?= View::e($text) ?></span>
    </div>
    
    <?php if ($text && !$overlay): ?>
        <div class="mt-2 text-muted"><?= View::e($text) ?></div>
    <?php endif; ?>
</div>

<?php if ($overlay): ?>
    </div>
<?php endif; ?>
