<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../Services/UsuarioService.php';

// GET Todos los usuarios 
$app->get('/usuarios', function(Request $request, Response $response){
  try{
    $usuarioService = UsuarioService::getInstance();
    $usuarios = $usuarioService->obtenerTodosLosUsuarios();

    if (count($usuarios) > 0){
      echo json_encode($usuarios);
    }else {
      echo json_encode("No existen usuarios en la BBDD.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

// GET Recuperar usuario por ID 
$app->get('/usuarios/{id}', function(Request $request, Response $response){
  $id = (int)$request->getAttribute('id');
  try{
    $usuarioService = UsuarioService::getInstance();
    $usuario = $usuarioService->obtenerUsuarioPorId($id);

    if ($usuario){
      echo json_encode([$usuario]); // Wrapped in array for backwards compatibility with fetchAll
    }else {
      echo json_encode("No existen usuarios en la BBDD con este ID.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

// PUT Modificar usuario / editarPerfil
$app->put('/editarPerfil/{id}', function(Request $request, Response $response){
  $id = (int)$request->getAttribute('id');
  $datos = [
      'nombre' => $request->getParam('nombre'),
      'apellido' => $request->getParam('apellido'),
      'fNac' => $request->getParam('fNac'),
      'email' => $request->getParam('email'),
      'password' => $request->getParam('password'),
      'fotoPerfil' => $request->getParam('fotoPerfil'),
      'celular' => $request->getParam('celular'),
      'tipoPerfil' => $request->getParam('tipoPerfil'),
      'descripcion' => $request->getParam('descripcion'),
      'trayectoria' => $request->getParam('trayectoria'),
      'idCiudad' => $request->getParam('idCiudad')
  ];
  
  try{
    $usuarioService = UsuarioService::getInstance();
    $modificado = $usuarioService->actualizarUsuario($id, $datos);

    if ($modificado) {
        echo json_encode("Usuario modificado.");  
    } else {
        echo json_encode("No se pudo modificar el usuario.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

// PUT Modificar usuario (Duplicated route from original code)
$app->put('/usuarios/modificar/{id}', function(Request $request, Response $response){
  $id = (int)$request->getAttribute('id');
  $datos = [
      'nombre' => $request->getParam('nombre'),
      'apellido' => $request->getParam('apellido'),
      'fNac' => $request->getParam('fNac'),
      'email' => $request->getParam('email'),
      'password' => $request->getParam('password'),
      'fotoPerfil' => $request->getParam('fotoPerfil'),
      'celular' => $request->getParam('celular'),
      'tipoPerfil' => $request->getParam('tipoPerfil'),
      'descripcion' => $request->getParam('descripcion'),
      'trayectoria' => $request->getParam('trayectoria'),
      'idCiudad' => $request->getParam('idCiudad')
  ];
  
  try{
    $usuarioService = UsuarioService::getInstance();
    $modificado = $usuarioService->actualizarUsuario($id, $datos);

    if ($modificado) {
        echo json_encode("Usuario modificado.");  
    } else {
        echo json_encode("No se pudo modificar el usuario.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

// DELETE borrar usuario 
$app->delete('/usuarios/delete/{id}', function(Request $request, Response $response){
   $id = (int)$request->getAttribute('id');
      
  try{
    $usuarioService = UsuarioService::getInstance();
    $eliminado = $usuarioService->eliminarUsuario($id);

    if ($eliminado) {
      echo json_encode("Usuario eliminado.");  
    }else {
      echo json_encode("No existe usuario con este ID.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});

// GET Favoritos (Esto se movería idealmente a un FavoritosService, mantenido aquí temporalmente)
$app->get('/favoritos', function(Request $request, Response $response){
  $sql = "SELECT * FROM favoritos";
  try{
    $db = db::getInstance()->getConnection();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0){
      $favoritos = $resultado->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($favoritos);
    }else {
      echo json_encode("No existen favoritos en la BBDD.");
    }
    $resultado = null;
    $db = null;
  }catch(PDOException $e){
    echo '{"error" : {"text":'.$e->getMessage().'}';
  }
});

// GET Mensaje Directo (Mantenido temporalmente)
$app->get('/mensajedirecto', function(Request $request, Response $response){
  $sql = "SELECT * FROM mensajedirecto";
  try{
    $db = db::getInstance()->getConnection();
    $resultado = $db->query($sql);

    if ($resultado->rowCount() > 0){
      $mensajedirecto = $resultado->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($mensajedirecto);
    }else {
      echo json_encode("No existen mensajes en la BBDD.");
    }
    $resultado = null;
    $db = null;
  }catch(PDOException $e){
    echo '{"error" : {"text":'.$e->getMessage().'}';
  }
});

$app->get('/perfil/{id}', function(Request $request, Response $response){
  $id = (int)$request->getAttribute('id');
  try{
    // Reutilizamos el servicio para traer el usuario, aunque solo pida algunas columnas.
    // En N-Capas, las vistas (Frontend) deciden qué datos usar del objeto de dominio.
    $usuarioService = UsuarioService::getInstance();
    $usuario = $usuarioService->obtenerUsuarioPorId($id);

    if ($usuario){
      echo json_encode([$usuario]);
    }else {
      echo json_encode("No existen usuarios en la BBDD con este ID.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
});