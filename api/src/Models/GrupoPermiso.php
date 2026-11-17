<?php
require_once __DIR__ . '/Permiso.php';

class GrupoPermiso {
    private string $nombre;
    private string $descripcion;

    /** @var Permiso[] */
    private array $permisos = [];
    /** @var DetalleGrupoPermiso[] */
    private array $detallesUsuario = [];
    /** @var GrupoPermisoPermiso[] */
    private array $detallesPermiso = [];

    public function __construct(string $nombre, string $descripcion) {
        $this->nombre      = $nombre;
        $this->descripcion = $descripcion;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): string { return $this->descripcion; }

    /** @return Permiso[] */
    public function getPermisos(): array { return $this->permisos; }
    public function agregarPermiso(Permiso $permiso): void {
        $this->permisos[] = $permiso;
    }

    /** @return DetalleGrupoPermiso[] */
    public function getDetallesUsuario(): array { return $this->detallesUsuario; }
    public function agregarDetalleUsuario(DetalleGrupoPermiso $detalle): void { $this->detallesUsuario[] = $detalle; }

    /** @return GrupoPermisoPermiso[] */
    public function getDetallesPermiso(): array { return $this->detallesPermiso; }
    public function agregarDetallePermiso(GrupoPermisoPermiso $detalle): void {
        $this->detallesPermiso[] = $detalle;
        $this->permisos[] = $detalle->getPermiso();
    }

    /**
     * Patrón Composite RBAC: verifica si el grupo contiene un código de permiso específico.
     */
    public function tienePermiso(string $codigoAccion): bool {
        foreach ($this->permisos as $permiso) {
            if ($permiso->getCodigoAccion() === $codigoAccion || $permiso->getCodigoAccion() === 'ADMIN_FULL') {
                return true;
            }
        }
        return false;
    }
}
