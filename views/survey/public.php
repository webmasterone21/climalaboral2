<?php
// ======================================
// views/survey/public.php - Vista Pública de Encuesta
// ======================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($survey['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .survey-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .survey-header {
            background: white;
            border-radius: 15px 15px 0 0;
            padding: 2rem;
            text-align: center;
            border-bottom: 3px solid #007bff;
        }
        .survey-body {
            background: white;
            padding: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .question-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid #007bff;
        }
        .likert-scale {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        .likert-option {
            flex: 1;
            text-align: center;
            min-width: 120px;
        }
        .likert-option input[type="radio"] {
            display: none;
        }
        .likert-option label {
            display: block;
            padding: 0.75rem 0.5rem;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        .likert-option input[type="radio"]:checked + label {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .likert-option label:hover {
            border-color: #007bff;
            background-color: rgba(0, 123, 255, 0.1);
        }
        .progress-bar-container {
            position: sticky;
            top: 0;
            background: white;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
            z-index: 1000;
        }
        .save-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1001;
        }
        .save-indicator.show {
            opacity: 1;
        }
        @media (max-width: 768px) {
            .likert-scale {
                flex-direction: column;
            }
            .likert-option {
                width: 100%;
            }
        }
    </style>
</head>
<body data-survey-id="<?= $survey['id'] ?>" data-participant-token="<?= $participant_token ?? '' ?>">
    <!-- Save Indicator -->
    <div class="save-indicator" id="save-indicator">
        <i class="fas fa-check"></i> Progreso guardado
    </div>

    <!-- Progress Bar -->
    <div class="progress-bar-container">
        <div class="container">
            <div class="progress">
                <div class="progress-bar" id="progress-bar" style="width: 0%"></div>
            </div>
            <small class="text-muted">
                <span id="progress-text">0% completado</span>
                <span class="float-end">Pregunta <span id="current-question">1</span> de <?= count($questions) ?></span>
            </small>
        </div>
    </div>

    <div class="survey-container">
        <!-- Survey Header -->
        <div class="survey-header">
            <?php if ($survey['logo']): ?>
                <img src="<?= BASE_URL ?>uploads/<?= $survey['logo'] ?>" alt="Logo" class="mb-3" style="max-height: 80px;">
            <?php endif; ?>
            <h1><?= htmlspecialchars($survey['title']) ?></h1>
            <?php if ($survey['description']): ?>
                <p class="text-muted"><?= htmlspecialchars($survey['description']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Survey Body -->
        <div class="survey-body">
            <?php if ($survey['introduction']): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?= nl2br(htmlspecialchars($survey['introduction'])) ?>
                </div>
            <?php endif; ?>

            <?php if ($survey['instructions']): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Instrucciones:</strong><br>
                    <?= nl2br(htmlspecialchars($survey['instructions'])) ?>
                </div>
            <?php endif; ?>

            <form id="survey-form" method="POST" action="<?= BASE_URL ?>survey/<?= $survey['id'] ?>/submit">
                <?php $questionIndex = 1; ?>
                <?php foreach ($questions as $question): ?>
                    <div class="question-card" data-question-id="<?= $question['id'] ?>">
                        <h5>
                            <?= $questionIndex ?>. <?= htmlspecialchars($question['question_text']) ?>
                            <?php if ($question['required']): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </h5>
                        
                        <?php if ($question['description']): ?>
                            <p class="text-muted small"><?= htmlspecialchars($question['description']) ?></p>
                        <?php endif; ?>

                        <?php if ($question['type_name'] === 'likert_5'): ?>
                            <div class="likert-scale">
                                <?php foreach ($question['options'] as $option): ?>
                                    <div class="likert-option">
                                        <input type="radio" 
                                               id="q<?= $question['id'] ?>_<?= $option['option_value'] ?>" 
                                               name="question_<?= $question['id'] ?>" 
                                               value="<?= $option['option_value'] ?>"
                                               <?= $question['required'] ? 'required' : '' ?>>
                                        <label for="q<?= $question['id'] ?>_<?= $option['option_value'] ?>">
                                            <div><?= $option['option_value'] ?></div>
                                            <small><?= htmlspecialchars($option['option_text']) ?></small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($question['type_name'] === 'yes_no'): ?>
                            <div class="d-flex gap-3 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="question_<?= $question['id'] ?>" 
                                           id="q<?= $question['id'] ?>_yes" 
                                           value="1" <?= $question['required'] ? 'required' : '' ?>>
                                    <label class="form-check-label" for="q<?= $question['id'] ?>_yes">
                                        <i class="fas fa-check text-success"></i> Sí
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="question_<?= $question['id'] ?>" 
                                           id="q<?= $question['id'] ?>_no" 
                                           value="0" <?= $question['required'] ? 'required' : '' ?>>
                                    <label class="form-check-label" for="q<?= $question['id'] ?>_no">
                                        <i class="fas fa-times text-danger"></i> No
                                    </label>
                                </div>
                            </div>

                        <?php elseif ($question['type_name'] === 'text'): ?>
                            <div class="mt-3">
                                <textarea class="form-control" 
                                          name="question_<?= $question['id'] ?>" 
                                          rows="4" 
                                          placeholder="Escriba su respuesta aquí..."
                                          <?= $question['required'] ? 'required' : '' ?>></textarea>
                            </div>

                        <?php elseif ($question['type_name'] === 'multiple_choice'): ?>
                            <div class="mt-3">
                                <?php foreach ($question['options'] as $option): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" 
                                               name="question_<?= $question['id'] ?>" 
                                               id="q<?= $question['id'] ?>_<?= $option['id'] ?>" 
                                               value="<?= $option['option_value'] ?>"
                                               <?= $question['required'] ? 'required' : '' ?>>
                                        <label class="form-check-label" for="q<?= $question['id'] ?>_<?= $option['id'] ?>">
                                            <?= htmlspecialchars($option['option_text']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php $questionIndex++; ?>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="save-progress-btn">
                        <i class="fas fa-save"></i> Guardar Progreso
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Enviar Encuesta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/survey-progress.js"></script>
    <script>
        // Form submission handler
        document.getElementById('survey-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required questions
            const requiredQuestions = document.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            requiredQuestions.forEach(input => {
                if (input.type === 'radio') {
                    const radioGroup = document.querySelectorAll(`input[name="${input.name}"]`);
                    const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                    if (!isChecked) {
                        isValid = false;
                        input.closest('.question-card').classList.add('border-danger');
                    } else {
                        input.closest('.question-card').classList.remove('border-danger');
                    }
                } else {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                }
            });
            
            if (!isValid) {
                alert('Por favor complete todas las preguntas obligatorias marcadas con *');
                return;
            }
            
            // Submit form
            if (confirm('¿Está seguro que desea enviar la encuesta? No podrá modificar sus respuestas después.')) {
                this.submit();
            }
        });

        // Save progress manually
        document.getElementById('save-progress-btn').addEventListener('click', function() {
            if (window.surveyProgress) {
                window.surveyProgress.saveProgress();
                document.getElementById('save-indicator').classList.add('show');
                setTimeout(() => {
                    document.getElementById('save-indicator').classList.remove('show');
                }, 2000);
            }
        });
    </script>
</body>
</html>