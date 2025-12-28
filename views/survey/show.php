<?php
/**
 * Vista de presentación de encuesta
 * views/survey/show.php
 * 
 * Muestra la información de la encuesta antes de participar
 * Permite verificar requisitos y comenzar la participación
 */

// Verificar que la encuesta esté disponible
$isAvailable = ($survey['status'] === 'active');
$now = date('Y-m-d');
$canStart = true;
$message = '';

if ($survey['start_date'] && $survey['start_date'] > $now) {
    $canStart = false;
    $message = 'Esta encuesta iniciará el ' . date('d/m/Y', strtotime($survey['start_date']));
}

if ($survey['end_date'] && $survey['end_date'] < $now) {
    $canStart = false;
    $message = 'Esta encuesta finalizó el ' . date('d/m/Y', strtotime($survey['end_date']));
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($survey['title']) ?> - Encuesta de Clima Laboral</title>
    
    <!-- CSS Framework -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados -->
    <link href="<?= BASE_URL ?>assets/css/survey.css" rel="stylesheet">
    
    <style>
        .survey-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 0;
        }
        
        .company-logo {
            max-height: 80px;
            width: auto;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .survey-info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: -60px;
            position: relative;
            z-index: 10;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: #667eea;
        }
        
        .start-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }
        
        .start-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .unavailable-message {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            color: #856404;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header de la encuesta -->
    <section class="survey-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-3">
                        <?= htmlspecialchars($survey['title']) ?>
                    </h1>
                    <?php if ($survey['description']): ?>
                        <p class="lead mb-0">
                            <?= nl2br(htmlspecialchars($survey['description'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($survey['logo'] && file_exists(UPLOAD_PATH . 'logos/' . $survey['logo'])): ?>
                        <img src="<?= BASE_URL ?>uploads/logos/<?= $survey['logo'] ?>" 
                             alt="Logo" class="company-logo">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                
                <!-- Información de la encuesta -->
                <div class="survey-info-card">
                    
                    <!-- Verificar disponibilidad -->
                    <?php if (!$canStart): ?>
                        <div class="unavailable-message mb-4">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h4>Encuesta no disponible</h4>
                            <p class="mb-0"><?= $message ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Introducción -->
                    <?php if ($survey['introduction']): ?>
                        <div class="mb-4">
                            <h3 class="h4 mb-3">
                                <i class="fas fa-info-circle text-primary"></i>
                                Introducción
                            </h3>
                            <div class="text-muted">
                                <?= nl2br(htmlspecialchars($survey['introduction'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Información general -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-questions"></i>
                                </div>
                                <div>
                                    <strong>Total de preguntas</strong><br>
                                    <span class="text-muted"><?= $total_questions ?> preguntas</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <strong>Tiempo estimado</strong><br>
                                    <span class="text-muted"><?= ceil($total_questions * 0.5) ?> - <?= ceil($total_questions * 1) ?> minutos</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <?php if ($survey['anonymous']): ?>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-user-secret"></i>
                                </div>
                                <div>
                                    <strong>Encuesta anónima</strong><br>
                                    <span class="text-muted">Tus respuestas son completamente anónimas</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($survey['allow_save_progress']): ?>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-save"></i>
                                </div>
                                <div>
                                    <strong>Progreso guardado</strong><br>
                                    <span class="text-muted">Puedes continuar más tarde</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Fechas -->
                    <?php if ($survey['start_date'] || $survey['end_date']): ?>
                    <div class="mb-4">
                        <h5><i class="fas fa-calendar text-primary"></i> Período de participación</h5>
                        <div class="row">
                            <?php if ($survey['start_date']): ?>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    <div>
                                        <strong>Fecha de inicio</strong><br>
                                        <span class="text-muted"><?= date('d/m/Y', strtotime($survey['start_date'])) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($survey['end_date']): ?>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="fas fa-stop"></i>
                                    </div>
                                    <div>
                                        <strong>Fecha de cierre</strong><br>
                                        <span class="text-muted"><?= date('d/m/Y', strtotime($survey['end_date'])) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Instrucciones -->
                    <?php if ($survey['instructions']): ?>
                        <div class="mb-4 p-3 bg-light rounded">
                            <h5><i class="fas fa-list-ul text-primary"></i> Instrucciones</h5>
                            <div class="text-muted">
                                <?= nl2br(htmlspecialchars($survey['instructions'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Categorías de preguntas -->
                    <?php if (!empty($questionsByCategory)): ?>
                        <div class="mb-4">
                            <h5><i class="fas fa-layer-group text-primary"></i> Áreas de evaluación</h5>
                            <div class="row">
                                <?php foreach ($questionsByCategory as $category): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <span><?= htmlspecialchars($category['category']) ?></span>
                                            <span class="badge bg-secondary ms-auto">
                                                <?= count($category['questions']) ?> pregunta(s)
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Botón para comenzar -->
                    <div class="text-center pt-4">
                        <?php if ($canStart && $isAvailable): ?>
                            <button type="button" class="btn btn-primary start-button" onclick="startSurvey()">
                                <i class="fas fa-play me-2"></i>
                                Comenzar Encuesta
                            </button>
                            <p class="mt-3 text-muted small">
                                Al hacer clic en "Comenzar Encuesta", aceptas participar voluntariamente en esta evaluación.
                            </p>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-lock me-2"></i>
                                Encuesta no disponible
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-4 bg-light">
        <div class="container text-center">
            <p class="text-muted mb-0">
                Sistema de Encuestas de Clima Laboral &copy; <?= date('Y') ?>
            </p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function startSurvey() {
            // Verificar si hay progreso guardado
            fetch('<?= BASE_URL ?>api/check-progress?survey_id=<?= $survey['id'] ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.hasProgress) {
                        if (confirm('Tienes un progreso guardado en esta encuesta. ¿Deseas continuar donde lo dejaste?')) {
                            window.location.href = '<?= BASE_URL ?>survey/<?= $survey['id'] ?>/participate?continue=true';
                        } else {
                            window.location.href = '<?= BASE_URL ?>survey/<?= $survey['id'] ?>/participate';
                        }
                    } else {
                        window.location.href = '<?= BASE_URL ?>survey/<?= $survey['id'] ?>/participate';
                    }
                })
                .catch(error => {
                    console.error('Error checking progress:', error);
                    window.location.href = '<?= BASE_URL ?>survey/<?= $survey['id'] ?>/participate';
                });
        }

        // Verificar disponibilidad cada 30 segundos si la encuesta no está disponible aún
        <?php if (!$canStart && $survey['start_date'] && $survey['start_date'] > $now): ?>
        setInterval(() => {
            const now = new Date();
            const startDate = new Date('<?= $survey['start_date'] ?>');
            
            if (now >= startDate) {
                location.reload();
            }
        }, 30000);
        <?php endif; ?>
    </script>

</body>
</html>