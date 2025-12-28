<?php
/**
 * Vista: Crear Nueva Empresa (Solo Super Admin)
 * views/admin/companies/create.php
 */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Empresa - Sistema HERCO</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <a class="nav-link" href="/admin/companies">Empresas</a>
            <a class="nav-link" href="/logout">Salir</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/companies">Empresas</a></li>
            <li class="breadcrumb-item active">Nueva Empresa</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h2">
                <i class="fas fa-plus-circle me-2"></i>Crear Nueva Empresa
            </h1>
            <p class="text-muted">Complete la información para registrar una nueva empresa en el sistema</p>
        </div>
    </div>

    <!-- Formulario -->
    <form action="/admin/companies/store" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        
        <div class="row">
            <!-- Información Básica -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información Básica
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Empresa *</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   required
                                   placeholder="Ingrese el nombre de la empresa">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Breve descripción de la empresa"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Industria</label>
                                <select name="industry" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <?php if (!empty($industries)): ?>
                                        <?php foreach ($industries as $industry): ?>
                                            <option value="<?= htmlspecialchars($industry['industry']) ?>">
                                                <?= htmlspecialchars($industry['industry']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <option value="Tecnología">Tecnología</option>
                                    <option value="Manufactura">Manufactura</option>
                                    <option value="Servicios">Servicios</option>
                                    <option value="Retail">Retail</option>
                                    <option value="Salud">Salud</option>
                                    <option value="Educación">Educación</option>
                                    <option value="Finanzas">Finanzas</option>
                                    <option value="Construcción">Construcción</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Número de Empleados</label>
                                <input type="number" 
                                       name="employee_count" 
                                       class="form-control" 
                                       min="0"
                                       placeholder="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo de la Empresa</label>
                            <input type="file" 
                                   name="logo" 
                                   class="form-control" 
                                   accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted">Formatos aceptados: JPG, PNG, GIF (Máximo 2MB)</small>
                        </div>

                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-address-card me-2"></i>Información de Contacto
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email de Contacto</label>
                                <input type="email" 
                                       name="contact_email" 
                                       class="form-control"
                                       placeholder="contacto@empresa.com">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" 
                                       name="contact_phone" 
                                       class="form-control"
                                       placeholder="+504 1234-5678">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <textarea name="address" 
                                      class="form-control" 
                                      rows="2"
                                      placeholder="Dirección física de la empresa"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sitio Web</label>
                            <input type="url" 
                                   name="website" 
                                   class="form-control"
                                   placeholder="https://www.empresa.com">
                        </div>

                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="text-end mb-4">
                    <a href="/admin/companies" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Crear Empresa
                    </button>
                </div>
            </div>

            <!-- Panel Lateral de Ayuda -->
            <div class="col-md-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Información
                        </h5>
                    </div>
                    <div class="card-body">
<h6><i class="fas fa-check-circle text-success me-2"></i>Campos Requeridos</h6>
<p class="small">Solo el <strong>nombre de la empresa</strong> es obligatorio. Los demás campos son opcionales.</p>
<hr>
                    
                    <h6><i class="fas fa-building text-primary me-2"></i>Nombre de la Empresa</h6>
                    <p class="small">Debe ser único en el sistema. Este será el nombre principal de identificación.</p>
                    
                    <hr>
                    
                    <h6><i class="fas fa-image text-warning me-2"></i>Logo</h6>
                    <p class="small">Se recomienda usar un logo cuadrado de al menos 200x200 píxeles. El sistema lo redimensionará automáticamente.</p>
                    
                    <hr>
                    
                    <h6><i class="fas fa-industry text-info me-2"></i>Industria</h6>
                    <p class="small">Seleccione la industria más apropiada. Esto ayuda a categorizar y generar reportes comparativos.</p>
                    
                    <hr>
                    
                    <h6><i class="fas fa-shield-alt text-success me-2"></i>Siguiente Paso</h6>
                    <p class="small mb-0">Después de crear la empresa, podrá:</p>
                    <ul class="small">
                        <li>Agregar usuarios administradores</li>
                        <li>Configurar departamentos</li>
                        <li>Personalizar branding</li>
                        <li>Crear encuestas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
