<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/PublicacionService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// GET /publicaciones — feed principal (publico, usa idUsuario de query param)
$app->get('/publicaciones', function(Request $request, Response $response) {
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $posts = PublicacionService::getInstance()->obtenerParati($idUsuario);
        return jsonResponse($response, $posts ?: []);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// GET /parati — alias retrocompatible de /publicaciones
$app->get('/parati', function(Request $request, Response $response) {
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $posts = PublicacionService::getInstance()->obtenerParati($idUsuario);
        return jsonResponse($response, $posts ?: []);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// GET /consultas — listado de todas las consultas (publico)
$app->get('/consultas', function(Request $request, Response $response) {
    try {
        $consultas = PublicacionService::getInstance()->obtenerConsultas();
        return jsonResponse($response, $consultas ?: []);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// GET /tured — publicaciones de la red del usuario (requiere JWT)
$app->get('/tured', function(Request $request, Response $response) {
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    if ($idUsuario === 0) return jsonResponse($response, []);
    try {
        $publicaciones = PublicacionService::getInstance()->obtenerTuRed($idUsuario);
        return jsonResponse($response, $publicaciones ?: []);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// GET /publicacion/{id} y /publicaciones/{id} — detalle de una publicacion (publico)
$detallePublicacionHandler = function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $publicacion = PublicacionService::getInstance()->obtenerPorId($id, $idUsuario);
        if ($publicacion) return jsonResponse($response, $publicacion);
        return jsonResponse($response, ['error' => ['text' => 'No existe la publicacion.']], 404);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
};
$app->get('/publicacion/{id:[0-9]+}', $detallePublicacionHandler);
$app->get('/publicaciones/{id:[0-9]+}', $detallePublicacionHandler);

// POST /publicacion/nuevo y /publicaciones/nuevo — crear publicacion (requiere JWT)
$crearPublicacionHandler = function(Request $request, Response $response) {
    $db = db::getInstance()->getConnection();

    $jwtPayload = $request->getAttribute('jwtPayload');
    $idUsuario  = !empty($jwtPayload['idUsuario']) ? (int)$jwtPayload['idUsuario'] : (int)($request->getParam('idUsuario') ?? 0);
    $tipoPublicacion = $request->getParam('tipoPublicacion') ?? 'Consulta';
    $titulo          = $request->getParam('titulo') ?? '';
    $texto           = $request->getParam('texto') ?? '';
    $idUniversidad   = $request->getParam('idUniversidad');
    $idSede          = $request->getParam('idSede');
    $idCarrera       = $request->getParam('idCarrera');
    $nombreUniversidad = $request->getParam('universidad');
    $nombreSede        = $request->getParam('sede');
    $nombreCarrera     = $request->getParam('carrera');

    if (empty($idUsuario) || empty(trim($texto))) {
        return jsonResponse($response, ['error' => ['text' => 'idUsuario y texto son requeridos.']], 400);
    }

    try {
        $idUni = !empty($idUniversidad) ? (int)$idUniversidad : null;
        if (empty($idUni) && !empty($nombreUniversidad)) {
            $stmtUni = $db->prepare("SELECT idUniversidad FROM universidades WHERE nombre = :nombre LIMIT 1");
            $stmtUni->execute([':nombre' => $nombreUniversidad]);
            $rowUni = $stmtUni->fetch(PDO::FETCH_ASSOC);
            if ($rowUni) {
                $idUni = (int)$rowUni['idUniversidad'];
            }
        }

        $idSed = !empty($idSede) ? (int)$idSede : null;
        if (empty($idSed) && !empty($nombreSede)) {
            $sqlSed = "SELECT idSede, idUniversidad FROM sedes WHERE nombre = :nombre " . (!empty($idUni) ? "AND idUniversidad = :idUni " : "") . "LIMIT 1";
            $stmtSed = $db->prepare($sqlSed);
            $paramsSed = [':nombre' => $nombreSede];
            if (!empty($idUni)) $paramsSed[':idUni'] = $idUni;
            $stmtSed->execute($paramsSed);
            $rowSed = $stmtSed->fetch(PDO::FETCH_ASSOC);
            if ($rowSed) {
                $idSed = (int)$rowSed['idSede'];
                if (empty($idUni)) $idUni = (int)$rowSed['idUniversidad'];
            }
        }

        $idCar = !empty($idCarrera) ? (int)$idCarrera : null;
        if (empty($idCar) && !empty($nombreCarrera)) {
            $stmtCar = $db->prepare("SELECT idCarrera FROM carreras WHERE nombre = :nombre LIMIT 1");
            $stmtCar->execute([':nombre' => $nombreCarrera]);
            $rowCar = $stmtCar->fetch(PDO::FETCH_ASSOC);
            if ($rowCar) {
                $idCar = (int)$rowCar['idCarrera'];
            }
        }

        $datos = [
            'idUsuario'       => $idUsuario,
            'tipoPublicacion' => $tipoPublicacion,
            'titulo'          => $titulo,
            'fecha'           => date('Y-m-d'),
            'hora'            => date('H:i:s'),
            'idUniversidad'   => $idUni,
            'idSede'          => $idSed,
            'idCarrera'       => $idCar,
            'texto'           => $texto,
            'puntuacionClima'          => $request->getParam('puntuacionClima'),
            'puntuacionInfraestructura'=> $request->getParam('puntuacionInfraestructura'),
            'puntuacionAcademica'      => $request->getParam('puntuacionAcademica'),
        ];

        PublicacionService::getInstance()->crear($datos);
        return jsonResponse($response, ['mensaje' => 'Nueva publicacion guardada.'], 201);

    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
};
$app->post('/publicacion/nuevo', $crearPublicacionHandler)->add(new AuthMiddleware());
$app->post('/publicaciones/nuevo', $crearPublicacionHandler)->add(new AuthMiddleware());

// POST /publicacion/{id}/like y /publicaciones/{id}/like — toggle like (requiere JWT)
$toggleLikeHandler = function(Request $request, Response $response, array $args) {
    $idPublicacion = (int)$args['id'];
    $idUsuario = (int)$request->getParam('idUsuario');
    if ($idUsuario === 0) {
        return jsonResponse($response, ['error' => ['text' => 'idUsuario requerido.']], 400);
    }
    try {
        $resultado = PublicacionService::getInstance()->toggleLike($idPublicacion, $idUsuario);
        return jsonResponse($response, $resultado);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
};
$app->post('/publicacion/{id:[0-9]+}/like', $toggleLikeHandler)->add(new AuthMiddleware());
$app->post('/publicaciones/{id:[0-9]+}/like', $toggleLikeHandler)->add(new AuthMiddleware());

