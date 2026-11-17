<?php

class Ciudad {
    private string $nombre;
    /** @var Persona[] */
    private array $personas = [];
    /** @var Sede[] */
    private array $sedes = [];
    /** @var Universidad[] */
    private array $universidades = [];

    public function __construct(string $nombre) {
        $this->nombre = $nombre;
    }

    public function getNombre(): string { return $this->nombre; }

    /** @return Persona[] */
    public function getPersonas(): array { return $this->personas; }
    public function agregarPersona(Persona $persona): void { $this->personas[] = $persona; }

    /** @return Sede[] */
    public function getSedes(): array { return $this->sedes; }
    public function agregarSede(Sede $sede): void { $this->sedes[] = $sede; }

    /** @return Universidad[] */
    public function getUniversidades(): array { return $this->universidades; }
    public function agregarUniversidad(Universidad $universidad): void { $this->universidades[] = $universidad; }
}
