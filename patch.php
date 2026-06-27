<?php
$conn = new mysqli('localhost', 'root', '', 'u-tell');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("ALTER TABLE `sedes` ADD `idCiudad` int(11) DEFAULT NULL AFTER `idUniversidad`");
echo "Alter 1: " . $conn->error . "\n";
$conn->query("ALTER TABLE `sedes` ADD KEY `fk_sede_ciudad` (`idCiudad`)");
echo "Alter 2: " . $conn->error . "\n";
$conn->query("ALTER TABLE `sedes` ADD CONSTRAINT `fk_sede_ciudad` FOREIGN KEY (`idCiudad`) REFERENCES `ciudades` (`idCiudad`) ON DELETE SET NULL ON UPDATE CASCADE");
echo "Alter 3: " . $conn->error . "\n";
echo "Database update complete.\n";
