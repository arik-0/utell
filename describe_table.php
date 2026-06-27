<?php
$conn = new mysqli("localhost", "root", "", "u-tell");
$res = $conn->query("DESCRIBE favoritos");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?>
