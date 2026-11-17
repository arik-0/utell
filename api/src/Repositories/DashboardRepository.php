<?php
// api/src/Repositories/DashboardRepository.php

require_once __DIR__ . '/../config/db.php';

class DashboardRepository {
    private $db;
    private bool $hasScoreColumns = false;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
        $this->verificarEsquema();
    }

    private function verificarEsquema(): void {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM `publicaciones` LIKE 'puntuacionAcademica'");
            $this->hasScoreColumns = (bool)($stmt && $stmt->fetch());
        } catch (Exception $e) {
            $this->hasScoreColumns = false;
        }
    }

    private function getScoreExpressions(): array {
        if ($this->hasScoreColumns) {
            return [
                'acad'  => 'p.puntuacionAcademica',
                'clima' => 'p.puntuacionClima',
                'infra' => 'p.puntuacionInfraestructura',
                'join'  => '',
            ];
        }

        return [
            'acad'  => 'COALESCE(e.puntuacionAcademica, 5)',
            'clima' => 'COALESCE(e.puntuacionClima, 5)',
            'infra' => 'COALESCE(e.puntuacionInfraestructura, 5)',
            'join'  => 'LEFT JOIN experiencias e ON p.idPublicacion = e.idExperiencia',
        ];
    }

    public function buscarUsuarioDashboard(string $email): ?array {
        $stmt = $this->db->prepare("
            SELECT u.idUsuario, u.nombre, u.apellido, u.email, u.password, u.tipoPerfil, u.fotoPerfil,
                   u.idRol, r.nombreRol, c.nombre AS ciudad
            FROM usuarios u
            LEFT JOIN roles r ON u.idRol = r.idRol
            LEFT JOIN ciudades c ON u.idCiudad = c.idCiudad
            WHERE u.email = :email
            LIMIT 1
        ");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function buscarUniversidadDashboard(string $email): ?array {
        $stmtUni = $this->db->prepare("
            SELECT idUniversidad, nombre, email, password, celular, estado
            FROM universidades
            WHERE email = :email AND estado = 'activo'
            LIMIT 1
        ");
        $stmtUni->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtUni->execute();
        $row = $stmtUni->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function obtenerKpisGlobales(): array {
        $scores  = $this->getScoreExpressions();
        $acad    = $scores['acad'];
        $clima   = $scores['clima'];
        $infra   = $scores['infra'];
        $joinExp = $scores['join'];

        $sqlKpis = "
            SELECT 
                COUNT(DISTINCT u.idUniversidad) AS total_universidades,
                COUNT(DISTINCT s.idSede) AS total_sedes,
                COUNT(DISTINCT c.idCarrera) AS total_carreras,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                COALESCE(ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$acad} END), 2), 0) AS promedio_academico_global,
                COALESCE(ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$clima} END), 2), 0) AS promedio_clima_global,
                COALESCE(ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$infra} END), 2), 0) AS promedio_infraestructura_global,
                COALESCE(ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END), 2), 0) AS score_satisfaccion_global
            FROM universidades u
            LEFT JOIN sedes s ON u.idUniversidad = s.idUniversidad AND s.estado = 'activo'
            LEFT JOIN oferta_academica oa ON s.idSede = oa.idSede
            LEFT JOIN carreras c ON oa.idCarrera = c.idCarrera
            LEFT JOIN publicaciones p ON u.idUniversidad = p.idUniversidad
            {$joinExp}
            WHERE u.estado = 'activo'
        ";
        $stmtKpis = $this->db->prepare($sqlKpis);
        $stmtKpis->execute();
        return $stmtKpis->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerRankingUniversidades(): array {
        $scores  = $this->getScoreExpressions();
        $acad    = $scores['acad'];
        $clima   = $scores['clima'];
        $infra   = $scores['infra'];
        $joinExp = $scores['join'];

        $sqlUniversidades = "
            SELECT 
                u.idUniversidad,
                u.nombre AS universidad,
                u.email,
                u.celular,
                COUNT(DISTINCT s.idSede) AS total_sedes,
                COUNT(DISTINCT oa.idCarrera) AS total_carreras,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$acad} END), 2)
                    ELSE NULL 
                END AS promedio_academico,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$clima} END), 2)
                    ELSE NULL 
                END AS promedio_clima,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$infra} END), 2)
                    ELSE NULL 
                END AS promedio_infraestructura,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END), 2)
                    ELSE NULL 
                END AS promedio_general,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) = 0 THEN 'SIN_DATOS'
                    WHEN AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END) >= 4.5 THEN 'VERDE'
                    WHEN AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END) >= 3.5 THEN 'AMARILLO'
                    ELSE 'ROJO'
                END AS estado_semaforo
            FROM universidades u
            LEFT JOIN sedes s ON u.idUniversidad = s.idUniversidad AND s.estado = 'activo'
            LEFT JOIN oferta_academica oa ON s.idSede = oa.idSede
            LEFT JOIN publicaciones p ON u.idUniversidad = p.idUniversidad
            {$joinExp}
            WHERE u.estado = 'activo'
            GROUP BY u.idUniversidad, u.nombre, u.email, u.celular
            ORDER BY total_publicaciones DESC, promedio_general DESC, u.nombre ASC
        ";
        $stmtUni = $this->db->prepare($sqlUniversidades);
        $stmtUni->execute();
        return $stmtUni->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerResumenUniversidad(int $idUniversidad): ?array {
        $scores  = $this->getScoreExpressions();
        $acad    = $scores['acad'];
        $clima   = $scores['clima'];
        $infra   = $scores['infra'];
        $joinExp = $scores['join'];

        $sqlUni = "
            SELECT 
                u.idUniversidad,
                u.nombre AS universidad,
                u.email,
                u.celular,
                COUNT(DISTINCT s.idSede) AS total_sedes,
                COUNT(DISTINCT oa.idCarrera) AS total_carreras,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$acad} END), 2)
                    ELSE NULL 
                END AS promedio_academico,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$clima} END), 2)
                    ELSE NULL 
                END AS promedio_clima,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$infra} END), 2)
                    ELSE NULL 
                END AS promedio_infraestructura,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END), 2)
                    ELSE NULL 
                END AS promedio_general,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) = 0 THEN 'SIN_DATOS'
                    WHEN AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END) >= 4.5 THEN 'VERDE'
                    WHEN AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END) >= 3.5 THEN 'AMARILLO'
                    ELSE 'ROJO'
                END AS estado_semaforo
            FROM universidades u
            LEFT JOIN sedes s ON u.idUniversidad = s.idUniversidad AND s.estado = 'activo'
            LEFT JOIN oferta_academica oa ON s.idSede = oa.idSede
            LEFT JOIN publicaciones p ON u.idUniversidad = p.idUniversidad
            {$joinExp}
            WHERE u.idUniversidad = :idUniversidad
            GROUP BY u.idUniversidad, u.nombre, u.email, u.celular
        ";
        $stmtUni = $this->db->prepare($sqlUni);
        $stmtUni->bindParam(':idUniversidad', $idUniversidad, PDO::PARAM_INT);
        $stmtUni->execute();
        $row = $stmtUni->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function obtenerSedesCarrerasUniversidad(int $idUniversidad): array {
        $scores  = $this->getScoreExpressions();
        $acad    = $scores['acad'];
        $clima   = $scores['clima'];
        $infra   = $scores['infra'];
        $joinExp = $scores['join'];

        $sqlSedesCarreras = "
            SELECT 
                s.idSede,
                s.nombre AS sede,
                ci.nombre AS ciudad,
                pr.nombre AS provincia,
                c.idCarrera,
                c.nombre AS carrera,
                oa.descripcion AS descripcion_carrera,
                oa.web AS web_carrera,
                oa.telefono AS telefono_carrera,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$acad} END), 2)
                    ELSE NULL 
                END AS promedio_academico,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$clima} END), 2)
                    ELSE NULL 
                END AS promedio_clima,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$infra} END), 2)
                    ELSE NULL 
                END AS promedio_infraestructura,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END), 2)
                    ELSE NULL 
                END AS promedio_carrera,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) = 0 THEN 'SIN_DATOS'
                    WHEN AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END) >= 4.5 THEN 'VERDE'
                    WHEN AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END) >= 3.5 THEN 'AMARILLO'
                    ELSE 'ROJO'
                END AS estado_semaforo
            FROM sedes s
            JOIN oferta_academica oa ON s.idSede = oa.idSede
            JOIN carreras c ON oa.idCarrera = c.idCarrera
            LEFT JOIN ciudades ci ON s.idCiudad = ci.idCiudad
            LEFT JOIN provincias pr ON ci.idProvincia = pr.idProvincia
            LEFT JOIN publicaciones p ON (p.idSede = s.idSede AND p.idCarrera = c.idCarrera)
            {$joinExp}
            WHERE s.idUniversidad = :idUniversidad
            GROUP BY s.idSede, s.nombre, ci.nombre, pr.nombre, c.idCarrera, c.nombre, oa.descripcion, oa.web, oa.telefono
            ORDER BY s.nombre ASC, promedio_carrera DESC, c.nombre ASC
        ";
        $stmtSC = $this->db->prepare($sqlSedesCarreras);
        $stmtSC->bindParam(':idUniversidad', $idUniversidad, PDO::PARAM_INT);
        $stmtSC->execute();
        return $stmtSC->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSedesResumenUniversidad(int $idUniversidad): array {
        $scores  = $this->getScoreExpressions();
        $acad    = $scores['acad'];
        $clima   = $scores['clima'];
        $infra   = $scores['infra'];
        $joinExp = $scores['join'];

        $sqlSedes = "
            SELECT 
                s.idSede,
                s.nombre AS sede,
                ci.nombre AS ciudad,
                pr.nombre AS provincia,
                s.email,
                s.telefono,
                s.web,
                COUNT(DISTINCT oa.idCarrera) AS total_carreras,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END), 2)
                    ELSE NULL 
                END AS promedio_sede
            FROM sedes s
            LEFT JOIN ciudades ci ON s.idCiudad = ci.idCiudad
            LEFT JOIN provincias pr ON ci.idProvincia = pr.idProvincia
            LEFT JOIN oferta_academica oa ON s.idSede = oa.idSede
            LEFT JOIN publicaciones p ON s.idSede = p.idSede
            {$joinExp}
            WHERE s.idUniversidad = :idUniversidad AND s.estado = 'activo'
            GROUP BY s.idSede, s.nombre, ci.nombre, pr.nombre, s.email, s.telefono, s.web
            ORDER BY s.nombre ASC
        ";
        $stmtSedes = $this->db->prepare($sqlSedes);
        $stmtSedes->bindParam(':idUniversidad', $idUniversidad, PDO::PARAM_INT);
        $stmtSedes->execute();
        return $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerContextoCarrera(int $idSede, int $idCarrera): ?array {
        $scores  = $this->getScoreExpressions();
        $acad    = $scores['acad'];
        $clima   = $scores['clima'];
        $infra   = $scores['infra'];
        $joinExp = $scores['join'];

        $sqlHeader = "
            SELECT 
                u.idUniversidad,
                u.nombre AS universidad,
                s.idSede,
                s.nombre AS sede,
                ci.nombre AS ciudad,
                pr.nombre AS provincia,
                c.idCarrera,
                c.nombre AS carrera,
                oa.descripcion,
                oa.web,
                oa.telefono,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$acad} END), 2)
                    ELSE NULL 
                END AS promedio_academico,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$clima} END), 2)
                    ELSE NULL 
                END AS promedio_clima,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN {$infra} END), 2)
                    ELSE NULL 
                END AS promedio_infraestructura,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) > 0 
                    THEN ROUND(AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END), 2)
                    ELSE NULL 
                END AS promedio_general,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) = 0 THEN 'SIN_DATOS'
                    WHEN AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END) >= 4.5 THEN 'VERDE'
                    WHEN AVG(CASE WHEN p.tipoPublicacion = 'Experiencia' THEN ({$acad} + {$clima} + {$infra}) / 3.0 END) >= 3.5 THEN 'AMARILLO'
                    ELSE 'ROJO'
                END AS estado_semaforo
            FROM sedes s
            JOIN universidades u ON s.idUniversidad = u.idUniversidad
            JOIN oferta_academica oa ON s.idSede = oa.idSede
            JOIN carreras c ON oa.idCarrera = c.idCarrera
            LEFT JOIN ciudades ci ON s.idCiudad = ci.idCiudad
            LEFT JOIN provincias pr ON ci.idProvincia = pr.idProvincia
            LEFT JOIN publicaciones p ON (p.idSede = s.idSede AND p.idCarrera = c.idCarrera)
            {$joinExp}
            WHERE s.idSede = :idSede AND c.idCarrera = :idCarrera
            GROUP BY u.idUniversidad, u.nombre, s.idSede, s.nombre, ci.nombre, pr.nombre, c.idCarrera, c.nombre, oa.descripcion, oa.web, oa.telefono
        ";
        $stmtHead = $this->db->prepare($sqlHeader);
        $stmtHead->bindParam(':idSede', $idSede, PDO::PARAM_INT);
        $stmtHead->bindParam(':idCarrera', $idCarrera, PDO::PARAM_INT);
        $stmtHead->execute();
        $row = $stmtHead->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function obtenerPublicacionesCarrera(int $idSede, int $idCarrera): array {
        $scores  = $this->getScoreExpressions();
        $acad    = $scores['acad'];
        $clima   = $scores['clima'];
        $infra   = $scores['infra'];
        $joinExp = $scores['join'];

        $extraGroupScore = $this->hasScoreColumns
            ? ", p.puntuacionClima, p.puntuacionInfraestructura, p.puntuacionAcademica"
            : ", e.puntuacionClima, e.puntuacionInfraestructura, e.puntuacionAcademica";

        $sqlPublicaciones = "
            SELECT 
                p.idPublicacion,
                p.tipoPublicacion,
                p.titulo,
                p.texto,
                p.fecha,
                p.hora,
                (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') as likes,
                p.resuelta,
                usr.idUsuario,
                usr.nombre AS autor_nombre,
                usr.apellido AS autor_apellido,
                usr.email AS autor_email,
                usr.tipoPerfil AS autor_perfil,
                usr.fotoPerfil AS autor_foto,
                {$clima} AS puntuacionClima,
                {$infra} AS puntuacionInfraestructura,
                {$acad} AS puntuacionAcademica,
                CASE 
                    WHEN p.tipoPublicacion = 'Experiencia' THEN 
                        ROUND(({$clima} + {$infra} + {$acad}) / 3.0, 2)
                    ELSE NULL
                END AS promedio_experiencia,
                COUNT(DISTINCT r.idRespuesta) AS total_respuestas,
                COUNT(DISTINCT f.idFavorito) AS total_favoritos
            FROM publicaciones p
            JOIN usuarios usr ON p.idUsuario = usr.idUsuario
            LEFT JOIN respuestas r ON p.idPublicacion = r.idPublicacion
            LEFT JOIN favoritos f ON p.idPublicacion = f.idPublicacion AND f.estado = 'activo'
            {$joinExp}
            WHERE p.idSede = :idSede AND p.idCarrera = :idCarrera
            GROUP BY p.idPublicacion, p.tipoPublicacion, p.titulo, p.texto, p.fecha, p.hora, p.resuelta,
                     usr.idUsuario, usr.nombre, usr.apellido, usr.email, usr.tipoPerfil, usr.fotoPerfil{$extraGroupScore}
            ORDER BY p.fecha DESC, p.hora DESC
        ";
        $stmtPubs = $this->db->prepare($sqlPublicaciones);
        $stmtPubs->bindParam(':idSede', $idSede, PDO::PARAM_INT);
        $stmtPubs->bindParam(':idCarrera', $idCarrera, PDO::PARAM_INT);
        $stmtPubs->execute();
        return $stmtPubs->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerRespuestasPorPublicaciones(array $pubIds): array {
        if (empty($pubIds)) {
            return [];
        }

        $inPlaceholders = implode(',', array_fill(0, count($pubIds), '?'));
        $sqlRespuestas = "
            SELECT 
                r.idRespuesta,
                r.idPublicacion,
                r.texto,
                r.fecha,
                r.hora,
                u.idUsuario,
                u.nombre AS autor_nombre,
                u.apellido AS autor_apellido,
                u.tipoPerfil AS autor_perfil,
                u.fotoPerfil AS autor_foto
            FROM respuestas r
            JOIN usuarios u ON r.idUsuario = u.idUsuario
            WHERE r.idPublicacion IN ($inPlaceholders)
            ORDER BY r.fecha ASC, r.hora ASC
        ";
        $stmtResp = $this->db->prepare($sqlRespuestas);
        foreach ($pubIds as $idx => $id) {
            $stmtResp->bindValue($idx + 1, $id, PDO::PARAM_INT);
        }
        $stmtResp->execute();
        return $stmtResp->fetchAll(PDO::FETCH_ASSOC);
    }
}
