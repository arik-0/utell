<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/AuthService.php';

// ─── POST /login ──────────────────────────────────────────────────────────────
$app->post('/login', function(Request $request, Response $response) {
    $email    = $request->getParam('email');
    $password = $request->getParam('password');

    try {
        $authService = AuthService::getInstance();
        $usuario     = $authService->login($email, $password);

        if ($usuario) {
            echo json_encode($usuario);
        } else {
            echo json_encode("Error de email y/o contrasena");
        }
    } catch (PDOException $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
});

// ─── POST /login/google ───────────────────────────────────────────────────────
// Receives a Google Identity JWT credential, decodes it without signature
// verification (suitable for trusted localhost dev; add proper verification
// using a JWT library like firebase/php-jwt for production).
$app->post('/login/google', function(Request $request, Response $response) {
    $credential = $request->getParam('credential');

    if (empty($credential)) {
        echo '{"error":{"text":"No se recibio el token de Google."}}';
        return;
    }

    try {
        // Decode JWT payload (middle segment, base64url-encoded)
        $parts = explode('.', $credential);
        if (count($parts) !== 3) {
            throw new Exception('Token de Google invalido.');
        }

        // base64url to base64 standard
        $payload = base64_decode(str_pad(
            str_replace(['-', '_'], ['+', '/'], $parts[1]),
            strlen($parts[1]) % 4 === 0 ? strlen($parts[1]) : strlen($parts[1]) + 4 - strlen($parts[1]) % 4,
            '='
        ));

        $data = json_decode($payload, true);
        if (!$data || empty($data['email'])) {
            throw new Exception('No se pudo decodificar el token de Google.');
        }

        $authService = AuthService::getInstance();
        $usuario     = $authService->loginOrCreateGoogle(
            $data['email'],
            $data['name']  ?? '',
            $data['given_name']  ?? '',
            $data['family_name'] ?? '',
            $data['picture'] ?? ''
        );

        echo json_encode($usuario);
    } catch (Exception $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
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

    try {
        $authService = AuthService::getInstance();
        $exito       = $authService->registro($datos);

        if ($exito) {
            echo json_encode("Nuevo usuario guardado.");
        } else {
            echo json_encode("Error al guardar el usuario.");
        }
    } catch (Exception $e) {
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
});
