<?php
require_once __DIR__ . '/../Repositories/RespuestaRepository.php';
require_once __DIR__ . '/../Repositories/AuditoriaRepository.php';

class RespuestaService {
    private static $instance = null;
    private RespuestaRepository $respuestaRepository;

    private function __construct() {
        $this->respuestaRepository = new RespuestaRepository();
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerRespuestasPorPublicacion(int $idPublicacion) {
        return $this->respuestaRepository->obtenerRespuestasPorPublicacion($idPublicacion);
    }

    public function crearRespuesta(array $datos): bool {
        $ok = $this->respuestaRepository->crearRespuesta($datos);
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                (int)$datos['idUsuario'],
                'CREAR_RESPUESTA',
                "Respuesta agregada en publicación #{$datos['idPublicacion']}",
                'respuestas'
            );
        }
        return $ok;
    }

    public function actualizarRespuesta(int $idRespuesta, int $idActor, bool $esStaff, string $texto): bool {
        $prev = $this->respuestaRepository->obtenerPorId($idRespuesta);
        if (!$prev) {
            throw new InvalidArgumentException("Respuesta no encontrada.");
        }
        if ((int)$prev['idUsuario'] !== $idActor && !$esStaff) {
            throw new DomainException("No tiene permisos para modificar esta respuesta.");
        }
        $textoLimpio = trim($texto);
        if ($textoLimpio === '') {
            throw new InvalidArgumentException("El texto de la respuesta no puede estar vacío.");
        }
        $ok = $this->respuestaRepository->actualizarRespuesta($idRespuesta, $textoLimpio);
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'MODIFICAR_RESPUESTA',
                "Respuesta #{$idRespuesta} actualizada por usuario #{$idActor}",
                'respuestas',
                null,
                json_encode(['texto' => $prev['texto']], JSON_UNESCAPED_UNICODE),
                json_encode(['texto' => $textoLimpio], JSON_UNESCAPED_UNICODE)
            );
        }
        return $ok;
    }

    public function eliminarRespuesta(int $idRespuesta, int $idActor, bool $esStaff): bool {
        $prev = $this->respuestaRepository->obtenerPorId($idRespuesta);
        if (!$prev) {
            throw new InvalidArgumentException("Respuesta no encontrada.");
        }
        if ((int)$prev['idUsuario'] !== $idActor && !$esStaff) {
            throw new DomainException("No tiene permisos para eliminar esta respuesta.");
        }
        $ok = $this->respuestaRepository->eliminarRespuesta($idRespuesta);
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'ELIMINAR_RESPUESTA',
                "Respuesta #{$idRespuesta} eliminada por usuario #{$idActor}",
                'respuestas',
                null,
                json_encode(['idRespuesta' => $idRespuesta, 'texto' => $prev['texto']], JSON_UNESCAPED_UNICODE),
                json_encode(['eliminado' => true])
            );
        }
        return $ok;
    }
}
