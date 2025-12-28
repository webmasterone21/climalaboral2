<?php
/**
 * Vista: Editar Usuario - VERSIÓN FINAL
 * views/admin/users/edit.php
 * 
 * Formulario de edición de usuarios existentes
 * 
 * VERSIÓN: v2.0.4 (FINAL)
 * 
 * CORRECCIONES APLICADAS:
 * ✅ v2.0.1 - Manejo de departamentos como strings
 * ✅ v2.0.2 - Campo role con hidden input correcto
 * ✅ v2.0.3 - Permisos para admin y super_admin
 * ✅ v2.0.4 - Normalización de rol 'usuario' a 'user'
 * 
 * PERMISOS:
 * - super_admin: Puede cambiar roles y eliminar usuarios
 * - admin: Puede cambiar roles y eliminar usuarios
 * - usuario/user: Solo puede ver (campo bloqueado)
 * 
 * Variables disponibles:
 * - $user: Datos del usuario a editar
 * - $roles: Array de roles disponibles
 * - $statuses: Array de estados disponibles
 * - $departments: Array de strings con nombres de departamentos
 * - $csrf_token: Token CSRF para seguridad
 * 
 * @package EncuestasHERCO\Views
 * @version 2.0.4
 */

// Datos recibidos del controlador
$user = $data['user'] ?? [];
$roles = $data['roles'] ?? [];
$statuses = $data['statuses'] ?? [];
$departments = $data['departments'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - <?= htmlspecialchars($user['name'] ?? '') ?> - Sistema HERCO</title>
    
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
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .card-header {
            border-bottom: 1px solid #e2e8f0;
        }
        
        .form-label {
            font-weight: 500;
            color: #334155;
        }
        
        .required::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 38px;
            color: #64748b;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
        }
        
        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }
        
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .password-toggle-section {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .password-fields {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .password-fields.show {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .delete-user-btn {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .delete-user-btn:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-active { background-color: #d1fae5; color: #065f46; }
        .status-inactive { background-color: #f3f4f6; color: #374151; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-suspended { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    
    <div class="container-fluid">
        <div class="row">
            
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="text-center mb-4">
                    <h4 class="fw-bold">HERCO</h4>
                    <small class="text-white-50">Sistema de Encuestas</small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard">
                        <i class="fas fa-chart-line me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/surveys">
                        <i class="fas fa-clipboard-list me-2"></i> Encuestas
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/participants">
                        <i class="fas fa-users me-2"></i> Participantes
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/reports">
                        <i class="fas fa-chart-bar me-2"></i> Reportes
                    </a>
                    <a class="nav-link active" href="<?= BASE_URL ?>admin/users">
                        <i class="fas fa-user-shield me-2"></i> Usuarios
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/companies">
                        <i class="fas fa-building me-2"></i> Empresas
                    </a>
                    <a class="nav-link" href="<?= BASE_URL ?>admin/settings">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                </nav>
                
                <div class="mt-4 px-3">
                    <hr class="text-white-50">
                    <div class="text-white-50 small">
                        <i class="fas fa-user-circle me-2"></i>
                        <?= htmlspecialchars($this->user['name'] ?? 'Usuario') ?>
                    </div>
                    <a href="<?= BASE_URL ?>auth/logout" class="btn btn-sm btn-outline-light mt-2 w-100">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-user-edit text-primary me-2"></i>
                            Editar Usuario
                        </h2>
                        <p class="text-muted mb-0">Modificar información del usuario existente</p>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>admin/users" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Volver a lista
                        </a>
                    </div>
                </div>
                
                <!-- Alertas de sesión -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <div class="row">
                    
                    <!-- Columna izquierda: Información del usuario -->
                    <div class="col-md-4">
                        
                        <!-- Card de información -->
                        <div class="info-card">
                            <div class="text-center mb-3">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? 'Usuario') ?>&size=100&background=random" 
                                     alt="Avatar" 
                                     class="user-avatar">
                            </div>
                            
                            <h5 class="text-center mb-3"><?= htmlspecialchars($user['name'] ?? '') ?></h5>
                            
                            <div class="info-item">
                                <span><i class="fas fa-envelope me-2"></i> Email:</span>
                                <strong><?= htmlspecialchars($user['email'] ?? '') ?></strong>
                            </div>
                            
                            <div class="info-item">
                                <span><i class="fas fa-shield-alt me-2"></i> Rol:</span>
                                <strong><?= ucfirst($user['role'] ?? 'usuario') ?></strong>
                            </div>
                            
                            <div class="info-item">
                                <span><i class="fas fa-circle me-2"></i> Estado:</span>
                                <span class="status-badge status-<?= $user['status'] ?? 'active' ?>">
                                    <?= ucfirst($user['status'] ?? 'activo') ?>
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <span><i class="fas fa-calendar-plus me-2"></i> Registro:</span>
                                <strong><?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?></strong>
                            </div>
                            
                            <?php if (!empty($user['last_login'])): ?>
                            <div class="info-item">
                                <span><i class="fas fa-sign-in-alt me-2"></i> Último acceso:</span>
                                <strong><?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Botón eliminar -->
                        <?php 
                        $canDeleteUsers = in_array($this->user['role'] ?? '', ['super_admin', 'admin']);
                        if ($canDeleteUsers && $user['id'] != $this->user['id']): 
                        ?>
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title text-danger mb-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Zona de Peligro
                                </h6>
                                <p class="small text-muted">Esta acción desactivará al usuario permanentemente.</p>
                                <button type="button" 
                                        class="delete-user-btn w-100" 
                                        id="deleteUserBtn">
                                    <i class="fas fa-trash-alt me-2"></i>
                                    Eliminar Usuario
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Columna derecha: Formulario de edición -->
                    <div class="col-md-8">
                        
                        <form id="editUserForm" 
                              method="POST" 
                              action="<?= BASE_URL ?>admin/users/<?= $user['id'] ?>/update"
                              class="needs-validation" 
                              novalidate>
                            
                            <!-- Token CSRF -->
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            
                            <!-- Información Personal -->
                            <div class="card mb-3">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        Información Personal
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        
                                        <!-- Nombre completo -->
                                        <div class="col-md-6">
                                            <label for="name" class="form-label required">Nombre Completo</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="name" 
                                                   name="name" 
                                                   value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                                   placeholder="Ej: Juan Pérez García"
                                                   required
                                                   minlength="3">
                                            <div class="invalid-feedback">
                                                El nombre debe tener al menos 3 caracteres.
                                            </div>
                                        </div>
                                        
                                        <!-- Email -->
                                        <div class="col-md-6">
                                            <label for="email" class="form-label required">Correo Electrónico</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                                   placeholder="usuario@empresa.com"
                                                   required>
                                            <div class="invalid-feedback">
                                                Ingrese un correo electrónico válido.
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cambiar Contraseña -->
                            <div class="card mb-3">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-key me-2 text-primary"></i>
                                        Cambiar Contraseña
                                    </h5>
                                </div>
                                <div class="card-body">
                                    
                                    <!-- Toggle para cambiar contraseña -->
                                    <div class="password-toggle-section">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="changePassword" 
                                                   name="change_password">
                                            <label class="form-check-label" for="changePassword">
                                                <i class="fas fa-lock me-2"></i>
                                                ¿Desea cambiar la contraseña?
                                            </label>
                                        </div>
                                        <small class="text-muted">
                                            Active esta opción solo si necesita actualizar la contraseña del usuario.
                                        </small>
                                    </div>
                                    
                                    <!-- Campos de contraseña (ocultos inicialmente) -->
                                    <div id="passwordFields" class="password-fields">
                                        <div class="row g-3">
                                            
                                            <!-- Nueva contraseña -->
                                            <div class="col-md-6">
                                                <label for="password" class="form-label">Nueva Contraseña</label>
                                                <div class="position-relative">
                                                    <input type="password" 
                                                           class="form-control" 
                                                           id="password" 
                                                           name="password"
                                                           placeholder="Mínimo 6 caracteres"
                                                           minlength="6">
                                                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                                                </div>
                                                <div id="passwordStrength" class="password-strength"></div>
                                                <div class="invalid-feedback">
                                                    La contraseña debe tener al menos 6 caracteres.
                                                </div>
                                            </div>
                                            
                                            <!-- Confirmar contraseña -->
                                            <div class="col-md-6">
                                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                                <div class="position-relative">
                                                    <input type="password" 
                                                           class="form-control" 
                                                           id="password_confirmation" 
                                                           name="password_confirmation"
                                                           placeholder="Repita la contraseña">
                                                    <i class="fas fa-eye password-toggle" id="togglePasswordConfirm"></i>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Las contraseñas no coinciden.
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información Profesional -->
                            <div class="card mb-3">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-briefcase me-2 text-primary"></i>
                                        Información Profesional
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        
                                        <!-- Rol -->
                                        <div class="col-md-6">
                                            <label for="role" class="form-label required">Rol en el Sistema</label>
                                            <?php 
                                            // Permitir cambio de roles a super_admin y admin
                                            $canChangeRoles = in_array($this->user['role'] ?? '', ['super_admin', 'admin']);
                                            
                                            // Normalizar rol del usuario para compatibilidad
                                            $userRole = $user['role'] ?? '';
                                            // Si el rol es 'usuario', convertir a 'user' para compatibilidad
                                            if ($userRole === 'usuario') {
                                                $userRole = 'user';
                                            }
                                            ?>
                                            <?php if ($canChangeRoles): ?>
                                                <select class="form-select" 
                                                        id="role" 
                                                        name="role" 
                                                        required>
                                                    <option value="">Seleccione un rol...</option>
                                                    <?php foreach ($roles as $roleKey => $roleLabel): ?>
                                                        <option value="<?= htmlspecialchars($roleKey) ?>" 
                                                                <?= $userRole === $roleKey ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($roleLabel) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Seleccione un rol del sistema.
                                                </div>
                                            <?php else: ?>
                                                <!-- Campo solo lectura para usuarios sin permisos -->
                                                <input type="text" 
                                                       class="form-control" 
                                                       value="<?= htmlspecialchars($roles[$userRole] ?? ucfirst($user['role'] ?? '')) ?>" 
                                                       disabled>
                                                <input type="hidden" name="role" value="<?= htmlspecialchars($user['role'] ?? '') ?>">
                                                <small class="text-muted">
                                                    <i class="fas fa-lock me-1"></i>
                                                    Solo administradores pueden cambiar roles.
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Estado -->
                                        <div class="col-md-6">
                                            <label for="status" class="form-label required">Estado</label>
                                            <select class="form-select" 
                                                    id="status" 
                                                    name="status" 
                                                    required>
                                                <?php foreach ($statuses as $statusKey => $statusLabel): ?>
                                                    <option value="<?= htmlspecialchars($statusKey) ?>" 
                                                            <?= ($user['status'] ?? 'active') === $statusKey ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($statusLabel) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Seleccione el estado del usuario.
                                            </div>
                                        </div>
                                        
                                        <!-- Departamento -->
                                        <div class="col-md-12">
                                            <label class="form-label">Departamento</label>
                                            <?php if (!empty($departments)): ?>
                                                <select class="form-select" name="department" id="department">
                                                    <option value="">Sin departamento asignado</option>
                                                    <?php foreach ($departments as $dept): ?>
                                                        <option value="<?= htmlspecialchars($dept) ?>" 
                                                                <?= (isset($user['department']) && $user['department'] == $dept) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($dept) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="department"
                                                       value="<?= htmlspecialchars($user['department'] ?? '') ?>"
                                                       placeholder="Nombre del departamento">
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Seleccione o escriba el departamento del usuario
                                            </small>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= BASE_URL ?>admin/users" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>
                                            Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                        </form>
                        
                    </div>
                    
                </div>
                
            </div>
            
        </div>
    </div>
    
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script>
        // ==========================================
        // 1. TOGGLE DE CONTRASEÑA
        // ==========================================
        document.getElementById('changePassword').addEventListener('change', function() {
            const passwordFields = document.getElementById('passwordFields');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            
            if (this.checked) {
                passwordFields.classList.add('show');
                passwordInput.setAttribute('required', 'required');
                confirmInput.setAttribute('required', 'required');
            } else {
                passwordFields.classList.remove('show');
                passwordInput.removeAttribute('required');
                confirmInput.removeAttribute('required');
                passwordInput.value = '';
                confirmInput.value = '';
                passwordInput.classList.remove('is-invalid');
                confirmInput.classList.remove('is-invalid');
                document.getElementById('passwordStrength').innerHTML = '';
                document.getElementById('passwordStrength').className = 'password-strength';
            }
        });
        
        // ==========================================
        // 2. MOSTRAR/OCULTAR CONTRASEÑA
        // ==========================================
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
            const passwordInput = document.getElementById('password_confirmation');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // ==========================================
        // 3. INDICADOR DE FUERZA DE CONTRASEÑA
        // ==========================================
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 3) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });
        
        // ==========================================
        // 4. VALIDACIÓN DE FORMULARIO
        // ==========================================
        (function() {
            'use strict';
            
            const form = document.getElementById('editUserForm');
            
            form.addEventListener('submit', function(event) {
                // Validar contraseñas si el checkbox está marcado
                const changePassword = document.getElementById('changePassword');
                if (changePassword.checked) {
                    const password = document.getElementById('password').value;
                    const confirmation = document.getElementById('password_confirmation').value;
                    const confirmInput = document.getElementById('password_confirmation');
                    
                    if (password !== confirmation) {
                        event.preventDefault();
                        event.stopPropagation();
                        confirmInput.classList.add('is-invalid');
                        alert('Las contraseñas no coinciden. Por favor verifique.');
                        return false;
                    } else {
                        confirmInput.classList.remove('is-invalid');
                    }
                    
                    if (password.length < 6) {
                        event.preventDefault();
                        event.stopPropagation();
                        document.getElementById('password').classList.add('is-invalid');
                        alert('La contraseña debe tener al menos 6 caracteres.');
                        return false;
                    }
                }
                
                // Validación estándar de Bootstrap
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        })();
        
        // ==========================================
        // 5. VALIDACIÓN DE EMAIL EN TIEMPO REAL
        // ==========================================
        document.getElementById('email').addEventListener('blur', function() {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailPattern.test(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // ==========================================
        // 6. VALIDACIÓN DE NOMBRE (mínimo 3 caracteres)
        // ==========================================
        document.getElementById('name').addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 3) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // ==========================================
        // 7. ELIMINAR USUARIO
        // ==========================================
        <?php 
        $canDeleteUsers = in_array($this->user['role'] ?? '', ['super_admin', 'admin']);
        if ($canDeleteUsers && $user['id'] != $this->user['id']): 
        ?>
        document.getElementById('deleteUserBtn').addEventListener('click', function() {
            if (this.disabled) {
                return;
            }
            
            const userName = '<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>';
            const userId = '<?= $user['id'] ?>';
            
            if (confirm(`¿Está seguro de eliminar al usuario "${userName}"?\n\nEsta acción desactivará al usuario y no se puede deshacer.`)) {
                // Crear formulario para enviar DELETE
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= BASE_URL ?>admin/users/' + userId + '/delete';
                
                // Agregar token CSRF
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = 'csrf_token';
                csrfToken.value = '<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES) ?>';
                form.appendChild(csrfToken);
                
                // Agregar al DOM y enviar
                document.body.appendChild(form);
                form.submit();
            }
        });
        <?php endif; ?>
        
        // ==========================================
        // 8. CONFIRMACIÓN AL SALIR SIN GUARDAR
        // ==========================================
        let formChanged = false;
        const formInputs = document.querySelectorAll('#editUserForm input:not([type="hidden"]), #editUserForm select, #editUserForm textarea');
        
        formInputs.forEach(input => {
            input.addEventListener('change', function() {
                formChanged = true;
            });
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
        
        document.getElementById('editUserForm').addEventListener('submit', function() {
            formChanged = false;
        });
        
        // ==========================================
        // 9. AUTO-FOCUS EN PRIMER CAMPO
        // ==========================================
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').focus();
        });
    </script>
    
</body>
</html>
