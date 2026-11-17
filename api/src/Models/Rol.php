<?php

/**
 * Entidad de Dominio: Rol (RBAC — clasificación de jerarquía de usuario).
 */
class Rol {
    private ?int $idRol;
    private string $nombreRol;
    private int $nivelJerarquia;
    /** @var Usuario[] */
    private array $usuarios = [];

    public function __construct(string $nombreRol, int $nivelJerarquia = 1, ?int $idRol = null) {
        $this->idRol          = $idRol;
        $this->nombreRol      = $nombreRol;
        $this->nivelJerarquia = $nivelJerarquia;
    }

    public function getIdRol(): ?int { return $this->idRol; }
    public function getNombreRol(): string { return $this->nombreRol; }
    public function getNivelJerarquia(): int { return $this->nivelJerarquia; }

    public function esAdministrativo(): bool {
        return in_array($this->nombreRol, ['Administrador', 'Moderador'], true);
    }

    /** @return Usuario[] */
    public function getUsuarios(): array { return $this->usuarios; }
    public function agregarUsuario(Usuario $usuario): void { $this->usuarios[] = $usuario; }
}
