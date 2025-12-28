<?php
/**
 * Vista: Listado de Usuarios - Sistema HERCO v2.0
 * views/admin/users/index.php
 * 
 * VERSIÓN CORREGIDA Y LIMPIA
 * ✅ Estructura completa y funcional
 * ✅ Enlaces de edición correctos
 * ✅ Manejo apropiado de datos
 * ✅ Diseño profesional
 */

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

// Datos recibidos del controlador
$users = $data['users'] ?? [];
$pagination = $data['pagination'] ?? [];
$stats = $data['stats'] ?? [];
$filters = $data['filters'] ?? [];
$roles = $data['roles'] ?? [];
$statuses = $data['statuses'] ?? [];

// Extraer datos de paginación
$currentPage = $pagination['current_page'] ?? 1;
$totalPages = $pagination['total_pages'] ?? 1;
$totalUsers = $stats['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema HERCO</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 20px 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 25px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .stat-card {
            border-radius: 12px;
            padding: 25px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .badge-role {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        
        .status-active {
            color: #10b981;
        }
        
        .status-inactive {
            color: #ef4444;
        }
        
        .table-actions {
            white-space: nowrap;
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 12px 12px 0 0 !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="text-center mb-4">
                    <h4 class="fw-bold">🏢 Sistema HERCO</h4>
                    <small class="text-white-50">v2.0.0</small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/surveys">
                        <i class="fas fa-clipboard-list me-2"></i> Encuestas
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/questions">
                        <i class="fas fa-question-circle me-2"></i> Preguntas
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/participants">
                        <i class="fas fa-users me-2"></i> Participantes
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/reports">
                        <i class="fas fa-chart-bar me-2"></i> Reportes
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/companies">
                        <i class="fas fa-building me-2"></i> Empresas
                    </a>
                    <a class="nav-link active" href="<?= BASE_URL ?>admin/users">
                        <i class="fas fa-user-cog me-2"></i> Usuarios
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/settings">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                    
                    <hr class="text-white-50 my-3">
                    
                    <a class="nav-link" href="<?= BASE_URL ?>logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Gestión de Usuarios</h2>
                        <p class="text-muted mb-0">Administra los usuarios del sistema</p>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>admin/users/create" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i> Nuevo Usuario
                        </a>
                    </div>
                </div>
                
                <!-- Alertas -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card bg-white">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold"><?= $stats['total'] ?? 0 ?></h3>
                                    <small class="text-muted">Total Usuarios</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stat-card bg-white">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold"><?= $stats['active'] ?? 0 ?></h3>
                                    <small class="text-muted">Activos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stat-card bg-white">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold"><?= $stats['admins'] ?? 0 ?></h3>
                                    <small class="text-muted">Administradores</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="stat-card bg-white">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                                    <i class="fas fa-user-times"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold"><?= $stats['inactive'] ?? 0 ?></h3>
                                    <small class="text-muted">Inactivos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros de Búsqueda -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= BASE_URL ?>admin/users" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Nombre, email..." 
                                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Rol</label>
                                <select name="role" class="form-select">
                                    <option value="">Todos los roles</option>
                                    <?php foreach ($roles as $roleKey => $roleLabel): ?>
                                        <option value="<?= $roleKey ?>" <?= ($filters['role'] ?? '') === $roleKey ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($roleLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($statuses as $statusKey => $statusLabel): ?>
                                        <option value="<?= $statusKey ?>" <?= ($filters['status'] ?? '') === $statusKey ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($statusLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tabla de Usuarios -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-list me-2"></i>Lista de Usuarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-users fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted">No hay usuarios para mostrar</h5>
                                <p class="text-muted">
                                    <?php if (!empty($filters['search']) || !empty($filters['role']) || !empty($filters['status'])): ?>
                                        No se encontraron usuarios con los filtros aplicados.
                                        <br>
                                        <a href="<?= BASE_URL ?>admin/users" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-times me-1"></i> Limpiar Filtros
                                        </a>
                                    <?php else: ?>
                                        Comienza agregando tu primer usuario.
                                        <br>
                                        <a href="<?= BASE_URL ?>admin/users/create" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-plus me-1"></i> Crear Usuario
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Departamento</th>
                                            <th>Estado</th>
                                            <th>Último Acceso</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($user['avatar'])): ?>
                                                            <img src="<?= htmlspecialchars($user['avatar']) ?>" class="user-avatar me-2" alt="Avatar">
                                                        <?php else: ?>
                                                            <div class="user-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                                                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?= htmlspecialchars($user['name'] ?? 'Sin nombre') ?></strong>
                                                            <?php if (!empty($user['position'])): ?>
                                                                <br><small class="text-muted"><?= htmlspecialchars($user['position']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td>
                                                    <?php
                                                    $roleBadges = [
                                                        'super_admin' => 'danger',
                                                        'admin' => 'warning',
                                                        'manager' => 'info',
                                                        'hr' => 'primary',
                                                        'user' => 'secondary',
                                                        'viewer' => 'light'
                                                    ];
                                                    $badgeClass = $roleBadges[$user['role']] ?? 'secondary';
                                                    $roleLabel = $roles[$user['role']] ?? 'Usuario';
                                                    ?>
                                                    <span class="badge bg-<?= $badgeClass ?> badge-role">
                                                        <?= htmlspecialchars($roleLabel) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($user['department'])): ?>
                                                        <small class="text-muted">
                                                            <i class="bi bi-diagram-3"></i> 
                                                            <?= htmlspecialchars($user['department']) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">-</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <i class="bi bi-circle-fill status-active me-1"></i> Activo
                                                    <?php else: ?>
                                                        <i class="bi bi-circle-fill status-inactive me-1"></i> Inactivo
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($user['last_login'])): ?>
                                                        <small><?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></small>
                                                    <?php else: ?>
                                                        <small class="text-muted">Nunca</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center table-actions">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <!-- Botón Editar - URL CORREGIDA -->
                                                        <a href="<?= BASE_URL ?>admin/users/<?= $user['id'] ?>/edit" 
                                                           class="btn btn-outline-primary" 
                                                           title="Editar usuario">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <!-- Botón Eliminar - Solo si no es el usuario actual -->
                                                        <?php if ($user['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger" 
                                                                    onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>')"
                                                                    title="Eliminar usuario">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" 
                                                                    class="btn btn-outline-secondary" 
                                                                    disabled
                                                                    title="No puedes eliminarte a ti mismo">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <?php if ($totalPages > 1): ?>
                                <div class="d-flex justify-content-center mt-4">
                                    <nav aria-label="Paginación de usuarios">
                                        <ul class="pagination pagination-sm">
                                            <!-- Primera página -->
                                            <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                                                <a class="page-link" href="<?= BASE_URL ?>admin/users?page=1<?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['role']) ? '&role=' . urlencode($filters['role']) : '' ?><?= !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '' ?>">
                                                    <i class="fas fa-angle-double-left"></i>
                                                </a>
                                            </li>
                                            
                                            <!-- Páginas numeradas -->
                                            <?php 
                                            $startPage = max(1, $currentPage - 2);
                                            $endPage = min($totalPages, $currentPage + 2);
                                            
                                            for ($i = $startPage; $i <= $endPage; $i++): 
                                            ?>
                                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                                    <a class="page-link" href="<?= BASE_URL ?>admin/users?page=<?= $i ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['role']) ? '&role=' . urlencode($filters['role']) : '' ?><?= !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '' ?>">
                                                        <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <!-- Última página -->
                                            <li class="page-item <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                                                <a class="page-link" href="<?= BASE_URL ?>admin/users?page=<?= $totalPages ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['role']) ? '&role=' . urlencode($filters['role']) : '' ?><?= !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : '' ?>">
                                                    <i class="fas fa-angle-double-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                                
                                <!-- Info de paginación -->
                                <div class="text-center text-muted small mt-2">
                                    Mostrando <?= count($users) ?> de <?= $totalUsers ?> usuarios
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">¿Estás seguro de que deseas eliminar al usuario con email:</p>
                    <p class="mb-3"><strong id="deleteUserEmail"></strong>?</p>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Atención:</strong> Esta acción no se puede deshacer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Eliminar Usuario
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Función para confirmar eliminación
        function confirmDelete(userId, userEmail) {
            document.getElementById('deleteUserEmail').textContent = userEmail;
            document.getElementById('deleteForm').action = '<?= BASE_URL ?>admin/users/' + userId + '/delete';
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Auto-ocultar alertas después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>
