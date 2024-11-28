<?php

namespace App\Core;

class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "Vista no encontrada: $view";
        }
    }
}
