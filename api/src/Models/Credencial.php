<?php

class Credencial {
    private string $email;
    private string $passwordHash;
    private string $estadoCuenta;
    private ?Persona $persona = null;
    /** @var AsignacionGrupoUsuario[] */
    private array $asignacionesGrupo = [];

    public function __construct(
        string $email,
        string $passwordHash,
        string $estadoCuenta = 'activo',
        ?Persona $persona = null
    ) {
        $this->email        = $email;
        $this->passwordHash = $passwordHash;
        $this->estadoCuenta = $estadoCuenta;
        $this->persona      = $persona;
    }

    public function validarAcceso(string $pass): bool {
        return password_verify($pass, $this->passwordHash);
    }

    public function bloquear(): void {
        $this->estadoCuenta = 'bloqueado';
    }

    // Getters
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getEstadoCuenta(): string { return $this->estadoCuenta; }
    public function getPersona(): ?Persona { return $this->persona; }

    /** @return AsignacionGrupoUsuario[] */
    public function getAsignacionesGrupo(): array { return $this->asignacionesGrupo; }
    public function agregarAsignacionGrupo(AsignacionGrupoUsuario $asignacion): void {
        $this->asignacionesGrupo[] = $asignacion;
    }

    public function tienePermiso(string $codigoAccion): bool {
        foreach ($this->asignacionesGrupo as $asignacion) {
            if ($asignacion->getEstadoVigencia() === 'activo' && $asignacion->getGrupoPermiso()->tienePermiso($codigoAccion)) {
                return true;
            }
        }
        return false;
    }

    // Setters
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPasswordHash(string $passwordHash): void { $this->passwordHash = $passwordHash; }
    public function setPersona(?Persona $persona): void { $this->persona = $persona; }
}
