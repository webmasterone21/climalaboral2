<?php
// ======================================
// ARCHIVO: views/components/form-field.php
// ======================================
?>
<!-- Form Field Component -->
<?php
$type = $type ?? 'text';
$name = $name ?? '';
$label = $label ?? '';
$value = $value ?? '';
$placeholder = $placeholder ?? '';
$required = $required ?? false;
$readonly = $readonly ?? false;
$disabled = $disabled ?? false;
$help = $help ?? '';
$error = $error ?? '';
$options = $options ?? [];
$class = $class ?? '';
$id = $id ?? $name;
?>

<div class="mb-3">
    <?php if ($label): ?>
        <label for="<?= View::e($id) ?>" class="form-label">
            <?= View::e($label) ?>
            <?php if ($required): ?>
                <span class="text-danger">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>
    
    <?php if ($type === 'select'): ?>
        <select 
            name="<?= View::e($name) ?>" 
            id="<?= View::e($id) ?>" 
            class="form-select <?= $error ? 'is-invalid' : '' ?> <?= $class ?>"
            <?= $required ? 'required' : '' ?>
            <?= $disabled ? 'disabled' : '' ?>
        >
            <?php if ($placeholder): ?>
                <option value=""><?= View::e($placeholder) ?></option>
            <?php endif; ?>
            
            <?php foreach ($options as $optValue => $optLabel): ?>
                <option value="<?= View::e($optValue) ?>" <?= $optValue == $value ? 'selected' : '' ?>>
                    <?= View::e($optLabel) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
    <?php elseif ($type === 'textarea'): ?>
        <textarea 
            name="<?= View::e($name) ?>" 
            id="<?= View::e($id) ?>" 
            class="form-control <?= $error ? 'is-invalid' : '' ?> <?= $class ?>"
            placeholder="<?= View::e($placeholder) ?>"
            <?= $required ? 'required' : '' ?>
            <?= $readonly ? 'readonly' : '' ?>
            <?= $disabled ? 'disabled' : '' ?>
            rows="4"
        ><?= View::e($value) ?></textarea>
        
    <?php elseif ($type === 'checkbox'): ?>
        <div class="form-check">
            <input 
                type="checkbox" 
                name="<?= View::e($name) ?>" 
                id="<?= View::e($id) ?>" 
                class="form-check-input <?= $error ? 'is-invalid' : '' ?> <?= $class ?>"
                value="1"
                <?= $value ? 'checked' : '' ?>
                <?= $disabled ? 'disabled' : '' ?>
            >
            <?php if ($label): ?>
                <label class="form-check-label" for="<?= View::e($id) ?>">
                    <?= View::e($label) ?>
                </label>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        <input 
            type="<?= View::e($type) ?>" 
            name="<?= View::e($name) ?>" 
            id="<?= View::e($id) ?>" 
            class="form-control <?= $error ? 'is-invalid' : '' ?> <?= $class ?>"
            value="<?= View::e($value) ?>"
            placeholder="<?= View::e($placeholder) ?>"
            <?= $required ? 'required' : '' ?>
            <?= $readonly ? 'readonly' : '' ?>
            <?= $disabled ? 'disabled' : '' ?>
        >
    <?php endif; ?>
    
    <?php if ($help): ?>
        <div class="form-text"><?= View::e($help) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="invalid-feedback"><?= View::e($error) ?></div>
    <?php endif; ?>
</div>
