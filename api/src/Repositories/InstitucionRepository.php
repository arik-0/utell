<?php
class InstitucionRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function obtenerUniversidades() {
        $sql = "SELECT * FROM universidades ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el listado de universidades con métricas consolidadas de evaluación y experiencias.
     */
    public function obtenerUniversidadesConExperiencias(): array {
        $sql = "SELECT 
                    u.idUniversidad,
                    u.nombre,
                    u.email,
                    COUNT(DISTINCT p.idPublicacion) as totalPublicaciones,
                    COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) as totalExperiencias,
                    COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) as totalConsultas,
                    ROUND(COALESCE(AVG(p.puntuacionClima), 5.0), 1) as promedioClima,
                    ROUND(COALESCE(AVG(p.puntuacionInfraestructura), 5.0), 1) as promedioInfraestructura,
                    ROUND(COALESCE(AVG(p.puntuacionAcademica), 5.0), 1) as promedioAcademico,
                    ROUND(
                        (
                            COALESCE(AVG(p.puntuacionClima), 5.0) + 
                            COALESCE(AVG(p.puntuacionInfraestructura), 5.0) + 
                            COALESCE(AVG(p.puntuacionAcademica), 5.0)
                        ) / 3, 1
                    ) as promedioGeneral
                FROM universidades u
                LEFT JOIN publicaciones p ON p.idUniversidad = u.idUniversidad
                GROUP BY u.idUniversidad, u.nombre, u.email
                ORDER BY totalExperiencias DESC, totalPublicaciones DESC, u.nombre ASC";

        try {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in obtenerUniversidadesConExperiencias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la vista completa del Muro de una Universidad (Datos institucionales, métricas, sedes y publicaciones).
     */
    public function obtenerMuroUniversidad(int $idUniversidad, int $idUsuario = 0): ?array {
        // 1. Datos Institucionales
        $stmtUni = $this->db->prepare("SELECT * FROM universidades WHERE idUniversidad = :id LIMIT 1");
        $stmtUni->execute([':id' => $idUniversidad]);
        $uni = $stmtUni->fetch(PDO::FETCH_ASSOC);
        if (!$uni) return null;

        // 2. Métricas Promedio de Experiencias
        $stmtMetrics = $this->db->prepare("
            SELECT 
                COUNT(*) as totalExperiencias,
                ROUND(COALESCE(AVG(puntuacionClima), 5.0), 1) as promedioClima,
                ROUND(COALESCE(AVG(puntuacionInfraestructura), 5.0), 1) as promedioInfraestructura,
                ROUND(COALESCE(AVG(puntuacionAcademica), 5.0), 1) as promedioAcademico,
                ROUND((AVG(puntuacionClima) + AVG(puntuacionInfraestructura) + AVG(puntuacionAcademica)) / 3, 1) as promedioGeneral
            FROM publicaciones 
            WHERE tipoPublicacion = 'Experiencia' AND idUniversidad = :id
        ");
        $stmtMetrics->execute([':id' => $idUniversidad]);
        $metrics = $stmtMetrics->fetch(PDO::FETCH_ASSOC);

        // 3. Publicaciones del Muro (Experiencias y Consultas asociadas a la Universidad)
        $stmtPubs = $this->db->prepare("
            SELECT p.idPublicacion, p.idUsuario, p.tipoPublicacion, p.titulo, p.fecha, p.hora, 
                   p.idUniversidad, p.idSede, p.idCarrera, p.texto,
                   (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') as likes,
                   u.nombre, u.apellido, u.fotoPerfil, u.tipoPerfil,
                   c.nombre as nombreCarrera,
                   s.nombre as nombreSede,
                   (SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) as cantidadRespuestas,
                   (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.idUsuario = :idUsuario AND f.estado = 'activo') as hasLiked,
                   COALESCE(p.puntuacionClima, 5) as puntuacionClima,
                   COALESCE(p.puntuacionInfraestructura, 5) as puntuacionInfraestructura,
                   COALESCE(p.puntuacionAcademica, 5) as puntuacionAcademica
            FROM publicaciones p
            INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
            LEFT JOIN carreras c ON p.idCarrera = c.idCarrera
            LEFT JOIN sedes s ON p.idSede = s.idSede
            WHERE p.idUniversidad = :idUni
            ORDER BY p.fecha DESC, p.hora DESC
        ");
        $stmtPubs->execute([':idUni' => $idUniversidad, ':idUsuario' => $idUsuario]);
        $publicaciones = $stmtPubs->fetchAll(PDO::FETCH_ASSOC);

        // 4. Sedes y Carreras dictadas
        $stmtSedes = $this->db->prepare("SELECT idSede, nombre, web, email, telefono FROM sedes WHERE idUniversidad = :id ORDER BY nombre ASC");
        $stmtSedes->execute([':id' => $idUniversidad]);
        $sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);

        return [
            'universidad'   => $uni,
            'metricas'      => $metrics,
            'publicaciones' => $publicaciones,
            'sedes'         => $sedes
        ];
    }

    public function obtenerCarreras() {
        $sql = "SELECT * FROM carreras ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSedes() {
        $sql = "SELECT * FROM sedes ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Carreras dictadas en una sede especifica (via oferta_academica)
    public function obtenerCarrerasPorSede(int $idSede): array {
        $sql = "SELECT c.idCarrera, c.nombre, oa.anioImplementacion
                FROM oferta_academica oa
                INNER JOIN carreras c ON oa.idCarrera = c.idCarrera
                WHERE oa.idSede = :idSede
                ORDER BY c.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idSede' => $idSede]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sedes donde se dicta una carrera (via oferta_academica)
    public function obtenerSedesPorCarrera(int $idCarrera): array {
        $sql = "SELECT s.idSede, s.nombre, s.telefono, u.nombre as nombreUniversidad,
                       oa.anioImplementacion
                FROM oferta_academica oa
                INNER JOIN sedes s ON oa.idSede = s.idSede
                INNER JOIN universidades u ON s.idUniversidad = u.idUniversidad
                WHERE oa.idCarrera = :idCarrera
                ORDER BY s.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idCarrera' => $idCarrera]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sedes filtradas por universidad
    public function obtenerSedesPorUniversidad(int $idUniversidad): array {
        $sql = "SELECT * FROM sedes WHERE idUniversidad = :id ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUniversidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OfertaAcademica CRUD
    public function crearOfertaAcademica(int $idCarrera, int $idSede, ?int $anio): bool {
        $sql  = "INSERT INTO oferta_academica (idCarrera, idSede, anioImplementacion)
                 VALUES (:car, :sede, :anio)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':car' => $idCarrera, ':sede' => $idSede, ':anio' => $anio]);
    }

    public function eliminarOfertaAcademica(int $idOferta): bool {
        $stmt = $this->db->prepare("DELETE FROM oferta_academica WHERE idOfertaAcademica = :id");
        $stmt->execute([':id' => $idOferta]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerPaises() {
        $sql = "SELECT * FROM paises ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProvincias() {
        $sql = "SELECT * FROM provincias ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCiudades() {
        $sql = "SELECT * FROM ciudades ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
