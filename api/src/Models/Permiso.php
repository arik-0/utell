<?php
require_once __DIR__ . '/ComponenteSeguridad.php';

/**
 * Patrón GoF Composite — Hoja (Leaf): Permiso atómico del sistema RBAC.
 */
class Permiso extends ComponenteSeguridad {
    private ?int $idPermiso;
    private string $codigoAccion;
    /** @var DetalleGrupoPermiso[] */
    private array $detallesGrupo = [];
    /** @var GrupoPermisoPermiso[] */
    private array $gruposRelacionados = [];

    public function __construct(string $nombre, string $codigoAccion, ?int $idPermiso = null) {
        parent::__construct($nombre);
        $this->codigoAccion = strtoupper(trim($codigoAccion));
        $this->idPermiso    = $idPermiso;
    }

    public function getIdPermiso(): ?int { return $this->idPermiso; }
    public function getCodigoAccion(): string { return $this->codigoAccion; }

    /**
     * Patrón Composite (Leaf): evalúa si esta hoja concede la acción solicitada.
     */
    public function tienePermiso(string $codigoAccion): bool {
        return $this->codigoAccion === $codigoAccion
            || in_array($this->codigoAccion, ['OWNER_FULL', 'ADMIN_FULL'], true);
    }

    /**
     * Patrón Composite (Leaf): devuelve el código atómico encapsulado en esta hoja.
     * @return string[]
     */
    public function obtenerCodigosPermiso(): array {
        return [$this->codigoAccion];
    }

    /** @return DetalleGrupoPermiso[] */
    public function getDetallesGrupo(): array { return $this->detallesGrupo; }
    public function agregarDetalleGrupo(DetalleGrupoPermiso $detalle): void { $this->detallesGrupo[] = $detalle; }

    /** @return GrupoPermisoPermiso[] */
    public function getGruposRelacionados(): array { return $this->gruposRelacionados; }
    public function agregarGrupoRelacionado(GrupoPermisoPermiso $rel): void { $this->gruposRelacionados[] = $rel; }
}
