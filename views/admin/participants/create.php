<?php
// views/admin/participants/create.php
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="container-xl">
    <!-- Header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <a href="/admin/participants" class="text-muted">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver a Participantes
                    </a>
                </div>
                <h2 class="page-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Nuevo Participante
                </h2>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="row">
        <div class="col-md-8 mx-auto">
            <form action="/admin/participants/store" method="POST" class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Participante</h3>
                </div>
                <div class="card-body">
                    
                    <!-- Encuesta -->
                    <div class="mb-3">
                        <label class="form-label required">Encuesta</label>
                        <select name="survey_id" class="form-select" required>
                            <option value="">Seleccione una encuesta...</option>
                            <?php if (!empty($surveys)): ?>
                                <?php foreach ($surveys as $survey): ?>
                                    <option value="<?= $survey['id'] ?>">
                                        <?= htmlspecialchars($survey['title']) ?>
                                        <span class="text-muted">(<?= ucfirst($survey['status']) ?>)</span>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($surveys)): ?>
                            <small class="form-hint text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                No hay encuestas disponibles. 
                                <a href="/admin/surveys/create">Crear encuesta</a>
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="name" class="form-control" placeholder="Ej: Juan Pérez">
                        <small class="form-hint">Nombre del participante (opcional)</small>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="ejemplo@empresa.com" required>
                        <small class="form-hint">Email único para este participante</small>
                    </div>

                    <!-- Teléfono -->
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+504 1234-5678">
                    </div>

                    <!-- Cargo/Posición -->
                    <div class="mb-3">
                        <label class="form-label">Cargo/Posición</label>
                        <input type="text" name="position" class="form-control" placeholder="Ej: Gerente de Ventas">
                    </div>

                    <!-- Departamento -->
                    <?php if (!empty($departments)): ?>
                        <div class="mb-3">
                            <label class="form-label">Departamento</label>
                            <select name="department_id" class="form-select">
                                <option value="">Sin departamento</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>">
                                        <?= htmlspecialchars($dept['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                </div>
                <div class="card-footer text-end">
                    <div class="d-flex">
                        <a href="/admin/participants" class="btn btn-link">Cancelar</a>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <i class="fas fa-save me-2"></i>
                            Guardar Participante
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-label.required::after {
    content: " *";
    color: red;
}
</style>