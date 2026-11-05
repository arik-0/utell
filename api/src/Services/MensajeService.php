<?php
require_once __DIR__ . '/../Repositories/MensajeRepository.php';
require_once __DIR__ . '/../Repositories/AmistadRepository.php';
require_once __DIR__ . '/../Repositories/AuditoriaRepository.php';
require_once __DIR__ . '/../Models/Mensaje.php';

class MensajeService {
    private static $instance = null;
    private MensajeRepository $repo;
    private AmistadRepository $amistadRepo;

    private function __construct() {
        $this->repo = new MensajeRepository();
        $this->amistadRepo = new AmistadRepository();
    }

    private function __clone() {}

    public static function getInstance(): MensajeService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function enviar(int $idEmisor, int $idReceptor, string $texto): array {
        if ($idEmisor === $idReceptor) {
            throw new InvalidArgumentException("No puedes enviarte un mensaje directo a ti mismo.");
        }

        $textoLimpio = trim($texto);
        if (empty($textoLimpio)) {
            throw new InvalidArgumentException("El contenido del mensaje no puede estar vacío.");
        }
        if (mb_strlen($textoLimpio) > 2000) {
            throw new InvalidArgumentException("El mensaje supera el límite máximo de 2000 caracteres.");
        }

        // Regla de Vínculo Habilitante (CU12 / Sección 14.5)
        if (!$this->amistadRepo->verificarVinculoHabilitante($idEmisor, $idReceptor)) {
            throw new DomainException("Para intercambiar mensajes privados, ambos usuarios deben ser amigos en la red o debes tener perfil de Postulante.");
        }

        $entidad = new Mensaje($idEmisor, $idReceptor, $textoLimpio);
        $mensaje = $this->repo->enviarMensaje(
            $entidad->getIdEmisor(),
            $entidad->getIdReceptor(),
            $entidad->getContenidoTexto()
        );

        (new AuditoriaRepository())->registrar(
            $idEmisor,
            'ENVIAR_MENSAJE',
            "Mensaje directo enviado a usuario ID {$idReceptor}",
            'mensajedirecto'
        );

        return [
            'success' => true,
            'mensaje' => 'Mensaje enviado con éxito.',
            'data'    => $mensaje
        ];
    }

    public function obtenerConversaciones(int $idUsuario): array {
        return $this->repo->obtenerConversaciones($idUsuario);
    }

    public function obtenerChat(int $idUsuario, int $idContacto): array {
        return $this->repo->obtenerChat($idUsuario, $idContacto);
    }

    public function obtenerTotalNoLeidos(int $idUsuario): int {
        return $this->repo->obtenerConteoNoLeidos($idUsuario);
    }

    public function ocultarMensaje(int $idMensaje, int $idUsuario): bool {
        return $this->repo->ocultarMensaje($idMensaje, $idUsuario);
    }
}
