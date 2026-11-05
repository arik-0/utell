<?php
class Auditoria {
    private ?int $idLog;
    private ?int $idUsuario;
    private string $tablaAfectada;
    private string $accion;
    private ?string $valorOriginal;
    private ?string $valorNuevo;
    private ?string $detalle;
    private ?string $ip;

    public function __construct(
        ?int $idUsuario,
        string $tablaAfectada,
        string $accion,
        ?string $valorOriginal = null,
        ?string $valorNuevo = null,
        ?string $detalle = null,
        ?string $ip = null,
        ?int $idLog = null
    ) {
        $this->idLog         = $idLog;
        $this->idUsuario     = $idUsuario;
        $this->tablaAfectada = $tablaAfectada;
        $this->accion        = $accion;
        $this->valorOriginal = $valorOriginal;
        $this->valorNuevo    = $valorNuevo;
        $this->detalle       = $detalle;
        $this->ip            = $ip;
    }

    public function getIdLog(): ?int { return $this->idLog; }
    public function getIdUsuario(): ?int { return $this->idUsuario; }
    public function getTablaAfectada(): string { return $this->tablaAfectada; }
    public function getAccion(): string { return $this->accion; }
    public function getValorOriginal(): ?string { return $this->valorOriginal; }
    public function getValorNuevo(): ?string { return $this->valorNuevo; }
    public function getDetalle(): ?string { return $this->detalle; }
    public function getIp(): ?string { return $this->ip; }
}
