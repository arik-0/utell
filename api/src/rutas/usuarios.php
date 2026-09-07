<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/UsuarioService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}

// GET Todos los usuarios — publico
$app->get('/usuarios', function(Request $request, Response $response) {
    try {
        $usuarioService = UsuarioService::getInstance();
        $usuarios = $usuarioService->obtenerTodosLosUsuarios();
        return jsonResponse($response, $usuarios);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// GET Perfil completo por ID — publico
$app->get('/usuarios/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        $usuarioService = UsuarioService::getInstance();
        $usuario = $usuarioService->obtenerPerfilCompleto($id);
        if ($usuario) return jsonResponse($response, $usuario);
        return jsonResponse($response, ['error' => ['text' => 'No existe usuario con este ID.']], 404);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// GET Perfil por ID (alias) — publico
$app->get('/perfil/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        $usuarioService = UsuarioService::getInstance();
        $usuario = $usuarioService->obtenerUsuarioPorId($id);
        if ($usuario) return jsonResponse($response, [$usuario]);
        return jsonResponse($response, ['error' => ['text' => 'No existe usuario con este ID.']], 404);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
});

// PUT Editar perfil — requiere JWT
$app->put('/editarPerfil/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    $datos = [
        'nombre'      => $request->getParam('nombre'),
        'apellido'    => $request->getParam('apellido'),
        'fNac'        => $request->getParam('fNac'),
        'email'       => $request->getParam('email'),
        'password'    => $request->getParam('password'),
        'fotoPerfil'  => $request->getParam('fotoPerfil'),
        'celular'     => $request->getParam('celular'),
        'tipoPerfil'  => $request->getParam('tipoPerfil'),
        'descripcion' => $request->getParam('descripcion'),
        'trayectoria' => $request->getParam('trayectoria'),
        'idCiudad'    => $request->getParam('idCiudad'),
    ];
    try {
        $modificado = UsuarioService::getInstance()->actualizarUsuario($id, $datos);
        if ($modificado) return jsonResponse($response, ['mensaje' => 'Usuario modificado.']);
        return jsonResponse($response, ['error' => ['text' => 'No se pudo modificar el usuario.']], 400);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// DELETE Borrar usuario — requiere JWT
$app->delete('/usuarios/delete/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        $eliminado = UsuarioService::getInstance()->eliminarUsuario($id);
        if ($eliminado) return jsonResponse($response, ['mensaje' => 'Usuario eliminado.']);
        return jsonResponse($response, ['error' => ['text' => 'No existe usuario con este ID.']], 404);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());

// PUT Cambiar estado de cuenta — requiere JWT
$app->put('/usuarios/{id:[0-9]+}/estado', function(Request $request, Response $response, array $args) {
    $id     = (int)$args['id'];
    $accion = $request->getParam('accion');
    if (empty($accion)) {
        return jsonResponse($response, ['error' => ['text' => 'El campo accion es requerido (bloquear o activar).']], 400);
    }
    try {
        $resultado = UsuarioService::getInstance()->cambiarEstadoCuenta($id, $accion);
        if ($resultado) return jsonResponse($response, ['mensaje' => "Estado de cuenta actualizado a '{$accion}'."]);
        return jsonResponse($response, ['error' => ['text' => 'No se pudo actualizar el estado o el usuario no existe.']], 404);
    } catch(Exception $e) {
        return jsonResponse($response, ['error' => ['text' => $e->getMessage()]], 500);
    }
})->add(new AuthMiddleware());
