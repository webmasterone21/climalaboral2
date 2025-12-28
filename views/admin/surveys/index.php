<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><?= htmlspecialchars($page_title ?? 'Encuestas') ?></h2>
            <p class="text-muted mb-0">Gestión de encuestas de clima laboral</p>
        </div>
        <div>
            <a href="/admin/surveys/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nueva Encuesta
            </a>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php if (!empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $message): ?>
            <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Error Message -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>
    
    <!-- Surveys Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($surveys)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Descripción</th>
                                <th>Fecha de Creación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($surveys as $survey): ?>
                                <tr>
                                    <td><?= htmlspecialchars($survey['id']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($survey['title'] ?? 'Sin título') ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars(substr($survey['description'] ?? '', 0, 100)) ?><?= strlen($survey['description'] ?? '') > 100 ? '...' : '' ?></td>
                                    <td><?= date('d/m/Y', strtotime($survey['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($survey['status'] ?? 'draft') === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($survey['status'] ?? 'draft') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/admin/surveys/<?= $survey['id'] ?>" class="btn btn-sm btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/surveys/<?= $survey['id'] ?>/edit" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="/admin/surveys/<?= $survey['id'] ?>/delete" style="display: inline;">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar esta encuesta?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay encuestas registradas</h4>
                    <p class="text-muted">Comienza creando tu primera encuesta de clima laboral</p>
                    <a href="/admin/surveys/create" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Crear Primera Encuesta
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
