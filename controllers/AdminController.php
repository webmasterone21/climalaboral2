<?php
/**
 * AdminController - Panel Administrativo
 * Sistema de Encuestas de Clima Laboral HERCO v2.0
 * ADAPTADO A ESTRUCTURA REAL DE BD
 * 
 * @version 2.0.0
 */

class AdminController extends Controller
{
    /**
     * Inicialización del controlador
     */
    protected function initialize()
    {
        // Requerir autenticación
        $this->requireAuth();
        
        // Verificar que sea administrador (opcional)
        // $this->requireAdmin();
    }
    
    /**
     * Dashboard administrativo - ADAPTADO
     */
    public function dashboard()
    {
        try {
            // Obtener estadísticas del sistema (adaptadas)
            $stats = $this->getDashboardStats();
            
            // Obtener encuestas recientes (adaptadas)
            $recentSurveys = $this->getRecentSurveys();
            
            // Obtener actividad reciente (sin tabla activity_log)
            $recentActivity = $this->getRecentActivity();
            
            // Preparar datos para la vista
            $data = [
                'page_title' => 'Dashboard Administrativo',
                'stats' => $stats,
                'recent_surveys' => $recentSurveys,
                'recent_activity' => $recentActivity,
                'quick_actions' => $this->getQuickActions()
            ];
            
            // Renderizar vista con layout admin
            $this->render('admin/dashboard', $data, 'admin');
            
        } catch (Exception $e) {
            error_log("Error en AdminController::dashboard: " . $e->getMessage());
            
            // En caso de error, mostrar dashboard con datos vacíos
            $this->render('admin/dashboard', [
                'page_title' => 'Dashboard Administrativo',
                'stats' => $this->getDefaultStats(),
                'recent_surveys' => [],
                'recent_activity' => [],
                'quick_actions' => $this->getQuickActions(),
                'error_message' => 'Algunos datos no pudieron cargarse correctamente.'
            ], 'admin');
        }
    }
    
    /**
     * Obtener estadísticas del dashboard - ADAPTADO
     * 
     * @return array
     */
    private function getDashboardStats()
    {
        try {
            $stats = [];
            
            // Total de usuarios (sin filtro de status si no existe la columna)
            try {
                $stats['total_users'] = $this->db->count('users');
            } catch (Exception $e) {
                error_log("⚠️ Error contando usuarios: " . $e->getMessage());
                $stats['total_users'] = 0;
            }
            
            // Total de encuestas
            try {
                $stats['total_surveys'] = $this->db->count('surveys');
            } catch (Exception $e) {
                error_log("⚠️ Error contando encuestas: " . $e->getMessage());
                $stats['total_surveys'] = 0;
            }
            
            // Encuestas activas (si la columna status existe en surveys)
            try {
                $stats['active_surveys'] = $this->db->count('surveys', 'status = ?', ['active']);
            } catch (Exception $e) {
                // Si no existe la columna status, usar total_surveys
                $stats['active_surveys'] = $stats['total_surveys'];
            }
            
            // Total de respuestas
            try {
                $stats['total_responses'] = $this->db->count('responses');
            } catch (Exception $e) {
                error_log("⚠️ Error contando respuestas: " . $e->getMessage());
                $stats['total_responses'] = 0;
            }
            
            // Total de empresas (si existe la tabla)
            try {
                $stats['total_companies'] = $this->db->count('companies');
            } catch (Exception $e) {
                // Si no existe la tabla companies, poner 1
                $stats['total_companies'] = 1;
            }
            
            // Total de departamentos (si existe la tabla)
            try {
                $stats['total_departments'] = $this->db->count('departments');
            } catch (Exception $e) {
                $stats['total_departments'] = 0;
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return $this->getDefaultStats();
        }
    }
    
    /**
     * Obtener estadísticas por defecto (cuando hay error)
     * 
     * @return array
     */
    private function getDefaultStats()
    {
        return [
            'total_users' => 0,
            'total_surveys' => 0,
            'active_surveys' => 0,
            'total_responses' => 0,
            'total_companies' => 1,
            'total_departments' => 0
        ];
    }
    
    /**
     * Obtener encuestas recientes - ADAPTADO
     * 
     * @param int $limit
     * @return array
     */
    private function getRecentSurveys($limit = 5)
    {
        try {
            // ✅ Query simple sin JOINs problemáticos
            $surveys = $this->db->fetchAll(
                "SELECT s.* 
                 FROM surveys s 
                 ORDER BY s.created_at DESC 
                 LIMIT ?",
                [$limit]
            );
            
            // Enriquecer con información del usuario creador si es posible
            if (!empty($surveys)) {
                foreach ($surveys as &$survey) {
                    // Intentar obtener usuario creador
                    if (isset($survey['created_by'])) {
                        try {
                            $user = $this->db->fetch(
                                'SELECT name, full_name FROM users WHERE id = ?',
                                [$survey['created_by']]
                            );
                            if ($user) {
                                $survey['creator_name'] = $user['name'] ?? $user['full_name'] ?? 'Usuario';
                            }
                        } catch (Exception $e) {
                            $survey['creator_name'] = 'N/A';
                        }
                    }
                }
            }
            
            return $surveys ?? [];
            
        } catch (Exception $e) {
            error_log("Error obteniendo encuestas recientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener actividad reciente del sistema - ADAPTADO
     * Sin tabla activity_log, devolver array vacío
     * 
     * @param int $limit
     * @return array
     */
    private function getRecentActivity($limit = 10)
    {
        // Como no existe la tabla activity_log, retornar vacío
        // Podrías construir actividad basada en otras tablas si quieres
        return [];
    }
    
    /**
     * Obtener acciones rápidas para el dashboard
     * 
     * @return array
     */
    private function getQuickActions()
    {
        return [
            [
                'title' => 'Nueva Encuesta',
                'description' => 'Crear una nueva encuesta de clima laboral',
                'icon' => 'plus-circle',
                'url' => '/admin/surveys/create',
                'color' => 'primary'
            ],
            [
                'title' => 'Ver Reportes',
                'description' => 'Consultar reportes y análisis HERCO',
                'icon' => 'chart-bar',
                'url' => '/admin/reports',
                'color' => 'success'
            ],
            [
                'title' => 'Gestionar Usuarios',
                'description' => 'Administrar usuarios del sistema',
                'icon' => 'users',
                'url' => '/admin/users',
                'color' => 'info'
            ],
            [
                'title' => 'Configuración',
                'description' => 'Ajustar configuraciones del sistema',
                'icon' => 'cog',
                'url' => '/admin/settings',
                'color' => 'warning'
            ]
        ];
    }
    
    /**
     * Vista de perfil del usuario
     */
    public function profile()
    {
        $this->requireAuth();
        
        $data = [
            'page_title' => 'Mi Perfil',
            'user' => $this->user
        ];
        
        $this->render('admin/profile', $data, 'admin');
    }
    
    /**
     * Actualizar perfil del usuario
     */
    public function updateProfile()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/profile');
        }
        
        try {
            // Validar y actualizar datos del perfil
            $updateData = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Actualizar contraseña si se proporcionó
            if (!empty($_POST['new_password'])) {
                if (!empty($_POST['current_password'])) {
                    // Verificar contraseña actual
                    if (password_verify($_POST['current_password'], $this->user['password'])) {
                        $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    } else {
                        $this->setFlashMessage('La contraseña actual es incorrecta', 'error');
                        $this->redirect('/admin/profile');
                    }
                }
            }
            
            // Actualizar en base de datos
            $this->db->update('users', $updateData, 'id = ?', [$this->user['id']]);
            
            $this->setFlashMessage('Perfil actualizado correctamente', 'success');
            $this->redirect('/admin/profile');
            
        } catch (Exception $e) {
            error_log("Error actualizando perfil: " . $e->getMessage());
            $this->setFlashMessage('Error al actualizar el perfil', 'error');
            $this->redirect('/admin/profile');
        }
    }
}
