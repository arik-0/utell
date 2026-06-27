<?php
class Consulta extends Publicacion {
    private bool $resuelta = false;
    private ?int $idRespuestaDestacada = null;

    public function marcarComoResuelta(int $idRespuesta): void {
        $this->resuelta = true;
        $this->idRespuestaDestacada = $idRespuesta;
    }
}