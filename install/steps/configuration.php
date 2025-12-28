<?php
/**
 * PASO 3: Configuración del Sistema y Usuario Administrador
 * install/steps/configuration.php
 * 
 * VERSIÓN FINAL - ARQUITECTURA CORREGIDA:
 * - FormData capturado ANTES de deshabilitar campos
 * - Lee datos de sesión del paso anterior
 * - Todas las características avanzadas mantenidas
 * - Debug completo
 */

// CRÍTICO: Obtener datos de BD del paso anterior
session_start();
$dbHost = $_SESSION['db_host'] ?? $_GET['db_host'] ?? 'localhost';
$dbPort = $_SESSION['db_port'] ?? $_GET['db_port'] ?? '3306';
$dbName = $_SESSION['db_name'] ?? $_GET['db_name'] ?? '';
$dbUser = $_SESSION['db_user'] ?? $_GET['db_user'] ?? '';
$dbPass = $_SESSION['db_pass'] ?? $_GET['db_pass'] ?? '';

// Debug de sesión (solo en desarrollo)
if (empty($dbName)) {
    error_log('ADVERTENCIA: No hay datos de BD en sesión');
}
?>

<div class="step-content">
    <h3><i class="fas fa-user-shield text-success"></i> Configuración Final del Sistema</h3>
    <p class="text-muted">
        Configure su cuenta de administrador y las opciones del sistema. Esta información será utilizada para completar la instalación de su Sistema HERCO.
    </p>
    
    <div class="alert alert-success border-left-success">
        <div class="d-flex">
            <i class="fas fa-check-circle text-success me-3 mt-1"></i>
            <div>
                <h6 class="mb-1">✅ Base de Datos Conectada Exitosamente</h6>
                <p class="mb-0 small">Sistema listo para completar la instalación con todas las configuraciones</p>
            </div>
        </div>
    </div>
    
    <form id="install-form" method="POST" action="process.php" novalidate>
        <!-- ========================================= -->
        <!-- CAMPOS OCULTOS CRÍTICOS - NO ELIMINAR -->
        <!-- ========================================= -->
        <input type="hidden" name="step" value="install_system">
        <input type="hidden" name="db_host" value="<?= htmlspecialchars($dbHost) ?>">
        <input type="hidden" name="db_port" value="<?= htmlspecialchars($dbPort) ?>">
        <input type="hidden" name="db_name" value="<?= htmlspecialchars($dbName) ?>">
        <input type="hidden" name="db_user" value="<?= htmlspecialchars($dbUser) ?>">
        <input type="hidden" name="db_pass" value="<?= htmlspecialchars($dbPass) ?>">
        
        <!-- Configuración del Sistema -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Configuración del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Sistema <span class="text-danger">*</span></label>
                            <input type="text" name="app_name" class="form-control" 
                                   value="Sistema de Encuestas Clima Laboral HERCO" required maxlength="100">
                            <div class="form-text">Nombre que aparecerá en todo el sistema y reportes</div>
                            <div class="invalid-feedback">El nombre del sistema es requerido</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Color Principal</label>
                            <input type="color" name="primary_color" class="form-control form-control-color" value="#007bff">
                            <div class="form-text">Color de la interfaz</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Idioma del Sistema</label>
                            <select name="system_language" class="form-select">
                                <option value="es" selected>Español</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Zona Horaria</label>
                            <select name="timezone" class="form-select">
                                <option value="America/Tegucigalpa" selected>Honduras (GMT-6)</option>
                                <option value="America/Guatemala">Guatemala (GMT-6)</option>
                                <option value="America/El_Salvador">El Salvador (GMT-6)</option>
                                <option value="America/Managua">Nicaragua (GMT-6)</option>
                                <option value="America/Costa_Rica">Costa Rica (GMT-6)</option>
                                <option value="America/Panama">Panamá (GMT-5)</option>
                                <option value="America/Mexico_City">México (GMT-6)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Usuario Administrador -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>
                    Usuario Administrador Principal
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Usuario con privilegios completos:</strong> Podrá gestionar todo el sistema, usuarios, encuestas y reportes.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="admin_name" class="form-control" required maxlength="255">
                            <div class="form-text">Nombre que aparecerá en el sistema</div>
                            <div class="invalid-feedback">El nombre es requerido</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="admin_email" class="form-control" required>
                            <div class="form-text">Usado para login y notificaciones</div>
                            <div class="invalid-feedback">Email válido requerido</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="admin_password" id="admin_password" class="form-control" required minlength="8">
                            <div class="form-text">
                                <i class="fas fa-shield-alt text-warning me-1"></i>
                                Mínimo 8 caracteres
                            </div>
                            <div class="invalid-feedback">Mínimo 8 caracteres</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <div class="form-text">Repita la contraseña</div>
                            <div class="invalid-feedback">Las contraseñas no coinciden</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información de la Empresa -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>
                    Información de la Empresa
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" required maxlength="255">
                            <div class="form-text">Nombre en reportes y encuestas</div>
                            <div class="invalid-feedback">El nombre es requerido</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tipo de Organización</label>
                            <select name="organization_type" class="form-select">
                                <option value="empresa">Empresa Privada</option>
                                <option value="publica">Institución Pública</option>
                                <option value="ong">ONG/Fundación</option>
                                <option value="educativa">Institución Educativa</option>
                                <option value="consultoria">Consultoría</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Industria/Sector</label>
                            <select name="industry" class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="tecnologia">Tecnología</option>
                                <option value="manufactura">Manufactura</option>
                                <option value="servicios">Servicios</option>
                                <option value="salud">Salud</option>
                                <option value="educacion">Educación</option>
                                <option value="financiero">Financiero</option>
                                <option value="retail">Retail/Comercio</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tamaño de la Empresa</label>
                            <select name="company_size" class="form-select">
                                <option value="small">Pequeña (1-50)</option>
                                <option value="medium" selected>Mediana (51-500)</option>
                                <option value="large">Grande (501-5000)</option>
                                <option value="enterprise">Corporativa (5000+)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Términos y Condiciones -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Términos y Condiciones
                </h6>
            </div>
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="acceptTerms" required>
                    <label class="form-check-label" for="acceptTerms">
                        <strong>Acepto instalar el Sistema HERCO en este servidor</strong> <span class="text-danger">*</span>
                    </label>
                    <div class="invalid-feedback">Debe aceptar los términos</div>
                </div>
                
                <div class="mt-3 p-3 bg-light rounded">
                    <h6>Al continuar, acepto que:</h6>
                    <ul class="small mb-0">
                        <li>Soy responsable del uso y mantenimiento</li>
                        <li>Los datos se almacenan en mi servidor</li>
                        <li>Implementaré medidas de seguridad necesarias</li>
                        <li>Realizaré respaldos regulares</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Área de resultados -->
        <div id="install-result" class="alert" style="display: none;"></div>
        
        <!-- Progreso de instalación -->
        <div id="install-progress" style="display: none;">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>
                        Instalando Sistema HERCO 2.0...
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Progreso:</span>
                        <span id="progress-text" class="fw-bold">0%</span>
                    </div>
                    <div class="progress mb-4" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-gradient" 
                             id="install-progress-bar" style="width: 0%;"></div>
                    </div>
                    
                    <div id="install-steps">
                        <ul class="list-unstyled mb-0">
                            <li id="step-1" class="mb-2 d-flex align-items-center">
                                <i class="fas fa-spinner fa-spin text-primary me-3"></i>
                                <span>Conectando a base de datos...</span>
                            </li>
                            <li id="step-2" class="mb-2 d-flex align-items-center" style="display:none;">
                                <i class="fas fa-spinner fa-spin text-primary me-3"></i>
                                <span>Creando estructura de tablas...</span>
                            </li>
                            <li id="step-3" class="mb-2 d-flex align-items-center" style="display:none;">
                                <i class="fas fa-spinner fa-spin text-primary me-3"></i>
                                <span>Insertando 18 categorías HERCO 2024...</span>
                            </li>
                            <li id="step-4" class="mb-2 d-flex align-items-center" style="display:none;">
                                <i class="fas fa-spinner fa-spin text-primary me-3"></i>
                                <span>Configurando tipos de preguntas...</span>
                            </li>
                            <li id="step-5" class="mb-2 d-flex align-items-center" style="display:none;">
                                <i class="fas fa-spinner fa-spin text-primary me-3"></i>
                                <span>Creando usuario administrador...</span>
                            </li>
                            <li id="step-6" class="mb-2 d-flex align-items-center" style="display:none;">
                                <i class="fas fa-spinner fa-spin text-primary me-3"></i>
                                <span>Aplicando configuraciones...</span>
                            </li>
                            <li id="step-7" class="mb-2 d-flex align-items-center" style="display:none;">
                                <i class="fas fa-spinner fa-spin text-primary me-3"></i>
                                <span>Finalizando instalación...</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="?step=2" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-1"></i>
                Anterior
            </a>
            
            <button type="submit" id="install-btn" class="btn btn-success btn-lg px-5">
                <i class="fas fa-rocket me-2"></i>
                Instalar Sistema HERCO Completo
            </button>
        </div>
    </form>
    
    <!-- Información -->
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card border-success h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Sistema Completo</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li><i class="fas fa-check text-success me-2"></i>12+ tablas de BD</li>
                        <li><i class="fas fa-check text-success me-2"></i>18 categorías HERCO 2024</li>
                        <li><i class="fas fa-check text-success me-2"></i>8 tipos de preguntas</li>
                        <li><i class="fas fa-check text-success me-2"></i>Sistema de seguridad</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-info h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-rocket me-2"></i>Listo Para Usar</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li><i class="fas fa-arrow-right text-info me-2"></i>Dashboard completo</li>
                        <li><i class="fas fa-arrow-right text-info me-2"></i>Constructor de encuestas</li>
                        <li><i class="fas fa-arrow-right text-info me-2"></i>Reportes HERCO</li>
                        <li><i class="fas fa-arrow-right text-info me-2"></i>Sistema de respaldos</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-warning h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-star me-2"></i>Características Premium</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li><i class="fas fa-star text-warning me-2"></i>Metodología HERCO 2024</li>
                        <li><i class="fas fa-star text-warning me-2"></i>Escalas Likert</li>
                        <li><i class="fas fa-star text-warning me-2"></i>Análisis comparativo</li>
                        <li><i class="fas fa-star text-warning me-2"></i>Exportación múltiple</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.card-header h5, .card-header h6 {
    font-weight: 600;
}

.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

#install-progress {
    margin-top: 30px;
}

.progress-bar {
    background: linear-gradient(45deg, #28a745, #20c997);
    font-weight: 600;
}

#install-steps li i.fa-check {
    color: #28a745 !important;
}

.bg-gradient {
    background: linear-gradient(45deg, #007bff, #6610f2) !important;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.was-validated .form-control:valid {
    border-color: #28a745;
}

.was-validated .form-control:invalid {
    border-color: #dc3545;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('install-form');
    const resultDiv = document.getElementById('install-result');
    const progressDiv = document.getElementById('install-progress');
    const progressBar = document.getElementById('install-progress-bar');
    const progressText = document.getElementById('progress-text');
    const installBtn = document.getElementById('install-btn');
    
    // Validación de contraseñas
    const passwordInput = document.getElementById('admin_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    function validatePasswords() {
        const password = passwordInput.value;
        const confirm = confirmPasswordInput.value;
        
        if (confirm && password !== confirm) {
            confirmPasswordInput.setCustomValidity('Las contraseñas no coinciden');
            confirmPasswordInput.classList.add('is-invalid');
            return false;
        } else {
            confirmPasswordInput.setCustomValidity('');
            confirmPasswordInput.classList.remove('is-invalid');
            if (confirm) confirmPasswordInput.classList.add('is-valid');
            return true;
        }
    }
    
    passwordInput.addEventListener('input', validatePasswords);
    confirmPasswordInput.addEventListener('input', validatePasswords);
    
    // Validación en tiempo real
    form.querySelectorAll('input[required], select[required]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    });
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar formulario
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            showResult('danger', 'Por favor corrija los errores en el formulario');
            
            const firstError = form.querySelector('.is-invalid, :invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return;
        }
        
        // Validar contraseñas
        if (!validatePasswords()) {
            showResult('danger', 'Las contraseñas no coinciden');
            confirmPasswordInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        
        // Verificar términos
        if (!document.getElementById('acceptTerms').checked) {
            showResult('danger', 'Debe aceptar los términos para continuar');
            document.getElementById('acceptTerms').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        
        // Iniciar instalación
        startInstallation();
    });
    
    function startInstallation() {
        // ============================================================
        // CRÍTICO: Capturar FormData ANTES de deshabilitar campos
        // ============================================================
        const formData = new FormData(form);
        
        // Verificar campos críticos
        console.log('=== VERIFICACIÓN INSTALACIÓN ===');
        console.log('Campo "step":', formData.get('step'));
        console.log('DB Host:', formData.get('db_host'));
        console.log('DB Name:', formData.get('db_name'));
        console.log('Admin Email:', formData.get('admin_email'));
        console.log('Company Name:', formData.get('company_name'));
        
        // Contar total de campos
        let fieldCount = 0;
        for (let [key] of formData.entries()) {
            fieldCount++;
        }
        console.log('Total campos enviados:', fieldCount);
        console.log('================================');
        
        // Ahora sí, deshabilitar formulario
        installBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Instalando...';
        installBtn.disabled = true;
        form.querySelectorAll('input:not([type="hidden"]), select, textarea, button').forEach(el => el.disabled = true);
        
        // Mostrar progreso
        resultDiv.style.display = 'none';
        progressDiv.style.display = 'block';
        progressDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Simular progreso
        simulateProgress();
        
        // Enviar petición (FormData ya capturado)
        fetch('process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('HTTP Status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta:', data);
            
            if (data.success) {
                completeInstallation(data);
            } else {
                showInstallationError(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showInstallationError({
                message: 'Error de conexión: ' + error.message
            });
        });
    }
    
    function simulateProgress() {
        let progress = 0;
        const steps = ['step-1', 'step-2', 'step-3', 'step-4', 'step-5', 'step-6', 'step-7'];
        let currentStep = 0;
        
        const interval = setInterval(() => {
            progress += Math.random() * 10 + 5;
            if (progress > 85) progress = 85;
            
            updateProgress(progress);
            
            if (currentStep < steps.length && progress > (currentStep + 1) * 12) {
                if (currentStep > 0) {
                    const prevStep = document.getElementById(steps[currentStep - 1]);
                    const icon = prevStep.querySelector('i');
                    icon.className = 'fas fa-check text-success me-3';
                }
                
                const currentStepEl = document.getElementById(steps[currentStep]);
                if (currentStepEl) {
                    currentStepEl.style.display = 'flex';
                }
                currentStep++;
            }
            
            if (progress >= 85) {
                clearInterval(interval);
            }
        }, 800);
    }
    
    function updateProgress(percentage) {
        progressBar.style.width = percentage + '%';
        progressText.textContent = Math.round(percentage) + '%';
    }
    
    function completeInstallation(data) {
        updateProgress(100);
        
        const lastStep = document.getElementById('step-7');
        if (lastStep) {
            const icon = lastStep.querySelector('i');
            icon.className = 'fas fa-check text-success me-3';
        }
        
        setTimeout(() => {
            showResult('success', `
                <div class="text-center py-4">
                    <div style="font-size: 4rem; color: #28a745; margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="text-success mb-3">¡Instalación Completada!</h3>
                    <p class="lead mb-4">Su Sistema HERCO 2.0 está listo</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Credenciales</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Email:</strong> <code>${data.admin_email || 'admin@sistema.com'}</code></p>
                                <p class="mb-0 small text-muted">Use su contraseña configurada</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Sistema</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Versión:</strong> HERCO 2.0</p>
                                <p class="mb-0"><strong>Instalado:</strong> ${data.installation_time || 'Ahora'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="../index.php" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-sign-in-alt me-1"></i>
                        Acceder al Sistema
                    </a>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Seguridad:</strong> Elimine la carpeta 'install' después de acceder
                    </div>
                </div>
            `);
            
            setTimeout(() => {
                window.location.href = '../index.php';
            }, 5000);
        }, 2000);
    }
    
    function showInstallationError(data) {
        progressDiv.style.display = 'none';
        
        showResult('danger', `
            <div class="text-center py-3">
                <div style="font-size: 3rem; color: #dc3545; margin-bottom: 15px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 class="text-danger mb-3">Error en la Instalación</h4>
                <p class="mb-3">${data.message || 'Error desconocido'}</p>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-redo me-1"></i>
                    Reintentar
                </button>
            </div>
        `);
        
        installBtn.innerHTML = '<i class="fas fa-rocket me-2"></i>Instalar Sistema HERCO Completo';
        installBtn.disabled = false;
        form.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = false);
    }
    
    function showResult(type, message) {
        resultDiv.className = `alert alert-${type}`;
        resultDiv.innerHTML = message;
        resultDiv.style.display = 'block';
        resultDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>