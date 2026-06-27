<?php
require_once __DIR__ . '/../Repositories/InstitucionRepository.php';

class InstitucionService {
    private static $instance = null;
    private InstitucionRepository $institucionRepository;

    private function __construct() {
        $this->institucionRepository = new InstitucionRepository();
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerUniversidades() {
        return $this->institucionRepository->obtenerUniversidades();
    }

    public function obtenerCarreras() {
        return $this->institucionRepository->obtenerCarreras();
    }

    public function obtenerSedes() {
        return $this->institucionRepository->obtenerSedes();
    }

    public function obtenerPaises() {
        return $this->institucionRepository->obtenerPaises();
    }

    public function obtenerProvincias() {
        return $this->institucionRepository->obtenerProvincias();
    }

    public function obtenerCiudades() {
        return $this->institucionRepository->obtenerCiudades();
    }
}
