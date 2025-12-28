<?php
/**
 * Vista: Crear Usuario
 * views/admin/users/create.php
 * 
 * Formulario completo para crear nuevos usuarios del sistema
 * 
 * @package EncuestasHERCO\Views
 * @version 2.0.0
 */

// Datos recibidos del controlador
$roles = $data['roles'] ?? [];
$statuses = $data['statuses'] ?? [];
$departments = $data['departments'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Sistema HERCO</title>
    
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
            transition: all 0.3s;
        }
        
        .password-strength.weak {
            background: linear-gradient(to right, #ef4444 33%, #e5e7eb 33%);
        }
        
        .password-strength.medium {
            background: linear-gradient(to right, #f59e0b 66%, #e5e7eb 66%);
        }
        
        .password-strength.strong {
            background: #10b981;
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
                        <h2 class="fw-bold">Crear Nuevo Usuario</h2>
                        <p class="text-muted mb-0">Complete el formulario para agregar un nuevo usuario al sistema</p>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Volver
                        </a>
                    </div>
                </div>
                
                <!-- Alertas -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <!-- Formulario -->
                <div class="row">
                    <div class="col-md-8">
                        <!-- Línea 188 - CORREGIDA -->
                    <form method="POST" action="<?= BASE_URL ?>admin/users/store" id="createUserForm">
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            
                            <!-- Información Personal -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
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
                                                   placeholder="Ej: Juan Carlos Pérez"
                                                   required>
                                            <div class="form-text">Ingrese el nombre completo del usuario</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label required">Email</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   name="email" 
                                                   id="email"
                                                   placeholder="usuario@empresa.com"
                                                   required>
                                            <div class="form-text">Email único para acceso al sistema</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" 
                                                   class="form-control" 
                                                   name="phone" 
                                                   id="phone"
                                                   placeholder="+504 1234-5678">
                                        </div>
                                        
                                        <div class="col-md-12">
                                            <label class="form-label">Cargo/Posición</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="position" 
                                                   id="position"
                                                   placeholder="Ej: Gerente de Recursos Humanos">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seguridad -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 fw-bold">
                                        <i class="fas fa-lock me-2 text-warning"></i> Seguridad
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label required">Contraseña</label>
                                            <div class="position-relative">
                                                <input type="password" 
                                                       class="form-control" 
                                                       name="password" 
                                                       id="password"
                                                       placeholder="••••••••"
                                                       minlength="6"
                                                       required>
                                                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                                            </div>
                                            <div class="password-strength" id="passwordStrength"></div>
                                            <div class="form-text">Mínimo 6 caracteres</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label required">Confirmar Contraseña</label>
                                            <div class="position-relative">
                                                <input type="password" 
                                                       class="form-control" 
                                                       name="password_confirmation" 
                                                       id="password_confirmation"
                                                       placeholder="••••••••"
                                                       minlength="6"
                                                       required>
                                                <i class="fas fa-eye password-toggle" id="togglePasswordConfirm"></i>
                                            </div>
                                            <div class="form-text">Repita la contraseña</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Rol y Permisos -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
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
                                                    <option value="<?= $roleKey ?>"><?= htmlspecialchars($roleLabel) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Define los permisos del usuario</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label required">Estado</label>
                                            <select class="form-select" name="status" id="status" required>
                                                <?php foreach ($statuses as $statusKey => $statusLabel): ?>
                                                    <option value="<?= $statusKey ?>" <?= $statusKey === 'active' ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($statusLabel) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Descripción de Roles -->
                                    <div class="mt-3 p-3 bg-light rounded" id="roleDescription">
                                        <small class="text-muted">
                                            <strong>Seleccione un rol para ver su descripción</strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organización -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 fw-bold">
                                        <i class="fas fa-sitemap me-2 text-success"></i> Organización
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Departamento</label>
                                            <?php if (!empty($departments)): ?>
                                                <select class="form-select" name="department" id="department">
                                                    <option value="">Sin departamento</option>
                                                    <?php foreach ($departments as $dept): ?>
                                                        <option value="<?= htmlspecialchars($dept) ?>">
                                                            <?= htmlspecialchars($dept) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                    <option value="__new__">➕ Agregar nuevo departamento</option>
                                                </select>
                                                <input type="text" 
                                                       class="form-control mt-2 d-none" 
                                                       id="newDepartment" 
                                                       placeholder="Nombre del nuevo departamento">
                                            <?php else: ?>
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="department" 
                                                       placeholder="Ej: Recursos Humanos">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de Acción -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary">
                                            <i class="fas fa-times me-2"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Crear Usuario
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Sidebar de Ayuda -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i> Información de Ayuda
                                </h6>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-bold">Roles Disponibles</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <span class="badge bg-danger">Administrador</span>
                                        <small class="d-block text-muted">Acceso completo al sistema</small>
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-warning">Gerente</span>
                                        <small class="d-block text-muted">Gestión de encuestas y reportes</small>
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-primary">RRHH</span>
                                        <small class="d-block text-muted">Gestión de personal</small>
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-secondary">Usuario</span>
                                        <small class="d-block text-muted">Acceso limitado</small>
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-light text-dark">Solo Lectura</span>
                                        <small class="d-block text-muted">Ver reportes únicamente</small>
                                    </li>
                                </ul>
                                
                                <hr>
                                
                                <h6 class="fw-bold">Requisitos de Contraseña</h6>
                                <ul class="small text-muted">
                                    <li>Mínimo 6 caracteres</li>
                                    <li>Se recomienda usar letras y números</li>
                                    <li>Evitar contraseñas comunes</li>
                                </ul>
                                
                                <hr>
                                
                                <h6 class="fw-bold">⚠️ Importante</h6>
                                <ul class="small text-muted">
                                    <li>El email debe ser único en el sistema</li>
                                    <li>El usuario recibirá sus credenciales</li>
                                    <li>Puede cambiar su contraseña después</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle text-muted" style="font-size: 3rem;"></i>
                                <h6 class="mt-3">¿Necesitas ayuda?</h6>
                                <p class="text-muted small">Consulta la documentación del sistema</p>
                                <a href="<?= BASE_URL ?>admin/help" class="btn btn-sm btn-outline-primary">
                                    Ver Documentación
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        
        togglePasswordConfirm.addEventListener('click', function() {
            const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Password strength meter
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength <= 2) {
                strengthBar.classList.add('weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('medium');
            } else {
                strengthBar.classList.add('strong');
            }
        });
        
        // Validate password match
        const form = document.getElementById('createUserForm');
        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const passwordConfirm = passwordConfirmInput.value;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                passwordConfirmInput.focus();
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                passwordInput.focus();
                return false;
            }
        });
        
        // Role descriptions
        const roleDescriptions = {
            'admin': '<strong>Administrador:</strong> Acceso completo a todas las funcionalidades del sistema. Puede gestionar usuarios, empresas, encuestas y configuraciones.',
            'manager': '<strong>Gerente:</strong> Puede crear y gestionar encuestas, ver reportes y gestionar participantes. No puede modificar configuraciones del sistema.',
            'hr': '<strong>Recursos Humanos:</strong> Acceso a gestión de personal, participantes y visualización de reportes departamentales.',
            'user': '<strong>Usuario:</strong> Acceso básico para participar en encuestas y ver reportes asignados.',
            'viewer': '<strong>Solo Lectura:</strong> Puede visualizar reportes y dashboards pero no puede realizar cambios.'
        };
        
        const roleSelect = document.getElementById('role');
        const roleDescriptionDiv = document.getElementById('roleDescription');
        
        roleSelect.addEventListener('change', function() {
            const selectedRole = this.value;
            if (roleDescriptions[selectedRole]) {
                roleDescriptionDiv.innerHTML = '<small class="text-muted">' + roleDescriptions[selectedRole] + '</small>';
            } else {
                roleDescriptionDiv.innerHTML = '<small class="text-muted"><strong>Seleccione un rol para ver su descripción</strong></small>';
            }
        });
        
        // Department select handler
        const departmentSelect = document.getElementById('department');
        const newDepartmentInput = document.getElementById('newDepartment');
        
        if (departmentSelect) {
            departmentSelect.addEventListener('change', function() {
                if (this.value === '__new__') {
                    newDepartmentInput.classList.remove('d-none');
                    newDepartmentInput.required = true;
                    newDepartmentInput.focus();
                } else {
                    newDepartmentInput.classList.add('d-none');
                    newDepartmentInput.required = false;
                }
            });
            
            // Submit form with new department value
            form.addEventListener('submit', function(e) {
                if (departmentSelect.value === '__new__' && newDepartmentInput.value) {
                    // Create a hidden input with the new department name
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'department';
                    hiddenInput.value = newDepartmentInput.value;
                    form.appendChild(hiddenInput);
                    
                    // Remove the name attribute from the select to avoid sending '__new__'
                    departmentSelect.removeAttribute('name');
                }
            });
        }
        
        // Auto-hide alerts after 5 seconds
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