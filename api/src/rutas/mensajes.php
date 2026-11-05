<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/MensajeService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// GET Conversaciones del usuario autenticado
$app->get('/mensajes/conversaciones', function(Request $request, Response $response) {
    $idUsuario = getAuthUserId($request);
    if ($idUsuario <= 0) {
        return jsonResponse($response, []);
    }
    try {
        return jsonResponse($response, MensajeService::getInstance()->obtenerConversaciones($idUsuario));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar la bandeja de conversaciones.', 500, $e);
    }
})->add(new AuthMiddleware());

// GET Historial de chat con un contacto
$app->get('/mensajes/chat/{idContacto:[0-9]+}', function(Request $request, Response $response, array $args) {
    $idContacto = (int)$args['idContacto'];
    $idUsuario  = getAuthUserId($request);
    if ($idUsuario <= 0 || $idContacto <= 0) {
        return jsonResponse($response, []);
    }
    try {
        return jsonResponse($response, MensajeService::getInstance()->obtenerChat($idUsuario, $idContacto));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al cargar el historial de conversación.', 500, $e);
    }
})->add(new AuthMiddleware());

// GET Conteo de mensajes no leídos del usuario autenticado
$app->get('/mensajes/no-leidos', function(Request $request, Response $response) {
    $idUsuario = getAuthUserId($request);
    if ($idUsuario <= 0) {
        return jsonResponse($response, ['totalNoLeidos' => 0]);
    }
    try {
        $total = MensajeService::getInstance()->obtenerTotalNoLeidos($idUsuario);
        return jsonResponse($response, ['totalNoLeidos' => $total]);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al obtener mensajes no leídos.', 500, $e);
    }
})->add(new AuthMiddleware());

// POST Enviar mensaje directo (el emisor se toma exclusivamente del JWT)
$app->post('/mensajes/enviar', function(Request $request, Response $response) {
    $idEmisor   = getAuthUserId($request);
    $idReceptor = (int)($request->getParam('idReceptor') ?? 0);
    $texto      = (string)($request->getParam('texto') ?? '');

    if ($idEmisor <= 0 || $idReceptor <= 0 || trim($texto) === '') {
        return errorResponse($response, 'Parámetros requeridos: idReceptor y texto.', 400);
    }

    try {
        return jsonResponse($response, MensajeService::getInstance()->enviar($idEmisor, $idReceptor, $texto));
    } catch (InvalidArgumentException $e) {
        return errorResponse($response, $e->getMessage(), 400);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 403);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al enviar el mensaje.', 500, $e);
    }
})->add(new AuthMiddleware());
