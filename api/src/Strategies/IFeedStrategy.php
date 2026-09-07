<?php
// api/src/Strategies/IFeedStrategy.php

interface IFeedStrategy {
    /**
     * Obtiene el listado de publicaciones procesadas según el algoritmo de la estrategia.
     * @param int $idUsuario ID del usuario que consulta el feed
     * @return array Lista de publicaciones ordenadas
     */
    public function obtenerFeed(int $idUsuario): array;
}