<?php
namespace App\Core;

use App\Core\View;

class Router
{
    private $routes = [
        'GET' => [],
        'POST' => [],
    ];

    private $groupPrefix = '';  // Prefijo de la URI para un grupo de rutas

    // Método para definir un grupo de rutas con un prefijo
    public function group($prefix, $callback)
    {
        // Guardamos el prefijo de las rutas del grupo
        $this->groupPrefix = $prefix;

        // Llamamos al callback donde se definen las rutas
        $callback($this);

        // Restauramos el prefijo después de la llamada
        $this->groupPrefix = '';
    }

    // Método para definir una ruta (independientemente de si está en un grupo o no)
    public function router($method, $uri, $controller, $action)
    {
        // Si hay un prefijo de grupo, lo agregamos a la URI
        if ($this->groupPrefix) {
            $uri = $this->groupPrefix . $uri;
        }

        // Guardamos la ruta con los parámetros dinámicos
        $this->routes[strtoupper($method)][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    // Método para manejar la solicitud
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        /* $uri = rtrim($uri, '/'); */

        // Verificamos si la URI está vacía y redirigimos a la ruta predeterminada
        if ($uri == '') {
            $uri = '/home';
        }

        // Buscamos si existe una ruta con parámetros dinámicos
        foreach ($this->routes[$method] as $routeUri => $route) {
            // Convertir las rutas a expresiones regulares para capturar parámetros
            $pattern = preg_replace('/{([^}]+)}/', '(?P<$1>[^/]+)', $routeUri);  // Captura el parámetro
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                // Si la ruta coincide, obtenemos los parámetros
                $controllerName = 'App\\Controllers\\' . ucfirst($route['controller']) . 'Controller';
                $action = $route['action'];

                // Verificamos si el controlador y la acción existen
                if (class_exists($controllerName) && method_exists($controllerName, $action)) {
                    // Llamar al controlador con los parámetros capturados
                    $controller = new $controllerName();
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Filtra solo las claves (parámetros)
                    call_user_func_array([$controller, $action], $params);
                } else {
                    $this->handleError();
                }
                return;  // Ya se ha encontrado y ejecutado la ruta, salimos
            }
        }

        // Si no se encontró ninguna ruta, manejamos el error
        $this->handleError();
    }

    // Manejar errores si no se encuentra la ruta
    private function handleError()
    {
        View::render('errors.error_404');
        echo "Error: Ruta no encontrada.";
    }
}
