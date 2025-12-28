<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <!-- Logo/Header -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-building fa-4x text-primary"></i>
                        </div>
                        <h3 class="fw-bold">Sistema HERCO</h3>
                        <p class="text-muted">Encuestas de Clima Laboral v2.0</p>
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
                    
                    <!-- Login Form -->
                    <form method="POST" action="/login" id="loginForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Correo Electrónico
                            </label>
                            <input 
                                type="email" 
                                class="form-control form-control-lg" 
                                id="email" 
                                name="email" 
                                placeholder="usuario@ejemplo.com"
                                required 
                                autofocus
                                autocomplete="email"
                            >
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg" 
                                    id="password" 
                                    name="password" 
                                    placeholder="••••••••"
                                    required
                                    autocomplete="current-password"
                                >
                                <button 
                                    class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="togglePassword"
                                    title="Mostrar/Ocultar contraseña"
                                >
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Remember Me -->
                        <div class="mb-3 form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="remember" 
                                name="remember"
                                value="1"
                            >
                            <label class="form-check-label" for="remember">
                                Recordar mi sesión
                            </label>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </button>
                        </div>
                        
                        <!-- Forgot Password Link -->
                        <div class="text-center">
                            <a href="/forgot-password" class="text-decoration-none">
                                <small>¿Olvidó su contraseña?</small>
                            </a>
                        </div>
                    </form>
                    
                    <!-- Divider -->
                    <hr class="my-4">
                    
                    <!-- System Info -->
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Conexión segura SSL
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Footer Info -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    Sistema de Encuestas de Clima Laboral HERCO v2.0<br>
                    © <?= date('Y') ?> - Todos los derechos reservados
                </small>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for password toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            // Toggle password visibility
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'password') {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        });
    }
    
    // Form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Por favor, complete todos los campos');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor, ingrese un email válido');
                return false;
            }
        });
    }
});
</script>
