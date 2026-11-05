<?php
class InstitucionRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function obtenerUniversidades() {
        $sql = "SELECT idUniversidad, nombre, email, celular, estado FROM universidades ORDER BY nombre ASC";
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
                    CASE WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                         THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.puntuacionClima END), 1) 
                         ELSE NULL END as promedioClima,
                    CASE WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                         THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.puntuacionInfraestructura END), 1) 
                         ELSE NULL END as promedioInfraestructura,
                    CASE WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                         THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.puntuacionAcademica END), 1) 
                         ELSE NULL END as promedioAcademico,
                    CASE WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                         THEN ROUND(
                             (
                                 AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.puntuacionClima END) + 
                                 AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.puntuacionInfraestructura END) + 
                                 AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.puntuacionAcademica END)
                             ) / 3, 1
                         ) ELSE NULL END as promedioGeneral
                FROM universidades u
                LEFT JOIN publicaciones p ON p.idUniversidad = u.idUniversidad AND COALESCE(p.estado, 'activo') = 'activo'
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
        try {
            $stmtUni = $this->db->prepare("SELECT idUniversidad, nombre, email, celular, estado FROM universidades WHERE idUniversidad = :id LIMIT 1");
            $stmtUni->execute([':id' => $idUniversidad]);
            $uni = $stmtUni->fetch(PDO::FETCH_ASSOC);
            if (!$uni) return null;

            // 1. Métricas consolidadas de la institución
            $metrics = [
                'totalExperiencias'        => 0,
                'promedioClima'            => null,
                'promedioInfraestructura'  => null,
                'promedioAcademico'        => null,
                'promedioGeneral'          => null,
            ];
            try {
                $stmtMetrics = $this->db->prepare("
                    SELECT 
                        COUNT(*) as totalExperiencias,
                        CASE WHEN COUNT(*) > 0 THEN ROUND(AVG(puntuacionClima), 1) ELSE NULL END as promedioClima,
                        CASE WHEN COUNT(*) > 0 THEN ROUND(AVG(puntuacionInfraestructura), 1) ELSE NULL END as promedioInfraestructura,
                        CASE WHEN COUNT(*) > 0 THEN ROUND(AVG(puntuacionAcademica), 1) ELSE NULL END as promedioAcademico,
                        CASE WHEN COUNT(*) > 0 THEN ROUND((AVG(puntuacionClima) + AVG(puntuacionInfraestructura) + AVG(puntuacionAcademica)) / 3, 1) ELSE NULL END as promedioGeneral
                    FROM publicaciones 
                    WHERE tipoPublicacion = 'Experiencia'
                      AND idUniversidad = :id
                      AND COALESCE(estado, 'activo') = 'activo'
                ");
                $stmtMetrics->execute([':id' => $idUniversidad]);
                $metricsRow = $stmtMetrics->fetch(PDO::FETCH_ASSOC);
                if ($metricsRow) {
                    $metrics = $metricsRow;
                }
            } catch (Exception $eMetrics) {
                error_log("Error al calcular metricas del muro institucional ({$idUniversidad}): " . $eMetrics->getMessage());
            }

            // 2. Publicaciones (Experiencias y Consultas) de la institución
            $publicaciones = [];
            try {
                $stmtPubs = $this->db->prepare("
                    SELECT p.idPublicacion, p.idUsuario, p.tipoPublicacion, p.titulo, p.fecha, p.hora, 
                           p.idUniversidad, p.idSede, p.idCarrera, p.texto, p.resuelta,
                           (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') as likes,
                           u.nombre, u.apellido, u.fotoPerfil, u.tipoPerfil,
                           c.nombre as nombreCarrera,
                           s.nombre as nombreSede,
                           (SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) as cantidadRespuestas,
                           (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.idUsuario = :idUsuario AND f.estado = 'activo') as hasLiked,
                           p.puntuacionClima,
                           p.puntuacionInfraestructura,
                           p.puntuacionAcademica
                    FROM publicaciones p
                    INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
                    LEFT JOIN carreras c ON p.idCarrera = c.idCarrera
                    LEFT JOIN sedes s ON p.idSede = s.idSede
                    WHERE p.idUniversidad = :idUni
                      AND COALESCE(p.estado, 'activo') = 'activo'
                    ORDER BY p.fecha DESC, p.hora DESC
                ");
                $stmtPubs->execute([':idUni' => $idUniversidad, ':idUsuario' => $idUsuario]);
                $publicaciones = $stmtPubs->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Exception $ePubs) {
                error_log("Error al obtener publicaciones del muro institucional ({$idUniversidad}): " . $ePubs->getMessage());
            }

            // 3. Sedes de la institución (con resolución de ciudad como descripción)
            $sedes = [];
            try {
                $stmtSedes = $this->db->prepare("
                    SELECT s.idSede, s.nombre, s.web, s.email, s.telefono, s.estado,
                           ci.nombre AS descripcion, ci.nombre AS ciudad
                    FROM sedes s
                    LEFT JOIN ciudades ci ON s.idCiudad = ci.idCiudad
                    WHERE s.idUniversidad = :id
                    ORDER BY s.nombre ASC
                ");
                $stmtSedes->execute([':id' => $idUniversidad]);
                $sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Exception $eSedes) {
                error_log("Error al obtener sedes del muro institucional ({$idUniversidad}): " . $eSedes->getMessage());
            }

            return [
                'universidad'   => $uni,
                'metricas'      => $metrics,
                'publicaciones' => $publicaciones,
                'sedes'         => $sedes,
            ];
        } catch (Exception $e) {
            error_log("Error in obtenerMuroUniversidad ({$idUniversidad}): " . $e->getMessage());
            throw $e;
        }
    }

    public function obtenerCarreras() {
        $sql = "SELECT * FROM carreras ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSedes() {
        $sql = "SELECT * FROM sedes ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCarrerasPorSede(int $idSede): array {
        $sql = "SELECT c.idCarrera, c.nombre,
                       YEAR(oa.fechaImplementacion) AS anioImplementacion,
                       oa.fechaImplementacion, oa.descripcion, oa.web, oa.telefono
                FROM oferta_academica oa
                INNER JOIN carreras c ON oa.idCarrera = c.idCarrera
                WHERE oa.idSede = :idSede
                ORDER BY c.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idSede' => $idSede]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSedesPorCarrera(int $idCarrera): array {
        $sql = "SELECT s.idSede, s.nombre, s.telefono, u.nombre as nombreUniversidad,
                       YEAR(oa.fechaImplementacion) AS anioImplementacion,
                       oa.fechaImplementacion
                FROM oferta_academica oa
                INNER JOIN sedes s ON oa.idSede = s.idSede
                INNER JOIN universidades u ON s.idUniversidad = u.idUniversidad
                WHERE oa.idCarrera = :idCarrera
                ORDER BY s.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idCarrera' => $idCarrera]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSedesPorUniversidad(int $idUniversidad): array {
        $sql = "SELECT * FROM sedes WHERE idUniversidad = :id ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUniversidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearOfertaAcademica(int $idCarrera, int $idSede, ?int $anio): bool {
        $fecha = ($anio && $anio > 1800) ? sprintf('%04d-03-01', $anio) : null;
        $sql  = "INSERT INTO oferta_academica (idCarrera, idSede, fechaImplementacion)
                 VALUES (:car, :sede, :fecha)
                 ON DUPLICATE KEY UPDATE fechaImplementacion = COALESCE(VALUES(fechaImplementacion), fechaImplementacion)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':car' => $idCarrera, ':sede' => $idSede, ':fecha' => $fecha]);
    }

    public function eliminarOfertaAcademica(int $idOferta): bool {
        $stmt = $this->db->prepare("DELETE FROM oferta_academica WHERE idOfertaAcademica = :id");
        $stmt->execute([':id' => $idOferta]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Resuelve IDs académicos a partir de nombres si el cliente envió nombres textuales.
     */
    public function resolverIdsAcademicos(
        ?int $idUniversidad,
        ?int $idSede,
        ?int $idCarrera,
        ?string $nombreUniversidad = null,
        ?string $nombreSede = null,
        ?string $nombreCarrera = null
    ): array {
        $idUni = $idUniversidad ?: null;
        if (empty($idUni) && !empty($nombreUniversidad)) {
            $stmt = $this->db->prepare("SELECT idUniversidad FROM universidades WHERE nombre = :nombre LIMIT 1");
            $stmt->execute([':nombre' => trim($nombreUniversidad)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $idUni = (int)$row['idUniversidad'];
            }
        }

        $idSed = $idSede ?: null;
        if (empty($idSed) && !empty($nombreSede)) {
            $sqlSed = "SELECT idSede, idUniversidad FROM sedes WHERE nombre = :nombre "
                    . (!empty($idUni) ? "AND idUniversidad = :idUni " : "")
                    . "LIMIT 1";
            $stmt = $this->db->prepare($sqlSed);
            $params = [':nombre' => trim($nombreSede)];
            if (!empty($idUni)) {
                $params[':idUni'] = $idUni;
            }
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $idSed = (int)$row['idSede'];
                if (empty($idUni)) {
                    $idUni = (int)$row['idUniversidad'];
                }
            }
        }

        $idCar = $idCarrera ?: null;
        if (empty($idCar) && !empty($nombreCarrera)) {
            $stmt = $this->db->prepare("SELECT idCarrera FROM carreras WHERE nombre = :nombre LIMIT 1");
            $stmt->execute([':nombre' => trim($nombreCarrera)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $idCar = (int)$row['idCarrera'];
            }
        }

        return [
            'idUniversidad' => $idUni,
            'idSede'        => $idSed,
            'idCarrera'     => $idCar,
        ];
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
