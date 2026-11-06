<?php
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/GrupoPermiso.php';
require_once __DIR__ . '/../Models/Permiso.php';
require_once __DIR__ . '/AuditoriaRepository.php';

class SeguridadRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    /**
     * Obtiene la lista consolidada de códigos de permiso para un usuario (RBAC Composite).
     */
    public function obtenerPermisosUsuario(int $idUsuario): array {
        $stmtUser = $this->db->prepare("SELECT idRol, tipoPerfil FROM usuarios WHERE idUsuario = :id LIMIT 1");
        $stmtUser->execute([':id' => $idUsuario]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [];
        }

        $permisos = [
            'POST_CREATE',
            'POST_REPLY',
            'SEND_DIRECT_MESSAGE',
        ];

        if (in_array($user['tipoPerfil'], ['Estudiante', 'Docente', 'Graduado'], true)) {
            $permisos[] = 'RATE_UNIVERSITY';
        }

        if (in_array($user['tipoPerfil'], ['Institución', 'Universidad', 'Facultad', 'Docente'], true) || (int)$user['idRol'] === 6) {
            $permisos[] = 'VIEW_INSTITUTION_METRICS';
            $permisos[] = 'OFFICIAL_BADGE';
        }

        try {
            $stmtGrupos = $this->db->prepare("
                SELECT DISTINCT p.codigoAccion 
                FROM detalle_grupo_permiso dgp
                INNER JOIN grupos_permiso gp ON dgp.idGrupoPermiso = gp.idGrupoPermiso
                LEFT JOIN grupos_permiso_permisos gpp ON gp.idGrupoPermiso = gpp.idGrupoPermiso
                LEFT JOIN permisos p ON (
                    p.idPermiso = gpp.idPermiso OR
                    (gp.idGrupoPermiso = 1) OR
                    (gp.idGrupoPermiso = 2 AND p.codigoAccion IN ('MODERATE_CONTENT', 'MODERATE_COMMENT', 'POST_REPLY', 'POST_CREATE')) OR
                    (gp.idGrupoPermiso = 3 AND p.codigoAccion IN ('POST_CREATE', 'POST_REPLY', 'RATE_UNIVERSITY', 'SEND_DIRECT_MESSAGE')) OR
                    (gp.idGrupoPermiso = 4 AND p.codigoAccion IN ('POST_CREATE', 'POST_REPLY', 'RATE_UNIVERSITY', 'SEND_DIRECT_MESSAGE', 'VIEW_INSTITUTION_METRICS'))
                )
                WHERE dgp.idUsuario = :id AND p.codigoAccion IS NOT NULL
            ");
            $stmtGrupos->execute([':id' => $idUsuario]);
            $rows = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $permisos[] = $r['codigoAccion'];
            }
        } catch (Exception $e) {
            // Tolerancia si grupos_permiso_permisos no existe en algún entorno
        }

        // Superadministrador (Rol 1) o Moderador (Rol 3)
        if ((int)$user['idRol'] === 1 || $user['tipoPerfil'] === 'Administrador') {
            $permisos = [
                'POST_CREATE',
                'POST_REPLY',
                'RATE_UNIVERSITY',
                'MODERATE_CONTENT',
                'MANAGE_USERS',
                'SEND_DIRECT_MESSAGE',
                'VIEW_INSTITUTION_METRICS',
                'OFFICIAL_BADGE',
                'DELETE_ANY_POST',
                'ADMIN_FULL'
            ];
        } elseif ((int)$user['idRol'] === 3 || $user['tipoPerfil'] === 'Moderador') {
            $permisos[] = 'MODERATE_CONTENT';
            $permisos[] = 'DELETE_ANY_POST';
        }

        return array_values(array_unique($permisos));
    }

    public function tienePermiso(int $idUsuario, string $codigoAccion): bool {
        $permisos = $this->obtenerPermisosUsuario($idUsuario);
        return in_array($codigoAccion, $permisos, true) || in_array('ADMIN_FULL', $permisos, true);
    }

    public function obtenerRoles(): array {
        return $this->db->query("SELECT * FROM roles ORDER BY idRol ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPermisosCatalogo(): array {
        try {
            return $this->db->query("SELECT * FROM permisos ORDER BY idPermiso ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene los grupos de permisos con sus permisos asignados y cantidad de usuarios (Puntos T02/T03).
     */
    public function obtenerGruposPermiso(): array {
        $grupos = $this->db->query("SELECT * FROM grupos_permiso ORDER BY idGrupoPermiso ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($grupos as &$g) {
            $idGrupo = (int)$g['idGrupoPermiso'];
            $g['totalUsuarios'] = 0;
            $g['permisos'] = [];

            try {
                $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM detalle_grupo_permiso WHERE idGrupoPermiso = :id");
                $stmtCount->execute([':id' => $idGrupo]);
                $g['totalUsuarios'] = (int)$stmtCount->fetchColumn();
            } catch (Exception $e) {}

            try {
                $stmtPerms = $this->db->prepare("
                    SELECT p.idPermiso, p.nombre, p.codigoAccion
                    FROM grupos_permiso_permisos gpp
                    INNER JOIN permisos p ON gpp.idPermiso = p.idPermiso
                    WHERE gpp.idGrupoPermiso = :id
                    ORDER BY p.idPermiso ASC
                ");
                $stmtPerms->execute([':id' => $idGrupo]);
                $g['permisos'] = $stmtPerms->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {}
        }
        return $grupos;
    }

    /**
     * Crea un nuevo grupo de permisos y le vincula permisos (Puntos T02/T03).
     */
    public function crearGrupoPermiso(string $nombre, string $descripcion, array $idsPermisos = [], ?int $idActor = null): array {
        $stmt = $this->db->prepare("INSERT INTO grupos_permiso (nombre, descripcion) VALUES (:nombre, :desc)");
        $stmt->execute([':nombre' => trim($nombre), ':desc' => trim($descripcion)]);
        $idGrupo = (int)$this->db->lastInsertId();

        $this->sincronizarPermisosDeGrupo($idGrupo, $idsPermisos);

        $valorNuevo = json_encode(['idGrupoPermiso' => $idGrupo, 'nombre' => $nombre, 'permisos' => $idsPermisos], JSON_UNESCAPED_UNICODE);
        (new AuditoriaRepository())->registrar(
            $idActor,
            'CREAR_GRUPO_PERMISO',
            "Grupo de permiso '{$nombre}' (#{$idGrupo}) creado",
            'grupos_permiso',
            null,
            null,
            $valorNuevo
        );

        return ['idGrupoPermiso' => $idGrupo, 'nombre' => $nombre, 'descripcion' => $descripcion];
    }

    /**
     * Modifica un grupo de permisos existente y sus permisos asociados (Puntos T02/T03).
     */
    public function actualizarGrupoPermiso(int $idGrupo, string $nombre, string $descripcion, array $idsPermisos = [], ?int $idActor = null): bool {
        $stmtOld = $this->db->prepare("SELECT * FROM grupos_permiso WHERE idGrupoPermiso = :id LIMIT 1");
        $stmtOld->execute([':id' => $idGrupo]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("UPDATE grupos_permiso SET nombre = :nombre, descripcion = :desc WHERE idGrupoPermiso = :id");
        $ok = $stmt->execute([':nombre' => trim($nombre), ':desc' => trim($descripcion), ':id' => $idGrupo]);

        $this->sincronizarPermisosDeGrupo($idGrupo, $idsPermisos);

        $valorOrig = $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null;
        $valorNuevo = json_encode(['idGrupoPermiso' => $idGrupo, 'nombre' => $nombre, 'descripcion' => $descripcion, 'permisos' => $idsPermisos], JSON_UNESCAPED_UNICODE);
        (new AuditoriaRepository())->registrar(
            $idActor,
            'MODIFICAR_GRUPO_PERMISO',
            "Grupo de permiso #{$idGrupo} ('{$nombre}') actualizado",
            'grupos_permiso',
            null,
            $valorOrig,
            $valorNuevo
        );

        return $ok;
    }

    /**
     * Elimina un grupo de permisos (Puntos T02/T03).
     */
    public function eliminarGrupoPermiso(int $idGrupo, ?int $idActor = null): bool {
        $stmtOld = $this->db->prepare("SELECT * FROM grupos_permiso WHERE idGrupoPermiso = :id LIMIT 1");
        $stmtOld->execute([':id' => $idGrupo]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("DELETE FROM grupos_permiso WHERE idGrupoPermiso = :id");
        $ok = $stmt->execute([':id' => $idGrupo]);

        if ($ok) {
            $valorOrig = $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null;
            (new AuditoriaRepository())->registrar(
                $idActor,
                'ELIMINAR_GRUPO_PERMISO',
                "Grupo de permiso #{$idGrupo} eliminado",
                'grupos_permiso',
                null,
                $valorOrig,
                json_encode(['eliminado' => true])
            );
        }
        return $ok;
    }

    private function sincronizarPermisosDeGrupo(int $idGrupo, array $idsPermisos): void {
        try {
            $stmtDel = $this->db->prepare("DELETE FROM grupos_permiso_permisos WHERE idGrupoPermiso = :id");
            $stmtDel->execute([':id' => $idGrupo]);

            if (!empty($idsPermisos)) {
                $stmtIns = $this->db->prepare("INSERT IGNORE INTO grupos_permiso_permisos (idGrupoPermiso, idPermiso) VALUES (:g, :p)");
                foreach ($idsPermisos as $idP) {
                    $idPermiso = (int)$idP;
                    if ($idPermiso > 0) {
                        $stmtIns->execute([':g' => $idGrupo, ':p' => $idPermiso]);
                    }
                }
            }
        } catch (Exception $e) {
            // Si la tabla grupos_permiso_permisos no existe en algún entorno antiguo, continúa sin romper
        }
    }

    public function asignarGrupoAUsuario(int $idUsuario, int $idGrupoPermiso, ?int $idActor = null): bool {
        $stmt = $this->db->prepare("
            INSERT INTO detalle_grupo_permiso (idUsuario, idGrupoPermiso, fechaAsignacion)
            VALUES (:user, :grupo, NOW())
            ON DUPLICATE KEY UPDATE fechaAsignacion = NOW()
        ");
        $ok = $stmt->execute([':user' => $idUsuario, ':grupo' => $idGrupoPermiso]);
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'ASIGNAR_GRUPO_PERMISO',
                "Usuario ID {$idUsuario} asignado al grupo {$idGrupoPermiso}",
                'detalle_grupo_permiso',
                null,
                null,
                json_encode(['idUsuario' => $idUsuario, 'idGrupoPermiso' => $idGrupoPermiso])
            );
        }
        return $ok;
    }

    /**
     * Modera una publicación mediante baja lógica, cierre o reactivación con trazabilidad (CU6 / Punto T06).
     */
    public function moderarPublicacion(int $idModerador, int $idPublicacion, string $accion, ?string $motivo = null): array {
        if (!$this->tienePermiso($idModerador, 'MODERATE_CONTENT')) {
            throw new DomainException("Acceso denegado: Se requieren permisos de moderador o administrador.");
        }

        // Capturar estado original antes de transformar (Punto T06)
        $stmtPrev = $this->db->prepare("SELECT idPublicacion, titulo, estado, resuelta FROM publicaciones WHERE idPublicacion = :id LIMIT 1");
        $stmtPrev->execute([':id' => $idPublicacion]);
        $prevRow = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        $valorOriginal = $prevRow ? json_encode(['estado' => $prevRow['estado'] ?? 'activo', 'resuelta' => (int)($prevRow['resuelta'] ?? 0)], JSON_UNESCAPED_UNICODE) : null;

        $auditoria = new AuditoriaRepository();

        if ($accion === 'eliminar') {
            $stmtDel = $this->db->prepare("UPDATE publicaciones SET estado = 'eliminado' WHERE idPublicacion = :id");
            $stmtDel->execute([':id' => $idPublicacion]);

            $valorNuevo = json_encode(['estado' => 'eliminado', 'motivo' => $motivo ?: 'Contenido inapropiado'], JSON_UNESCAPED_UNICODE);
            $auditoria->registrar(
                $idModerador,
                'DELETE_PUBLICACION',
                "Moderada por ID {$idModerador}. Motivo: " . ($motivo ?: 'Contenido inapropiado') . ". Post: {$idPublicacion}",
                'publicaciones',
                null,
                $valorOriginal,
                $valorNuevo
            );

            return ['success' => true, 'mensaje' => 'Publicación dada de baja por el equipo de moderación.'];
        } elseif ($accion === 'cerrar') {
            $stmtUpd = $this->db->prepare("UPDATE publicaciones SET resuelta = 1 WHERE idPublicacion = :id");
            $stmtUpd->execute([':id' => $idPublicacion]);

            $valorNuevo = json_encode(['estado' => $prevRow['estado'] ?? 'activo', 'resuelta' => 1], JSON_UNESCAPED_UNICODE);
            $auditoria->registrar(
                $idModerador,
                'CERRAR_PUBLICACION',
                "Cerrada/resuelta por ID {$idModerador}. Post: {$idPublicacion}",
                'publicaciones',
                null,
                $valorOriginal,
                $valorNuevo
            );

            return ['success' => true, 'mensaje' => 'Publicación cerrada/resuelta con éxito.'];
        } elseif ($accion === 'reactivar') {
            $stmtReac = $this->db->prepare("UPDATE publicaciones SET estado = 'activo', resuelta = 0 WHERE idPublicacion = :id");
            $stmtReac->execute([':id' => $idPublicacion]);

            $valorNuevo = json_encode(['estado' => 'activo', 'resuelta' => 0], JSON_UNESCAPED_UNICODE);
            $auditoria->registrar(
                $idModerador,
                'REACTIVAR_PUBLICACION',
                "Reactivada por ID {$idModerador}. Post: {$idPublicacion}",
                'publicaciones',
                null,
                $valorOriginal,
                $valorNuevo
            );

            return ['success' => true, 'mensaje' => 'Publicación reactivada como activa en el muro.'];
        }

        throw new InvalidArgumentException("Acción de moderación no reconocida.");
    }

    /**
     * Listado completo de publicaciones para panel de moderación (CU6).
     */
    public function obtenerPublicacionesModerables(): array {
        $sql = "SELECT p.idPublicacion, p.idUsuario, p.tipoPublicacion, p.titulo, p.fecha, p.hora,
                       p.texto, COALESCE(p.estado, 'activo') as estado, COALESCE(p.resuelta, 0) as resuelta,
                       u.nombre, u.apellido, u.email, u.fotoPerfil, u.tipoPerfil,
                       uni.nombre as nombreUniversidad,
                       c.nombre as nombreCarrera,
                       (SELECT COUNT(*) FROM respuestas r WHERE r.idPublicacion = p.idPublicacion) as cantidadRespuestas,
                       (SELECT COUNT(*) FROM favoritos f WHERE f.idPublicacion = p.idPublicacion AND f.estado = 'activo') as likes
                FROM publicaciones p
                INNER JOIN usuarios u ON p.idUsuario = u.idUsuario
                LEFT JOIN universidades uni ON p.idUniversidad = uni.idUniversidad
                LEFT JOIN carreras c ON p.idCarrera = c.idCarrera
                ORDER BY p.idPublicacion DESC
                LIMIT 150";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Listado de usuarios con roles y estados para el panel de administración (CU5 / CU8).
     */
    public function obtenerUsuariosAdmin(): array {
        $sql = "SELECT u.idUsuario, u.nombre, u.apellido, u.email, u.tipoPerfil, u.idRol,
                       COALESCE(u.estadoCuenta, 'activo') as estadoCuenta,
                       COALESCE(r.nombreRol, u.tipoPerfil) as nombreRol,
                       u.fotoPerfil,
                       uni.nombre as nombreUniversidad,
                       (SELECT COUNT(*) FROM publicaciones p WHERE p.idUsuario = u.idUsuario) as totalPublicaciones
                FROM usuarios u
                LEFT JOIN roles r ON u.idRol = r.idRol
                LEFT JOIN universidades uni ON u.idUniversidad = uni.idUniversidad
                ORDER BY u.idUsuario DESC
                LIMIT 200";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Asignación de rol a un usuario con registro de valorOriginal y valorNuevo (CU5 / Punto T06).
     */
    public function cambiarRolUsuario(int $idUsuario, int $idRol, ?int $idActor = null): bool {
        $stmtPrev = $this->db->prepare("
            SELECT u.idRol, u.tipoPerfil, r.nombreRol
            FROM usuarios u
            LEFT JOIN roles r ON u.idRol = r.idRol
            WHERE u.idUsuario = :id LIMIT 1
        ");
        $stmtPrev->execute([':id' => $idUsuario]);
        $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        $stmtNewRol = $this->db->prepare("SELECT nombreRol FROM roles WHERE idRol = :idRol LIMIT 1");
        $stmtNewRol->execute([':idRol' => $idRol]);
        $nuevoNombreRol = $stmtNewRol->fetchColumn() ?: "Rol #{$idRol}";

        $stmt = $this->db->prepare("UPDATE usuarios SET idRol = :rol WHERE idUsuario = :id");
        $ok = $stmt->execute([':rol' => $idRol, ':id' => $idUsuario]);
        if ($ok) {
            $valorOrig = $prev ? json_encode(['idRol' => (int)$prev['idRol'], 'nombreRol' => $prev['nombreRol'] ?? $prev['tipoPerfil']], JSON_UNESCAPED_UNICODE) : null;
            $valorNuevo = json_encode(['idRol' => $idRol, 'nombreRol' => $nuevoNombreRol], JSON_UNESCAPED_UNICODE);

            (new AuditoriaRepository())->registrar(
                $idActor,
                'CAMBIAR_ROL',
                "Usuario #{$idUsuario}: rol cambiado de '" . ($prev['nombreRol'] ?? $prev['idRol'] ?? 'N/A') . "' a '{$nuevoNombreRol}'",
                'usuarios',
                null,
                $valorOrig,
                $valorNuevo
            );
        }
        return $ok;
    }

    /**
     * Bloquea o reactiva una cuenta de usuario con trazabilidad de estado original y nuevo (CU6 / Punto T02 / T06).
     */
    public function cambiarEstadoUsuario(int $idUsuario, string $accion, ?int $idActor = null): bool {
        $stmtPrev = $this->db->prepare("SELECT idUsuario, email, COALESCE(estadoCuenta, 'activo') AS estadoCuenta FROM usuarios WHERE idUsuario = :id LIMIT 1");
        $stmtPrev->execute([':id' => $idUsuario]);
        $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        $nuevoEstado = ($accion === 'bloquear' || $accion === 'inactivo') ? 'inactivo' : 'activo';
        $stmt = $this->db->prepare("UPDATE usuarios SET estadoCuenta = :estado WHERE idUsuario = :id");
        $ok = $stmt->execute([':estado' => $nuevoEstado, ':id' => $idUsuario]);

        if ($ok) {
            $valorOrig = $prev ? json_encode(['estadoCuenta' => $prev['estadoCuenta']], JSON_UNESCAPED_UNICODE) : null;
            $valorNuevo = json_encode(['estadoCuenta' => $nuevoEstado], JSON_UNESCAPED_UNICODE);

            (new AuditoriaRepository())->registrar(
                $idActor,
                $nuevoEstado === 'inactivo' ? 'BLOQUEAR_USUARIO' : 'REACTIVAR_USUARIO',
                "Estado de cuenta de usuario #{$idUsuario} (" . ($prev['email'] ?? '') . ") cambiado a '{$nuevoEstado}'",
                'usuarios',
                null,
                $valorOrig,
                $valorNuevo
            );
        }
        return $ok;
    }

    /**
     * Reseteo administrativo de clave de un usuario (Puntos T02 y T03: Resetear Clave).
     */
    public function resetearClaveUsuario(int $idUsuario, string $nuevaClave, ?int $idActor = null): bool {
        $stmtPrev = $this->db->prepare("SELECT idUsuario, email FROM usuarios WHERE idUsuario = :id LIMIT 1");
        $stmtPrev->execute([':id' => $idUsuario]);
        $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        if (!$prev) {
            throw new DomainException("El usuario especificado no existe.");
        }

        $hash = password_hash($nuevaClave, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE usuarios SET password = :pass WHERE idUsuario = :id");
        $ok = $stmt->execute([':pass' => $hash, ':id' => $idUsuario]);

        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'RESETEAR_CLAVE',
                "Clave reseteada administrativamente para usuario #{$idUsuario} ({$prev['email']})",
                'usuarios',
                null,
                json_encode(['passwordHash' => '***HASH_ANTERIOR***']),
                json_encode(['passwordHash' => '***NUEVO_HASH_BCRYPT***'])
            );
        }
        return $ok;
    }

    /**
     * Recuperación de clave por parte del usuario validando email y fecha de nacimiento (Puntos T02 y T03: Recuperar Clave).
     */
    public function recuperarClavePorEmail(string $email, string $fNac, string $nuevaClave): bool {
        $stmt = $this->db->prepare("SELECT idUsuario, email, fNac FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => trim($email)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new DomainException("No se encontró ninguna cuenta asociada a ese correo electrónico.");
        }

        if (!empty($fNac) && !empty($user['fNac']) && $user['fNac'] !== '0000-00-00' && $user['fNac'] !== $fNac) {
            throw new DomainException("La fecha de nacimiento ingresada no coincide con nuestros registros.");
        }

        $hash = password_hash($nuevaClave, PASSWORD_BCRYPT);
        $stmtUpd = $this->db->prepare("UPDATE usuarios SET password = :pass WHERE idUsuario = :id");
        $ok = $stmtUpd->execute([':pass' => $hash, ':id' => (int)$user['idUsuario']]);

        if ($ok) {
            (new AuditoriaRepository())->registrar(
                (int)$user['idUsuario'],
                'RECUPERAR_CLAVE',
                "Recuperación de contraseña completada para {$user['email']}",
                'usuarios',
                null,
                json_encode(['passwordHash' => '***HASH_ANTERIOR***']),
                json_encode(['passwordHash' => '***RECUPERADO_BCRYPT***'])
            );
        }
        return $ok;
    }

    /**
     * Consulta del historial de auditoría transversal tolerante a esquemas (Punto T06 / CU9).
     */
    public function obtenerLogsAuditoria(): array {
        $sql = "SELECT a.*, u.nombre, u.apellido, u.email, u.tipoPerfil
                FROM log_auditoria a
                LEFT JOIN usuarios u ON a.idUsuario = u.idUsuario
                ORDER BY a.idLog DESC
                LIMIT 200";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['detalles']      = $r['detalle'] ?? $r['detalles'] ?? '';
            $r['valorOriginal'] = $r['valorOriginal'] ?? null;
            $r['valorNuevo']    = $r['valorNuevo'] ?? null;
            $r['ip']            = $r['ip'] ?? '127.0.0.1';
        }
        return $rows;
    }

    /**
     * Genera un volcado SQL completo (Resguardo / Backup) con firma de integridad SHA-256 (Punto T07 / CU9).
     */
    public function generarBackupSql(?int $idActor = null): array {
        $tablas = [];
        $stmtTables = $this->db->query("SHOW TABLES");
        while ($row = $stmtTables->fetch(PDO::FETCH_NUM)) {
            $tablas[] = $row[0];
        }

        $fecha = date('Y-m-d H:i:s');
        $sqlDump  = "-- ============================================================================\n";
        $sqlDump .= "-- U-TELL — RESGUARDO TRANSACCIONAL DE BASE DE DATOS (PUNTO T07 / CU9)\n";
        $sqlDump .= "-- Fecha de generación: {$fecha}\n";
        $sqlDump .= "-- Generado por Usuario ID: " . ($idActor ?? 0) . "\n";
        $sqlDump .= "-- ============================================================================\n\n";
        $sqlDump .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

        $resumenTablas = [];
        $totalRegistros = 0;

        foreach ($tablas as $tabla) {
            $stmtCreate = $this->db->query("SHOW CREATE TABLE `{$tabla}`");
            $createRow = $stmtCreate->fetch(PDO::FETCH_NUM);
            $createSql = $createRow[1] ?? '';

            $sqlDump .= "-- Estructura de tabla `{$tabla}`\n";
            $sqlDump .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
            $sqlDump .= $createSql . ";\n\n";

            $stmtRows = $this->db->query("SELECT * FROM `{$tabla}`");
            $rows = $stmtRows->fetchAll(PDO::FETCH_ASSOC);
            $count = count($rows);
            $totalRegistros += $count;
            $resumenTablas[] = ['tabla' => $tabla, 'registros' => $count];

            if ($count > 0) {
                $sqlDump .= "-- Datos de tabla `{$tabla}` ({$count} registros)\n";
                foreach ($rows as $r) {
                    $cols = array_map(fn($c) => "`{$c}`", array_keys($r));
                    $vals = array_map(function($v) {
                        if ($v === null) return 'NULL';
                        return $this->db->quote((string)$v);
                    }, array_values($r));
                    $sqlDump .= "INSERT INTO `{$tabla}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
                }
                $sqlDump .= "\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $sqlDump .= "-- Dump completed on {$fecha}\n";

        $sha256 = hash('sha256', $sqlDump);
        $nombreArchivo = 'utell_backup_' . date('Ymd_His') . '.sql';

        (new AuditoriaRepository())->registrar(
            $idActor,
            'BACKUP_SISTEMA',
            "Resguardo SQL completo generado ({$nombreArchivo}). Tablas: " . count($tablas) . ", Registros: {$totalRegistros}. SHA-256: " . substr($sha256, 0, 16) . "...",
            'sistema',
            null,
            null,
            json_encode(['archivo' => $nombreArchivo, 'tablas' => count($tablas), 'registros' => $totalRegistros, 'sha256' => $sha256])
        );

        return [
            'nombreArchivo'  => $nombreArchivo,
            'fechaHora'      => $fecha,
            'totalTablas'    => count($tablas),
            'totalRegistros' => $totalRegistros,
            'resumenTablas'  => $resumenTablas,
            'sha256'         => $sha256,
            'tamanioBytes'   => strlen($sqlDump),
            'contenidoSql'   => $sqlDump
        ];
    }
}
