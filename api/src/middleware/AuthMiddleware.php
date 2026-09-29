<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/JWTService.php';

class AuthMiddleware {
    /**
     * Slim 3 middleware: validates Bearer JWT from Authorization header.
     * On success, attaches decoded payload to $request->getAttribute('jwtPayload').
     * On failure, returns 401 JSON immediately.
     */
    public function __invoke(Request $request, Response $response, callable $next): Response {
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            }
        }

        if (empty($authHeader) || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $this->unauthorized($response, 'Token de autenticacion requerido.');
        }

        $token = $matches[1];
        $payload = JWTService::validarToken($token);

        if ($payload === null) {
            return $this->unauthorized($response, 'Token invalido o expirado. Por favor inicie sesion nuevamente.');
        }

        $request = $request->withAttribute('jwtPayload', $payload);
        return $next($request, $response);
    }

    private function unauthorized(Response $response, string $mensaje): Response {
        $response->getBody()->write(json_encode([
            'error' => true,
            'mensaje' => $mensaje
        ], JSON_UNESCAPED_UNICODE));
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
