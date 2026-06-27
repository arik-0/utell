<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Cargar el servicio de autenticación
require_once __DIR__ . '/../Services/AuthService.php';

// POST Login (Seguro contra Inyección SQL)
$app->post('/login', function(Request $request, Response $response){
  $email = $request->getParam('email');
  $password = $request->getParam('password');

  try{
    $authService = AuthService::getInstance();
    $usuario = $authService->login($email, $password);

    if ($usuario){
      echo json_encode($usuario);
    }else {
      echo json_encode("Error de email y/o contraseña");
    }
  }catch(PDOException $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 

// POST Crear nuevo usuario 
$app->post('/usuarios/nuevo', function(Request $request, Response $response){
   $datos = [
       'nombre' => $request->getParam('nombre'),
       'apellido' => $request->getParam('apellido'),
       'fNac' => $request->getParam('fNac'),
       'email' => $request->getParam('email'),
       'password' => $request->getParam('password'),
       'fotoPerfil' => $request->getParam('fotoPerfil') ?? '', 
       'celular' => $request->getParam('celular') ?? '',
       'tipoPerfil' => $request->getParam('tipoPerfil'),
       'descripcion' => $request->getParam('descripcion') ?? '',
       'trayectoria' => $request->getParam('trayectoria') ?? '',
       'pais' => $request->getParam('pais'),
       'provincia' => $request->getParam('provincia'),
       'ciudad' => $request->getParam('ciudad')
   ];

  try{
    $authService = AuthService::getInstance();
    $exito = $authService->registro($datos);
    
    if ($exito) {
        echo json_encode("Nuevo usuario guardado.");  
    } else {
        echo json_encode("Error al guardar el usuario.");
    }
  }catch(Exception $e){
    echo '{"error" : {"text":"'.$e->getMessage().'"}}';
  }
}); 
