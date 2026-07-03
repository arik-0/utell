<?php

class GrupoPermiso {
    private string $nombre;
    private string $descripcion;
    
    private array $permisos = [];

    public function __construct(string $nombre, string $descripcion) {
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): string { return $this->descripcion; }

    public function getPermisos(): array { return $this->permisos; }
    public function agregarPermiso(Permiso $permiso): void {
        $this->permisos[] = $permiso;
    }
}
