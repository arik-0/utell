<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// ─── CORS Whitelist ────────────────────────────────────────────────────────────
// Allowed origins configurable via ALLOWED_ORIGINS env var (comma-separated).
// Defaults to localhost dev URLs if env var is not set.
$defaultOrigins = 'http://localhost:5173,http://localhost:5174,http://localhost,http://127.0.0.1';
$allowedOriginsRaw = getenv('ALLOWED_ORIGINS') ?: $defaultOrigins;
$allowedOrigins = array_map('trim', explode(',', $allowedOriginsRaw));

$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
// Automatically permit Vercel domains (*.vercel.app) or configured origins
$isVercelDomain = !empty($requestOrigin) && (bool)preg_match('/^https:\/\/[a-zA-Z0-9._-]+\.vercel\.app$/', $requestOrigin);

if (!empty($requestOrigin) && (in_array($requestOrigin, $allowedOrigins, true) || $isVercelDomain)) {
    header("Access-Control-Allow-Origin: {$requestOrigin}");
    header("Access-Control-Allow-Credentials: true");
    header("Vary: Origin");
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
}

header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/db.php';

// Suppress deprecations for Slim 3 on PHP 8.x
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

// Fix Slim 3 basePath stripping bug when root URL starts with script dir (e.g. /publicacion when script is /public/index.php)
if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
    $reqPath = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($scriptDir !== '/' && $scriptDir !== '' && strpos($reqPath, $scriptDir . '/') !== 0 && $reqPath !== $scriptDir) {
        $_SERVER['SCRIPT_NAME'] = '/' . basename($_SERVER['SCRIPT_NAME']);
    }
}

$config = [ 
    "settings" => [ 
        "displayErrorDetails" => false,
        "determineRouteBeforeAppMiddleware" => true,
        "outputBuffering" => false
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
require __DIR__ . '/../src/rutas/upload.php';

$app->run();

