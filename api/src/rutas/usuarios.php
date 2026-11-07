<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/UsuarioService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// GET Todos los usuarios — público
$app->get('/usuarios', function(Request $request, Response $response) {
    try {
        $usuarios = UsuarioService::getInstance()->obtenerTodosLosUsuarios();
        return jsonResponse($response, $usuarios);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al consultar el listado de usuarios.', 500, $e);
    }
});

// GET Perfil completo por ID — público
$app->get('/usuarios/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        $usuario = UsuarioService::getInstance()->obtenerPerfilCompleto($id);
        if ($usuario) {
            return jsonResponse($response, $usuario);
        }
        return errorResponse($response, 'No existe usuario con este ID.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al obtener el perfil del usuario.', 500, $e);
    }
});

// GET Perfil por ID (alias) — público
$app->get('/perfil/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id = (int)$args['id'];
    try {
        $usuario = UsuarioService::getInstance()->obtenerUsuarioPorId($id);
        if ($usuario) {
            return jsonResponse($response, [$usuario]);
        }
        return errorResponse($response, 'No existe usuario con este ID.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error al obtener los datos del perfil.', 500, $e);
    }
});

// PUT Editar perfil — requiere JWT (solo titular o administrador/moderador)
$app->put('/editarPerfil/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id     = (int)$args['id'];
    $authId = getAuthUserId($request);

    if ($authId !== $id && !isAuthAdminOrMod($request)) {
        return errorResponse($response, 'No tienes permisos para modificar otro perfil.', 403);
    }

    $datos = [
        'nombre'        => $request->getParam('nombre'),
        'apellido'      => $request->getParam('apellido'),
        'fNac'          => $request->getParam('fNac'),
        'email'         => $request->getParam('email'),
        'password'      => $request->getParam('password'),
        'fotoPerfil'    => $request->getParam('fotoPerfil'),
        'celular'       => $request->getParam('celular'),
        'tipoPerfil'    => $request->getParam('tipoPerfil'),
        'descripcion'   => $request->getParam('descripcion'),
        'trayectoria'   => $request->getParam('trayectoria'),
        'idCiudad'      => $request->getParam('idCiudad'),
        'idUniversidad' => $request->getParam('idUniversidad'),
        'idCarrera'     => $request->getParam('idCarrera'),
    ];

    try {
        $modificado = UsuarioService::getInstance()->actualizarUsuario($id, $datos);
        if ($modificado) {
            return jsonResponse($response, ['mensaje' => 'Usuario modificado.']);
        }
        return errorResponse($response, 'No se pudo modificar el usuario.', 400);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al actualizar el perfil.', 500, $e);
    }
})->add(new AuthMiddleware());

// DELETE Baja lógica de usuario — requiere JWT (solo titular o administrador/moderador)
$app->delete('/usuarios/delete/{id:[0-9]+}', function(Request $request, Response $response, array $args) {
    $id     = (int)$args['id'];
    $authId = getAuthUserId($request);

    if ($authId !== $id && !isAuthAdminOrMod($request)) {
        return errorResponse($response, 'No tienes permisos para dar de baja esta cuenta.', 403);
    }

    try {
        $eliminado = UsuarioService::getInstance()->eliminarUsuario($id, $authId);
        if ($eliminado) {
            return jsonResponse($response, ['mensaje' => 'Usuario dado de baja correctamente.']);
        }
        return errorResponse($response, 'No existe usuario activo con este ID.', 404);
    } catch (Exception $e) {
        return errorResponse($response, 'Error interno al dar de baja el usuario.', 500, $e);
    }
})->add(new AuthMiddleware());

