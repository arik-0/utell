<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Allow CORS with dynamic origin support or fallback to wildcard in local dev
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: {$origin}");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === "OPTIONS") {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/config/db.php';

// Display errors dynamically based on APP_ENV (false for production)
$isProduction = (getenv('APP_ENV') === 'production');
$config = [ 
    "settings" => [ 
        "displayErrorDetails" => !$isProduction,
        "determineRouteBeforeAppMiddleware" => true
    ]
];

$app = new \Slim\App($config);

// Healthcheck Route
$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write(json_encode([
        "status"  => "online",
        "service" => "U-Tell API Engine",
        "version" => "2.0.0",
        "env"     => getenv('APP_ENV') ?: 'development'
    ]));
    return $response;
});

// RUTAS
require __DIR__ . '/../src/rutas/auth.php';
require __DIR__ . '/../src/rutas/usuarios.php';
require __DIR__ . '/../src/rutas/instituciones.php';
require __DIR__ . '/../src/rutas/publicaciones.php';
require __DIR__ . '/../src/rutas/respuestas.php';
require __DIR__ . '/../src/rutas/amistades.php';
require __DIR__ . '/../src/rutas/mensajes.php';
require __DIR__ . '/../src/rutas/seguridad.php';
require __DIR__ . '/../src/rutas/dashboard.php';

$app->run();

