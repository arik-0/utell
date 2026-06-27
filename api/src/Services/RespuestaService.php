<?php
require_once __DIR__ . '/../Repositories/RespuestaRepository.php';

class RespuestaService {
    private static $instance = null;
    private RespuestaRepository $respuestaRepository;

    private function __construct() {
        $this->respuestaRepository = new RespuestaRepository();
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerRespuestasPorPublicacion(int $idPublicacion) {
        return $this->respuestaRepository->obtenerRespuestasPorPublicacion($idPublicacion);
    }

    public function crearRespuesta(array $datos): bool {
        return $this->respuestaRepository->crearRespuesta($datos);
    }
}
