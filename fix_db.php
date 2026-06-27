<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "u-tell";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure at least one role exists
$conn->query("INSERT IGNORE INTO roles (idRol, nombreRol) VALUES (1, 'Admin'), (2, 'Usuario')");

// Ensure at least one country, province and city exists
$conn->query("INSERT IGNORE INTO paises (idPais, nombre) VALUES (1, 'Argentina')");
$conn->query("INSERT IGNORE INTO provincias (idProvincia, idPais, nombre) VALUES (1, 1, 'Santa Fe')");
$conn->query("INSERT IGNORE INTO ciudades (idCiudad, idProvincia, nombre) VALUES (1, 1, 'Rosario')");

echo "Database fixed!";
$conn->close();
?>
