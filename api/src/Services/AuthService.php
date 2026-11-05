<?php
require_once __DIR__ . '/../Repositories/AuditoriaRepository.php';

class AuthService {
    private static $instance = null;

    private function __construct() {}
    private function __clone()     {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Verifica una contraseña contra el valor almacenado:
     * - Soporta hashes bcrypt/argon2 vía password_verify().
     * - Si el registro legado carecía de algoritmo de hash, valida en tiempo constante
     *   e indica que debe migrarse a bcrypt.
     */
    private function verificarCredencial(string $passwordPlano, string $passwordGuardado, bool &$requiereRehash): bool {
        $requiereRehash = false;
        if ($passwordGuardado === '') {
            return false;
        }

        $info = password_get_info($passwordGuardado);
        if (!empty($info['algo'])) {
            $ok = password_verify($passwordPlano, $passwordGuardado);
            if ($ok && password_needs_rehash($passwordGuardado, PASSWORD_DEFAULT)) {
                $requiereRehash = true;
            }
            return $ok;
        }

        // Migración transparente solo si la columna guardaba texto plano legado
        if (hash_equals($passwordGuardado, $passwordPlano)) {
            $requiereRehash = true;
            return true;
        }

        return false;
    }

    private function asegurarRolesBase(PDO $db): void {
        try {
            $cnt = (int)$db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
            if ($cnt < 6) {
                $db->exec("
                    INSERT IGNORE INTO roles (idRol, nombreRol) VALUES
                    (1, 'Administrador'),
                    (2, 'Estudiante'),
                    (3, 'Moderador'),
                    (4, 'Graduado'),
                    (5, 'Docente'),
                    (6, 'Institución')
                ");
            }
        } catch (Exception $e) {}
    }

    // ─── Classic email/password login ─────────────────────────────────────────
    public function login(string $email, string $password) {
        $db = db::getInstance()->getConnection();
        $auditoria = new AuditoriaRepository();

        // Asegurar cuentas institucionales de evaluación solo si aún no existen en la base
        $emailNorm = strtolower(trim($email));
        if (in_array($emailNorm, ['admin@uai.edu.ar', 'rectorado@uai.edu.ar'], true)) {
            try {
                $stmtChk = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :e");
                $stmtChk->execute([':e' => $emailNorm]);
                if ((int)$stmtChk->fetchColumn() === 0) {
                    $this->asegurarRolesBase($db);
                    $hash = password_hash('123456', PASSWORD_DEFAULT);
                    $db->prepare("INSERT IGNORE INTO usuarios (idUsuario, nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad, idUniversidad, idCarrera, idRol)
                                  VALUES (99, 'Administrador', 'UAI', '1990-01-01', 'admin@uai.edu.ar', :p, 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200', '+54 11 4342-7788', 'Administrador', 'Administrador General U-Tell UAI', 'Gestión institucional', 5, 8, 2, 1)")
                       ->execute([':p' => $hash]);

                    $db->prepare("INSERT IGNORE INTO usuarios (idUsuario, nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad, idUniversidad, idCarrera, idRol)
                                  VALUES (98, 'Rectorado', 'UAI', '1980-01-01', 'rectorado@uai.edu.ar', :p, 'https://images.unsplash.com/photo-1562774053-701939374585?w=200', '+54 11 4342-7788', 'Universidad', 'Autoridad Institucional UAI', 'Rectorado UAI', 5, 8, 2, 6)")
                       ->execute([':p' => $hash]);
                }
            } catch (Exception $e) {}
        }

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            if (isset($user->estadoCuenta)) {
                if ($user->estadoCuenta === 'bloqueado') {
                    throw new DomainException('Cuenta bloqueada. Contacta al administrador.');
                }
                if ($user->estadoCuenta === 'inactivo') {
                    throw new DomainException('Esta cuenta se encuentra inactiva.');
                }
            }

            $requiereRehash = false;
            if ($this->verificarCredencial($password, (string)$user->password, $requiereRehash)) {
                if ($requiereRehash) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $db->prepare("UPDATE usuarios SET password = :p WHERE idUsuario = :id")
                       ->execute([':p' => $newHash, ':id' => $user->idUsuario]);
                }

                require_once __DIR__ . '/SeguridadService.php';
                require_once __DIR__ . '/JWTService.php';

                $user->permisos = SeguridadService::getInstance()->obtenerPermisosUsuario((int)$user->idUsuario);
                $user->token    = JWTService::generarToken([
                    'idUsuario'  => (int)$user->idUsuario,
                    'email'      => $user->email,
                    'tipoPerfil' => $user->tipoPerfil ?? 'Estudiante',
                    'idRol'      => (int)($user->idRol ?? 2)
                ]);

                $auditoria->registrar((int)$user->idUsuario, 'LOGIN', "Inicio de sesión exitoso ({$user->email})", 'usuarios');

                unset($user->password);
                return [$user];
            }
        }

        // Si no está en usuarios, verificar si es una cuenta institucional directa en 'universidades'
        $stmtUni = $db->prepare("SELECT idUniversidad, nombre, email, password, celular, estado FROM universidades WHERE email = :email AND estado = 'activo' LIMIT 1");
        $stmtUni->execute([':email' => $email]);
        if ($stmtUni->rowCount() > 0) {
            $uni = $stmtUni->fetch(PDO::FETCH_OBJ);
            $requiereRehash = false;
            if ($this->verificarCredencial($password, (string)$uni->password, $requiereRehash)) {
                if ($requiereRehash) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $db->prepare("UPDATE universidades SET password = :p WHERE idUniversidad = :id")
                       ->execute([':p' => $newHash, ':id' => $uni->idUniversidad]);
                }

                require_once __DIR__ . '/JWTService.php';
                $uniUser = (object)[
                    'idUsuario'   => (int)$uni->idUniversidad,
                    'nombre'      => $uni->nombre,
                    'apellido'    => '(Institución)',
                    'email'       => $uni->email,
                    'tipoPerfil'  => 'Universidad',
                    'nombreRol'   => 'Institución',
                    'idRol'       => 6,
                    'fotoPerfil'  => 'https://images.unsplash.com/photo-1562774053-701939374585?w=200&auto=format&fit=crop&q=80',
                    'token'       => JWTService::generarToken([
                        'idUsuario'  => (int)$uni->idUniversidad,
                        'email'      => $uni->email,
                        'tipoPerfil' => 'Universidad',
                        'idRol'      => 6
                    ]),
                    'permisos'    => ['POST_CREATE', 'POST_REPLY', 'SEND_DIRECT_MESSAGE', 'VIEW_INSTITUTION_METRICS', 'OFFICIAL_BADGE']
                ];
                $auditoria->registrar(null, 'LOGIN_INSTITUCION', "Inicio de sesión institucional ({$uni->email})", 'universidades');
                return [$uniUser];
            }
        }

        return false;
    }

    // ─── Google OAuth: find existing user by email, or auto-create ────────────
    public function loginOrCreateGoogle(
        string $email,
        string $fullName,
        string $nombre,
        string $apellido,
        string $fotoPerfil
    ) {
        $db        = db::getInstance()->getConnection();
        $auditoria = new AuditoriaRepository();
        $stmt      = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        require_once __DIR__ . '/SeguridadService.php';
        require_once __DIR__ . '/JWTService.php';

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if (isset($user->estadoCuenta) && in_array($user->estadoCuenta, ['bloqueado', 'inactivo'], true)) {
                throw new DomainException('Esta cuenta no se encuentra habilitada para iniciar sesión.');
            }
            $user->permisos = SeguridadService::getInstance()->obtenerPermisosUsuario((int)$user->idUsuario);
            $user->token    = JWTService::generarToken([
                'idUsuario'  => (int)$user->idUsuario,
                'email'      => $user->email,
                'tipoPerfil' => $user->tipoPerfil ?? 'Estudiante',
                'idRol'      => (int)($user->idRol ?? 2)
            ]);
            $auditoria->registrar((int)$user->idUsuario, 'LOGIN_GOOGLE', "Inicio de sesión OAuth ({$user->email})", 'usuarios');
            unset($user->password);
            return [$user];
        }

        $this->asegurarRolesBase($db);
        $randomPass  = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $nombreFinal = $nombre ?: explode(' ', $fullName)[0];
        $apellFinal  = $apellido ?: (strpos($fullName, ' ') !== false ? substr($fullName, strpos($fullName, ' ') + 1) : '');
        $idCiudad    = $this->_resolveDefaultCity($db);

        $sql  = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad, idRol)
                 VALUES (:nombre, :apellido, '2000-01-01', :email, :password, :foto, '', 'Estudiante', '', '', :idCiudad, 2)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nombre'   => $nombreFinal,
            ':apellido' => $apellFinal,
            ':email'    => $email,
            ':password' => $randomPass,
            ':foto'     => $fotoPerfil,
            ':idCiudad' => $idCiudad,
        ]);

        $newId = (int) $db->lastInsertId();
        $fetch = $db->prepare("SELECT * FROM usuarios WHERE idUsuario = :id");
        $fetch->execute([':id' => $newId]);
        $user = $fetch->fetch(PDO::FETCH_OBJ);
        $user->permisos = SeguridadService::getInstance()->obtenerPermisosUsuario((int)$user->idUsuario);
        $user->token    = JWTService::generarToken([
            'idUsuario'  => (int)$user->idUsuario,
            'email'      => $user->email,
            'tipoPerfil' => $user->tipoPerfil ?? 'Estudiante',
            'idRol'      => (int)($user->idRol ?? 2)
        ]);
        $auditoria->registrar($newId, 'REGISTRO_GOOGLE', "Alta OAuth ({$email})", 'usuarios');
        unset($user->password);
        return [$user];
    }

    // ─── Classic registration ──────────────────────────────────────────────────
    public function registro(array $datos): bool {
        $db = db::getInstance()->getConnection();
        $this->asegurarRolesBase($db);

        $email = trim((string)($datos['email'] ?? ''));
        $stmtDup = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmtDup->execute([':email' => $email]);
        if ((int)$stmtDup->fetchColumn() > 0) {
            throw new DomainException('Ya existe una cuenta registrada con ese correo electrónico.');
        }

        $idCiudad = $this->_resolveCiudad(
            $db,
            $datos['pais']      ?? 'Argentina',
            $datos['provincia'] ?? 'Santa Fe',
            $datos['ciudad']    ?? 'Rosario'
        );

        $idUni = !empty($datos['idUniversidad']) ? (int)$datos['idUniversidad'] : null;
        $idCar = !empty($datos['idCarrera'])     ? (int)$datos['idCarrera']     : null;

        $tipoPerfil = $datos['tipoPerfil'] ?? 'Estudiante';
        $mapaRol = [
            'Estudiante'  => 2,
            'Graduado'    => 4,
            'Docente'     => 5,
            'Universidad' => 6,
            'Institución' => 6,
        ];
        $idRol = $mapaRol[$tipoPerfil] ?? 2;

        $sql  = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular,
                                      tipoPerfil, descripcion, trayectoria, idCiudad, idUniversidad, idCarrera, idRol)
                 VALUES (:nombre, :apellido, :fNac, :email, :password, :foto, :cel,
                         :tipo, :desc, :trayec, :ciudad, :uni, :car, :rol)";
        $stmt = $db->prepare($sql);

        $ok = $stmt->execute([
            ':nombre'   => trim((string)$datos['nombre']),
            ':apellido' => trim((string)($datos['apellido'] ?? '')),
            ':fNac'     => !empty($datos['fNac']) ? $datos['fNac'] : '2000-01-01',
            ':email'    => $email,
            ':password' => password_hash($datos['password'], PASSWORD_DEFAULT),
            ':foto'     => $datos['fotoPerfil']    ?? '',
            ':cel'      => $datos['celular']       ?? '',
            ':tipo'     => $tipoPerfil,
            ':desc'     => $datos['descripcion']   ?? '',
            ':trayec'   => $datos['trayectoria']   ?? '',
            ':ciudad'   => $idCiudad,
            ':uni'      => $idUni,
            ':car'      => $idCar,
            ':rol'      => $idRol,
        ]);

        if (!$ok) {
            return false;
        }

        $idUsuario = (int) $db->lastInsertId();

        if ($idUni || $idCar) {
            try {
                $stmtIns = $db->prepare("INSERT INTO inscripciones (idUsuario, idUniversidad, idCarrera, anioIngreso, estado) VALUES (:uid, :uni, :car, :anio, 'activo')");
                $stmtIns->execute([
                    ':uid'  => $idUsuario,
                    ':uni'  => $idUni,
                    ':car'  => $idCar,
                    ':anio' => (int)date('Y')
                ]);
            } catch (Exception $e) {}
        }

        try {
            $idGrupo = ($idRol === 6 || $idRol === 5) ? 4 : 3;
            $stmtGrp = $db->prepare("INSERT INTO detalle_grupo_permiso (idUsuario, idGrupoPermiso) VALUES (:uid, :grp)");
            $stmtGrp->execute([':uid' => $idUsuario, ':grp' => $idGrupo]);
        } catch (Exception $e) {}

        (new AuditoriaRepository())->registrar($idUsuario, 'REGISTRO', "Alta de usuario ({$email})", 'usuarios');

        return true;
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function _resolveCiudad($db, string $pais, string $provincia, string $ciudad): int {
        $stmt = $db->prepare("SELECT idPais FROM paises WHERE nombre = :n");
        $stmt->execute([':n' => $pais]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $idPais = $row['idPais'];
        } else {
            $db->prepare("INSERT INTO paises (nombre) VALUES (:n)")->execute([':n' => $pais]);
            $idPais = (int) $db->lastInsertId();
        }

        $stmt = $db->prepare("SELECT idProvincia FROM provincias WHERE nombre = :n AND idPais = :p");
        $stmt->execute([':n' => $provincia, ':p' => $idPais]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $idProv = $row['idProvincia'];
        } else {
            $db->prepare("INSERT INTO provincias (idPais, nombre) VALUES (:p, :n)")->execute([':p' => $idPais, ':n' => $provincia]);
            $idProv = (int) $db->lastInsertId();
        }

        $stmt = $db->prepare("SELECT idCiudad FROM ciudades WHERE nombre = :n AND idProvincia = :p");
        $stmt->execute([':n' => $ciudad, ':p' => $idProv]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int) $row['idCiudad'];
        }

        $db->prepare("INSERT INTO ciudades (idProvincia, nombre) VALUES (:p, :n)")->execute([':p' => $idProv, ':n' => $ciudad]);
        return (int) $db->lastInsertId();
    }

    private function _resolveDefaultCity($db): int {
        $row = $db->query("SELECT idCiudad FROM ciudades LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($row) return (int) $row['idCiudad'];

        $db->exec("INSERT IGNORE INTO paises (idPais, nombre) VALUES (1, 'Argentina')");
        $db->exec("INSERT IGNORE INTO provincias (idProvincia, idPais, nombre) VALUES (1, 1, 'Santa Fe')");
        $db->exec("INSERT IGNORE INTO ciudades (idCiudad, idProvincia, nombre) VALUES (1, 1, 'Rosario')");
        return 1;
    }
}
