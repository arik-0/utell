<?php
require_once __DIR__ . '/Carrera.php';

class OfertaAcademica {
    private Carrera $carrera;
    private string $fechaImplementacion;

    public function __construct(Carrera $carrera, string $fechaImplementacion) {
        $this->carrera = $carrera;
        $this->fechaImplementacion = $fechaImplementacion;
    }

    public function getCarrera(): Carrera { return $this->carrera; }
    public function getFechaImplementacion(): string { return $this->fechaImplementacion; }
}
