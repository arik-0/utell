<?php

class Permiso {
    private string $nombre;
    private string $codigoAccion;
    /** @var DetalleGrupoPermiso[] */
    private array $detallesGrupo = [];
    /** @var GrupoPermisoPermiso[] */
    private array $gruposRelacionados = [];

    public function __construct(string $nombre, string $codigoAccion) {
        $this->nombre       = $nombre;
        $this->codigoAccion = $codigoAccion;
    }

    public function getNombre(): string { return $this->nombre; }
    public function getCodigoAccion(): string { return $this->codigoAccion; }

    /** @return DetalleGrupoPermiso[] */
    public function getDetallesGrupo(): array { return $this->detallesGrupo; }
    public function agregarDetalleGrupo(DetalleGrupoPermiso $detalle): void { $this->detallesGrupo[] = $detalle; }

    /** @return GrupoPermisoPermiso[] */
    public function getGruposRelacionados(): array { return $this->gruposRelacionados; }
    public function agregarGrupoRelacionado(GrupoPermisoPermiso $rel): void { $this->gruposRelacionados[] = $rel; }
}
