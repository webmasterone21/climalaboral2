<?php
/**
 * Script de Migración Automática
 * Sistema HERCO v2.0 - Refactorización de Seguridad
 * 
 * Este script automatiza la migración del código antiguo al nuevo sistema
 * 
 * Uso: php scripts/migrate_security.php [--dry-run] [--backup]
 * 
 * @package EncuestasHERCO\Scripts
 */

class SecurityMigration
{
    private $rootDir;
    private $dryRun = false;
    private $backup = true;
    private $changes = [];
    private $warnings = [];
    private $errors = [];
    
    public function __construct($rootDir, $options = [])
    {
        $this->rootDir = rtrim($rootDir, '/');
        $this->dryRun = $options['dry-run'] ?? false;
        $this->backup = $options['backup'] ?? true;
        
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║  🔧 MIGRACIÓN AUTOMÁTICA DE SEGURIDAD - HERCO v2.0       ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        if ($this->dryRun) {
            echo "⚠️  Modo DRY-RUN: No se realizarán cambios reales\n\n";
        }
    }
    
    /**
     * Ejecutar migración completa
     */
    public function run()
    {
        echo "📋 Iniciando migración...\n\n";
        
        // Paso 1: Verificar estructura
        $this->step1_verifyStructure();
        
        // Paso 2: Backup de archivos
        if ($this->backup) {
            $this->step2_backupFiles();
        }
        
        // Paso 3: Eliminar archivos duplicados
        $this->step3_removeDuplicates();
        
        // Paso 4: Actualizar index.php
        $this->step4_updateIndex();
        
        // Paso 5: Refactorizar Auth.php
        $this->step5_refactorAuth();
        
        // Paso 6: Refactorizar Controller.php
        $this->step6_refactorController();
        
        // Paso 7: Actualizar vistas
        $this->step7_updateViews();
        
        // Paso 8: Crear/actualizar configuración
        $this->step8_updateConfig();
        
        // Paso 9: Crear directorios necesarios
        $this->step9_createDirectories();
        
        // Mostrar resumen
        $this->showSummary();
    }
    
    // ==========================================
    // PASO 1: VERIFICAR ESTRUCTURA
    // ==========================================
    
    private function step1_verifyStructure()
    {
        echo "1️⃣  Verificando estructura del proyecto...\n";
        
        $requiredDirs = ['core', 'config', 'views', 'controllers'];
        $requiredFiles = ['index.php'];
        
        foreach ($requiredDirs as $dir) {
            $path = $this->rootDir . '/' . $dir;
            if (!is_dir($path)) {
                $this->addError("Directorio no encontrado: {$dir}");
            } else {
                echo "   ✅ {$dir}/\n";
            }
        }
        
        foreach ($requiredFiles as $file) {
            $path = $this->rootDir . '/' . $file;
            if (!file_exists($path)) {
                $this->addError("Archivo no encontrado: {$file}");
            } else {
                echo "   ✅ {$file}\n";
            }
        }
        
        echo "\n";
    }
    
    // ==========================================
    // PASO 2: BACKUP
    // ==========================================
    
    private function step2_backupFiles()
    {
        echo "2️⃣  Creando respaldo de archivos...\n";
        
        $backupDir = $this->rootDir . '/backups/security_migration_' . date('Y-m-d_H-i-s');
        
        if (!$this->dryRun) {
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
        }
        
        $filesToBackup = [
            'core/SecurityMiddleware.php',
            'core/SecurityMiddleware2.php',
            'core/Auth.php',
            'core/Controller.php',
            'core/View.php',
            'index.php',
            'config/app.php'
        ];
        
        foreach ($filesToBackup as $file) {
            $source = $this->rootDir . '/' . $file;
            if (file_exists($source)) {
                $dest = $backupDir . '/' . $file;
                
                if (!$this->dryRun) {
                    $destDir = dirname($dest);
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    copy($source, $dest);
                }
                
                echo "   📦 {$file} → backup/\n";
                $this->addChange("Backup creado: {$file}");
            }
        }
        
        if (!$this->dryRun) {
            echo "   ✅ Backup guardado en: {$backupDir}\n";
        }
        
        echo "\n";
    }
    
    // ==========================================
    // PASO 3: ELIMINAR DUPLICADOS
    // ==========================================
    
    private function step3_removeDuplicates()
    {
        echo "3️⃣  Eliminando archivos duplicados...\n";
        
        $filesToRemove = [
            'core/SecurityMiddleware2.php',
            'core/SecurityMiddleware_old.php',
            'core/SecurityMiddleware.bak'
        ];
        
        foreach ($filesToRemove as $file) {
            $path = $this->rootDir . '/' . $file;
            if (file_exists($path)) {
                if (!$this->dryRun) {
                    unlink($path);
                }
                echo "   🗑️  Eliminado: {$file}\n";
                $this->addChange("Archivo eliminado: {$file}");
            }
        }
        
        echo "\n";
    }
    
    // ==========================================
    // PASO 4: ACTUALIZAR INDEX.PHP
    // ==========================================
    
    private function step4_updateIndex()
    {
        echo "4️⃣  Actualizando index.php...\n";
        
        $indexPath = $this->rootDir . '/index.php';
        
        if (!file_exists($indexPath)) {
            $this->addError("index.php no encontrado");
            return;
        }
        
        $content = file_get_contents($indexPath);
        $modified = false;
        
        // Agregar require de SecurityHelpers si no existe
        if (strpos($content, 'SecurityHelpers.php') === false) {
            $pattern = "/(require_once\s+['\"]core\/SecurityMiddleware\.php['\"];)/";
            $replacement = "$1\nrequire_once 'core/SecurityHelpers.php';";
            $content = preg_replace($pattern, $replacement, $content, 1, $count);
            
            if ($count > 0) {
                $modified = true;
                echo "   ✅ Agregado require de SecurityHelpers.php\n";
                $this->addChange("index.php: Agregado require de SecurityHelpers");
            } else {
                // Si no encontró el patrón, agregar después del primer require_once
                $pattern = "/(require_once[^;]+;)/";
                $replacement = "$1\nrequire_once 'core/SecurityHelpers.php';";
                $content = preg_replace($pattern, $replacement, $content, 1, $count);
                
                if ($count > 0) {
                    $modified = true;
                    echo "   ✅ Agregado require de SecurityHelpers.php\n";
                }
            }
        }
        
        // Agregar inicialización de SecurityMiddleware si no existe
        if (strpos($content, '$security = new SecurityMiddleware()') === false) {
            $pattern = "/(session_start\(\);)/";
            $replacement = "$1\n\n// Ejecutar middleware de seguridad\n\$security = new SecurityMiddleware();\n\$security->process();";
            $content = preg_replace($pattern, $replacement, $content, 1, $count);
            
            if ($count > 0) {
                $modified = true;
                echo "   ✅ Agregada inicialización de SecurityMiddleware\n";
                $this->addChange("index.php: Agregada inicialización de SecurityMiddleware");
            }
        }
        
        if ($modified && !$this->dryRun) {
            file_put_contents($indexPath, $content);
        }
        
        echo "\n";
    }
    
    // ==========================================
    // PASO 5: REFACTORIZAR AUTH.PHP
    // ==========================================
    
    private function step5_refactorAuth()
    {
        echo "5️⃣  Refactorizando Auth.php...\n";
        
        $authPath = $this->rootDir . '/core/Auth.php';
        
        if (!file_exists($authPath)) {
            $this->addWarning("Auth.php no encontrado, se omite");
            return;
        }
        
        $content = file_get_contents($authPath);
        $modified = false;
        
        // Eliminar función generateCSRFToken()
        $pattern = '/function\s+generateCSRFToken\s*\([^)]*\)\s*\{[^}]*\}/s';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '', $content);
            $modified = true;
            echo "   🗑️  Eliminada función generateCSRFToken()\n";
            $this->addChange("Auth.php: Eliminada generateCSRFToken()");
        }
        
        // Eliminar función verifyCSRFToken()
        $pattern = '/function\s+verifyCSRFToken\s*\([^)]*\)\s*\{[^}]*\}/s';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '', $content);
            $modified = true;
            echo "   🗑️  Eliminada función verifyCSRFToken()\n";
            $this->addChange("Auth.php: Eliminada verifyCSRFToken()");
        }
        
        // Reemplazar llamadas a funciones CSRF
        $replacements = [
            'generateCSRFToken()' => 'csrf_token()',
            'verifyCSRFToken(' => 'csrf_verify(',
            '$this->generateCSRFToken()' => 'csrf_token()',
            '$this->verifyCSRFToken(' => 'csrf_verify('
        ];
        
        foreach ($replacements as $old => $new) {
            if (strpos($content, $old) !== false) {
                $content = str_replace($old, $new, $content);
                $modified = true;
                echo "   🔄 Reemplazado: {$old} → {$new}\n";
                $this->addChange("Auth.php: Reemplazado {$old}");
            }
        }
        
        if ($modified && !$this->dryRun) {
            file_put_contents($authPath, $content);
        }
        
        echo "\n";
    }
    
    // ==========================================
    // PASO 6: REFACTORIZAR CONTROLLER.PHP
    // ==========================================
    
    private function step6_refactorController()
    {
        echo "6️⃣  Refactorizando Controller.php...\n";
        
        $controllerPath = $this->rootDir . '/core/Controller.php';
        
        if (!file_exists($controllerPath)) {
            $this->addWarning("Controller.php no encontrado, se omite");
            return;
        }
        
        $content = file_get_contents($controllerPath);
        $modified = false;
        
        // Eliminar métodos CSRF duplicados
        $methodsToRemove = [
            'validateCsrfToken',
            'generateCsrfToken'
        ];
        
        foreach ($methodsToRemove as $method) {
            $pattern = "/protected\s+function\s+{$method}\s*\([^)]*\)\s*\{(?:[^{}]|\{(?:[^{}]|\{[^{}]*\})*\})*\}/s";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '', $content);
                $modified = true;
                echo "   🗑️  Eliminado método {$method}()\n";
                $this->addChange("Controller.php: Eliminado {$method}()");
            }
        }
        
        // Eliminar método sanitizeInput si es duplicado
        if (strpos($content, 'protected function sanitizeInput') !== false) {
            $pattern = "/protected\s+function\s+sanitizeInput\s*\([^)]*\)\s*\{(?:[^{}]|\{(?:[^{}]|\{[^{}]*\})*\})*\}/s";
            $content = preg_replace($pattern, '', $content);
            $modified = true;
            echo "   🗑️  Eliminado método sanitizeInput() duplicado\n";
            $this->addChange("Controller.php: Eliminado sanitizeInput()");
        }
        
        if ($modified && !$this->dryRun) {
            file_put_contents($controllerPath, $content);
        }
        
        echo "\n";
    }
    
    // ==========================================
    // PASO 7: ACTUALIZAR VISTAS
    // ==========================================
    
    private function step7_updateViews()
    {
        echo "7️⃣  Actualizando vistas...\n";
        
        $viewsDir = $this->rootDir . '/views';
        
        if (!is_dir($viewsDir)) {
            $this->addWarning("Directorio views/ no encontrado, se omite");
            return;
        }
        
        $phpFiles = $this->findPHPFiles($viewsDir);
        $updatedCount = 0;
        
        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $originalContent = $content;
            
            // Reemplazar tokens CSRF manualmente escritos
            $patterns = [
                '/<input\s+type=["\']hidden["\']\s+name=["\']_token["\']\s+value=["\']<\?=\s*\$_SESSION\[["\']csrf_token["\']\]\s*\?>["\']>/i',
                '/<input\s+type=["\']hidden["\']\s+name=["\']_token["\']\s+value=["\']<\?php\s+echo\s+\$_SESSION\[["\']csrf_token["\']\];\s*\?>["\']>/i'
            ];
            
            foreach ($patterns as $pattern) {
                $content = preg_replace($pattern, '<?= csrf_field() ?>', $content);
            }
            
            // Reemplazar llamadas a funciones obsoletas
            $content = str_replace('generateCSRFToken()', 'csrf_token()', $content);
            
            if ($content !== $originalContent) {
                if (!$this->dryRun) {
                    file_put_contents($file, $content);
                }
                
                $relativePath = str_replace($this->rootDir . '/', '', $file);
                echo "   🔄 Actualizado: {$relativePath}\n";
                $this->addChange("Vista actualizada: {$relativePath}");
                $updatedCount++;
            }
        }
        
        echo "   ✅ {$updatedCount} vistas actualizadas\n\n";
    }
    
    // ==========================================
    // PASO 8: ACTUALIZAR CONFIGURACIÓN
    // ==========================================
    
    private function step8_updateConfig()
    {
        echo "8️⃣  Actualizando configuración...\n";
        
        $configPath = $this->rootDir . '/config/app.php';
        
        if (!file_exists($configPath)) {
            $this->addWarning("config/app.php no encontrado");
            echo "   ⚠️  Crear manualmente el archivo de configuración\n\n";
            return;
        }
        
        $content = file_get_contents($configPath);
        
        // Verificar si ya tiene configuración de seguridad
        if (strpos($content, "'security'") === false) {
            $securityConfig = <<<'PHP'

    'security' => [
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 60,
            'window' => 300,
            'login_max' => 5,
            'login_window' => 900
        ],
        'csrf' => [
            'enabled' => true,
            'token_name' => '_token',
            'exclude_routes' => ['/api/']
        ],
        'headers' => [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ]
    ],
PHP;
            
            // Insertar antes del último ];
            $content = preg_replace('/\];(\s*)$/', $securityConfig . "\n];$1", $content, 1);
            
            if (!$this->dryRun) {
                file_put_contents($configPath, $content);
            }
            
            echo "   ✅ Agregada configuración de seguridad\n";
            $this->addChange("config/app.php: Agregada configuración de seguridad");
        } else {
            echo "   ℹ️  Configuración de seguridad ya existe\n";
        }
        
        echo "\n";
    }
    
    // ==========================================
    // PASO 9: CREAR DIRECTORIOS
    // ==========================================
    
    private function step9_createDirectories()
    {
        echo "9️⃣  Creando directorios necesarios...\n";
        
        $directories = [
            'logs',
            'logs/security',
            'logs/app',
            'backups'
        ];
        
        foreach ($directories as $dir) {
            $path = $this->rootDir . '/' . $dir;
            
            if (!is_dir($path)) {
                if (!$this->dryRun) {
                    mkdir($path, 0755, true);
                }
                echo "   📁 Creado: {$dir}/\n";
                $this->addChange("Directorio creado: {$dir}");
            } else {
                echo "   ✅ Ya existe: {$dir}/\n";
            }
        }
        
        echo "\n";
    }
    
    // ==========================================
    // UTILIDADES
    // ==========================================
    
    private function findPHPFiles($dir)
    {
        $files = [];
        
        if (!is_dir($dir)) {
            return $files;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    private function addChange($message)
    {
        $this->changes[] = $message;
    }
    
    private function addWarning($message)
    {
        $this->warnings[] = $message;
    }
    
    private function addError($message)
    {
        $this->errors[] = $message;
    }
    
    private function showSummary()
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║  📊 RESUMEN DE LA MIGRACIÓN                              ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        echo "✅ Cambios realizados: " . count($this->changes) . "\n";
        foreach ($this->changes as $change) {
            echo "   • {$change}\n";
        }
        
        if (!empty($this->warnings)) {
            echo "\n⚠️  Advertencias: " . count($this->warnings) . "\n";
            foreach ($this->warnings as $warning) {
                echo "   • {$warning}\n";
            }
        }
        
        if (!empty($this->errors)) {
            echo "\n❌ Errores: " . count($this->errors) . "\n";
            foreach ($this->errors as $error) {
                echo "   • {$error}\n";
            }
        }
        
        echo "\n";
        
        if (empty($this->errors)) {
            echo "🎉 Migración completada exitosamente!\n";
            echo "\n";
            echo "📋 Próximos pasos:\n";
            echo "   1. Revisar los cambios realizados\n";
            echo "   2. Ejecutar los tests: php tests/SecurityTest.php\n";
            echo "   3. Probar el sistema manualmente\n";
            echo "   4. Hacer commit de los cambios\n";
            echo "\n";
        } else {
            echo "⚠️  Migración completada con errores.\n";
            echo "    Por favor, revise los errores arriba y corrija manualmente.\n";
            echo "\n";
        }
    }
}

// ==========================================
// EJECUTAR MIGRACIÓN
// ==========================================

if (php_sapi_name() === 'cli') {
    // Parsear argumentos
    $options = [];
    $args = array_slice($argv, 1);
    
    foreach ($args as $arg) {
        if ($arg === '--dry-run') {
            $options['dry-run'] = true;
        } elseif ($arg === '--no-backup') {
            $options['backup'] = false;
        } elseif ($arg === '--help' || $arg === '-h') {
            echo "Uso: php migrate_security.php [opciones]\n";
            echo "\nOpciones:\n";
            echo "  --dry-run     Simular cambios sin aplicarlos\n";
            echo "  --no-backup   No crear backup de archivos\n";
            echo "  --help, -h    Mostrar esta ayuda\n";
            echo "\n";
            exit(0);
        }
    }
    
    // Determinar directorio raíz (un nivel arriba de scripts/)
    $rootDir = dirname(__DIR__);
    
    // Ejecutar migración
    $migration = new SecurityMigration($rootDir, $options);
    $migration->run();
}
