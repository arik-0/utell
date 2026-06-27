<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "u-tell";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add titulo column
$conn->query("ALTER TABLE consultas ADD COLUMN titulo VARCHAR(255) DEFAULT NULL AFTER hora;");

// Make Universidad, Sede, Carrera nullable to support UI "Opcional"
$conn->query("ALTER TABLE consultas MODIFY idUniversidad INT(11) NULL;");
$conn->query("ALTER TABLE consultas MODIFY idSede INT(11) NULL;");
$conn->query("ALTER TABLE consultas MODIFY idCarrera INT(11) NULL;");

echo "Consultas table updated!";
$conn->close();
?>
