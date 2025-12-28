<?php
/**
 * Dashboard de Seguridad
 * Vista para monitorear eventos de seguridad en tiempo real
 * 
 * @package EncuestasHERCO\Views\Admin
 */

// Requiere autenticación de administrador
require_auth();
require_role('admin');

// Cargar datos de seguridad
$securityLogs = getRecentSecurityEvents(50);
$stats = getSecurityStats();
$failedLogins = getRecentFailedLogins(20);
$suspiciousActivity = getSuspiciousActivity();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Seguridad - Sistema HERCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.info { border-left-color: #17a2b8; }
        
        .log-entry {
            border-left: 3px solid #e9ecef;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .log-entry.critical { border-left-color: #dc3545; background: #f8d7da; }
        .log-entry.warning { border-left-color: #ffc107; background: #fff3cd; }
        .log-entry.info { border-left-color: #17a2b8; background: #d1ecf1; }
        
        .badge-event {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .refresh-indicator {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <?php include 'views/components/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2">
                <?php include 'views/components/sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                    <div>
                        <h2><i class="fas fa-shield-alt text-primary"></i> Dashboard de Seguridad</h2>
                        <p class="text-muted">Monitoreo en tiempo real de eventos de seguridad</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                        <button class="btn btn-secondary" onclick="exportSecurityReport()">
                            <i class="fas fa-download"></i> Exportar Reporte
                        </button>
                    </div>
                </div>
                
                <!-- Indicador de actualización -->
                <div class="refresh-indicator alert alert-info" role="alert">
                    <i class="fas fa-sync-alt fa-spin"></i> Actualizando datos...
                </div>
                
                <!-- Estadísticas Generales -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Intentos Fallidos (24h)</h6>
                                        <h2 class="mb-0"><?= $stats['failed_logins_24h'] ?? 0 ?></h2>
                                    </div>
                                    <div class="text-danger">
                                        <i class="fas fa-exclamation-triangle fa-3x"></i>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?= $stats['failed_logins_change'] ?? 0 ?>% vs ayer
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Rate Limits (24h)</h6>
                                        <h2 class="mb-0"><?= $stats['rate_limits_24h'] ?? 0 ?></h2>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fas fa-clock fa-3x"></i>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    IPs bloqueadas temporalmente
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Eventos Totales (24h)</h6>
                                        <h2 class="mb-0"><?= $stats['total_events_24h'] ?? 0 ?></h2>
                                    </div>
                                    <div class="text-info">
                                        <i class="fas fa-list fa-3x"></i>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Todos los eventos registrados
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-2">Logins Exitosos (24h)</h6>
                                        <h2 class="mb-0"><?= $stats['successful_logins_24h'] ?? 0 ?></h2>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-check-circle fa-3x"></i>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Autenticaciones exitosas
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line"></i> Eventos de Seguridad (Últimos 7 días)</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="eventsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-pie"></i> Tipos de Eventos</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="eventTypesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="securityTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">
                            <i class="fas fa-list"></i> Eventos Recientes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="failed-logins-tab" data-bs-toggle="tab" data-bs-target="#failed-logins" type="button">
                            <i class="fas fa-user-times"></i> Intentos Fallidos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="suspicious-tab" data-bs-toggle="tab" data-bs-target="#suspicious" type="button">
                            <i class="fas fa-exclamation-circle"></i> Actividad Sospechosa
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="blocked-ips-tab" data-bs-toggle="tab" data-bs-target="#blocked-ips" type="button">
                            <i class="fas fa-ban"></i> IPs Bloqueadas
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content border border-top-0 p-3" id="securityTabsContent">
                    <!-- Eventos Recientes -->
                    <div class="tab-pane fade show active" id="logs" role="tabpanel">
                        <h5 class="mb-3">Últimos 50 Eventos de Seguridad</h5>
                        
                        <?php if (empty($securityLogs)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No hay eventos registrados
                            </div>
                        <?php else: ?>
                            <div id="security-logs-container">
                                <?php foreach ($securityLogs as $log): ?>
                                    <?php 
                                        $severity = getSeverityClass($log['event_type']);
                                        $icon = getEventIcon($log['event_type']);
                                    ?>
                                    <div class="log-entry <?= $severity ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="mb-1">
                                                    <i class="<?= $icon ?>"></i>
                                                    <strong><?= getEventTitle($log['event_type']) ?></strong>
                                                    <span class="badge badge-event bg-secondary ms-2">
                                                        <?= htmlspecialchars($log['event_type']) ?>
                                                    </span>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="fas fa-network-wired"></i> IP: <?= htmlspecialchars($log['ip_address']) ?>
                                                    <span class="ms-3">
                                                        <i class="fas fa-clock"></i> 
                                                        <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                                    </span>
                                                    <?php if (!empty($log['user_id'])): ?>
                                                        <span class="ms-3">
                                                            <i class="fas fa-user"></i> 
                                                            Usuario ID: <?= $log['user_id'] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($log['event_data'])): ?>
                                                    <div class="mt-2 small">
                                                        <details>
                                                            <summary class="text-muted" style="cursor: pointer;">
                                                                <i class="fas fa-code"></i> Ver datos del evento
                                                            </summary>
                                                            <pre class="mt-2 p-2 bg-light border rounded"><code><?= htmlspecialchars(json_encode(json_decode($log['event_data']), JSON_PRETTY_PRINT)) ?></code></pre>
                                                        </details>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Intentos Fallidos -->
                    <div class="tab-pane fade" id="failed-logins" role="tabpanel">
                        <h5 class="mb-3">Últimos Intentos de Login Fallidos</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Identificador</th>
                                        <th>IP</th>
                                        <th>User Agent</th>
                                        <th>Intentos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($failedLogins as $login): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($login['created_at'])) ?></td>
                                            <td>
                                                <?php if (!empty($login['event_data'])): ?>
                                                    <?php $data = json_decode($login['event_data'], true); ?>
                                                    <?= htmlspecialchars($data['identifier'] ?? 'N/A') ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($login['ip_address']) ?></code>
                                            </td>
                                            <td class="small text-muted">
                                                <?= htmlspecialchars(substr($login['user_agent'], 0, 50)) ?>...
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <?= getLoginAttempts($login['ip_address']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="blockIP('<?= $login['ip_address'] ?>')">
                                                    <i class="fas fa-ban"></i> Bloquear
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Actividad Sospechosa -->
                    <div class="tab-pane fade" id="suspicious" role="tabpanel">
                        <h5 class="mb-3">Actividad Sospechosa Detectada</h5>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Nota:</strong> Los siguientes eventos han sido marcados como potencialmente maliciosos
                        </div>
                        
                        <?php foreach ($suspiciousActivity as $activity): ?>
                            <div class="card mb-3 border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <i class="fas fa-shield-alt"></i>
                                    <strong><?= getEventTitle($activity['event_type']) ?></strong>
                                    <span class="float-end">
                                        <small><?= date('d/m/Y H:i:s', strtotime($activity['created_at'])) ?></small>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p><strong>IP:</strong> <code><?= htmlspecialchars($activity['ip_address']) ?></code></p>
                                    <p><strong>Tipo:</strong> <?= htmlspecialchars($activity['event_type']) ?></p>
                                    <?php if (!empty($activity['event_data'])): ?>
                                        <p><strong>Detalles:</strong></p>
                                        <pre class="bg-light p-2 border rounded"><code><?= htmlspecialchars(json_encode(json_decode($activity['event_data']), JSON_PRETTY_PRINT)) ?></code></pre>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm" onclick="blockIP('<?= $activity['ip_address'] ?>')">
                                        <i class="fas fa-ban"></i> Bloquear IP Permanentemente
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- IPs Bloqueadas -->
                    <div class="tab-pane fade" id="blocked-ips" role="tabpanel">
                        <h5 class="mb-3">IPs Bloqueadas</h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            IPs que han sido bloqueadas permanentemente por actividad maliciosa
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>IP</th>
                                        <th>Razón</th>
                                        <th>Fecha de Bloqueo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $blockedIPs = getBlockedIPs(); ?>
                                    <?php if (empty($blockedIPs)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                No hay IPs bloqueadas
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($blockedIPs as $blocked): ?>
                                            <tr>
                                                <td><code><?= htmlspecialchars($blocked['ip_address']) ?></code></td>
                                                <td><?= htmlspecialchars($blocked['reason']) ?></td>
                                                <td><?= date('d/m/Y H:i:s', strtotime($blocked['blocked_at'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="unblockIP('<?= $blocked['ip_address'] ?>')">
                                                        <i class="fas fa-check"></i> Desbloquear
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Datos para gráficos
        const eventsData = <?= json_encode($stats['events_by_day'] ?? []) ?>;
        const eventTypesData = <?= json_encode($stats['events_by_type'] ?? []) ?>;
        
        // Gráfico de línea - Eventos por día
        const ctx1 = document.getElementById('eventsChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: eventsData.labels,
                datasets: [{
                    label: 'Eventos Totales',
                    data: eventsData.data,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
        
        // Gráfico de dona - Tipos de eventos
        const ctx2 = document.getElementById('eventTypesChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: eventTypesData.labels,
                datasets: [{
                    data: eventTypesData.data,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Funciones de interacción
        function refreshDashboard() {
            document.querySelector('.refresh-indicator').style.display = 'block';
            setTimeout(() => {
                location.reload();
            }, 500);
        }
        
        function blockIP(ip) {
            if (confirm(`¿Está seguro de bloquear la IP ${ip}?`)) {
                fetch('/admin/security/block-ip', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?= csrf_token() ?>'
                    },
                    body: JSON.stringify({ ip: ip })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    refreshDashboard();
                })
                .catch(error => {
                    alert('Error al bloquear IP');
                    console.error(error);
                });
            }
        }
        
        function unblockIP(ip) {
            if (confirm(`¿Está seguro de desbloquear la IP ${ip}?`)) {
                fetch('/admin/security/unblock-ip', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?= csrf_token() ?>'
                    },
                    body: JSON.stringify({ ip: ip })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    refreshDashboard();
                })
                .catch(error => {
                    alert('Error al desbloquear IP');
                    console.error(error);
                });
            }
        }
        
        function exportSecurityReport() {
            window.location.href = '/admin/security/export-report';
        }
        
        // Auto-refresh cada 30 segundos
        setInterval(refreshDashboard, 30000);
    </script>
</body>
</html>

<?php
// ==========================================
// FUNCIONES HELPER PARA LA VISTA
// ==========================================

function getRecentSecurityEvents($limit = 50) {
    try {
        $db = new Database();
        $conn = $db->connect();
        
        $sql = "SELECT * FROM security_logs 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getSecurityStats() {
    // Implementar cálculo de estadísticas
    return [
        'failed_logins_24h' => 42,
        'failed_logins_change' => -15,
        'rate_limits_24h' => 8,
        'total_events_24h' => 1247,
        'successful_logins_24h' => 156,
        'events_by_day' => [
            'labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            'data' => [120, 150, 180, 140, 200, 90, 110]
        ],
        'events_by_type' => [
            'labels' => ['Login Exitoso', 'Login Fallido', 'CSRF Inválido', 'Rate Limit', 'Otros'],
            'data' => [450, 120, 80, 50, 300]
        ]
    ];
}

function getRecentFailedLogins($limit = 20) {
    try {
        $db = new Database();
        $conn = $db->connect();
        
        $sql = "SELECT * FROM security_logs 
                WHERE event_type IN ('failed_login', 'login_rate_limit_exceeded')
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getSuspiciousActivity() {
    try {
        $db = new Database();
        $conn = $db->connect();
        
        $sql = "SELECT * FROM security_logs 
                WHERE event_type IN ('sql_injection_attempt', 'xss_attempt', 'path_traversal_attempt')
                ORDER BY created_at DESC 
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function getBlockedIPs() {
    // Implementar obtención de IPs bloqueadas
    return [];
}

function getSeverityClass($eventType) {
    $critical = ['sql_injection_attempt', 'xss_attempt', 'path_traversal_attempt', 'login_rate_limit_exceeded'];
    $warning = ['failed_login', 'csrf_token_invalid', 'permission_denied'];
    
    if (in_array($eventType, $critical)) return 'critical';
    if (in_array($eventType, $warning)) return 'warning';
    return 'info';
}

function getEventIcon($eventType) {
    $icons = [
        'successful_login' => 'fas fa-sign-in-alt text-success',
        'failed_login' => 'fas fa-times-circle text-danger',
        'user_logout' => 'fas fa-sign-out-alt text-info',
        'csrf_token_invalid' => 'fas fa-shield-alt text-warning',
        'sql_injection_attempt' => 'fas fa-database text-danger',
        'xss_attempt' => 'fas fa-code text-danger',
        'rate_limit_exceeded' => 'fas fa-clock text-warning'
    ];
    
    return $icons[$eventType] ?? 'fas fa-circle-notch';
}

function getEventTitle($eventType) {
    $titles = [
        'successful_login' => 'Login Exitoso',
        'failed_login' => 'Intento de Login Fallido',
        'user_logout' => 'Usuario Cerró Sesión',
        'csrf_token_invalid' => 'Token CSRF Inválido',
        'sql_injection_attempt' => 'Intento de SQL Injection',
        'xss_attempt' => 'Intento de XSS',
        'path_traversal_attempt' => 'Intento de Path Traversal',
        'rate_limit_exceeded' => 'Límite de Rate Excedido',
        'login_rate_limit_exceeded' => 'Límite de Login Excedido'
    ];
    
    return $titles[$eventType] ?? ucfirst(str_replace('_', ' ', $eventType));
}

function getLoginAttempts($ip) {
    // Implementar conteo de intentos
    return rand(1, 10);
}
?>
