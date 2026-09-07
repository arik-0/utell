<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/MensajeService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// GET Conversaciones del usuario
$app->get('/mensajes/conversaciones', function(Request $request, Response $response) {
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    if ($idUsuario === 0) return jsonResponse($response, []);
    try {
        return jsonResponse($response, MensajeService::getInstance()->obtenerConversaciones($idUsuario));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// GET Historial de chat
$app->get('/mensajes/chat/{idContacto:[0-9]+}', function(Request $request, Response $response, array $args) {
    $idContacto = (int)$args['idContacto'];
    $idUsuario  = (int)($request->getQueryParam('idUsuario') ?? 0);
    if ($idUsuario === 0 || $idContacto === 0) return jsonResponse($response, []);
    try {
        return jsonResponse($response, MensajeService::getInstance()->obtenerChat($idUsuario, $idContacto));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// GET Conteo de mensajes no leidos
$app->get('/mensajes/no-leidos', function(Request $request, Response $response) {
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    if ($idUsuario === 0) return jsonResponse($response, ['totalNoLeidos' => 0]);
    try {
        $total = MensajeService::getInstance()->obtenerTotalNoLeidos($idUsuario);
        return jsonResponse($response, ['totalNoLeidos' => $total]);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// POST Enviar mensaje directo
$app->post('/mensajes/enviar', function(Request $request, Response $response) {
    $idEmisor   = (int)($request->getParam('idEmisor') ?? 0);
    $idReceptor = (int)($request->getParam('idReceptor') ?? 0);
    $texto      = (string)($request->getParam('texto') ?? '');
    if ($idEmisor === 0 || $idReceptor === 0 || empty(trim($texto))) {
        return jsonResponse($response, ['error' => ['text' => 'Parametros requeridos: idEmisor, idReceptor, texto.']], 400);
    }
    try {
        return jsonResponse($response, MensajeService::getInstance()->enviar($idEmisor, $idReceptor, $texto));
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());
