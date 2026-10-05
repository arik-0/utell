<?php
  class db {
    private static $instance = null;
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

      // Support direct configuration file (crucial for InfinityFree / shared hosting without getenv)
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

      foreach ($databases as $dbname) {
        try {
          $mysqlConnect = "mysql:host={$this->dbHost};port={$this->dbPort};dbname={$dbname};charset=utf8mb4";
          $this->connection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
          ]);
          $this->connection->exec("SET NAMES utf8mb4");
          $connected = true;
          $this->dbName = $dbname;
          self::asegurarEsquema($this->connection);
          break;
        } catch (PDOException $e) {
          $lastException = $e;
        }
      }

      if (!$connected && $lastException) {
        throw $lastException;
      }
    }

    private static $esquemaListo = false;

    private static function asegurarEsquema(PDO $pdo): void {
      if (self::$esquemaListo) {
        return;
      }
      self::$esquemaListo = true;

      try {
        // Verificar si existe la tabla publicaciones
        $checkPub = $pdo->query("SHOW TABLES LIKE 'publicaciones'")->fetch();
        if (!$checkPub) {
          return;
        }

        // Verificar si ya existen las columnas de puntuación
        $checkCol = $pdo->query("SHOW COLUMNS FROM `publicaciones` LIKE 'puntuacionAcademica'")->fetch();
        if (!$checkCol) {
          // Agregar columnas a publicaciones
          try { $pdo->exec("ALTER TABLE `publicaciones` ADD COLUMN `puntuacionClima` TINYINT NULL DEFAULT 5"); } catch (Exception $e) {}
          try { $pdo->exec("ALTER TABLE `publicaciones` ADD COLUMN `puntuacionInfraestructura` TINYINT NULL DEFAULT 5"); } catch (Exception $e) {}
          try { $pdo->exec("ALTER TABLE `publicaciones` ADD COLUMN `puntuacionAcademica` TINYINT NULL DEFAULT 5"); } catch (Exception $e) {}

          // Si existe la tabla experiencias, sincronizar las puntuaciones históricas
          $checkExp = $pdo->query("SHOW TABLES LIKE 'experiencias'")->fetch();
          if ($checkExp) {
            try {
              $pdo->exec("
                UPDATE `publicaciones` p
                JOIN `experiencias` e ON e.idExperiencia = p.idPublicacion
                SET p.puntuacionClima = e.puntuacionClima,
                    p.puntuacionInfraestructura = e.puntuacionInfraestructura,
                    p.puntuacionAcademica = e.puntuacionAcademica
                WHERE p.tipoPublicacion = 'Experiencia'
              ");
            } catch (Exception $e) {}
          }

          // Asignar default a experiencias sin puntuación
          try {
            $pdo->exec("
              UPDATE `publicaciones`
              SET puntuacionClima = 5, puntuacionInfraestructura = 5, puntuacionAcademica = 5
              WHERE tipoPublicacion = 'Experiencia' AND puntuacionClima IS NULL
            ");
          } catch (Exception $e) {}
        }

        // Asegurar coherencia de sedes en publicaciones existentes
        try {
          // Si Medicina UNR está en sede 1 (Rectorado), asociar a sede 2 (FCM UNR)
          $pdo->exec("UPDATE `publicaciones` SET `idSede` = 2, `idCarrera` = 1 WHERE `idPublicacion` IN (1, 2, 3, 13) AND `idUniversidad` = 1 AND `idSede` = 1");
          // Si Sistemas UTN no tiene sede 7, asignar sede 7 (FRBA)
          $pdo->exec("UPDATE `publicaciones` SET `idSede` = 7, `idCarrera` = 2 WHERE `idPublicacion` IN (4, 5) AND `idUniversidad` = 3 AND (`idSede` IS NULL OR `idSede` = 1)");
          // Sincronizar sedes nulas desde oferta_academica
          $pdo->exec("
            UPDATE `publicaciones` p
            JOIN `oferta_academica` oa ON p.idCarrera = oa.idCarrera
            JOIN `sedes` s ON oa.idSede = s.idSede AND p.idUniversidad = s.idUniversidad
            SET p.idSede = oa.idSede
            WHERE p.idSede IS NULL AND p.idCarrera IS NOT NULL
          ");
        } catch (Exception $e) {}

        // Asegurar sedes y oferta académica de UAI (idUniversidad = 8) si no existen
        try {
          $checkUai = (int)$pdo->query("SELECT COUNT(*) FROM `sedes` WHERE `idUniversidad` = 8")->fetchColumn();
          if ($checkUai === 0) {
            $pdo->exec("
              INSERT IGNORE INTO `sedes` (`idSede`, `idUniversidad`, `idCiudad`, `nombre`, `descripcion`, `email`, `web`, `telefono`, `estado`) VALUES
              (13, 8, 1, 'UAI - Sede Rosario (Campus Pellegrini)', 'Facultad de Tecnología Informática, Medicina y Ciencias Jurídicas', 'rosario@uai.edu.ar', 'https://uai.edu.ar', '+54 341 440-8010', 'activo'),
              (14, 8, 5, 'UAI - Sede Central Buenos Aires (San Telmo)', 'Rectorado y Facultad de Tecnología Informática y Arquitectura', 'buenosaires@uai.edu.ar', 'https://uai.edu.ar', '+54 11 4342-7788', 'activo')
            ");
            $pdo->exec("
              INSERT IGNORE INTO `oferta_academica` (`idCarrera`, `idSede`, `fechaImplementacion`, `descripcion`, `web`, `telefono`) VALUES
              (2, 13, '1995-03-01', 'Ingeniería en Sistemas Informáticos - Cátedra de Bases de Datos Aplicada', 'https://uai.edu.ar', '+54 341 440-8010'),
              (1, 13, '1998-03-01', 'Carrera de Medicina - Hospital Universitario UAI', 'https://uai.edu.ar', '+54 341 440-8011'),
              (4, 13, '1996-03-01', 'Abogacía y Práctica Profesional Supervisada', 'https://uai.edu.ar', '+54 341 440-8012'),
              (2, 14, '1994-03-01', 'Ingeniería en Sistemas Informáticos - Sede San Telmo', 'https://uai.edu.ar', '+54 11 4342-7788')
            ");
          }
        } catch (Exception $e) {}
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