<?php
require_once __DIR__ . '/Carrera.php';

class OfertaAcademica {
    private Carrera $carrera;
    private ?Sede $sede;
    private string $fechaImplementacion;
    private string $modalidad;
    private string $estadoCurricula;

    public function __construct(
        Carrera $carrera,
        string $fechaImplementacion,
        ?Sede $sede = null,
        string $modalidad = 'Presencial',
        string $estadoCurricula = 'vigente'
    ) {
        $this->carrera             = $carrera;
        $this->fechaImplementacion = $fechaImplementacion;
        $this->sede                = $sede;
        $this->modalidad           = $modalidad;
        $this->estadoCurricula     = $estadoCurricula;
    }

    public function getCarrera(): Carrera { return $this->carrera; }
    public function getSede(): ?Sede { return $this->sede; }
    public function setSede(?Sede $sede): void { $this->sede = $sede; }
    public function getFechaImplementacion(): string { return $this->fechaImplementacion; }
    public function getModalidad(): string { return $this->modalidad; }
    public function getEstadoCurricula(): string { return $this->estadoCurricula; }
    public function estaVigente(): bool { return $this->estadoCurricula === 'vigente'; }
}
