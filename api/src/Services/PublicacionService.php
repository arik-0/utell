<?php
require_once __DIR__ . '/../Repositories/PublicacionRepository.php';
require_once __DIR__ . '/../Repositories/AuditoriaRepository.php';
require_once __DIR__ . '/../Strategies/IFeedStrategy.php';
require_once __DIR__ . '/../Strategies/ParaTiFeedStrategy.php';
require_once __DIR__ . '/../Strategies/TuRedFeedStrategy.php';
require_once __DIR__ . '/../Factories/PublicacionFactory.php';

class PublicacionService {
    private static $instance = null;
    private PublicacionRepository $publicacionRepository;

    private function __construct() {
        $this->publicacionRepository = new PublicacionRepository();
    }

    private function __clone() {}

    public static function getInstance(): self {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerConsultas(int $idUsuario = 0): array {
        return $this->publicacionRepository->obtenerConsultas($idUsuario);
    }

    /**
     * Patrón Strategy: Obtiene el feed según el algoritmo suministrado
     */
    public function obtenerFeedSegunEstrategia(IFeedStrategy $strategy, int $idUsuario): array {
        return $strategy->obtenerFeed($idUsuario);
    }

    /**
     * Feed "Para ti": Delega a ParaTiFeedStrategy
     */
    public function obtenerParati(int $idUsuario): array {
        return $this->obtenerFeedSegunEstrategia(new ParaTiFeedStrategy($this->publicacionRepository), $idUsuario);
    }

    /**
     * Feed "Tu red": Delega a TuRedFeedStrategy
     */
    public function obtenerTuRed(int $idUsuario): array {
        return $this->obtenerFeedSegunEstrategia(new TuRedFeedStrategy($this->publicacionRepository), $idUsuario);
    }

    public function obtenerPorId(int $idPublicacion, int $idUsuario) {
        return $this->publicacionRepository->obtenerPorId($idPublicacion, $idUsuario);
    }

    /**
     * Patrón Factory Method: Normaliza y valida la publicación antes de persistir
     */
    public function crear(array $datos): bool {
        $datosValidados = PublicacionFactory::crear($datos);
        $idPub = $this->publicacionRepository->crear($datosValidados);
        if ($idPub > 0) {
            $tipo = $datosValidados['tipoPublicacion'] ?? 'Consulta';
            (new AuditoriaRepository())->registrar(
                (int)$datosValidados['idUsuario'],
                'CREAR_PUBLICACION',
                "Alta de {$tipo} ID {$idPub}",
                'publicaciones'
            );
            return true;
        }
        return false;
    }

    public function toggleLike(int $idPublicacion, int $idUsuario): array {
        $favorito = $this->publicacionRepository->obtenerFavorito($idPublicacion, $idUsuario);
        $isLiking = true;

        if ($favorito) {
            if ($favorito['estado'] == 'activo') {
                $this->publicacionRepository->actualizarFavorito((int)$favorito['idFavorito'], 'inactivo');
                $isLiking = false;
            } else {
                $this->publicacionRepository->actualizarFavorito((int)$favorito['idFavorito'], 'activo');
            }
        } else {
            $this->publicacionRepository->insertarFavorito($idPublicacion, $idUsuario);
        }

        $totalLikes = $this->publicacionRepository->actualizarLikesPublicacion($idPublicacion);

        return [
            "likedByUser" => $isLiking,
            "totalLikes"  => $totalLikes
        ];
    }
}
