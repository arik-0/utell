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

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// ─── 1. LOGIN DE ACCESO AL TABLERO (SOLO UNIVERSIDADES Y ADMINISTRADORES/MODERADORES) ──
$app->post('/dashboard/login', function (Request $request, Response $response) {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $request->getParsedBody() ?? $_POST ?? [];

    $email = trim($body['email'] ?? $body['usuario'] ?? '');
    $password = trim($body['password'] ?? $body['clave'] ?? '');

    try {
        $usuario = DashboardFacade::getInstance()->autenticarAcceso($email, $password);
        return jsonResponse($response, [
            "error" => false,
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
        return jsonResponse($response, ["error" => true, "mensaje" => "Error interno: " . $e->getMessage()], 500);
    }
});

// ─── 2. NIVEL 1: VISTA GLOBAL / PANORÁMICA DE UNIVERSIDADES ──────────────────
$app->get('/dashboard/global', function (Request $request, Response $response) {
    try {
        $data = DashboardFacade::getInstance()->obtenerPanoramaGlobal();
        return jsonResponse($response, array_merge(["error" => false], $data));
    } catch (Exception $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => "Error al obtener vista global: " . $e->getMessage()], 500);
    }
})->add(new AuthMiddleware());

// ─── 3. NIVEL 2: DRILL-DOWN UNIVERSIDAD (SEDES Y CARRERAS) ────────────────────
$app->get('/dashboard/universidad/{id}', function (Request $request, Response $response, array $args) {
    $idUniversidad = intval($args['id'] ?? 0);

    try {
        $data = DashboardFacade::getInstance()->obtenerDesgloseUniversidad($idUniversidad);
        return jsonResponse($response, array_merge(["error" => false], $data));
    } catch (InvalidArgumentException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 400);
    } catch (RuntimeException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 404);
    } catch (Exception $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => "Error al obtener detalle de universidad: " . $e->getMessage()], 500);
    }
})->add(new AuthMiddleware());

// ─── 4. NIVEL 3: DRILL-DOWN DETALLE CARRERA/SEDE (PUBLICACIONES E INTERACCIONES)
$app->get('/dashboard/carrera-detalle/{idSede}/{idCarrera}', function (Request $request, Response $response, array $args) {
    $idSede = intval($args['idSede'] ?? 0);
    $idCarrera = intval($args['idCarrera'] ?? 0);

    try {
        $data = DashboardFacade::getInstance()->obtenerDetalleCarrera($idSede, $idCarrera);
        return jsonResponse($response, array_merge(["error" => false], $data));
    } catch (InvalidArgumentException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 400);
    } catch (RuntimeException $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => $e->getMessage()], 404);
    } catch (Exception $e) {
        return jsonResponse($response, ["error" => true, "mensaje" => "Error al obtener detalle de carrera: " . $e->getMessage()], 500);
    }
})->add(new AuthMiddleware());