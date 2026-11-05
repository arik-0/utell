<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/SeguridadService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// GET Permisos de un usuario
$app->get('/usuarios/{id:[0-9]+}/permisos', function(Request $request, Response $response, array $args) {
    $idUsuario = (int)$args['id'];
    try {
        $permisos = SeguridadService::getInstance()->obtenerPermisosUsuario($idUsuario);
        return jsonResponse($response, ['idUsuario' => $idUsuario, 'permisos' => $permisos]);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar los permisos del usuario.', 500, $e);
    }
});

// GET Roles
$app->get('/roles', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, SeguridadService::getInstance()->obtenerRoles());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar los roles.', 500, $e);
    }
});

// GET Grupos de permiso
$app->get('/grupos-permiso', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, SeguridadService::getInstance()->obtenerGruposPermiso());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar los grupos de permiso.', 500, $e);
    }
});

// POST Asignar grupo de permiso a usuario (requiere JWT administrativo)
$app->post('/usuarios/{id:[0-9]+}/grupos', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos de administrador para asignar grupos.', 403);
    }

    $idUsuario      = (int)$args['id'];
    $idGrupoPermiso = (int)($request->getParam('idGrupoPermiso') ?? 0);
    if ($idGrupoPermiso <= 0) {
        return errorResponse($response, 'Parámetro requerido: idGrupoPermiso.', 400);
    }

    try {
        $ok = SeguridadService::getInstance()->asignarGrupo($idUsuario, $idGrupoPermiso, getAuthUserId($request));
        return jsonResponse($response, ['success' => $ok, 'mensaje' => 'Grupo de permiso asignado.']);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al asignar el grupo de permiso.', 500, $e);
    }
})->add(new AuthMiddleware());

// POST Moderación de publicaciones (requiere JWT; moderador se extrae del token)
$app->post('/moderacion/publicacion', function(Request $request, Response $response) {
    $idModerador   = getAuthUserId($request);
    $idPublicacion = (int)($request->getParam('idPublicacion') ?? 0);
    $accion        = (string)($request->getParam('accion') ?? 'cerrar');
    $motivo        = $request->getParam('motivo');

    if ($idModerador <= 0 || $idPublicacion <= 0) {
        return errorResponse($response, 'Parámetro requerido: idPublicacion.', 400);
    }

    try {
        $resultado = SeguridadService::getInstance()->moderarPublicacion($idModerador, $idPublicacion, $accion, $motivo);
        return jsonResponse($response, $resultado);
    } catch (InvalidArgumentException $e) {
        return errorResponse($response, $e->getMessage(), 400);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 403);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al moderar la publicación.', 500, $e);
    }
})->add(new AuthMiddleware());
