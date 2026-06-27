<?php
require_once __DIR__ . '/../Repositories/PublicacionRepository.php';

class PublicacionService {
    private static $instance = null;
    private PublicacionRepository $publicacionRepository;

    private function __construct() {
        $this->publicacionRepository = new PublicacionRepository();
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerConsultas() {
        return $this->publicacionRepository->obtenerConsultas();
    }

    public function obtenerParati(int $idUsuario) {
        return $this->publicacionRepository->obtenerParati($idUsuario);
    }

    public function obtenerPorId(int $idPublicacion, int $idUsuario) {
        return $this->publicacionRepository->obtenerPorId($idPublicacion, $idUsuario);
    }

    public function crear(array $datos): bool {
        // Here we could resolve Universidad and Carrera names to IDs before saving
        // But assuming the route or a separate InstitutionService handles it
        // Or we handle it directly here if needed.
        return $this->publicacionRepository->crear($datos);
    }

    public function toggleLike(int $idPublicacion, int $idUsuario): array {
        $favorito = $this->publicacionRepository->obtenerFavorito($idPublicacion, $idUsuario);
        $isLiking = true;
        
        if ($favorito) {
            if ($favorito['estado'] == 'activo') {
                $this->publicacionRepository->actualizarFavorito($favorito['idFavorito'], 'inactivo');
                $isLiking = false;
            } else {
                $this->publicacionRepository->actualizarFavorito($favorito['idFavorito'], 'activo');
            }
        } else {
            $this->publicacionRepository->insertarFavorito($idPublicacion, $idUsuario);
        }
        
        $totalLikes = $this->publicacionRepository->actualizarLikesPublicacion($idPublicacion);
        
        return [
            "likedByUser" => $isLiking,
            "totalLikes" => $totalLikes
        ];
    }
}
