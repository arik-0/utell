<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/AmistadService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// POST Enviar solicitud de amistad (el solicitante se obtiene del JWT)
$app->post('/amistad/solicitar', function(Request $request, Response $response) {
    $idSolicitante = getAuthUserId($request);
    $idReceptor    = (int)($request->getParam('idReceptor') ?? 0);

    if ($idSolicitante <= 0 || $idReceptor <= 0) {
        return errorResponse($response, 'idReceptor válido es requerido.', 400);
    }

    try {
        $result = AmistadService::getInstance()->enviarSolicitud($idSolicitante, $idReceptor);
        return jsonResponse($response, $result);
    } catch (InvalidArgumentException $e) {
        return errorResponse($response, $e->getMessage(), 400);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 409);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al enviar la solicitud de amistad.', 500, $e);
    }
})->add(new AuthMiddleware());

// POST Responder solicitud (aceptar / rechazar — solo destinatario)
$app->post('/amistad/{id:[0-9]+}/responder', function(Request $request, Response $response, array $args) {
    $idAmistad = (int)$args['id'];
    $accion    = (string)($request->getParam('accion') ?? '');
    $authId    = getAuthUserId($request);

    if ($accion === '') {
        return errorResponse($response, 'El campo accion es requerido (aceptar o rechazar).', 400);
    }

    try {
        $result = AmistadService::getInstance()->responderSolicitud($idAmistad, $accion, $authId);
        return jsonResponse($response, $result);
    } catch (InvalidArgumentException $e) {
        return errorResponse($response, $e->getMessage(), 400);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 403);
    } catch (RuntimeException $e) {
        return errorResponse($response, $e->getMessage(), 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al procesar la solicitud de amistad.', 500, $e);
    }
})->add(new AuthMiddleware());

// DELETE Eliminar amistad (solo participantes o administrador)
$app->delete('/amistad/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $idAmistad = (int)$args['id'];
    $authId    = getAuthUserId($request);

    try {
        $result = AmistadService::getInstance()->eliminarAmistad($idAmistad, $authId, isAuthAdminOrMod($request));
        return jsonResponse($response, $result);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 403);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al eliminar el vínculo de amistad.', 500, $e);
    }
})->add(new AuthMiddleware());

// GET Amigos de un usuario
$app->get('/usuarios/{id:[0-9]+}/amigos', function(Request $request, Response $response, array $args) {
    $idUsuario = (int)$args['id'];
    try {
        return jsonResponse($response, AmistadService::getInstance()->obtenerAmigos($idUsuario));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar la red de amigos.', 500, $e);
    }
})->add(new AuthMiddleware());

// GET Solicitudes pendientes (privado al titular o administrador)
$app->get('/usuarios/{id:[0-9]+}/solicitudes', function(Request $request, Response $response, array $args) {
    $idUsuario = (int)$args['id'];
    $authId    = getAuthUserId($request);

    if ($authId !== $idUsuario && !isAuthAdminOrMod($request)) {
        return errorResponse($response, 'No tienes permisos para consultar solicitudes ajenas.', 403);
    }

    try {
        return jsonResponse($response, AmistadService::getInstance()->obtenerSolicitudesPendientes($idUsuario));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar las solicitudes pendientes.', 500, $e);
    }
})->add(new AuthMiddleware());

// GET Estado de relación entre dos usuarios
$app->get('/usuarios/{id:[0-9]+}/relacion/{idOtro:[0-9]+}', function(Request $request, Response $response, array $args) {
    $idUsuario = (int)$args['id'];
    $idOtro    = (int)$args['idOtro'];
    $authId    = getAuthUserId($request);

    if ($authId !== $idUsuario && $authId !== $idOtro && !isAuthAdminOrMod($request)) {
        return errorResponse($response, 'No tienes permisos para consultar esta relación.', 403);
    }

    try {
        return jsonResponse($response, AmistadService::getInstance()->obtenerEstadoRelacion($idUsuario, $idOtro));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al verificar el estado de relación.', 500, $e);
    }
})->add(new AuthMiddleware());
