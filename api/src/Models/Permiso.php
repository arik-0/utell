<?php

class Permiso {
    private string $nombre;
    private string $codigoAccion;

    public function __construct(string $nombre, string $codigoAccion) {
        $this->nombre = $nombre;
        $this->codigoAccion = $codigoAccion;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getCodigoAccion(): string { return $this->codigoAccion; }
}
