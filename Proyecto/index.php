<?php

// Desactivar la visualización de errores y warnings en PHP
ini_set('display_errors', 0);  // No mostrar errores
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);  // Reportar todos los errores excepto los Notices y Warnings


// Incluir el autoloader (si no se ha hecho en otro lugar)
require_once 'autoload.php';

// Incluir el archivo que maneja el enrutamiento
require_once 'router.php';
