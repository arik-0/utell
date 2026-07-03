<?php
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Credencial.php';

class Usuario extends Persona {
    private ?int $idUsuario;
    private Credencial $credencial;
    private string $tipoPerfil;
    private int $idCiudad; // Mantendremos el ID por practicidad con BD, o idealmente `private Ciudad $ciudad;`
    
    // Optional properties
    private ?string $fotoPerfil;
    private ?string $descripcion;
    private ?string $trayectoria;

    // Relaciones
    private array $amigos = [];         // Arreglo de instancias de Amistad
    private array $publicaciones = [];  // Arreglo de instancias de Publicacion

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
        parent::__construct($nombre, $apellido, $fNac, $celular);
        
        // Composición con Credencial
        $this->credencial = new Credencial($email, $password);
        
        $this->tipoPerfil = $tipoPerfil;
        $this->idCiudad = $idCiudad;
        $this->idUsuario = $idUsuario;
        $this->fotoPerfil = $fotoPerfil;
        $this->descripcion = $descripcion;
        $this->trayectoria = $trayectoria;
    }

    public function getIdUsuario(): ?int { return $this->idUsuario; }
    
    // Delegación a Credencial
    public function getEmail(): string { return $this->credencial->getEmail(); }
    public function getPassword(): string { return $this->credencial->getPasswordHash(); }
    public function getCredencial(): Credencial { return $this->credencial; }

    public function getFnac(): string { return $this->fechaNacimiento; }
    public function getTipoPerfil(): string { return $this->tipoPerfil; }
    public function getIdCiudad(): int { return $this->idCiudad; }
    public function getFotoPerfil(): ?string { return $this->fotoPerfil; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getTrayectoria(): ?string { return $this->trayectoria; }

    public function actualizarBiografia(string $desc, string $tray): void {
        $this->descripcion = $desc;
        $this->trayectoria = $tray;
    }

    public function getAmigos(): array { return $this->amigos; }
    public function agregarAmigo($amistad): void { $this->amigos[] = $amistad; }

    public function getPublicaciones(): array { return $this->publicaciones; }
    public function agregarPublicacion($publicacion): void { $this->publicaciones[] = $publicacion; }

    public function esEstudianteValidado(): bool {
        return $this->tipoPerfil === 'Estudiante';
    }

    public function toArray(): array {
        return [
            'idUsuario' => $this->idUsuario,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->credencial->getEmail(),
            'fNac' => $this->fechaNacimiento,
            'tipoPerfil' => $this->tipoPerfil,
            'idCiudad' => $this->idCiudad,
            'fotoPerfil' => $this->fotoPerfil,
            'celular' => $this->celular,
            'descripcion' => $this->descripcion,
            'trayectoria' => $this->trayectoria
        ];
    }
}