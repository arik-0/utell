<?php
/**
 * Plantilla de configuración de Base de Datos para InfinityFree / Producción.
 * 
 * INSTRUCCIONES:
 * 1. En InfinityFree, crea tu base de datos MySQL desde el panel de control.
 * 2. Copia este archivo como `db-config.php` en la misma carpeta (`api/src/config/`).
 * 3. Completa los valores con los datos que te da InfinityFree (Panel -> MySQL Databases).
 */

return [
    'DB_HOST' => 'sqlXXX.epizy.com',         // Host MySQL de InfinityFree (ej: sql105.epizy.com)
    'DB_USER' => 'epiz_XXXXXXXX',            // Usuario MySQL de InfinityFree (ej: epiz_34567890)
    'DB_PASS' => 'tu_contraseña_de_panel',   // Contraseña de tu cuenta de InfinityFree
    'DB_NAME' => 'epiz_XXXXXXXX_utell',      // Nombre de la base de datos creada en el panel
    'DB_PORT' => '3306'                      // Puerto estándar MySQL
];
