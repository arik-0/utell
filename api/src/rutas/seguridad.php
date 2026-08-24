<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/SeguridadService.php';

// GET Permisos y capacidades de un usuario
$app->get('/usuarios/{id:[0-9]+}/permisos', function(Request $request, Response $response, array $args){
    $idUsuario = (int)$args['id'];
    try {
        $service  = SeguridadService::getInstance();
        $permisos = $service->obtenerPermisosUsuario($idUsuario);
        echo json_encode([
            'idUsuario' => $idUsuario,
            'permisos'  => $permisos
        ]);
    } catch (Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// GET Listado de roles disponibles
$app->get('/roles', function(Request $request, Response $response){
    try {
        $service = SeguridadService::getInstance();
        echo json_encode($service->obtenerRoles());
    } catch (Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// GET Listado de grupos de permiso
$app->get('/grupos-permiso', function(Request $request, Response $response){
    try {
        $service = SeguridadService::getInstance();
        echo json_encode($service->obtenerGruposPermiso());
    } catch (Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// POST Asignar grupo de permiso a usuario
$app->post('/usuarios/{id:[0-9]+}/grupos', function(Request $request, Response $response, array $args){
    $idUsuario      = (int)$args['id'];
    $idGrupoPermiso = (int)($request->getParam('idGrupoPermiso') ?? 0);

    if ($idGrupoPermiso === 0) {
        echo '{"error":{"text":"Parámetro requerido: idGrupoPermiso."}}';
        return;
    }

    try {
        $service = SeguridadService::getInstance();
        $ok      = $service->asignarGrupo($idUsuario, $idGrupoPermiso);
        echo json_encode(['success' => $ok, 'mensaje' => 'Grupo de permiso asignado.']);
    } catch (Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// POST Moderación de publicaciones (cerrar o eliminar)
$app->post('/moderacion/publicacion', function(Request $request, Response $response){
    $idModerador   = (int)($request->getParam('idModerador') ?? 0);
    $idPublicacion = (int)($request->getParam('idPublicacion') ?? 0);
    $accion        = (string)($request->getParam('accion') ?? 'cerrar');
    $motivo        = $request->getParam('motivo');

    if ($idModerador === 0 || $idPublicacion === 0) {
        echo '{"error":{"text":"Parámetros requeridos: idModerador, idPublicacion."}}';
        return;
    }

    try {
        $service   = SeguridadService::getInstance();
        $resultado = $service->moderarPublicacion($idModerador, $idPublicacion, $accion, $motivo);
        echo json_encode($resultado);
    } catch (Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});
