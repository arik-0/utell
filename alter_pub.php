<?php
$conn = new mysqli("localhost", "root", "", "u-tell");
// Add titulo
$conn->query("ALTER TABLE publicaciones ADD COLUMN titulo VARCHAR(255) DEFAULT NULL AFTER tipoPublicacion;");
// Make optional
$conn->query("ALTER TABLE publicaciones MODIFY idUniversidad INT(11) NULL;");
$conn->query("ALTER TABLE publicaciones MODIFY idSede INT(11) NULL;");
$conn->query("ALTER TABLE publicaciones MODIFY idCarrera INT(11) NULL;");
echo "Done";
?>
