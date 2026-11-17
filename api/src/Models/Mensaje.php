<?php
require_once __DIR__ . '/VisibilidadMensaje.php';

/**
 * Entidad de Dominio: Mensaje (Iteración 2 — Sección 14.5.2)
 */
class Mensaje {
    private ?int $idMensaje;
    private int $idEmisor;
    private int $idReceptor;
    private ?Persona $emisor = null;
    private ?Conversacion $conversacion = null;
    private string $contenidoTexto;
    private string $fechaMensaje;
    private string $horaMensaje;
    private ?string $fechaEntrega = null;
    private ?string $fechaLectura = null;
    private string $estadoLectura; // 'Enviado' | 'Entregado' | 'Leido'
    private bool $leido;
    /** @var VisibilidadMensaje[] */
    private array $visibilidades = [];

    public function __construct(
        int $idEmisor,
        int $idReceptor,
        string $contenidoTexto,
        ?string $fechaMensaje = null,
        ?string $horaMensaje = null,
        bool $leido = false,
        ?int $idMensaje = null,
        ?Persona $emisor = null,
        ?Conversacion $conversacion = null
    ) {
        $this->idMensaje      = $idMensaje;
        $this->idEmisor       = $idEmisor;
        $this->idReceptor     = $idReceptor;
        $this->emisor         = $emisor;
        $this->conversacion   = $conversacion;
        $this->contenidoTexto = trim($contenidoTexto);
        $this->fechaMensaje   = $fechaMensaje ?? date('Y-m-d');
        $this->horaMensaje    = $horaMensaje ?? date('H:i:s');
        $this->leido          = $leido;
        $this->estadoLectura  = $leido ? 'Leido' : 'Enviado';
    }

    public function getIdMensaje(): ?int { return $this->idMensaje; }
    public function getIdEmisor(): int { return $this->idEmisor; }
    public function getIdReceptor(): int { return $this->idReceptor; }
    public function getEmisor(): ?Persona { return $this->emisor; }
    public function setEmisor(?Persona $emisor): void { $this->emisor = $emisor; }
    public function getConversacion(): ?Conversacion { return $this->conversacion; }
    public function setConversacion(?Conversacion $conversacion): void { $this->conversacion = $conversacion; }
    public function getContenidoTexto(): string { return $this->contenidoTexto; }
    public function getFechaMensaje(): string { return $this->fechaMensaje; }
    public function getHoraMensaje(): string { return $this->horaMensaje; }
    public function getFechaEntrega(): ?string { return $this->fechaEntrega; }
    public function getFechaLectura(): ?string { return $this->fechaLectura; }
    public function isLeido(): bool { return $this->leido; }
    public function getEstadoLectura(): string { return $this->estadoLectura; }

    /** @return VisibilidadMensaje[] */
    public function getVisibilidades(): array { return $this->visibilidades; }
    public function agregarVisibilidad(VisibilidadMensaje $visibilidad): void {
        $this->visibilidades[] = $visibilidad;
    }

    public function esVisiblePara(int $idUsuario): bool {
        foreach ($this->visibilidades as $vis) {
            if ($vis->getIdUsuario() === $idUsuario && $vis->isOculto()) {
                return false;
            }
        }
        return true;
    }

    public function marcarEntregado(): void {
        if (!$this->leido) {
            $this->estadoLectura = 'Entregado';
            $this->fechaEntrega  = date('Y-m-d H:i:s');
        }
    }

    public function marcarLeido(): void {
        $this->leido         = true;
        $this->estadoLectura = 'Leido';
        $this->fechaLectura  = date('Y-m-d H:i:s');
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
