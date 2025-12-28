<?php
/**
 * Vista: Configuración de Branding
 * views/admin/companies/branding.php
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branding - Sistema HERCO</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .color-preview {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            cursor: pointer;
        }
        .theme-card {
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .theme-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .theme-card.active {
            border-color: #007bff;
        }
        .color-palette {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .color-swatch {
            width: 50px;
            height: 50px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/dashboard">
            <i class="fas fa-building me-2"></i>Sistema HERCO
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="/admin/dashboard">Dashboard</a>
            <a class="nav-link" href="/admin/companies/profile">Mi Empresa</a>
            <a class="nav-link" href="/logout">Salir</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/companies/profile">Mi Empresa</a></li>
            <li class="breadcrumb-item active">Branding</li>
        </ol>
    </nav>

    <!-- Mensajes Flash -->
    <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $message): ?>
            <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show">
                <?= htmlspecialchars($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h2">
                <i class="fas fa-palette me-2"></i>Personalización de Marca
            </h1>
            <p class="text-muted">Personalice los colores y apariencia visual del sistema</p>
        </div>
    </div>

    <form action="/admin/companies/branding" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        
        <div class="row">
            <!-- Esquemas de Color Predefinidos -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-swatchbook me-2"></i>Esquemas de Color Predefinidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($color_schemes as $key => $scheme): ?>
                                <div class="col-md-3">
                                    <div class="theme-card card h-100 <?= ($branding['survey_theme'] ?? 'default') === $key ? 'active' : '' ?>"
                                         onclick="selectTheme('<?= $key ?>', <?= htmlspecialchars(json_encode($scheme)) ?>)">
                                        <div class="card-body text-center">
                                            <h6 class="mb-3"><?= htmlspecialchars($scheme['name']) ?></h6>
                                            <div class="color-palette justify-content-center">
                                                <div class="color-swatch" style="background-color: <?= $scheme['primary'] ?>"></div>
                                                <div class="color-swatch" style="background-color: <?= $scheme['secondary'] ?>"></div>
                                                <div class="color-swatch" style="background-color: <?= $scheme['accent'] ?>"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colores Personalizados -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-droplet me-2"></i>Colores Personalizados
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-4">
                            <label class="form-label">Color Primario</label>
                            <div class="input-group">
                                <input type="color" 
                                       name="primary_color" 
                                       id="primaryColor"
                                       class="form-control form-control-color" 
                                       value="<?= htmlspecialchars($branding['primary_color'] ?? '#007bff') ?>">
                                <input type="text" 
                                       class="form-control" 
                                       id="primaryColorText"
                                       value="<?= htmlspecialchars($branding['primary_color'] ?? '#007bff') ?>"
                                       readonly>
                            </div>
                            <small class="text-muted">Color principal del sistema (botones, enlaces, etc.)</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Color Secundario</label>
                            <div class="input-group">
                                <input type="color" 
                                       name="secondary_color" 
                                       id="secondaryColor"
                                       class="form-control form-control-color" 
                                       value="<?= htmlspecialchars($branding['secondary_color'] ?? '#6c757d') ?>">
                                <input type="text" 
                                       class="form-control" 
                                       id="secondaryColorText"
                                       value="<?= htmlspecialchars($branding['secondary_color'] ?? '#6c757d') ?>"
                                       readonly>
                            </div>
                            <small class="text-muted">Color secundario para elementos de apoyo</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Color de Acento</label>
                            <div class="input-group">
                                <input type="color" 
                                       name="accent_color" 
                                       id="accentColor"
                                       class="form-control form-control-color" 
                                       value="<?= htmlspecialchars($branding['accent_color'] ?? '#28a745') ?>">
                                <input type="text" 
                                       class="form-control" 
                                       id="accentColorText"
                                       value="<?= htmlspecialchars($branding['accent_color'] ?? '#28a745') ?>"
                                       readonly>
                            </div>
                            <small class="text-muted">Color de acento para destacar elementos</small>
                        </div>

                        <input type="hidden" name="survey_theme" id="surveyTheme" value="<?= htmlspecialchars($branding['survey_theme'] ?? 'default') ?>">

                    </div>
                </div>
            </div>

            <!-- Vista Previa -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-eye me-2"></i>Vista Previa
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="previewArea" class="border rounded p-4">
                            <h5 id="previewTitle" class="mb-3">Sistema de Encuestas HERCO</h5>
                            <button type="button" id="previewButton" class="btn mb-2">Botón Primario</button>
                            <button type="button" id="previewButtonSecondary" class="btn mb-2">Botón Secundario</button>
                            <button type="button" id="previewButtonAccent" class="btn mb-2">Botón Acento</button>
                            
                            <div class="mt-3">
                                <div id="previewProgress" class="progress mb-2" style="height: 25px;">
                                    <div class="progress-bar" role="progressbar" style="width: 75%">75%</div>
                                </div>
                                
                                <div id="previewAlert" class="alert" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Este es un mensaje de ejemplo
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CSS Personalizado -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-code me-2"></i>CSS Personalizado (Avanzado)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">CSS Adicional</label>
                            <textarea name="custom_css" 
                                      class="form-control font-monospace" 
                                      rows="8"
                                      placeholder="/* Agregar estilos CSS personalizados aquí */&#10;.mi-clase {&#10;    color: #333;&#10;}"><?= htmlspecialchars($branding['custom_css'] ?? '') ?></textarea>
                            <small class="text-muted">Código CSS que se aplicará en todo el sistema</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie de Página de Emails -->
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">
                            <i class="fas fa-envelope me-2"></i>Pie de Página de Emails
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Texto del Pie de Página</label>
                            <textarea name="email_footer" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Texto que aparecerá al final de todos los emails enviados por el sistema"><?= htmlspecialchars($branding['email_footer'] ?? '') ?></textarea>
                            <small class="text-muted">Este texto aparecerá en todos los correos electrónicos del sistema</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-12">
                <div class="text-end">
                    <a href="/admin/companies/profile" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="resetColors()">
                        <i class="fas fa-undo me-1"></i> Restablecer
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sincronizar color pickers con text inputs
document.getElementById('primaryColor').addEventListener('input', function(e) {
    document.getElementById('primaryColorText').value = e.target.value;
    updatePreview();
});

document.getElementById('secondaryColor').addEventListener('input', function(e) {
    document.getElementById('secondaryColorText').value = e.target.value;
    updatePreview();
});

document.getElementById('accentColor').addEventListener('input', function(e) {
    document.getElementById('accentColorText').value = e.target.value;
    updatePreview();
});

// Actualizar vista previa
function updatePreview() {
    const primary = document.getElementById('primaryColor').value;
    const secondary = document.getElementById('secondaryColor').value;
    const accent = document.getElementById('accentColor').value;
    
    document.getElementById('previewTitle').style.color = primary;
    document.getElementById('previewButton').style.backgroundColor = primary;
    document.getElementById('previewButton').style.borderColor = primary;
    document.getElementById('previewButton').style.color = '#fff';
    
    document.getElementById('previewButtonSecondary').style.backgroundColor = secondary;
    document.getElementById('previewButtonSecondary').style.borderColor = secondary;
    document.getElementById('previewButtonSecondary').style.color = '#fff';
    
    document.getElementById('previewButtonAccent').style.backgroundColor = accent;
    document.getElementById('previewButtonAccent').style.borderColor = accent;
    document.getElementById('previewButtonAccent').style.color = '#fff';
    
    document.querySelector('#previewProgress .progress-bar').style.backgroundColor = primary;
    document.getElementById('previewAlert').style.backgroundColor = primary + '20';
    document.getElementById('previewAlert').style.borderColor = primary;
    document.getElementById('previewAlert').style.color = primary;
}

// Seleccionar tema predefinido
function selectTheme(themeKey, scheme) {
    document.getElementById('primaryColor').value = scheme.primary;
    document.getElementById('primaryColorText').value = scheme.primary;
    
    document.getElementById('secondaryColor').value = scheme.secondary;
    document.getElementById('secondaryColorText').value = scheme.secondary;
    
    document.getElementById('accentColor').value = scheme.accent;
    document.getElementById('accentColorText').value = scheme.accent;
    
    document.getElementById('surveyTheme').value = themeKey;
    
    // Actualizar UI
    document.querySelectorAll('.theme-card').forEach(card => {
        card.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
    
    updatePreview();
}

// Restablecer colores
function resetColors() {
    if (confirm('¿Está seguro de restablecer los colores al tema por defecto?')) {
        selectTheme('default', {
            primary: '#007bff',
            secondary: '#6c757d',
            accent: '#28a745'
        });
    }
}

// Inicializar vista previa
updatePreview();
</script>

</body>
</html>