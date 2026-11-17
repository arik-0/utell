<?php
/**
 * Entidad de Dominio: VisibilidadMensaje (Iteración 2 — Sección 14.5.2 / CU13)
 * Representa la visibilidad individual por participante ("Eliminar para mí").
 */
class VisibilidadMensaje {
    private int $idMensaje;
    private int $idUsuario;
    private ?Mensaje $mensaje = null;
    private ?Persona $usuario = null;
    private bool $oculto;
    private ?string $fechaOcultamiento;

    public function __construct(
        int $idMensaje,
        int $idUsuario,
        bool $oculto = false,
        ?string $fechaOcultamiento = null,
        ?Mensaje $mensaje = null,
        ?Persona $usuario = null
    ) {
        $this->idMensaje         = $idMensaje;
        $this->idUsuario         = $idUsuario;
        $this->mensaje           = $mensaje;
        $this->usuario           = $usuario;
        $this->oculto            = $oculto;
        $this->fechaOcultamiento = $fechaOcultamiento;
    }

    public function getIdMensaje(): int { return $this->idMensaje; }
    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getMensaje(): ?Mensaje { return $this->mensaje; }
    public function getUsuario(): ?Persona { return $this->usuario; }
    public function isOculto(): bool { return $this->oculto; }
    public function isVisible(): bool { return !$this->oculto; }
    public function getFechaOcultamiento(): ?string { return $this->fechaOcultamiento; }

    public function ocultar(): void {
        $this->oculto = true;
        $this->fechaOcultamiento = date('Y-m-d H:i:s');
    }
}
