<?php
require_once __DIR__ . '/Mensaje.php';
require_once __DIR__ . '/ParticipanteConversacion.php';

/**
 * Entidad de Dominio: Conversacion (Iteración 2 — Sección 14.5.2)
 */
class Conversacion {
    private ?int $idConversacion;
    private int $idUsuarioA;
    private int $idUsuarioB;
    private string $fechaCreacion;
    /** @var ParticipanteConversacion[] */
    private array $participantes = [];
    /** @var Mensaje[] */
    private array $mensajes = [];

    public function __construct(
        int $idUsuarioA,
        int $idUsuarioB,
        ?string $fechaCreacion = null,
        ?int $idConversacion = null
    ) {
        $this->idConversacion = $idConversacion;
        $this->idUsuarioA     = $idUsuarioA;
        $this->idUsuarioB     = $idUsuarioB;
        $this->fechaCreacion  = $fechaCreacion ?? date('Y-m-d H:i:s');
    }

    public function getIdConversacion(): ?int { return $this->idConversacion; }
    public function getIdUsuarioA(): int { return $this->idUsuarioA; }
    public function getIdUsuarioB(): int { return $this->idUsuarioB; }
    public function getFechaCreacion(): string { return $this->fechaCreacion; }

    public function agregarParticipante(ParticipanteConversacion $participante): void {
        $this->participantes[] = $participante;
    }

    /** @return ParticipanteConversacion[] */
    public function getParticipantes(): array { return $this->participantes; }

    public function agregarMensaje(Mensaje $mensaje): void {
        $this->mensajes[] = $mensaje;
        $mensaje->setConversacion($this);
    }

    /** @return Mensaje[] */
    public function getMensajes(): array {
        return $this->mensajes;
    }

    /** @return Mensaje[] */
    public function obtenerMensajesVisibles(int $idUsuario): array {
        return array_values(array_filter($this->mensajes, function (Mensaje $msg) use ($idUsuario) {
            return $msg->esVisiblePara($idUsuario);
        }));
    }
}
