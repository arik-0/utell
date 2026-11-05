<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * DASHBOARD API - Cátedra Bases de Datos Aplicada (UAI)
 * Endpoints multinivel (Nivel 1, Nivel 2, Nivel 3) desacoplados mediante DashboardFacade
 * ══════════════════════════════════════════════════════════════════════════════
 */
require_once __DIR__ . '/../Facades/DashboardFacade.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// ─── 1. LOGIN DE ACCESO AL TABLERO (SOLO UNIVERSIDADES Y ADMINISTRADORES/MODERADORES) ──
$app->post('/dashboard/login', function (Request $request, Response $response) {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $request->getParsedBody() ?? $_POST ?? [];

    $email    = trim((string)($body['email'] ?? $body['usuario'] ?? ''));
    $password = trim((string)($body['password'] ?? $body['clave'] ?? ''));

    try {
        $usuario = DashboardFacade::getInstance()->autenticarAcceso($email, $password);
        return jsonResponse($response, [
            "error"   => false,
            "mensaje" => "Autenticación exitosa al Tablero de Control.",
            "usuario" => $usuario
        ]);
    } catch (InvalidArgumentException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 400);
    } catch (DomainException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 403);
    } catch (RuntimeException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 401);
    } catch (Exception $e) {
        error_log("Dashboard login error: " . $e->getMessage());
        return jsonResponse($response, ["error" => true, "mensaje" => "Error interno al autenticar en el tablero."], 500);
    }
});

// ─── 2. NIVEL 1: VISTA GLOBAL / PANORÁMICA DE UNIVERSIDADES ──────────────────
$app->get('/dashboard/global', function (Request $request, Response $response) {
    if (!isAuthStaff($request)) {
        return jsonResponse($response, ["error" => true, "mensaje" => "Acceso denegado: Se requieren permisos institucionales o administrativos."], 403);
    }

    try {
        $data = DashboardFacade::getInstance()->obtenerPanoramaGlobal();
        return jsonResponse($response, array_merge(["error" => false], $data));
    } catch (Exception $e) {
        error_log("Dashboard global error: " . $e->getMessage());
        return jsonResponse($response, ["error" => true, "mensaje" => "Error al obtener la vista global del tablero."], 500);
    }
})->add(new AuthMiddleware());

// ─── 3. NIVEL 2: DRILL-DOWN UNIVERSIDAD (SEDES Y CARRERAS) ────────────────────
$app->get('/dashboard/universidad/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
    if (!isAuthStaff($request)) {
        return jsonResponse($response, ["error" => true, "mensaje" => "Acceso denegado: Se requieren permisos institucionales o administrativos."], 403);
    }

    $idUniversidad = (int)($args['id'] ?? 0);
    try {
        $data = DashboardFacade::getInstance()->obtenerDesgloseUniversidad($idUniversidad);
        return jsonResponse($response, array_merge(["error" => false], $data));
    } catch (InvalidArgumentException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 400);
    } catch (RuntimeException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 404);
    } catch (Exception $e) {
        error_log("Dashboard universidad error: " . $e->getMessage());
        return jsonResponse($response, ["error" => true, "mensaje" => "Error al obtener el desglose de la universidad."], 500);
    }
})->add(new AuthMiddleware());

// ─── 4. NIVEL 3: DRILL-DOWN DETALLE CARRERA/SEDE (PUBLICACIONES E INTERACCIONES)
$app->get('/dashboard/carrera-detalle/{idSede:[0-9]+}/{idCarrera:[0-9]+}', function (Request $request, Response $response, array $args) {
    if (!isAuthStaff($request)) {
        return jsonResponse($response, ["error" => true, "mensaje" => "Acceso denegado: Se requieren permisos institucionales o administrativos."], 403);
    }

    $idSede    = (int)($args['idSede'] ?? 0);
    $idCarrera = (int)($args['idCarrera'] ?? 0);
    try {
        $data = DashboardFacade::getInstance()->obtenerDetalleCarrera($idSede, $idCarrera);
        return jsonResponse($response, array_merge(["error" => false], $data));
    } catch (InvalidArgumentException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 400);
    } catch (RuntimeException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 404);
    } catch (Exception $e) {
        error_log("Dashboard carrera-detalle error: " . $e->getMessage());
        return jsonResponse($response, ["error" => true, "mensaje" => "Error al obtener el detalle de la carrera."], 500);
    }
})->add(new AuthMiddleware());
