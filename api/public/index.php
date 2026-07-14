<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

// Agregamos /../ para salir de la carpeta public/ y volver a api/
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/config/db.php';

// Mostrar errores detallados para el entorno de desarrollo
$config = [ 
    "settings" => [ 
        "displayErrorDetails" => true, 
    ]
];

$app = new \Slim\App($config);

// Ruta de prueba
$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("¡La API de UTell está funcionando desde public!");
    return $response;
});

// RUTAS (También agregamos /../ aquí)
require __DIR__ . '/../src/rutas/auth.php';
require __DIR__ . '/../src/rutas/usuarios.php';
require __DIR__ . '/../src/rutas/instituciones.php';
require __DIR__ . '/../src/rutas/publicaciones.php';
require __DIR__ . '/../src/rutas/respuestas.php';

$app->run();