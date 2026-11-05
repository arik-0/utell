<?php
/**
 * Entidad de Dominio: VisibilidadMensaje (Iteración 2 — Sección 14.5.2 / CU13)
 * Representa la visibilidad individual por participante ("Eliminar para mí").
 */
class VisibilidadMensaje {
    private int $idMensaje;
    private int $idUsuario;
    private bool $oculto;
    private ?string $fechaOcultamiento;

    public function __construct(
        int $idMensaje,
        int $idUsuario,
        bool $oculto = false,
        ?string $fechaOcultamiento = null
    ) {
        $this->idMensaje         = $idMensaje;
        $this->idUsuario         = $idUsuario;
        $this->oculto            = $oculto;
        $this->fechaOcultamiento = $fechaOcultamiento;
    }

    public function getIdMensaje(): int { return $this->idMensaje; }
    public function getIdUsuario(): int { return $this->idUsuario; }
    public function isOculto(): bool { return $this->oculto; }
    public function getFechaOcultamiento(): ?string { return $this->fechaOcultamiento; }

    public function ocultar(): void {
        $this->oculto = true;
        $this->fechaOcultamiento = date('Y-m-d H:i:s');
    }
}
