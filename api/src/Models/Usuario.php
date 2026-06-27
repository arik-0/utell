<?php
class Usuario {
    private ?int $idUsuario;
    private string $nombre;
    private string $apellido;
    private string $email;
    private string $password;
    private string $fNac;
    private string $tipoPerfil;
    private int $idCiudad;
    
    // Optional properties
    private ?string $fotoPerfil;
    private ?string $celular;
    private ?string $descripcion;
    private ?string $trayectoria;

    public function __construct(
        string $nombre, 
        string $apellido, 
        string $email, 
        string $password, 
        string $fNac, 
        string $tipoPerfil, 
        int $idCiudad,
        ?int $idUsuario = null,
        ?string $fotoPerfil = null,
        ?string $celular = null,
        ?string $descripcion = null,
        ?string $trayectoria = null
    ) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->password = $password;
        $this->fNac = $fNac;
        $this->tipoPerfil = $tipoPerfil;
        $this->idCiudad = $idCiudad;
        $this->idUsuario = $idUsuario;
        $this->fotoPerfil = $fotoPerfil;
        $this->celular = $celular;
        $this->descripcion = $descripcion;
        $this->trayectoria = $trayectoria;
    }

    public function getIdUsuario(): ?int { return $this->idUsuario; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getNombreCompleto(): string { return $this->nombre . ' ' . $this->apellido; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getFnac(): string { return $this->fNac; }
    public function getTipoPerfil(): string { return $this->tipoPerfil; }
    public function getIdCiudad(): int { return $this->idCiudad; }
    public function getFotoPerfil(): ?string { return $this->fotoPerfil; }
    public function getCelular(): ?string { return $this->celular; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getTrayectoria(): ?string { return $this->trayectoria; }

    public function esEstudianteValidado(): bool {
        return $this->tipoPerfil === 'Estudiante';
    }

    // Convert object to associative array for JSON encoding
    public function toArray(): array {
        return [
            'idUsuario' => $this->idUsuario,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'fNac' => $this->fNac,
            'tipoPerfil' => $this->tipoPerfil,
            'idCiudad' => $this->idCiudad,
            'fotoPerfil' => $this->fotoPerfil,
            'celular' => $this->celular,
            'descripcion' => $this->descripcion,
            'trayectoria' => $this->trayectoria
        ];
    }
}