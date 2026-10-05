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