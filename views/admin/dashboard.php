<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Dashboard') ?> - <?= htmlspecialchars($app_name ?? 'Sistema HERCO') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        .action-card {
            border-radius: 12px;
            padding: 20px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .action-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
                    <a class="nav-link active" href="/admin/dashboard">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="/admin/surveys">
                        <i class="fas fa-clipboard-list me-2"></i> Encuestas
                    </a>
                    <a class="nav-link" href="/admin/questions">
                        <i class="fas fa-question-circle me-2"></i> Preguntas
                    </a>
                    <a class="nav-link" href="/admin/participants">
                        <i class="fas fa-users me-2"></i> Participantes
                    </a>
                    <a class="nav-link" href="/admin/reports">
                        <i class="fas fa-chart-bar me-2"></i> Reportes
                    </a>
                    <a class="nav-link" href="/admin/companies">
                        <i class="fas fa-building me-2"></i> Empresas
                    </a>
                    <a class="nav-link" href="/admin/users">
                        <i class="fas fa-user-cog me-2"></i> Usuarios
                    </a>
                    <a class="nav-link" href="/admin/settings">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                    
                    <hr class="text-white-50 my-3">
                    
                    <a class="nav-link" href="/logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold"><?= htmlspecialchars($page_title ?? 'Dashboard') ?></h2>
                        <p class="text-muted mb-0">
                            Bienvenido, <?= htmlspecialchars($current_user['first_name'] ?? 'Usuario') ?> 
                            <?= htmlspecialchars($current_user['last_name'] ?? '') ?>
                        </p>
                    </div>
                    <div>
                        <span class="badge bg-success">Sistema Activo</span>
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
                
                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <!-- Total Users -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Usuarios Activos</p>
                                    <h3 class="fw-bold mb-0"><?= number_format($stats['total_users'] ?? 0) ?></h3>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Surveys -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total Encuestas</p>
                                    <h3 class="fw-bold mb-0"><?= number_format($stats['total_surveys'] ?? 0) ?></h3>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Responses -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Respuestas</p>
                                    <h3 class="fw-bold mb-0"><?= number_format($stats['total_responses'] ?? 0) ?></h3>
                                </div>
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Companies -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Empresas</p>
                                    <h3 class="fw-bold mb-0"><?= number_format($stats['total_companies'] ?? 0) ?></h3>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($quick_actions ?? [] as $action): ?>
                                <div class="col-md-3">
                                    <a href="<?= htmlspecialchars($action['url']) ?>" class="action-card">
                                        <div class="text-center">
                                            <div class="mb-3">
                                                <i class="fas fa-<?= htmlspecialchars($action['icon']) ?> fa-3x text-<?= htmlspecialchars($action['color']) ?>"></i>
                                            </div>
                                            <h6 class="fw-bold"><?= htmlspecialchars($action['title']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($action['description']) ?></small>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Surveys -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Encuestas Recientes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_surveys)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Empresa</th>
                                            <th>Creado por</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_surveys as $survey): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($survey['title'] ?? 'Sin título') ?></td>
                                                <td><?= htmlspecialchars($survey['company_name'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?= htmlspecialchars($survey['first_name'] ?? '') ?> 
                                                    <?= htmlspecialchars($survey['last_name'] ?? '') ?>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($survey['created_at'] ?? 'now')) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= ($survey['status'] ?? 'draft') === 'active' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($survey['status'] ?? 'draft') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="/admin/surveys/<?= $survey['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                <p>No hay encuestas recientes</p>
                                <a href="/admin/surveys/create" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Crear Primera Encuesta
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
