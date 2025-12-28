<?php
/**
 * PASO 2: Configuración de Base de Datos
 * install/steps/database.php
 * 
 * VERSIÓN FINAL CORREGIDA:
 * - FormData capturado ANTES de deshabilitar campos
 * - Guarda datos en sesión automáticamente (process.php)
 * - Validaciones completas
 * - Debug mejorado
 */
?>

<div class="step-content">
    <h3><i class="fas fa-database text-primary"></i> Configuración de Base de Datos</h3>
    <p class="text-muted">
        Configure la conexión a su base de datos MySQL. El sistema creará automáticamente la base de datos si no existe.
    </p>
    
    <div class="alert alert-info">
        <h6><i class="fas fa-info-circle"></i> Información Importante</h6>
        <ul class="mb-0">
            <li>La base de datos se creará automáticamente si no existe</li>
            <li>Se verificarán los permisos necesarios del usuario</li>
            <li>Todos los datos se almacenan con codificación UTF-8</li>
            <li>Su información de conexión se guarda de forma segura</li>
        </ul>
    </div>
    
    <form id="database-form" method="POST" action="process.php" novalidate>
        <input type="hidden" name="step" value="test_database">
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-server"></i> Servidor de Base de Datos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Host/Servidor <span class="text-danger">*</span></label>
                            <input type="text" name="db_host" class="form-control" value="localhost" required>
                            <div class="form-text">
                                <i class="fas fa-lightbulb text-warning"></i>
                                <strong>Hosting compartido:</strong> Generalmente 'localhost'<br>
                                <strong>Servidor remoto:</strong> IP o dominio del servidor MySQL
                            </div>
                            <div class="invalid-feedback">El host es requerido</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Puerto</label>
                            <input type="number" name="db_port" class="form-control" value="3306" min="1" max="65535">
                            <div class="form-text">Puerto estándar: 3306</div>
                            <div class="invalid-feedback">Puerto inválido</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-database"></i> Base de Datos</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nombre de la Base de Datos <span class="text-danger">*</span></label>
                    <input type="text" name="db_name" class="form-control" placeholder="encuestas_clima" required pattern="[a-zA-Z0-9_]+">
                    <div class="form-text">
                        <i class="fas fa-info-circle text-info"></i>
                        Solo letras, números y guiones bajos. Se creará automáticamente si no existe.
                    </div>
                    <div class="invalid-feedback">Nombre de BD inválido (solo letras, números y guiones bajos)</div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key"></i> Credenciales de Acceso</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Usuario de Base de Datos <span class="text-danger">*</span></label>
                            <input type="text" name="db_user" class="form-control" required autocomplete="username">
                            <div class="form-text">Usuario con permisos para crear bases de datos y tablas</div>
                            <div class="invalid-feedback">El usuario es requerido</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="db_pass" class="form-control" id="dbPassword" autocomplete="current-password">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('dbPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Puede estar vacía en algunos hostings locales</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="db-test-result" class="alert" style="display: none;"></div>
        
        <div id="db-test-progress" class="progress mb-3" style="display: none; height: 25px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%">
                <span class="progress-text">0%</span>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="?step=1" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Anterior
            </a>
            <button type="submit" class="btn btn-primary btn-lg" id="testButton">
                <i class="fas fa-database"></i> Probar Conexión y Continuar
            </button>
        </div>
    </form>
    
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-question-circle"></i> ¿Dónde Encontrar Esta Información?</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-cloud"></i> Hosting Compartido</h6>
                    <p class="small">La información de base de datos generalmente se encuentra en:</p>
                    <ul class="small">
                        <li><strong>cPanel:</strong> Sección "Bases de datos MySQL"</li>
                        <li><strong>Plesk:</strong> Panel "Bases de datos"</li>
                        <li><strong>Email de bienvenida</strong> del hosting</li>
                        <li><strong>Panel de control</strong> del proveedor</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-server"></i> Servidor Propio</h6>
                    <p class="small">Si administra su propio servidor:</p>
                    <ul class="small">
                        <li><strong>Host:</strong> localhost o IP del servidor</li>
                        <li><strong>Usuario:</strong> root (o usuario creado)</li>
                        <li><strong>Puerto:</strong> 3306 (por defecto)</li>
                        <li><strong>Crear usuario:</strong> <code>CREATE USER 'usuario'@'localhost'</code></li>
                    </ul>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-12">
                    <h6><i class="fas fa-shield-alt"></i> Permisos Necesarios</h6>
                    <p class="small">El usuario de base de datos debe tener los siguientes permisos:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary">CREATE</span>
                        <span class="badge bg-primary">DROP</span>
                        <span class="badge bg-primary">SELECT</span>
                        <span class="badge bg-primary">INSERT</span>
                        <span class="badge bg-primary">UPDATE</span>
                        <span class="badge bg-primary">DELETE</span>
                        <span class="badge bg-primary">ALTER</span>
                        <span class="badge bg-primary">INDEX</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('database-form');
    const result = document.getElementById('db-test-result');
    const progress = document.getElementById('db-test-progress');
    const progressBar = progress.querySelector('.progress-bar');
    const progressText = progressBar.querySelector('.progress-text');
    const testButton = document.getElementById('testButton');
    
    // Validación en tiempo real
    form.querySelectorAll('input[required]').forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        if (field.hasAttribute('required') && !value) {
            isValid = false;
        } else if (field.name === 'db_name' && value) {
            if (!/^[a-zA-Z0-9_]+$/.test(value)) {
                isValid = false;
            } else if (value.length < 3) {
                isValid = false;
            }
        } else if (field.name === 'db_port' && value) {
            const port = parseInt(value);
            if (port < 1 || port > 65535) {
                isValid = false;
            }
        }
        
        if (isValid && value) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else {
            field.classList.remove('is-valid', 'is-invalid');
        }
        
        return isValid;
    }
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar todos los campos requeridos
        let isFormValid = true;
        form.querySelectorAll('input[required]').forEach(input => {
            if (!validateField(input) || !input.value.trim()) {
                isFormValid = false;
            }
        });
        
        if (!isFormValid) {
            showResult('danger', '<i class="fas fa-exclamation-triangle"></i> Por favor corrija los errores en el formulario');
            return;
        }
        
        const originalText = testButton.innerHTML;
        
        // ============================================================
        // CRÍTICO: Capturar FormData ANTES de deshabilitar campos
        // ============================================================
        const formData = new FormData(form);
        
        // Verificar que step esté presente
        console.log('=== VERIFICACIÓN DE DATOS ===');
        console.log('Campo "step" presente:', formData.has('step'));
        console.log('Valor de "step":', formData.get('step'));
        console.log('Campos enviados:');
        for (let [key, value] of formData.entries()) {
            if (key === 'db_pass') {
                console.log(`  ${key}: ${'*'.repeat(value.length)}`);
            } else {
                console.log(`  ${key}: ${value}`);
            }
        }
        console.log('=============================');
        
        // Ahora sí, cambiar estado del botón y deshabilitar formulario
        testButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Probando conexión...';
        testButton.disabled = true;
        form.querySelectorAll('input:not([type="hidden"]), button').forEach(el => el.disabled = true);
        result.style.display = 'none';
        
        // Mostrar barra de progreso
        progress.style.display = 'block';
        let progressValue = 0;
        const progressInterval = setInterval(() => {
            progressValue += Math.random() * 15 + 5;
            if (progressValue > 85) progressValue = 85;
            progressBar.style.width = progressValue + '%';
            progressText.textContent = Math.round(progressValue) + '%';
        }, 300);
        
        // Enviar datos (FormData ya fue capturado antes de deshabilitar)
        fetch('process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressText.textContent = '100%';
            
            setTimeout(() => {
                progress.style.display = 'none';
                
                if (data.success) {
                    showResult('success', `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x text-success me-3"></i>
                            <div>
                                <strong>${data.message}</strong>
                                ${data.database_exists === false ? '<br><small class="text-muted">Base de datos creada automáticamente</small>' : ''}
                                <br><small class="text-muted">Versión MySQL: ${data.mysql_version || 'detectada'}</small>
                            </div>
                        </div>
                    `);
                    
                    // Redirigir al siguiente paso (datos en sesión)
                    setTimeout(() => {
                        window.location.href = '?step=3';
                    }, 2000);
                    
                } else {
                    showResult('danger', `
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Error de Conexión:</strong> ${data.message}
                    `);
                    
                    testButton.innerHTML = originalText;
                    testButton.disabled = false;
                    form.querySelectorAll('input, button').forEach(el => el.disabled = false);
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(progressInterval);
            progress.style.display = 'none';
            
            console.error('Error:', error);
            showResult('danger', `
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Error de Conexión:</strong> ${error.message}
                <br><small>Verifique sus datos y la conexión al servidor de base de datos</small>
            `);
            
            testButton.innerHTML = originalText;
            testButton.disabled = false;
            form.querySelectorAll('input, button').forEach(el => el.disabled = false);
        });
    });
    
    function showResult(type, message) {
        result.className = `alert alert-${type}`;
        result.innerHTML = message;
        result.style.display = 'block';
        result.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const dbNameInput = document.querySelector('input[name="db_name"]');
    
    if (dbNameInput) {
        dbNameInput.addEventListener('focus', function() {
            if (!this.value) {
                const path = window.location.pathname;
                const folder = path.split('/').filter(p => p).slice(-2)[0] || 'encuestas';
                this.placeholder = folder + '_clima';
            }
        });
    }
});
</script>

<style>
.form-control.is-valid {
    border-color: #28a745;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
}

.progress-text {
    font-weight: 600;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

.card-header h5, .card-header h6 {
    color: #495057;
    font-weight: 600;
}

.form-text {
    font-size: 0.875em;
    margin-top: 0.5rem;
}

.form-text i {
    margin-right: 0.25rem;
}

.alert {
    border-radius: 10px;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
</style>