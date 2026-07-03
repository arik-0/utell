<?php
require_once __DIR__ . '/Sede.php';

class Universidad {
    private string $nombre;
    private string $estado;

    // Multiplicidad 1..* de sedes
    private array $sedes = [];

    public function __construct(string $nombre, string $estado = 'activo') {
        $this->nombre = $nombre;
        $this->estado = $estado;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getEstado(): string { return $this->estado; }

    public function getSedes(): array { return $this->sedes; }
    public function agregarSede(Sede $sede): void {
        $this->sedes[] = $sede;
    }
}
