<?php
require_once __DIR__ . '/Publicacion.php';

class Consulta extends Publicacion {
    private bool $estadoResuelta;

    public function __construct(Usuario $redactor, string $texto, Universidad $universidad, bool $estadoResuelta = false) {
        parent::__construct($redactor, $texto, $universidad);
        $this->estadoResuelta = $estadoResuelta;
    }

    public function marcarComoResuelta(): void {
        $this->estadoResuelta = true;
    }

    public function getEstadoResuelta(): bool {
        return $this->estadoResuelta;
    }
}