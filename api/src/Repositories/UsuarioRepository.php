<?php
require_once __DIR__ . '/../Models/Usuario.php';

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function guardar(Usuario $usuario): int {
        $idCiudad = $usuario->getIdCiudad() ?: 1;
        $rawPass  = $usuario->getPassword();
        $passInfo = password_get_info($rawPass);
        $passHash = !empty($passInfo['algo']) ? $rawPass : password_hash($rawPass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad, idUniversidad, idCarrera, idRol) 
                VALUES (:nom, :ape, :fnac, :email, :pass, :foto, :cel, :tipo, :desc, :trayec, :ciudad, :uni, :car, 2)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nom'    => $usuario->getNombre(),
            ':ape'    => $usuario->getApellido(),
            ':fnac'   => $usuario->getFnac(),
            ':email'  => $usuario->getEmail(),
            ':pass'   => $passHash,
            ':foto'   => $usuario->getFotoPerfil() ?: '',
            ':cel'    => $usuario->getCelular() ?: '',
            ':tipo'   => $usuario->getTipoPerfil() ?: 'Estudiante',
            ':desc'   => $usuario->getDescripcion() ?: '',
            ':trayec' => $usuario->getTrayectoria() ?: '',
            ':ciudad' => $idCiudad,
            ':uni'    => $usuario->getIdUniversidad(),
            ':car'    => $usuario->getIdCarrera()
        ]);

        $idUsuario = (int) $this->db->lastInsertId();

        if ($usuario->getIdUniversidad() || $usuario->getIdCarrera()) {
            try {
                $stmtIns = $this->db->prepare("INSERT INTO inscripciones (idUsuario, idUniversidad, idCarrera, anioIngreso, estado) VALUES (:uid, :uni, :car, :anio, 'activo')");
                $stmtIns->execute([
                    ':uid'  => $idUsuario,
                    ':uni'  => $usuario->getIdUniversidad(),
                    ':car'  => $usuario->getIdCarrera(),
                    ':anio' => (int)date('Y')
                ]);
            } catch (Exception $e) {}
        }

        return $idUsuario;
    }

    public function actualizar(Usuario $usuario): bool {
        $rawPass = $usuario->getPassword();
        $cambiarPassword = !empty($rawPass);

        $sql = "UPDATE usuarios SET
                nombre = :nom,
                apellido = :ape,
                fNac = :fnac,
                email = :email,
                fotoPerfil = :foto,
                celular = :cel,
                tipoPerfil = :tipo,
                descripcion = :desc,
                trayectoria = :trayec,
                idCiudad = :ciudad,
                idUniversidad = :uni,
                idCarrera = :car";

        if ($cambiarPassword) {
            $sql .= ", password = :pass";
        }

        $sql .= " WHERE idUsuario = :id";

        $stmt = $this->db->prepare($sql);
        $params = [
            ':id'     => $usuario->getIdUsuario(),
            ':nom'    => $usuario->getNombre(),
            ':ape'    => $usuario->getApellido(),
            ':fnac'   => $usuario->getFnac(),
            ':email'  => $usuario->getEmail(),
            ':foto'   => $usuario->getFotoPerfil(),
            ':cel'    => $usuario->getCelular(),
            ':tipo'   => $usuario->getTipoPerfil(),
            ':desc'   => $usuario->getDescripcion(),
            ':trayec' => $usuario->getTrayectoria(),
            ':ciudad' => $usuario->getIdCiudad(),
            ':uni'    => $usuario->getIdUniversidad(),
            ':car'    => $usuario->getIdCarrera()
        ];

        if ($cambiarPassword) {
            $passInfo = password_get_info($rawPass);
            $params[':pass'] = !empty($passInfo['algo']) ? $rawPass : password_hash($rawPass, PASSWORD_DEFAULT);
        }

        $res = $stmt->execute($params);

        if ($usuario->getIdUniversidad() || $usuario->getIdCarrera()) {
            try {
                $stmtIns = $this->db->prepare("INSERT INTO inscripciones (idUsuario, idUniversidad, idCarrera, anioIngreso, estado) 
                                               VALUES (:uid, :uni, :car, :anio, 'activo')
                                               ON DUPLICATE KEY UPDATE idUniversidad = VALUES(idUniversidad), idCarrera = VALUES(idCarrera)");
                $stmtIns->execute([
                    ':uid'  => $usuario->getIdUsuario(),
                    ':uni'  => $usuario->getIdUniversidad(),
                    ':car'  => $usuario->getIdCarrera(),
                    ':anio' => (int)date('Y')
                ]);
            } catch (Exception $e) {}
        }

        return $res;
    }

    public function actualizarFotoPerfil(int $idUsuario, string $fotoUrl): bool {
        $stmt = $this->db->prepare("UPDATE usuarios SET fotoPerfil = :foto WHERE idUsuario = :id");
        return $stmt->execute([':foto' => $fotoUrl, ':id' => $idUsuario]);
    }

    public function buscarPorId(int $idUsuario): ?Usuario {
        $sql = "SELECT * FROM usuarios WHERE idUsuario = :id AND COALESCE(estadoCuenta, 'activo') <> 'inactivo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            return null;
        }

        return $this->hidratar($fila);
    }

    public function obtenerTodos(): array {
        $sql = "SELECT * FROM usuarios WHERE COALESCE(estadoCuenta, 'activo') <> 'inactivo'";
        $resultado = $this->db->query($sql);
        $filas = $resultado->fetchAll(PDO::FETCH_ASSOC);

        $listaUsuarios = [];
        foreach ($filas as $fila) {
            $listaUsuarios[] = $this->hidratar($fila);
        }

        return $listaUsuarios;
    }

    /**
     * Baja lógica de la cuenta (CU2 / Sección 10 — preservación de integridad histórica).
     */
    public function eliminar(int $idUsuario): bool {
        $stmt = $this->db->prepare("UPDATE usuarios SET estadoCuenta = 'inactivo' WHERE idUsuario = :id");
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->rowCount() > 0;
    }

    public function bloquearCuenta(int $idUsuario): bool {
        $stmt = $this->db->prepare("UPDATE usuarios SET estadoCuenta = 'bloqueado' WHERE idUsuario = :id");
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerPerfilCompleto(int $idUsuario): ?array {
        $sql = "SELECT u.idUsuario, u.nombre, u.apellido, u.fNac, u.email, u.fotoPerfil, u.celular,
                       u.tipoPerfil, u.descripcion, u.trayectoria, u.idCiudad, u.idRol,
                       u.idUniversidad, u.idCarrera,
                       uni.nombre as nombreUniversidad,
                       car.nombre as nombreCarrera,
                       ciu.nombre as nombreCiudad,
                       (SELECT COUNT(*) FROM amigos a WHERE (a.idSolicitante = u.idUsuario OR a.idReceptor = u.idUsuario) AND a.estado = 'aceptada') as totalAmigos,
                       (SELECT COUNT(*) FROM publicaciones p WHERE p.idUsuario = u.idUsuario AND COALESCE(p.estado, 'activo') = 'activo') as totalPublicaciones
                FROM usuarios u
                LEFT JOIN universidades uni ON u.idUniversidad = uni.idUniversidad
                LEFT JOIN carreras car ON u.idCarrera = car.idCarrera
                LEFT JOIN ciudades ciu ON u.idCiudad = ciu.idCiudad
                WHERE u.idUsuario = :id AND COALESCE(u.estadoCuenta, 'activo') <> 'inactivo'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function asegurarRolesBase(): void {
        try {
            $cnt = (int)$this->db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
            if ($cnt === 0) {
                $this->db->exec("
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

    public function asegurarCuentasInstitucionalesEvaluacion(): void {
        try {
            $this->asegurarRolesBase();
            $hash = password_hash('123456', PASSWORD_DEFAULT);
            $this->db->prepare("INSERT IGNORE INTO usuarios (idUsuario, nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad, idUniversidad, idCarrera, idRol)
                                VALUES (99, 'Administrador', 'UAI', '1990-01-01', 'admin@uai.edu.ar', :p, 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200', '+54 11 4342-7788', 'Administrador', 'Administrador General U-Tell UAI', 'Gestión institucional', 5, 8, 2, 1)")
                     ->execute([':p' => $hash]);

            $this->db->prepare("INSERT IGNORE INTO usuarios (idUsuario, nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad, idUniversidad, idCarrera, idRol)
                                VALUES (98, 'Rectorado', 'UAI', '1980-01-01', 'rectorado@uai.edu.ar', :p, 'https://images.unsplash.com/photo-1562774053-701939374585?w=200', '+54 11 4342-7788', 'Universidad', 'Autoridad Institucional UAI', 'Rectorado UAI', 5, 8, 2, 6)")
                     ->execute([':p' => $hash]);
        } catch (Exception $e) {}
    }

    public function buscarPorEmailRaw(string $email): ?object {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function existeEmail(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function actualizarPasswordHash(int $idUsuario, string $nuevoHash): void {
        $this->db->prepare("UPDATE usuarios SET password = :p WHERE idUsuario = :id")
                 ->execute([':p' => $nuevoHash, ':id' => $idUsuario]);
    }

    public function buscarUniversidadPorEmail(string $email): ?object {
        $stmt = $this->db->prepare("SELECT idUniversidad, nombre, email, password, celular, estado FROM universidades WHERE email = :email AND estado = 'activo' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function actualizarPasswordHashUniversidad(int $idUniversidad, string $nuevoHash): void {
        $this->db->prepare("UPDATE universidades SET password = :p WHERE idUniversidad = :id")
                 ->execute([':p' => $nuevoHash, ':id' => $idUniversidad]);
    }

    public function resolverCiudadPorNombre(string $pais, string $provincia, string $ciudad): int {
        $stmt = $this->db->prepare("SELECT idPais FROM paises WHERE nombre = :n");
        $stmt->execute([':n' => $pais]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $idPais = (int)$row['idPais'];
        } else {
            $this->db->prepare("INSERT INTO paises (nombre) VALUES (:n)")->execute([':n' => $pais]);
            $idPais = (int)$this->db->lastInsertId();
        }

        $stmt = $this->db->prepare("SELECT idProvincia FROM provincias WHERE nombre = :n AND idPais = :p");
        $stmt->execute([':n' => $provincia, ':p' => $idPais]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $idProv = (int)$row['idProvincia'];
        } else {
            $this->db->prepare("INSERT INTO provincias (idPais, nombre) VALUES (:p, :n)")->execute([':p' => $idPais, ':n' => $provincia]);
            $idProv = (int)$this->db->lastInsertId();
        }

        $stmt = $this->db->prepare("SELECT idCiudad FROM ciudades WHERE nombre = :n AND idProvincia = :p");
        $stmt->execute([':n' => $ciudad, ':p' => $idProv]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['idCiudad'];
        }

        $this->db->prepare("INSERT INTO ciudades (idProvincia, nombre) VALUES (:p, :n)")->execute([':p' => $idProv, ':n' => $ciudad]);
        return (int)$this->db->lastInsertId();
    }

    public function obtenerCiudadPredeterminada(): int {
        $row = $this->db->query("SELECT idCiudad FROM ciudades LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['idCiudad'];
        }

        $this->db->exec("INSERT IGNORE INTO paises (idPais, nombre) VALUES (1, 'Argentina')");
        $this->db->exec("INSERT IGNORE INTO provincias (idProvincia, idPais, nombre) VALUES (1, 1, 'Santa Fe')");
        $this->db->exec("INSERT IGNORE INTO ciudades (idCiudad, idProvincia, nombre) VALUES (1, 1, 'Rosario')");
        return 1;
    }

    public function registrarUsuarioConInscripcionYGrupo(array $campos, int $idGrupoPermiso): int {
        $this->asegurarRolesBase();

        $sql = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular,
                                      tipoPerfil, descripcion, trayectoria, idCiudad, idUniversidad, idCarrera, idRol)
                VALUES (:nombre, :apellido, :fNac, :email, :password, :foto, :cel,
                        :tipo, :desc, :trayec, :ciudad, :uni, :car, :rol)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':nombre'   => $campos['nombre'],
            ':apellido' => $campos['apellido'],
            ':fNac'     => $campos['fNac'],
            ':email'    => $campos['email'],
            ':password' => $campos['passwordHash'],
            ':foto'     => $campos['fotoPerfil'],
            ':cel'      => $campos['celular'],
            ':tipo'     => $campos['tipoPerfil'],
            ':desc'     => $campos['descripcion'],
            ':trayec'   => $campos['trayectoria'],
            ':ciudad'   => $campos['idCiudad'],
            ':uni'      => $campos['idUniversidad'],
            ':car'      => $campos['idCarrera'],
            ':rol'      => $campos['idRol'],
        ]);

        if (!$ok) {
            return 0;
        }

        $idUsuario = (int)$this->db->lastInsertId();

        if (!empty($campos['idUniversidad']) || !empty($campos['idCarrera'])) {
            try {
                $stmtIns = $this->db->prepare("INSERT INTO inscripciones (idUsuario, idUniversidad, idCarrera, anioIngreso, estado) VALUES (:uid, :uni, :car, :anio, 'activo')");
                $stmtIns->execute([
                    ':uid'  => $idUsuario,
                    ':uni'  => $campos['idUniversidad'],
                    ':car'  => $campos['idCarrera'],
                    ':anio' => (int)date('Y'),
                ]);
            } catch (Exception $e) {}
        }

        try {
            $stmtGrp = $this->db->prepare("INSERT INTO detalle_grupo_permiso (idUsuario, idGrupoPermiso) VALUES (:uid, :grp)");
            $stmtGrp->execute([':uid' => $idUsuario, ':grp' => $idGrupoPermiso]);
        } catch (Exception $e) {}

        return $idUsuario;
    }

    public function buscarPorIdRaw(int $idUsuario): ?object {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE idUsuario = :id LIMIT 1");
        $stmt->execute([':id' => $idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function activarCuenta(int $idUsuario): bool {
        $stmt = $this->db->prepare("UPDATE usuarios SET estadoCuenta = 'activo' WHERE idUsuario = :id");
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->rowCount() > 0;
    }

    private function hidratar(array $fila): Usuario {
        return new Usuario(
            $fila['nombre'],
            $fila['apellido'],
            $fila['email'],
            $fila['password'],
            $fila['fNac'],
            $fila['tipoPerfil'],
            (int)$fila['idCiudad'],
            (int)$fila['idUsuario'],
            $fila['fotoPerfil'],
            $fila['celular'],
            $fila['descripcion'],
            $fila['trayectoria'],
            isset($fila['idUniversidad']) ? (int)$fila['idUniversidad'] : null,
            isset($fila['idCarrera']) ? (int)$fila['idCarrera'] : null
        );
    }
}
