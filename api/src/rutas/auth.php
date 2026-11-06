<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

require_once __DIR__ . '/../Services/AuthService.php';

// ─── POST /login (y alias /auth/login) ────────────────────────────────────────
$loginHandler = function(Request $request, Response $response) {
    $email    = trim((string)($request->getParam('email') ?? ''));
    $password = (string)($request->getParam('password') ?? '');

    if ($email === '' || $password === '') {
        return errorResponse($response, 'Email y contraseña son requeridos.', 400);
    }

    try {
        $usuario = AuthService::getInstance()->login($email, $password);
        if ($usuario) {
            return jsonResponse($response, $usuario);
        }
        return errorResponse($response, 'Email o contraseña incorrectos.', 401);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 403);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al procesar el inicio de sesión.', 500, $e);
    }
};
$app->post('/login', $loginHandler);
$app->post('/auth/login', $loginHandler);

// ─── POST /login/google (y alias /auth/google) ────────────────────────────────
$googleLoginHandler = function(Request $request, Response $response) {
    $credential = (string)($request->getParam('credential') ?? '');

    if ($credential === '') {
        return errorResponse($response, 'Token de Google no recibido.', 400);
    }

    $clientId = getenv('GOOGLE_CLIENT_ID');
    if (empty($clientId)) {
        return errorResponse($response, 'Login con Google no está configurado en el servidor.', 501);
    }

    try {
        $jwksCache = sys_get_temp_dir() . '/google_jwks_cache.json';
        $cacheLife = 3600;

        if (file_exists($jwksCache) && (time() - filemtime($jwksCache)) < $cacheLife) {
            $jwksJson = file_get_contents($jwksCache);
        } else {
            $ch = curl_init('https://www.googleapis.com/oauth2/v3/certs');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $jwksJson = curl_exec($ch);
            $curlErr  = curl_errno($ch);
            curl_close($ch);
            if ($curlErr || !$jwksJson) {
                throw new RuntimeException('No se pudo obtener las claves públicas de Google.');
            }
            file_put_contents($jwksCache, $jwksJson);
        }

        $jwks = json_decode($jwksJson, true);
        if (empty($jwks['keys'])) {
            throw new RuntimeException('JWKS de Google inválido.');
        }

        $keys    = JWK::parseKeySet($jwks);
        $decoded = (array) JWT::decode($credential, $keys);

        if (($decoded['aud'] ?? '') !== $clientId) {
            throw new DomainException('Token de Google no es válido para esta aplicación.');
        }
        if (!in_array($decoded['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new DomainException('Emisor del token de Google no reconocido.');
        }
        if (empty($decoded['email'])) {
            throw new DomainException('El token de Google no contiene un email válido.');
        }

        $usuario = AuthService::getInstance()->loginOrCreateGoogle(
            $decoded['email'],
            $decoded['name']        ?? '',
            $decoded['given_name']  ?? '',
            $decoded['family_name'] ?? '',
            $decoded['picture']     ?? ''
        );

        return jsonResponse($response, $usuario);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 403);
    } catch (Exception $e) {
        return errorResponse($response, 'No fue posible verificar la credencial de Google.', 401, $e);
    }
};
$app->post('/login/google', $googleLoginHandler);
$app->post('/auth/google', $googleLoginHandler);

// ─── POST /usuarios/nuevo ─────────────────────────────────────────────────────
$app->post('/usuarios/nuevo', function(Request $request, Response $response) {
    $datos = [
        'nombre'        => $request->getParam('nombre'),
        'apellido'      => $request->getParam('apellido'),
        'fNac'          => $request->getParam('fNac'),
        'email'         => $request->getParam('email'),
        'password'      => $request->getParam('password'),
        'fotoPerfil'    => $request->getParam('fotoPerfil')    ?? '',
        'celular'       => $request->getParam('celular')       ?? '',
        'tipoPerfil'    => $request->getParam('tipoPerfil'),
        'descripcion'   => $request->getParam('descripcion')   ?? '',
        'trayectoria'   => $request->getParam('trayectoria')   ?? '',
        'pais'          => $request->getParam('pais'),
        'provincia'     => $request->getParam('provincia'),
        'ciudad'        => $request->getParam('ciudad'),
        'idUniversidad' => $request->getParam('idUniversidad') ?? null,
        'idCarrera'     => $request->getParam('idCarrera')     ?? null,
    ];

    if (empty(trim((string)$datos['nombre'])) || empty(trim((string)$datos['email'])) || empty($datos['password'])) {
        return errorResponse($response, 'Nombre, email y contraseña son requeridos.', 400);
    }
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        return errorResponse($response, 'El formato del correo electrónico no es válido.', 400);
    }

    try {
        $exito = AuthService::getInstance()->registro($datos);
        if ($exito) {
            return jsonResponse($response, ['mensaje' => 'Nuevo usuario guardado.'], 201);
        }
        return errorResponse($response, 'No se pudo completar el registro del usuario.', 500);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 409);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al registrar el usuario.', 500, $e);
    }
});

// ─── POST /logout (y alias /auth/logout) — Auditoría Login-Logout (Punto T06) ──
$logoutHandler = function(Request $request, Response $response) {
    $idUsuario = (int)($request->getParam('idUsuario') ?? 0);
    $email     = trim((string)($request->getParam('email') ?? ''));

    try {
        require_once __DIR__ . '/../Repositories/AuditoriaRepository.php';
        (new AuditoriaRepository())->registrar(
            $idUsuario > 0 ? $idUsuario : null,
            'LOGOUT',
            'Cierre de sesión voluntario' . ($email !== '' ? " ({$email})" : ''),
            'usuarios',
            null,
            json_encode(['sesion' => 'ACTIVA']),
            json_encode(['sesion' => 'CERRADA_LOGOUT'])
        );
        return jsonResponse($response, ['success' => true, 'mensaje' => 'Sesión cerrada y registrada en auditoría.']);
    } catch (Exception $e) {
        return jsonResponse($response, ['success' => true]);
    }
};
$app->post('/logout', $logoutHandler);
$app->post('/auth/logout', $logoutHandler);

// ─── POST /recuperar-clave — Recuperación de contraseña (Puntos T02 y T03) ────
$recuperarClaveHandler = function(Request $request, Response $response) {
    $email      = trim((string)($request->getParam('email') ?? ''));
    $fNac       = trim((string)($request->getParam('fNac') ?? ''));
    $nuevaClave = trim((string)($request->getParam('nuevaClave') ?? ''));

    if ($email === '' || strlen($nuevaClave) < 6) {
        return errorResponse($response, 'Debes ingresar un email válido y una nueva contraseña de al menos 6 caracteres.', 400);
    }

    try {
        require_once __DIR__ . '/../Services/SeguridadService.php';
        $ok = SeguridadService::getInstance()->recuperarClavePorEmail($email, $fNac, $nuevaClave);
        return jsonResponse($response, [
            'success' => $ok,
            'mensaje' => 'Contraseña restablecida con éxito. Ya puedes iniciar sesión.'
        ]);
    } catch (DomainException $e) {
        return errorResponse($response, $e->getMessage(), 400);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al procesar la recuperación de contraseña.', 500, $e);
    }
};
$app->post('/recuperar-clave', $recuperarClaveHandler);
$app->post('/auth/recuperar-clave', $recuperarClaveHandler);
