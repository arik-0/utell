<?php
require_once __DIR__ . '/GrupoPermiso.php';
require_once __DIR__ . '/Permiso.php';

/**
 * Entidad Intermedia: GrupoPermisoPermiso (atributos propios de la relación Grupo ↔ Permiso).
 */
class GrupoPermisoPermiso {
    private string $fechaInclusion;
    private bool $soloLectura;
    private GrupoPermiso $grupoPermiso;
    private Permiso $permiso;

    public function __construct(
        GrupoPermiso $grupoPermiso,
        Permiso $permiso,
        bool $soloLectura = false,
        ?string $fechaInclusion = null
    ) {
        $this->grupoPermiso   = $grupoPermiso;
        $this->permiso        = $permiso;
        $this->soloLectura    = $soloLectura;
        $this->fechaInclusion = $fechaInclusion ?? date('Y-m-d H:i:s');
    }

    public function getFechaInclusion(): string { return $this->fechaInclusion; }
    public function isSoloLectura(): bool { return $this->soloLectura; }
    public function getGrupoPermiso(): GrupoPermiso { return $this->grupoPermiso; }
    public function getPermiso(): Permiso { return $this->permiso; }
    public function estaActivo(): bool { return true; }
}
