<?php
require_once __DIR__ . '/Ciudad.php';
require_once __DIR__ . '/OfertaAcademica.php';

class Sede {
    private string $nombre;
    private ?string $telefono;
    private Ciudad $ciudad;
    
    // Multiplicidad 0..* de Ofertas Academicas
    private array $ofertas = [];

    public function __construct(string $nombre, ?string $telefono, Ciudad $ciudad) {
        $this->nombre = $nombre;
        $this->telefono = $telefono;
        $this->ciudad = $ciudad;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getTelefono(): ?string { return $this->telefono; }
    public function getCiudad(): Ciudad { return $this->ciudad; }
    
    public function getOfertas(): array { return $this->ofertas; }
    public function agregarOferta(OfertaAcademica $oferta): void {
        $this->ofertas[] = $oferta;
    }
}
