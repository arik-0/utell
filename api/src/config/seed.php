<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso permitido únicamente por consola (CLI).');
}

require_once __DIR__ . '/db.php';

try {
    $pdo = db::getInstance()->getConnection();
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");

    $baseDir = dirname(__DIR__, 3) . '/database';
    $archivos = [
        $baseDir . '/01_schema.sql',
        $baseDir . '/02_seed.sql',
    ];

    foreach ($archivos as $ruta) {
        if (!is_file($ruta)) {
            throw new RuntimeException("No se encontró el archivo SQL: {$ruta}");
        }
        $sql = file_get_contents($ruta);
        $pdo->exec($sql);
        echo "Ejecutado correctamente: " . basename($ruta) . "\n";
    }

    echo "Esquema y datos semilla de U-Tell aplicados con éxito en UTF-8 (utf8mb4).\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Error ejecutando migración/seed: " . $e->getMessage() . "\n");
    exit(1);
}
