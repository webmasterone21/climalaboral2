<?php
// ======================================
// views/survey/thank_you.php - Página de Agradecimiento
// ======================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por su participación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .thank-you-card {
            max-width: 500px;
            background: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 2rem;
        }
        .btn-home {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="thank-you-card">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    
                    <h1 class="h2 mb-4">¡Gracias por su participación!</h1>
                    
                    <p class="lead text-muted mb-4">
                        Su encuesta ha sido enviada exitosamente. Sus respuestas son muy valiosas para mejorar nuestro ambiente laboral.
                    </p>
                    
                    <?php if (isset($survey_title)): ?>
                        <div class="alert alert-info">
                            <strong>Encuesta completada:</strong> <?= htmlspecialchars($survey_title) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <p class="small text-muted mb-3">
                            <i class="fas fa-shield-alt"></i> Todas sus respuestas son confidenciales y anónimas
                        </p>
                        
                        <a href="<?= BASE_URL ?>" class="btn btn-home">
                            <i class="fas fa-home"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>