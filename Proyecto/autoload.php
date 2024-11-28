<?php

// Función de autoload para cargar clases automáticamente
function autoload($class) {
    // Reemplaza el espacio de nombres 'App\Controllers' por la ruta 'app/controllers'
    $class = str_replace('App\\Controllers\\', 'app/controllers/', $class);
    
    // Reemplaza el espacio de nombres 'App\Models' por la ruta 'app/models' (si lo tienes)
    $class = str_replace('App\\Models\\', 'app/models/', $class);

    // Agrega la extensión .php al final del nombre de la clase
    $class .= '.php';

    // Incluye el archivo de la clase
    if (file_exists($class)) {
        require_once $class;
    }
}

// Registra la función de autoload
spl_autoload_register('autoload');