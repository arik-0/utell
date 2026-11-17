<?php
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/GrupoPermiso.php';
require_once __DIR__ . '/../Models/Permiso.php';
require_once __DIR__ . '/AuditoriaRepository.php';

class SeguridadRepository {
    private $db;
    private bool $rbacVerificado = false;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    private function obtenerRolDuenoId(): int {
        try {
            $id = $this->db->query("SELECT idRol FROM roles WHERE nombreRol = 'Dueño' LIMIT 1")->fetchColumn();
            if ($id) {
                return (int)$id;
            }
        } catch (Exception $e) {}
        return 7;
    }

    private function obtenerResumenUsuarioSeguridad(int $idUsuario): ?array {
        $stmt = $this->db->prepare("
            SELECT u.idUsuario, u.email, u.idRol, u.tipoPerfil, COALESCE(u.estadoCuenta, 'activo') AS estadoCuenta,
                   r.nombreRol
            FROM usuarios u
            LEFT JOIN roles r ON u.idRol = r.idRol
            WHERE u.idUsuario = :id LIMIT 1
        ");
        $stmt->execute([':id' => $idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $idRolDueno = $this->obtenerRolDuenoId();
        $row['esDueno'] = ($row['tipoPerfil'] === 'Dueño' || $row['nombreRol'] === 'Dueño' || (int)$row['idRol'] === $idRolDueno);
        $row['esAdmin'] = ($row['esDueno'] || (int)$row['idRol'] === 1 || $row['tipoPerfil'] === 'Administrador');
        return $row;
    }

    public function esUsuarioDueno(int $idUsuario): bool {
        $this->asegurarTablasRbac();
        $info = $this->obtenerResumenUsuarioSeguridad($idUsuario);
        if ($info && $info['esDueno']) {
            return true;
        }
        try {
            $arbol = $this->construirArbolCompositeUsuario($idUsuario);
            return $arbol->tienePermiso('OWNER_FULL');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Patrón GoF Composite RBAC: construye el árbol en memoria con todos los GrupoPermiso
     * asignados al usuario (soporte de múltiples grupos por usuario y subgrupos anidados).
     */
    public function construirArbolCompositeUsuario(int $idUsuario): GrupoPermiso {
        $this->asegurarTablasRbac();
        $raizUsuario = new GrupoPermiso("Usuario_{$idUsuario}", "Composición RBAC de múltiples grupos");

        try {
            $rowsGrupos = $this->db->query("SELECT * FROM grupos_permiso ORDER BY idGrupoPermiso ASC")->fetchAll(PDO::FETCH_ASSOC);
            $nodosGrupo = [];
            foreach ($rowsGrupos as $g) {
                $idG = (int)$g['idGrupoPermiso'];
                $idPadre = !empty($g['idGrupoPadre']) ? (int)$g['idGrupoPadre'] : null;
                $nodosGrupo[$idG] = new GrupoPermiso((string)$g['nombre'], (string)($g['descripcion'] ?? ''), $idG, $idPadre);
            }

            $rowsRel = $this->db->query("
                SELECT gpp.idGrupoPermiso, p.idPermiso, p.nombre, p.codigoAccion
                FROM grupos_permiso_permisos gpp
                INNER JOIN permisos p ON gpp.idPermiso = p.idPermiso
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rowsRel as $rel) {
                $idG = (int)$rel['idGrupoPermiso'];
                if (isset($nodosGrupo[$idG])) {
                    $nodosGrupo[$idG]->agregarComponente(
                        new Permiso((string)$rel['nombre'], (string)$rel['codigoAccion'], (int)$rel['idPermiso'])
                    );
                }
            }

            // Enlazar jerarquía Composite entre grupos (herencia de subgrupos vía idGrupoPadre)
            foreach ($rowsGrupos as $g) {
                $idG = (int)$g['idGrupoPermiso'];
                $idPadre = !empty($g['idGrupoPadre']) ? (int)$g['idGrupoPadre'] : 0;
                if ($idPadre > 0 && $idPadre !== $idG && isset($nodosGrupo[$idG], $nodosGrupo[$idPadre])) {
                    $nodosGrupo[$idG]->agregarComponente($nodosGrupo[$idPadre]);
                }
            }

            $stmtUserGrupos = $this->db->prepare("SELECT idGrupoPermiso FROM detalle_grupo_permiso WHERE idUsuario = :id");
            $stmtUserGrupos->execute([':id' => $idUsuario]);
            $idsAsignados = $stmtUserGrupos->fetchAll(PDO::FETCH_COLUMN);

            foreach ($idsAsignados as $idG) {
                $idGrupo = (int)$idG;
                if (isset($nodosGrupo[$idGrupo])) {
                    $raizUsuario->agregarComponente($nodosGrupo[$idGrupo]);
                }
            }
        } catch (Exception $e) {
            error_log("construirArbolCompositeUsuario: " . $e->getMessage());
        }

        return $raizUsuario;
    }

    public function obtenerGruposUsuario(int $idUsuario): array {
        $this->asegurarTablasRbac();
        try {
            $stmt = $this->db->prepare("
                SELECT gp.idGrupoPermiso, gp.nombre, gp.descripcion, dgp.fechaAsignacion
                FROM detalle_grupo_permiso dgp
                INNER JOIN grupos_permiso gp ON dgp.idGrupoPermiso = gp.idGrupoPermiso
                WHERE dgp.idUsuario = :id
                ORDER BY gp.idGrupoPermiso ASC
            ");
            $stmt->execute([':id' => $idUsuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene la lista consolidada de códigos de permiso para un usuario (RBAC Composite).
     */
    public function obtenerPermisosUsuario(int $idUsuario): array {
        $this->asegurarTablasRbac();
        $user = $this->obtenerResumenUsuarioSeguridad($idUsuario);
        if (!$user) {
            return [];
        }

        $arbol    = $this->construirArbolCompositeUsuario($idUsuario);
        $permisos = array_merge([
            'POST_CREATE',
            'POST_REPLY',
            'SEND_DIRECT_MESSAGE',
        ], $arbol->obtenerCodigosPermiso());

        if (in_array($user['tipoPerfil'], ['Estudiante', 'Docente', 'Graduado', 'Dueño'], true)) {
            $permisos[] = 'RATE_UNIVERSITY';
        }

        if (in_array($user['tipoPerfil'], ['Institución', 'Universidad', 'Facultad', 'Docente', 'Dueño'], true) || (int)$user['idRol'] === 6) {
            $permisos[] = 'VIEW_INSTITUTION_METRICS';
            $permisos[] = 'OFFICIAL_BADGE';
        }

        if ($user['esDueno'] || $arbol->tienePermiso('OWNER_FULL')) {
            try {
                $todosCodigos = $this->db->query("SELECT codigoAccion FROM permisos")->fetchAll(PDO::FETCH_COLUMN);
                $permisos = array_merge($permisos, $todosCodigos ?: []);
            } catch (Exception $e) {}
            $permisos = array_merge($permisos, [
                'ADMIN_FULL',
                'OWNER_FULL',
                'MANAGE_ADMINS',
                'MANAGE_OWNERS',
                'SYSTEM_BACKUP',
                'RBAC_COMPOSITE_ADMIN',
            ]);
        } elseif ($user['esAdmin'] || $arbol->tienePermiso('ADMIN_FULL')) {
            $permisos = array_merge($permisos, [
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
            ]);
        } elseif ((int)$user['idRol'] === 3 || $user['tipoPerfil'] === 'Moderador') {
            $permisos[] = 'MODERATE_CONTENT';
            $permisos[] = 'DELETE_ANY_POST';
        }

        return array_values(array_unique($permisos));
    }

    public function tienePermiso(int $idUsuario, string $codigoAccion): bool {
        $arbol = $this->construirArbolCompositeUsuario($idUsuario);
        if ($arbol->tienePermiso($codigoAccion)) {
            return true;
        }
        $permisos = $this->obtenerPermisosUsuario($idUsuario);
        return in_array($codigoAccion, $permisos, true)
            || in_array('OWNER_FULL', $permisos, true)
            || in_array('ADMIN_FULL', $permisos, true);
    }

    public function obtenerRoles(): array {
        $this->asegurarTablasRbac();
        return $this->db->query("SELECT * FROM roles ORDER BY idRol ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearRol(string $nombreRol, ?int $idActor = null): int {
        $nombreLimpio = trim($nombreRol);
        $stmt = $this->db->prepare("INSERT INTO roles (nombreRol) VALUES (:nombre)");
        $stmt->execute([':nombre' => $nombreLimpio]);
        $id = (int)$this->db->lastInsertId();
        (new AuditoriaRepository())->registrar(
            $idActor,
            'ALTA_ROL',
            "Rol '{$nombreLimpio}' (#{$id}) creado",
            'roles',
            null,
            null,
            json_encode(['idRol' => $id, 'nombreRol' => $nombreLimpio], JSON_UNESCAPED_UNICODE)
        );
        return $id;
    }

    public function actualizarRol(int $idRol, string $nombreRol, ?int $idActor = null): bool {
        $stmtOld = $this->db->prepare("SELECT * FROM roles WHERE idRol = :id LIMIT 1");
        $stmtOld->execute([':id' => $idRol]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);
        if (!$old) return false;
        if (($old['nombreRol'] ?? '') === 'Dueño' && trim($nombreRol) !== 'Dueño') {
            throw new DomainException("No se puede renombrar el rol supremo Dueño.");
        }

        $nombreLimpio = trim($nombreRol);
        $stmt = $this->db->prepare("UPDATE roles SET nombreRol = :nombre WHERE idRol = :id");
        $ok = $stmt->execute([':nombre' => $nombreLimpio, ':id' => $idRol]);
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'MODIFICAR_ROL',
                "Rol #{$idRol} actualizado a '{$nombreLimpio}'",
                'roles',
                null,
                json_encode($old, JSON_UNESCAPED_UNICODE),
                json_encode(['idRol' => $idRol, 'nombreRol' => $nombreLimpio], JSON_UNESCAPED_UNICODE)
            );
        }
        return $ok;
    }

    public function eliminarRol(int $idRol, ?int $idActor = null): bool {
        $stmtOld = $this->db->prepare("SELECT * FROM roles WHERE idRol = :id LIMIT 1");
        $stmtOld->execute([':id' => $idRol]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);
        if (!$old) return false;

        if (in_array($idRol, [1, 2, 3], true) || ($old['nombreRol'] ?? '') === 'Dueño') {
            throw new DomainException("No se puede eliminar un rol base o protegido del sistema ('{$old['nombreRol']}').");
        }

        $stmt = $this->db->prepare("DELETE FROM roles WHERE idRol = :id");
        $ok = $stmt->execute([':id' => $idRol]) && $stmt->rowCount() > 0;
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'BAJA_ROL',
                "Rol #{$idRol} ('{$old['nombreRol']}') eliminado",
                'roles',
                null,
                json_encode($old, JSON_UNESCAPED_UNICODE),
                json_encode(['eliminado' => true])
            );
        }
        return $ok;
    }

    private function asegurarTablasRbac(): void {
        if ($this->rbacVerificado) {
            return;
        }
        $this->rbacVerificado = true;
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `grupos_permiso_permisos` (
                  `idGrupoPermiso` INT(11) NOT NULL,
                  `idPermiso`      INT(11) NOT NULL,
                  PRIMARY KEY (`idGrupoPermiso`, `idPermiso`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            try {
                $colsGp = $this->db->query("SHOW COLUMNS FROM `grupos_permiso` LIKE 'idGrupoPadre'")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($colsGp)) {
                    $this->db->exec("ALTER TABLE `grupos_permiso` ADD COLUMN `idGrupoPadre` INT(11) DEFAULT NULL");
                }
            } catch (Exception $e) {}

            $cntPerms = (int)$this->db->query("SELECT COUNT(*) FROM permisos")->fetchColumn();
            if ($cntPerms === 0) {
                $this->db->exec("
                    INSERT IGNORE INTO `permisos` (`idPermiso`, `nombre`, `codigoAccion`) VALUES
                    (1,  'Crear Publicaciones',              'POST_CREATE'),
                    (2,  'Responder Consultas',              'POST_REPLY'),
                    (3,  'Calificar Instituciones',          'RATE_UNIVERSITY'),
                    (4,  'Moderar Contenido',                'MODERATE_CONTENT'),
                    (5,  'Gestionar Usuarios',               'MANAGE_USERS'),
                    (6,  'Enviar Mensajes Directos',         'SEND_DIRECT_MESSAGE'),
                    (7,  'Ver Métricas Institucionales',     'VIEW_INSTITUTION_METRICS'),
                    (8,  'Insignia Oficial Verificada',      'OFFICIAL_BADGE'),
                    (9,  'Eliminar Cualquier Publicación',   'DELETE_ANY_POST'),
                    (10, 'Administración Total del Sistema', 'ADMIN_FULL')
                ");
            }

            $cnt = (int)$this->db->query("SELECT COUNT(*) FROM grupos_permiso_permisos")->fetchColumn();
            if ($cnt === 0) {
                $this->db->exec("
                    INSERT IGNORE INTO `grupos_permiso_permisos` (`idGrupoPermiso`, `idPermiso`) VALUES
                    (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),
                    (2,1),(2,2),(2,4),(2,6),(2,9),
                    (3,1),(3,2),(3,3),(3,6),
                    (4,1),(4,2),(4,3),(4,6),(4,7),(4,8)
                ");
            }

            // Asegurar permiso supremo OWNER_FULL y rol Dueño (solo si aún no existen)
            $stmtOwnerPerm = $this->db->prepare("SELECT idPermiso FROM permisos WHERE codigoAccion = 'OWNER_FULL' LIMIT 1");
            $stmtOwnerPerm->execute();
            $idPermOwner = (int)$stmtOwnerPerm->fetchColumn();
            if ($idPermOwner === 0) {
                $extras = [
                    ['Control Supremo de Dueño del Sistema', 'OWNER_FULL'],
                    ['Gestionar Administradores',            'MANAGE_ADMINS'],
                    ['Gestionar Dueños del Sistema',         'MANAGE_OWNERS'],
                    ['Generar Resguardos SQL',               'SYSTEM_BACKUP'],
                    ['Administrar Jerarquía RBAC Composite', 'RBAC_COMPOSITE_ADMIN'],
                ];
                $stmtInsPerm = $this->db->prepare("INSERT IGNORE INTO permisos (nombre, codigoAccion) VALUES (:n, :c)");
                foreach ($extras as $ex) {
                    $stmtInsPerm->execute([':n' => $ex[0], ':c' => $ex[1]]);
                }
                $stmtOwnerPerm->execute();
                $idPermOwner = (int)$stmtOwnerPerm->fetchColumn();
            }

            $stmtRolDueno = $this->db->prepare("SELECT idRol FROM roles WHERE nombreRol = 'Dueño' LIMIT 1");
            $stmtRolDueno->execute();
            $idRolDueno = (int)$stmtRolDueno->fetchColumn();
            if ($idRolDueno === 0) {
                try {
                    $this->db->exec("INSERT INTO roles (idRol, nombreRol) VALUES (7, 'Dueño')");
                    $idRolDueno = 7;
                } catch (Exception $e) {
                    $this->db->exec("INSERT IGNORE INTO roles (nombreRol) VALUES ('Dueño')");
                    $stmtRolDueno->execute();
                    $idRolDueno = (int)$stmtRolDueno->fetchColumn();
                }
            }

            // Asegurar grupo compuesto 'Dueños del Sistema' heredando grupo 1 (Administradores Generales)
            $stmtGrpDueno = $this->db->prepare("SELECT idGrupoPermiso FROM grupos_permiso WHERE nombre = 'Dueños del Sistema' LIMIT 1");
            $stmtGrpDueno->execute();
            $idGrpDueno = (int)$stmtGrpDueno->fetchColumn();
            $grupoDuenoRecienCreado = false;
            if ($idGrpDueno === 0) {
                $grupoDuenoRecienCreado = true;
                try {
                    $stmtInsGrp = $this->db->prepare("INSERT INTO grupos_permiso (nombre, descripcion, idGrupoPadre) VALUES ('Dueños del Sistema', 'Propietarios con derechos aumentados (hereda Composite de Administradores Generales)', 1)");
                    $stmtInsGrp->execute();
                    $idGrpDueno = (int)$this->db->lastInsertId();
                } catch (Exception $e) {
                    $stmtInsGrp = $this->db->prepare("INSERT INTO grupos_permiso (nombre, descripcion) VALUES ('Dueños del Sistema', 'Propietarios con derechos aumentados')");
                    $stmtInsGrp->execute();
                    $idGrpDueno = (int)$this->db->lastInsertId();
                }
                $codigosOwner = ['OWNER_FULL', 'MANAGE_ADMINS', 'MANAGE_OWNERS', 'SYSTEM_BACKUP', 'RBAC_COMPOSITE_ADMIN'];
                $inClause = "'" . implode("','", $codigosOwner) . "'";
                $idsPermsOwner = $this->db->query("SELECT idPermiso FROM permisos WHERE codigoAccion IN ({$inClause})")->fetchAll(PDO::FETCH_COLUMN);
                $stmtLink = $this->db->prepare("INSERT IGNORE INTO grupos_permiso_permisos (idGrupoPermiso, idPermiso) VALUES (:g, :p)");
                foreach ($idsPermsOwner as $pid) {
                    $stmtLink->execute([':g' => $idGrpDueno, ':p' => (int)$pid]);
                }
            }

            // Promover inicialmente la cuenta de Arik Yelin (solo cuando se crea el grupo Dueños por primera vez y nadie tiene aún el rol Dueño)
            if ($grupoDuenoRecienCreado && $idRolDueno > 0 && $idGrpDueno > 0) {
                $cntDuenos = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE idRol = {$idRolDueno} OR tipoPerfil = 'Dueño'")->fetchColumn();
                if ($cntDuenos === 0) {
                    $stmtArik = $this->db->prepare("SELECT idUsuario FROM usuarios WHERE LOWER(email) = 'arikayelin@gmail.com' OR idUsuario = 4 ORDER BY CASE WHEN LOWER(email) = 'arikayelin@gmail.com' THEN 0 ELSE 1 END LIMIT 1");
                    $stmtArik->execute();
                    $idArik = (int)$stmtArik->fetchColumn();
                    if ($idArik > 0) {
                        $this->db->prepare("UPDATE usuarios SET idRol = :rol, tipoPerfil = 'Dueño' WHERE idUsuario = :id")
                                 ->execute([':rol' => $idRolDueno, ':id' => $idArik]);
                        foreach ([$idGrpDueno, 1, 3] as $gid) {
                            $this->vincularGrupoSiNoExiste($idArik, (int)$gid);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("asegurarTablasRbac: " . $e->getMessage());
        }
    }

    private function vincularGrupoSiNoExiste(int $idUsuario, int $idGrupoPermiso): void {
        $stmtChk = $this->db->prepare("SELECT COUNT(*) FROM detalle_grupo_permiso WHERE idUsuario = :u AND idGrupoPermiso = :g");
        $stmtChk->execute([':u' => $idUsuario, ':g' => $idGrupoPermiso]);
        if ((int)$stmtChk->fetchColumn() === 0) {
            $stmtIns = $this->db->prepare("INSERT INTO detalle_grupo_permiso (idUsuario, idGrupoPermiso, fechaAsignacion) VALUES (:u, :g, NOW())");
            $stmtIns->execute([':u' => $idUsuario, ':g' => $idGrupoPermiso]);
        }
    }

    public function obtenerPermisosCatalogo(): array {
        $this->asegurarTablasRbac();
        try {
            return $this->db->query("SELECT * FROM permisos ORDER BY idPermiso ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function crearPermiso(string $nombre, string $codigoAccion, ?int $idActor = null): int {
        $nombreLimpio = trim($nombre);
        $codigoLimpio = strtoupper(trim($codigoAccion));
        $stmt = $this->db->prepare("INSERT INTO permisos (nombre, codigoAccion) VALUES (:nombre, :codigo)");
        $stmt->execute([':nombre' => $nombreLimpio, ':codigo' => $codigoLimpio]);
        $id = (int)$this->db->lastInsertId();
        (new AuditoriaRepository())->registrar(
            $idActor,
            'ALTA_PERMISO',
            "Permiso '{$codigoLimpio}' (#{$id}) creado",
            'permisos',
            null,
            null,
            json_encode(['idPermiso' => $id, 'nombre' => $nombreLimpio, 'codigoAccion' => $codigoLimpio], JSON_UNESCAPED_UNICODE)
        );
        return $id;
    }

    public function actualizarPermiso(int $idPermiso, string $nombre, string $codigoAccion, ?int $idActor = null): bool {
        $stmtOld = $this->db->prepare("SELECT * FROM permisos WHERE idPermiso = :id LIMIT 1");
        $stmtOld->execute([':id' => $idPermiso]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);
        if (!$old) return false;

        $nombreLimpio = trim($nombre);
        $codigoLimpio = strtoupper(trim($codigoAccion));
        $origCode = $old['codigoAccion'] ?? '';
        if (in_array($origCode, ['ADMIN_FULL', 'OWNER_FULL'], true) && $codigoLimpio !== $origCode) {
            throw new DomainException("No se puede modificar el código del permiso crítico {$origCode}.");
        }
        $stmt = $this->db->prepare("UPDATE permisos SET nombre = :nombre, codigoAccion = :codigo WHERE idPermiso = :id");
        $ok = $stmt->execute([':nombre' => $nombreLimpio, ':codigo' => $codigoLimpio, ':id' => $idPermiso]);
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'MODIFICAR_PERMISO',
                "Permiso #{$idPermiso} ('{$codigoLimpio}') actualizado",
                'permisos',
                null,
                json_encode($old, JSON_UNESCAPED_UNICODE),
                json_encode(['idPermiso' => $idPermiso, 'nombre' => $nombreLimpio, 'codigoAccion' => $codigoLimpio], JSON_UNESCAPED_UNICODE)
            );
        }
        return $ok;
    }

    public function eliminarPermiso(int $idPermiso, ?int $idActor = null): bool {
        $stmtOld = $this->db->prepare("SELECT * FROM permisos WHERE idPermiso = :id LIMIT 1");
        $stmtOld->execute([':id' => $idPermiso]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);
        if (!$old) return false;
        if (in_array($old['codigoAccion'] ?? '', ['ADMIN_FULL', 'OWNER_FULL'], true)) {
            throw new DomainException("No se puede eliminar el permiso crítico {$old['codigoAccion']}.");
        }

        $stmt = $this->db->prepare("DELETE FROM permisos WHERE idPermiso = :id");
        $ok = $stmt->execute([':id' => $idPermiso]) && $stmt->rowCount() > 0;
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'BAJA_PERMISO',
                "Permiso #{$idPermiso} ('{$old['codigoAccion']}') eliminado",
                'permisos',
                null,
                json_encode($old, JSON_UNESCAPED_UNICODE),
                json_encode(['eliminado' => true])
            );
        }
        return $ok;
    }

    /**
     * Obtiene los grupos de permisos con sus permisos directos, heredados por Composite y cantidad de usuarios.
     */
    public function obtenerGruposPermiso(): array {
        $this->asegurarTablasRbac();
        $grupos = $this->db->query("SELECT * FROM grupos_permiso ORDER BY idGrupoPermiso ASC")->fetchAll(PDO::FETCH_ASSOC);
        $nombresById = [];
        $nodos = [];
        foreach ($grupos as $row) {
            $idG = (int)$row['idGrupoPermiso'];
            $nombresById[$idG] = $row['nombre'];
            $idPadre = !empty($row['idGrupoPadre']) ? (int)$row['idGrupoPadre'] : null;
            $nodos[$idG] = new GrupoPermiso((string)$row['nombre'], (string)($row['descripcion'] ?? ''), $idG, $idPadre);
        }

        // Hidratar nodos Composite para resolver también permisos efectivos/heredados
        try {
            $rowsRel = $this->db->query("
                SELECT gpp.idGrupoPermiso, p.idPermiso, p.nombre, p.codigoAccion
                FROM grupos_permiso_permisos gpp
                INNER JOIN permisos p ON gpp.idPermiso = p.idPermiso
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rowsRel as $rel) {
                $idG = (int)$rel['idGrupoPermiso'];
                if (isset($nodos[$idG])) {
                    $nodos[$idG]->agregarComponente(new Permiso((string)$rel['nombre'], (string)$rel['codigoAccion'], (int)$rel['idPermiso']));
                }
            }
            foreach ($grupos as $row) {
                $idG = (int)$row['idGrupoPermiso'];
                $idPadre = !empty($row['idGrupoPadre']) ? (int)$row['idGrupoPadre'] : 0;
                if ($idPadre > 0 && $idPadre !== $idG && isset($nodos[$idG], $nodos[$idPadre])) {
                    $nodos[$idG]->agregarComponente($nodos[$idPadre]);
                }
            }
        } catch (Exception $e) {}

        foreach ($grupos as &$g) {
            $idGrupo = (int)$g['idGrupoPermiso'];
            $idPadre = !empty($g['idGrupoPadre']) ? (int)$g['idGrupoPadre'] : null;
            $g['idGrupoPadre']     = $idPadre;
            $g['nombreGrupoPadre'] = ($idPadre && isset($nombresById[$idPadre])) ? $nombresById[$idPadre] : null;
            $g['totalUsuarios']    = 0;
            $g['permisos']         = [];
            $g['codigosEfectivos'] = isset($nodos[$idGrupo]) ? $nodos[$idGrupo]->obtenerCodigosPermiso() : [];

            try {
                $stmtCount = $this->db->prepare("SELECT COUNT(DISTINCT idUsuario) FROM detalle_grupo_permiso WHERE idGrupoPermiso = :id");
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
     * Crea un nuevo grupo de permisos (compuesto Composite opcional vía idGrupoPadre).
     */
    public function crearGrupoPermiso(string $nombre, string $descripcion, array $idsPermisos = [], ?int $idActor = null, ?int $idGrupoPadre = null): array {
        $this->asegurarTablasRbac();
        $padreVal = ($idGrupoPadre && $idGrupoPadre > 0) ? $idGrupoPadre : null;
        try {
            $stmt = $this->db->prepare("INSERT INTO grupos_permiso (nombre, descripcion, idGrupoPadre) VALUES (:nombre, :desc, :padre)");
            $stmt->execute([':nombre' => trim($nombre), ':desc' => trim($descripcion), ':padre' => $padreVal]);
        } catch (Exception $e) {
            $stmt = $this->db->prepare("INSERT INTO grupos_permiso (nombre, descripcion) VALUES (:nombre, :desc)");
            $stmt->execute([':nombre' => trim($nombre), ':desc' => trim($descripcion)]);
        }
        $idGrupo = (int)$this->db->lastInsertId();

        $this->sincronizarPermisosDeGrupo($idGrupo, $idsPermisos);

        $valorNuevo = json_encode(['idGrupoPermiso' => $idGrupo, 'nombre' => $nombre, 'idGrupoPadre' => $padreVal, 'permisos' => $idsPermisos], JSON_UNESCAPED_UNICODE);
        (new AuditoriaRepository())->registrar(
            $idActor,
            'CREAR_GRUPO_PERMISO',
            "Grupo de permiso '{$nombre}' (#{$idGrupo}) creado",
            'grupos_permiso',
            null,
            null,
            $valorNuevo
        );

        return ['idGrupoPermiso' => $idGrupo, 'nombre' => $nombre, 'descripcion' => $descripcion, 'idGrupoPadre' => $padreVal];
    }

    /**
     * Modifica un grupo de permisos existente, su grupo padre Composite y sus permisos asociados.
     */
    public function actualizarGrupoPermiso(int $idGrupo, string $nombre, string $descripcion, array $idsPermisos = [], ?int $idActor = null, ?int $idGrupoPadre = null): bool {
        $this->asegurarTablasRbac();
        $stmtOld = $this->db->prepare("SELECT * FROM grupos_permiso WHERE idGrupoPermiso = :id LIMIT 1");
        $stmtOld->execute([':id' => $idGrupo]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

        $padreVal = ($idGrupoPadre && $idGrupoPadre > 0 && $idGrupoPadre !== $idGrupo) ? $idGrupoPadre : null;
        try {
            $stmt = $this->db->prepare("UPDATE grupos_permiso SET nombre = :nombre, descripcion = :desc, idGrupoPadre = :padre WHERE idGrupoPermiso = :id");
            $ok = $stmt->execute([':nombre' => trim($nombre), ':desc' => trim($descripcion), ':padre' => $padreVal, ':id' => $idGrupo]);
        } catch (Exception $e) {
            $stmt = $this->db->prepare("UPDATE grupos_permiso SET nombre = :nombre, descripcion = :desc WHERE idGrupoPermiso = :id");
            $ok = $stmt->execute([':nombre' => trim($nombre), ':desc' => trim($descripcion), ':id' => $idGrupo]);
        }

        $this->sincronizarPermisosDeGrupo($idGrupo, $idsPermisos);

        $valorOrig = $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null;
        $valorNuevo = json_encode(['idGrupoPermiso' => $idGrupo, 'nombre' => $nombre, 'descripcion' => $descripcion, 'idGrupoPadre' => $padreVal, 'permisos' => $idsPermisos], JSON_UNESCAPED_UNICODE);
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
        if (!$old) return false;

        if ($idGrupo === 1 || ($old['nombre'] ?? '') === 'Dueños del Sistema') {
            throw new DomainException("No se puede eliminar un grupo base del sistema ('{$old['nombre']}').");
        }

        $stmt = $this->db->prepare("DELETE FROM grupos_permiso WHERE idGrupoPermiso = :id");
        $ok = $stmt->execute([':id' => $idGrupo]);

        if ($ok) {
            $valorOrig = json_encode($old, JSON_UNESCAPED_UNICODE);
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

    /**
     * Vincula un usuario a un grupo adicional sin sobrescribir los anteriores (1 usuario -> N grupos).
     */
    public function asignarGrupoAUsuario(int $idUsuario, int $idGrupoPermiso, ?int $idActor = null): bool {
        $stmtChk = $this->db->prepare("SELECT COUNT(*) FROM detalle_grupo_permiso WHERE idUsuario = :u AND idGrupoPermiso = :g");
        $stmtChk->execute([':u' => $idUsuario, ':g' => $idGrupoPermiso]);
        if ((int)$stmtChk->fetchColumn() > 0) {
            $stmtUpd = $this->db->prepare("UPDATE detalle_grupo_permiso SET fechaAsignacion = NOW() WHERE idUsuario = :u AND idGrupoPermiso = :g");
            return $stmtUpd->execute([':u' => $idUsuario, ':g' => $idGrupoPermiso]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO detalle_grupo_permiso (idUsuario, idGrupoPermiso, fechaAsignacion)
            VALUES (:user, :grupo, NOW())
        ");
        $ok = $stmt->execute([':user' => $idUsuario, ':grupo' => $idGrupoPermiso]);
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'ASIGNAR_GRUPO_PERMISO',
                "Usuario ID {$idUsuario} vinculado al grupo {$idGrupoPermiso}",
                'detalle_grupo_permiso',
                null,
                null,
                json_encode(['idUsuario' => $idUsuario, 'idGrupoPermiso' => $idGrupoPermiso])
            );
        }
        return $ok;
    }

    /**
     * Desvincula uno de los grupos asignados a un usuario conservando sus demás grupos.
     */
    public function revocarGrupoDeUsuario(int $idUsuario, int $idGrupoPermiso, ?int $idActor = null): bool {
        $stmt = $this->db->prepare("DELETE FROM detalle_grupo_permiso WHERE idUsuario = :u AND idGrupoPermiso = :g");
        $ok = $stmt->execute([':u' => $idUsuario, ':g' => $idGrupoPermiso]) && $stmt->rowCount() > 0;
        if ($ok) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'REVOCAR_GRUPO_PERMISO',
                "Grupo #{$idGrupoPermiso} desvinculado del usuario #{$idUsuario}",
                'detalle_grupo_permiso',
                null,
                json_encode(['idUsuario' => $idUsuario, 'idGrupoPermiso' => $idGrupoPermiso]),
                json_encode(['revocado' => true])
            );
        }
        return $ok;
    }

    /**
     * Sincroniza la lista múltiple de grupos RBAC de un usuario.
     */
    public function sincronizarGruposUsuario(int $idUsuario, array $idsGrupos, ?int $idActor = null): void {
        $stmtOld = $this->db->prepare("SELECT idGrupoPermiso FROM detalle_grupo_permiso WHERE idUsuario = :u");
        $stmtOld->execute([':u' => $idUsuario]);
        $prev = array_map('intval', $stmtOld->fetchAll(PDO::FETCH_COLUMN));

        $nuevos = array_values(array_unique(array_filter(array_map('intval', $idsGrupos), static fn($v) => $v > 0)));
        $this->db->prepare("DELETE FROM detalle_grupo_permiso WHERE idUsuario = :u")->execute([':u' => $idUsuario]);

        $stmtIns = $this->db->prepare("INSERT INTO detalle_grupo_permiso (idUsuario, idGrupoPermiso, fechaAsignacion) VALUES (:u, :g, NOW())");
        foreach ($nuevos as $gid) {
            $stmtIns->execute([':u' => $idUsuario, ':g' => $gid]);
        }

        if ($prev !== $nuevos) {
            (new AuditoriaRepository())->registrar(
                $idActor,
                'SINCRONIZAR_GRUPOS_USUARIO',
                "Grupos múltiples del usuario #{$idUsuario} actualizados",
                'detalle_grupo_permiso',
                null,
                json_encode($prev),
                json_encode($nuevos)
            );
        }
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
                       p.idUniversidad, p.idSede, p.idCarrera,
                       p.puntuacionClima, p.puntuacionInfraestructura, p.puntuacionAcademica,
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
     * Valida derechos jerárquicos aumentados del rol Dueño vs Administrador común.
     */
    private function verificarAutoridadSobreUsuario(int $idObjetivo, ?int $idActor, ?int $nuevoIdRol = null, ?string $nuevoTipoPerfil = null): void {
        $target = $this->obtenerResumenUsuarioSeguridad($idObjetivo);
        if (!$target) {
            return;
        }
        $actorEsDueno = ($idActor !== null && $idActor > 0) ? $this->esUsuarioDueno($idActor) : false;
        $idRolDueno   = $this->obtenerRolDuenoId();

        if ($target['esDueno'] && !$actorEsDueno) {
            throw new DomainException("Acceso denegado: Solo un Dueño del sistema puede modificar o gestionar cuentas de Dueño.");
        }

        $targetEsAdmin = ((int)$target['idRol'] === 1 || $target['tipoPerfil'] === 'Administrador');
        if ($targetEsAdmin && !$target['esDueno'] && !$actorEsDueno && $idActor !== $idObjetivo) {
            throw new DomainException("Privilegio insuficiente: Solo el rol Dueño puede modificar, bloquear, resetear o cambiar el rol de un Administrador.");
        }

        $intentaPromoverDueno = ($nuevoIdRol !== null && $nuevoIdRol === $idRolDueno) || ($nuevoTipoPerfil === 'Dueño');
        if ($intentaPromoverDueno && !$actorEsDueno) {
            throw new DomainException("Solo un Dueño del sistema puede asignar el rol o perfil Dueño.");
        }
    }

    /**
     * Listado de usuarios con roles, múltiples grupos RBAC y estados para administración (CU5 / CU8).
     */
    public function obtenerUsuariosAdmin(): array {
        $this->asegurarTablasRbac();
        $sql = "SELECT u.idUsuario, u.nombre, u.apellido, u.email, u.celular, u.fNac,
                       u.tipoPerfil, u.idRol, u.idUniversidad, u.idCarrera,
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
        $usuarios = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $gruposPorUsuario = [];
        try {
            $rowsGrp = $this->db->query("
                SELECT dgp.idUsuario, gp.idGrupoPermiso, gp.nombre
                FROM detalle_grupo_permiso dgp
                INNER JOIN grupos_permiso gp ON dgp.idGrupoPermiso = gp.idGrupoPermiso
                ORDER BY gp.idGrupoPermiso ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rowsGrp as $rg) {
                $uid = (int)$rg['idUsuario'];
                $gruposPorUsuario[$uid][] = [
                    'idGrupoPermiso' => (int)$rg['idGrupoPermiso'],
                    'nombre'         => $rg['nombre'],
                ];
            }
        } catch (Exception $e) {}

        foreach ($usuarios as &$u) {
            $u['grupos'] = $gruposPorUsuario[(int)$u['idUsuario']] ?? [];
        }
        return $usuarios;
    }

    /**
     * Actualización integral de datos de un usuario desde el panel administrativo (ABM Usuarios + Auditoría T06).
     */
    public function actualizarUsuarioAdmin(int $idUsuario, array $datos, ?int $idActor = null): bool {
        $stmtOld = $this->db->prepare("
            SELECT idUsuario, nombre, apellido, email, celular, fNac, tipoPerfil, idRol, idUniversidad, idCarrera,
                   COALESCE(estadoCuenta, 'activo') AS estadoCuenta
            FROM usuarios
            WHERE idUsuario = :id LIMIT 1
        ");
        $stmtOld->execute([':id' => $idUsuario]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);
        if (!$old) {
            return false;
        }

        $nombre        = trim((string)($datos['nombre'] ?? $old['nombre']));
        $apellido      = trim((string)($datos['apellido'] ?? $old['apellido']));
        $email         = trim((string)($datos['email'] ?? $old['email']));
        $celular       = array_key_exists('celular', $datos) ? ($datos['celular'] ?: null) : $old['celular'];
        $fNac          = !empty($datos['fNac']) ? $datos['fNac'] : $old['fNac'];
        $tipoPerfil    = trim((string)($datos['tipoPerfil'] ?? $old['tipoPerfil']));
        $idRol         = !empty($datos['idRol']) ? (int)$datos['idRol'] : (int)$old['idRol'];
        $idUniversidad = array_key_exists('idUniversidad', $datos) ? (!empty($datos['idUniversidad']) ? (int)$datos['idUniversidad'] : null) : ($old['idUniversidad'] ? (int)$old['idUniversidad'] : null);
        $idCarrera     = array_key_exists('idCarrera', $datos) ? (!empty($datos['idCarrera']) ? (int)$datos['idCarrera'] : null) : ($old['idCarrera'] ? (int)$old['idCarrera'] : null);
        $estadoCuenta  = !empty($datos['estadoCuenta']) ? trim((string)$datos['estadoCuenta']) : $old['estadoCuenta'];

        $this->verificarAutoridadSobreUsuario($idUsuario, $idActor, $idRol, $tipoPerfil);

        $sql = "UPDATE usuarios
                SET nombre = :nombre, apellido = :apellido, email = :email, celular = :celular,
                    fNac = :fNac, tipoPerfil = :tipoPerfil, idRol = :idRol,
                    idUniversidad = :idUni, idCarrera = :idCar, estadoCuenta = :estado
                WHERE idUsuario = :id";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':nombre'     => $nombre,
            ':apellido'   => $apellido,
            ':email'      => $email,
            ':celular'    => $celular,
            ':fNac'       => $fNac,
            ':tipoPerfil' => $tipoPerfil,
            ':idRol'      => $idRol,
            ':idUni'      => $idUniversidad,
            ':idCar'      => $idCarrera,
            ':estado'     => $estadoCuenta,
            ':id'         => $idUsuario,
        ]);

        if ($ok) {
            if (isset($datos['grupos']) && is_array($datos['grupos'])) {
                $this->sincronizarGruposUsuario($idUsuario, $datos['grupos'], $idActor);
            }
            $nuevo = [
                'nombre' => $nombre, 'apellido' => $apellido, 'email' => $email,
                'tipoPerfil' => $tipoPerfil, 'idRol' => $idRol,
                'idUniversidad' => $idUniversidad, 'idCarrera' => $idCarrera,
                'estadoCuenta' => $estadoCuenta,
            ];
            (new AuditoriaRepository())->registrar(
                $idActor,
                'MODIFICAR_USUARIO_ADMIN',
                "Usuario #{$idUsuario} ({$email}) modificado administrativamente",
                'usuarios',
                null,
                json_encode($old, JSON_UNESCAPED_UNICODE),
                json_encode($nuevo, JSON_UNESCAPED_UNICODE)
            );
        }
        return $ok;
    }

    /**
     * Asignación de rol a un usuario con registro de valorOriginal y valorNuevo (CU5 / Punto T06).
     */
    public function cambiarRolUsuario(int $idUsuario, int $idRol, ?int $idActor = null): bool {
        $this->verificarAutoridadSobreUsuario($idUsuario, $idActor, $idRol, null);

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
     * Bloquea o reactiva una cuenta de usuario con trazabilidad y protección jerárquica (Dueño > Admin).
     */
    public function cambiarEstadoUsuario(int $idUsuario, string $accion, ?int $idActor = null): bool {
        $stmtPrev = $this->db->prepare("SELECT idUsuario, email, idRol, tipoPerfil, COALESCE(estadoCuenta, 'activo') AS estadoCuenta FROM usuarios WHERE idUsuario = :id LIMIT 1");
        $stmtPrev->execute([':id' => $idUsuario]);
        $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        $esBloquear = ($accion === 'bloquear' || $accion === 'inactivo');
        if ($esBloquear && $idActor !== null && $idUsuario === $idActor) {
            throw new DomainException("No puedes bloquear tu propia cuenta activa.");
        }
        $this->verificarAutoridadSobreUsuario($idUsuario, $idActor, null, null);
        if ($esBloquear && $prev && ((int)($prev['idRol'] ?? 0) === 1 || ($prev['tipoPerfil'] ?? '') === 'Administrador')) {
            $actorEsDueno = ($idActor !== null && $idActor > 0) ? $this->esUsuarioDueno($idActor) : false;
            if (!$actorEsDueno) {
                throw new DomainException("Solo el rol Dueño puede bloquear una cuenta de Administrador.");
            }
        }

        $nuevoEstado = $esBloquear ? 'inactivo' : 'activo';
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
     * Reseteo administrativo de clave de un usuario (derechos aumentados de Dueño sobre Admins).
     */
    public function resetearClaveUsuario(int $idUsuario, string $nuevaClave, ?int $idActor = null): bool {
        $stmtPrev = $this->db->prepare("SELECT idUsuario, email, idRol, tipoPerfil FROM usuarios WHERE idUsuario = :id LIMIT 1");
        $stmtPrev->execute([':id' => $idUsuario]);
        $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        if (!$prev) {
            throw new DomainException("El usuario especificado no existe.");
        }
        $this->verificarAutoridadSobreUsuario($idUsuario, $idActor, null, null);

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
