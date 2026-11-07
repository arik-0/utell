<?php
class db {
    private static $instance = null;
    private static $esquemaListo = false;
    private $connection = null;

    private $dbHost;
    private $dbUser;
    private $dbPass;
    private $dbName;
    private $dbPort;

    private function __construct() {
        $this->dbHost = getenv('DB_HOST') ?: 'localhost';
        $this->dbUser = getenv('DB_USER') ?: 'root';
        $this->dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        $this->dbName = getenv('DB_NAME') ?: 'u-tell';
        $this->dbPort = getenv('DB_PORT') ?: '3306';

        // Support direct configuration file for shared hosting environments without getenv
        if (file_exists(__DIR__ . '/db-config.php')) {
            $cfg = require __DIR__ . '/db-config.php';
            if (is_array($cfg)) {
                $this->dbHost = $cfg['DB_HOST'] ?? $this->dbHost;
                $this->dbUser = $cfg['DB_USER'] ?? $this->dbUser;
                $this->dbPass = $cfg['DB_PASS'] ?? $this->dbPass;
                $this->dbName = $cfg['DB_NAME'] ?? $this->dbName;
                $this->dbPort = $cfg['DB_PORT'] ?? $this->dbPort;
            }
        }

        $databases = array_unique(array_filter([$this->dbName, 'utell', 'u_tell']));
        $connected = false;
        $lastException = null;

        for ($attempt = 1; $attempt <= 3 && !$connected; $attempt++) {
            foreach ($databases as $dbname) {
                try {
                    $mysqlConnect = "mysql:host={$this->dbHost};port={$this->dbPort};dbname={$dbname};charset=utf8mb4";
                    $this->connection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]);
                    $connected = true;
                    $this->dbName = $dbname;
                    self::asegurarEsquema($this->connection, $this->dbHost . '_' . $this->dbName);
                    break 2;
                } catch (PDOException $e) {
                    $lastException = $e;
                    // Si es error de límite de conexiones concurrentes (1040 / 1203), esperamos brevemente y reintentamos el mismo dbname
                    $msg = $e->getMessage();
                    if (strpos($msg, '1203') !== false || strpos($msg, '1040') !== false || stripos($msg, 'max_user_connections') !== false) {
                        break;
                    }
                }
            }
            if (!$connected && $attempt < 3) {
                usleep(180000);
            }
        }

        if (!$connected && $lastException) {
            throw $lastException;
        }
    }

    /**
     * Verifica una sola vez por servidor/base de datos que las columnas requeridas existan
     * (evita ejecutar consultas DDL/SHOW en cada petición HTTP).
     */
    private static function asegurarEsquema(PDO $pdo, string $cacheKey): void {
        if (self::$esquemaListo) {
            return;
        }
        self::$esquemaListo = true;

        $lockFile = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'utell_schema_v2_' . md5($cacheKey) . '.stamp';
        if (is_file($lockFile) && (time() - (int)@filemtime($lockFile)) < 86400) {
            return;
        }

        try {
            $checkPub = $pdo->query("SHOW TABLES LIKE 'publicaciones'")->fetch();
            if (!$checkPub) {
                return;
            }

            // Columnas de compatibilidad en publicaciones
            $pubCols = $pdo->query("SHOW COLUMNS FROM `publicaciones`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('puntuacionClima', $pubCols, true)) {
                try { $pdo->exec("ALTER TABLE `publicaciones` ADD COLUMN `puntuacionClima` TINYINT NULL DEFAULT NULL"); } catch (Exception $e) {}
            }
            if (!in_array('puntuacionInfraestructura', $pubCols, true)) {
                try { $pdo->exec("ALTER TABLE `publicaciones` ADD COLUMN `puntuacionInfraestructura` TINYINT NULL DEFAULT NULL"); } catch (Exception $e) {}
            }
            if (!in_array('puntuacionAcademica', $pubCols, true)) {
                try { $pdo->exec("ALTER TABLE `publicaciones` ADD COLUMN `puntuacionAcademica` TINYINT NULL DEFAULT NULL"); } catch (Exception $e) {}
            }
            if (!in_array('resuelta', $pubCols, true)) {
                try { $pdo->exec("ALTER TABLE `publicaciones` ADD COLUMN `resuelta` TINYINT(1) NOT NULL DEFAULT 0"); } catch (Exception $e) {}
            }
            if (!in_array('estado', $pubCols, true)) {
                try { $pdo->exec("ALTER TABLE `publicaciones` ADD COLUMN `estado` VARCHAR(20) NOT NULL DEFAULT 'activo'"); } catch (Exception $e) {}
            }

            // Columna estadoCuenta en usuarios
            $usrCols = $pdo->query("SHOW COLUMNS FROM `usuarios`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('estadoCuenta', $usrCols, true)) {
                try { $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `estadoCuenta` VARCHAR(30) NOT NULL DEFAULT 'activo'"); } catch (Exception $e) {}
            }

            // Tablas auxiliares de pertenencia y auditoría si faltasen en entornos previos
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `inscripciones` (
                  `idInscripcion` INT(11) NOT NULL AUTO_INCREMENT,
                  `idUsuario` INT(11) NOT NULL,
                  `idUniversidad` INT(11) DEFAULT NULL,
                  `idSede` INT(11) DEFAULT NULL,
                  `idCarrera` INT(11) DEFAULT NULL,
                  `anioIngreso` INT(4) DEFAULT NULL,
                  `estado` VARCHAR(30) NOT NULL DEFAULT 'activo',
                  PRIMARY KEY (`idInscripcion`),
                  UNIQUE KEY `idx_usuario_inscripcion` (`idUsuario`),
                  KEY `idx_inscripcion_uni` (`idUniversidad`),
                  KEY `idx_inscripcion_car` (`idCarrera`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `log_auditoria` (
                  `idLog` INT(11) NOT NULL AUTO_INCREMENT,
                  `idUsuario` INT(11) DEFAULT NULL,
                  `tablaAfectada` VARCHAR(60) DEFAULT 'general',
                  `accion` VARCHAR(80) NOT NULL,
                  `valorOriginal` TEXT DEFAULT NULL,
                  `valorNuevo` TEXT DEFAULT NULL,
                  `detalle` TEXT DEFAULT NULL,
                  `fechaHora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `ip` VARCHAR(45) DEFAULT NULL,
                  PRIMARY KEY (`idLog`),
                  KEY `fk_auditoria_usuario` (`idUsuario`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            @file_put_contents($lockFile, (string)time());
        } catch (Exception $e) {
            error_log("Error en db::asegurarEsquema: " . $e->getMessage());
        }
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
