<?php
/**
 * Script de Diagnostico - Problema de Routing
 * Sistema de Encuestas HERCO
 * 
 * Este script ayuda a debuggear por que da Error 404
 * Copia en la raiz del proyecto y accede a:
 * http://tu-sitio.com/diagnose_routing.php
 */

// Configuracion
$baseDir = __DIR__;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnostico de Routing</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .section h2 { margin-top: 0; color: #007bff; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
    </style>
</head>
<body>
<div class='container'>
    <h1>Diagnostico de Routing - Sistema HERCO</h1>
    <hr>
";

// 1. Verificar archivos principales
echo "<div class='section'>
    <h2>1. Verificacion de Archivos Principales</h2>";

$files = [
    'index.php' => 'Punto de entrada principal',
    'Router.php' => 'Sistema de rutas',
    'UserController.php' => 'Controlador de usuarios',
    'Controller.php' => 'Controlador base'
];

foreach ($files as $file => $desc) {
    $path = $baseDir . '/' . $file;
    if (file_exists($path)) {
        echo "<p><span class='ok'>✓ OK</span> $file ($desc) - Encontrado</p>";
    } else {
        echo "<p><span class='error'>✗ ERROR</span> $file ($desc) - NO ENCONTRADO en $path</p>";
    }
}

echo "</div>";

// 2. Verificar contenido de index.php
echo "<div class='section'>
    <h2>2. Verificacion de Rutas en index.php</h2>";

if (file_exists($baseDir . '/index.php')) {
    $indexContent = file_get_contents($baseDir . '/index.php');
    
    // Buscar rutas de usuarios
    $patterns = [
        '/admin/users' => 'GET /admin/users - Listar usuarios',
        '/admin/users/:id/edit' => 'GET /admin/users/:id/edit - Editar usuario',
        'UserController' => 'Referencia a UserController'
    ];
    
    foreach ($patterns as $pattern => $desc) {
        if (strpos($indexContent, $pattern) !== false) {
            echo "<p><span class='ok'>✓ OK</span> $desc</p>";
        } else {
            echo "<p><span class='error'>✗ NO ENCONTRADO</span> $desc - Patron: $pattern</p>";
        }
    }
} else {
    echo "<p><span class='error'>✗ index.php no encontrado</span></p>";
}

echo "</div>";

// 3. Verificar metodos en UserController
echo "<div class='section'>
    <h2>3. Verificacion de Metodos en UserController</h2>";

if (file_exists($baseDir . '/UserController.php')) {
    $ucContent = file_get_contents($baseDir . '/UserController.php');
    
    $methods = [
        'public function edit($id)' => 'edit($id)',
        'public function update($id)' => 'update($id)',
        'public function destroy($id)' => 'destroy($id)',
        'public function delete($id)' => 'delete($id)'
    ];
    
    foreach ($methods as $pattern => $name) {
        if (strpos($ucContent, $pattern) !== false) {
            echo "<p><span class='ok'>✓ OK</span> Metodo $name ENCONTRADO</p>";
        } else {
            echo "<p><span class='error'>✗ ERROR</span> Metodo $name NO ENCONTRADO o INCORRECTO</p>";
            
            // Buscar variantes incorrectas
            if (strpos($ucContent, 'public function edit($userId)') !== false) {
                echo "<p style='margin-left: 20px;'><span class='warning'>! Encontrado</span> edit(\$userId) - INCORRECTO, deberia ser edit(\$id)</p>";
            }
            if (strpos($ucContent, 'public function update($userId)') !== false) {
                echo "<p style='margin-left: 20px;'><span class='warning'>! Encontrado</span> update(\$userId) - INCORRECTO, deberia ser update(\$id)</p>";
            }
        }
    }
} else {
    echo "<p><span class='error'>✗ UserController.php no encontrado</span></p>";
}

echo "</div>";

// 4. Test de URL
echo "<div class='section'>
    <h2>4. Test de URL Solicitada</h2>";

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

echo "<p><span class='info'>Informacion de la solicitud:</span></p>";
echo "<table>";
echo "<tr><th>Parametro</th><th>Valor</th></tr>";
echo "<tr><td>REQUEST_METHOD</td><td>$method</td></tr>";
echo "<tr><td>REQUEST_URI</td><td>$requestUri</td></tr>";
echo "<tr><td>SCRIPT_NAME</td><td>" . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>PHP_SELF</td><td>" . ($_SERVER['PHP_SELF'] ?? 'N/A') . "</td></tr>";
echo "</table>";

echo "<p><span class='info'>URL de prueba para editar usuario ID 9:</span></p>";
echo "<pre>GET /admin/users/9/edit</pre>";

echo "</div>";

// 5. Verificar Router.php
echo "<div class='section'>
    <h2>5. Verificacion de Router.php</h2>";

if (file_exists($baseDir . '/Router.php')) {
    $routerContent = file_get_contents($baseDir . '/Router.php');
    
    $routerChecks = [
        'public function dispatch' => 'Metodo dispatch',
        'public function add' => 'Metodo add',
        'private function convertToRegex' => 'Metodo convertToRegex',
        'private function extractParameters' => 'Metodo extractParameters'
    ];
    
    foreach ($routerChecks as $pattern => $name) {
        if (strpos($routerContent, $pattern) !== false) {
            echo "<p><span class='ok'>✓ OK</span> $name ENCONTRADO</p>";
        } else {
            echo "<p><span class='error'>✗ ERROR</span> $name NO ENCONTRADO</p>";
        }
    }
} else {
    echo "<p><span class='error'>✗ Router.php no encontrado</span></p>";
}

echo "</div>";

// 6. Mostrar logs de error
echo "<div class='section'>
    <h2>6. Ultimas Lineas de logs/php_errors.log</h2>";

$logFile = $baseDir . '/logs/php_errors.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    
    echo "<pre>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<p><span class='warning'>! No se encontro archivo de log: $logFile</span></p>";
}

echo "</div>";

// 7. Sugerencias
echo "<div class='section'>
    <h2>7. Sugerencias</h2>";

$issues = [];

// Verificar archivos
if (!file_exists($baseDir . '/index.php')) {
    $issues[] = "El archivo index.php no existe en la raiz del proyecto";
}

if (!file_exists($baseDir . '/UserController.php')) {
    $issues[] = "El archivo UserController.php no existe";
}

// Verificar que no haya $userId
if (file_exists($baseDir . '/UserController.php')) {
    $ucContent = file_get_contents($baseDir . '/UserController.php');
    if (strpos($ucContent, 'public function edit($userId)') !== false) {
        $issues[] = "El metodo edit() aun tiene el parametro incorrecto: edit(\$userId) en lugar de edit(\$id)";
    }
    if (strpos($ucContent, 'public function update($userId)') !== false) {
        $issues[] = "El metodo update() aun tiene el parametro incorrecto: update(\$userId) en lugar de update(\$id)";
    }
}

if (!empty($issues)) {
    echo "<p><span class='error'>Problemas encontrados:</span></p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li><span class='error'>✗</span> $issue</li>";
    }
    echo "</ul>";
} else {
    echo "<p><span class='ok'>No se encontraron problemas obvios.</span></p>";
    echo "<p><span class='warning'>Si aun tienes Error 404, verifica:</span></p>";
    echo "<ul>";
    echo "<li>El archivo .htaccess - Podria estar bloqueando las rutas</li>";
    echo "<li>La configuracion de Apache - mod_rewrite debe estar activado</li>";
    echo "<li>El Router.php - Verifica que el dispatch() este siendo llamado</li>";
    echo "<li>Anade debug() en index.php para ver que rutas se registran</li>";
    echo "</ul>";
}

echo "</div>";

// 8. Instrucciones de debug
echo "<div class='section'>
    <h2>8. Pasos Adicionales de Debug</h2>";

echo "<p><span class='info'>Agrega esto al inicio de index.php para ver debug:</span></p>";
echo "<pre>";
echo "// DEBUG - Agrega esto DESPUES de iniciar Router
\$router = new Router();

// ... registra tus rutas ...

// AGREGAR ESTO:
error_log('=== DEBUG ROUTING ===');
error_log('REQUEST_METHOD: ' . \$_SERVER['REQUEST_METHOD']);
error_log('REQUEST_URI: ' . \$_SERVER['REQUEST_URI']);
error_log('Rutas registradas: ' . count(\$router->getRoutes()));

foreach (\$router->getRoutes() as \$route) {
    if (strpos(\$route['path'], 'users') !== false) {
        error_log('RUTA: ' . \$route['method'] . ' ' . \$route['path'] . ' -> ' . 
                  \$route['controller'] . '@' . \$route['action']);
    }
}
";
echo "</pre>";

echo "</div>";

echo "
    <hr>
    <p style='text-align: center; color: #666; font-size: 12px;'>
        Generado: " . date('Y-m-d H:i:s') . "<br>
        Directorio: $baseDir
    </p>
</div>
</body>
</html>
";
?>
