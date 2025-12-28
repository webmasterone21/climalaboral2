<?php
// ======================================
// ARCHIVO: views/components/card.php
// ======================================
?>
<!-- Card Component -->
<?php
$title = $title ?? '';
$subtitle = $subtitle ?? '';
$content = $content ?? '';
$footer = $footer ?? '';
$headerClass = $headerClass ?? '';
$bodyClass = $bodyClass ?? '';
$footerClass = $footerClass ?? '';
$actions = $actions ?? [];
?>

<div class="card h-100">
    
    <?php if ($title || !empty($actions)): ?>
        <div class="card-header <?= $headerClass ?> d-flex justify-content-between align-items-center">
            <div>
                <?php if ($title): ?>
                    <h5 class="card-title mb-0"><?= View::e($title) ?></h5>
                <?php endif; ?>
                
                <?php if ($subtitle): ?>
                    <small class="text-muted"><?= View::e($subtitle) ?></small>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($actions)): ?>
                <div class="card-actions">
                    <?php foreach ($actions as $action): ?>
                        <a href="<?= View::e($action['url']) ?>" 
                           class="btn btn-sm btn-outline-secondary <?= $action['class'] ?? '' ?>"
                           <?= isset($action['confirm']) ? 'data-confirm="' . View::e($action['confirm']) . '"' : '' ?>>
                            <?php if (isset($action['icon'])): ?>
                                <i class="<?= View::e($action['icon']) ?> me-1"></i>
                            <?php endif; ?>
                            <?= View::e($action['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($content): ?>
        <div class="card-body <?= $bodyClass ?>">
            <?= $content ?>
        </div>
    <?php endif; ?>
    
    <?php if ($footer): ?>
        <div class="card-footer <?= $footerClass ?>">
            <?= $footer ?>
        </div>
    <?php endif; ?>
    
</div>
