<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/AmistadService.php';

// POST Enviar solicitud de amistad
$app->post('/amistad/solicitar', function(Request $request, Response $response){
    $idSolicitante = $request->getParam('idSolicitante');
    $idReceptor    = $request->getParam('idReceptor');

    if (!$idSolicitante || !$idReceptor) {
        echo '{"error":{"text":"idSolicitante y idReceptor son requeridos."}}';
        return;
    }

    try {
        $service = AmistadService::getInstance();
        $result  = $service->enviarSolicitud((int)$idSolicitante, (int)$idReceptor);
        echo json_encode($result);
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// POST Responder solicitud (aceptar / rechazar)
$app->post('/amistad/{id}/responder', function(Request $request, Response $response){
    $idAmistad = (int)$request->getAttribute('id');
    $accion    = $request->getParam('accion');

    if (!$accion) {
        echo '{"error":{"text":"El campo accion es requerido (aceptar o rechazar)."}}';
        return;
    }

    try {
        $service = AmistadService::getInstance();
        $result  = $service->responderSolicitud($idAmistad, $accion);
        echo json_encode($result);
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// DELETE Eliminar amistad
$app->delete('/amistad/{id}', function(Request $request, Response $response){
    $idAmistad = (int)$request->getAttribute('id');

    try {
        $service = AmistadService::getInstance();
        $result  = $service->eliminarAmistad($idAmistad);
        echo json_encode($result);
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// GET Amigos de un usuario
$app->get('/usuarios/{id}/amigos', function(Request $request, Response $response){
    $idUsuario = (int)$request->getAttribute('id');

    try {
        $service = AmistadService::getInstance();
        echo json_encode($service->obtenerAmigos($idUsuario));
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// GET Solicitudes pendientes de un usuario (recibidas + enviadas)
$app->get('/usuarios/{id}/solicitudes', function(Request $request, Response $response){
    $idUsuario = (int)$request->getAttribute('id');

    try {
        $service = AmistadService::getInstance();
        echo json_encode($service->obtenerSolicitudesPendientes($idUsuario));
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// GET Estado de relacion entre dos usuarios
$app->get('/usuarios/{id}/relacion/{idOtro}', function(Request $request, Response $response){
    $idUsuario = (int)$request->getAttribute('id');
    $idOtro    = (int)$request->getAttribute('idOtro');

    try {
        $service = AmistadService::getInstance();
        echo json_encode($service->obtenerEstadoRelacion($idUsuario, $idOtro));
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});
