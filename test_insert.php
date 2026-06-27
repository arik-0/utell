<?php
$conn = new mysqli("localhost", "root", "", "u-tell");

$idUsuario = 1; // Assuming a valid user ID
$tipoPublicacion = 'Consulta';
$titulo = 'Test Titulo';
$fecha = date('Y-m-d');
$hora = date('H:i:s');
$idUniversidad = null;
$idSede = null;
$idCarrera = null;
$texto = 'Test Texto';
$likes = 0;

$sql = "INSERT INTO publicaciones (idUsuario, tipoPublicacion, titulo, fecha, hora, idUniversidad, idSede, idCarrera, texto, likes, resuelta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("issssiiisi", $idUsuario, $tipoPublicacion, $titulo, $fecha, $hora, $idUniversidad, $idSede, $idCarrera, $texto, $likes);

if (!$stmt->execute()) {
    echo "Execute failed: " . $stmt->error;
} else {
    echo "Success";
}
?>
