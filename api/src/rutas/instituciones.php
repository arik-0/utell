<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/InstitucionService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

$app->get('/universidades/con-experiencias', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerUniversidadesConExperiencias() ?: []);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar las universidades.', 500, $e);
    }
});

$app->get('/universidades/{id:[0-9]+}/muro', function(Request $request, Response $response, array $args) {
    $id        = (int)$args['id'];
    $idUsuario = (int)($request->getQueryParam('idUsuario') ?? 0);
    try {
        $muro = InstitucionService::getInstance()->obtenerMuroUniversidad($id, $idUsuario);
        if ($muro) {
            return jsonResponse($response, $muro);
        }
        return errorResponse($response, 'No se encontró la universidad especificada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al cargar el muro institucional.', 500, $e);
    }
});

$app->get('/universidades', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerUniversidades() ?: []);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar universidades.', 500, $e);
    }
});

$app->post('/universidades', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos de administrador para gestionar universidades.', 403);
    }
    $nombre  = trim((string)($request->getParam('nombre') ?? ''));
    $email   = $request->getParam('email');
    $celular = $request->getParam('celular');
    $estado  = trim((string)($request->getParam('estado') ?? 'activo'));
    if ($nombre === '') {
        return errorResponse($response, 'El nombre de la universidad es obligatorio.', 400);
    }
    try {
        $id = InstitucionService::getInstance()->crearUniversidad($nombre, $email, $celular, $estado, getAuthUserId($request));
        return jsonResponse($response, ['success' => true, 'idUniversidad' => $id, 'mensaje' => 'Universidad creada exitosamente.'], 201);
    } catch (Exception $e) {
        return errorResponse($response, 'No se pudo crear la universidad (verifique si el email ya existe).', 400, $e);
    }
})->add(new AuthMiddleware());

$app->put('/universidades/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos de administrador para modificar universidades.', 403);
    }
    $id      = (int)$args['id'];
    $nombre  = trim((string)($request->getParam('nombre') ?? ''));
    $email   = $request->getParam('email');
    $celular = $request->getParam('celular');
    $estado  = trim((string)($request->getParam('estado') ?? 'activo'));
    if ($nombre === '') {
        return errorResponse($response, 'El nombre de la universidad es obligatorio.', 400);
    }
    try {
        $ok = InstitucionService::getInstance()->actualizarUniversidad($id, $nombre, $email, $celular, $estado, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Universidad actualizada.']);
        }
        return errorResponse($response, 'Universidad no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar la universidad.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->delete('/universidades/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos de administrador para eliminar universidades.', 403);
    }
    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarUniversidad($id, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Universidad dada de baja correctamente.']);
        }
        return errorResponse($response, 'Universidad no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al dar de baja la universidad.', 500, $e);
    }
})->add(new AuthMiddleware());

$app->get('/carreras', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCarreras());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar carreras.', 500, $e);
    }
});

$app->post('/carreras', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos para gestionar carreras.', 403);
    }
    $nombre = trim((string)($request->getParam('nombre') ?? ''));
    if ($nombre === '') {
        return errorResponse($response, 'El nombre de la carrera es obligatorio.', 400);
    }
    try {
        $id = InstitucionService::getInstance()->crearCarrera($nombre, getAuthUserId($request));
        return jsonResponse($response, ['success' => true, 'idCarrera' => $id, 'mensaje' => 'Carrera creada.'], 201);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al crear la carrera.', 500, $e);
    }
})->add(new AuthMiddleware());

$app->put('/carreras/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos para modificar carreras.', 403);
    }
    $id     = (int)$args['id'];
    $nombre = trim((string)($request->getParam('nombre') ?? ''));
    if ($nombre === '') {
        return errorResponse($response, 'El nombre de la carrera es obligatorio.', 400);
    }
    try {
        $ok = InstitucionService::getInstance()->actualizarCarrera($id, $nombre, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Carrera actualizada.']);
        }
        return errorResponse($response, 'Carrera no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar la carrera.', 500, $e);
    }
})->add(new AuthMiddleware());

$app->delete('/carreras/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos para eliminar carreras.', 403);
    }
    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarCarrera($id, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Carrera eliminada.']);
        }
        return errorResponse($response, 'Carrera no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'No se pudo eliminar la carrera porque tiene registros asociados.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->get('/sedes', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedes());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar sedes.', 500, $e);
    }
});

$app->post('/sedes', function(Request $request, Response $response) {
    if (!isAuthStaff($request)) {
        return errorResponse($response, 'No autorizado para crear sedes.', 403);
    }
    $idUni    = (int)($request->getParam('idUniversidad') ?? 0);
    $nombre   = trim((string)($request->getParam('nombre') ?? ''));
    $idCiudad = $request->getParam('idCiudad') ? (int)$request->getParam('idCiudad') : null;
    $telefono = $request->getParam('telefono');
    $email    = $request->getParam('email');
    $web      = $request->getParam('web');
    $estado   = trim((string)($request->getParam('estado') ?? 'activo'));
    if ($idUni <= 0 || $nombre === '') {
        return errorResponse($response, 'idUniversidad y nombre son obligatorios.', 400);
    }
    try {
        $id = InstitucionService::getInstance()->crearSede($idUni, $nombre, $idCiudad, $telefono, $email, $web, $estado, getAuthUserId($request));
        return jsonResponse($response, ['success' => true, 'idSede' => $id, 'mensaje' => 'Sede creada.'], 201);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al crear la sede.', 500, $e);
    }
})->add(new AuthMiddleware());

$app->put('/sedes/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthStaff($request)) {
        return errorResponse($response, 'No autorizado para modificar sedes.', 403);
    }
    $id       = (int)$args['id'];
    $idUni    = (int)($request->getParam('idUniversidad') ?? 0);
    $nombre   = trim((string)($request->getParam('nombre') ?? ''));
    $idCiudad = $request->getParam('idCiudad') ? (int)$request->getParam('idCiudad') : null;
    $telefono = $request->getParam('telefono');
    $email    = $request->getParam('email');
    $web      = $request->getParam('web');
    $estado   = trim((string)($request->getParam('estado') ?? 'activo'));
    if ($idUni <= 0 || $nombre === '') {
        return errorResponse($response, 'idUniversidad y nombre son obligatorios.', 400);
    }
    try {
        $ok = InstitucionService::getInstance()->actualizarSede($id, $idUni, $nombre, $idCiudad, $telefono, $email, $web, $estado, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Sede actualizada.']);
        }
        return errorResponse($response, 'Sede no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al modificar la sede.', 500, $e);
    }
})->add(new AuthMiddleware());

$app->delete('/sedes/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthStaff($request)) {
        return errorResponse($response, 'No autorizado para eliminar sedes.', 403);
    }
    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarSede($id, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Sede dada de baja.']);
        }
        return errorResponse($response, 'Sede no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al dar de baja la sede.', 500, $e);
    }
})->add(new AuthMiddleware());

$app->get('/universidades/{id:[0-9]+}/sedes', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedesPorUniversidad($id));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar sedes de la universidad.', 500, $e);
    }
});

$app->get('/sedes/{id:[0-9]+}/carreras', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCarrerasPorSede($id));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar carreras de la sede.', 500, $e);
    }
});

$app->get('/carreras/{id:[0-9]+}/sedes', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerSedesPorCarrera($id));
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar sedes donde se dicta la carrera.', 500, $e);
    }
});

// Listar toda la oferta académica (ABM completo)
$app->get('/oferta-academica', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerOfertaAcademica());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar la oferta académica.', 500, $e);
    }
});

// Crear oferta académica (requiere JWT institucional/staff)
$app->post('/oferta-academica', function(Request $request, Response $response) {
    if (!isAuthStaff($request)) {
        return errorResponse($response, 'No autorizado para gestionar la oferta académica.', 403);
    }

    $idCarrera   = (int)$request->getParam('idCarrera');
    $idSede      = (int)$request->getParam('idSede');
    $anio        = $request->getParam('anioImplementacion') ? (int)$request->getParam('anioImplementacion') : null;
    $descripcion = $request->getParam('descripcion');
    $web         = $request->getParam('web');
    $telefono    = $request->getParam('telefono');
    if ($idCarrera <= 0 || $idSede <= 0) {
        return errorResponse($response, 'idCarrera e idSede son requeridos.', 400);
    }

    try {
        $ok = InstitucionService::getInstance()->crearOfertaAcademica($idCarrera, $idSede, $anio, $descripcion, $web, $telefono, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Oferta académica creada.'], 201);
        }
        return errorResponse($response, 'Error al crear la oferta académica.', 500);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al registrar la oferta académica.', 500, $e);
    }
})->add(new AuthMiddleware());

// Modificar oferta académica (requiere JWT institucional/staff)
$app->put('/oferta-academica/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthStaff($request)) {
        return errorResponse($response, 'No autorizado para modificar la oferta académica.', 403);
    }

    $id          = (int)$args['id'];
    $idCarrera   = (int)$request->getParam('idCarrera');
    $idSede      = (int)$request->getParam('idSede');
    $anio        = $request->getParam('anioImplementacion') ? (int)$request->getParam('anioImplementacion') : null;
    $descripcion = $request->getParam('descripcion');
    $web         = $request->getParam('web');
    $telefono    = $request->getParam('telefono');
    if ($idCarrera <= 0 || $idSede <= 0) {
        return errorResponse($response, 'idCarrera e idSede son requeridos.', 400);
    }

    try {
        $ok = InstitucionService::getInstance()->actualizarOfertaAcademica($id, $idCarrera, $idSede, $anio, $descripcion, $web, $telefono, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Oferta académica actualizada.']);
        }
        return errorResponse($response, 'Oferta académica no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar la oferta académica.', 400, $e);
    }
})->add(new AuthMiddleware());

// Eliminar oferta académica (requiere JWT institucional/staff)
$app->delete('/oferta-academica/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthStaff($request)) {
        return errorResponse($response, 'No autorizado para eliminar oferta académica.', 403);
    }

    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarOfertaAcademica($id, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Oferta académica eliminada.']);
        }
        return errorResponse($response, 'No se encontró la oferta académica.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al eliminar la oferta académica.', 500, $e);
    }
})->add(new AuthMiddleware());

$app->get('/paises', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerPaises());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar países.', 500, $e);
    }
});

$app->post('/paises', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos para gestionar países.', 403);
    }
    $nombre = trim((string)($request->getParam('nombre') ?? ''));
    if ($nombre === '') {
        return errorResponse($response, 'El nombre del país es obligatorio.', 400);
    }
    try {
        $id = InstitucionService::getInstance()->crearPais($nombre, getAuthUserId($request));
        return jsonResponse($response, ['success' => true, 'idPais' => $id, 'mensaje' => 'País creado.'], 201);
    } catch (Exception $e) {
        return errorResponse($response, 'No se pudo crear el país (posible nombre duplicado).', 400, $e);
    }
})->add(new AuthMiddleware());

$app->put('/paises/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos.', 403);
    }
    $id     = (int)$args['id'];
    $nombre = trim((string)($request->getParam('nombre') ?? ''));
    if ($nombre === '') {
        return errorResponse($response, 'El nombre del país es obligatorio.', 400);
    }
    try {
        $ok = InstitucionService::getInstance()->actualizarPais($id, $nombre, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'País actualizado.']);
        }
        return errorResponse($response, 'País no encontrado.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar el país.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->delete('/paises/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos.', 403);
    }
    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarPais($id, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'País eliminado.']);
        }
        return errorResponse($response, 'País no encontrado.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'No se pudo eliminar el país porque tiene provincias o registros asociados.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->get('/provincias', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerProvincias());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar provincias.', 500, $e);
    }
});

$app->post('/provincias', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos.', 403);
    }
    $nombre = trim((string)($request->getParam('nombre') ?? ''));
    $idPais = (int)($request->getParam('idPais') ?? 0);
    if ($nombre === '' || $idPais <= 0) {
        return errorResponse($response, 'nombre e idPais son obligatorios.', 400);
    }
    try {
        $id = InstitucionService::getInstance()->crearProvincia($nombre, $idPais, getAuthUserId($request));
        return jsonResponse($response, ['success' => true, 'idProvincia' => $id, 'mensaje' => 'Provincia creada.'], 201);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al crear la provincia.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->put('/provincias/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos.', 403);
    }
    $id     = (int)$args['id'];
    $nombre = trim((string)($request->getParam('nombre') ?? ''));
    $idPais = (int)($request->getParam('idPais') ?? 0);
    if ($nombre === '' || $idPais <= 0) {
        return errorResponse($response, 'nombre e idPais son obligatorios.', 400);
    }
    try {
        $ok = InstitucionService::getInstance()->actualizarProvincia($id, $nombre, $idPais, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Provincia actualizada.']);
        }
        return errorResponse($response, 'Provincia no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar la provincia.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->delete('/provincias/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos.', 403);
    }
    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarProvincia($id, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Provincia eliminada.']);
        }
        return errorResponse($response, 'Provincia no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'No se pudo eliminar la provincia porque tiene ciudades asociadas.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->get('/ciudades', function(Request $request, Response $response) {
    try {
        return jsonResponse($response, InstitucionService::getInstance()->obtenerCiudades());
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar ciudades.', 500, $e);
    }
});

$app->post('/ciudades', function(Request $request, Response $response) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos.', 403);
    }
    $nombre      = trim((string)($request->getParam('nombre') ?? ''));
    $idProvincia = (int)($request->getParam('idProvincia') ?? 0);
    if ($nombre === '' || $idProvincia <= 0) {
        return errorResponse($response, 'nombre e idProvincia son obligatorios.', 400);
    }
    try {
        $id = InstitucionService::getInstance()->crearCiudad($nombre, $idProvincia, getAuthUserId($request));
        return jsonResponse($response, ['success' => true, 'idCiudad' => $id, 'mensaje' => 'Ciudad creada.'], 201);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al crear la ciudad.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->put('/ciudades/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos.', 403);
    }
    $id          = (int)$args['id'];
    $nombre      = trim((string)($request->getParam('nombre') ?? ''));
    $idProvincia = (int)($request->getParam('idProvincia') ?? 0);
    if ($nombre === '' || $idProvincia <= 0) {
        return errorResponse($response, 'nombre e idProvincia son obligatorios.', 400);
    }
    try {
        $ok = InstitucionService::getInstance()->actualizarCiudad($id, $nombre, $idProvincia, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Ciudad actualizada.']);
        }
        return errorResponse($response, 'Ciudad no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al actualizar la ciudad.', 400, $e);
    }
})->add(new AuthMiddleware());

$app->delete('/ciudades/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    if (!isAuthAdminOrMod($request)) {
        return errorResponse($response, 'Se requieren permisos administrativos.', 403);
    }
    $id = (int)$args['id'];
    try {
        $ok = InstitucionService::getInstance()->eliminarCiudad($id, getAuthUserId($request));
        if ($ok) {
            return jsonResponse($response, ['success' => true, 'mensaje' => 'Ciudad eliminada.']);
        }
        return errorResponse($response, 'Ciudad no encontrada.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'No se pudo eliminar la ciudad porque tiene usuarios o sedes asociadas.', 400, $e);
    }
})->add(new AuthMiddleware());
