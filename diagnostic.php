<?php
/**
 * DIAGNÓSTICO COMPLETO DEL SISTEMA
 * ==================================
 * Identifica exactamente dónde está el problema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database_config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnóstico Completo - HERCO</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f7fafc;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #2d3748; border-bottom: 3px solid #4299e1; padding-bottom: 10px; }
        h2 { color: #4a5568; margin-top: 30px; }
        .success { background: #d1fae5; padding: 15px; border-left: 4px solid #10b981; margin: 10px 0; }
        .error { background: #fee2e2; padding: 15px; border-left: 4px solid #ef4444; margin: 10px 0; }
        .warning { background: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 10px 0; }
        .info { background: #dbeafe; padding: 15px; border-left: 4px solid #3b82f6; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #4a5568; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        tr:hover { background: #f7fafc; }
        pre { 
            background: #1a202c; 
            color: #48bb78; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            font-size: 13px;
        }
        .badge { 
            display: inline-block; 
            padding: 4px 12px; 
            border-radius: 12px; 
            font-size: 12px; 
            font-weight: bold;
        }
        .badge-success { background: #10b981; color: white; }
        .badge-error { background: #ef4444; color: white; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 DIAGNÓSTICO COMPLETO DEL SISTEMA HERCO</h1>";
echo "<p style='color: #718096;'>Fecha: " . date('Y-m-d H:i:s') . "</p>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>";
    echo "✅ <strong>Conexión a base de datos exitosa</strong><br>";
    echo "Base de datos: <strong>" . DB_NAME . "</strong>";
    echo "</div>";
    
    // ===================================================================
    // 1. VERIFICAR ESTRUCTURA DE TABLA USERS
    // ===================================================================
    echo "<h2>📊 1. Estructura de Tabla USERS</h2>";
    
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $hasUpdatedAt = false;
    $hasUpdatedBy = false;
    $hasCreatedAt = false;
    $hasCreatedBy = false;
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>";
        
        if ($col['Field'] === 'updated_at') $hasUpdatedAt = true;
        if ($col['Field'] === 'updated_by') $hasUpdatedBy = true;
        if ($col['Field'] === 'created_at') $hasCreatedAt = true;
        if ($col['Field'] === 'created_by') $hasCreatedBy = true;
    }
    echo "</table>";
    
    // ===================================================================
    // 2. ESTADO DE COLUMNAS DE AUDITORÍA
    // ===================================================================
    echo "<h2>🎯 2. Estado de Columnas de Auditoría</h2>";
    
    $auditColumns = [
        'created_at' => $hasCreatedAt,
        'created_by' => $hasCreatedBy,
        'updated_at' => $hasUpdatedAt,
        'updated_by' => $hasUpdatedBy
    ];
    
    foreach ($auditColumns as $col => $exists) {
        if ($exists) {
            echo "<div class='success'>";
            echo "<span class='badge badge-success'>✅ EXISTE</span> ";
            echo "<strong>$col</strong>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<span class='badge badge-error'>❌ NO EXISTE</span> ";
            echo "<strong>$col</strong> - ⚠️ DEBE AGREGARSE";
            echo "</div>";
        }
    }
    
    // ===================================================================
    // 3. PROBAR UPDATE REAL
    // ===================================================================
    echo "<h2>🧪 3. Prueba de UPDATE (Usuario ID: 11)</h2>";
    
    try {
        // Verificar que el usuario existe
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = 11");
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<div class='info'>";
            echo "Usuario encontrado: <strong>" . htmlspecialchars($user['name']) . "</strong> ";
            echo "(" . htmlspecialchars($user['email']) . ")";
            echo "</div>";
            
            // Intentar hacer un UPDATE de prueba (sin modificar datos reales)
            echo "<p>Probando UPDATE con columnas de auditoría...</p>";
            
            $testData = [
                'name' => $user['name'], // Sin cambios reales
                'email' => $user['email']
            ];
            
            // Si existe updated_at, agregarla
            if ($hasUpdatedAt) {
                $testData['updated_at'] = date('Y-m-d H:i:s');
            }
            
            // Si existe updated_by, agregarla
            if ($hasUpdatedBy) {
                $testData['updated_by'] = 1;
            }
            
            $columns = array_keys($testData);
            $setClause = implode(' = ?, ', $columns) . ' = ?';
            $sql = "UPDATE users SET {$setClause} WHERE id = ?";
            
            echo "<pre>SQL: " . $sql . "</pre>";
            
            $stmt = $pdo->prepare($sql);
            $values = array_values($testData);
            $values[] = 11;
            
            $result = $stmt->execute($values);
            
            if ($result) {
                echo "<div class='success'>";
                echo "✅ <strong>UPDATE ejecutado exitosamente</strong>";
                echo "</div>";
            }
            
        } else {
            echo "<div class='warning'>";
            echo "⚠️ Usuario ID 11 no encontrado en la base de datos";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "❌ <strong>Error al ejecutar UPDATE:</strong><br>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
    // ===================================================================
    // 4. VERIFICAR CONFIGURACIÓN DEL MODEL
    // ===================================================================
    echo "<h2>⚙️ 4. Configuración del Model.php</h2>";
    
    if (file_exists('core/Model.php')) {
        $modelContent = file_get_contents('core/Model.php');
        
        echo "<table>";
        echo "<tr><th>Configuración</th><th>Estado</th></tr>";
        
        // Verificar timestamps
        if (preg_match('/protected\s+\$timestamps\s*=\s*(true|false)/', $modelContent, $matches)) {
            $timestamps = $matches[1] === 'true';
            echo "<tr>";
            echo "<td>Timestamps habilitados</td>";
            echo "<td>" . ($timestamps ? 
                "<span class='badge badge-success'>✅ TRUE</span>" : 
                "<span class='badge badge-error'>❌ FALSE</span>") . "</td>";
            echo "</tr>";
        }
        
        // Verificar método addTimestamps
        if (strpos($modelContent, 'function addTimestamps') !== false) {
            echo "<tr>";
            echo "<td>Método addTimestamps</td>";
            echo "<td><span class='badge badge-success'>✅ EXISTE</span></td>";
            echo "</tr>";
            
            // Extraer código del método
            if (preg_match('/function addTimestamps.*?\{(.*?)\n    \}/s', $modelContent, $matches)) {
                echo "<tr>";
                echo "<td colspan='2'><strong>Código del método:</strong>";
                echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";
                echo "</td>";
                echo "</tr>";
            }
        }
        
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ Archivo core/Model.php no encontrado</div>";
    }
    
    // ===================================================================
    // 5. SQL DE CORRECCIÓN
    // ===================================================================
    echo "<h2>🔧 5. SQL de Corrección Necesario</h2>";
    
    $needsFix = !$hasUpdatedAt || !$hasUpdatedBy;
    
    if ($needsFix) {
        echo "<div class='error'>";
        echo "<strong>⚠️ SE REQUIERE EJECUTAR SQL DE CORRECCIÓN</strong>";
        echo "</div>";
        
        echo "<pre>";
        if (!$hasUpdatedAt) {
            echo "-- 1. Agregar updated_at\n";
            echo "ALTER TABLE users \n";
            echo "ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP\n";
            echo "COMMENT 'Fecha y hora de última actualización'\n";
            echo "AFTER created_at;\n\n";
        }
        
        if (!$hasUpdatedBy) {
            echo "-- " . ($hasUpdatedAt ? "1" : "2") . ". Agregar updated_by\n";
            echo "ALTER TABLE users \n";
            echo "ADD COLUMN updated_by INT NULL\n";
            echo "COMMENT 'Usuario que realizó la última actualización'\n";
            echo "AFTER " . ($hasUpdatedAt ? "updated_at" : "created_at") . ";";
        }
        echo "</pre>";
        
    } else {
        echo "<div class='success'>";
        echo "✅ <strong>¡TODAS LAS COLUMNAS EXISTEN!</strong> No se requiere corrección.";
        echo "</div>";
        
        echo "<div class='info'>";
        echo "<strong>Si aún tienes errores, el problema puede ser:</strong>";
        echo "<ul>";
        echo "<li>Cache de PHP/OpCode (reinicia el servidor web)</li>";
        echo "<li>Múltiples bases de datos (verifica que estés en la correcta)</li>";
        echo "<li>Problema en el código del UserController o Model</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    // ===================================================================
    // 6. INFORMACIÓN DEL SISTEMA
    // ===================================================================
    echo "<h2>💻 6. Información del Sistema</h2>";
    
    echo "<table>";
    echo "<tr><td><strong>Versión PHP:</strong></td><td>" . PHP_VERSION . "</td></tr>";
    echo "<tr><td><strong>Base de datos:</strong></td><td>" . DB_NAME . "</td></tr>";
    echo "<tr><td><strong>Host:</strong></td><td>" . DB_HOST . "</td></tr>";
    echo "<tr><td><strong>Usuario:</strong></td><td>" . DB_USER . "</td></tr>";
    
    // Versión MySQL
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "<tr><td><strong>Versión MySQL:</strong></td><td>" . $version . "</td></tr>";
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Error Fatal</h2>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div class='warning'>";
echo "<strong>⚠️ IMPORTANTE:</strong> Elimina este archivo después de usarlo:";
echo "<pre>rm " . basename(__FILE__) . "</pre>";
echo "</div>";

echo "</div></body></html>";
?>
