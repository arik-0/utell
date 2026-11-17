<?php
require_once __DIR__ . '/Publicacion.php';

class Experiencia extends Publicacion {
    private float $puntuacionClima;
    private float $puntuacionInfraestructura;
    private float $puntuacionAcademica;

    public function __construct(
        ?Usuario $redactor,
        string $texto,
        ?Universidad $universidad = null,
        float $clima = 5.0,
        float $infra = 5.0,
        float $acad = 5.0,
        ?Sede $sede = null,
        ?Carrera $carrera = null,
        string $titulo = '',
        ?int $idPublicacion = null,
        ?string $fechaCreacion = null,
        ?string $horaCreacion = null,
        int $cantidadLikes = 0,
        string $estado = 'activo'
    ) {
        parent::__construct(
            $redactor,
            $texto,
            $universidad,
            $sede,
            $carrera,
            $titulo,
            $idPublicacion,
            $fechaCreacion,
            $horaCreacion,
            $cantidadLikes,
            $estado
        );
        $this->puntuacionClima           = min(5.0, max(1.0, $clima));
        $this->puntuacionInfraestructura = min(5.0, max(1.0, $infra));
        $this->puntuacionAcademica       = min(5.0, max(1.0, $acad));
    }

    public function getTipoPublicacion(): string {
        return 'Experiencia';
    }

    public function calcularPromedio(): float {
        return round(($this->puntuacionClima + $this->puntuacionInfraestructura + $this->puntuacionAcademica) / 3.0, 2);
    }

    public function validarRangosPuntuacion(): bool {
        return $this->puntuacionClima >= 1.0 && $this->puntuacionClima <= 5.0
            && $this->puntuacionInfraestructura >= 1.0 && $this->puntuacionInfraestructura <= 5.0
            && $this->puntuacionAcademica >= 1.0 && $this->puntuacionAcademica <= 5.0;
    }

    public function getPuntuacionClima(): float { return $this->puntuacionClima; }
    public function getPuntuacionInfraestructura(): float { return $this->puntuacionInfraestructura; }
    public function getPuntuacionAcademica(): float { return $this->puntuacionAcademica; }
}