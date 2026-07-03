<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Publicacion.php';

class Comentario {
    private string $texto;
    private string $fecha;
    private Usuario $autor;
    private Publicacion $publicacion;

    public function __construct(Usuario $autor, Publicacion $publicacion, string $texto) {
        $this->autor = $autor;
        $this->publicacion = $publicacion;
        $this->texto = $texto;
        $this->fecha = date('Y-m-d H:i:s');
    }

    public function getTexto(): string { return $this->texto; }
    public function getFecha(): string { return $this->fecha; }
    public function getAutor(): Usuario { return $this->autor; }
    public function getPublicacion(): Publicacion { return $this->publicacion; }
}