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
                WHERE r.idPublicacion = :id
                ORDER BY r.fecha ASC, r.hora ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idPublicacion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearRespuesta(array $datos): bool {
        $sql = "INSERT INTO respuestas (idPublicacion, idUsuario, fecha, hora, texto) 
                VALUES (:idPublicacion, :idUsuario, :fecha, :hora, :texto)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':idPublicacion' => $datos['idPublicacion'],
            ':idUsuario' => $datos['idUsuario'],
            ':fecha' => date('Y-m-d'),
            ':hora' => date('H:i:s'),
            ':texto' => $datos['texto']
        ]);
    }
}
