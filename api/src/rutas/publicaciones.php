<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/PublicacionService.php';

$app->get('/consultas', function(Request $request, Response $response){
  try{
    $service = PublicacionService::getInstance();
    $consultas = $service->obtenerConsultas();

    if (count($consultas) > 0){
      echo json_encode($consultas);
    }else {
      echo json_encode("No existen consultas en la BBDD.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

$app->get('/parati', function(Request $request, Response $response){
  $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
  
  try{
    $service = PublicacionService::getInstance();
    $consultas = $service->obtenerParati($idUsuario);

    if (count($consultas) > 0){
      echo json_encode($consultas);
    }else {
      echo json_encode([]);
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

$app->post('/publicacion/nuevo', function(Request $request, Response $response){
   $db = db::getInstance()->getConnection(); // Keep DB connection here temporarily for resolvers, or move it to a service
   
   $idUsuario = $request->getParam('idUsuario');
   $tipoPublicacion = $request->getParam('tipoPublicacion') ?? 'Consulta';
   $titulo = $request->getParam('titulo') ?? '';
   $texto = $request->getParam('texto') ?? ''; 
   $nombreUniversidad = $request->getParam('universidad');
   $nombreCarrera = $request->getParam('carrera');
   
   $fecha = date('Y-m-d');
   $hora = date('H:i:s');
   $idSede = null;
   $likes = 0;

  try{
    $idUni = null;
    if (!empty($nombreUniversidad)) {
        $stmtUni = $db->prepare("SELECT idUniversidad FROM universidades WHERE nombre = :nombre LIMIT 1");
        $stmtUni->execute([':nombre' => $nombreUniversidad]);
        $rowUni = $stmtUni->fetch(PDO::FETCH_ASSOC);
        if ($rowUni) {
            $idUni = $rowUni['idUniversidad'];
        } else {
            $stmtInsertUni = $db->prepare("INSERT INTO universidades (nombre, email, password) VALUES (:nombre, '', '')");
            $stmtInsertUni->execute([':nombre' => $nombreUniversidad]);
            $idUni = $db->lastInsertId();
        }
    }
    
    $idCar = null;
    if (!empty($nombreCarrera)) {
        $stmtCar = $db->prepare("SELECT idCarrera FROM carreras WHERE nombre = :nombre LIMIT 1");
        $stmtCar->execute([':nombre' => $nombreCarrera]);
        $rowCar = $stmtCar->fetch(PDO::FETCH_ASSOC);
        if ($rowCar) {
            $idCar = $rowCar['idCarrera'];
        } else {
            $stmtInsertCar = $db->prepare("INSERT INTO carreras (nombre) VALUES (:nombre)");
            $stmtInsertCar->execute([':nombre' => $nombreCarrera]);
            $idCar = $db->lastInsertId();
        }
    }

    $datos = [
        'idUsuario' => $idUsuario,
        'tipoPublicacion' => $tipoPublicacion,
        'titulo' => $titulo,
        'fecha' => $fecha,
        'hora' => $hora,
        'idUniversidad' => $idUni,
        'idSede' => $idSede,
        'idCarrera' => $idCar,
        'texto' => $texto
    ];
    
    $service = PublicacionService::getInstance();
    $service->crear($datos);
    
    echo json_encode("Nueva publicación guardada.");  
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});

$app->get('/publicacion/{id}', function(Request $request, Response $response){
  $id = (int)$request->getAttribute('id');
  $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
  
  try{
    $service = PublicacionService::getInstance();
    $publicacion = $service->obtenerPorId($id, $idUsuario);

    if ($publicacion) {
      echo json_encode($publicacion);
    } else {
      echo json_encode("No existe la publicación.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});

$app->post('/publicacion/{id}/like', function(Request $request, Response $response){
  $idPublicacion = (int)$request->getAttribute('id');
  $idUsuario = (int)$request->getParam('idUsuario');
  
  try{
    $service = PublicacionService::getInstance();
    $resultado = $service->toggleLike($idPublicacion, $idUsuario);
    
    echo json_encode($resultado);
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});
