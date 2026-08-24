<?php
require_once __DIR__ . '/db.php';

try {
    $pdo = db::getInstance()->getConnection();
    $sql = file_get_contents(__DIR__ . '/seed_database.sql');
    
    // Split and execute multi-queries cleanly
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec($sql);
    
    echo "Seed ejecutado con éxito en UTF-8 puro.\n";
} catch (Exception $e) {
    echo "Error en seed: " . $e->getMessage() . "\n";
}
