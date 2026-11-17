<?php
require_once __DIR__ . '/Usuario.php';

class Amistad {
    private string $fechaInicio;
    private ?string $fechaRespuesta;
    private string $estado;
    private Usuario $solicitante;
    private Usuario $receptor;

    public function __construct(
        Usuario $solicitante,
        Usuario $receptor,
        string $fechaInicio,
        string $estado = 'pendiente',
        ?string $fechaRespuesta = null
    ) {
        $this->solicitante    = $solicitante;
        $this->receptor       = $receptor;
        $this->fechaInicio    = $fechaInicio;
        $this->estado         = $estado;
        $this->fechaRespuesta = $fechaRespuesta;
    }

    public function aceptarSolicitud(): void {
        $this->estado = 'aceptada';
        $this->fechaRespuesta = date('Y-m-d');
    }

    public function rechazarSolicitud(): void {
        $this->estado = 'rechazada';
        $this->fechaRespuesta = date('Y-m-d');
    }

    public function esActiva(): bool {
        return $this->estado === 'aceptada';
    }

    // Getters
    public function getFechaInicio(): string { return $this->fechaInicio; }
    public function getFechaRespuesta(): ?string { return $this->fechaRespuesta; }
    public function getEstado(): string { return $this->estado; }
    public function getSolicitante(): Usuario { return $this->solicitante; }
    public function getReceptor(): Usuario { return $this->receptor; }
}
