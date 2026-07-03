<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/GrupoPermiso.php';

class DetalleGrupoPermiso {
    private string $fechaAsignacion;
    private Usuario $usuario;
    private GrupoPermiso $grupoPermiso;

    public function __construct(Usuario $usuario, GrupoPermiso $grupoPermiso) {
        $this->usuario = $usuario;
        $this->grupoPermiso = $grupoPermiso;
        $this->fechaAsignacion = date('Y-m-d H:i:s');
    }

    public function getFechaAsignacion(): string { return $this->fechaAsignacion; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function getGrupoPermiso(): GrupoPermiso { return $this->grupoPermiso; }
}
