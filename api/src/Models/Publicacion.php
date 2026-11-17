<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Universidad.php';
require_once __DIR__ . '/Sede.php';
require_once __DIR__ . '/Carrera.php';
require_once __DIR__ . '/Comentario.php';

abstract class Publicacion {
    protected ?int $idPublicacion;
    protected string $titulo;
    protected string $texto;
    protected string $fechaCreacion;
    protected string $horaCreacion;
    protected int $cantidadLikes;
    protected string $estado;

    // Relaciones tipadas y listas (alineadas con Diagrama de Dominio y Clases)
    protected ?Usuario $redactor;
    protected ?Universidad $universidad;
    protected ?Sede $sede;
    protected ?Carrera $carrera;
    /** @var Comentario[] */
    protected array $comentarios = [];
    /** @var ReaccionPublicacion[] */
    protected array $reacciones = [];

    public function __construct(
        ?Usuario $redactor,
        string $texto,
        ?Universidad $universidad = null,
        ?Sede $sede = null,
        ?Carrera $carrera = null,
        string $titulo = '',
        ?int $idPublicacion = null,
        ?string $fechaCreacion = null,
        ?string $horaCreacion = null,
        int $cantidadLikes = 0,
        string $estado = 'activo'
    ) {
        $this->idPublicacion = $idPublicacion;
        $this->redactor      = $redactor;
        $this->titulo        = $titulo;
        $this->texto         = $texto;
        $this->universidad   = $universidad;
        $this->sede          = $sede;
        $this->carrera       = $carrera;
        $this->fechaCreacion = $fechaCreacion ?? date('Y-m-d');
        $this->horaCreacion  = $horaCreacion ?? date('H:i:s');
        $this->cantidadLikes = $cantidadLikes;
        $this->estado        = $estado;
    }

    abstract public function getTipoPublicacion(): string;

    public function getIdPublicacion(): ?int { return $this->idPublicacion; }
    public function getTitulo(): string { return $this->titulo; }
    public function getTexto(): string { return $this->texto; }
    public function getFechaCreacion(): string { return $this->fechaCreacion; }
    public function getHoraCreacion(): string { return $this->horaCreacion; }
    public function getCantidadLikes(): int { return $this->cantidadLikes; }
    public function getEstado(): string { return $this->estado; }
    public function getRedactor(): ?Usuario { return $this->redactor; }
    public function getUniversidad(): ?Universidad { return $this->universidad; }
    public function getSede(): ?Sede { return $this->sede; }
    public function getCarrera(): ?Carrera { return $this->carrera; }

    public function setTexto(string $texto): void {
        if (strlen(trim($texto)) < 1) {
            throw new InvalidArgumentException("El texto de la publicación no puede estar vacío.");
        }
        $this->texto = $texto;
    }

    public function validarContenido(): bool {
        return trim($this->texto) !== '' || trim($this->titulo) !== '';
    }

    public function sumarReaccion(): void {
        $this->cantidadLikes++;
    }

    /** @return Comentario[] */
    public function getComentarios(): array { return $this->comentarios; }
    public function agregarComentario(Comentario $comentario): void {
        $this->comentarios[] = $comentario;
    }

    /** @return ReaccionPublicacion[] */
    public function getReacciones(): array { return $this->reacciones; }
    public function agregarReaccion(ReaccionPublicacion $reaccion): void {
        $this->reacciones[] = $reaccion;
    }
}