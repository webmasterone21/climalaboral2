<?php
/**
 * Vista: Interfaz Pública de Participación en Encuesta - Sistema HERCO v2.0
 * 
 * Interfaz moderna y accesible para que los participantes respondan encuestas
 * con navegación fluida, validación en tiempo real y guardado automático
 * 
 * @package HERCO\Views\Survey
 * @version 2.0.0
 * @author Sistema HERCO
 */

// Datos de la vista
$survey = $survey ?? [];
$questions = $questions ?? [];
$participant = $participant ?? [];
$progress = $progress ?? [];
$company = $company ?? [];

// Configuración de la encuesta
$surveyConfig = [
    'id' => $survey['id'] ?? 0,
    'title' => $survey['title'] ?? 'Encuesta de Clima Laboral',
    'description' => $survey['description'] ?? '',
    'totalQuestions' => count($questions),
    'estimatedTime' => $survey['estimated_time'] ?? 15,
    'allowBack' => $survey['allow_back_navigation'] ?? true,
    'autoSave' => $survey['enable_auto_save'] ?? true,
    'showProgress' => $survey['show_progress'] ?? true
];

// Configuración del participante
$participantConfig = [
    'id' => $participant['id'] ?? null,
    'token' => $participant['token'] ?? '',
    'startedAt' => $progress['started_at'] ?? null,
    'currentQuestion' => $progress['current_question'] ?? 0,
    'responses' => $progress['responses'] ?? []
];

// Información de la empresa
$companyInfo = [
    'name' => $company['name'] ?? 'Organización',
    'logo' => $company['logo'] ?? null,
    'primaryColor' => $company['primary_color'] ?? '#667eea',
    'secondaryColor' => $company['secondary_color'] ?? '#764ba2'
];

// Meta tags para SEO y redes sociales
$pageTitle = "Encuesta: " . $surveyConfig['title'];
$pageDescription = $surveyConfig['description'] ?: "Participe en nuestra encuesta de clima laboral";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= $companyInfo['logo'] ?: '/assets/images/herco-logo.png' ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/images/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/apple-touch-icon.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/survey.css" rel="stylesheet">
    
    <!-- Custom Colors -->
    <style>
        :root {
            --company-primary: <?= $companyInfo['primaryColor'] ?>;
            --company-secondary: <?= $companyInfo['secondaryColor'] ?>;
            --company-gradient: linear-gradient(135deg, <?= $companyInfo['primaryColor'] ?> 0%, <?= $companyInfo['secondaryColor'] ?> 100%);
        }
        
        .survey-header {
            background: var(--company-gradient);
        }
        
        .nav-button-primary,
        .progress-bar {
            background: var(--company-gradient);
        }
        
        .question-number,
        .likert-option input:checked + label,
        .option-item input:checked + label::before {
            background: var(--company-primary);
        }
    </style>
</head>

<body class="survey-page">
    <!-- Indicador de guardado automático -->
    <div id="saveIndicator" class="save-indicator">
        <i class="fas fa-spinner fa-spin"></i> Guardando...
    </div>

    <!-- Contenedor principal -->
    <div class="survey-container" id="surveyContainer" 
         data-survey-id="<?= $surveyConfig['id'] ?>"
         data-participant-id="<?= $participantConfig['id'] ?>"
         data-participant-token="<?= $participantConfig['token'] ?>">
        
        <!-- Tarjeta principal de la encuesta -->
        <div class="survey-card">
            <!-- Header de la encuesta -->
            <div class="survey-header">
                <?php if ($companyInfo['logo']): ?>
                <div class="company-logo mb-3">
                    <img src="<?= htmlspecialchars($companyInfo['logo']) ?>" 
                         alt="<?= htmlspecialchars($companyInfo['name']) ?>"
                         style="max-height: 60px; max-width: 200px;">
                </div>
                <?php endif; ?>
                
                <h1 class="survey-title"><?= htmlspecialchars($surveyConfig['title']) ?></h1>
                
                <?php if ($surveyConfig['description']): ?>
                <p class="survey-description"><?= htmlspecialchars($surveyConfig['description']) ?></p>
                <?php endif; ?>
                
                <div class="survey-meta">
                    <div class="survey-meta-item">
                        <i class="fas fa-list"></i>
                        <span><?= $surveyConfig['totalQuestions'] ?> preguntas</span>
                    </div>
                    <div class="survey-meta-item">
                        <i class="fas fa-clock"></i>
                        <span>~<?= $surveyConfig['estimatedTime'] ?> minutos</span>
                    </div>
                    <div class="survey-meta-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Confidencial</span>
                    </div>
                </div>
            </div>

            <!-- Indicador de progreso -->
            <?php if ($surveyConfig['showProgress']): ?>
            <div class="progress-container">
                <div class="progress-header">
                    <span class="progress-text" id="progressText">
                        Pregunta 1 de <?= $surveyConfig['totalQuestions'] ?>
                    </span>
                    <span class="progress-percentage" id="progressPercentage">0%</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar" 
                         role="progressbar" 
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100"
                         style="width: 0%"></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulario de la encuesta -->
            <form id="surveyForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="survey_id" value="<?= $surveyConfig['id'] ?>">
                <input type="hidden" name="participant_id" value="<?= $participantConfig['id'] ?>">
                <input type="hidden" name="participant_token" value="<?= $participantConfig['token'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

                <!-- Contenedor de preguntas -->
                <div class="questions-container" id="questionsContainer">
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card <?= $index === 0 ? 'active' : '' ?>" 
                         data-question-id="<?= $question['id'] ?>"
                         data-question-index="<?= $index ?>"
                         data-type="<?= htmlspecialchars($question['type']) ?>"
                         data-required="<?= $question['required'] ? 'true' : 'false' ?>"
                         data-category="<?= htmlspecialchars($question['category'] ?? '') ?>"
                         style="<?= $index === 0 ? '' : 'display: none;' ?>">
                        
                        <!-- Header de la pregunta -->
                        <div class="question-header">
                            <div class="question-number">
                                <?= $index + 1 ?> / <?= $surveyConfig['totalQuestions'] ?>
                            </div>
                            
                            <h2 class="question-title" id="question-<?= $index ?>-title">
                                <?= htmlspecialchars($question['text']) ?>
                            </h2>
                            
                            <?php if (!empty($question['description'])): ?>
                            <p class="question-description">
                                <?= htmlspecialchars($question['description']) ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($question['required']): ?>
                            <div class="question-required">
                                <i class="fas fa-asterisk"></i>
                                <span>Esta pregunta es obligatoria</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Contenido de la pregunta según tipo -->
                        <div class="question-content">
                            <?php echo $this->renderQuestionContent($question, $index, $participantConfig['responses']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Navegación -->
                <div class="navigation-container">
                    <button type="button" 
                            id="prevButton" 
                            class="nav-button nav-button-secondary"
                            <?= !$surveyConfig['allowBack'] ? 'style="display: none;"' : '' ?>
                            disabled>
                        <i class="fas fa-chevron-left"></i>
                        Anterior
                    </button>
                    
                    <div class="nav-center">
                        <?php if ($surveyConfig['autoSave']): ?>
                        <small class="text-muted">
                            <i class="fas fa-save"></i>
                            Su progreso se guarda automáticamente
                        </small>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" 
                            id="nextButton" 
                            class="nav-button nav-button-primary">
                        Siguiente
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <button type="button" 
                            id="submitButton" 
                            class="nav-button nav-button-success"
                            style="display: none;">
                        <i class="fas fa-paper-plane"></i>
                        Enviar Encuesta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Confirmación de envío -->
    <div class="modal fade" id="submitConfirmModal" tabindex="-1" aria-labelledby="submitConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="submitConfirmModalLabel">
                        <i class="fas fa-check-circle text-success"></i>
                        Confirmar Envío
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">¿Está seguro de que desea enviar su encuesta?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Una vez enviada, no podrá modificar sus respuestas.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" onclick="surveyEngine.submitSurvey()">
                        <i class="fas fa-paper-plane"></i>
                        Confirmar Envío
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Preguntas incompletas -->
    <div class="modal fade" id="incompleteWarningModal" tabindex="-1" aria-labelledby="incompleteWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="incompleteWarningModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Preguntas Obligatorias Pendientes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Las siguientes preguntas obligatorias aún no han sido respondidas:</p>
                    <ul class="list-group missing-questions-list">
                        <!-- Se llena dinámicamente via JavaScript -->
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-edit"></i>
                        Completar Preguntas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Advertencia de inactividad -->
    <div class="modal fade" id="inactivityWarningModal" tabindex="-1" aria-labelledby="inactivityWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inactivityWarningModalLabel">
                        <i class="fas fa-clock text-warning"></i>
                        Sesión Inactiva
                    </h5>
                </div>
                <div class="modal-body">
                    <p>Su sesión ha estado inactiva por un tiempo prolongado.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-save"></i>
                        <strong>Tranquilo:</strong> Su progreso ha sido guardado automáticamente.
                    </div>
                    <p>¿Desea continuar con la encuesta?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='/survey/exit'">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-play"></i>
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmación de salida -->
    <div class="modal fade" id="exitModal" tabindex="-1" aria-labelledby="exitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exitModalLabel">
                        <i class="fas fa-sign-out-alt text-warning"></i>
                        Confirmar Salida
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea salir de la encuesta?</p>
                    <div class="alert alert-success">
                        <i class="fas fa-save"></i>
                        <strong>Su progreso será guardado</strong> y podrá continuar más tarde.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" onclick="window.location.href='/survey/exit'">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir y Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/notifications.js"></script>
    <script src="/assets/js/survey-engine.js"></script>
    
    <!-- Configuración global -->
    <script>
        // Configuración global para el motor de encuestas
        window.surveyConfig = <?= json_encode($surveyConfig) ?>;
        window.participantConfig = <?= json_encode($participantConfig) ?>;
        window.surveyId = <?= $surveyConfig['id'] ?>;
        window.participantId = '<?= $participantConfig['id'] ?>';
        
        // Datos para restaurar progreso
        window.savedResponses = <?= json_encode($participantConfig['responses']) ?>;
        
        // Configuración de características
        window.features = {
            autoSave: <?= $surveyConfig['autoSave'] ? 'true' : 'false' ?>,
            allowBack: <?= $surveyConfig['allowBack'] ? 'true' : 'false' ?>,
            showProgress: <?= $surveyConfig['showProgress'] ? 'true' : 'false' ?>
        };
    </script>

    <!-- Analytics y seguimiento (opcional) -->
    <?php if (!empty($survey['analytics_code'])): ?>
    <!-- Google Analytics o similar -->
    <script>
        // Código de analytics personalizado
        <?= $survey['analytics_code'] ?>
    </script>
    <?php endif; ?>

</body>
</html>

<?php
/**
 * Función auxiliar para renderizar contenido de pregunta
 * Esta función debería estar en el controlador o helper, 
 * pero la incluimos aquí para completitud
 */
function renderQuestionContent($question, $index, $savedResponses = []) {
    $questionId = $question['id'];
    $questionType = $question['type'];
    $options = $question['options'] ?? [];
    $savedValue = $savedResponses[$questionId]['value'] ?? null;
    
    $html = '';
    
    switch ($questionType) {
        case 'likert_5':
        case 'likert_7':
        case 'likert_3':
            $html .= '<div class="likert-container">';
            $html .= '<div class="likert-scale">';
            
            foreach ($options as $optionIndex => $option) {
                $optionValue = $optionIndex + 1;
                $checked = ($savedValue == $optionValue) ? 'checked' : '';
                
                $html .= '<div class="likert-option">';
                $html .= '<input type="radio" ';
                $html .= 'id="q' . $questionId . '_' . $optionValue . '" ';
                $html .= 'name="question_' . $questionId . '" ';
                $html .= 'value="' . $optionValue . '" ' . $checked . '>';
                $html .= '<label for="q' . $questionId . '_' . $optionValue . '">';
                $html .= htmlspecialchars($option);
                $html .= '</label>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            
            if (count($options) >= 5) {
                $html .= '<div class="likert-labels">';
                $html .= '<span>Muy en desacuerdo</span>';
                $html .= '<span>Muy de acuerdo</span>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            break;
            
        case 'multiple_choice':
            $html .= '<div class="options-container">';
            
            foreach ($options as $optionIndex => $option) {
                $optionValue = $optionIndex + 1;
                $checked = ($savedValue == $optionValue) ? 'checked' : '';
                
                $html .= '<div class="option-item">';
                $html .= '<input type="radio" ';
                $html .= 'id="q' . $questionId . '_' . $optionValue . '" ';
                $html .= 'name="question_' . $questionId . '" ';
                $html .= 'value="' . $optionValue . '" ' . $checked . '>';
                $html .= '<label for="q' . $questionId . '_' . $optionValue . '">';
                $html .= htmlspecialchars($option);
                $html .= '</label>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            break;
            
        case 'checkbox':
            $html .= '<div class="options-container">';
            $savedArray = is_array($savedValue) ? $savedValue : [];
            
            foreach ($options as $optionIndex => $option) {
                $optionValue = $optionIndex + 1;
                $checked = in_array($optionValue, $savedArray) ? 'checked' : '';
                
                $html .= '<div class="option-item">';
                $html .= '<input type="checkbox" ';
                $html .= 'id="q' . $questionId . '_' . $optionValue . '" ';
                $html .= 'name="question_' . $questionId . '[]" ';
                $html .= 'value="' . $optionValue . '" ' . $checked . '>';
                $html .= '<label for="q' . $questionId . '_' . $optionValue . '">';
                $html .= htmlspecialchars($option);
                $html .= '</label>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            break;
            
        case 'text':
            $value = htmlspecialchars($savedValue ?? '');
            $maxLength = $question['max_length'] ?? 500;
            
            $html .= '<div class="text-input-container">';
            $html .= '<input type="text" ';
            $html .= 'class="text-input" ';
            $html .= 'name="question_' . $questionId . '" ';
            $html .= 'id="q' . $questionId . '" ';
            $html .= 'value="' . $value . '" ';
            $html .= 'maxlength="' . $maxLength . '" ';
            $html .= 'placeholder="Escriba su respuesta aquí...">';
            $html .= '<div class="character-counter">';
            $html .= '<span class="current-count">0</span> / ' . $maxLength;
            $html .= '</div>';
            $html .= '</div>';
            break;
            
        case 'textarea':
            $value = htmlspecialchars($savedValue ?? '');
            $maxLength = $question['max_length'] ?? 2000;
            
            $html .= '<div class="text-input-container">';
            $html .= '<textarea ';
            $html .= 'class="textarea-input" ';
            $html .= 'name="question_' . $questionId . '" ';
            $html .= 'id="q' . $questionId . '" ';
            $html .= 'maxlength="' . $maxLength . '" ';
            $html .= 'rows="5" ';
            $html .= 'placeholder="Escriba su respuesta detallada aquí...">';
            $html .= $value;
            $html .= '</textarea>';
            $html .= '<div class="character-counter">';
            $html .= '<span class="current-count">0</span> / ' . $maxLength;
            $html .= '</div>';
            $html .= '</div>';
            break;
            
        case 'rating':
            $value = intval($savedValue ?? 0);
            
            $html .= '<div class="rating-container">';
            $html .= '<div class="rating-stars" data-rating="' . $value . '">';
            
            for ($i = 1; $i <= 5; $i++) {
                $activeClass = ($i <= $value) ? 'active' : '';
                $html .= '<i class="fas fa-star rating-star ' . $activeClass . '" data-value="' . $i . '"></i>';
            }
            
            $html .= '</div>';
            $html .= '<input type="hidden" name="question_' . $questionId . '" value="' . $value . '">';
            $html .= '<div class="rating-value">' . ($value > 0 ? $value . '/5' : 'Sin calificar') . '</div>';
            $html .= '</div>';
            break;
            
        case 'slider':
            $value = intval($savedValue ?? 50);
            $min = $question['min_value'] ?? 0;
            $max = $question['max_value'] ?? 100;
            $step = $question['step'] ?? 1;
            
            $html .= '<div class="slider-container">';
            $html .= '<input type="range" ';
            $html .= 'class="slider-input" ';
            $html .= 'name="question_' . $questionId . '" ';
            $html .= 'id="q' . $questionId . '" ';
            $html .= 'min="' . $min . '" ';
            $html .= 'max="' . $max . '" ';
            $html .= 'step="' . $step . '" ';
            $html .= 'value="' . $value . '">';
            $html .= '<div class="slider-labels">';
            $html .= '<span>' . $min . '</span>';
            $html .= '<span>' . $max . '</span>';
            $html .= '</div>';
            $html .= '<div class="slider-value">' . $value . '</div>';
            $html .= '</div>';
            break;
            
        case 'yes_no':
            $html .= '<div class="options-container">';
            
            $yesChecked = ($savedValue === 'yes') ? 'checked' : '';
            $noChecked = ($savedValue === 'no') ? 'checked' : '';
            
            $html .= '<div class="option-item">';
            $html .= '<input type="radio" id="q' . $questionId . '_yes" name="question_' . $questionId . '" value="yes" ' . $yesChecked . '>';
            $html .= '<label for="q' . $questionId . '_yes">Sí</label>';
            $html .= '</div>';
            
            $html .= '<div class="option-item">';
            $html .= '<input type="radio" id="q' . $questionId . '_no" name="question_' . $questionId . '" value="no" ' . $noChecked . '>';
            $html .= '<label for="q' . $questionId . '_no">No</label>';
            $html .= '</div>';
            
            $html .= '</div>';
            break;
            
        case 'date':
            $value = $savedValue ?? '';
            
            $html .= '<div class="text-input-container">';
            $html .= '<input type="date" ';
            $html .= 'class="text-input" ';
            $html .= 'name="question_' . $questionId . '" ';
            $html .= 'id="q' . $questionId . '" ';
            $html .= 'value="' . htmlspecialchars($value) . '">';
            $html .= '</div>';
            break;
            
        case 'number':
            $value = $savedValue ?? '';
            $min = $question['min_value'] ?? '';
            $max = $question['max_value'] ?? '';
            
            $html .= '<div class="text-input-container">';
            $html .= '<input type="number" ';
            $html .= 'class="text-input" ';
            $html .= 'name="question_' . $questionId . '" ';
            $html .= 'id="q' . $questionId . '" ';
            $html .= 'value="' . htmlspecialchars($value) . '" ';
            if ($min !== '') $html .= 'min="' . $min . '" ';
            if ($max !== '') $html .= 'max="' . $max . '" ';
            $html .= 'placeholder="Ingrese un número">';
            $html .= '</div>';
            break;
            
        case 'email':
            $value = htmlspecialchars($savedValue ?? '');
            
            $html .= '<div class="text-input-container">';
            $html .= '<input type="email" ';
            $html .= 'class="text-input" ';
            $html .= 'name="question_' . $questionId . '" ';
            $html .= 'id="q' . $questionId . '" ';
            $html .= 'value="' . $value . '" ';
            $html .= 'placeholder="correo@ejemplo.com">';
            $html .= '</div>';
            break;
            
        case 'phone':
            $value = htmlspecialchars($savedValue ?? '');
            
            $html .= '<div class="text-input-container">';
            $html .= '<input type="tel" ';
            $html .= 'class="text-input" ';
            $html .= 'name="question_' . $questionId . '" ';
            $html .= 'id="q' . $questionId . '" ';
            $html .= 'value="' . $value . '" ';
            $html .= 'placeholder="+504 0000-0000">';
            $html .= '</div>';
            break;
            
        case 'nps':
            $value = intval($savedValue ?? -1);
            
            $html .= '<div class="nps-container">';
            $html .= '<div class="nps-scale">';
            
            for ($i = 0; $i <= 10; $i++) {
                $checked = ($value === $i) ? 'checked' : '';
                $html .= '<div class="nps-option">';
                $html .= '<input type="radio" ';
                $html .= 'id="q' . $questionId . '_' . $i . '" ';
                $html .= 'name="question_' . $questionId . '" ';
                $html .= 'value="' . $i . '" ' . $checked . '>';
                $html .= '<label for="q' . $questionId . '_' . $i . '">' . $i . '</label>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            $html .= '<div class="nps-labels">';
            $html .= '<span>Muy poco probable</span>';
            $html .= '<span>Muy probable</span>';
            $html .= '</div>';
            $html .= '</div>';
            break;
            
        case 'file':
            $html .= '<div class="file-upload-container">';
            $html .= '<div class="file-upload-area" onclick="document.getElementById(\'q' . $questionId . '\').click()">';
            $html .= '<div class="file-upload-icon">';
            $html .= '<i class="fas fa-cloud-upload-alt"></i>';
            $html .= '</div>';
            $html .= '<div class="file-upload-text">Haga clic para seleccionar archivo</div>';
            $html .= '<div class="file-upload-hint">o arrastre el archivo aquí</div>';
            $html .= '</div>';
            $html .= '<input type="file" ';
            $html .= 'class="file-input" ';
            $html .= 'name="question_' . $questionId . '" ';
            $html .= 'id="q' . $questionId . '" ';
            $html .= 'accept="' . ($question['allowed_types'] ?? '.pdf,.doc,.docx,.jpg,.png') . '">';
            $html .= '</div>';
            break;
            
        default:
            $html .= '<div class="alert alert-warning">';
            $html .= '<i class="fas fa-exclamation-triangle"></i>';
            $html .= ' Tipo de pregunta no soportado: ' . htmlspecialchars($questionType);
            $html .= '</div>';
            break;
    }
    
    return $html;
}
?>