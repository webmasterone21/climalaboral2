// ======================================
// ARCHIVO: views/components/alert.php
// ======================================
?>
<!-- Alert Component -->
<?php
$type = $type ?? 'info';
$message = $message ?? '';
$dismissible = $dismissible ?? true;
$icon = $icon ?? true;

$iconMap = [
    'success' => 'fas fa-check-circle',
    'danger' => 'fas fa-exclamation-triangle',
    'warning' => 'fas fa-exclamation-circle',
    'info' => 'fas fa-info-circle',
    'primary' => 'fas fa-bell'
];

$iconClass = $iconMap[$type] ?? $iconMap['info'];
?>

<div class="alert alert-<?= View::e($type) ?> <?= $dismissible ? 'alert-dismissible' : '' ?> fade show" role="alert">
    <?php if ($icon): ?>
        <i class="<?= $iconClass ?> me-2"></i>
    <?php endif; ?>
    
    <?= $message ?>
    
    <?php if ($dismissible): ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    <?php endif; ?>
</div>