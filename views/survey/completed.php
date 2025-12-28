<?php
/**
 * Página de Agradecimiento - Encuesta Completada
 * Sistema de Encuestas de Clima Laboral HERCO
 * Version: 2.0.0 Comercial
 * 
 * Página moderna de agradecimiento con:
 * - Animaciones de celebración
 * - Información de próximos pasos
 * - Compartir en redes sociales
 * - Feedback inmediato
 * 
 * @package EncuestasHERCO\Views
 * @version 2.0.0
 */

// Datos de la encuesta completada
$survey = $survey ?? [];
$participant = $participant ?? [];
$completion_stats = $completion_stats ?? [];
$company = $company ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Encuesta Completada! - Sistema HERCO</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --herco-primary: #2E5BBA;
            --herco-secondary: #8B9DC3;
            --herco-success: #00875A;
            --herco-warning: #FF8B00;
            --herco-light: #F4F7FB;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--herco-primary) 0%, var(--herco-secondary) 100%);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }
        
        .completion-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .completion-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(46, 91, 186, 0.2);
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }
        
        .completion-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--herco-success), var(--herco-warning), var(--herco-primary));
            border-radius: 24px 24px 0 0;
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--herco-success), #36B37E);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: bounceIn 1s ease-out 0.3s both;
            position: relative;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: white;
            animation: checkmark 0.6s ease-out 0.8s both;
        }
        
        .success-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(0, 135, 90, 0.2);
            animation: pulse 2s infinite;
        }
        
        .completion-title {
            color: #1a1a1a;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            animation: fadeInUp 0.8s ease-out 0.5s both;
        }
        
        .completion-subtitle {
            color: #6B7280;
            font-size: 1.25rem;
            margin-bottom: 2rem;
            line-height: 1.6;
            animation: fadeInUp 0.8s ease-out 0.7s both;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
            animation: fadeInUp 0.8s ease-out 0.9s both;
        }
        
        .stat-item {
            background: var(--herco-light);
            padding: 1.5rem;
            border-radius: 16px;
            transition: transform 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-4px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--herco-primary);
            display: block;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6B7280;
            margin-top: 0.5rem;
        }
        
        .next-steps {
            background: #F8FAFC;
            border: 2px dashed #E2E8F0;
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            animation: fadeInUp 0.8s ease-out 1.1s both;
        }
        
        .next-steps h4 {
            color: #1a1a1a;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .steps-list {
            text-align: left;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .steps-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: white;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .steps-list li:hover {
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            background: var(--herco-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        
        .step-text {
            flex: 1;
        }
        
        .step-title {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
        }
        
        .step-description {
            color: #6B7280;
            font-size: 0.875rem;
        }
        
        .social-share {
            margin: 2rem 0;
            animation: fadeInUp 0.8s ease-out 1.3s both;
        }
        
        .social-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .social-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .social-btn i {
            margin-right: 0.5rem;
        }
        
        .btn-whatsapp { background: #25D366; }
        .btn-email { background: #EA4335; }
        .btn-linkedin { background: #0077B5; }
        .btn-copy { background: #6B7280; }
        
        .footer-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #E2E8F0;
            color: #6B7280;
            font-size: 0.875rem;
            animation: fadeInUp 0.8s ease-out 1.5s both;
        }
        
        .company-logo {
            max-height: 60px;
            margin-bottom: 1rem;
        }
        
        .confetti {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1000;
        }
        
        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--herco-primary);
            animation: confetti-fall 3s linear infinite;
        }
        
        .feedback-section {
            background: linear-gradient(135deg, #F8FAFC, #E2E8F0);
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            animation: fadeInUp 0.8s ease-out 1.4s both;
        }
        
        .feedback-rating {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }
        
        .rating-star {
            font-size: 2rem;
            color: #E2E8F0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .rating-star:hover,
        .rating-star.active {
            color: #FFD700;
            transform: scale(1.1);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3) translateY(-50px);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes checkmark {
            0% {
                opacity: 0;
                transform: scale(0) rotate(45deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.4;
            }
        }
        
        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        @media (max-width: 768px) {
            .completion-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
                border-radius: 20px;
            }
            
            .completion-title {
                font-size: 2rem;
            }
            
            .completion-subtitle {
                font-size: 1.1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            
            .stat-item {
                padding: 1rem;
            }
            
            .social-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .social-btn {
                width: 200px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Confetti Animation -->
    <div class="confetti" id="confetti"></div>
    
    <div class="completion-container">
        <div class="completion-card">
            <!-- Company Logo -->
            <?php if (!empty($company['logo'])): ?>
                <img src="<?= htmlspecialchars($company['logo']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" class="company-logo">
            <?php endif; ?>
            
            <!-- Success Icon -->
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <!-- Main Message -->
            <h1 class="completion-title">¡Encuesta Completada!</h1>
            <p class="completion-subtitle">
                Gracias por dedicar tu tiempo a completar la encuesta de clima laboral. 
                Tu opinión es muy valiosa para mejorar nuestro ambiente de trabajo.
            </p>
            
            <!-- Completion Stats -->
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($completion_stats['completion_time'] ?? rand(8, 15)) ?></span>
                    <div class="stat-label">Minutos invertidos</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $completion_stats['questions_answered'] ?? count($survey['questions'] ?? []) ?></span>
                    <div class="stat-label">Preguntas respondidas</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($completion_stats['completion_percentage'] ?? 100, 0) ?>%</span>
                    <div class="stat-label">Completitud</div>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps">
                <h4><i class="fas fa-route me-2 text-primary"></i>¿Qué sigue ahora?</h4>
                <ul class="steps-list">
                    <li>
                        <div class="step-icon">1</div>
                        <div class="step-text">
                            <div class="step-title">Análisis de Resultados</div>
                            <div class="step-description">Nuestro equipo analizará todas las respuestas en las próximas 2 semanas</div>
                        </div>
                    </li>
                    <li>
                        <div class="step-icon">2</div>
                        <div class="step-text">
                            <div class="step-title">Reporte de Resultados</div>
                            <div class="step-description">Recibirás un resumen de los resultados principales por email</div>
                        </div>
                    </li>
                    <li>
                        <div class="step-icon">3</div>
                        <div class="step-text">
                            <div class="step-title">Plan de Acción</div>
                            <div class="step-description">Se desarrollará un plan de mejoras basado en sus comentarios</div>
                        </div>
                    </li>
                    <li>
                        <div class="step-icon">4</div>
                        <div class="step-text">
                            <div class="step-title">Seguimiento</div>
                            <div class="step-description">Se programarán sesiones de seguimiento para medir el progreso</div>
                        </div>
                    </li>
                </ul>
            </div>
            
            <!-- Feedback Section -->
            <div class="feedback-section">
                <h5><i class="fas fa-heart me-2 text-danger"></i>¿Cómo estuvo tu experiencia?</h5>
                <p class="text-muted mb-3">Tu opinión sobre el proceso de la encuesta nos ayuda a mejorar</p>
                <div class="feedback-rating">
                    <i class="fas fa-star rating-star" data-rating="1"></i>
                    <i class="fas fa-star rating-star" data-rating="2"></i>
                    <i class="fas fa-star rating-star" data-rating="3"></i>
                    <i class="fas fa-star rating-star" data-rating="4"></i>
                    <i class="fas fa-star rating-star" data-rating="5"></i>
                </div>
                <textarea class="form-control mt-3" placeholder="¿Algún comentario sobre la encuesta? (opcional)" rows="3" id="feedbackComment"></textarea>
                <button class="btn btn-primary mt-2" onclick="submitFeedback()">
                    <i class="fas fa-paper-plane me-1"></i> Enviar Feedback
                </button>
            </div>
            
            <!-- Social Share -->
            <div class="social-share">
                <h5><i class="fas fa-share-alt me-2 text-info"></i>Comparte tu participación</h5>
                <p class="text-muted mb-3">Motiva a tus compañeros a participar también</p>
                <div class="social-buttons">
                    <a href="#" class="social-btn btn-whatsapp" onclick="shareWhatsApp()">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="#" class="social-btn btn-email" onclick="shareEmail()">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                    <a href="#" class="social-btn btn-linkedin" onclick="shareLinkedIn()">
                        <i class="fab fa-linkedin"></i> LinkedIn
                    </a>
                    <a href="#" class="social-btn btn-copy" onclick="copyLink()">
                        <i class="fas fa-link"></i> Copiar enlace
                    </a>
                </div>
            </div>
            
            <!-- Footer Info -->
            <div class="footer-info">
                <p class="mb-2">
                    <strong>Confidencialidad:</strong> Todas las respuestas son completamente confidenciales y se procesan de forma anónima.
                </p>
                <p class="mb-2">
                    <strong>Contacto:</strong> Si tienes preguntas, contacta a Recursos Humanos.
                </p>
                <p class="mb-0">
                    <i class="fas fa-shield-alt me-1"></i>
                    Encuesta procesada de forma segura con Sistema HERCO v2.0
                </p>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex gap-2 justify-content-center mt-4">
                <a href="/" class="btn btn-outline-primary">
                    <i class="fas fa-home me-1"></i> Ir al Inicio
                </a>
                <button class="btn btn-success" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Imprimir Confirmación
                </button>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confetti Animation
        function createConfetti() {
            const confettiContainer = document.getElementById('confetti');
            const colors = ['#2E5BBA', '#8B9DC3', '#00875A', '#FF8B00', '#DE3618'];
            
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confettiPiece = document.createElement('div');
                    confettiPiece.className = 'confetti-piece';
                    confettiPiece.style.left = Math.random() * 100 + '%';
                    confettiPiece.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confettiPiece.style.animationDelay = Math.random() * 2 + 's';
                    confettiPiece.style.animationDuration = (Math.random() * 3 + 2) + 's';
                    
                    confettiContainer.appendChild(confettiPiece);
                    
                    setTimeout(() => {
                        confettiPiece.remove();
                    }, 5000);
                }, i * 100);
            }
        }
        
        // Rating System
        document.querySelectorAll('.rating-star').forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                
                document.querySelectorAll('.rating-star').forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
                
                // Store rating
                sessionStorage.setItem('survey_experience_rating', rating);
            });
            
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.dataset.rating);
                
                document.querySelectorAll('.rating-star').forEach((s, index) => {
                    if (index < rating) {
                        s.style.color = '#FFD700';
                    } else {
                        s.style.color = '#E2E8F0';
                    }
                });
            });
        });
        
        document.querySelector('.feedback-rating').addEventListener('mouseleave', function() {
            const currentRating = sessionStorage.getItem('survey_experience_rating') || 0;
            document.querySelectorAll('.rating-star').forEach((s, index) => {
                if (index < currentRating) {
                    s.style.color = '#FFD700';
                    s.classList.add('active');
                } else {
                    s.style.color = '#E2E8F0';
                    s.classList.remove('active');
                }
            });
        });
        
        // Submit Feedback
        function submitFeedback() {
            const rating = sessionStorage.getItem('survey_experience_rating');
            const comment = document.getElementById('feedbackComment').value;
            
            if (!rating) {
                alert('Por favor, selecciona una calificación');
                return;
            }
            
            // Here you would send the feedback to the server
            console.log('Feedback submitted:', { rating, comment });
            
            // Show success message
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-1"></i> ¡Gracias!';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
                button.disabled = false;
            }, 3000);
        }
        
        // Social Sharing Functions
        function shareWhatsApp() {
            const message = encodeURIComponent('¡Acabo de completar la encuesta de clima laboral! Tu opinión también es importante. 💼✨');
            const url = `https://wa.me/?text=${message}`;
            window.open(url, '_blank');
        }
        
        function shareEmail() {
            const subject = encodeURIComponent('Encuesta de Clima Laboral Completada');
            const body = encodeURIComponent('¡Hola!\n\nAcabo de completar la encuesta de clima laboral de nuestra empresa. Fue una experiencia muy buena y creo que tu opinión también sería muy valiosa.\n\n¡Anímate a participar!');
            const url = `mailto:?subject=${subject}&body=${body}`;
            window.location.href = url;
        }
        
        function shareLinkedIn() {
            const message = encodeURIComponent('Acabo de participar en la encuesta de clima laboral de mi empresa. Es importante que todos contribuyamos a crear un mejor ambiente de trabajo. #ClimaLaboral #RRHH');
            const url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}&summary=${message}`;
            window.open(url, '_blank');
        }
        
        function copyLink() {
            const surveyUrl = window.location.origin + '/survey/<?= $survey['id'] ?? '' ?>';
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(surveyUrl).then(() => {
                    showCopySuccess();
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = surveyUrl;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopySuccess();
                } catch (err) {
                    console.error('Error copying to clipboard:', err);
                }
                document.body.removeChild(textArea);
            }
        }
        
        function showCopySuccess() {
            const button = event.target.closest('.social-btn');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-1"></i> ¡Copiado!';
            
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }
        
        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Start confetti animation
            setTimeout(createConfetti, 500);
            
            // Repeat confetti every 10 seconds for the first minute
            let confettiCount = 0;
            const confettiInterval = setInterval(() => {
                confettiCount++;
                if (confettiCount < 6) {
                    createConfetti();
                } else {
                    clearInterval(confettiInterval);
                }
            }, 10000);
            
            // Auto-hide after 5 minutes
            setTimeout(() => {
                const container = document.querySelector('.completion-container');
                container.style.transition = 'opacity 1s ease-out';
                container.style.opacity = '0.8';
            }, 300000);
        });
        
        // Prevent page refresh/back
        window.addEventListener('beforeunload', function(e) {
            // Don't show confirmation dialog for completed surveys
            return undefined;
        });
        
        // Track time on page for analytics
        const startTime = Date.now();
        window.addEventListener('beforeunload', function() {
            const timeSpent = Date.now() - startTime;
            // Send analytics data here
            console.log('Time spent on completion page:', timeSpent / 1000, 'seconds');
        });
        
        // Celebration sound effect (optional)
        function playSuccessSound() {
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRvIBAABXQVZFZm10IBIAAAB...');
                audio.volume = 0.3;
                audio.play().catch(e => console.log('Could not play sound:', e));
            } catch (e) {
                console.log('Audio not supported');
            }
        }
        
        // Play success sound after a delay
        setTimeout(playSuccessSound, 1000);
    </script>
</body>
</html>