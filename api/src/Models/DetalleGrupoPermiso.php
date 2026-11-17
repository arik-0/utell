<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/GrupoPermiso.php';

class DetalleGrupoPermiso {
    private string $fechaAsignacion;
    private string $usuarioAsignador;
    private Usuario $usuario;
    private GrupoPermiso $grupoPermiso;

    public function __construct(
        Usuario $usuario,
        GrupoPermiso $grupoPermiso,
        string $usuarioAsignador = 'Sistema',
        ?string $fechaAsignacion = null
    ) {
        $this->usuario          = $usuario;
        $this->grupoPermiso     = $grupoPermiso;
        $this->usuarioAsignador = $usuarioAsignador;
        $this->fechaAsignacion  = $fechaAsignacion ?? date('Y-m-d H:i:s');
    }

    public function getFechaAsignacion(): string { return $this->fechaAsignacion; }
    public function getUsuarioAsignador(): string { return $this->usuarioAsignador; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function getGrupoPermiso(): GrupoPermiso { return $this->grupoPermiso; }
    public function esVigente(): bool { return $this->fechaAsignacion !== ''; }
}
