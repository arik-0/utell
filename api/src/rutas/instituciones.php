<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/InstitucionService.php';

$app->get('/universidades', function(Request $request, Response $response){
  try{
    $service = InstitucionService::getInstance();
    $universidades = $service->obtenerUniversidades();

    if (count($universidades) > 0){
      echo json_encode($universidades);
    }else {
      echo json_encode("No existen universidades en la BBDD.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

$app->get('/carreras', function(Request $request, Response $response){
  try{
    $service = InstitucionService::getInstance();
    $carreras = $service->obtenerCarreras();

    if (count($carreras) > 0){
      echo json_encode($carreras);
    }else {
      echo json_encode("No existen carreras en la BBDD.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

$app->get('/sedes', function(Request $request, Response $response){
  try{
    $service = InstitucionService::getInstance();
    $sedes = $service->obtenerSedes();

    if (count($sedes) > 0){
      echo json_encode($sedes);
    }else {
      echo json_encode("No existen sedes en la BBDD.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

$app->get('/paises', function(Request $request, Response $response){
  try{
    $service = InstitucionService::getInstance();
    $paises = $service->obtenerPaises();

    if (count($paises) > 0){
      echo json_encode($paises);
    }else {
      echo json_encode("No existen paises en la BBDD.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});

$app->get('/provincias', function(Request $request, Response $response){
  try{
    $service = InstitucionService::getInstance();
    $provincias = $service->obtenerProvincias();

    if (count($provincias) > 0){
      echo json_encode($provincias);
    }else {
      echo json_encode("No existen provincias en la BBDD.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});

$app->get('/ciudades', function(Request $request, Response $response){
  try{
    $service = InstitucionService::getInstance();
    $ciudades = $service->obtenerCiudades();

    if (count($ciudades) > 0){
      echo json_encode($ciudades);
    }else {
      echo json_encode("No existen ciudades en la BBDD.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});

