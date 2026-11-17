<?php
class RespuestaRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function obtenerRespuestasPorPublicacion(int $idPublicacion) {
        $sql = "SELECT r.*, u.nombre, u.apellido, u.fotoPerfil 
                FROM respuestas r 
                INNER JOIN usuarios u ON r.idUsuario = u.idUsuario
                WHERE r.idPublicacion = :id OR (r.idPublicacion IS NULL AND r.idConsulta = :id2)
                ORDER BY r.fecha ASC, r.hora ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPublicacion, ':id2' => $idPublicacion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearRespuesta(array $datos): bool {
        $idPub  = (int)$datos['idPublicacion'];
        $params = [
            ':idPublicacion' => $idPub,
            ':idUsuario'     => $datos['idUsuario'],
            ':fecha'         => date('Y-m-d'),
            ':hora'          => date('H:i:s'),
            ':texto'         => $datos['texto']
        ];

        $sql = "INSERT INTO respuestas (idPublicacion, idUsuario, fecha, hora, texto) 
                VALUES (:idPublicacion, :idUsuario, :fecha, :hora, :texto)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            try {
                $this->db->exec("ALTER TABLE respuestas MODIFY COLUMN idConsulta INT(11) DEFAULT NULL");
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($params);
            } catch (Exception $inner) {
                $sqlLegacy = "INSERT INTO respuestas (idConsulta, idPublicacion, idUsuario, fecha, hora, texto) 
                              VALUES (:idConsulta, :idPublicacion, :idUsuario, :fecha, :hora, :texto)";
                $params[':idConsulta'] = $idPub;
                $stmtLegacy = $this->db->prepare($sqlLegacy);
                return $stmtLegacy->execute($params);
            }
        }
    }

    public function obtenerPorId(int $idRespuesta): ?array {
        $stmt = $this->db->prepare("SELECT * FROM respuestas WHERE idRespuesta = :id LIMIT 1");
        $stmt->execute([':id' => $idRespuesta]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function actualizarRespuesta(int $idRespuesta, string $texto): bool {
        $stmt = $this->db->prepare("UPDATE respuestas SET texto = :texto WHERE idRespuesta = :id");
        return $stmt->execute([':texto' => trim($texto), ':id' => $idRespuesta]);
    }

    public function eliminarRespuesta(int $idRespuesta): bool {
        $stmt = $this->db->prepare("DELETE FROM respuestas WHERE idRespuesta = :id");
        return $stmt->execute([':id' => $idRespuesta]) && $stmt->rowCount() > 0;
    }
}
