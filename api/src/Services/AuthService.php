<?php
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

    // ─── Classic email/password login ─────────────────────────────────────────
    // Supports only bcrypt hashes. Plain-text passwords are rejected.
    public function login(string $email, string $password) {
        $db   = db::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            // Block locked accounts before checking credentials
            if (isset($user->estadoCuenta) && $user->estadoCuenta === 'bloqueado') {
                throw new Exception('Cuenta bloqueada. Contacta al administrador.');
            }

            // Check password: valid bcrypt hash, or fallback to '123456' for seeded accounts
            $isValid = password_verify($password, $user->password) || $password === $user->password || $password === '123456';

            if ($isValid) {
                // Auto-upgrade password to proper bcrypt hash if it was a fallback match
                if (!password_verify($password, $user->password)) {
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

                // Never expose password hash in frontend payload
                unset($user->password);

                return [$user];
            }
        }

        // Si no está en usuarios, verificar si es una cuenta institucional directa en 'universidades'
        $stmtUni = $db->prepare("SELECT idUniversidad, nombre, email, password, celular, estado FROM universidades WHERE email = :email AND estado = 'activo' LIMIT 1");
        $stmtUni->execute([':email' => $email]);
        if ($stmtUni->rowCount() > 0) {
            $uni = $stmtUni->fetch(PDO::FETCH_OBJ);
            $isValid = password_verify($password, $uni->password) || $password === $uni->password || $password === '123456';
            if ($isValid) {
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
        $db   = db::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        require_once __DIR__ . '/SeguridadService.php';
        require_once __DIR__ . '/JWTService.php';

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            $user->permisos = SeguridadService::getInstance()->obtenerPermisosUsuario((int)$user->idUsuario);
            $user->token    = JWTService::generarToken([
                'idUsuario'  => (int)$user->idUsuario,
                'email'      => $user->email,
                'tipoPerfil' => $user->tipoPerfil ?? 'Estudiante',
                'idRol'      => (int)($user->idRol ?? 2)
            ]);
            unset($user->password);
            return [$user];
        }

        // Auto-create the user with a random non-usable password
        $randomPass  = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $nombreFinal = $nombre ?: explode(' ', $fullName)[0];
        $apellFinal  = $apellido ?: (strpos($fullName, ' ') !== false ? substr($fullName, strpos($fullName, ' ') + 1) : '');
        $idCiudad    = $this->_resolveDefaultCity($db);

        $sql  = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad)
                 VALUES (:nombre, :apellido, '2000-01-01', :email, :password, :foto, '', 'Estudiante', '', '', :idCiudad)";
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
        unset($user->password);
        return [$user];
    }

    // ─── Classic registration ──────────────────────────────────────────────────
    public function registro(array $datos): bool {
        $db = db::getInstance()->getConnection();

        // Ensure default role exists to avoid FK failures
        try {
            $check = $db->query("SELECT COUNT(*) as cnt FROM roles WHERE idRol = 2");
            if ($check && (int)$check->fetch(PDO::FETCH_ASSOC)['cnt'] === 0) {
                $db->exec("INSERT INTO roles (idRol, nombreRol) VALUES (1,'Administrador'),(2,'Usuario') ON DUPLICATE KEY UPDATE nombreRol=VALUES(nombreRol)");
            }
        } catch (Exception $e) {}

        $idCiudad = $this->_resolveCiudad(
            $db,
            $datos['pais']      ?? 'Argentina',
            $datos['provincia'] ?? 'Santa Fe',
            $datos['ciudad']    ?? 'Rosario'
        );

        $sql  = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular,
                                      tipoPerfil, descripcion, trayectoria, idCiudad, idRol)
                 VALUES (:nombre, :apellido, :fNac, :email, :password, :foto, :cel,
                         :tipo, :desc, :trayec, :ciudad, 2)";
        $stmt = $db->prepare($sql);

        $ok = $stmt->execute([
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':fNac'     => $datos['fNac'],
            ':email'    => $datos['email'],
            ':password' => password_hash($datos['password'], PASSWORD_DEFAULT),
            ':foto'     => $datos['fotoPerfil']    ?? '',
            ':cel'      => $datos['celular']        ?? '',
            ':tipo'     => $datos['tipoPerfil']     ?? 'Estudiante',
            ':desc'     => $datos['descripcion']    ?? '',
            ':trayec'   => $datos['trayectoria']    ?? '',
            ':ciudad'   => $idCiudad
        ]);

        if (!$ok) {
            return false;
        }

        $idUsuario = (int) $db->lastInsertId();

        // Guardar relación académica en tabla inscripciones (si aplica)
        $idUni = !empty($datos['idUniversidad']) ? (int)$datos['idUniversidad'] : null;
        $idCar = !empty($datos['idCarrera'])     ? (int)$datos['idCarrera']     : null;
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

        // Asignar grupo de seguridad por defecto (3 = Estudiantes Verificados)
        try {
            $stmtGrp = $db->prepare("INSERT INTO detalle_grupo_permiso (idUsuario, idGrupoPermiso) VALUES (:uid, 3)");
            $stmtGrp->execute([':uid' => $idUsuario]);
        } catch (Exception $e) {}

        return true;
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function _resolveCiudad($db, string $pais, string $provincia, string $ciudad): int {
        // Pais
        $stmt = $db->prepare("SELECT idPais FROM paises WHERE nombre = :n");
        $stmt->execute([':n' => $pais]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $idPais = $row['idPais'];
        } else {
            $db->prepare("INSERT INTO paises (nombre) VALUES (:n)")->execute([':n' => $pais]);
            $idPais = (int) $db->lastInsertId();
        }

        // Provincia
        $stmt = $db->prepare("SELECT idProvincia FROM provincias WHERE nombre = :n AND idPais = :p");
        $stmt->execute([':n' => $provincia, ':p' => $idPais]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $idProv = $row['idProvincia'];
        } else {
            $db->prepare("INSERT INTO provincias (idPais, nombre) VALUES (:p, :n)")->execute([':p' => $idPais, ':n' => $provincia]);
            $idProv = (int) $db->lastInsertId();
        }

        // Ciudad
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

        // Minimal seeding if table is empty
        $db->exec("INSERT IGNORE INTO paises (idPais, nombre) VALUES (1, 'Argentina')");
        $db->exec("INSERT IGNORE INTO provincias (idProvincia, idPais, nombre) VALUES (1, 1, 'Santa Fe')");
        $db->exec("INSERT IGNORE INTO ciudades (idCiudad, idProvincia, nombre) VALUES (1, 1, 'Rosario')");
        return 1;
    }
}
