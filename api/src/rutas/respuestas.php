<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/RespuestaService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// GET Respuestas de una publicación (público)
$obtenerRespuestasHandler = function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        $respuestas = RespuestaService::getInstance()->obtenerRespuestasPorPublicacion($id);
        return jsonResponse($response, $respuestas ?: []);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al cargar las respuestas.', 500, $e);
    }
};
$app->get('/publicacion/{id:[0-9]+}/respuestas', $obtenerRespuestasHandler);
$app->get('/publicaciones/{id:[0-9]+}/respuestas', $obtenerRespuestasHandler);

// POST Crear respuesta (requiere JWT)
$crearRespuestaHandler = function(Request $request, Response $response) {
    $idUsuario     = getAuthUserId($request);
    $idPublicacion = (int)($request->getParam('idPublicacion') ?? 0);
    $texto         = trim((string)($request->getParam('texto') ?? ''));

    if ($idPublicacion <= 0 || $idUsuario <= 0 || $texto === '') {
        return errorResponse($response, 'idPublicacion y texto son requeridos.', 400);
    }

    try {
        $guardado = RespuestaService::getInstance()->crearRespuesta([
            'idPublicacion' => $idPublicacion,
            'idUsuario'     => $idUsuario,
            'texto'         => $texto,
        ]);
        if ($guardado) {
            return jsonResponse($response, ['mensaje' => 'Respuesta guardada.'], 201);
        }
        return errorResponse($response, 'No se pudo guardar la respuesta.', 500);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al guardar la respuesta.', 500, $e);
    }
};
$app->post('/respuesta/nuevo', $crearRespuestaHandler)->add(new AuthMiddleware());
$app->post('/respuestas/nuevo', $crearRespuestaHandler)->add(new AuthMiddleware());

// PUT Modificar respuesta (requiere JWT, autor o staff)
$actualizarRespuestaHandler = function(Request $request, Response $response, array $args) {
    $idRespuesta = (int)$args['id'];
    $idActor     = getAuthUserId($request);
    $esStaff     = isAuthAdminOrMod($request);
    $texto       = trim((string)($request->getParam('texto') ?? ''));

    if ($idRespuesta <= 0 || $texto === '') {
        return errorResponse($response, 'ID de respuesta y texto son obligatorios.', 400);
    }

    try {
        $ok = RespuestaService::getInstance()->actualizarRespuesta($idRespuesta, $idActor, $esStaff, $texto);
        return jsonResponse($response, ['success' => $ok, 'mensaje' => 'Respuesta actualizada.']);
    } catch (InvalidArgumentException $e) {
        return errorResponse($response, $e->getMessage(), 404);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 403);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al actualizar la respuesta.', 500, $e);
    }
};
$app->put('/respuesta/{id:[0-9]+}', $actualizarRespuestaHandler)->add(new AuthMiddleware());
$app->put('/respuestas/{id:[0-9]+}', $actualizarRespuestaHandler)->add(new AuthMiddleware());

// DELETE Eliminar respuesta (requiere JWT, autor o staff)
$eliminarRespuestaHandler = function(Request $request, Response $response, array $args) {
    $idRespuesta = (int)$args['id'];
    $idActor     = getAuthUserId($request);
    $esStaff     = isAuthAdminOrMod($request);

    try {
        $ok = RespuestaService::getInstance()->eliminarRespuesta($idRespuesta, $idActor, $esStaff);
        return jsonResponse($response, ['success' => $ok, 'mensaje' => 'Respuesta eliminada.']);
    } catch (InvalidArgumentException $e) {
        return errorResponse($response, $e->getMessage(), 404);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 403);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al eliminar la respuesta.', 500, $e);
    }
};
$app->delete('/respuesta/{id:[0-9]+}', $eliminarRespuestaHandler)->add(new AuthMiddleware());
$app->delete('/respuestas/{id:[0-9]+}', $eliminarRespuestaHandler)->add(new AuthMiddleware());
