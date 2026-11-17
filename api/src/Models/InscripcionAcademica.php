<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Universidad.php';
require_once __DIR__ . '/Sede.php';
require_once __DIR__ . '/Carrera.php';

/**
 * Entidad Intermedia: InscripcionAcademica (vincula Usuario con Institución/Sede/Carrera).
 */
class InscripcionAcademica {
    private int $anioIngreso;
    private string $estadoCursado;
    private Usuario $usuario;
    private Universidad $universidad;
    private ?Sede $sede;
    private ?Carrera $carrera;

    public function __construct(
        Usuario $usuario,
        Universidad $universidad,
        ?Sede $sede = null,
        ?Carrera $carrera = null,
        ?int $anioIngreso = null,
        string $estadoCursado = 'activo'
    ) {
        $this->usuario       = $usuario;
        $this->universidad   = $universidad;
        $this->sede          = $sede;
        $this->carrera       = $carrera;
        $this->anioIngreso   = $anioIngreso ?? (int)date('Y');
        $this->estadoCursado = $estadoCursado;
    }

    public function getAnioIngreso(): int { return $this->anioIngreso; }
    public function getEstadoCursado(): string { return $this->estadoCursado; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function getUniversidad(): Universidad { return $this->universidad; }
    public function getSede(): ?Sede { return $this->sede; }
    public function getCarrera(): ?Carrera { return $this->carrera; }

    public function validarCursado(): bool {
        return $this->estadoCursado === 'activo';
    }
}
