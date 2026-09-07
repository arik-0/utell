<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

require_once __DIR__ . '/../Services/AuthService.php';

// ─── Helper: write JSON response ──────────────────────────────────────────────
if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// ─── POST /login ──────────────────────────────────────────────────────────────
$app->post('/login', function(Request $request, Response $response) {
    $email    = trim($request->getParam('email') ?? '');
    $password = $request->getParam('password') ?? '';

    if (empty($email) || empty($password)) {
        return jsonResponse($response, ['error' => ['text' => 'Email y contraseña son requeridos.']], 400);
    }

    try {
        $authService = AuthService::getInstance();
        $usuario     = $authService->login($email, $password);

        if ($usuario) {
            return jsonResponse($response, $usuario);
        } else {
            return jsonResponse($response, ['error' => ['text' => 'Email o contraseña incorrectos.']], 401);
        }
    } catch (Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// ─── POST /login/google ───────────────────────────────────────────────────────
// Verifica el Google Identity JWT usando las claves públicas de Google (JWKS).
// Si GOOGLE_CLIENT_ID no está configurado, el login con Google queda deshabilitado.
$app->post('/login/google', function(Request $request, Response $response) {
    $credential = $request->getParam('credential') ?? '';

    if (empty($credential)) {
        return jsonResponse($response, ['error' => ['text' => 'Token de Google no recibido.']], 400);
    }

    $clientId = getenv('GOOGLE_CLIENT_ID');
    if (empty($clientId)) {
        return jsonResponse($response, ['error' => ['text' => 'Login con Google no está configurado en el servidor.']], 501);
    }

    try {
        // Obtener JWKS de Google (con caché básico en archivo)
        $jwksCache = sys_get_temp_dir() . '/google_jwks_cache.json';
        $cacheLife = 3600; // 1 hora

        if (file_exists($jwksCache) && (time() - filemtime($jwksCache)) < $cacheLife) {
            $jwksJson = file_get_contents($jwksCache);
        } else {
            $ch = curl_init('https://www.googleapis.com/oauth2/v3/certs');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $jwksJson = curl_exec($ch);
            if (curl_errno($ch) || !$jwksJson) {
                throw new Exception('No se pudo obtener las claves públicas de Google.');
            }
            curl_close($ch);
            file_put_contents($jwksCache, $jwksJson);
        }

        $jwks = json_decode($jwksJson, true);
        if (empty($jwks['keys'])) {
            throw new Exception('JWKS de Google inválido.');
        }

        // Verificar token con firebase/php-jwt
        $keys = JWK::parseKeySet($jwks);
        $decoded = (array) JWT::decode($credential, $keys);

        // Validar audience y emisor
        if (($decoded['aud'] ?? '') !== $clientId) {
            throw new Exception('Token de Google no es válido para esta aplicación.');
        }
        if (!in_array($decoded['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new Exception('Emisor del token de Google no reconocido.');
        }
        if (empty($decoded['email'])) {
            throw new Exception('El token de Google no contiene un email válido.');
        }

        $authService = AuthService::getInstance();
        $usuario     = $authService->loginOrCreateGoogle(
            $decoded['email'],
            $decoded['name']        ?? '',
            $decoded['given_name']  ?? '',
            $decoded['family_name'] ?? '',
            $decoded['picture']     ?? ''
        );

        return jsonResponse($response, $usuario);

    } catch (Exception $e) {
        return jsonResponse($response, ['error' => ['text' => 'Error al verificar token de Google: ' . $e->getMessage()]], 401);
    }
});

// ─── POST /usuarios/nuevo ─────────────────────────────────────────────────────
$app->post('/usuarios/nuevo', function(Request $request, Response $response) {
    $datos = [
        'nombre'         => $request->getParam('nombre'),
        'apellido'       => $request->getParam('apellido'),
        'fNac'           => $request->getParam('fNac'),
        'email'          => $request->getParam('email'),
        'password'       => $request->getParam('password'),
        'fotoPerfil'     => $request->getParam('fotoPerfil')   ?? '',
        'celular'        => $request->getParam('celular')      ?? '',
        'tipoPerfil'     => $request->getParam('tipoPerfil'),
        'descripcion'    => $request->getParam('descripcion')  ?? '',
        'trayectoria'    => $request->getParam('trayectoria')  ?? '',
        'pais'           => $request->getParam('pais'),
        'provincia'      => $request->getParam('provincia'),
        'ciudad'         => $request->getParam('ciudad'),
        'idUniversidad'  => $request->getParam('idUniversidad') ?? null,
        'idCarrera'      => $request->getParam('idCarrera')     ?? null,
    ];

    // Validaciones mínimas
    if (empty($datos['nombre']) || empty($datos['email']) || empty($datos['password'])) {
        return jsonResponse($response, ['error' => ['text' => 'Nombre, email y contraseña son requeridos.']], 400);
    }

    try {
        $authService = AuthService::getInstance();
        $exito       = $authService->registro($datos);

        if ($exito) {
            return jsonResponse($response, ['mensaje' => 'Nuevo usuario guardado.'], 201);
        } else {
            return jsonResponse($response, ['error' => ['text' => 'Error al guardar el usuario.']], 500);
        }
    } catch (Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});
