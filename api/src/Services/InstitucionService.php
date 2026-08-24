<?php
require_once __DIR__ . '/../Repositories/InstitucionRepository.php';

class InstitucionService {
    private static $instance = null;
    private InstitucionRepository $repo;

    private function __construct() {
        $this->repo = new InstitucionRepository();
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerUniversidades(): array {
        return $this->repo->obtenerUniversidades();
    }

    public function obtenerUniversidadesConExperiencias(): array {
        return $this->repo->obtenerUniversidadesConExperiencias();
    }

    public function obtenerMuroUniversidad(int $idUniversidad, int $idUsuario = 0): ?array {
        return $this->repo->obtenerMuroUniversidad($idUniversidad, $idUsuario);
    }

    public function obtenerCarreras(): array {
        return $this->repo->obtenerCarreras();
    }

    public function obtenerSedes(): array {
        return $this->repo->obtenerSedes();
    }

    public function obtenerSedesPorUniversidad(int $idUniversidad): array {
        return $this->repo->obtenerSedesPorUniversidad($idUniversidad);
    }

    public function obtenerCarrerasPorSede(int $idSede): array {
        return $this->repo->obtenerCarrerasPorSede($idSede);
    }

    public function obtenerSedesPorCarrera(int $idCarrera): array {
        return $this->repo->obtenerSedesPorCarrera($idCarrera);
    }

    public function crearOfertaAcademica(int $idCarrera, int $idSede, ?int $anio): bool {
        return $this->repo->crearOfertaAcademica($idCarrera, $idSede, $anio);
    }

    public function eliminarOfertaAcademica(int $idOferta): bool {
        return $this->repo->eliminarOfertaAcademica($idOferta);
    }

    public function obtenerPaises(): array {
        return $this->repo->obtenerPaises();
    }

    public function obtenerProvincias(): array {
        return $this->repo->obtenerProvincias();
    }

    public function obtenerCiudades(): array {
        return $this->repo->obtenerCiudades();
    }
}
