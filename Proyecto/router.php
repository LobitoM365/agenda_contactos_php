<?php

/* // Define la ruta base de la aplicación

use App\Core\View;

define('APP_PATH', realpath(__DIR__ . '/app'));

$input = file_get_contents('php://input');

// Decodificar el JSON recibido
$_POST = json_decode($input, true); 


// Procesar la URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/'); // Eliminamos las barras finales de la URL

// Verificamos si la URI está vacía y redirigimos a la ruta predeterminada
if ($uri == '') {
    $uri = '/home';
}

// Separamos la URI en partes (controlador y acción)
$parts = explode('/', $uri);

// Depuración para verificar las partes de la URI

// Si la URI está en el formato correcto, el índice 1 debe ser el nombre del controlador
$controllerName = ucfirst($parts[1] ?? 'Home') . 'Controller'; // Controlador por defecto: 'HomeController'

// Acción predeterminada (por defecto 'index')
$action = $parts[2] ?? 'index'; // Acción por defecto: 'index'


// Construir el nombre completo del controlador con el espacio de nombres
$controllerClass = 'App\\Controllers\\' . $controllerName;


// Verifica si el controlador y la acción existen
if (class_exists($controllerClass) && method_exists($controllerClass, $action)) {
    // Instancia el controlador y ejecuta la acción
    $controller = new $controllerClass();
    $controller->$action();
} else {
    // Si no se encuentra el controlador o la acción, muestra un mensaje de error
    View::render('errors.error_404');
    echo "Error: Controlador o acción no encontrados.";
}
 */

use App\Controllers\GeneralController;
use App\Core\Router;

// Define la ruta base de la aplicación
define('APP_PATH', realpath(__DIR__ . '/app'));

$_DATA = GeneralController::getRequestData();

// Crear una instancia del enrutador
$router = new Router();

$router->router('GET', '/', 'Contactos', 'index');

$router->group('/contactos', function ($router) {
    $router->router('POST', '/crear', 'Contactos', 'crear');
    $router->router('GET', '/listar', 'Contactos', 'listar');
    $router->router('POST', '/editar/{id}', 'Contactos', 'editar');
    $router->router('GET', '/buscar/{id}', 'Contactos', 'buscar');
    $router->router('DELETE', '/eliminar/{id}', 'Contactos', 'eliminar');
    $router->router('POST', '/cambiar/imagen/{id}', 'Contactos', 'actualizarImagen');
    $router->router('DELETE', '/eliminar/imagen/{id}', 'Contactos', 'eliminarImagen');
});

// Manejar la solicitud y redirigir al controlador y acción adecuados
$router->handleRequest();
