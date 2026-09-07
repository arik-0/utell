<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/SeguridadService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// GET Permisos de un usuario (publico — el frontend lo usa al iniciar sesion)
$app->get('/usuarios/{id:[0-9]+}/permisos', function(Request $request, Response $response, array $args) {
    $idUsuario = (int)$args['id'];
    try {
        $permisos = SeguridadService::getInstance()->obtenerPermisosUsuario($idUsuario);
        return jsonResponse($response, ['idUsuario' => $idUsuario, 'permisos' => $permisos]);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// GET Roles (publico)
$app->get('/roles', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, SeguridadService::getInstance()->obtenerRoles());
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// GET Grupos de permiso (publico)
$app->get('/grupos-permiso', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, SeguridadService::getInstance()->obtenerGruposPermiso());
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// POST Asignar grupo de permiso a usuario (requiere JWT)
$app->post('/usuarios/{id:[0-9]+}/grupos', function(Request $request, Response $response, array $args) {
    $idUsuario      = (int)$args['id'];
    $idGrupoPermiso = (int)($request->getParam('idGrupoPermiso') ?? 0);
    if ($idGrupoPermiso === 0) {
        return jsonResponse($response, ['error' => ['text' => 'Parametro requerido: idGrupoPermiso.']], 400);
    }
    try {
        $ok = SeguridadService::getInstance()->asignarGrupo($idUsuario, $idGrupoPermiso);
        return jsonResponse($response, ['success' => $ok, 'mensaje' => 'Grupo de permiso asignado.']);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// POST Moderacion (requiere JWT)
$app->post('/moderacion/publicacion', function(Request $request, Response $response) {
    $idModerador   = (int)($request->getParam('idModerador') ?? 0);
    $idPublicacion = (int)($request->getParam('idPublicacion') ?? 0);
    $accion        = (string)($request->getParam('accion') ?? 'cerrar');
    $motivo        = $request->getParam('motivo');
    if ($idModerador === 0 || $idPublicacion === 0) {
        return jsonResponse($response, ['error' => ['text' => 'Parametros requeridos: idModerador, idPublicacion.']], 400);
    }
    try {
        $resultado = SeguridadService::getInstance()->moderarPublicacion($idModerador, $idPublicacion, $accion, $motivo);
        return jsonResponse($response, $resultado);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());
