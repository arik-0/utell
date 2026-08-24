<?php
require_once __DIR__ . '/../Repositories/MensajeRepository.php';

class MensajeService {
    private static $instance = null;
    private MensajeRepository $repo;

    private function __construct() {
        $this->repo = new MensajeRepository();
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
            throw new Exception("No puedes enviarte un mensaje directo a ti mismo.");
        }

        $textoLimpio = trim($texto);
        if (empty($textoLimpio)) {
            throw new Exception("El contenido del mensaje no puede estar vacío.");
        }

        $mensaje = $this->repo->enviarMensaje($idEmisor, $idReceptor, $textoLimpio);
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
}
