<?php
require_once __DIR__ . '/Ciudad.php';
require_once __DIR__ . '/OfertaAcademica.php';

class Sede {
    private string $nombre;
    private ?string $telefono;
    private ?Universidad $universidad;
    private Ciudad $ciudad;

    /** @var OfertaAcademica[] Multiplicidad 0..* de Ofertas Academicas */
    private array $ofertas = [];
    /** @var Publicacion[] */
    private array $publicaciones = [];
    /** @var Respuesta[] */
    private array $respuestas = [];
    /** @var InscripcionAcademica[] */
    private array $inscripciones = [];

    public function __construct(string $nombre, ?string $telefono, Ciudad $ciudad, ?Universidad $universidad = null) {
        $this->nombre      = $nombre;
        $this->telefono    = $telefono;
        $this->ciudad      = $ciudad;
        $this->universidad = $universidad;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getTelefono(): ?string { return $this->telefono; }
    public function getCiudad(): Ciudad { return $this->ciudad; }
    public function getUniversidad(): ?Universidad { return $this->universidad; }
    public function setUniversidad(?Universidad $universidad): void { $this->universidad = $universidad; }

    /** @return OfertaAcademica[] */
    public function getOfertas(): array { return $this->ofertas; }
    public function agregarOferta(OfertaAcademica $oferta): void {
        $this->ofertas[] = $oferta;
    }

    /** @return Publicacion[] */
    public function getPublicaciones(): array { return $this->publicaciones; }
    public function agregarPublicacion(Publicacion $publicacion): void { $this->publicaciones[] = $publicacion; }

    /** @return Respuesta[] */
    public function getRespuestas(): array { return $this->respuestas; }
    public function agregarRespuesta(Respuesta $respuesta): void { $this->respuestas[] = $respuesta; }

    /** @return InscripcionAcademica[] */
    public function getInscripciones(): array { return $this->inscripciones; }
    public function agregarInscripcion(InscripcionAcademica $inscripcion): void { $this->inscripciones[] = $inscripcion; }
}
