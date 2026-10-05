<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/RespuestaService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// GET Respuestas de una publicacion (publico)
$obtenerRespuestasHandler = function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        $respuestas = RespuestaService::getInstance()->obtenerRespuestasPorPublicacion($id);
        return jsonResponse($response, $respuestas ?: []);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
};
$app->get('/publicacion/{id:[0-9]+}/respuestas', $obtenerRespuestasHandler);
$app->get('/publicaciones/{id:[0-9]+}/respuestas', $obtenerRespuestasHandler);

// POST Crear respuesta (requiere JWT)
$crearRespuestaHandler = function(Request $request, Response $response) {
    $datos = [
        'idPublicacion' => $request->getParam('idPublicacion'),
        'idUsuario'     => $request->getParam('idUsuario'),
        'texto'         => $request->getParam('texto'),
    ];
    if (empty($datos['idPublicacion']) || empty($datos['idUsuario']) || empty(trim($datos['texto'] ?? ''))) {
        return jsonResponse($response, ['error' => ['text' => 'idPublicacion, idUsuario y texto son requeridos.']], 400);
    }
    try {
        $guardado = RespuestaService::getInstance()->crearRespuesta($datos);
        if ($guardado) return jsonResponse($response, ['mensaje' => 'Respuesta guardada.'], 201);
        return jsonResponse($response, ['error' => ['text' => 'Error al guardar respuesta.']], 500);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
};
$app->post('/respuesta/nuevo', $crearRespuestaHandler)->add(new AuthMiddleware());
$app->post('/respuestas/nuevo', $crearRespuestaHandler)->add(new AuthMiddleware());

