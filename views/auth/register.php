<?php
/**
 * Vista de registro
 * views/auth/register.php
 * 
 * Formulario de registro de nuevos usuarios con validación
 */

$errors = $errors ?? [];
$oldData = $oldData ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Sistema de Encuestas</title>
    
    <!-- CSS Framework -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        
        .register-container {
            width: 100%;
            max-width: 550px;
            padding: 2rem;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .register-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .register-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .required-asterisk {
            color: #dc3545;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control-custom:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }
        
        .form-control-custom.is-invalid {
            border-color: #dc3545;
        }
        
        .form-control-custom.is-valid {
            border-color: #28a745;
        }
        
        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
            z-index: 5;
        }
        
        .form-control-custom:focus + .form-icon {
            color: #667eea;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
            z-index: 5;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .password-strength.show {
            opacity: 1;
        }
        
        .strength-bar {
            height: 100%;
            border-radius: 3px;
            transition: all 0.3s ease;
            width: 0;
        }
        
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #17a2b8; width: 75%; }
        .strength-strong { background: #28a745; width: 100%; }
        
        .strength-text {
            font-size: 0.8rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }
        
        .strength-weak-text { color: #dc3545; }
        .strength-fair-text { color: #ffc107; }
        .strength-good-text { color: #17a2b8; }
        .strength-strong-text { color: #28a745; }
        
        .form-check-custom {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin: 1.5rem 0;
        }
        
        .form-check-input-custom {
            width: 18px;
            height: 18px;
            border: 2px solid #e9ecef;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        .form-check-input-custom:checked {
            background: #667eea;
            border-color: #667eea;
        }
        
        .form-check-label-custom {
            color: #6c757d;
            font-size: 0.9rem;
            cursor: pointer;
            line-height: 1.4;
        }
        
        .form-check-label-custom a {
            color: #667eea;
            text-decoration: none;
        }
        
        .form-check-label-custom a:hover {
            text-decoration: underline;
        }
        
        .btn-register {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .btn-register:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .btn-register:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
            z-index: 1;
        }
        
        .divider span {
            background: white;
            padding: 0 1rem;
            position: relative;
            z-index: 2;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert-custom {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }
        
        .alert-error {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-container {
                padding: 1rem;
            }
            
            .register-card {
                padding: 2rem 1.5rem;
            }
            
            .register-title {
                font-size: 1.5rem;
            }
        }
        
        /* Efectos de partículas de fondo */
        .bg-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            33% {
                transform: translateY(-30px) rotate(120deg);
            }
            66% {
                transform: translateY(-20px) rotate(240deg);
            }
        }
    </style>
</head>
<body>

    <!-- Partículas de fondo -->
    <div class="bg-particles" id="particles"></div>

    <div class="register-container">
        <div class="register-card">
            
            <!-- Header -->
            <div class="register-header">
                <div class="register-logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="register-title">Crear Cuenta</h1>
                <p class="register-subtitle">Únete a nuestro sistema de encuestas</p>
            </div>
            
            <!-- Mensajes de error -->
            <?php if (!empty($errors)): ?>
            <div class="alert-custom alert-error">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $field => $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Formulario de registro -->
            <form id="registerForm" method="POST" action="<?= BASE_URL ?>register" novalidate>
                
                <!-- Información personal -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i>
                            Usuario <span class="required-asterisk">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control-custom <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Elige un nombre de usuario"
                                   value="<?= htmlspecialchars($oldData['username'] ?? '') ?>"
                                   required
                                   minlength="3"
                                   maxlength="50"
                                   pattern="[a-zA-Z0-9_]+"
                                   autocomplete="username">
                            <i class="fas fa-user form-icon"></i>
                        </div>
                        <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email <span class="required-asterisk">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control-custom <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   placeholder="tu@email.com"
                                   value="<?= htmlspecialchars($oldData['email'] ?? '') ?>"
                                   required
                                   autocomplete="email">
                            <i class="fas fa-envelope form-icon"></i>
                        </div>
                        <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Empresa -->
                <div class="form-group">
                    <label for="company" class="form-label">
                        <i class="fas fa-building"></i>
                        Empresa <span class="required-asterisk">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="text" 
                               id="company" 
                               name="company" 
                               class="form-control-custom <?= isset($errors['company']) ? 'is-invalid' : '' ?>" 
                               placeholder="Nombre de tu empresa"
                               value="<?= htmlspecialchars($oldData['company'] ?? '') ?>"
                               required
                               maxlength="100"
                               autocomplete="organization">
                        <i class="fas fa-building form-icon"></i>
                    </div>
                    <?php if (isset($errors['company'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['company']) ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Contraseñas -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Contraseña <span class="required-asterisk">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control-custom <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Crea una contraseña segura"
                                   required
                                   minlength="6"
                                   autocomplete="new-password">
                            <i class="fas fa-lock form-icon"></i>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye" id="toggleIconPassword"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                        <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">
                            <i class="fas fa-lock"></i>
                            Confirmar Contraseña <span class="required-asterisk">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   class="form-control-custom <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Repite tu contraseña"
                                   required
                                   autocomplete="new-password">
                            <i class="fas fa-lock form-icon"></i>
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirm')">
                                <i class="fas fa-eye" id="toggleIconPasswordConfirm"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password_confirm'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['password_confirm']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Términos y condiciones -->
                <div class="form-check-custom">
                    <input type="checkbox" 
                           id="terms" 
                           name="terms" 
                           class="form-check-input-custom" 
                           required>
                    <label for="terms" class="form-check-label-custom">
                        Acepto los <a href="#" onclick="showTerms()">términos y condiciones</a> 
                        y la <a href="#" onclick="showPrivacy()">política de privacidad</a> 
                        del sistema <span class="required-asterisk">*</span>
                    </label>
                </div>
                
                <!-- Botón de registro -->
                <button type="submit" class="btn-register" id="submitBtn">
                    <span id="submitText">Crear Cuenta</span>
                </button>
                
            </form>
            
            <!-- Divider -->
            <div class="divider">
                <span>¿Ya tienes cuenta?</span>
            </div>
            
            <!-- Enlace de login -->
            <div class="login-link">
                <a href="<?= BASE_URL ?>login">Iniciar sesión aquí</a>
            </div>
            
        </div>
        
        <!-- Footer info -->
        <div class="text-center mt-4">
            <small class="text-white-50">
                Al registrarte aceptas nuestras políticas de uso y privacidad
            </small>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById('toggleIcon' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1).replace('_', ''));
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 8) strength += 1;
            else feedback.push('Al menos 8 caracteres');
            
            if (password.match(/[a-z]/)) strength += 1;
            else feedback.push('Incluir minúsculas');
            
            if (password.match(/[A-Z]/)) strength += 1;
            else feedback.push('Incluir mayúsculas');
            
            if (password.match(/[0-9]/)) strength += 1;
            else feedback.push('Incluir números');
            
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            else feedback.push('Incluir símbolos');
            
            return { strength, feedback };
        }
        
        function updatePasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthContainer = document.getElementById('passwordStrength');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if (password.length === 0) {
                strengthContainer.classList.remove('show');
                return;
            }
            
            strengthContainer.classList.add('show');
            
            const { strength, feedback } = checkPasswordStrength(password);
            
            // Reset classes
            strengthBar.className = 'strength-bar';
            strengthText.className = 'strength-text';
            
            let strengthClass, strengthLabel;
            
            switch (strength) {
                case 0:
                case 1:
                    strengthClass = 'strength-weak';
                    strengthLabel = 'Muy débil';
                    break;
                case 2:
                    strengthClass = 'strength-fair';
                    strengthLabel = 'Débil';
                    break;
                case 3:
                case 4:
                    strengthClass = 'strength-good';
                    strengthLabel = 'Buena';
                    break;
                case 5:
                    strengthClass = 'strength-strong';
                    strengthLabel = 'Muy fuerte';
                    break;
            }
            
            strengthBar.classList.add(strengthClass);
            strengthText.classList.add(strengthClass + '-text');
            strengthText.textContent = strengthLabel;
        }
        
        // Real-time validation
        function validateField(field) {
            const value = field.value.trim();
            const fieldName = field.name;
            
            field.classList.remove('is-valid', 'is-invalid');
            
            let isValid = true;
            
            switch (fieldName) {
                case 'username':
                    isValid = value.length >= 3 && /^[a-zA-Z0-9_]+$/.test(value);
                    break;
                case 'email':
                    isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                    break;
                case 'company':
                    isValid = value.length > 0;
                    break;
                case 'password':
                    isValid = value.length >= 6;
                    updatePasswordStrength();
                    break;
                case 'password_confirm':
                    const password = document.getElementById('password').value;
                    isValid = value === password && value.length >= 6;
                    break;
            }
            
            if (value.length > 0) {
                field.classList.add(isValid ? 'is-valid' : 'is-invalid');
            }
            
            return isValid;
        }
        
        // Form submission with loading state
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Validate all fields
            const fields = this.querySelectorAll('.form-control-custom');
            let allValid = true;
            
            fields.forEach(field => {
                if (!validateField(field)) {
                    allValid = false;
                }
            });
            
            // Check terms acceptance
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                allValid = false;
                alert('Debes aceptar los términos y condiciones para continuar.');
                e.preventDefault();
                return;
            }
            
            if (!allValid) {
                e.preventDefault();
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            
            submitBtn.disabled = true;
            submitText.innerHTML = '<span class="loading-spinner"></span>Creando cuenta...';
            
            // Re-enable after 15 seconds (fallback)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitText.innerHTML = 'Crear Cuenta';
            }, 15000);
        });
        
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 40;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Random size
                const size = Math.random() * 4 + 2;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                
                // Random position
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                
                // Random animation delay
                particle.style.animationDelay = Math.random() * 8 + 's';
                particle.style.animationDuration = (Math.random() * 4 + 6) + 's';
                
                particlesContainer.appendChild(particle);
            }
        }
        
        // Show terms modal (placeholder)
        function showTerms() {
            alert('Términos y Condiciones:\n\n1. Uso responsable del sistema\n2. Respeto a la privacidad de los datos\n3. No compartir credenciales de acceso\n4. Reportar cualquier problema de seguridad\n\n(En un entorno de producción, esto sería un modal completo)');
        }
        
        // Show privacy modal (placeholder)
        function showPrivacy() {
            alert('Política de Privacidad:\n\n- Protegemos tus datos personales\n- No compartimos información con terceros\n- Usamos encriptación para proteger tu información\n- Cumplimos con regulaciones de protección de datos\n\n(En un entorno de producción, esto sería un modal completo)');
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Add real-time validation
            const inputs = document.querySelectorAll('.form-control-custom');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    validateField(this);
                });
                
                input.addEventListener('blur', function() {
                    validateField(this);
                });
            });
            
            // Focus first input
            document.getElementById('username').focus();
            
            // Check username availability (placeholder)
            document.getElementById('username').addEventListener('blur', function() {
                const username = this.value.trim();
                if (username.length >= 3) {
                    // En un entorno real, aquí se haría una llamada AJAX
                    // checkUsernameAvailability(username);
                }
            });
            
            // Check email availability (placeholder)
            document.getElementById('email').addEventListener('blur', function() {
                const email = this.value.trim();
                if (email.includes('@')) {
                    // En un entorno real, aquí se haría una llamada AJAX
                    // checkEmailAvailability(email);
                }
            });
        });
        
        // Handle back button
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                document.getElementById('registerForm').reset();
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('submitText').innerHTML = 'Crear Cuenta';
                
                // Clear validation states
                document.querySelectorAll('.form-control-custom').forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                });
                
                document.getElementById('passwordStrength').classList.remove('show');
            }
        });
    </script>

</body>
</html>