<?php
require_once __DIR__ . '/Usuario.php';

class Amistad {
    private string $fechaInicio;
    private string $estado;
    private Usuario $solicitante;
    private Usuario $receptor;

    public function __construct(Usuario $solicitante, Usuario $receptor, string $fechaInicio, string $estado = 'pendiente') {
        $this->solicitante = $solicitante;
        $this->receptor = $receptor;
        $this->fechaInicio = $fechaInicio;
        $this->estado = $estado;
    }

    public function aceptarSolicitud(): void {
        $this->estado = 'aceptada';
    }

    // Getters
    public function getFechaInicio(): string { return $this->fechaInicio; }
    public function getEstado(): string { return $this->estado; }
    public function getSolicitante(): Usuario { return $this->solicitante; }
    public function getReceptor(): Usuario { return $this->receptor; }
}
