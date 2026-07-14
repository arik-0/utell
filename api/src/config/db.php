<?php
  class db{
    private static $instance = null;
    private $connection = null;

    private $dbHost ='localhost';
    private $dbUser = 'root';
    private $dbPass = '';
    private $dbName = 'utell';

    // Constructor privado para evitar instanciación externa
    private function __construct() {
      $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName";
      $this->connection = new PDO($mysqlConnect, $this->dbUser, $this->dbPass);
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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