<?php
require_once __DIR__ . '/ComponenteSeguridad.php';
require_once __DIR__ . '/Permiso.php';

/**
 * Patrón GoF Composite — Compuesto (Composite): Grupo de Permisos RBAC.
 * Puede contener tanto hojas (Permiso) como otros subgrupos (GrupoPermiso) recursivamente.
 */
class GrupoPermiso extends ComponenteSeguridad {
    private ?int $idGrupoPermiso;
    private ?int $idGrupoPadre;
    private string $descripcion;

    /** @var ComponenteSeguridad[] */
    private array $hijos = [];
    /** @var Permiso[] */
    private array $permisos = [];
    /** @var DetalleGrupoPermiso[] */
    private array $detallesUsuario = [];
    /** @var GrupoPermisoPermiso[] */
    private array $detallesPermiso = [];

    public function __construct(
        string $nombre,
        string $descripcion = '',
        ?int $idGrupoPermiso = null,
        ?int $idGrupoPadre = null
    ) {
        parent::__construct($nombre);
        $this->descripcion    = $descripcion;
        $this->idGrupoPermiso = $idGrupoPermiso;
        $this->idGrupoPadre   = $idGrupoPadre;
    }

    public function getIdGrupoPermiso(): ?int { return $this->idGrupoPermiso; }
    public function getIdGrupoPadre(): ?int { return $this->idGrupoPadre; }
    public function getDescripcion(): string { return $this->descripcion; }

    public function esCompuesto(): bool {
        return true;
    }

    /**
     * Agrega cualquier ComponenteSeguridad (Permiso hoja o GrupoPermiso subgrupo),
     * evitando referencias circulares o duplicación del mismo objeto.
     */
    public function agregarComponente(ComponenteSeguridad $componente): void {
        if ($componente === $this) {
            return;
        }
        foreach ($this->hijos as $existente) {
            if ($existente === $componente) {
                return;
            }
        }
        $this->hijos[] = $componente;
        if ($componente instanceof Permiso) {
            $this->permisos[] = $componente;
        }
    }

    public function removerComponente(ComponenteSeguridad $componente): void {
        $this->hijos = array_values(array_filter(
            $this->hijos,
            static fn(ComponenteSeguridad $item) => $item !== $componente
        ));
        if ($componente instanceof Permiso) {
            $this->permisos = array_values(array_filter(
                $this->permisos,
                static fn(Permiso $item) => $item !== $componente
            ));
        }
    }

    /** @return ComponenteSeguridad[] */
    public function getHijos(): array {
        return $this->hijos;
    }

    /** @return Permiso[] */
    public function getPermisos(): array { return $this->permisos; }

    public function agregarPermiso(Permiso $permiso): void {
        $this->agregarComponente($permiso);
    }

    /** @return DetalleGrupoPermiso[] */
    public function getDetallesUsuario(): array { return $this->detallesUsuario; }
    public function agregarDetalleUsuario(DetalleGrupoPermiso $detalle): void { $this->detallesUsuario[] = $detalle; }

    /** @return GrupoPermisoPermiso[] */
    public function getDetallesPermiso(): array { return $this->detallesPermiso; }
    public function agregarDetallePermiso(GrupoPermisoPermiso $detalle): void {
        $this->detallesPermiso[] = $detalle;
        $this->agregarComponente($detalle->getPermiso());
    }

    /**
     * Patrón Composite RBAC: recorre recursivamente hojas y subgrupos hijos.
     */
    public function tienePermiso(string $codigoAccion): bool {
        $visitados = [];
        return $this->tienePermisoInterno($codigoAccion, $visitados);
    }

    private function tienePermisoInterno(string $codigoAccion, array &$visitados): bool {
        $oid = spl_object_id($this);
        if (isset($visitados[$oid])) {
            return false;
        }
        $visitados[$oid] = true;

        foreach ($this->hijos as $hijo) {
            if ($hijo instanceof self) {
                if ($hijo->tienePermisoInterno($codigoAccion, $visitados)) {
                    return true;
                }
            } elseif ($hijo->tienePermiso($codigoAccion)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Patrón Composite RBAC: consolida recursivamente todos los códigos de permiso del árbol.
     * @return string[]
     */
    public function obtenerCodigosPermiso(): array {
        $visitados = [];
        return array_values(array_unique($this->obtenerCodigosInterno($visitados)));
    }

    /** @return string[] */
    private function obtenerCodigosInterno(array &$visitados): array {
        $oid = spl_object_id($this);
        if (isset($visitados[$oid])) {
            return [];
        }
        $visitados[$oid] = true;

        $codigos = [];
        foreach ($this->hijos as $hijo) {
            $subCodigos = ($hijo instanceof self)
                ? $hijo->obtenerCodigosInterno($visitados)
                : $hijo->obtenerCodigosPermiso();
            foreach ($subCodigos as $c) {
                $codigos[] = $c;
            }
        }
        return $codigos;
    }
}
