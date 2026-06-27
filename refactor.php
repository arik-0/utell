<?php
$files = [
    "c:/xampp/htdocs/utell/api/src/rutas/auth.php",
    "c:/xampp/htdocs/utell/api/src/rutas/usuarios.php",
    "c:/xampp/htdocs/utell/api/src/rutas/respuestas.php",
    "c:/xampp/htdocs/utell/api/src/rutas/publicaciones.php",
    "c:/xampp/htdocs/utell/api/src/rutas/instituciones.php",
    "c:/xampp/htdocs/utell/api/src/Repositories/UsuarioRepository.php"
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace $db = new db(); \n $db = $db->conectDB();
    $content = preg_replace('/\$db\s*=\s*new\s+db\(\);\s*\$db\s*=\s*\$db->conectDB\(\);/s', '$db = db::getInstance()->getConnection();', $content);
    
    // Also replace (new db())->conectDB()
    $content = str_replace('(new db())->conectDB()', 'db::getInstance()->getConnection()', $content);
    
    file_put_contents($file, $content);
}
echo "Refactor complete";
