<?php
require_once __DIR__ . '/../Models/Usuario.php';

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = db::getInstance()->getConnection();
    }

    public function guardar(Usuario $usuario): int {
        $sql = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad) 
                VALUES (:nom, :ape, :fnac, :email, :pass, :foto, :cel, :tipo, :desc, :trayec, :ciudad)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            ':nom'    => $usuario->getNombre(),
            ':ape'    => $usuario->getApellido(),
            ':fnac'   => $usuario->getFnac(),
            ':email'  => $usuario->getEmail(),
            ':pass'   => password_hash($usuario->getPassword(), PASSWORD_DEFAULT),
            ':foto'   => $usuario->getFotoPerfil(),
            ':cel'    => $usuario->getCelular(),
            ':tipo'   => $usuario->getTipoPerfil(),
            ':desc'   => $usuario->getDescripcion(),
            ':trayec' => $usuario->getTrayectoria(),
            ':ciudad' => $usuario->getIdCiudad()
        ]);
        
        return (int) $this->db->lastInsertId();
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
                idCiudad = :ciudad";

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
            ':ciudad' => $usuario->getIdCiudad()
        ];

        if (!empty($usuario->getPassword())) {
            $params[':pass'] = password_hash($usuario->getPassword(), PASSWORD_DEFAULT);
        }
        
        return $stmt->execute($params);
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
        $sql = "DELETE FROM usuarios WHERE idUsuario = :id";
        $stmt = $this->db->prepare($sql);
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
            $fila['trayectoria']
        );
    }
}