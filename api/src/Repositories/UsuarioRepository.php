<?php
require_once __DIR__ . '/../Models/Usuario.php';

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function guardar(Usuario $usuario): int {
        $idCiudad = $usuario->getIdCiudad() ?: 1;

        $sql = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad, idUniversidad, idCarrera, idRol) 
                VALUES (:nom, :ape, :fnac, :email, :pass, :foto, :cel, :tipo, :desc, :trayec, :ciudad, :uni, :car, 2)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            ':nom'    => $usuario->getNombre(),
            ':ape'    => $usuario->getApellido(),
            ':fnac'   => $usuario->getFnac(),
            ':email'  => $usuario->getEmail(),
            ':pass'   => password_hash($usuario->getPassword(), PASSWORD_DEFAULT),
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

        if (!empty($usuario->getPassword())) {
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

        if (!empty($usuario->getPassword())) {
            $params[':pass'] = password_hash($usuario->getPassword(), PASSWORD_DEFAULT);
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

    public function buscarPorId(int $idUsuario): ?Usuario {
        $sql = "SELECT * FROM usuarios WHERE idUsuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return $this->hidratar($fila);
    }

    public function obtenerTodos(): array {
        $sql = "SELECT * FROM usuarios";
        $resultado = $this->db->query($sql);
        $filas = $resultado->fetchAll(PDO::FETCH_ASSOC);

        $listaUsuarios = [];
        foreach ($filas as $fila) {
            $listaUsuarios[] = $this->hidratar($fila);
        }

        return $listaUsuarios;
    }

    public function eliminar(int $idUsuario): bool {
        $sql  = "DELETE FROM usuarios WHERE idUsuario = :id";
        $stmt = $this->db->prepare($sql);
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
                       (SELECT COUNT(*) FROM publicaciones p WHERE p.idUsuario = u.idUsuario) as totalPublicaciones
                FROM usuarios u
                LEFT JOIN universidades uni ON u.idUniversidad = uni.idUniversidad
                LEFT JOIN carreras car ON u.idCarrera = car.idCarrera
                LEFT JOIN ciudades ciu ON u.idCiudad = ciu.idCiudad
                WHERE u.idUsuario = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
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