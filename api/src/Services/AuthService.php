<?php
class AuthService {
    private static $instance = null;
    
    // Constructor privado para el Singleton
    private function __construct() {}
    
    // Prevenir clonación
    private function __clone() {}
    
    // Obtener la instancia
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Método de autenticación
    public function login($email, $password) {
        $db = db::getInstance()->getConnection();
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_OBJ);
            if (password_verify($password, $user->password)) {
                return [$user];
            }
        }
        return false;
    }

    public function registro(array $datos): bool {
        $db = db::getInstance()->getConnection();
        
        $paisNombre = $datos['pais'] ?? 'Argentina';
        $provinciaNombre = $datos['provincia'] ?? 'Santa Fe';
        $ciudadNombre = $datos['ciudad'] ?? 'Rosario';
        $idCiudad = 1;

        // 1. Resolve Pais
        $stmtPais = $db->prepare("SELECT idPais FROM paises WHERE nombre = :nombre");
        $stmtPais->execute([':nombre' => $paisNombre]);
        $rowPais = $stmtPais->fetch(PDO::FETCH_ASSOC);
        if ($rowPais) {
            $idPais = $rowPais['idPais'];
        } else {
            $stmtInsertPais = $db->prepare("INSERT INTO paises (nombre) VALUES (:nombre)");
            $stmtInsertPais->execute([':nombre' => $paisNombre]);
            $idPais = $db->lastInsertId();
        }
        
        // 2. Resolve Provincia
        $stmtProv = $db->prepare("SELECT idProvincia FROM provincias WHERE nombre = :nombre AND idPais = :idPais");
        $stmtProv->execute([':nombre' => $provinciaNombre, ':idPais' => $idPais]);
        $rowProv = $stmtProv->fetch(PDO::FETCH_ASSOC);
        if ($rowProv) {
            $idProvincia = $rowProv['idProvincia'];
        } else {
            $stmtInsertProv = $db->prepare("INSERT INTO provincias (idPais, nombre) VALUES (:idPais, :nombre)");
            $stmtInsertProv->execute([':idPais' => $idPais, ':nombre' => $provinciaNombre]);
            $idProvincia = $db->lastInsertId();
        }
        
        // 3. Resolve Ciudad
        $stmtCiu = $db->prepare("SELECT idCiudad FROM ciudades WHERE nombre = :nombre AND idProvincia = :idProv");
        $stmtCiu->execute([':nombre' => $ciudadNombre, ':idProv' => $idProvincia]);
        $rowCiu = $stmtCiu->fetch(PDO::FETCH_ASSOC);
        if ($rowCiu) {
            $idCiudad = $rowCiu['idCiudad'];
        } else {
            $stmtInsertCiu = $db->prepare("INSERT INTO ciudades (idProvincia, nombre) VALUES (:idProv, :nombre)");
            $stmtInsertCiu->execute([':idProv' => $idProvincia, ':nombre' => $ciudadNombre]);
            $idCiudad = $db->lastInsertId();
        }

        // 4. Insert Usuario (Ideally this goes to UsuarioRepository, but keeping it here for simplicity of AuthService)
        $sql = "INSERT INTO usuarios (nombre, apellido, fNac, email, password, fotoPerfil, celular, tipoPerfil, descripcion, trayectoria, idCiudad) VALUES 
                (:nombre, :apellido, :fNac, :email, :password, :fotoPerfil, :celular, :tipoPerfil, :descripcion, :trayectoria, :idCiudad)";
        
        $resultado = $db->prepare($sql);

        return $resultado->execute([
            ':nombre' => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':fNac' => $datos['fNac'],
            ':email' => $datos['email'],
            ':password' => password_hash($datos['password'], PASSWORD_DEFAULT),
            ':fotoPerfil' => $datos['fotoPerfil'],
            ':celular' => $datos['celular'],
            ':tipoPerfil' => $datos['tipoPerfil'],
            ':descripcion' => $datos['descripcion'],
            ':trayectoria' => $datos['trayectoria'],
            ':idCiudad' => $idCiudad
        ]);
    }
}
