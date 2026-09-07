<?php
require_once __DIR__ . '/../config/db.php';

class MensajeRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function enviarMensaje(int $idEmisor, int $idReceptor, string $texto): array {
        $fecha = date('Y-m-d');
        $hora  = date('H:i:s');

        $stmt = $this->db->prepare(
            "INSERT INTO mensajedirecto (idEmisor, idReceptor, fechaMensaje, horaMensaje, textoMensaje, leido) 
             VALUES (:emisor, :receptor, :fecha, :hora, :texto, 0)"
        );
        $stmt->execute([
            ':emisor'   => $idEmisor,
            ':receptor' => $idReceptor,
            ':fecha'    => $fecha,
            ':hora'     => $hora,
            ':texto'    => $texto
        ]);

        $id = (int)$this->db->lastInsertId();

        return [
            'idMensajeDirecto' => $id,
            'idEmisor'         => $idEmisor,
            'idReceptor'       => $idReceptor,
            'fechaMensaje'     => $fecha,
            'horaMensaje'      => $hora,
            'textoMensaje'     => $texto,
            'leido'            => 0
        ];
    }

    public function obtenerConversaciones(int $idUsuario): array {
        // Obtenemos los usuarios con los que se ha tenido intercambio de mensajes
        $sql = "SELECT 
                    u.idUsuario,
                    u.nombre,
                    u.apellido,
                    u.fotoPerfil,
                    u.tipoPerfil,
                    m.textoMensaje as ultimoMensaje,
                    m.fechaMensaje as ultimaFecha,
                    m.horaMensaje as ultimaHora,
                    m.idEmisor as ultimoEmisor,
                    (SELECT COUNT(*) 
                     FROM mensajedirecto unread 
                     WHERE unread.idEmisor = u.idUsuario 
                       AND unread.idReceptor = :idUsuarioUnread 
                       AND unread.leido = 0) as noLeidos
                FROM (
                    SELECT 
                        CASE WHEN idEmisor = :idUsuario1 THEN idReceptor ELSE idEmisor END AS idContacto,
                        MAX(idMensajeDirecto) as maxId
                    FROM mensajedirecto
                    WHERE idEmisor = :idUsuario2 OR idReceptor = :idUsuario3
                    GROUP BY idContacto
                ) ultimos
                INNER JOIN usuarios u ON u.idUsuario = ultimos.idContacto
                INNER JOIN mensajedirecto m ON m.idMensajeDirecto = ultimos.maxId
                ORDER BY m.fechaMensaje DESC, m.horaMensaje DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':idUsuarioUnread' => $idUsuario,
                ':idUsuario1'      => $idUsuario,
                ':idUsuario2'      => $idUsuario,
                ':idUsuario3'      => $idUsuario,
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in obtenerConversaciones: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerChat(int $idUsuario, int $idContacto): array {
        // 1. Marcar como leídos los mensajes que envió el contacto a este usuario
        $updateStmt = $this->db->prepare(
            "UPDATE mensajedirecto 
             SET leido = 1 
             WHERE idEmisor = :contacto AND idReceptor = :usuario AND leido = 0"
        );
        $updateStmt->execute([':contacto' => $idContacto, ':usuario' => $idUsuario]);

        // 2. Obtener el historial completo
        $sql = "SELECT idMensajeDirecto, idEmisor, idReceptor, fechaMensaje, horaMensaje, textoMensaje, leido
                FROM mensajedirecto
                WHERE (idEmisor = :id1 AND idReceptor = :id2)
                   OR (idEmisor = :id3 AND idReceptor = :id4)
                ORDER BY fechaMensaje ASC, horaMensaje ASC, idMensajeDirecto ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id1' => $idUsuario,
            ':id2' => $idContacto,
            ':id3' => $idContacto,
            ':id4' => $idUsuario,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerConteoNoLeidos(int $idUsuario): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM mensajedirecto WHERE idReceptor = :id AND leido = 0"
        );
        $stmt->execute([':id' => $idUsuario]);
        return (int)$stmt->fetchColumn();
    }
}
