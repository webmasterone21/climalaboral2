<?php
/**
 * Vista: Editar Usuario - VERSIÓN CORREGIDA
 * views/admin/users/edit.php
 * 
 * CORRECCIONES:
 * ✅ Botón Guardar visible
 * ✅ Toggle de contraseña funcional
 * ✅ Departamento como texto simple
 * ✅ Todo el diseño original preservado
 */

// Datos recibidos del controlador
$user = $data['user'] ?? [];
$roles = $data['roles'] ?? [];
$statuses = $data['statuses'] ?? [];
$departments = $data['departments'] ?? [];
$current_user = $data['current_user'] ?? $_SESSION['user'] ?? [];
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
            margin-bottom: 20px;
        }
        
        .card-header {
            border-bottom: 1px solid #e2e8f0;
            background: white !important;
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
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            margin: 0 auto 15px;
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
        
        .delete-user-btn:hover:not(:disabled) {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }
        
        .delete-user-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
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
                        <i class="fas fa-poll me-2"></i> Encuestas
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
                        <h2 class="fw-bold mb-1">
                            <i class="fas fa-user-edit me-2"></i>
                            Editar Usuario: <?= htmlspecialchars($user['name'] ?? '') ?>
                        </h2>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="<?= BASE_URL ?>admin/dashboard" class="text-decoration-none">Inicio</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="<?= BASE_URL ?>admin/users" class="text-decoration-none">Usuarios</a>
                                </li>
                                <li class="breadcrumb-item active">Editar</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Volver al listado
                        </a>
                    </div>
                </div>
                
                <!-- Mensajes Flash -->
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php
                    unset($_SESSION['flash_message']);
                    unset($_SESSION['flash_type']);
                    ?>
                <?php endif; ?>
                
                <div class="row">
                    
                    <!-- Columna Izquierda: Información del Usuario -->
                    <div class="col-md-4">
                        
                        <!-- Card de Información -->
                        <div class="info-card">
                            <div class="text-center mb-3">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['name'] ?? '') ?></h5>
                                <p class="mb-0 opacity-75"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                            </div>
                            
                            <hr style="border-color: rgba(255,255,255,0.3);">
                            
                            <div class="info-item">
                                <span><i class="fas fa-shield-alt me-2"></i> Rol:</span>
                                <strong><?= htmlspecialchars($roles[$user['role']] ?? $user['role']) ?></strong>
                            </div>
                            
                            <div class="info-item">
                                <span><i class="fas fa-circle me-2"></i> Estado:</span>
                                <span class="status-badge status-<?= $user['status'] ?>">
                                    <?= htmlspecialchars($statuses[$user['status']] ?? $user['status']) ?>
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <span><i class="fas fa-calendar me-2"></i> Registrado:</span>
                                <strong><?= date('d/m/Y', strtotime($user['created_at'])) ?></strong>
                            </div>
                            
                            <?php if (!empty($user['last_login'])): ?>
                            <div class="info-item">
                                <span><i class="fas fa-clock me-2"></i> Último acceso:</span>
                                <strong><?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Card de Ayuda -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-info-circle me-2 text-info"></i> Información
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small mb-2"><strong>Roles disponibles:</strong></p>
                                <ul class="small mb-3">
                                    <li><strong>Admin:</strong> Acceso completo al sistema</li>
                                    <li><strong>Gerente:</strong> Gestión de encuestas y reportes</li>
                                    <li><strong>Usuario:</strong> Participación en encuestas</li>
                                </ul>
                                
                                <p class="small mb-2"><strong>Notas importantes:</strong></p>
                                <ul class="small mb-0">
                                    <li>El email debe ser único en el sistema</li>
                                    <li>La contraseña es opcional al editar</li>
                                    <li>No puede eliminar su propia cuenta</li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Columna Derecha: Formulario de Edición -->
                    <div class="col-md-8">
                        
                        <form action="<?= BASE_URL ?>admin/users/<?= $user['id'] ?>/update" 
                              method="POST" 
                              id="editUserForm" 
                              class="needs-validation" 
                              novalidate>
                            
                            <!-- Token CSRF -->
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <!-- Información Personal -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold">
                                        <i class="fas fa-user me-2 text-primary"></i> Información Personal
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label required">Nombre Completo</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="name" 
                                                   id="name"
                                                   value="<?= htmlspecialchars($user['name']) ?>"
                                                   placeholder="Ej: Juan Carlos Pérez"
                                                   required
                                                   minlength="3">
                                            <div class="invalid-feedback">
                                                El nombre es requerido (mínimo 3 caracteres)
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label required">Email</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   name="email" 
                                                   id="email"
                                                   value="<?= htmlspecialchars($user['email']) ?>"
                                                   placeholder="usuario@empresa.com"
                                                   required>
                                            <div class="invalid-feedback">
                                                Ingrese un email válido
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" 
                                                   class="form-control" 
                                                   name="phone" 
                                                   id="phone"
                                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                                   placeholder="+504 1234-5678">
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <label class="form-label">Cargo/Posición</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="position" 
                                                   id="position"
                                                   value="<?= htmlspecialchars($user['position'] ?? '') ?>"
                                                   placeholder="Ej: Gerente de Recursos Humanos">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seguridad y Contraseña -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold">
                                        <i class="fas fa-lock me-2 text-warning"></i> Seguridad
                                    </h5>
                                </div>
                                <div class="card-body">
                                    
                                    <!-- Toggle para cambiar contraseña -->
                                    <div class="password-toggle-section">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="changePassword">
                                            <label class="form-check-label fw-bold" for="changePassword">
                                                <i class="fas fa-key me-2"></i>
                                                Cambiar contraseña del usuario
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Active esta opción solo si desea cambiar la contraseña actual
                                        </small>
                                    </div>
                                    
                                    <!-- Campos de contraseña (ocultos por defecto) -->
                                    <div class="password-fields" id="passwordFields">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Nueva Contraseña</label>
                                                <div class="position-relative">
                                                    <input type="password" 
                                                           class="form-control" 
                                                           name="password" 
                                                           id="password"
                                                           placeholder="Mínimo 6 caracteres"
                                                           minlength="6">
                                                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                                                </div>
                                                <div id="passwordStrength" class="password-strength"></div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Confirmar Contraseña</label>
                                                <div class="position-relative">
                                                    <input type="password" 
                                                           class="form-control" 
                                                           name="password_confirmation" 
                                                           id="password_confirmation"
                                                           placeholder="Repita la contraseña"
                                                           minlength="6">
                                                    <i class="fas fa-eye password-toggle" id="togglePasswordConfirm"></i>
                                                </div>
                                                <div class="invalid-feedback">
                                                    Las contraseñas no coinciden
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            
                            <!-- Rol y Permisos -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold">
                                        <i class="fas fa-shield-alt me-2 text-info"></i> Rol y Permisos
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label required">Rol</label>
                                            <select class="form-select" name="role" id="role" required>
                                                <option value="">Seleccione un rol</option>
                                                <?php foreach ($roles as $roleKey => $roleLabel): ?>
                                                    <option value="<?= $roleKey ?>" 
                                                            <?= ($user['role'] === $roleKey) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($roleLabel) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Debe seleccionar un rol
                                            </div>
                                            <div class="form-text">Define los permisos del usuario</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label required">Estado</label>
                                            <select class="form-select" name="status" id="status" required>
                                                <?php foreach ($statuses as $statusKey => $statusLabel): ?>
                                                    <option value="<?= $statusKey ?>" 
                                                            <?= ($user['status'] === $statusKey) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($statusLabel) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Debe seleccionar un estado
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organización -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold">
                                        <i class="fas fa-sitemap me-2 text-success"></i> Organización
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Departamento</label>
                                            <select class="form-select" name="department" id="department">
                                                <option value="">Sin departamento asignado</option>
                                                <?php if (is_array($departments) && !empty($departments)): ?>
                                                    <?php foreach ($departments as $dept): ?>
                                                        <?php 
                                                        $deptName = is_array($dept) ? ($dept['name'] ?? $dept['department'] ?? '') : $dept;
                                                        if (!empty($deptName)):
                                                        ?>
                                                        <option value="<?= htmlspecialchars($deptName) ?>" 
                                                                <?= ($user['department'] == $deptName) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($deptName) ?>
                                                        </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Opcional: Asigne el usuario a un departamento específico
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de Acción -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        
                                        <!-- Botón Eliminar (izquierda) -->
                                        <button type="button" 
                                                class="delete-user-btn" 
                                                id="deleteUserBtn"
                                                <?= ($user['id'] == ($current_user['id'] ?? 0)) ? 'disabled title="No puede eliminar su propia cuenta"' : '' ?>>
                                            <i class="fas fa-trash-alt me-2"></i>
                                            Eliminar Usuario
                                        </button>
                                        
                                        <!-- Botones de Acción (derecha) -->
                                        <div>
                                            <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary me-2">
                                                <i class="fas fa-times me-2"></i> Cancelar
                                            </a>
                                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                                <i class="fas fa-save me-2"></i> Guardar Cambios
                                            </button>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            
                        </form>
                        
                    </div>
                    
                </div>
                
            </div>
            
        </div>
    </div>
    
    <!-- Bootstrap JS -->
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
        // 5. ELIMINAR USUARIO
        // ==========================================
        document.getElementById('deleteUserBtn').addEventListener('click', function() {
            if (this.disabled) return;
            
            if (confirm('¿Está seguro que desea eliminar este usuario?\n\nEsta acción no se puede deshacer.')) {
                // Crear formulario para DELETE
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= BASE_URL ?>admin/users/<?= $user['id'] ?>/delete';
                
                // Token CSRF
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = 'csrf_token';
                csrfToken.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
                form.appendChild(csrfToken);
                
                // Método DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
        
        // ==========================================
        // 6. DESCRIPCIÓN DINÁMICA DE ROLES
        // ==========================================
        document.getElementById('role').addEventListener('change', function() {
            const roleDescriptions = {
                'admin': 'Acceso completo a todas las funciones del sistema, incluyendo configuración y gestión de usuarios.',
                'gerente': 'Puede crear y gestionar encuestas, ver reportes y administrar participantes.',
                'usuario': 'Puede participar en encuestas y ver sus propias respuestas.'
            };
            
            const description = roleDescriptions[this.value] || 'Seleccione un rol para ver su descripción.';
            
            // Actualizar descripción si existe el elemento
            const roleDesc = document.getElementById('roleDescription');
            if (roleDesc) {
                roleDesc.innerHTML = '<small class="text-muted"><i class="fas fa-info-circle me-1"></i>' + description + '</small>';
            }
        });
    </script>
</body>
</html>
