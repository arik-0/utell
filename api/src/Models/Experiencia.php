<?php
class Experiencia extends Publicacion {
    private int $puntuacionClima;
    private int $puntuacionInfraestructura;
    private int $puntuacionAcademica;

    public function __construct($idU, $txt, $uni, $sede, $car, int $clima, int $infra, int $acad) {
        parent::__construct($idU, $txt, $uni, $sede, $car);
        $this->puntuacionClima = $clima;
        $this->puntuacionInfraestructura = $infra;
        $this->puntuacionAcademica = $acad;
    }

    public function getPromedio(): float {
        return ($this->puntuacionClima + $this->puntuacionInfraestructura + $this->puntuacionAcademica) / 3;
    }
}