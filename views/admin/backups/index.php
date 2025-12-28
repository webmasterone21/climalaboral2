<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gestión de Respaldos' ?> - Sistema de Encuestas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <?php include __DIR__ . '/../layouts/topbar.php'; ?>

            <!-- Content Wrapper -->
            <div class="content-wrapper">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0"><i class="fas fa-database"></i> Gestión de Respaldos</h1>
                        <p class="text-muted">Administre los respaldos automáticos y manuales del sistema</p>
                    </div>
                    <div>
                        <button class="btn btn-primary me-2" onclick="createBackup()">
                            <i class="fas fa-plus"></i> Crear Respaldo
                        </button>
                        <button class="btn btn-outline-secondary" onclick="refreshBackupList()">
                            <i class="fas fa-refresh"></i> Actualizar
                        </button>
                    </div>
                </div>

                <!-- Alert Container -->
                <div id="alert-container"></div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h4><?= $stats['successful_backups'] ?? 0 ?></h4>
                                        <small>Respaldos Exitosos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h4><?= $stats['failed_backups'] ?? 0 ?></h4>
                                        <small>Respaldos Fallidos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-hdd fa-2x"></i>
                                    </div>
                                    <div>
                                        <h4><?= $stats['total_size'] ?? '0 MB' ?></h4>
                                        <small>Espacio Total</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-percentage fa-2x"></i>
                                    </div>
                                    <div>
                                        <h4><?= $stats['success_rate'] ?? 0 ?>%</h4>
                                        <small>Tasa de Éxito</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Respaldos -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog"></i> Configuración de Respaldos Automáticos
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="backup-config-form">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="backup_frequency" class="form-label">Frecuencia</label>
                                        <select class="form-control" id="backup_frequency" name="backup_frequency">
                                            <option value="daily" <?= ($config['backup_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>>Diario</option>
                                            <option value="weekly" <?= ($config['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Semanal</option>
                                            <option value="monthly" <?= ($config['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>Mensual</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="backup_time" class="form-label">Hora de Respaldo</label>
                                        <input type="time" class="form-control" id="backup_time" name="backup_time" 
                                               value="<?= $config['backup_time'] ?? '02:00' ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label for="max_backups" class="form-label">Máximo de Respaldos</label>
                                        <input type="number" class="form-control" id="max_backups" name="max_backups" 
                                               value="<?= $config['max_backups'] ?? 10 ?>" min="1" max="50">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="auto_backup" name="auto_backup" 
                                                   <?= ($config['auto_backup'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="auto_backup">Automático</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="compress_backups" name="compress_backups" 
                                                   <?= ($config['compress_backups'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="compress_backups">Comprimir</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                                <span class="text-muted">
                                    <i class="fas fa-clock"></i> 
                                    Último respaldo: <?= $config['last_backup'] ? date('d/m/Y H:i', strtotime($config['last_backup'])) : 'Nunca' ?>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Respaldos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Respaldos Disponibles
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="backups-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file"></i> Archivo</th>
                                        <th><i class="fas fa-hdd"></i> Tamaño</th>
                                        <th><i class="fas fa-calendar"></i> Fecha</th>
                                        <th><i class="fas fa-robot"></i> Tipo</th>
                                        <th><i class="fas fa-check-circle"></i> Estado</th>
                                        <th><i class="fas fa-cogs"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($backups)): ?>
                                        <?php foreach ($backups as $backup): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-database text-primary"></i>
                                                    <strong><?= htmlspecialchars($backup['filename']) ?></strong>
                                                </td>
                                                <td><?= formatBytes($backup['file_size']) ?></td>
                                                <td><?= date('d/m/Y H:i:s', strtotime($backup['created_at'])) ?></td>
                                                <td>
                                                    <?php if ($backup['backup_type'] === 'automatic'): ?>
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-robot"></i> Automático
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-user"></i> Manual
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($backup['status'] === 'success'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check"></i> Exitoso
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times"></i> Error
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($backup['status'] === 'success'): ?>
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-outline-primary" 
                                                                    onclick="downloadBackup('<?= htmlspecialchars($backup['filename']) ?>')"
                                                                    title="Descargar">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                            <button class="btn btn-outline-success" 
                                                                    onclick="restoreBackup('<?= htmlspecialchars($backup['filename']) ?>')"
                                                                    title="Restaurar">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger" 
                                                                    onclick="deleteBackup('<?= htmlspecialchars($backup['filename']) ?>')"
                                                                    title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-danger btn-sm" 
                                                                onclick="deleteBackup('<?= htmlspecialchars($backup['filename']) ?>')"
                                                                title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-database fa-3x mb-3 d-block"></i>
                                                <p>No hay respaldos disponibles</p>
                                                <button class="btn btn-primary" onclick="createBackup()">
                                                    <i class="fas fa-plus"></i> Crear primer respaldo
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Progreso -->
    <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="progressModalLabel">
                        <i class="fas fa-spinner fa-spin"></i> Procesando Respaldo
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%"></div>
                    </div>
                    <p id="progress-message">Iniciando proceso...</p>
                    <div id="progress-details" class="small text-muted"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Acción
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirm-message">
                    <!-- Mensaje de confirmación -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirm-action">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador de guardado -->
    <div class="save-indicator" id="save-indicator">
        <i class="fas fa-check"></i> Guardado exitosamente
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let progressModal, confirmModal;

        document.addEventListener('DOMContentLoaded', function() {
            progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
            confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        });

        // Función para mostrar alertas
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.innerHTML = alertHtml;
            
            // Auto-remove alert after 5 seconds
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }

        // Crear respaldo manual
        function createBackup() {
            const compress = document.getElementById('compress_backups').checked;
            
            progressModal.show();
            updateProgressBar(0, 'Iniciando respaldo...');

            fetch('<?= BASE_URL ?>admin/backups/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `compress=${compress ? '1' : '0'}`
            })
            .then(response => response.json())
            .then(data => {
                progressModal.hide();
                if (data.success) {
                    showAlert('success', `Respaldo creado exitosamente: ${data.filename} (${data.size})`);
                    refreshBackupList();
                } else {
                    showAlert('danger', 'Error al crear respaldo: ' + data.error);
                }
            })
            .catch(error => {
                progressModal.hide();
                showAlert('danger', 'Error de conexión: ' + error.message);
            });
        }

        // Descargar respaldo
        function downloadBackup(filename) {
            window.location.href = `<?= BASE_URL ?>admin/backups/download?filename=${encodeURIComponent(filename)}`;
        }

        // Restaurar respaldo
        function restoreBackup(filename) {
            document.getElementById('confirm-message').innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡ATENCIÓN!</strong> Esta acción reemplazará completamente la base de datos actual con el respaldo seleccionado.
                </div>
                <p>¿Está seguro de que desea restaurar el respaldo:</p>
                <p><strong>${filename}</strong></p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            `;
            
            document.getElementById('confirm-action').onclick = function() {
                confirmModal.hide();
                executeRestore(filename);
            };
            
            confirmModal.show();
        }

        function executeRestore(filename) {
            progressModal.show();
            updateProgressBar(0, 'Creando respaldo de seguridad...');

            fetch('<?= BASE_URL ?>admin/backups/restore', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'filename=' + encodeURIComponent(filename)
            })
            .then(response => response.json())
            .then(data => {
                progressModal.hide();
                if (data.success) {
                    showAlert('success', data.message);
                    if (data.safety_backup) {
                        showAlert('info', `Se creó respaldo de seguridad: ${data.safety_backup}`);
                    }
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('danger', 'Error en restauración: ' + data.error);
                }
            })
            .catch(error => {
                progressModal.hide();
                showAlert('danger', 'Error de conexión: ' + error.message);
            });
        }

        // Eliminar respaldo
        function deleteBackup(filename) {
            document.getElementById('confirm-message').innerHTML = `
                <p>¿Está seguro de que desea eliminar el respaldo:</p>
                <p><strong>${filename}</strong></p>
                <p class="text-muted"><small>Esta acción no se puede deshacer.</small></p>
            `;
            
            document.getElementById('confirm-action').onclick = function() {
                confirmModal.hide();
                executeDelete(filename);
            };
            
            confirmModal.show();
        }

        function executeDelete(filename) {
            fetch('<?= BASE_URL ?>admin/backups/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'filename=' + encodeURIComponent(filename)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Respaldo eliminado exitosamente');
                    refreshBackupList();
                } else {
                    showAlert('danger', 'Error al eliminar: ' + data.error);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error de conexión: ' + error.message);
            });
        }

        // Actualizar lista de respaldos
        function refreshBackupList() {
            location.reload();
        }

        // Guardar configuración
        document.getElementById('backup-config-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('<?= BASE_URL ?>admin/backups/config', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Configuración guardada exitosamente');
                    showSaveIndicator();
                } else {
                    showAlert('danger', 'Error al guardar configuración: ' + data.error);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error de conexión: ' + error.message);
            });
        });

        // Actualizar barra de progreso
        function updateProgressBar(percentage, message) {
            const progressBar = document.querySelector('#progressModal .progress-bar');
            const progressMessage = document.getElementById('progress-message');
            
            progressBar.style.width = percentage + '%';
            progressMessage.textContent = message;
        }

        // Mostrar indicador de guardado
        function showSaveIndicator() {
            const indicator = document.getElementById('save-indicator');
            indicator.classList.add('show');
            setTimeout(() => {
                indicator.classList.remove('show');
            }, 2000);
        }

        // Función auxiliar para formatear bytes
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Polling para actualizar estadísticas cada 30 segundos
        setInterval(function() {
            fetch('<?= BASE_URL ?>admin/backups/stats', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar estadísticas sin recargar la página
                    // Implementar actualización de stats si es necesario
                }
            })
            .catch(error => {
                console.log('Error updating stats:', error);
            });
        }, 30000);
    </script>
</body>
</html>

<?php
// Función auxiliar para formatear bytes (si no existe globalmente)
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
?>