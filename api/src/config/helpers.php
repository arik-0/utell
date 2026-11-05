<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

if (!function_exists('errorResponse')) {
    /**
     * Devuelve un error JSON estandarizado compatible con el frontend (`error.text` y `mensaje`),
     * ocultando detalles internos de SQL/servidor ante fallos 5xx (RNF08).
     */
    function errorResponse(Response $response, string $mensajePublico, int $status = 400, ?Throwable $ex = null): Response {
        if ($ex !== null) {
            error_log(sprintf('[U-Tell API %d] %s — %s', $status, $mensajePublico, $ex->getMessage()));
        }
        return jsonResponse($response, [
            'error'   => ['text' => $mensajePublico],
            'mensaje' => $mensajePublico,
        ], $status);
    }
}

if (!function_exists('getAuthPayload')) {
    function getAuthPayload(Request $request): array {
        $payload = $request->getAttribute('jwtPayload');
        return is_array($payload) ? $payload : [];
    }
}

if (!function_exists('getAuthUserId')) {
    function getAuthUserId(Request $request): int {
        $payload = getAuthPayload($request);
        return isset($payload['idUsuario']) ? (int)$payload['idUsuario'] : 0;
    }
}

if (!function_exists('isAuthAdminOrMod')) {
    function isAuthAdminOrMod(Request $request): bool {
        $payload = getAuthPayload($request);
        $rolId   = (int)($payload['idRol'] ?? 0);
        $perfil  = trim((string)($payload['tipoPerfil'] ?? ''));
        return in_array($rolId, [1, 3], true)
            || in_array($perfil, ['Administrador', 'Moderador'], true);
    }
}

if (!function_exists('isAuthStaff')) {
    function isAuthStaff(Request $request): bool {
        $payload = getAuthPayload($request);
        $rolId   = (int)($payload['idRol'] ?? 0);
        $perfil  = trim((string)($payload['tipoPerfil'] ?? ''));
        return in_array($rolId, [1, 3, 5, 6], true)
            || in_array($perfil, ['Administrador', 'Moderador', 'Universidad', 'Institución', 'Docente'], true);
    }
}

if (!function_exists('getClientIp')) {
    function getClientIp(): ?string {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
}
