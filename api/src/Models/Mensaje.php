<?php
/**
 * Entidad de Dominio: Mensaje (Iteración 2 — Sección 14.5.2)
 */
class Mensaje {
    private ?int $idMensaje;
    private int $idEmisor;
    private int $idReceptor;
    private string $contenidoTexto;
    private string $fechaMensaje;
    private string $horaMensaje;
    private string $estadoLectura; // 'Enviado' | 'Entregado' | 'Leido'
    private bool $leido;

    public function __construct(
        int $idEmisor,
        int $idReceptor,
        string $contenidoTexto,
        ?string $fechaMensaje = null,
        ?string $horaMensaje = null,
        bool $leido = false,
        ?int $idMensaje = null
    ) {
        $this->idMensaje      = $idMensaje;
        $this->idEmisor       = $idEmisor;
        $this->idReceptor     = $idReceptor;
        $this->contenidoTexto = trim($contenidoTexto);
        $this->fechaMensaje   = $fechaMensaje ?? date('Y-m-d');
        $this->horaMensaje    = $horaMensaje ?? date('H:i:s');
        $this->leido          = $leido;
        $this->estadoLectura  = $leido ? 'Leido' : 'Enviado';
    }

    public function getIdMensaje(): ?int { return $this->idMensaje; }
    public function getIdEmisor(): int { return $this->idEmisor; }
    public function getIdReceptor(): int { return $this->idReceptor; }
    public function getContenidoTexto(): string { return $this->contenidoTexto; }
    public function getFechaMensaje(): string { return $this->fechaMensaje; }
    public function getHoraMensaje(): string { return $this->horaMensaje; }
    public function isLeido(): bool { return $this->leido; }
    public function getEstadoLectura(): string { return $this->estadoLectura; }

    public function marcarLeido(): void {
        $this->leido = true;
        $this->estadoLectura = 'Leido';
    }

    public function toArray(): array {
        return [
            'idMensajeDirecto' => $this->idMensaje,
            'idEmisor'         => $this->idEmisor,
            'idReceptor'       => $this->idReceptor,
            'fechaMensaje'     => $this->fechaMensaje,
            'horaMensaje'      => $this->horaMensaje,
            'textoMensaje'     => $this->contenidoTexto,
            'leido'            => $this->leido ? 1 : 0,
            'estadoLectura'    => $this->estadoLectura,
        ];
    }
}
