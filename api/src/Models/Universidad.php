<?php
require_once __DIR__ . '/Sede.php';
require_once __DIR__ . '/Ciudad.php';

class Universidad {
    private string $nombre;
    private string $estado;

    /** @var Sede[] Multiplicidad 1..* de sedes */
    private array $sedes = [];
    /** @var Ciudad[] */
    private array $ciudades = [];
    /** @var Publicacion[] */
    private array $publicaciones = [];
    /** @var InscripcionAcademica[] */
    private array $inscripciones = [];

    public function __construct(string $nombre, string $estado = 'activo') {
        $this->nombre = $nombre;
        $this->estado = $estado;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getEstado(): string { return $this->estado; }
    public function estaActiva(): bool { return $this->estado === 'activo'; }

    /** @return Sede[] */
    public function getSedes(): array { return $this->sedes; }
    public function agregarSede(Sede $sede): void {
        $this->sedes[] = $sede;
    }

    /** @return Ciudad[] */
    public function getCiudades(): array { return $this->ciudades; }
    public function agregarCiudad(Ciudad $ciudad): void { $this->ciudades[] = $ciudad; }

    /** @return Publicacion[] */
    public function getPublicaciones(): array { return $this->publicaciones; }
    public function agregarPublicacion(Publicacion $publicacion): void { $this->publicaciones[] = $publicacion; }

    /** @return InscripcionAcademica[] */
    public function getInscripciones(): array { return $this->inscripciones; }
    public function agregarInscripcion(InscripcionAcademica $inscripcion): void { $this->inscripciones[] = $inscripcion; }
}
