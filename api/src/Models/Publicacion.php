<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Universidad.php';
require_once __DIR__ . '/Comentario.php';

abstract class Publicacion {
    protected ?int $idPublicacion;
    protected string $texto;
    protected string $fechaCreacion;
    protected string $horaCreacion;
    protected int $cantidadLikes;
    
    // Relaciones
    protected Usuario $redactor;
    protected Universidad $universidad;
    protected array $comentarios = [];

    public function __construct(Usuario $redactor, string $texto, Universidad $universidad) {
        $this->redactor = $redactor;
        $this->texto = $texto;
        $this->universidad = $universidad;
        $this->fechaCreacion = date('Y-m-d');
        $this->horaCreacion = date('H:i:s');
        $this->cantidadLikes = 0;
    }

    public function getTexto(): string { return $this->texto; }
    public function getRedactor(): Usuario { return $this->redactor; }
    public function getUniversidad(): Universidad { return $this->universidad; }
    public function getIdPublicacion(): ?int { return $this->idPublicacion; }
    public function getCantidadLikes(): int { return $this->cantidadLikes; }

    public function setTexto(string $texto): void {
        if (strlen($texto) < 10) throw new Exception("El texto es muy corto.");
        $this->texto = $texto;
    }

    public function sumarReaccion(): void {
        $this->cantidadLikes++;
    }

    public function getComentarios(): array { return $this->comentarios; }
    public function agregarComentario(Comentario $comentario): void {
        $this->comentarios[] = $comentario;
    }
}