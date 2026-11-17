<?php
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Conversacion.php';

/**
 * Entidad Intermedia: ParticipanteConversacion (vincula Conversacion ↔ Persona).
 */
class ParticipanteConversacion {
    private string $fechaUnion;
    private string $rolConversacion;
    private bool $notificacionesActivas;
    private Conversacion $conversacion;
    private Persona $participante;

    public function __construct(
        Conversacion $conversacion,
        Persona $participante,
        string $rolConversacion = 'miembro',
        bool $notificacionesActivas = true,
        ?string $fechaUnion = null
    ) {
        $this->conversacion          = $conversacion;
        $this->participante          = $participante;
        $this->rolConversacion       = $rolConversacion;
        $this->notificacionesActivas = $notificacionesActivas;
        $this->fechaUnion            = $fechaUnion ?? date('Y-m-d H:i:s');
    }

    public function getFechaUnion(): string { return $this->fechaUnion; }
    public function getRolConversacion(): string { return $this->rolConversacion; }
    public function tieneNotificacionesActivas(): bool { return $this->notificacionesActivas; }
    public function getConversacion(): Conversacion { return $this->conversacion; }
    public function getParticipante(): Persona { return $this->participante; }

    public function silenciarNotificaciones(): void {
        $this->notificacionesActivas = false;
    }
}
