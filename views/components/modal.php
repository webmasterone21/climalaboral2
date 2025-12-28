<?php
// ======================================
// ARCHIVO: views/components/modal.php
// ======================================
?>
<!-- Modal Component -->
<?php
$id = $id ?? 'modal';
$title = $title ?? 'Modal';
$size = $size ?? ''; // sm, lg, xl
$centered = $centered ?? false;
$backdrop = $backdrop ?? 'true';
$keyboard = $keyboard ?? 'true';
$content = $content ?? '';
$footer = $footer ?? '';
?>

<div class="modal fade" id="<?= View::e($id) ?>" tabindex="-1" data-bs-backdrop="<?= $backdrop ?>" data-bs-keyboard="<?= $keyboard ?>">
    <div class="modal-dialog <?= $size ? "modal-{$size}" : '' ?> <?= $centered ? 'modal-dialog-centered' : '' ?> modal-dialog-scrollable">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title"><?= View::e($title) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body">
                <?= $content ?>
            </div>
            
            <?php if ($footer): ?>
                <div class="modal-footer">
                    <?= $footer ?>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>
