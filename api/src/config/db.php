<?php
  class db{
    private static $instance = null;
    private $connection = null;

    private $dbHost = 'localhost';
    private $dbUser = 'root';
    private $dbPass = '';
    private $dbName = 'u-tell';

    // Constructor privado con tolerancia automática a nombres de BD ('u-tell' o 'utell')
    private function __construct() {
      $databases = [getenv('DB_NAME') ?: null, $this->dbName, 'utell', 'u_tell'];
      $connected = false;
      $lastException = null;

      foreach (array_filter($databases) as $dbname) {
        try {
          $mysqlConnect = "mysql:host={$this->dbHost};dbname={$dbname};charset=utf8mb4";
          $this->connection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
          ]);
          $connected = true;
          $this->dbName = $dbname;
          break;
        } catch (PDOException $e) {
          $lastException = $e;
        }
      }

      if (!$connected && $lastException) {
        throw $lastException;
      }
    }

    // Prevenir la clonación
    private function __clone() {}

    // Método para obtener la única instancia de la clase
    public static function getInstance() {
      if (self::$instance == null) {
        self::$instance = new self();
      }
      return self::$instance;
    }

    // Método para obtener la conexión a la base de datos
    public function getConnection() {
      return $this->connection;
    }
  }