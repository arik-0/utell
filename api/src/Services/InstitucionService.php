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

    public function crearUniversidad(string $nombre, ?string $email, ?string $celular, string $estado = 'activo', ?int $idActor = null): int {
        return $this->repo->crearUniversidad($nombre, $email, $celular, $estado, $idActor);
    }

    public function actualizarUniversidad(int $id, string $nombre, ?string $email, ?string $celular, string $estado = 'activo', ?int $idActor = null): bool {
        return $this->repo->actualizarUniversidad($id, $nombre, $email, $celular, $estado, $idActor);
    }

    public function eliminarUniversidad(int $id, ?int $idActor = null): bool {
        return $this->repo->eliminarUniversidad($id, $idActor);
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

    public function crearCarrera(string $nombre, ?int $idActor = null): int {
        return $this->repo->crearCarrera($nombre, $idActor);
    }

    public function actualizarCarrera(int $idCarrera, string $nombre, ?int $idActor = null): bool {
        return $this->repo->actualizarCarrera($idCarrera, $nombre, $idActor);
    }

    public function eliminarCarrera(int $idCarrera, ?int $idActor = null): bool {
        return $this->repo->eliminarCarrera($idCarrera, $idActor);
    }

    public function obtenerSedes(): array {
        return $this->repo->obtenerSedes();
    }

    public function crearSede(
        int $idUniversidad,
        string $nombre,
        ?int $idCiudad = null,
        ?string $telefono = null,
        ?string $email = null,
        ?string $web = null,
        string $estado = 'activo',
        ?int $idActor = null
    ): int {
        return $this->repo->crearSede($idUniversidad, $nombre, $idCiudad, $telefono, $email, $web, $estado, $idActor);
    }

    public function actualizarSede(
        int $idSede,
        int $idUniversidad,
        string $nombre,
        ?int $idCiudad = null,
        ?string $telefono = null,
        ?string $email = null,
        ?string $web = null,
        string $estado = 'activo',
        ?int $idActor = null
    ): bool {
        return $this->repo->actualizarSede($idSede, $idUniversidad, $nombre, $idCiudad, $telefono, $email, $web, $estado, $idActor);
    }

    public function eliminarSede(int $idSede, ?int $idActor = null): bool {
        return $this->repo->eliminarSede($idSede, $idActor);
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

    public function obtenerOfertaAcademica(): array {
        return $this->repo->obtenerOfertaAcademica();
    }

    public function crearOfertaAcademica(
        int $idCarrera,
        int $idSede,
        ?int $anio,
        ?string $descripcion = null,
        ?string $web = null,
        ?string $telefono = null,
        ?int $idActor = null
    ): bool {
        return $this->repo->crearOfertaAcademica($idCarrera, $idSede, $anio, $descripcion, $web, $telefono, $idActor);
    }

    public function actualizarOfertaAcademica(
        int $idOferta,
        int $idCarrera,
        int $idSede,
        ?int $anio,
        ?string $descripcion = null,
        ?string $web = null,
        ?string $telefono = null,
        ?int $idActor = null
    ): bool {
        return $this->repo->actualizarOfertaAcademica($idOferta, $idCarrera, $idSede, $anio, $descripcion, $web, $telefono, $idActor);
    }

    public function eliminarOfertaAcademica(int $idOferta, ?int $idActor = null): bool {
        return $this->repo->eliminarOfertaAcademica($idOferta, $idActor);
    }

    public function resolverIdsAcademicos(
        ?int $idUniversidad,
        ?int $idSede,
        ?int $idCarrera,
        ?string $nombreUniversidad = null,
        ?string $nombreSede = null,
        ?string $nombreCarrera = null
    ): array {
        return $this->repo->resolverIdsAcademicos(
            $idUniversidad,
            $idSede,
            $idCarrera,
            $nombreUniversidad,
            $nombreSede,
            $nombreCarrera
        );
    }

    public function obtenerPaises(): array {
        return $this->repo->obtenerPaises();
    }

    public function crearPais(string $nombre, ?int $idActor = null): int {
        return $this->repo->crearPais($nombre, $idActor);
    }

    public function actualizarPais(int $idPais, string $nombre, ?int $idActor = null): bool {
        return $this->repo->actualizarPais($idPais, $nombre, $idActor);
    }

    public function eliminarPais(int $idPais, ?int $idActor = null): bool {
        return $this->repo->eliminarPais($idPais, $idActor);
    }

    public function obtenerProvincias(): array {
        return $this->repo->obtenerProvincias();
    }

    public function crearProvincia(string $nombre, int $idPais, ?int $idActor = null): int {
        return $this->repo->crearProvincia($nombre, $idPais, $idActor);
    }

    public function actualizarProvincia(int $idProvincia, string $nombre, int $idPais, ?int $idActor = null): bool {
        return $this->repo->actualizarProvincia($idProvincia, $nombre, $idPais, $idActor);
    }

    public function eliminarProvincia(int $idProvincia, ?int $idActor = null): bool {
        return $this->repo->eliminarProvincia($idProvincia, $idActor);
    }

    public function obtenerCiudades(): array {
        return $this->repo->obtenerCiudades();
    }

    public function crearCiudad(string $nombre, int $idProvincia, ?int $idActor = null): int {
        return $this->repo->crearCiudad($nombre, $idProvincia, $idActor);
    }

    public function actualizarCiudad(int $idCiudad, string $nombre, int $idProvincia, ?int $idActor = null): bool {
        return $this->repo->actualizarCiudad($idCiudad, $nombre, $idProvincia, $idActor);
    }

    public function eliminarCiudad(int $idCiudad, ?int $idActor = null): bool {
        return $this->repo->eliminarCiudad($idCiudad, $idActor);
    }
}
