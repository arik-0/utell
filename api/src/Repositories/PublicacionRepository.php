<?php
class PublicacionRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function obtenerConsultas() {
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') as likes,
                       (SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) as cantidadRespuestas,
                       u.nombre, u.apellido, u.fotoPerfil,
                       uni.nombre as nombreUniversidad,
                       c.nombre as nombreCarrera
                FROM publicaciones p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
                LEFT JOIN universidades uni ON p.idUniversidad = uni.idUniversidad
                LEFT JOIN carreras c ON p.idCarrera = c.idCarrera
                ORDER BY p.fecha DESC, p.hora DESC";
        $resultado = $this->db->query($sql);
        return $resultado->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Algoritmo "Para ti":
     * Sistema de puntuación y recomendación que pondera afinidad académica (Universidad y Carrera),
     * círculo social (Amigos), nivel de interacción (Likes + Respuestas) y frescura temporal.
     */
    public function obtenerParati(int $idUsuario) {
        $sql = "SELECT p.idPublicacion, p.idUsuario, p.tipoPublicacion, p.titulo, p.fecha, p.hora, 
                       p.idUniversidad, p.idSede, p.idCarrera, p.texto,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') as likes,
                       (SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) as cantidadRespuestas,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.idUsuario = :idUsuarioFav AND f.estado = 'activo') as hasLiked,
                       u.nombre, u.apellido, u.fotoPerfil,
                       uni.nombre as nombreUniversidad,
                       c.nombre as nombreCarrera,
                       (
                         -- Afinidad por Carrera (+40)
                         (CASE WHEN meIns.idCarrera IS NOT NULL AND p.idCarrera = meIns.idCarrera THEN 40 ELSE 0 END) +
                         -- Afinidad por Universidad (+30)
                         (CASE WHEN meIns.idUniversidad IS NOT NULL AND p.idUniversidad = meIns.idUniversidad THEN 30 ELSE 0 END) +
                         -- Puntuación por Engagement (3 pts por like, 4 pts por respuesta)
                         ((SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') * 3) +
                         ((SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) * 4) +
                         -- Bonificación por amigos confirmados (+25)
                         (CASE WHEN p.idUsuario IN (
                             SELECT IF(a.idSolicitante = :idUsuarioFriend, a.idReceptor, a.idSolicitante)
                             FROM amigos a
                             WHERE (a.idSolicitante = :idUsuarioFriend2 OR a.idReceptor = :idUsuarioFriend3)
                               AND a.estado = 'aceptada'
                         ) THEN 25 ELSE 0 END)
                       ) as rankingScore
                FROM publicaciones p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
                LEFT JOIN inscripciones meIns ON meIns.idUsuario = :meId
                LEFT JOIN universidades uni ON p.idUniversidad = uni.idUniversidad
                LEFT JOIN carreras c ON p.idCarrera = c.idCarrera
                ORDER BY rankingScore DESC, p.fecha DESC, p.hora DESC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':idUsuarioFav'     => $idUsuario,
                ':idUsuarioFriend'  => $idUsuario,
                ':idUsuarioFriend2' => $idUsuario,
                ':idUsuarioFriend3' => $idUsuario,
                ':meId'             => $idUsuario
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in obtenerParati: " . $e->getMessage());
            return $this->obtenerConsultas();
        }
    }

    /**
     * Algoritmo "Tu red":
     * Filtra estrictamente las publicaciones creadas por los amigos aceptados del usuario,
     * pares que cursan en su misma Universidad o Carrera, y sus propias publicaciones.
     */
    public function obtenerTuRed(int $idUsuario) {
        $sql = "SELECT p.idPublicacion, p.idUsuario, p.tipoPublicacion, p.titulo, p.fecha, p.hora, 
                       p.idUniversidad, p.idSede, p.idCarrera, p.texto,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') as likes,
                       (SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) as cantidadRespuestas,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.idUsuario = :idUsuarioFav AND f.estado = 'activo') as hasLiked,
                       u.nombre, u.apellido, u.fotoPerfil,
                       uni.nombre as nombreUniversidad,
                       c.nombre as nombreCarrera,
                       (CASE WHEN p.idUsuario = :meIdSelf THEN 1
                             WHEN p.idUsuario IN (
                                 SELECT IF(a.idSolicitante = :idUsuarioFriend, a.idReceptor, a.idSolicitante)
                                 FROM amigos a
                                 WHERE (a.idSolicitante = :idUsuarioFriend2 OR a.idReceptor = :idUsuarioFriend3)
                                   AND a.estado = 'aceptada'
                             ) THEN 1 ELSE 0 END) as esAmigoDirecto
                FROM publicaciones p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
                LEFT JOIN inscripciones meIns ON meIns.idUsuario = :meId
                LEFT JOIN universidades uni ON p.idUniversidad = uni.idUniversidad
                LEFT JOIN carreras c ON p.idCarrera = c.idCarrera
                WHERE p.idUsuario = :meIdSelf2
                   OR p.idUsuario IN (
                       SELECT IF(a.idSolicitante = :idUsuarioFriend4, a.idReceptor, a.idSolicitante)
                       FROM amigos a
                       WHERE (a.idSolicitante = :idUsuarioFriend5 OR a.idReceptor = :idUsuarioFriend6)
                         AND a.estado = 'aceptada'
                   )
                   OR (meIns.idUniversidad IS NOT NULL AND p.idUniversidad = meIns.idUniversidad)
                   OR (meIns.idCarrera IS NOT NULL AND p.idCarrera = meIns.idCarrera)
                ORDER BY esAmigoDirecto DESC, p.fecha DESC, p.hora DESC";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':idUsuarioFav'     => $idUsuario,
                ':meId'             => $idUsuario,
                ':meIdSelf'         => $idUsuario,
                ':meIdSelf2'        => $idUsuario,
                ':idUsuarioFriend'  => $idUsuario,
                ':idUsuarioFriend2' => $idUsuario,
                ':idUsuarioFriend3' => $idUsuario,
                ':idUsuarioFriend4' => $idUsuario,
                ':idUsuarioFriend5' => $idUsuario,
                ':idUsuarioFriend6' => $idUsuario,
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in obtenerTuRed: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $idPublicacion, int $idUsuario) {
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') as likes,
                       (SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) as cantidadRespuestas,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.idUsuario = :idUsuario AND f.estado = 'activo') as hasLiked,
                       u.nombre, u.apellido, u.fotoPerfil,
                       uni.nombre as nombreUniversidad,
                       c.nombre as nombreCarrera
                FROM publicaciones p 
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
                LEFT JOIN universidades uni ON p.idUniversidad = uni.idUniversidad
                LEFT JOIN carreras c ON p.idCarrera = c.idCarrera
                WHERE p.idPublicacion = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idPublicacion);
        $stmt->bindParam(':idUsuario', $idUsuario);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    private function asegurarRelacionesPredeterminadas(?int &$idUni, ?int &$idSede, ?int &$idCarrera): void {
        try {
            if (!empty($idUni) && empty($idSede)) {
                $stmt = $this->db->prepare("SELECT idSede FROM sedes WHERE idUniversidad = :idUni LIMIT 1");
                $stmt->execute([':idUni' => $idUni]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $idSede = (int)$row['idSede'];
                }
            }
        } catch (Exception $e) {
            // Silencioso
        }
    }

    public function crear(array $datos): bool {
        $tipo = $datos['tipoPublicacion'] ?? 'Consulta';
        $titulo = $datos['titulo'] ?? '';
        $texto = $datos['texto'] ?? '';
        $fecha = $datos['fecha'] ?? date('Y-m-d');
        $hora = $datos['hora'] ?? date('H:i:s');
        $idUsuario = (int)$datos['idUsuario'];
        
        $idUni = !empty($datos['idUniversidad']) ? (int)$datos['idUniversidad'] : null;
        $idSede = !empty($datos['idSede']) ? (int)$datos['idSede'] : null;
        $idCarrera = !empty($datos['idCarrera']) ? (int)$datos['idCarrera'] : null;

        $this->asegurarRelacionesPredeterminadas($idUni, $idSede, $idCarrera);

        // Las puntuaciones solo aplican a Experiencias (NULL en Consultas)
        $esExperiencia = ($tipo === 'Experiencia');
        $pClima = $esExperiencia ? (isset($datos['puntuacionClima']) ? (int)$datos['puntuacionClima'] : 5) : null;
        $pInfra = $esExperiencia ? (isset($datos['puntuacionInfraestructura']) ? (int)$datos['puntuacionInfraestructura'] : 5) : null;
        $pAcad  = $esExperiencia ? (isset($datos['puntuacionAcademica']) ? (int)$datos['puntuacionAcademica'] : 5) : null;

        $sqlPub = "INSERT INTO publicaciones (idUsuario, tipoPublicacion, titulo, fecha, hora, idUniversidad, idSede, idCarrera, texto, likes, puntuacionClima, puntuacionInfraestructura, puntuacionAcademica) 
                   VALUES (:idUsuario, :tipoPublicacion, :titulo, :fecha, :hora, :idUniversidad, :idSede, :idCarrera, :texto, 0, :pClima, :pInfra, :pAcad)";
        $stmtPub = $this->db->prepare($sqlPub);
        return $stmtPub->execute([
            ':idUsuario' => $idUsuario,
            ':tipoPublicacion' => $tipo,
            ':titulo' => $titulo,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':idUniversidad' => $idUni,
            ':idSede' => $idSede,
            ':idCarrera' => $idCarrera,
            ':texto' => $texto,
            ':pClima' => $pClima,
            ':pInfra' => $pInfra,
            ':pAcad' => $pAcad
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
        $totalLikes = (int)$countStmt->fetchColumn();
        
        $updatePubStmt = $this->db->prepare("UPDATE publicaciones SET likes = :likes WHERE idPublicacion = :idPub");
        $updatePubStmt->execute([':likes' => $totalLikes, ':idPub' => $idPublicacion]);
        
        return $totalLikes;
    }
}