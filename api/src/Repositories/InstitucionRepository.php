<?php
require_once __DIR__ . '/AuditoriaRepository.php';

class InstitucionRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    private function auditar(?int $idActor, string $accion, string $detalle, string $tabla, ?array $orig = null, ?array $nuevo = null): void {
        (new AuditoriaRepository())->registrar(
            $idActor,
            $accion,
            $detalle,
            $tabla,
            null,
            $orig ? json_encode($orig, JSON_UNESCAPED_UNICODE) : null,
            $nuevo ? json_encode($nuevo, JSON_UNESCAPED_UNICODE) : null
        );
    }

    private function obtenerRegistroPorPk(string $tabla, string $pkCol, int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM `{$tabla}` WHERE `{$pkCol}` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function obtenerUniversidades() {
        $sql = "SELECT idUniversidad, nombre, email, celular, estado FROM universidades ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearUniversidad(string $nombre, ?string $email, ?string $celular, string $estado = 'activo', ?int $idActor = null): int {
        $nombreLimpio = trim($nombre);
        $emailLimpio  = trim((string)$email);
        if ($emailLimpio === '') {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nombreLimpio)) ?: 'uni';
            $emailLimpio = "{$slug}_" . time() . "@utell.edu.ar";
        }
        $hashDefault = password_hash('UtellUni2026!', PASSWORD_BCRYPT);
        $sql = "INSERT INTO universidades (nombre, email, password, celular, estado)
                VALUES (:nombre, :email, :pass, :celular, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'  => $nombreLimpio,
            ':email'   => $emailLimpio,
            ':pass'    => $hashDefault,
            ':celular' => $celular ?: null,
            ':estado'  => $estado ?: 'activo',
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->auditar($idActor, 'ALTA_UNIVERSIDAD', "Universidad '{$nombreLimpio}' (#{$id}) creada", 'universidades', null, [
            'idUniversidad' => $id, 'nombre' => $nombreLimpio, 'email' => $emailLimpio, 'estado' => $estado
        ]);
        return $id;
    }

    public function actualizarUniversidad(int $id, string $nombre, ?string $email, ?string $celular, string $estado = 'activo', ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('universidades', 'idUniversidad', $id);
        if (!$old) {
            return false;
        }
        $emailFinal = trim((string)$email) !== '' ? trim((string)$email) : $old['email'];
        $sql = "UPDATE universidades
                SET nombre = :nombre, email = :email, celular = :celular, estado = :estado
                WHERE idUniversidad = :id";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':nombre'  => trim($nombre),
            ':email'   => $emailFinal,
            ':celular' => $celular ?: null,
            ':estado'  => $estado ?: 'activo',
            ':id'      => $id,
        ]);
        if ($ok) {
            unset($old['password']);
            $this->auditar($idActor, 'MODIFICAR_UNIVERSIDAD', "Universidad #{$id} ('{$nombre}') actualizada", 'universidades', $old, [
                'idUniversidad' => $id, 'nombre' => trim($nombre), 'email' => $emailFinal, 'celular' => $celular, 'estado' => $estado
            ]);
        }
        return $ok;
    }

    public function eliminarUniversidad(int $id, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('universidades', 'idUniversidad', $id);
        if (!$old) {
            return false;
        }
        unset($old['password']);
        try {
            $stmt = $this->db->prepare("DELETE FROM universidades WHERE idUniversidad = :id");
            $ok = $stmt->execute([':id' => $id]) && $stmt->rowCount() > 0;
            $modo = 'fisica';
        } catch (Exception $e) {
            $stmt = $this->db->prepare("UPDATE universidades SET estado = 'inactivo' WHERE idUniversidad = :id");
            $ok = $stmt->execute([':id' => $id]);
            $modo = 'logica';
        }
        if ($ok) {
            $this->auditar($idActor, 'BAJA_UNIVERSIDAD', "Universidad #{$id} ('{$old['nombre']}') dada de baja ({$modo})", 'universidades', $old, ['eliminado' => true, 'modo' => $modo]);
        }
        return $ok;
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

    public function crearCarrera(string $nombre, ?int $idActor = null): int {
        $nombreLimpio = trim($nombre);
        $stmt = $this->db->prepare("INSERT INTO carreras (nombre) VALUES (:nombre)");
        $stmt->execute([':nombre' => $nombreLimpio]);
        $id = (int)$this->db->lastInsertId();
        $this->auditar($idActor, 'ALTA_CARRERA', "Carrera '{$nombreLimpio}' (#{$id}) creada", 'carreras', null, ['idCarrera' => $id, 'nombre' => $nombreLimpio]);
        return $id;
    }

    public function actualizarCarrera(int $idCarrera, string $nombre, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('carreras', 'idCarrera', $idCarrera);
        if (!$old) return false;
        $nombreLimpio = trim($nombre);
        $stmt = $this->db->prepare("UPDATE carreras SET nombre = :nombre WHERE idCarrera = :id");
        $ok = $stmt->execute([':nombre' => $nombreLimpio, ':id' => $idCarrera]);
        if ($ok) {
            $this->auditar($idActor, 'MODIFICAR_CARRERA', "Carrera #{$idCarrera} ('{$nombreLimpio}') actualizada", 'carreras', $old, ['idCarrera' => $idCarrera, 'nombre' => $nombreLimpio]);
        }
        return $ok;
    }

    public function eliminarCarrera(int $idCarrera, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('carreras', 'idCarrera', $idCarrera);
        if (!$old) return false;
        $stmt = $this->db->prepare("DELETE FROM carreras WHERE idCarrera = :id");
        $ok = $stmt->execute([':id' => $idCarrera]) && $stmt->rowCount() > 0;
        if ($ok) {
            $this->auditar($idActor, 'BAJA_CARRERA', "Carrera #{$idCarrera} ('{$old['nombre']}') eliminada", 'carreras', $old, ['eliminado' => true]);
        }
        return $ok;
    }

    private bool $esquemaCatalogoVerificado = false;
    private ?bool $ofertaTieneId = null;

    private function ofertaTieneColumnaId(): bool {
        if ($this->ofertaTieneId !== null) {
            return $this->ofertaTieneId;
        }
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM oferta_academica LIKE 'idOfertaAcademica'")->fetchAll(PDO::FETCH_ASSOC);
            $this->ofertaTieneId = !empty($cols);
        } catch (Exception $e) {
            $this->ofertaTieneId = false;
        }
        return $this->ofertaTieneId;
    }

    private function asegurarEsquemaOfertaYSedes(): void {
        if ($this->esquemaCatalogoVerificado) {
            return;
        }
        $this->esquemaCatalogoVerificado = true;
        if (!$this->ofertaTieneColumnaId()) {
            try {
                $this->db->exec("ALTER TABLE oferta_academica ADD UNIQUE KEY uq_oa_carrera_sede (idCarrera, idSede)");
            } catch (Exception $e) {}
            try {
                $this->db->exec("ALTER TABLE oferta_academica DROP PRIMARY KEY, ADD COLUMN idOfertaAcademica INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
                $this->ofertaTieneId = true;
            } catch (Exception $e) {
                error_log("asegurarEsquemaOfertaYSedes oferta_academica: " . $e->getMessage());
            }
        }
        try {
            $this->db->exec("ALTER TABLE sedes MODIFY COLUMN password VARCHAR(255) DEFAULT NULL");
        } catch (Exception $e) {}
    }

    public function obtenerSedes() {
        $sql = "SELECT s.*, u.nombre AS nombreUniversidad, ci.nombre AS nombreCiudad
                FROM sedes s
                LEFT JOIN universidades u ON s.idUniversidad = u.idUniversidad
                LEFT JOIN ciudades ci ON s.idCiudad = ci.idCiudad
                ORDER BY s.nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearSede(
        int $idUniversidad,
        string $nombre,
        ?int $idCiudad = null,
        ?string $telefono = null,
        ?string $email = null,
        ?string $web = null,
        string $estado = 'activo',
        ?int $idActor = null
    ): int {
        $this->asegurarEsquemaOfertaYSedes();
        $nombreLimpio = trim($nombre);
        $params = [
            ':idUni'  => $idUniversidad,
            ':idCiu'  => ($idCiudad && $idCiudad > 0) ? $idCiudad : null,
            ':nombre' => $nombreLimpio,
            ':tel'    => $telefono ?: null,
            ':email'  => $email ?: null,
            ':web'    => $web ?: null,
            ':estado' => $estado ?: 'activo',
        ];
        try {
            $sql = "INSERT INTO sedes (idUniversidad, idCiudad, nombre, telefono, email, web, estado)
                    VALUES (:idUni, :idCiu, :nombre, :tel, :email, :web, :estado)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } catch (Exception $e) {
            $sql = "INSERT INTO sedes (idUniversidad, idCiudad, nombre, password, telefono, email, web, estado)
                    VALUES (:idUni, :idCiu, :nombre, :pass, :tel, :email, :web, :estado)";
            $params[':pass'] = password_hash('UtellSede2026!', PASSWORD_BCRYPT);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
        $id = (int)$this->db->lastInsertId();
        $this->auditar($idActor, 'ALTA_SEDE', "Sede '{$nombreLimpio}' (#{$id}) creada en Universidad #{$idUniversidad}", 'sedes', null, [
            'idSede' => $id, 'idUniversidad' => $idUniversidad, 'nombre' => $nombreLimpio
        ]);
        return $id;
    }

    public function actualizarSede(
        int $idSede,
        int $idUniversidad,
        string $nombre,
        ?int $idCiudad = null,
        ?string $telefono = null,
        ?string $email = null,
        ?string $web = null,
        string $estado = 'activo',
        ?int $idActor = null
    ): bool {
        $old = $this->obtenerRegistroPorPk('sedes', 'idSede', $idSede);
        if (!$old) return false;
        $nombreLimpio = trim($nombre);
        $sql = "UPDATE sedes
                SET idUniversidad = :idUni, idCiudad = :idCiu, nombre = :nombre,
                    telefono = :tel, email = :email, web = :web, estado = :estado
                WHERE idSede = :id";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':idUni'  => $idUniversidad,
            ':idCiu'  => ($idCiudad && $idCiudad > 0) ? $idCiudad : null,
            ':nombre' => $nombreLimpio,
            ':tel'    => $telefono ?: null,
            ':email'  => $email ?: null,
            ':web'    => $web ?: null,
            ':estado' => $estado ?: 'activo',
            ':id'     => $idSede,
        ]);
        if ($ok) {
            $this->auditar($idActor, 'MODIFICAR_SEDE', "Sede #{$idSede} ('{$nombreLimpio}') actualizada", 'sedes', $old, [
                'idSede' => $idSede, 'idUniversidad' => $idUniversidad, 'nombre' => $nombreLimpio, 'estado' => $estado
            ]);
        }
        return $ok;
    }

    public function eliminarSede(int $idSede, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('sedes', 'idSede', $idSede);
        if (!$old) return false;
        try {
            $stmt = $this->db->prepare("DELETE FROM sedes WHERE idSede = :id");
            $ok = $stmt->execute([':id' => $idSede]) && $stmt->rowCount() > 0;
            $modo = 'fisica';
        } catch (Exception $e) {
            $stmt = $this->db->prepare("UPDATE sedes SET estado = 'inactivo' WHERE idSede = :id");
            $ok = $stmt->execute([':id' => $idSede]);
            $modo = 'logica';
        }
        if ($ok) {
            $this->auditar($idActor, 'BAJA_SEDE', "Sede #{$idSede} ('{$old['nombre']}') dada de baja ({$modo})", 'sedes', $old, ['eliminado' => true, 'modo' => $modo]);
        }
        return $ok;
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

    public function obtenerOfertaAcademica(): array {
        $this->asegurarEsquemaOfertaYSedes();
        $selectId = $this->ofertaTieneColumnaId()
            ? 'oa.idOfertaAcademica'
            : '(oa.idCarrera * 10000 + oa.idSede) AS idOfertaAcademica';

        $sql = "SELECT {$selectId}, oa.idCarrera, oa.idSede, oa.fechaImplementacion,
                       YEAR(oa.fechaImplementacion) AS anioImplementacion,
                       oa.descripcion, oa.web, oa.telefono,
                       c.nombre AS nombreCarrera,
                       s.nombre AS nombreSede,
                       s.idUniversidad,
                       u.nombre AS nombreUniversidad
                FROM oferta_academica oa
                INNER JOIN carreras c ON oa.idCarrera = c.idCarrera
                INNER JOIN sedes s ON oa.idSede = s.idSede
                LEFT JOIN universidades u ON s.idUniversidad = u.idUniversidad
                ORDER BY u.nombre ASC, s.nombre ASC, c.nombre ASC";
        try {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $sqlFallback = "SELECT (oa.idCarrera * 10000 + oa.idSede) AS idOfertaAcademica,
                                   oa.idCarrera, oa.idSede, oa.fechaImplementacion,
                                   YEAR(oa.fechaImplementacion) AS anioImplementacion,
                                   oa.descripcion, oa.web, oa.telefono,
                                   c.nombre AS nombreCarrera,
                                   s.nombre AS nombreSede,
                                   s.idUniversidad,
                                   u.nombre AS nombreUniversidad
                            FROM oferta_academica oa
                            INNER JOIN carreras c ON oa.idCarrera = c.idCarrera
                            INNER JOIN sedes s ON oa.idSede = s.idSede
                            LEFT JOIN universidades u ON s.idUniversidad = u.idUniversidad
                            ORDER BY u.nombre ASC, s.nombre ASC, c.nombre ASC";
            return $this->db->query($sqlFallback)->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function crearOfertaAcademica(
        int $idCarrera,
        int $idSede,
        ?int $anio,
        ?string $descripcion = null,
        ?string $web = null,
        ?string $telefono = null,
        ?int $idActor = null
    ): bool {
        $this->asegurarEsquemaOfertaYSedes();
        $fecha = ($anio && $anio > 1800) ? sprintf('%04d-03-01', $anio) : null;
        $sql  = "INSERT INTO oferta_academica (idCarrera, idSede, fechaImplementacion, descripcion, web, telefono)
                 VALUES (:car, :sede, :fecha, :desc, :web, :tel)
                 ON DUPLICATE KEY UPDATE
                   fechaImplementacion = COALESCE(VALUES(fechaImplementacion), fechaImplementacion),
                   descripcion = COALESCE(VALUES(descripcion), descripcion),
                   web = COALESCE(VALUES(web), web),
                   telefono = COALESCE(VALUES(telefono), telefono)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':car'   => $idCarrera,
            ':sede'  => $idSede,
            ':fecha' => $fecha,
            ':desc'  => $descripcion ?: null,
            ':web'   => $web ?: null,
            ':tel'   => $telefono ?: null,
        ]);
        if ($ok) {
            $this->auditar($idActor, 'ALTA_OFERTA_ACADEMICA', "Oferta académica Carrera #{$idCarrera} en Sede #{$idSede} registrada", 'oferta_academica', null, [
                'idCarrera' => $idCarrera, 'idSede' => $idSede, 'anioImplementacion' => $anio
            ]);
        }
        return $ok;
    }

    private function obtenerOfertaPorIdentificador(int $idOferta): array {
        $this->asegurarEsquemaOfertaYSedes();
        if ($this->ofertaTieneColumnaId()) {
            $old = $this->obtenerRegistroPorPk('oferta_academica', 'idOfertaAcademica', $idOferta);
            return [
                'row'   => $old,
                'where' => 'idOfertaAcademica = :id',
                'bind'  => [':id' => $idOferta],
            ];
        }
        $carOrig = intdiv($idOferta, 10000);
        $sedeOrig = $idOferta % 10000;
        $stmt = $this->db->prepare("SELECT * FROM oferta_academica WHERE idCarrera = :c AND idSede = :s LIMIT 1");
        $stmt->execute([':c' => $carOrig, ':s' => $sedeOrig]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        return [
            'row'   => $old,
            'where' => 'idCarrera = :cOrig AND idSede = :sOrig',
            'bind'  => [':cOrig' => $carOrig, ':sOrig' => $sedeOrig],
        ];
    }

    public function actualizarOfertaAcademica(
        int $idOferta,
        int $idCarrera,
        int $idSede,
        ?int $anio,
        ?string $descripcion = null,
        ?string $web = null,
        ?string $telefono = null,
        ?int $idActor = null
    ): bool {
        $target = $this->obtenerOfertaPorIdentificador($idOferta);
        if (!$target['row']) return false;
        $fecha = ($anio && $anio > 1800) ? sprintf('%04d-03-01', $anio) : null;
        $sql = "UPDATE oferta_academica
                SET idCarrera = :car, idSede = :sede, fechaImplementacion = :fecha,
                    descripcion = :desc, web = :web, telefono = :tel
                WHERE {$target['where']}";
        $stmt = $this->db->prepare($sql);
        $params = array_merge([
            ':car'   => $idCarrera,
            ':sede'  => $idSede,
            ':fecha' => $fecha,
            ':desc'  => $descripcion ?: null,
            ':web'   => $web ?: null,
            ':tel'   => $telefono ?: null,
        ], $target['bind']);
        $ok = $stmt->execute($params);
        if ($ok) {
            $this->auditar($idActor, 'MODIFICAR_OFERTA_ACADEMICA', "Oferta académica #{$idOferta} actualizada", 'oferta_academica', $target['row'], [
                'idOfertaAcademica' => $idOferta, 'idCarrera' => $idCarrera, 'idSede' => $idSede, 'fechaImplementacion' => $fecha
            ]);
        }
        return $ok;
    }

    public function eliminarOfertaAcademica(int $idOferta, ?int $idActor = null): bool {
        $target = $this->obtenerOfertaPorIdentificador($idOferta);
        if (!$target['row']) return false;
        $stmt = $this->db->prepare("DELETE FROM oferta_academica WHERE {$target['where']}");
        $stmt->execute($target['bind']);
        $ok = $stmt->rowCount() > 0;
        if ($ok) {
            $this->auditar($idActor, 'BAJA_OFERTA_ACADEMICA', "Oferta académica #{$idOferta} eliminada", 'oferta_academica', $target['row'], ['eliminado' => true]);
        }
        return $ok;
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

    public function crearPais(string $nombre, ?int $idActor = null): int {
        $nombreLimpio = trim($nombre);
        $stmt = $this->db->prepare("INSERT INTO paises (nombre) VALUES (:nombre)");
        $stmt->execute([':nombre' => $nombreLimpio]);
        $id = (int)$this->db->lastInsertId();
        $this->auditar($idActor, 'ALTA_PAIS', "País '{$nombreLimpio}' (#{$id}) creado", 'paises', null, ['idPais' => $id, 'nombre' => $nombreLimpio]);
        return $id;
    }

    public function actualizarPais(int $idPais, string $nombre, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('paises', 'idPais', $idPais);
        if (!$old) return false;
        $nombreLimpio = trim($nombre);
        $stmt = $this->db->prepare("UPDATE paises SET nombre = :nombre WHERE idPais = :id");
        $ok = $stmt->execute([':nombre' => $nombreLimpio, ':id' => $idPais]);
        if ($ok) {
            $this->auditar($idActor, 'MODIFICAR_PAIS', "País #{$idPais} ('{$nombreLimpio}') actualizado", 'paises', $old, ['idPais' => $idPais, 'nombre' => $nombreLimpio]);
        }
        return $ok;
    }

    public function eliminarPais(int $idPais, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('paises', 'idPais', $idPais);
        if (!$old) return false;
        $stmt = $this->db->prepare("DELETE FROM paises WHERE idPais = :id");
        $ok = $stmt->execute([':id' => $idPais]) && $stmt->rowCount() > 0;
        if ($ok) {
            $this->auditar($idActor, 'BAJA_PAIS', "País #{$idPais} ('{$old['nombre']}') eliminado", 'paises', $old, ['eliminado' => true]);
        }
        return $ok;
    }

    public function obtenerProvincias() {
        $sql = "SELECT pr.*, p.nombre AS nombrePais
                FROM provincias pr
                LEFT JOIN paises p ON pr.idPais = p.idPais
                ORDER BY pr.nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearProvincia(string $nombre, int $idPais, ?int $idActor = null): int {
        $nombreLimpio = trim($nombre);
        $stmt = $this->db->prepare("INSERT INTO provincias (nombre, idPais) VALUES (:nombre, :idPais)");
        $stmt->execute([':nombre' => $nombreLimpio, ':idPais' => $idPais]);
        $id = (int)$this->db->lastInsertId();
        $this->auditar($idActor, 'ALTA_PROVINCIA', "Provincia '{$nombreLimpio}' (#{$id}) creada", 'provincias', null, ['idProvincia' => $id, 'nombre' => $nombreLimpio, 'idPais' => $idPais]);
        return $id;
    }

    public function actualizarProvincia(int $idProvincia, string $nombre, int $idPais, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('provincias', 'idProvincia', $idProvincia);
        if (!$old) return false;
        $nombreLimpio = trim($nombre);
        $stmt = $this->db->prepare("UPDATE provincias SET nombre = :nombre, idPais = :idPais WHERE idProvincia = :id");
        $ok = $stmt->execute([':nombre' => $nombreLimpio, ':idPais' => $idPais, ':id' => $idProvincia]);
        if ($ok) {
            $this->auditar($idActor, 'MODIFICAR_PROVINCIA', "Provincia #{$idProvincia} ('{$nombreLimpio}') actualizada", 'provincias', $old, ['idProvincia' => $idProvincia, 'nombre' => $nombreLimpio, 'idPais' => $idPais]);
        }
        return $ok;
    }

    public function eliminarProvincia(int $idProvincia, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('provincias', 'idProvincia', $idProvincia);
        if (!$old) return false;
        $stmt = $this->db->prepare("DELETE FROM provincias WHERE idProvincia = :id");
        $ok = $stmt->execute([':id' => $idProvincia]) && $stmt->rowCount() > 0;
        if ($ok) {
            $this->auditar($idActor, 'BAJA_PROVINCIA', "Provincia #{$idProvincia} ('{$old['nombre']}') eliminada", 'provincias', $old, ['eliminado' => true]);
        }
        return $ok;
    }

    public function obtenerCiudades() {
        $sql = "SELECT ci.*, pr.nombre AS nombreProvincia, pr.idPais, p.nombre AS nombrePais
                FROM ciudades ci
                LEFT JOIN provincias pr ON ci.idProvincia = pr.idProvincia
                LEFT JOIN paises p ON pr.idPais = p.idPais
                ORDER BY ci.nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCiudad(string $nombre, int $idProvincia, ?int $idActor = null): int {
        $nombreLimpio = trim($nombre);
        $stmt = $this->db->prepare("INSERT INTO ciudades (nombre, idProvincia) VALUES (:nombre, :idProv)");
        $stmt->execute([':nombre' => $nombreLimpio, ':idProv' => $idProvincia]);
        $id = (int)$this->db->lastInsertId();
        $this->auditar($idActor, 'ALTA_CIUDAD', "Ciudad '{$nombreLimpio}' (#{$id}) creada", 'ciudades', null, ['idCiudad' => $id, 'nombre' => $nombreLimpio, 'idProvincia' => $idProvincia]);
        return $id;
    }

    public function actualizarCiudad(int $idCiudad, string $nombre, int $idProvincia, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('ciudades', 'idCiudad', $idCiudad);
        if (!$old) return false;
        $nombreLimpio = trim($nombre);
        $stmt = $this->db->prepare("UPDATE ciudades SET nombre = :nombre, idProvincia = :idProv WHERE idCiudad = :id");
        $ok = $stmt->execute([':nombre' => $nombreLimpio, ':idProv' => $idProvincia, ':id' => $idCiudad]);
        if ($ok) {
            $this->auditar($idActor, 'MODIFICAR_CIUDAD', "Ciudad #{$idCiudad} ('{$nombreLimpio}') actualizada", 'ciudades', $old, ['idCiudad' => $idCiudad, 'nombre' => $nombreLimpio, 'idProvincia' => $idProvincia]);
        }
        return $ok;
    }

    public function eliminarCiudad(int $idCiudad, ?int $idActor = null): bool {
        $old = $this->obtenerRegistroPorPk('ciudades', 'idCiudad', $idCiudad);
        if (!$old) return false;
        $stmt = $this->db->prepare("DELETE FROM ciudades WHERE idCiudad = :id");
        $ok = $stmt->execute([':id' => $idCiudad]) && $stmt->rowCount() > 0;
        if ($ok) {
            $this->auditar($idActor, 'BAJA_CIUDAD', "Ciudad #{$idCiudad} ('{$old['nombre']}') eliminada", 'ciudades', $old, ['eliminado' => true]);
        }
        return $ok;
    }
}
