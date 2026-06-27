<?php
class PublicacionRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function obtenerConsultas() {
        $sql = "SELECT * FROM consultas";
        $resultado = $this->db->query($sql);
        return $resultado->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerParati(int $idUsuario) {
        $sql = "SELECT p.*, u.nombre, u.apellido, u.fotoPerfil,
                       (SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) as cantidadRespuestas,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.idUsuario = :idUsuario AND f.estado = 'activo') as hasLiked,
                       uni.nombre as nombreUniversidad
                FROM publicaciones p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
                LEFT JOIN universidades uni ON p.idUniversidad = uni.idUniversidad
                ORDER BY p.fecha DESC, p.hora DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $idPublicacion, int $idUsuario) {
        $sql = "SELECT p.*, u.nombre, u.apellido, u.fotoPerfil,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.idUsuario = :idUsuario AND f.estado = 'activo') as hasLiked,
                       uni.nombre as nombreUniversidad
                FROM publicaciones p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
                LEFT JOIN universidades uni ON p.idUniversidad = uni.idUniversidad
                WHERE p.idPublicacion = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idPublicacion);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? $fila : null;
    }

    public function crear(array $datos): bool {
        $sql = "INSERT INTO publicaciones (idUsuario, tipoPublicacion, titulo, fecha, hora, idUniversidad, idSede, idCarrera, texto, likes, resuelta) 
                VALUES (:idUsuario, :tipoPublicacion, :titulo, :fecha, :hora, :idUniversidad, :idSede, :idCarrera, :texto, :likes, 0)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':idUsuario' => $datos['idUsuario'],
            ':tipoPublicacion' => $datos['tipoPublicacion'],
            ':titulo' => $datos['titulo'],
            ':fecha' => $datos['fecha'],
            ':hora' => $datos['hora'],
            ':idUniversidad' => $datos['idUniversidad'],
            ':idSede' => $datos['idSede'],
            ':idCarrera' => $datos['idCarrera'],
            ':texto' => $datos['texto'],
            ':likes' => 0
        ]);
    }

    public function obtenerFavorito(int $idPublicacion, int $idUsuario) {
        $stmt = $this->db->prepare("SELECT idFavorito, estado FROM favoritos WHERE idPublicacion = :idPub AND idUsuario = :idUsr");
        $stmt->execute([':idPub' => $idPublicacion, ':idUsr' => $idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarFavorito(int $idFavorito, string $estado): void {
        $stmt = $this->db->prepare("UPDATE favoritos SET estado = :estado WHERE idFavorito = :idFav");
        $stmt->execute([':estado' => $estado, ':idFav' => $idFavorito]);
    }

    public function insertarFavorito(int $idPublicacion, int $idUsuario): void {
        $stmt = $this->db->prepare("INSERT INTO favoritos (idUsuario, idPublicacion, fecha, hora, estado) VALUES (:idUsr, :idPub, :fecha, :hora, 'activo')");
        $stmt->execute([
            ':idUsr' => $idUsuario, 
            ':idPub' => $idPublicacion,
            ':fecha' => date('Y-m-d'),
            ':hora' => date('H:i:s')
        ]);
    }

    public function actualizarLikesPublicacion(int $idPublicacion): int {
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM favoritos WHERE idPublicacion = :idPub AND estado = 'activo'");
        $countStmt->execute([':idPub' => $idPublicacion]);
        $totalLikes = $countStmt->fetchColumn();
        
        $updatePubStmt = $this->db->prepare("UPDATE publicaciones SET likes = :likes WHERE idPublicacion = :idPub");
        $updatePubStmt->execute([':likes' => $totalLikes, ':idPub' => $idPublicacion]);
        
        return $totalLikes;
    }
}