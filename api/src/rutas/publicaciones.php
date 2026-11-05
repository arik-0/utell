<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/PublicacionService.php';
require_once __DIR__ . '/../Services/InstitucionService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// GET /publicaciones y /parati — feed principal (público)
$feedParatiHandler = function(Request $request, Response $response) {
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $posts = PublicacionService::getInstance()->obtenerParati($idUsuario);
        return jsonResponse($response, $posts ?: []);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al cargar el muro de publicaciones.', 500, $e);
    }
};
$app->get('/publicaciones', $feedParatiHandler);
$app->get('/parati', $feedParatiHandler);

// GET /consultas — listado de consultas activas (público)
$app->get('/consultas', function(Request $request, Response $response) {
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $consultas = PublicacionService::getInstance()->obtenerConsultas($idUsuario);
        return jsonResponse($response, $consultas ?: []);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al cargar las consultas.', 500, $e);
    }
});

// GET /tured — publicaciones de la red del usuario autenticado (requiere JWT)
$app->get('/tured', function(Request $request, Response $response) {
    $idUsuario = getAuthUserId($request);
    if ($idUsuario <= 0) {
        return jsonResponse($response, []);
    }
    try {
        $publicaciones = PublicacionService::getInstance()->obtenerTuRed($idUsuario);
        return jsonResponse($response, $publicaciones ?: []);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al cargar las publicaciones de tu red.', 500, $e);
    }
})->add(new AuthMiddleware());

// GET /publicacion/{id} y /publicaciones/{id} — detalle de una publicación (público)
$detallePublicacionHandler = function(Request $request, Response $response, array $args) {
    $id        = (int)$args['id'];
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $publicacion = PublicacionService::getInstance()->obtenerPorId($id, $idUsuario);
        if ($publicacion) {
            return jsonResponse($response, $publicacion);
        }
        return errorResponse($response, 'No existe la publicación.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al obtener la publicación.', 500, $e);
    }
};
$app->get('/publicacion/{id:[0-9]+}', $detallePublicacionHandler);
$app->get('/publicaciones/{id:[0-9]+}', $detallePublicacionHandler);

// POST /publicacion/nuevo y /publicaciones/nuevo — crear publicación (requiere JWT)
$crearPublicacionHandler = function(Request $request, Response $response) {
    $idUsuario       = getAuthUserId($request);
    $tipoPublicacion = (string)($request->getParam('tipoPublicacion') ?? 'Consulta');
    $titulo          = (string)($request->getParam('titulo') ?? '');
    $texto           = (string)($request->getParam('texto') ?? '');

    if ($idUsuario <= 0 || trim($texto) === '') {
        return errorResponse($response, 'Usuario autenticado y texto son requeridos.', 400);
    }

    try {
        $idsAcademicos = InstitucionService::getInstance()->resolverIdsAcademicos(
            !empty($request->getParam('idUniversidad')) ? (int)$request->getParam('idUniversidad') : null,
            !empty($request->getParam('idSede'))        ? (int)$request->getParam('idSede')        : null,
            !empty($request->getParam('idCarrera'))     ? (int)$request->getParam('idCarrera')     : null,
            $request->getParam('universidad'),
            $request->getParam('sede'),
            $request->getParam('carrera')
        );

        $datos = [
            'idUsuario'                 => $idUsuario,
            'tipoPublicacion'           => $tipoPublicacion,
            'titulo'                    => $titulo,
            'fecha'                     => date('Y-m-d'),
            'hora'                      => date('H:i:s'),
            'idUniversidad'             => $idsAcademicos['idUniversidad'],
            'idSede'                    => $idsAcademicos['idSede'],
            'idCarrera'                 => $idsAcademicos['idCarrera'],
            'texto'                     => $texto,
            'puntuacionClima'           => $request->getParam('puntuacionClima'),
            'puntuacionInfraestructura' => $request->getParam('puntuacionInfraestructura'),
            'puntuacionAcademica'       => $request->getParam('puntuacionAcademica'),
        ];

        PublicacionService::getInstance()->crear($datos);
        return jsonResponse($response, ['mensaje' => 'Nueva publicación guardada.'], 201);
    } catch (InvalidArgumentException $e) {
        return errorResponse($response, $e->getMessage(), 400);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al guardar la publicación.', 500, $e);
    }
};
$app->post('/publicacion/nuevo', $crearPublicacionHandler)->add(new AuthMiddleware());
$app->post('/publicaciones/nuevo', $crearPublicacionHandler)->add(new AuthMiddleware());

// POST /publicacion/{id}/like y /publicaciones/{id}/like — toggle like (requiere JWT)
$toggleLikeHandler = function(Request $request, Response $response, array $args) {
    $idPublicacion = (int)$args['id'];
    $idUsuario     = getAuthUserId($request);
    if ($idUsuario <= 0) {
        return errorResponse($response, 'Autenticación requerida para interactuar.', 401);
    }
    try {
        $resultado = PublicacionService::getInstance()->toggleLike($idPublicacion, $idUsuario);
        return jsonResponse($response, $resultado);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar la valoración.', 500, $e);
    }
};
$app->post('/publicacion/{id:[0-9]+}/like', $toggleLikeHandler)->add(new AuthMiddleware());
$app->post('/publicaciones/{id:[0-9]+}/like', $toggleLikeHandler)->add(new AuthMiddleware());
