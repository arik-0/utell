<?php
require_once __DIR__ . '/Credencial.php';
require_once __DIR__ . '/GrupoPermiso.php';

/**
 * Entidad Intermedia: AsignacionGrupoUsuario (vincula Credencial con GrupoPermiso).
 */
class AsignacionGrupoUsuario {
    private string $fechaAsignacion;
    private string $estadoVigencia;
    private Credencial $credencial;
    private GrupoPermiso $grupoPermiso;

    public function __construct(
        Credencial $credencial,
        GrupoPermiso $grupoPermiso,
        string $estadoVigencia = 'activo',
        ?string $fechaAsignacion = null
    ) {
        $this->credencial      = $credencial;
        $this->grupoPermiso    = $grupoPermiso;
        $this->estadoVigencia  = $estadoVigencia;
        $this->fechaAsignacion = $fechaAsignacion ?? date('Y-m-d H:i:s');
    }

    public function getFechaAsignacion(): string { return $this->fechaAsignacion; }
    public function getEstadoVigencia(): string { return $this->estadoVigencia; }
    public function getCredencial(): Credencial { return $this->credencial; }
    public function getGrupoPermiso(): GrupoPermiso { return $this->grupoPermiso; }

    public function revocar(): void {
        $this->estadoVigencia = 'inactivo';
    }
}
