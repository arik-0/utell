<?php
require_once __DIR__ . '/../Repositories/SeguridadRepository.php';

class SeguridadService {
    private static $instance = null;
    private SeguridadRepository $repo;

    private function __construct() {
        $this->repo = new SeguridadRepository();
    }

    private function __clone() {}

    public static function getInstance(): SeguridadService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerPermisosUsuario(int $idUsuario): array {
        return $this->repo->obtenerPermisosUsuario($idUsuario);
    }

    public function tienePermiso(int $idUsuario, string $codigoAccion): bool {
        return $this->repo->tienePermiso($idUsuario, $codigoAccion);
    }

    public function obtenerRoles(): array {
        return $this->repo->obtenerRoles();
    }

    public function obtenerGruposPermiso(): array {
        return $this->repo->obtenerGruposPermiso();
    }

    public function asignarGrupo(int $idUsuario, int $idGrupoPermiso, ?int $idActor = null): bool {
        return $this->repo->asignarGrupoAUsuario($idUsuario, $idGrupoPermiso, $idActor);
    }

    public function moderarPublicacion(int $idModerador, int $idPublicacion, string $accion, ?string $motivo = null): array {
        return $this->repo->moderarPublicacion($idModerador, $idPublicacion, $accion, $motivo);
    }
}
