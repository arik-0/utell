<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Publicacion.php';
require_once __DIR__ . '/Sede.php';

class Comentario {
    private string $texto;
    private string $fecha;
    private string $hora;
    private Usuario $autor;
    private Publicacion $publicacion;
    private ?Sede $sede;

    public function __construct(
        Usuario $autor,
        Publicacion $publicacion,
        string $texto,
        ?Sede $sede = null,
        ?string $fecha = null,
        ?string $hora = null
    ) {
        $this->autor       = $autor;
        $this->publicacion = $publicacion;
        $this->texto       = $texto;
        $this->sede        = $sede;
        $this->fecha       = $fecha ?? date('Y-m-d');
        $this->hora        = $hora ?? date('H:i:s');
    }

    public function validarTexto(): bool { return trim($this->texto) !== ''; }
    public function getTexto(): string { return $this->texto; }
    public function getFecha(): string { return $this->fecha; }
    public function getHora(): string { return $this->hora; }
    public function getAutor(): Usuario { return $this->autor; }
    public function getPublicacion(): Publicacion { return $this->publicacion; }
    public function getSede(): ?Sede { return $this->sede; }
}