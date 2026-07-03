<?php
require_once __DIR__ . '/Publicacion.php';

class Experiencia extends Publicacion {
    private int $puntuacionClima;
    private int $puntuacionInfraestructura;
    private int $puntuacionAcademica;

    public function __construct(Usuario $redactor, string $texto, Universidad $universidad, int $clima, int $infra, int $acad) {
        parent::__construct($redactor, $texto, $universidad);
        $this->puntuacionClima = $clima;
        $this->puntuacionInfraestructura = $infra;
        $this->puntuacionAcademica = $acad;
    }

    public function calcularPromedio(): float {
        return ($this->puntuacionClima + $this->puntuacionInfraestructura + $this->puntuacionAcademica) / 3;
    }

    public function getPuntuacionClima(): int { return $this->puntuacionClima; }
    public function getPuntuacionInfraestructura(): int { return $this->puntuacionInfraestructura; }
    public function getPuntuacionAcademica(): int { return $this->puntuacionAcademica; }
}