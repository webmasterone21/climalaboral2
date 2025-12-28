<?php
/**
 * SurveyController - VERSIÓN MÍNIMA SIN ERRORES
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 */

class SurveyController extends Controller
{
    // ✅ IMPORTANTE: NO declarar $db aquí
    // Ya se hereda automáticamente de Controller
    
    protected function initialize()
    {
        $this->requireAuth();
    }
    
    public function index()
    {
        try {
            $surveys = $this->db->fetchAll('SELECT * FROM surveys ORDER BY created_at DESC');
            
            $this->render('admin/surveys/index', [
                'page_title' => 'Gestión de Encuestas',
                'surveys' => $surveys
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Error en SurveyController::index: " . $e->getMessage());
            
            $this->render('admin/surveys/index', [
                'page_title' => 'Gestión de Encuestas',
                'surveys' => [],
                'error_message' => 'No se pudieron cargar las encuestas.'
            ], 'admin');
        }
    }
    
    public function create()
    {
        $this->render('admin/surveys/create', [
            'page_title' => 'Crear Encuesta'
        ], 'admin');
    }
    
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/surveys');
            return;
        }
        
        try {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($title)) {
                $this->setFlashMessage('El título es requerido', 'error');
                $this->redirect('/admin/surveys/create');
                return;
            }
            
            $surveyId = $this->db->insert('surveys', [
                'title' => $title,
                'description' => $description,
                'created_by' => $this->user['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($surveyId) {
                $this->setFlashMessage('Encuesta creada exitosamente', 'success');
                $this->redirect('/admin/surveys');
            } else {
                throw new Exception('No se pudo crear la encuesta');
            }
            
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $this->setFlashMessage('Error al crear la encuesta', 'error');
            $this->redirect('/admin/surveys/create');
        }
    }
    
    public function show()
    {
        $surveyId = $this->params['id'] ?? null;
        
        if (!$surveyId) {
            $this->redirect('/admin/surveys');
            return;
        }
        
        try {
            $survey = $this->db->fetch('SELECT * FROM surveys WHERE id = ?', [$surveyId]);
            
            if (!$survey) {
                $this->setFlashMessage('Encuesta no encontrada', 'error');
                $this->redirect('/admin/surveys');
                return;
            }
            
            $this->render('admin/surveys/show', [
                'page_title' => 'Detalles de Encuesta',
                'survey' => $survey
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $this->redirect('/admin/surveys');
        }
    }
    
    public function edit()
    {
        $surveyId = $this->params['id'] ?? null;
        
        if (!$surveyId) {
            $this->redirect('/admin/surveys');
            return;
        }
        
        try {
            $survey = $this->db->fetch('SELECT * FROM surveys WHERE id = ?', [$surveyId]);
            
            if (!$survey) {
                $this->redirect('/admin/surveys');
                return;
            }
            
            $this->render('admin/surveys/edit', [
                'page_title' => 'Editar Encuesta',
                'survey' => $survey
            ], 'admin');
            
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $this->redirect('/admin/surveys');
        }
    }
    
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/surveys');
            return;
        }
        
        $surveyId = $this->params['id'] ?? null;
        
        if (!$surveyId) {
            $this->redirect('/admin/surveys');
            return;
        }
        
        try {
            $title = trim($_POST['title'] ?? '');
            
            if (empty($title)) {
                $this->setFlashMessage('El título es requerido', 'error');
                $this->redirect('/admin/surveys/' . $surveyId . '/edit');
                return;
            }
            
            $this->db->update('surveys', [
                'title' => $title,
                'description' => trim($_POST['description'] ?? ''),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$surveyId]);
            
            $this->setFlashMessage('Encuesta actualizada', 'success');
            $this->redirect('/admin/surveys');
            
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $this->setFlashMessage('Error al actualizar', 'error');
            $this->redirect('/admin/surveys/' . $surveyId . '/edit');
        }
    }
    
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/surveys');
            return;
        }
        
        $surveyId = $this->params['id'] ?? null;
        
        if (!$surveyId) {
            $this->redirect('/admin/surveys');
            return;
        }
        
        try {
            $this->db->delete('surveys', 'id = ?', [$surveyId]);
            $this->setFlashMessage('Encuesta eliminada', 'success');
            
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $this->setFlashMessage('Error al eliminar', 'error');
        }
        
        $this->redirect('/admin/surveys');
    }
}
