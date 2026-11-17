<?php

class Carrera {
    private string $nombre;
    /** @var OfertaAcademica[] */
    private array $ofertasAcademicas = [];
    /** @var Publicacion[] */
    private array $publicaciones = [];
    /** @var InscripcionAcademica[] */
    private array $inscripciones = [];

    public function __construct(string $nombre) {
        $this->nombre = $nombre;
    }

    public function getNombre(): string { return $this->nombre; }

    /** @return OfertaAcademica[] */
    public function getOfertasAcademicas(): array { return $this->ofertasAcademicas; }
    public function agregarOfertaAcademica(OfertaAcademica $oferta): void { $this->ofertasAcademicas[] = $oferta; }

    /** @return Publicacion[] */
    public function getPublicaciones(): array { return $this->publicaciones; }
    public function agregarPublicacion(Publicacion $publicacion): void { $this->publicaciones[] = $publicacion; }

    /** @return InscripcionAcademica[] */
    public function getInscripciones(): array { return $this->inscripciones; }
    public function agregarInscripcion(InscripcionAcademica $inscripcion): void { $this->inscripciones[] = $inscripcion; }
}
