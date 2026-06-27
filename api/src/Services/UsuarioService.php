<?php
require_once __DIR__ . '/../Repositories/UsuarioRepository.php';

class UsuarioService {
    private static $instance = null;
    private UsuarioRepository $usuarioRepository;

    private function __construct() {
        $this->usuarioRepository = new UsuarioRepository();
    }

    private function __clone() {}

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerTodosLosUsuarios(): array {
        $usuarios = $this->usuarioRepository->obtenerTodos();
        $resultado = [];
        foreach ($usuarios as $usuario) {
            $resultado[] = $usuario->toArray();
        }
        return $resultado;
    }

    public function obtenerUsuarioPorId(int $id) {
        $usuario = $this->usuarioRepository->buscarPorId($id);
        if ($usuario) {
            return $usuario->toArray();
        }
        return null;
    }

    public function eliminarUsuario(int $id): bool {
        return $this->usuarioRepository->eliminar($id);
    }

    public function actualizarUsuario(int $id, array $datos): bool {
        $usuarioActual = $this->usuarioRepository->buscarPorId($id);
        if (!$usuarioActual) {
            return false;
        }

        // Crear objeto actualizado
        $usuarioModificado = new Usuario(
            $datos['nombre'] ?? $usuarioActual->getNombre(),
            $datos['apellido'] ?? $usuarioActual->getApellido(),
            $datos['email'] ?? $usuarioActual->getEmail(),
            $datos['password'] ?? $usuarioActual->getPassword(),
            $datos['fNac'] ?? $usuarioActual->getFnac(),
            $datos['tipoPerfil'] ?? $usuarioActual->getTipoPerfil(),
            $datos['idCiudad'] ?? $usuarioActual->getIdCiudad(),
            $id,
            $datos['fotoPerfil'] ?? $usuarioActual->getFotoPerfil(),
            $datos['celular'] ?? $usuarioActual->getCelular(),
            $datos['descripcion'] ?? $usuarioActual->getDescripcion(),
            $datos['trayectoria'] ?? $usuarioActual->getTrayectoria()
        );

        return $this->usuarioRepository->actualizar($usuarioModificado);
    }
}
