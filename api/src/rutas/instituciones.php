<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/InstitucionService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

$app->get('/universidades/con-experiencias', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerUniversidadesConExperiencias() ?: []);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar las universidades.', 500, $e);
    }
});

$app->get('/universidades/{id:[0-9]+}/muro', function(Request $request, Response $response, array $args) {
    $id        = (int)$args['id'];
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $muro = InstitucionService::getInstance()->obtenerMuroUniversidad($id, $idUsuario);
        if ($muro) {
            return jsonResponse($response, $muro);
        }
        return errorResponse($response, 'No se encontró la universidad especificada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al cargar el muro institucional.', 500, $e);
    }
});

$app->get('/universidades', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerUniversidades() ?: []);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar universidades.', 500, $e);
    }
});

$app->get('/carreras', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCarreras());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar carreras.', 500, $e);
    }
});

$app->get('/sedes', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedes());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar sedes.', 500, $e);
    }
});

$app->get('/universidades/{id:[0-9]+}/sedes', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedesPorUniversidad($id));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar sedes de la universidad.', 500, $e);
    }
});

$app->get('/sedes/{id:[0-9]+}/carreras', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCarrerasPorSede($id));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar carreras de la sede.', 500, $e);
    }
});

$app->get('/carreras/{id:[0-9]+}/sedes', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedesPorCarrera($id));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar sedes donde se dicta la carrera.', 500, $e);
    }
});

// Crear oferta académica (requiere JWT institucional/staff)
$app->post('/oferta-academica', function(Request $request, Response $response) {
    if (!isAuthStaff($request)) {
        return errorResponse($response, 'No autorizado para gestionar la oferta académica.', 403);
    }

    $idCarrera = (int)$request->getParam('idCarrera');
    $idSede    = (int)$request->getParam('idSede');
    $anio      = $request->getParam('anioImplementacion') ? (int)$request->getParam('anioImplementacion') : null;
    if ($idCarrera <= 0 || $idSede <= 0) {
        return errorResponse($response, 'idCarrera e idSede son requeridos.', 400);
    }

    try {
        $ok = InstitucionService::getInstance()->crearOfertaAcademica($idCarrera, $idSede, $anio);
        if ($ok) {
            return jsonResponse($response, ['mensaje' => 'Oferta académica creada.'], 201);
        }
        return errorResponse($response, 'Error al crear la oferta académica.', 500);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al registrar la oferta académica.', 500, $e);
    }
})->add(new AuthMiddleware());

// Eliminar oferta académica (requiere JWT institucional/staff)
$app->delete('/oferta-academica/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthStaff($request)) {
        return errorResponse($response, 'No autorizado para eliminar oferta académica.', 403);
    }

    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarOfertaAcademica($id);
        if ($ok) {
            return jsonResponse($response, ['mensaje' => 'Oferta académica eliminada.']);
        }
        return errorResponse($response, 'No se encontró la oferta académica.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al eliminar la oferta académica.', 500, $e);
    }
})->add(new AuthMiddleware());

$app->get('/paises', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerPaises());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar países.', 500, $e);
    }
});

$app->get('/provincias', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerProvincias());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar provincias.', 500, $e);
    }
});

$app->get('/ciudades', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCiudades());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar ciudades.', 500, $e);
    }
});
