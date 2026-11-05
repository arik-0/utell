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
     * Obtiene la lista consolidada de códigos de permiso para un usuario.
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

        $stmtGrupos = $this->db->prepare("
            SELECT DISTINCT p.codigoAccion 
            FROM detalle_grupo_permiso dgp
            INNER JOIN grupos_permiso gp ON dgp.idGrupoPermiso = gp.idGrupoPermiso
            INNER JOIN permisos p ON (
                (gp.idGrupoPermiso = 1) OR
                (gp.idGrupoPermiso = 2 AND p.codigoAccion IN ('MODERATE_CONTENT', 'MODERATE_COMMENT', 'POST_REPLY', 'POST_CREATE')) OR
                (gp.idGrupoPermiso = 3 AND p.codigoAccion IN ('POST_CREATE', 'POST_REPLY', 'RATE_UNIVERSITY', 'SEND_DIRECT_MESSAGE')) OR
                (gp.idGrupoPermiso = 4 AND p.codigoAccion IN ('POST_CREATE', 'POST_REPLY', 'RATE_UNIVERSITY', 'SEND_DIRECT_MESSAGE', 'VIEW_INSTITUTION_METRICS'))
            )
            WHERE dgp.idUsuario = :id
        ");
        $stmtGrupos->execute([':id' => $idUsuario]);
        $rows = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $permisos[] = $r['codigoAccion'];
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

    public function obtenerGruposPermiso(): array {
        return $this->db->query("SELECT * FROM grupos_permiso ORDER BY idGrupoPermiso ASC")->fetchAll(PDO::FETCH_ASSOC);
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
                'detalle_grupo_permiso'
            );
        }
        return $ok;
    }

    /**
     * Modera una publicación mediante baja lógica, cierre o reactivación (CU6 / Sección 10).
     */
    public function moderarPublicacion(int $idModerador, int $idPublicacion, string $accion, ?string $motivo = null): array {
        if (!$this->tienePermiso($idModerador, 'MODERATE_CONTENT')) {
            throw new DomainException("Acceso denegado: Se requieren permisos de moderador o administrador.");
        }

        $auditoria = new AuditoriaRepository();

        if ($accion === 'eliminar') {
            $stmtDel = $this->db->prepare("UPDATE publicaciones SET estado = 'eliminado' WHERE idPublicacion = :id");
            $stmtDel->execute([':id' => $idPublicacion]);

            $auditoria->registrar(
                $idModerador,
                'DELETE_PUBLICACION',
                "Moderada por ID {$idModerador}. Motivo: " . ($motivo ?: 'Contenido inapropiado') . ". Post: {$idPublicacion}",
                'publicaciones'
            );

            return ['success' => true, 'mensaje' => 'Publicación dada de baja por el equipo de moderación.'];
        } elseif ($accion === 'cerrar') {
            $stmtUpd = $this->db->prepare("UPDATE publicaciones SET resuelta = 1 WHERE idPublicacion = :id");
            $stmtUpd->execute([':id' => $idPublicacion]);

            $auditoria->registrar(
                $idModerador,
                'CERRAR_PUBLICACION',
                "Cerrada/resuelta por ID {$idModerador}. Post: {$idPublicacion}",
                'publicaciones'
            );

            return ['success' => true, 'mensaje' => 'Publicación cerrada/resuelta con éxito.'];
        } elseif ($accion === 'reactivar') {
            $stmtReac = $this->db->prepare("UPDATE publicaciones SET estado = 'activo', resuelta = 0 WHERE idPublicacion = :id");
            $stmtReac->execute([':id' => $idPublicacion]);

            $auditoria->registrar(
                $idModerador,
                'REACTIVAR_PUBLICACION',
                "Reactivada por ID {$idModerador}. Post: {$idPublicacion}",
                'publicaciones'
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
     * Asignación de rol a un usuario por parte de un administrador (CU5 / CU8).
     */
    public function cambiarRolUsuario(int $idUsuario, int $idRol, ?int $idActor = null): bool {
        $stmt = $this->db->prepare("UPDATE usuarios SET idRol = :rol WHERE idUsuario = :id");
        $ok = $stmt->execute([':rol' => $idRol, ':id' => $idUsuario]);
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'CAMBIAR_ROL',
                "Rol de usuario ID {$idUsuario} modificado a ID {$idRol}",
                'usuarios'
            );
        }
        return $ok;
    }

    /**
     * Consulta del historial de auditoría transversal (CU9 / RNF07).
     */
    public function obtenerLogsAuditoria(): array {
        $sql = "SELECT a.idLog, a.idUsuario, a.accion, a.detalles, a.tablaAfectada, a.fechaHora,
                       u.nombre, u.apellido, u.email, u.tipoPerfil
                FROM log_auditoria a
                LEFT JOIN usuarios u ON a.idUsuario = u.idUsuario
                ORDER BY a.idLog DESC
                LIMIT 150";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
