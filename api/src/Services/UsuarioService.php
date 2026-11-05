<?php
require_once __DIR__ . '/../Repositories/UsuarioRepository.php';
require_once __DIR__ . '/../Repositories/AuditoriaRepository.php';

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

    public function obtenerPerfilCompleto(int $id): ?array {
        return $this->usuarioRepository->obtenerPerfilCompleto($id);
    }

    public function eliminarUsuario(int $id, ?int $idActor = null): bool {
        $ok = $this->usuarioRepository->eliminar($id);
        if ($ok) {
            (new AuditoriaRepository())->registrar($idActor ?? $id, 'BAJA_USUARIO', "Baja lógica de usuario ID {$id}", 'usuarios');
        }
        return $ok;
    }

    public function actualizarFotoPerfil(int $id, string $fotoUrl): bool {
        return $this->usuarioRepository->actualizarFotoPerfil($id, $fotoUrl);
    }

    public function actualizarUsuario(int $id, array $datos): bool {
        $usuarioActual = $this->usuarioRepository->buscarPorId($id);
        if (!$usuarioActual) {
            return false;
        }

        // Solo transmitir nueva contraseña si el cliente explícitamente solicitó cambiarla
        // (evita re-hashear el hash bcrypt existente al editar foto o datos básicos).
        $nuevaPassword = !empty($datos['password']) ? (string)$datos['password'] : '';

        $usuarioModificado = new Usuario(
            $datos['nombre'] ?? $usuarioActual->getNombre(),
            $datos['apellido'] ?? $usuarioActual->getApellido(),
            $datos['email'] ?? $usuarioActual->getEmail(),
            $nuevaPassword,
            $datos['fNac'] ?? $usuarioActual->getFnac(),
            $datos['tipoPerfil'] ?? $usuarioActual->getTipoPerfil(),
            isset($datos['idCiudad']) && $datos['idCiudad'] !== null && $datos['idCiudad'] !== ''
                ? (int)$datos['idCiudad']
                : $usuarioActual->getIdCiudad(),
            $id,
            $datos['fotoPerfil'] ?? $usuarioActual->getFotoPerfil(),
            $datos['celular'] ?? $usuarioActual->getCelular(),
            $datos['descripcion'] ?? $usuarioActual->getDescripcion(),
            $datos['trayectoria'] ?? $usuarioActual->getTrayectoria(),
            array_key_exists('idUniversidad', $datos) && $datos['idUniversidad'] !== null
                ? ($datos['idUniversidad'] !== '' ? (int)$datos['idUniversidad'] : null)
                : $usuarioActual->getIdUniversidad(),
            array_key_exists('idCarrera', $datos) && $datos['idCarrera'] !== null
                ? ($datos['idCarrera'] !== '' ? (int)$datos['idCarrera'] : null)
                : $usuarioActual->getIdCarrera()
        );

        return $this->usuarioRepository->actualizar($usuarioModificado);
    }

    public function cambiarEstadoCuenta(int $id, string $accion, ?int $idActor = null): bool {
        if ($accion === 'bloquear') {
            $ok = $this->usuarioRepository->bloquearCuenta($id);
        } elseif ($accion === 'activar') {
            $ok = $this->usuarioRepository->activarCuenta($id);
        } else {
            throw new InvalidArgumentException("Acción inválida. Use 'bloquear' o 'activar'.");
        }

        if ($ok) {
            (new AuditoriaRepository())->registrar($idActor, 'ESTADO_CUENTA', "Cuenta ID {$id} -> {$accion}", 'usuarios');
        }
        return $ok;
    }
}
