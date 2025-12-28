<?php
// views/admin/questions/index.php
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <!-- Header de la página -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <!-- Breadcrumb -->
                    <div class="page-pretitle">
                        <ol class="breadcrumb breadcrumb-arrows">
                            <li class="breadcrumb-item"><a href="/admin">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="/admin/surveys">Encuestas</a></li>
                            <li class="breadcrumb-item active">Preguntas</li>
                        </ol>
                    </div>
                    <h2 class="page-title">
                        Constructor de Preguntas
                        <div class="text-muted fs-5 fw-normal">
                            <?= htmlspecialchars($survey['title']) ?>
                        </div>
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <div class="btn-list">
                        <a href="/admin/surveys/<?= $survey['id'] ?>" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Volver a Encuesta
                        </a>
                        <a href="/survey/<?= $survey['id'] ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-eye me-2"></i>
                            Vista Previa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <!-- Panel Principal - Lista de Preguntas -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list-ul me-2"></i>
                                Preguntas de la Encuesta
                            </h3>
                            <div class="card-actions">
                                <div class="dropdown">
                                    <a href="#" class="btn-action dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#" onclick="importQuestions()">
                                            <i class="fas fa-file-import me-2"></i>
                                            Importar Preguntas
                                        </a>
                                        <a class="dropdown-item" href="#" onclick="exportQuestions()">
                                            <i class="fas fa-file-export me-2"></i>
                                            Exportar Preguntas
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" onclick="clearAllQuestions()">
                                            <i class="fas fa-trash me-2"></i>
                                            Limpiar Todo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- Estadísticas de preguntas -->
                            <div class="row mb-4">
                                <div class="col-sm-6 col-lg-3">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-primary text-white avatar">
                                                        <i class="fas fa-question"></i>
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">
                                                        Total Preguntas
                                                    </div>
                                                    <div class="text-muted">
                                                        <span id="total-questions"><?= count($questions) ?></span> preguntas
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-sm-6 col-lg-3">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-success text-white avatar">
                                                        <i class="fas fa-check"></i>
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">
                                                        Obligatorias
                                                    </div>
                                                    <div class="text-muted">
                                                        <span id="required-questions"><?= count(array_filter($questions, function($q) { return $q['required']; })) ?></span> requeridas
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-sm-6 col-lg-3">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-info text-white avatar">
                                                        <i class="fas fa-folder"></i>
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">
                                                        Categorías
                                                    </div>
                                                    <div class="text-muted">
                                                        <span id="active-categories"><?= count($questionsByCategory) ?></span> activas
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-sm-6 col-lg-3">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="bg-warning text-white avatar">
                                                        <i class="fas fa-clock"></i>
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <div class="font-weight-medium">
                                                        Tiempo Est.
                                                    </div>
                                                    <div class="text-muted">
                                                        <span id="estimated-time"><?= ceil(count($questions) * 0.5) ?></span> min
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de preguntas por categoría -->
                            <div id="questions-container">
                                <?php if (empty($questions)): ?>
                                    <!-- Estado vacío -->
                                    <div class="empty-state text-center py-5">
                                        <div class="empty-icon">
                                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                        </div>
                                        <h3 class="empty-title">No hay preguntas creadas</h3>
                                        <p class="empty-subtitle text-muted">
                                            Comienza agregando preguntas a tu encuesta usando el constructor de la derecha
                                        </p>
                                        <div class="empty-action">
                                            <button class="btn btn-primary" onclick="scrollToBuilder()">
                                                <i class="fas fa-plus me-2"></i>
                                                Crear Primera Pregunta
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Preguntas agrupadas por categoría -->
                                    <?php foreach ($categories as $category): ?>
                                        <?php if (isset($questionsByCategory[$category['id']])): ?>
                                            <div class="category-section mb-4" data-category-id="<?= $category['id'] ?>">
                                                <!-- Header de categoría -->
                                                <div class="category-header d-flex align-items-center justify-content-between p-3 bg-light rounded-top">
                                                    <div class="d-flex align-items-center">
                                                        <span class="category-icon me-2">
                                                            <i class="fas fa-folder text-primary"></i>
                                                        </span>
                                                        <h5 class="mb-0 fw-bold">
                                                            <?= htmlspecialchars($category['name']) ?>
                                                        </h5>
                                                        <span class="badge bg-secondary ms-2">
                                                            <?= count($questionsByCategory[$category['id']]['questions']) ?> preguntas
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="category-actions">
                                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                                onclick="addQuestionToCategory(<?= $category['id'] ?>)"
                                                                title="Agregar pregunta a esta categoría">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-secondary" 
                                                                onclick="toggleCategory(<?= $category['id'] ?>)"
                                                                title="Expandir/Contraer categoría">
                                                            <i class="fas fa-chevron-down category-toggle"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Lista de preguntas -->
                                                <div class="questions-list border border-top-0 rounded-bottom" data-category="<?= $category['id'] ?>">
                                                    <?php foreach ($questionsByCategory[$category['id']]['questions'] as $index => $question): ?>
                                                        <div class="question-item" data-question-id="<?= $question['id'] ?>">
                                                            <div class="d-flex align-items-start p-3 border-bottom">
                                                                <!-- Drag handle -->
                                                                <div class="drag-handle me-3 text-muted" style="cursor: move;">
                                                                    <i class="fas fa-grip-vertical"></i>
                                                                </div>
                                                                
                                                                <!-- Question content -->
                                                                <div class="flex-grow-1">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <span class="question-number badge bg-primary me-2">
                                                                            #<?= $question['order_position'] ?: ($index + 1) ?>
                                                                        </span>
                                                                        
                                                                        <?php if ($question['required']): ?>
                                                                            <span class="badge bg-danger me-2">
                                                                                <i class="fas fa-asterisk"></i> Obligatoria
                                                                            </span>
                                                                        <?php endif; ?>
                                                                        
                                                                        <span class="badge bg-info">
                                                                            <?= htmlspecialchars($question['type_description']) ?>
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <h6 class="question-text mb-1">
                                                                        <?= htmlspecialchars($question['question_text']) ?>
                                                                    </h6>
                                                                    
                                                                    <?php if ($question['description']): ?>
                                                                        <p class="text-muted small mb-2">
                                                                            <?= htmlspecialchars($question['description']) ?>
                                                                        </p>
                                                                    <?php endif; ?>
                                                                    
                                                                    <!-- Options preview (for multiple choice questions) -->
                                                                    <?php if (isset($question['options']) && !empty($question['options'])): ?>
                                                                        <div class="options-preview mt-2">
                                                                            <small class="text-muted">Opciones:</small>
                                                                            <div class="ms-3">
                                                                                <?php foreach ($question['options'] as $option): ?>
                                                                                    <div class="small text-muted">
                                                                                        • <?= htmlspecialchars($option['option_text']) ?>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                
                                                                <!-- Actions -->
                                                                <div class="question-actions ms-3">
                                                                    <div class="btn-group-vertical btn-group-sm">
                                                                        <button class="btn btn-outline-primary" 
                                                                                onclick="editQuestion(<?= $question['id'] ?>)"
                                                                                title="Editar pregunta">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-secondary" 
                                                                                onclick="duplicateQuestion(<?= $question['id'] ?>)"
                                                                                title="Duplicar pregunta">
                                                                            <i class="fas fa-copy"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-danger" 
                                                                                onclick="deleteQuestion(<?= $question['id'] ?>)"
                                                                                title="Eliminar pregunta">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Constructor de Preguntas -->
                <div class="col-lg-4">
                    <div class="sticky-panel" style="position: sticky; top: 20px;">
                        <?php include 'builder.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h3>¿Eliminar pregunta?</h3>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos de la página -->
<script src="/assets/js/sortable.min.js"></script>
<script src="/assets/js/question-builder.js"></script>

<script>
// Variables globales
const surveyId = <?= $survey['id'] ?>;
const questionTypes = <?= json_encode($types) ?>;
const categories = <?= json_encode($categories) ?>;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initializeQuestionManager();
    initializeSortable();
});

// Funciones de gestión de preguntas
function scrollToBuilder() {
    document.querySelector('.sticky-panel').scrollIntoView({ behavior: 'smooth' });
}

function addQuestionToCategory(categoryId) {
    // Pre-seleccionar la categoría en el constructor
    document.getElementById('category_id').value = categoryId;
    scrollToBuilder();
    
    // Animar el constructor para llamar la atención
    const builder = document.querySelector('.sticky-panel .card');
    builder.style.transform = 'scale(1.02)';
    builder.style.boxShadow = '0 0 20px rgba(0, 123, 255, 0.3)';
    
    setTimeout(() => {
        builder.style.transform = 'scale(1)';
        builder.style.boxShadow = '';
    }, 300);
}

function toggleCategory(categoryId) {
    const category = document.querySelector(`[data-category-id="${categoryId}"]`);
    const questionsList = category.querySelector('.questions-list');
    const toggleIcon = category.querySelector('.category-toggle');
    
    if (questionsList.style.display === 'none') {
        questionsList.style.display = 'block';
        toggleIcon.className = 'fas fa-chevron-down category-toggle';
    } else {
        questionsList.style.display = 'none';
        toggleIcon.className = 'fas fa-chevron-right category-toggle';
    }
}

function editQuestion(questionId) {
    // Cargar datos de la pregunta en el constructor
    fetch(`/admin/questions/${questionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateQuestionForm(data.question);
                scrollToBuilder();
            }
        })
        .catch(error => {
            console.error('Error loading question:', error);
            showAlert('Error al cargar la pregunta', 'error');
        });
}

function duplicateQuestion(questionId) {
    fetch(`/admin/questions/${questionId}/duplicate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Pregunta duplicada exitosamente', 'success');
            location.reload(); // Recargar para mostrar la nueva pregunta
        } else {
            showAlert(data.message || 'Error al duplicar la pregunta', 'error');
        }
    })
    .catch(error => {
        console.error('Error duplicating question:', error);
        showAlert('Error al duplicar la pregunta', 'error');
    });
}

function deleteQuestion(questionId) {
    // Mostrar modal de confirmación
    const modal = new bootstrap.Modal(document.getElementById('deleteQuestionModal'));
    modal.show();
    
    // Configurar el botón de confirmación
    document.getElementById('confirmDeleteBtn').onclick = function() {
        performDelete(questionId);
        modal.hide();
    };
}

function performDelete(questionId) {
    fetch(`/admin/questions/${questionId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remover el elemento del DOM con animación
            const questionItem = document.querySelector(`[data-question-id="${questionId}"]`);
            if (questionItem) {
                questionItem.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => {
                    questionItem.remove();
                    updateQuestionStats();
                }, 300);
            }
            
            showAlert('Pregunta eliminada exitosamente', 'success');
        } else {
            showAlert(data.message || 'Error al eliminar la pregunta', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting question:', error);
        showAlert('Error al eliminar la pregunta', 'error');
    });
}

function updateQuestionStats() {
    const totalQuestions = document.querySelectorAll('.question-item').length;
    const requiredQuestions = document.querySelectorAll('.badge:contains("Obligatoria")').length;
    const activeCategories = document.querySelectorAll('.category-section').length;
    
    document.getElementById('total-questions').textContent = totalQuestions;
    document.getElementById('required-questions').textContent = requiredQuestions;
    document.getElementById('active-categories').textContent = activeCategories;
    document.getElementById('estimated-time').textContent = Math.ceil(totalQuestions * 0.5);
}

function initializeSortable() {
    // Hacer las listas de preguntas ordenables
    document.querySelectorAll('.questions-list').forEach(list => {
        new Sortable(list, {
            group: 'questions',
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateQuestionOrder(evt);
            }
        });
    });
}

function updateQuestionOrder(evt) {
    const questionId = evt.item.getAttribute('data-question-id');
    const newCategoryId = evt.to.getAttribute('data-category');
    const newPosition = evt.newIndex + 1;
    
    fetch('/admin/questions/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            question_id: questionId,
            category_id: newCategoryId,
            position: newPosition
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar números de pregunta
            updateQuestionNumbers();
        } else {
            showAlert('Error al actualizar el orden', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating order:', error);
        showAlert('Error al actualizar el orden', 'error');
    });
}

function updateQuestionNumbers() {
    document.querySelectorAll('.category-section').forEach(category => {
        const questions = category.querySelectorAll('.question-item');
        questions.forEach((question, index) => {
            const numberBadge = question.querySelector('.question-number');
            if (numberBadge) {
                numberBadge.textContent = `#${index + 1}`;
            }
        });
    });
}

// CSS animations
const style = document.createElement('style');
style.textContent = `
    .sortable-ghost {
        opacity: 0.4;
    }
    .sortable-chosen {
        background-color: #f8f9fa;
    }
    .sortable-drag {
        transform: rotate(5deg);
    }
    @keyframes slideOut {
        to {
            height: 0;
            opacity: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once __DIR__ . '/../../layouts/admin_footer.php'; ?>