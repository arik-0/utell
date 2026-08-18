<?php
require_once __DIR__ . '/../Repositories/AmistadRepository.php';

class AmistadService {
    private static $instance = null;
    private AmistadRepository $repo;

    private function __construct() {
        $this->repo = new AmistadRepository();
    }

    private function __clone() {}

    public static function getInstance(): AmistadService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enviarSolicitud(int $idSolicitante, int $idReceptor): array {
        if ($idSolicitante === $idReceptor) {
            throw new Exception("No puedes enviarte una solicitud a ti mismo.");
        }

        $relacion = $this->repo->existeRelacion($idSolicitante, $idReceptor);
        if ($relacion !== null) {
            throw new Exception("Ya existe una relacion entre estos usuarios.");
        }

        $ok = $this->repo->enviarSolicitud($idSolicitante, $idReceptor);
        if (!$ok) {
            throw new Exception("No se pudo enviar la solicitud.");
        }

        return ['success' => true, 'mensaje' => 'Solicitud enviada.'];
    }

    public function responderSolicitud(int $idAmistad, string $accion): array {
        if ($accion === 'aceptar') {
            $ok = $this->repo->aceptarSolicitud($idAmistad);
            $msg = 'Solicitud aceptada.';
        } elseif ($accion === 'rechazar') {
            $ok = $this->repo->rechazarSolicitud($idAmistad);
            $msg = 'Solicitud rechazada.';
        } else {
            throw new Exception("Accion invalida. Use 'aceptar' o 'rechazar'.");
        }

        return ['success' => $ok, 'mensaje' => $msg];
    }

    public function eliminarAmistad(int $idAmistad): array {
        $ok = $this->repo->eliminarAmistad($idAmistad);
        return ['success' => $ok, 'mensaje' => $ok ? 'Amistad eliminada.' : 'No se encontro la amistad.'];
    }

    public function obtenerAmigos(int $idUsuario): array {
        return $this->repo->obtenerAmigos($idUsuario);
    }

    public function obtenerSolicitudesPendientes(int $idUsuario): array {
        $recibidas = $this->repo->obtenerSolicitudesRecibidas($idUsuario);
        $enviadas  = $this->repo->obtenerSolicitudesEnviadas($idUsuario);

        foreach ($recibidas as &$s) { $s['tipo'] = 'recibida'; }
        foreach ($enviadas  as &$s) { $s['tipo'] = 'enviada'; }

        return array_merge($recibidas, $enviadas);
    }

    public function obtenerEstadoRelacion(int $idUsuario, int $idOtro): array {
        $relacion = $this->repo->existeRelacion($idUsuario, $idOtro);

        if ($relacion === null) {
            return ['estado' => 'ninguno', 'idAmistad' => null];
        }

        if ($relacion['estado'] === 'aceptada') {
            return ['estado' => 'aceptada', 'idAmistad' => (int)$relacion['idAmistad']];
        }

        if ($relacion['estado'] === 'rechazada') {
            return ['estado' => 'ninguno', 'idAmistad' => null];
        }

        // Pendiente — determinar si fue enviada o recibida por idUsuario
        if ((int)$relacion['idSolicitante'] === $idUsuario) {
            return ['estado' => 'pendiente_enviada', 'idAmistad' => (int)$relacion['idAmistad']];
        }

        return ['estado' => 'pendiente_recibida', 'idAmistad' => (int)$relacion['idAmistad']];
    }
}
