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
