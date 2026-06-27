<?php
class Auditoria {
    private int $idUsuario;
    private string $tablaAfectada;
    private string $accion; // INSERT, UPDATE, DELETE
    private string $valorOriginal;
    private string $valorNuevo;

    public function __construct($user, $tabla, $accion, $original, $nuevo) {
        $this->idUsuario = $user;
        $this->tablaAfectada = $tabla;
        $this->accion = $accion;
        $this->valorOriginal = $original;
        $this->valorNuevo = $nuevo;
    }
}