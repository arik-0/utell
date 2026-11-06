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

    public function obtenerPermisosCatalogo(): array {
        return $this->repo->obtenerPermisosCatalogo();
    }

    public function obtenerGruposPermiso(): array {
        return $this->repo->obtenerGruposPermiso();
    }

    public function crearGrupoPermiso(string $nombre, string $descripcion, array $idsPermisos = [], ?int $idActor = null): array {
        return $this->repo->crearGrupoPermiso($nombre, $descripcion, $idsPermisos, $idActor);
    }

    public function actualizarGrupoPermiso(int $idGrupo, string $nombre, string $descripcion, array $idsPermisos = [], ?int $idActor = null): bool {
        return $this->repo->actualizarGrupoPermiso($idGrupo, $nombre, $descripcion, $idsPermisos, $idActor);
    }

    public function eliminarGrupoPermiso(int $idGrupo, ?int $idActor = null): bool {
        return $this->repo->eliminarGrupoPermiso($idGrupo, $idActor);
    }

    public function asignarGrupo(int $idUsuario, int $idGrupoPermiso, ?int $idActor = null): bool {
        return $this->repo->asignarGrupoAUsuario($idUsuario, $idGrupoPermiso, $idActor);
    }

    public function moderarPublicacion(int $idModerador, int $idPublicacion, string $accion, ?string $motivo = null): array {
        return $this->repo->moderarPublicacion($idModerador, $idPublicacion, $accion, $motivo);
    }

    public function obtenerPublicacionesModerables(): array {
        return $this->repo->obtenerPublicacionesModerables();
    }

    public function obtenerUsuariosAdmin(): array {
        return $this->repo->obtenerUsuariosAdmin();
    }

    public function cambiarRolUsuario(int $idUsuario, int $idRol, ?int $idActor = null): bool {
        return $this->repo->cambiarRolUsuario($idUsuario, $idRol, $idActor);
    }

    public function cambiarEstadoUsuario(int $idUsuario, string $accion, ?int $idActor = null): bool {
        return $this->repo->cambiarEstadoUsuario($idUsuario, $accion, $idActor);
    }

    public function resetearClaveUsuario(int $idUsuario, string $nuevaClave, ?int $idActor = null): bool {
        return $this->repo->resetearClaveUsuario($idUsuario, $nuevaClave, $idActor);
    }

    public function recuperarClavePorEmail(string $email, string $fNac, string $nuevaClave): bool {
        return $this->repo->recuperarClavePorEmail($email, $fNac, $nuevaClave);
    }

    public function obtenerLogsAuditoria(): array {
        return $this->repo->obtenerLogsAuditoria();
    }

    public function generarBackupSql(?int $idActor = null): array {
        return $this->repo->generarBackupSql($idActor);
    }
}
