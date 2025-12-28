<?php
/**
 * Configuración de Base de Datos
 * Generado automáticamente por el instalador HERCO
 * Fecha: <?= date('Y-m-d H:i:s') ?>
 */

return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'u852274492_encuesta01',
    'username' => 'u852274492_userencuestas',
    'password' => 'Prensa2015!',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];
