<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/InstitucionService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// Todas las rutas de instituciones son publicas (listados de referencia)

$app->get('/universidades/con-experiencias', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerUniversidadesConExperiencias() ?: []);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/universidades/{id:[0-9]+}/muro', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $muro = InstitucionService::getInstance()->obtenerMuroUniversidad($id, $idUsuario);
        if ($muro) return jsonResponse($response, $muro);
        return jsonResponse($response, ['error' => ['text' => 'No se encontro la universidad especificada.']], 404);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/universidades', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerUniversidades() ?: []);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/carreras', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCarreras());
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/sedes', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedes());
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/universidades/{id:[0-9]+}/sedes', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedesPorUniversidad($id));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/sedes/{id:[0-9]+}/carreras', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCarrerasPorSede($id));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/carreras/{id:[0-9]+}/sedes', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedesPorCarrera($id));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// Crear oferta academica (requiere JWT)
$app->post('/oferta-academica', function(Request $request, Response $response) {
    $idCarrera = (int)$request->getParam('idCarrera');
    $idSede    = (int)$request->getParam('idSede');
    $anio      = $request->getParam('anioImplementacion') ? (int)$request->getParam('anioImplementacion') : null;
    try {
        $ok = InstitucionService::getInstance()->crearOfertaAcademica($idCarrera, $idSede, $anio);
        return jsonResponse($response, $ok ? ['mensaje' => 'Oferta academica creada.'] : ['error' => ['text' => 'Error al crear oferta academica.']], $ok ? 201 : 500);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// Eliminar oferta academica (requiere JWT)
$app->delete('/oferta-academica/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarOfertaAcademica($id);
        return jsonResponse($response, $ok ? ['mensaje' => 'Oferta academica eliminada.'] : ['error' => ['text' => 'No se encontro la oferta.']], $ok ? 200 : 404);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

$app->get('/paises', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerPaises());
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/provincias', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerProvincias());
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

$app->get('/ciudades', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCiudades());
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});
