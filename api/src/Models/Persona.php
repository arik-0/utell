<?php

abstract class Persona {
    protected string $nombre;
    protected string $apellido;
    protected string $fechaNacimiento;
    protected ?string $celular;
    protected ?Ciudad $ciudad = null;
    /** @var Amistad[] */
    protected array $solicitudesEnviadas = [];
    /** @var Amistad[] */
    protected array $solicitudesRecibidas = [];
    /** @var Publicacion[] */
    protected array $publicaciones = [];
    /** @var Comentario[] */
    protected array $respuestas = [];
    /** @var ReaccionPublicacion[] */
    protected array $reacciones = [];
    /** @var ParticipanteConversacion[] */
    protected array $participaciones = [];
    /** @var Mensaje[] */
    protected array $mensajesEnviados = [];
    /** @var VisibilidadMensaje[] */
    protected array $visibilidades = [];

    public function __construct(
        string $nombre,
        string $apellido,
        string $fechaNacimiento,
        ?string $celular = null,
        ?Ciudad $ciudad = null
    ) {
        $this->nombre          = $nombre;
        $this->apellido        = $apellido;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->celular         = $celular;
        $this->ciudad          = $ciudad;
    }

    // Getters
    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getNombreCompleto(): string { return $this->nombre . ' ' . $this->apellido; }
    public function getFechaNacimiento(): string { return $this->fechaNacimiento; }
    public function getCelular(): ?string { return $this->celular; }
    public function getCiudad(): ?Ciudad { return $this->ciudad; }

    public function getEdad(): int {
        $nacimiento = new DateTime($this->fechaNacimiento);
        return $nacimiento->diff(new DateTime())->y;
    }

    // Setters
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setApellido(string $apellido): void { $this->apellido = $apellido; }
    public function setFechaNacimiento(string $fechaNacimiento): void { $this->fechaNacimiento = $fechaNacimiento; }
    public function setCelular(?string $celular): void { $this->celular = $celular; }
    public function setCiudad(?Ciudad $ciudad): void { $this->ciudad = $ciudad; }

    // Colecciones de relaciones
    /** @return Amistad[] */
    public function getSolicitudesEnviadas(): array { return $this->solicitudesEnviadas; }
    public function agregarSolicitudEnviada(Amistad $amistad): void { $this->solicitudesEnviadas[] = $amistad; }

    /** @return Amistad[] */
    public function getSolicitudesRecibidas(): array { return $this->solicitudesRecibidas; }
    public function agregarSolicitudRecibida(Amistad $amistad): void { $this->solicitudesRecibidas[] = $amistad; }

    /** @return Publicacion[] */
    public function getPublicaciones(): array { return $this->publicaciones; }
    public function agregarPublicacion($publicacion): void { $this->publicaciones[] = $publicacion; }

    /** @return Comentario[] */
    public function getRespuestas(): array { return $this->respuestas; }
    public function agregarRespuesta($respuesta): void { $this->respuestas[] = $respuesta; }

    /** @return ReaccionPublicacion[] */
    public function getReacciones(): array { return $this->reacciones; }
    public function agregarReaccion(ReaccionPublicacion $reaccion): void { $this->reacciones[] = $reaccion; }

    /** @return ParticipanteConversacion[] */
    public function getParticipaciones(): array { return $this->participaciones; }
    public function agregarParticipacion(ParticipanteConversacion $participacion): void { $this->participaciones[] = $participacion; }

    /** @return Mensaje[] */
    public function getMensajesEnviados(): array { return $this->mensajesEnviados; }
    public function agregarMensajeEnviado(Mensaje $mensaje): void { $this->mensajesEnviados[] = $mensaje; }

    /** @return VisibilidadMensaje[] */
    public function getVisibilidades(): array { return $this->visibilidades; }
    public function agregarVisibilidad(VisibilidadMensaje $visibilidad): void { $this->visibilidades[] = $visibilidad; }
}
