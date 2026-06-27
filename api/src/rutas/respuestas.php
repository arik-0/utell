<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/RespuestaService.php';

// Get responses for a post
$app->get('/publicacion/{id}/respuestas', function(Request $request, Response $response){
  $id = (int)$request->getAttribute('id');
  try{
    $service = RespuestaService::getInstance();
    $respuestas = $service->obtenerRespuestasPorPublicacion($id);

    if (count($respuestas) > 0) {
      echo json_encode($respuestas);
    } else {
      echo json_encode([]);
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});

// Create new response
$app->post('/respuesta/nuevo', function(Request $request, Response $response){
  $datos = [
      'idPublicacion' => $request->getParam('idPublicacion'),
      'idUsuario' => $request->getParam('idUsuario'),
      'texto' => $request->getParam('texto')
  ];
  
  try{
    $service = RespuestaService::getInstance();
    $guardado = $service->crearRespuesta($datos);
    
    if ($guardado) {
        echo json_encode("Respuesta guardada.");
    } else {
        echo json_encode("Error al guardar respuesta.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});
