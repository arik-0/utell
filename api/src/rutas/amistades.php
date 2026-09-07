<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/AmistadService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// POST Enviar solicitud de amistad
$app->post('/amistad/solicitar', function(Request $request, Response $response) {
    $idSolicitante = $request->getParam('idSolicitante');
    $idReceptor    = $request->getParam('idReceptor');
    if (!$idSolicitante || !$idReceptor) {
        return jsonResponse($response, ['error' => ['text' => 'idSolicitante y idReceptor son requeridos.']], 400);
    }
    try {
        $result = AmistadService::getInstance()->enviarSolicitud((int)$idSolicitante, (int)$idReceptor);
        return jsonResponse($response, $result);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// POST Responder solicitud (aceptar / rechazar)
$app->post('/amistad/{id:[0-9]+}/responder', function(Request $request, Response $response, array $args) {
    $idAmistad = (int)$args['id'];
    $accion    = $request->getParam('accion');
    if (!$accion) {
        return jsonResponse($response, ['error' => ['text' => 'El campo accion es requerido (aceptar o rechazar).']], 400);
    }
    try {
        $result = AmistadService::getInstance()->responderSolicitud($idAmistad, $accion);
        return jsonResponse($response, $result);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// DELETE Eliminar amistad
$app->delete('/amistad/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $idAmistad = (int)$args['id'];
    try {
        $result = AmistadService::getInstance()->eliminarAmistad($idAmistad);
        return jsonResponse($response, $result);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// GET Amigos de un usuario
$app->get('/usuarios/{id:[0-9]+}/amigos', function(Request $request, Response $response, array $args) {
    $idUsuario = (int)$args['id'];
    try {
        return jsonResponse($response, AmistadService::getInstance()->obtenerAmigos($idUsuario));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// GET Solicitudes pendientes
$app->get('/usuarios/{id:[0-9]+}/solicitudes', function(Request $request, Response $response, array $args) {
    $idUsuario = (int)$args['id'];
    try {
        return jsonResponse($response, AmistadService::getInstance()->obtenerSolicitudesPendientes($idUsuario));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// GET Estado de relacion entre dos usuarios
$app->get('/usuarios/{id:[0-9]+}/relacion/{idOtro:[0-9]+}', function(Request $request, Response $response, array $args) {
    $idUsuario = (int)$args['id'];
    $idOtro    = (int)$args['idOtro'];
    try {
        return jsonResponse($response, AmistadService::getInstance()->obtenerEstadoRelacion($idUsuario, $idOtro));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());
