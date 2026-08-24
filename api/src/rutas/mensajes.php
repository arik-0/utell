<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/MensajeService.php';

// GET Listado de conversaciones del usuario
$app->get('/mensajes/conversaciones', function(Request $request, Response $response){
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);

    if ($idUsuario === 0) {
        echo json_encode([]);
        return;
    }

    try {
        $service = MensajeService::getInstance();
        $conversaciones = $service->obtenerConversaciones($idUsuario);
        echo json_encode($conversaciones);
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// GET Historial de mensajes entre dos usuarios
$app->get('/mensajes/chat/{idContacto}', function(Request $request, Response $response, array $args){
    $idContacto = (int)$args['idContacto'];
    $idUsuario  = (int)($request->getQueryParam('idUsuario') ?? 0);

    if ($idUsuario === 0 || $idContacto === 0) {
        echo json_encode([]);
        return;
    }

    try {
        $service = MensajeService::getInstance();
        $chat = $service->obtenerChat($idUsuario, $idContacto);
        echo json_encode($chat);
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// GET Conteo global de mensajes no leídos
$app->get('/mensajes/no-leidos', function(Request $request, Response $response){
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);

    if ($idUsuario === 0) {
        echo json_encode(['totalNoLeidos' => 0]);
        return;
    }

    try {
        $service = MensajeService::getInstance();
        $total = $service->obtenerTotalNoLeidos($idUsuario);
        echo json_encode(['totalNoLeidos' => $total]);
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// POST Enviar nuevo mensaje directo
$app->post('/mensajes/enviar', function(Request $request, Response $response){
    $idEmisor   = (int)($request->getParam('idEmisor') ?? 0);
    $idReceptor = (int)($request->getParam('idReceptor') ?? 0);
    $texto      = (string)($request->getParam('texto') ?? '');

    if ($idEmisor === 0 || $idReceptor === 0 || empty(trim($texto))) {
        echo '{"error":{"text":"Parámetros requeridos: idEmisor, idReceptor, texto."}}';
        return;
    }

    try {
        $service = MensajeService::getInstance();
        $resultado = $service->enviar($idEmisor, $idReceptor, $texto);
        echo json_encode($resultado);
    } catch(Exception $e) {
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});
