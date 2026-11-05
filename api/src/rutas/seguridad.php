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

// PUT Asignar rol a usuario (requiere JWT administrativo — CU5 / CU8)
$app->put('/usuarios/{id:[0-9]+}/rol', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos para cambiar roles.', 403);
    }

    $idUsuario = (int)$args['id'];
    $idRol     = (int)($request->getParam('idRol') ?? 0);
    if ($idRol <= 0) {
        return errorResponse($response, 'Parámetro requerido: idRol.', 400);
    }

    try {
        $ok = SeguridadService::getInstance()->cambiarRolUsuario($idUsuario, $idRol, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Rol actualizado exitosamente.']);
        }
        return errorResponse($response, 'No se pudo actualizar el rol.', 400);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar el rol del usuario.', 500, $e);
    }
})->add(new AuthMiddleware());

// POST Moderación de publicaciones (requiere JWT; moderador se extrae del token — CU6)
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

// GET Publicaciones para moderación (requiere rol Admin o Moderador — CU6)
$app->get('/moderacion/publicaciones', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Acceso restringido al equipo de moderación.', 403);
    }

    try {
        $posts = SeguridadService::getInstance()->obtenerPublicacionesModerables();
        return jsonResponse($response, $posts);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar publicaciones para moderación.', 500, $e);
    }
})->add(new AuthMiddleware());

// GET Listado de usuarios para panel de administración (requiere rol Admin o Moderador — CU5 / CU8)
$app->get('/moderacion/usuarios', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Acceso restringido a administradores y moderadores.', 403);
    }

    try {
        $usuarios = SeguridadService::getInstance()->obtenerUsuariosAdmin();
        return jsonResponse($response, $usuarios);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar usuarios para administración.', 500, $e);
    }
})->add(new AuthMiddleware());

// GET Registro de auditoría transversal (requiere rol Admin — CU9 / RNF07)
$app->get('/moderacion/auditoria', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Acceso restringido al registro de auditoría.', 403);
    }

    try {
        $logs = SeguridadService::getInstance()->obtenerLogsAuditoria();
        return jsonResponse($response, $logs);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar el registro de auditoría.', 500, $e);
    }
})->add(new AuthMiddleware());
