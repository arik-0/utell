<?php
abstract class Publicacion {
    protected ?int $idPublicacion;
    protected int $idUsuario;
    protected string $texto;
    protected string $fecha;
    protected string $hora;
    protected int $idUniversidad;
    protected int $idSede;
    protected int $idCarrera;
    protected int $likes;

    public function __construct(int $idUsuario, string $texto, int $idUni, int $idSede, int $idCarrera) {
        $this->idUsuario = $idUsuario;
        $this->texto = $texto;
        $this->idUniversidad = $idUni;
        $this->idSede = $idSede;
        $this->idCarrera = $idCarrera;
        $this->fecha = date('Y-m-d');
        $this->hora = date('H:i:s');
        $this->likes = 0;
    }

    // RNF03: Getters para acceder a los datos privados
    public function getTexto(): string { return $this->texto; }
    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getIdPublicacion(): ?int { return $this->idPublicacion; }
    
    // Setters con validación (Lógica de Negocio)
    public function setTexto(string $texto): void {
        if (strlen($texto) < 10) throw new Exception("El texto es muy corto.");
        $this->texto = $texto;
    }
}