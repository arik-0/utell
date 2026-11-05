<?php
require_once __DIR__ . '/../Models/Usuario.php';

class AmistadRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function obtenerPorId(int $idAmistad): ?array {
        $stmt = $this->db->prepare("SELECT * FROM amigos WHERE idAmistad = :id LIMIT 1");
        $stmt->execute([':id' => $idAmistad]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
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
        $existente = $this->existeRelacion($idSolicitante, $idReceptor);
        if ($existente !== null) {
            if ($existente['estado'] === 'rechazada') {
                $stmtUpd = $this->db->prepare(
                    "UPDATE amigos SET idSolicitante = :sol, idReceptor = :rec, fechaInicio = :fecha, estado = 'pendiente'
                     WHERE idAmistad = :id"
                );
                return $stmtUpd->execute([
                    ':sol'   => $idSolicitante,
                    ':rec'   => $idReceptor,
                    ':fecha' => date('Y-m-d'),
                    ':id'    => $existente['idAmistad'],
                ]);
            }
            return false;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO amigos (idSolicitante, idReceptor, fechaInicio, estado)
             VALUES (:sol, :rec, :fecha, 'pendiente')"
        );
        return $stmt->execute([
            ':sol'   => $idSolicitante,
            ':rec'   => $idReceptor,
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
        $sql  = "SELECT
                    a.idAmistad,
                    CASE WHEN a.idSolicitante = :uid  THEN uR.idUsuario   ELSE uS.idUsuario   END AS idUsuario,
                    CASE WHEN a.idSolicitante = :uid2 THEN uR.nombre      ELSE uS.nombre      END AS nombre,
                    CASE WHEN a.idSolicitante = :uid3 THEN uR.apellido    ELSE uS.apellido    END AS apellido,
                    CASE WHEN a.idSolicitante = :uid4 THEN uR.fotoPerfil  ELSE uS.fotoPerfil  END AS fotoPerfil,
                    CASE WHEN a.idSolicitante = :uid5 THEN uR.tipoPerfil  ELSE uS.tipoPerfil  END AS tipoPerfil,
                    CASE WHEN a.idSolicitante = :uid6 THEN uR.idUniversidad ELSE uS.idUniversidad END AS idUniversidad
                 FROM amigos a
                 JOIN usuarios uS ON a.idSolicitante = uS.idUsuario
                 JOIN usuarios uR ON a.idReceptor    = uR.idUsuario
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

    /**
     * Verifica si existe un vínculo habilitante para mensajería directa (CU12 / Sección 14.5).
     * Retorna true si:
     * 1. Son amigos aceptados en la red.
     * 2. Ya existe historial previo de mensajes intercambiados entre ambos.
     * 3. Uno de los participantes es Administrador o Moderador.
     * 4. El emisor es Postulante y el receptor es Estudiante, Docente o Graduado.
     */
    public function verificarVinculoHabilitante(int $idEmisor, int $idReceptor): bool {
        // 1. Amistad aceptada
        $rel = $this->existeRelacion($idEmisor, $idReceptor);
        if ($rel && ($rel['estado'] ?? '') === 'aceptada') {
            return true;
        }

        // 2. Historial previo de mensajes (permite continuar conversaciones activas)
        try {
            $stmtHist = $this->db->prepare("
                SELECT idMensajeDirecto FROM mensajedirecto 
                WHERE (idEmisor = :e1 AND idReceptor = :r1) OR (idEmisor = :r2 AND idReceptor = :e2)
                LIMIT 1
            ");
            $stmtHist->execute([':e1' => $idEmisor, ':r1' => $idReceptor, ':r2' => $idReceptor, ':e2' => $idEmisor]);
            if ($stmtHist->fetchColumn()) {
                return true;
            }
        } catch (Exception $e) {
            // Continuar con verificación de roles
        }

        // 3. Roles y perfiles (Postulante -> Estudiante/Docente/Graduado o Admin/Mod)
        $stmtUsers = $this->db->prepare("SELECT idUsuario, tipoPerfil, idRol FROM usuarios WHERE idUsuario IN (:e, :r)");
        $stmtUsers->execute([':e' => $idEmisor, ':r' => $idReceptor]);
        $rows = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['idUsuario']] = $row;
        }

        $emisor = $map[$idEmisor] ?? null;
        $receptor = $map[$idReceptor] ?? null;

        if ($emisor && $receptor) {
            if (in_array((int)$emisor['idRol'], [1, 3], true) || in_array((int)$receptor['idRol'], [1, 3], true)) {
                return true;
            }
            if ($emisor['tipoPerfil'] === 'Postulante' && in_array($receptor['tipoPerfil'], ['Estudiante', 'Docente', 'Graduado'], true)) {
                return true;
            }
        }

        return false;
    }
}
