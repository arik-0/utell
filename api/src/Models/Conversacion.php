<?php
require_once __DIR__ . '/Mensaje.php';

/**
 * Entidad de Dominio: Conversacion (Iteración 2 — Sección 14.5.2)
 */
class Conversacion {
    private ?int $idConversacion;
    private int $idUsuarioA;
    private int $idUsuarioB;
    private string $fechaCreacion;
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

    public function agregarMensaje(Mensaje $mensaje): void {
        $this->mensajes[] = $mensaje;
    }

    /** @return Mensaje[] */
    public function getMensajes(): array {
        return $this->mensajes;
    }
}
