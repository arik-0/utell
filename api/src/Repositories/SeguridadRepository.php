<?php
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/GrupoPermiso.php';
require_once __DIR__ . '/../Models/Permiso.php';

class SeguridadRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    /**
     * Obtiene la lista consolidada de códigos de permiso para un usuario.
     */
    public function obtenerPermisosUsuario(int $idUsuario): array {
        // 1. Obtener rol y datos del usuario
        $stmtUser = $this->db->prepare("SELECT idRol, tipoPerfil FROM usuarios WHERE idUsuario = :id LIMIT 1");
        $stmtUser->execute([':id' => $idUsuario]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [];
        }

        $permisos = [];

        // Permisos base de toda cuenta activa
        $permisos[] = 'POST_CREATE';
        $permisos[] = 'POST_REPLY';
        $permisos[] = 'SEND_DIRECT_MESSAGE';

        // Estudiantes y Docentes pueden calificar universidades
        if (in_array($user['tipoPerfil'], ['Estudiante', 'Docente', 'Graduado'])) {
            $permisos[] = 'RATE_UNIVERSITY';
        }

        // Cuentas Institucionales / Universidades / Facultades
        if (in_array($user['tipoPerfil'], ['Institución', 'Universidad', 'Facultad', 'Docente']) || (int)$user['idRol'] === 6) {
            $permisos[] = 'VIEW_INSTITUTION_METRICS';
            $permisos[] = 'OFFICIAL_BADGE';
        }

        // Permisos explícitos asignados vía detalle_grupo_permiso
        $stmtGrupos = $this->db->prepare("
            SELECT DISTINCT p.codigoAccion 
            FROM detalle_grupo_permiso dgp
            INNER JOIN grupos_permiso gp ON dgp.idGrupoPermiso = gp.idGrupoPermiso
            INNER JOIN permisos p ON (
                -- Administradores y moderadores mapeados a permisos correspondientes
                (gp.idGrupoPermiso = 1) OR
                (gp.idGrupoPermiso = 2 AND p.codigoAccion IN ('MODERATE_CONTENT', 'POST_REPLY', 'POST_CREATE')) OR
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

        // Superadministrador / Rol 1 o Moderador / Rol 5
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
        } elseif ((int)$user['idRol'] === 5 || $user['tipoPerfil'] === 'Moderador') {
            $permisos[] = 'MODERATE_CONTENT';
            $permisos[] = 'DELETE_ANY_POST';
        }

        return array_values(array_unique($permisos));
    }

    /**
     * Verifica si un usuario cuenta con un permiso específico.
     */
    public function tienePermiso(int $idUsuario, string $codigoAccion): bool {
        $permisos = $this->obtenerPermisosUsuario($idUsuario);
        return in_array($codigoAccion, $permisos, true) || in_array('ADMIN_FULL', $permisos, true);
    }

    /**
     * Lista todos los roles del sistema.
     */
    public function obtenerRoles(): array {
        return $this->db->query("SELECT * FROM roles ORDER BY idRol ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todos los grupos de permisos.
     */
    public function obtenerGruposPermiso(): array {
        return $this->db->query("SELECT * FROM grupos_permiso ORDER BY idGrupoPermiso ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Asigna un usuario a un grupo de permisos.
     */
    public function asignarGrupoAUsuario(int $idUsuario, int $idGrupoPermiso): bool {
        $stmt = $this->db->prepare("
            INSERT INTO detalle_grupo_permiso (idUsuario, idGrupoPermiso, fechaAsignacion)
            VALUES (:user, :grupo, NOW())
            ON DUPLICATE KEY UPDATE fechaAsignacion = NOW()
        ");
        return $stmt->execute([':user' => $idUsuario, ':grupo' => $idGrupoPermiso]);
    }

    /**
     * Modera una publicación (eliminar o resolver).
     */
    public function moderarPublicacion(int $idModerador, int $idPublicacion, string $accion, ?string $motivo = null): array {
        if (!$this->tienePermiso($idModerador, 'MODERATE_CONTENT')) {
            throw new Exception("Acceso denegado: Se requieren permisos de moderador o administrador.");
        }

        if ($accion === 'eliminar') {
            // Registrar log de auditoría
            $this->db->prepare("INSERT INTO log_auditoria (idUsuario, tablaAfectada, accion, valorOriginal, fechaHora) VALUES (:u, 'publicaciones', 'DELETE', :val, NOW())")
                 ->execute([':u' => $idModerador, ':val' => "Moderada por ID $idModerador. Motivo: " . ($motivo ?: 'Contenido inapropiado') . ". Post: $idPublicacion"]);

            $stmtDel = $this->db->prepare("DELETE FROM publicaciones WHERE idPublicacion = :id");
            $stmtDel->execute([':id' => $idPublicacion]);

            return ['success' => true, 'mensaje' => 'Publicación eliminada por el equipo de moderación.'];
        } elseif ($accion === 'cerrar') {
            $stmtUpd = $this->db->prepare("UPDATE publicaciones SET resuelta = 1 WHERE idPublicacion = :id");
            $stmtUpd->execute([':id' => $idPublicacion]);

            return ['success' => true, 'mensaje' => 'Publicación cerrada/resuelta con éxito.'];
        }

        throw new Exception("Acción de moderación no reconocida.");
    }
}
