<?php
require_once __DIR__ . '/../Models/Usuario.php';

class AmistadRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function existeRelacion(int $idA, int $idB): ?array {
        $sql  = "SELECT * FROM amigos
                 WHERE (idSolicitante = :a AND idReceptor = :b)
                    OR (idSolicitante = :b2 AND idReceptor = :a2)
                 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':a' => $idA, ':b' => $idB, ':b2' => $idB, ':a2' => $idA]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function enviarSolicitud(int $idSolicitante, int $idReceptor): bool {
        if ($this->existeRelacion($idSolicitante, $idReceptor) !== null) {
            return false;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO amigos (idSolicitante, idReceptor, fechaInicio, estado)
             VALUES (:sol, :rec, :fecha, 'pendiente')"
        );
        return $stmt->execute([
            ':sol'  => $idSolicitante,
            ':rec'  => $idReceptor,
            ':fecha' => date('Y-m-d'),
        ]);
    }

    public function aceptarSolicitud(int $idAmistad): bool {
        $stmt = $this->db->prepare(
            "UPDATE amigos SET estado = 'aceptada'
             WHERE idAmistad = :id AND estado = 'pendiente'"
        );
        $stmt->execute([':id' => $idAmistad]);
        return $stmt->rowCount() > 0;
    }

    public function rechazarSolicitud(int $idAmistad): bool {
        $stmt = $this->db->prepare(
            "UPDATE amigos SET estado = 'rechazada' WHERE idAmistad = :id"
        );
        $stmt->execute([':id' => $idAmistad]);
        return $stmt->rowCount() > 0;
    }

    public function eliminarAmistad(int $idAmistad): bool {
        $stmt = $this->db->prepare("DELETE FROM amigos WHERE idAmistad = :id");
        $stmt->execute([':id' => $idAmistad]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerAmigos(int $idUsuario): array {
        // Use a single parameter binding compatible with named params
        $sql  = "SELECT
                    a.idAmistad,
                    CASE WHEN a.idSolicitante = :uid  THEN uR.idUsuario   ELSE uS.idUsuario   END AS idUsuario,
                    CASE WHEN a.idSolicitante = :uid2 THEN uR.nombre      ELSE uS.nombre      END AS nombre,
                    CASE WHEN a.idSolicitante = :uid3 THEN uR.apellido    ELSE uS.apellido    END AS apellido,
                    CASE WHEN a.idSolicitante = :uid4 THEN uR.fotoPerfil  ELSE uS.fotoPerfil  END AS fotoPerfil,
                    CASE WHEN a.idSolicitante = :uid5 THEN uR.tipoPerfil  ELSE uS.tipoPerfil  END AS tipoPerfil,
                    CASE WHEN a.idSolicitante = :uid6 THEN insR.idUniversidad ELSE insS.idUniversidad END AS idUniversidad
                 FROM amigos a
                 JOIN usuarios uS ON a.idSolicitante = uS.idUsuario
                 JOIN usuarios uR ON a.idReceptor    = uR.idUsuario
                 LEFT JOIN inscripciones insS ON uS.idUsuario = insS.idUsuario
                 LEFT JOIN inscripciones insR ON uR.idUsuario = insR.idUsuario
                 WHERE (a.idSolicitante = :uid7 OR a.idReceptor = :uid8)
                   AND a.estado = 'aceptada'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':uid'  => $idUsuario, ':uid2' => $idUsuario,
            ':uid3' => $idUsuario, ':uid4' => $idUsuario,
            ':uid5' => $idUsuario, ':uid6' => $idUsuario,
            ':uid7' => $idUsuario, ':uid8' => $idUsuario,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSolicitudesRecibidas(int $idUsuario): array {
        $sql  = "SELECT a.idAmistad, u.idUsuario, u.nombre, u.apellido, u.fotoPerfil
                 FROM amigos a
                 JOIN usuarios u ON u.idUsuario = a.idSolicitante
                 WHERE a.idReceptor = :id AND a.estado = 'pendiente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSolicitudesEnviadas(int $idUsuario): array {
        $sql  = "SELECT a.idAmistad, u.idUsuario, u.nombre, u.apellido, u.fotoPerfil
                 FROM amigos a
                 JOIN usuarios u ON u.idUsuario = a.idReceptor
                 WHERE a.idSolicitante = :id AND a.estado = 'pendiente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
