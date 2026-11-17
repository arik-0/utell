<?php
require_once __DIR__ . '/Persona.php';
require_once __DIR__ . '/Credencial.php';
require_once __DIR__ . '/Ciudad.php';
require_once __DIR__ . '/Universidad.php';
require_once __DIR__ . '/Carrera.php';

class Usuario extends Persona {
    private ?int $idUsuario;
    private Credencial $credencial;
    private string $tipoPerfil;
    private ?Rol $rol = null;
    private int $idCiudad;
    private ?int $idUniversidad;
    private ?int $idCarrera;
    private ?Universidad $universidad = null;
    private ?Carrera $carrera = null;

    // Propiedades opcionales
    private ?string $fotoPerfil;
    private ?string $descripcion;
    private ?string $trayectoria;

    // Relaciones tipadas y listas
    /** @var Amistad[] */
    private array $amigos = [];
    /** @var InscripcionAcademica[] */
    private array $inscripciones = [];
    /** @var DetalleGrupoPermiso[] */
    private array $asignacionesGrupo = [];

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
        ?string $trayectoria = null,
        ?int $idUniversidad = null,
        ?int $idCarrera = null,
        ?Ciudad $ciudad = null,
        ?Universidad $universidad = null,
        ?Carrera $carrera = null,
        ?Rol $rol = null
    ) {
        parent::__construct($nombre, $apellido, $fNac, $celular, $ciudad);

        // Composición bidireccional con Credencial
        $this->credencial    = new Credencial($email, $password, 'activo', $this);
        $this->tipoPerfil    = $tipoPerfil;
        $this->idCiudad      = $idCiudad;
        $this->idUsuario     = $idUsuario;
        $this->fotoPerfil    = $fotoPerfil;
        $this->descripcion   = $descripcion;
        $this->trayectoria   = $trayectoria;
        $this->idUniversidad = $idUniversidad;
        $this->idCarrera     = $idCarrera;
        $this->universidad   = $universidad;
        $this->carrera       = $carrera;
        $this->rol           = $rol;
    }

    public function getIdUsuario(): ?int { return $this->idUsuario; }

    // Delegación a Credencial
    public function getEmail(): string { return $this->credencial->getEmail(); }
    public function getPassword(): string { return $this->credencial->getPasswordHash(); }
    public function getCredencial(): Credencial { return $this->credencial; }

    public function getFnac(): string { return $this->fechaNacimiento; }
    public function getTipoPerfil(): string { return $this->tipoPerfil; }
    public function getRol(): ?Rol { return $this->rol; }
    public function setRol(?Rol $rol): void { $this->rol = $rol; }

    public function getIdCiudad(): int { return $this->idCiudad; }
    public function getIdUniversidad(): ?int { return $this->idUniversidad; }
    public function getIdCarrera(): ?int { return $this->idCarrera; }
    public function getUniversidad(): ?Universidad { return $this->universidad; }
    public function setUniversidad(?Universidad $universidad): void { $this->universidad = $universidad; }
    public function getCarrera(): ?Carrera { return $this->carrera; }
    public function setCarrera(?Carrera $carrera): void { $this->carrera = $carrera; }

    public function getFotoPerfil(): ?string { return $this->fotoPerfil; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getTrayectoria(): ?string { return $this->trayectoria; }

    public function actualizarBiografia(string $desc, string $tray): void {
        $this->descripcion = $desc;
        $this->trayectoria = $tray;
    }

    /** @return Amistad[] */
    public function getAmigos(): array { return $this->amigos; }
    public function agregarAmigo($amistad): void { $this->amigos[] = $amistad; }

    /** @return InscripcionAcademica[] */
    public function getInscripciones(): array { return $this->inscripciones; }
    public function agregarInscripcion(InscripcionAcademica $inscripcion): void { $this->inscripciones[] = $inscripcion; }

    /** @return DetalleGrupoPermiso[] */
    public function getAsignacionesGrupo(): array { return $this->asignacionesGrupo; }
    public function agregarAsignacionGrupo(DetalleGrupoPermiso $detalle): void { $this->asignacionesGrupo[] = $detalle; }

    public function esEstudianteValidado(): bool {
        return in_array($this->tipoPerfil, ['Estudiante', 'Docente', 'Graduado'], true);
    }

    public function toArray(): array {
        return [
            'idUsuario'     => $this->idUsuario,
            'nombre'        => $this->nombre,
            'apellido'      => $this->apellido,
            'email'         => $this->credencial->getEmail(),
            'fNac'          => $this->fechaNacimiento,
            'tipoPerfil'    => $this->tipoPerfil,
            'idCiudad'      => $this->idCiudad,
            'fotoPerfil'    => $this->fotoPerfil,
            'celular'       => $this->celular,
            'descripcion'   => $this->descripcion,
            'trayectoria'   => $this->trayectoria,
            'idUniversidad' => $this->idUniversidad,
            'idCarrera'     => $this->idCarrera,
        ];
    }
}