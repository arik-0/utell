<?php
// api/src/Strategies/ParaTiFeedStrategy.php

require_once __DIR__ . '/IFeedStrategy.php';
require_once __DIR__ . '/../Repositories/PublicacionRepository.php';

class ParaTiFeedStrategy implements IFeedStrategy {
    private PublicacionRepository $repository;

    public function __construct(?PublicacionRepository $repository = null) {
        $this->repository = $repository ?? new PublicacionRepository();
    }

    public function obtenerFeed(int $idUsuario): array {
        return $this->repository->obtenerParati($idUsuario);
    }
}