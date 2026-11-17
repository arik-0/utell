<?php
require_once __DIR__ . '/../Repositories/UsuarioRepository.php';
require_once __DIR__ . '/../Repositories/AuditoriaRepository.php';
require_once __DIR__ . '/SeguridadService.php';
require_once __DIR__ . '/JWTService.php';

class AuthService {
    private static $instance = null;
    private UsuarioRepository $usuarioRepo;

    private function __construct() {
        $this->usuarioRepo = new UsuarioRepository();
    }

    private function __clone() {}

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

    private function enriquecerSesionUsuario(object $user): object {
        $seg      = SeguridadService::getInstance();
        $idUsr    = (int)$user->idUsuario;
        $permisos = $seg->obtenerPermisosUsuario($idUsr);

        // Recargar por si asegurarTablasRbac() actualizó rol/perfil inicial en el primer login
        $actualizado = $this->usuarioRepo->buscarPorIdRaw($idUsr);
        if ($actualizado) {
            $user = $actualizado;
        }

        $esDueno        = $seg->esUsuarioDueno($idUsr);
        $user->permisos = $permisos;
        $user->grupos   = $seg->obtenerGruposUsuario($idUsr);
        $user->esDueno  = $esDueno;
        $user->token    = JWTService::generarToken([
            'idUsuario'  => $idUsr,
            'email'      => $user->email,
            'tipoPerfil' => $user->tipoPerfil ?? 'Estudiante',
            'idRol'      => (int)($user->idRol ?? 2),
            'nombreRol'  => $esDueno ? 'Dueño' : ($user->tipoPerfil ?? 'Estudiante'),
        ]);
        unset($user->password);
        return $user;
    }

    // ─── Classic email/password login ─────────────────────────────────────────
    public function login(string $email, string $password) {
        $auditoria = new AuditoriaRepository();

        // Asegurar cuentas institucionales de evaluación solo si aún no existen en la base
        $emailNorm = strtolower(trim($email));
        if (in_array($emailNorm, ['admin@uai.edu.ar', 'rectorado@uai.edu.ar'], true) && !$this->usuarioRepo->existeEmail($emailNorm)) {
            $this->usuarioRepo->asegurarCuentasInstitucionalesEvaluacion();
        }

        $user = $this->usuarioRepo->buscarPorEmailRaw($email);

        if ($user) {
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
                    $this->usuarioRepo->actualizarPasswordHash((int)$user->idUsuario, $newHash);
                }

                $user = $this->enriquecerSesionUsuario($user);
                $auditoria->registrar((int)$user->idUsuario, 'LOGIN', "Inicio de sesión exitoso ({$user->email})", 'usuarios');

                return [$user];
            }
        }

        // Si no está en usuarios, verificar si es una cuenta institucional directa en 'universidades'
        $uni = $this->usuarioRepo->buscarUniversidadPorEmail($email);
        if ($uni) {
            $requiereRehash = false;
            if ($this->verificarCredencial($password, (string)$uni->password, $requiereRehash)) {
                if ($requiereRehash) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $this->usuarioRepo->actualizarPasswordHashUniversidad((int)$uni->idUniversidad, $newHash);
                }

                $uniUser = (object)[
                    'idUsuario'  => (int)$uni->idUniversidad,
                    'nombre'     => $uni->nombre,
                    'apellido'   => '(Institución)',
                    'email'      => $uni->email,
                    'tipoPerfil' => 'Universidad',
                    'nombreRol'  => 'Institución',
                    'idRol'      => 6,
                    'fotoPerfil' => 'https://images.unsplash.com/photo-1562774053-701939374585?w=200&auto=format&fit=crop&q=80',
                    'token'      => JWTService::generarToken([
                        'idUsuario'  => (int)$uni->idUniversidad,
                        'email'      => $uni->email,
                        'tipoPerfil' => 'Universidad',
                        'idRol'      => 6,
                    ]),
                    'permisos'   => ['POST_CREATE', 'POST_REPLY', 'SEND_DIRECT_MESSAGE', 'VIEW_INSTITUTION_METRICS', 'OFFICIAL_BADGE'],
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
        $auditoria = new AuditoriaRepository();
        $user      = $this->usuarioRepo->buscarPorEmailRaw($email);

        if ($user) {
            if (isset($user->estadoCuenta) && in_array($user->estadoCuenta, ['bloqueado', 'inactivo'], true)) {
                throw new DomainException('Esta cuenta no se encuentra habilitada para iniciar sesión.');
            }
            $user = $this->enriquecerSesionUsuario($user);
            $auditoria->registrar((int)$user->idUsuario, 'LOGIN_GOOGLE', "Inicio de sesión OAuth ({$user->email})", 'usuarios');
            return [$user];
        }

        $randomPass  = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $nombreFinal = $nombre ?: explode(' ', $fullName)[0];
        $apellFinal  = $apellido ?: (strpos($fullName, ' ') !== false ? substr($fullName, strpos($fullName, ' ') + 1) : '');
        $idCiudad    = $this->usuarioRepo->obtenerCiudadPredeterminada();

        $newId = $this->usuarioRepo->registrarUsuarioConInscripcionYGrupo([
            'nombre'        => $nombreFinal,
            'apellido'      => $apellFinal,
            'fNac'          => '2000-01-01',
            'email'         => $email,
            'passwordHash'  => $randomPass,
            'fotoPerfil'    => $fotoPerfil,
            'celular'       => '',
            'tipoPerfil'    => 'Estudiante',
            'descripcion'   => '',
            'trayectoria'   => '',
            'idCiudad'      => $idCiudad,
            'idUniversidad' => null,
            'idCarrera'     => null,
            'idRol'         => 2,
        ], 3);

        $user = $this->usuarioRepo->buscarPorIdRaw($newId);
        $user->permisos = SeguridadService::getInstance()->obtenerPermisosUsuario((int)$user->idUsuario);
        $user->token    = JWTService::generarToken([
            'idUsuario'  => (int)$user->idUsuario,
            'email'      => $user->email,
            'tipoPerfil' => $user->tipoPerfil ?? 'Estudiante',
            'idRol'      => (int)($user->idRol ?? 2),
        ]);
        $auditoria->registrar($newId, 'REGISTRO_GOOGLE', "Alta OAuth ({$email})", 'usuarios');
        unset($user->password);
        return [$user];
    }

    // ─── Classic registration ──────────────────────────────────────────────────
    public function registro(array $datos): bool {
        $email = trim((string)($datos['email'] ?? ''));
        if ($this->usuarioRepo->existeEmail($email)) {
            throw new DomainException('Ya existe una cuenta registrada con ese correo electrónico.');
        }

        $idCiudad = $this->usuarioRepo->resolverCiudadPorNombre(
            $datos['pais']      ?? 'Argentina',
            $datos['provincia'] ?? 'Santa Fe',
            $datos['ciudad']    ?? 'Rosario'
        );

        $idUni = !empty($datos['idUniversidad']) ? (int)$datos['idUniversidad'] : null;
        $idCar = !empty($datos['idCarrera'])     ? (int)$datos['idCarrera']     : null;

        $tipoPerfil = trim((string)($datos['tipoPerfil'] ?? 'Estudiante'));
        $perfilesPublicos = ['Aspirante', 'Postulante', 'Estudiante', 'Graduado', 'Docente', 'Universidad', 'Institución', 'Empresa'];
        if (empty($datos['esAdminCreador']) && !in_array($tipoPerfil, $perfilesPublicos, true)) {
            $tipoPerfil = 'Estudiante';
        }

        $mapaRol = [
            'Aspirante'     => 2,
            'Postulante'    => 2,
            'Estudiante'    => 2,
            'Moderador'     => 3,
            'Graduado'      => 4,
            'Docente'       => 5,
            'Universidad'   => 6,
            'Institución'   => 6,
            'Empresa'       => 6,
            'Administrador' => 1,
            'Dueño'         => 7,
        ];
        if (empty($datos['esAdminCreador'])) {
            unset($mapaRol['Administrador'], $mapaRol['Moderador'], $mapaRol['Dueño']);
        } elseif (empty($datos['esDuenoCreador'])) {
            unset($mapaRol['Dueño']);
        }
        $idRol   = $mapaRol[$tipoPerfil] ?? 2;
        $mapaGrupo = [
            1 => 1,
            3 => 2,
            5 => 4,
            6 => 4,
            7 => 5,
        ];
        $idGrupo = $mapaGrupo[$idRol] ?? 3;

        $idUsuario = $this->usuarioRepo->registrarUsuarioConInscripcionYGrupo([
            'nombre'        => trim((string)$datos['nombre']),
            'apellido'      => trim((string)($datos['apellido'] ?? '')),
            'fNac'          => !empty($datos['fNac']) ? $datos['fNac'] : '2000-01-01',
            'email'         => $email,
            'passwordHash'  => password_hash($datos['password'], PASSWORD_DEFAULT),
            'fotoPerfil'    => $datos['fotoPerfil']  ?? '',
            'celular'       => $datos['celular']     ?? '',
            'tipoPerfil'    => $tipoPerfil,
            'descripcion'   => $datos['descripcion'] ?? '',
            'trayectoria'   => $datos['trayectoria'] ?? '',
            'idCiudad'      => $idCiudad,
            'idUniversidad' => $idUni,
            'idCarrera'     => $idCar,
            'idRol'         => $idRol,
        ], $idGrupo);

        if ($idUsuario <= 0) {
            return false;
        }

        (new AuditoriaRepository())->registrar($idUsuario, 'REGISTRO', "Alta de usuario ({$email})", 'usuarios');
        return true;
    }
}
