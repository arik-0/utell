<?php

/**
 * Patrón GoF Composite — Componente abstracto de Seguridad RBAC.
 * Unifica el tratamiento de hojas (Permiso atómico) y compuestos (GrupoPermiso).
 */
abstract class ComponenteSeguridad {
    protected string $nombre;

    public function __construct(string $nombre) {
        $this->nombre = $nombre;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    abstract public function tienePermiso(string $codigoAccion): bool;

    /** @return string[] */
    abstract public function obtenerCodigosPermiso(): array;

    public function esCompuesto(): bool {
        return false;
    }
}
