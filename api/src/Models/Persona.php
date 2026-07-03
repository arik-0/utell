<?php

abstract class Persona {
    protected string $nombre;
    protected string $apellido;
    protected string $fechaNacimiento;
    protected ?string $celular;

    public function __construct(string $nombre, string $apellido, string $fechaNacimiento, ?string $celular = null) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->celular = $celular;
    }

    // Getters
    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getNombreCompleto(): string { return $this->nombre . ' ' . $this->apellido; }
    public function getFechaNacimiento(): string { return $this->fechaNacimiento; }
    public function getCelular(): ?string { return $this->celular; }

    // Setters
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setApellido(string $apellido): void { $this->apellido = $apellido; }
    public function setFechaNacimiento(string $fechaNacimiento): void { $this->fechaNacimiento = $fechaNacimiento; }
    public function setCelular(?string $celular): void { $this->celular = $celular; }
}
