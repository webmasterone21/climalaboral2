<?php
/**
 * Vista: Página Principal Pública - Lista de Encuestas Disponibles
 * views/survey/index.php
 * 
 * Sistema de Encuestas de Clima Laboral
 * Muestra todas las encuestas activas disponibles para participar
 */

// Variables recibidas del controlador:
// $title - Título de la página
// $surveys - Array de encuestas activas
// $error - Mensaje de error si existe
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Encuestas Disponibles') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem 2rem;
            margin: 2rem 0;
            text-align: center;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .survey-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }
        
        .survey-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .survey-title {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .survey-description {
            color: #7f8c8d;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .survey-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .survey-info {
            font-size: 0.9rem;
            color: #95a5a6;
        }
        
        .survey-info i {
            margin-right: 0.5rem;
            color: #3498db;
        }
        
        .btn-participate {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-participate:hover {
            background: linear-gradient(135deg, #2980b9, #1f3a93);
            transform: scale(1.05);
            color: white;
            text-decoration: none;
        }
        
        .no-surveys {
            text-align: center;
            padding: 4rem 2rem;
            color: white;
        }
        
        .no-surveys i {
            font-size: 4rem;
            margin-bottom: 2rem;
            opacity: 0.7;
        }
        
        .error-alert {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .company-info {
            background: rgba(52, 152, 219, 0.1);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }
        
        .company-name {
            font-weight: 600;
            color: #2980b9;
            font-size: 0.9rem;
        }
        
        .survey-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .footer-info {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            padding: 2rem 0;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1rem;
                margin: 1rem 0;
            }
            
            .survey-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .survey-meta {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-participate {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="display-4 mb-3">
                <i class="fas fa-clipboard-list me-3"></i>
                Encuestas de Clima Laboral
            </h1>
            <p class="lead mb-0">
                Participa en nuestras encuestas y ayúdanos a mejorar el ambiente de trabajo
            </p>
        </div>
        
        <!-- Mensaje de Error -->
        <?php if (isset($error) && !empty($error)): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <!-- Lista de Encuestas -->
        <?php if (!empty($surveys) && is_array($surveys)): ?>
            <div class="row">
                <?php foreach ($surveys as $survey): ?>
                    <div class="col-lg-6 col-md-12">
                        <div class="survey-card">
                            <!-- Información de la Empresa -->
                            <?php if (!empty($survey['company_name'])): ?>
                                <div class="company-info">
                                    <div class="company-name">
                                        <i class="fas fa-building me-2"></i>
                                        <?= htmlspecialchars($survey['company_name']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Título y Estado -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="survey-title mb-0">
                                    <?= htmlspecialchars($survey['title']) ?>
                                </h3>
                                <span class="survey-status status-active">
                                    <i class="fas fa-circle me-1"></i>
                                    Activa
                                </span>
                            </div>
                            
                            <!-- Descripción -->
                            <?php if (!empty($survey['description'])): ?>
                                <p class="survey-description">
                                    <?= htmlspecialchars(substr($survey['description'], 0, 150)) ?>
                                    <?= strlen($survey['description']) > 150 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Meta información -->
                            <div class="survey-meta">
                                <div class="survey-info">
                                    <?php if (!empty($survey['estimated_time'])): ?>
                                        <div class="mb-1">
                                            <i class="fas fa-clock"></i>
                                            Tiempo estimado: <?= htmlspecialchars($survey['estimated_time']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($survey['total_questions'])): ?>
                                        <div class="mb-1">
                                            <i class="fas fa-list"></i>
                                            <?= (int)$survey['total_questions'] ?> preguntas
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($survey['end_date'])): ?>
                                        <div class="mb-1">
                                            <i class="fas fa-calendar-alt"></i>
                                            Disponible hasta: <?= date('d/m/Y', strtotime($survey['end_date'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Botón de Participar -->
                            <div class="text-end">
                                <a href="<?= BASE_URL ?>survey/<?= $survey['id'] ?>" 
                                   class="btn-participate">
                                    <i class="fas fa-play me-2"></i>
                                    Participar Ahora
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- No hay encuestas disponibles -->
            <div class="no-surveys">
                <i class="fas fa-inbox"></i>
                <h3>No hay encuestas disponibles</h3>
                <p class="mb-0">
                    Actualmente no hay encuestas activas. Vuelve pronto para participar en nuevas encuestas.
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer-info">
            <p class="mb-0">
                <i class="fas fa-shield-alt me-2"></i>
                Todas las respuestas son confidenciales y se utilizan únicamente para mejorar el clima laboral
            </p>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts adicionales si están definidos -->
    <?php if (isset($scripts) && is_array($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= BASE_URL ?>assets/js/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>