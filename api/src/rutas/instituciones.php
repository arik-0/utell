<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/InstitucionService.php';

// Listado de universidades con estadísticas de experiencias
$app->get('/universidades/con-experiencias', function(Request $request, Response $response){
    try{
        $service = InstitucionService::getInstance();
        $data    = $service->obtenerUniversidadesConExperiencias();
        echo json_encode(count($data) > 0 ? $data : []);
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// Muro institucional de una universidad (Métricas consolidadas, experiencias, consultas y sedes)
$app->get('/universidades/{id:[0-9]+}/muro', function(Request $request, Response $response, array $args){
    $id = (int)$args['id'];
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);

    try{
        $service = InstitucionService::getInstance();
        $muro    = $service->obtenerMuroUniversidad($id, $idUsuario);
        if ($muro) {
            echo json_encode($muro);
        } else {
            echo '{"error":{"text":"No se encontró la universidad especificada."}}';
        }
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

$app->get('/universidades', function(Request $request, Response $response){
    try{
        $service = InstitucionService::getInstance();
        $data    = $service->obtenerUniversidades();
        echo json_encode(count($data) > 0 ? $data : []);
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

$app->get('/carreras', function(Request $request, Response $response){
    try{
        $service = InstitucionService::getInstance();
        echo json_encode($service->obtenerCarreras());
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

$app->get('/sedes', function(Request $request, Response $response){
    try{
        $service = InstitucionService::getInstance();
        echo json_encode($service->obtenerSedes());
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// Sedes de una universidad especifica
$app->get('/universidades/{id:[0-9]+}/sedes', function(Request $request, Response $response, array $args){
    $id = (int)$args['id'];
    try{
        $service = InstitucionService::getInstance();
        echo json_encode($service->obtenerSedesPorUniversidad($id));
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// Carreras dictadas en una sede (via oferta_academica)
$app->get('/sedes/{id:[0-9]+}/carreras', function(Request $request, Response $response, array $args){
    $id = (int)$args['id'];
    try{
        $service = InstitucionService::getInstance();
        echo json_encode($service->obtenerCarrerasPorSede($id));
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// Sedes donde se dicta una carrera
$app->get('/carreras/{id:[0-9]+}/sedes', function(Request $request, Response $response, array $args){
    $id = (int)$args['id'];
    try{
        $service = InstitucionService::getInstance();
        echo json_encode($service->obtenerSedesPorCarrera($id));
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// Crear oferta academica (vinculo carrera-sede)
$app->post('/oferta-academica', function(Request $request, Response $response){
    $idCarrera = (int)$request->getParam('idCarrera');
    $idSede    = (int)$request->getParam('idSede');
    $anio      = $request->getParam('anioImplementacion') ? (int)$request->getParam('anioImplementacion') : null;

    try{
        $service = InstitucionService::getInstance();
        $ok      = $service->crearOfertaAcademica($idCarrera, $idSede, $anio);
        echo json_encode($ok ? "Oferta academica creada." : "Error al crear oferta academica.");
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

// Eliminar oferta academica
$app->delete('/oferta-academica/{id:[0-9]+}', function(Request $request, Response $response, array $args){
    $id = (int)$args['id'];
    try{
        $service = InstitucionService::getInstance();
        $ok      = $service->eliminarOfertaAcademica($id);
        echo json_encode($ok ? "Oferta academica eliminada." : "No se encontro la oferta.");
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

$app->get('/paises', function(Request $request, Response $response){
    try{
        $service = InstitucionService::getInstance();
        echo json_encode($service->obtenerPaises());
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

$app->get('/provincias', function(Request $request, Response $response){
    try{
        $service = InstitucionService::getInstance();
        echo json_encode($service->obtenerProvincias());
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});

$app->get('/ciudades', function(Request $request, Response $response){
    try{
        $service = InstitucionService::getInstance();
        echo json_encode($service->obtenerCiudades());
    }catch(Exception $e){
        echo '{"error":{"text":"'.$e->getMessage().'"}}';
    }
});
