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

// GET Catálogo de permisos atómicos (Puntos T02/T03)
$app->get('/permisos', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, SeguridadService::getInstance()->obtenerPermisosCatalogo());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar el catálogo de permisos.', 500, $e);
    }
});

// GET Grupos de permiso con sus permisos y cantidad de usuarios (Puntos T02/T03)
$app->get('/grupos-permiso', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, SeguridadService::getInstance()->obtenerGruposPermiso());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar los grupos de permiso.', 500, $e);
    }
});

// POST Crear grupo de permisos (Puntos T02/T03)
$app->post('/grupos-permiso', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos de administrador para gestionar grupos.', 403);
    }
    $nombre      = trim((string)($request->getParam('nombre') ?? ''));
    $descripcion = trim((string)($request->getParam('descripcion') ?? ''));
    $permisos    = $request->getParam('permisos') ?? [];
    if ($nombre === '') {
        return errorResponse($response, 'El nombre del grupo es obligatorio.', 400);
    }
    try {
        $grupo = SeguridadService::getInstance()->crearGrupoPermiso($nombre, $descripcion, is_array($permisos) ? $permisos : [], getAuthUserId($request));
        return jsonResponse($response, ['success' => true, 'grupo' => $grupo], 201);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al crear el grupo de permisos.', 500, $e);
    }
})->add(new AuthMiddleware());

// PUT Modificar grupo de permisos (Puntos T02/T03)
$app->put('/grupos-permiso/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos de administrador para modificar grupos.', 403);
    }
    $idGrupo     = (int)$args['id'];
    $nombre      = trim((string)($request->getParam('nombre') ?? ''));
    $descripcion = trim((string)($request->getParam('descripcion') ?? ''));
    $permisos    = $request->getParam('permisos') ?? [];
    if ($idGrupo <= 0 || $nombre === '') {
        return errorResponse($response, 'ID y nombre del grupo son obligatorios.', 400);
    }
    try {
        $ok = SeguridadService::getInstance()->actualizarGrupoPermiso($idGrupo, $nombre, $descripcion, is_array($permisos) ? $permisos : [], getAuthUserId($request));
        return jsonResponse($response, ['success' => $ok, 'mensaje' => 'Grupo de permisos actualizado.']);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al modificar el grupo de permisos.', 500, $e);
    }
})->add(new AuthMiddleware());

// DELETE Eliminar grupo de permisos (Puntos T02/T03)
$app->delete('/grupos-permiso/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos de administrador para eliminar grupos.', 403);
    }
    $idGrupo = (int)$args['id'];
    try {
        $ok = SeguridadService::getInstance()->eliminarGrupoPermiso($idGrupo, getAuthUserId($request));
        return jsonResponse($response, ['success' => $ok, 'mensaje' => 'Grupo de permisos eliminado.']);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al eliminar el grupo de permisos.', 500, $e);
    }
})->add(new AuthMiddleware());

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

// PUT Asignar rol a usuario (requiere JWT administrativo — CU5 / CU8 / Punto T06)
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

// PUT Cambiar estado de cuenta (bloquear / activar — CU6 / Punto T02 / T06)
$app->put('/usuarios/{id:[0-9]+}/estado', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos para modificar estados de cuenta.', 403);
    }

    $idUsuario = (int)$args['id'];
    $accion    = trim((string)($request->getParam('accion') ?? 'bloquear'));
    try {
        $ok = SeguridadService::getInstance()->cambiarEstadoUsuario($idUsuario, $accion, getAuthUserId($request));
        return jsonResponse($response, ['success' => $ok, 'mensaje' => 'Estado de cuenta actualizado.']);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar el estado de la cuenta.', 500, $e);
    }
})->add(new AuthMiddleware());

// PUT Resetear clave de usuario administrativamente (Puntos T02 y T03)
$app->put('/usuarios/{id:[0-9]+}/reset-clave', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos para resetear contraseñas.', 403);
    }

    $idUsuario  = (int)$args['id'];
    $nuevaClave = trim((string)($request->getParam('nuevaClave') ?? ''));
    if (strlen($nuevaClave) < 6) {
        return errorResponse($response, 'La nueva contraseña debe tener al menos 6 caracteres.', 400);
    }

    try {
        $ok = SeguridadService::getInstance()->resetearClaveUsuario($idUsuario, $nuevaClave, getAuthUserId($request));
        return jsonResponse($response, ['success' => $ok, 'mensaje' => 'Contraseña reseteada correctamente.']);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al resetear la contraseña.', 500, $e);
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

// GET Registro de auditoría transversal (requiere rol Admin — Punto T06 / CU9)
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

// GET Exportar Resguardo SQL (Backup en 1 clic — Punto T07 / CU9)
$app->get('/moderacion/backup/exportar', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos de administrador para generar resguardos.', 403);
    }

    try {
        $backup = SeguridadService::getInstance()->generarBackupSql(getAuthUserId($request));
        return jsonResponse($response, $backup);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al generar el resguardo de base de datos.', 500, $e);
    }
})->add(new AuthMiddleware());
