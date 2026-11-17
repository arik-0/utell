<?php
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Publicacion.php';

/**
 * Entidad Intermedia: ReaccionPublicacion (vincula Persona ↔ Publicacion con fecha/tipo).
 */
class ReaccionPublicacion {
    private string $fechaReaccion;
    private string $horaReaccion;
    private string $tipoReaccion;
    private Persona $usuario;
    private Publicacion $publicacion;

    public function __construct(
        Persona $usuario,
        Publicacion $publicacion,
        string $tipoReaccion = 'like',
        ?string $fechaReaccion = null,
        ?string $horaReaccion = null
    ) {
        $this->usuario       = $usuario;
        $this->publicacion   = $publicacion;
        $this->tipoReaccion  = $tipoReaccion;
        $this->fechaReaccion = $fechaReaccion ?? date('Y-m-d');
        $this->horaReaccion  = $horaReaccion ?? date('H:i:s');
    }

    public function getFechaReaccion(): string { return $this->fechaReaccion; }
    public function getHoraReaccion(): string { return $this->horaReaccion; }
    public function getTipoReaccion(): string { return $this->tipoReaccion; }
    public function getUsuario(): Persona { return $this->usuario; }
    public function getPublicacion(): Publicacion { return $this->publicacion; }

    public function alternarEstado(): void {
        $this->tipoReaccion = $this->tipoReaccion === 'like' ? 'inactivo' : 'like';
    }
}
