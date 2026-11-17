<?php
// api/src/Facades/DashboardFacade.php

require_once __DIR__ . '/../Repositories/DashboardRepository.php';
require_once __DIR__ . '/../Services/JWTService.php';

class DashboardFacade {
    private static $instance = null;
    private DashboardRepository $repo;

    private function __construct() {
        $this->repo = new DashboardRepository();
    }

    private function __clone() {}

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Autenticación de acceso restringido al Tablero de Control
     */
    public function autenticarAcceso(string $email, string $password): array {
        if (empty($email) || empty($password)) {
            throw new InvalidArgumentException("Debe ingresar el correo electrónico y la contraseña.");
        }

        // 1.A: Buscar primero en la tabla de Usuarios
        $user = $this->repo->buscarUsuarioDashboard($email);

        // 1.B: Si no existe en usuarios, verificar si es una cuenta institucional directa en 'universidades'
        if (!$user) {
            $uni = $this->repo->buscarUniversidadDashboard($email);

            if ($uni && password_verify($password, $uni['password'])) {
                $token = JWTService::generarToken([
                    'idUsuario'  => (int)$uni['idUniversidad'],
                    'email'      => $uni['email'],
                    'tipoPerfil' => 'Universidad',
                    'idRol'      => 6
                ]);
                return [
                    'idUsuario'  => (int)$uni['idUniversidad'],
                    'nombre'     => $uni['nombre'],
                    'apellido'   => '(Institución)',
                    'email'      => $uni['email'],
                    'tipoPerfil' => 'Universidad',
                    'nombreRol'  => 'Institución',
                    'idRol'      => 6,
                    'fotoPerfil' => 'https://images.unsplash.com/photo-1562774053-701939374585?w=200&auto=format&fit=crop&q=80',
                    'token'      => $token
                ];
            }
        }

        if (!$user) {
            throw new RuntimeException("No existe una cuenta registrada con ese correo electrónico.");
        }

        if (!password_verify($password, $user['password'])) {
            throw new RuntimeException("Credenciales incorrectas. Verifique su contraseña.");
        }

        // 1.C: Validación de roles autorizados
        $perfil = trim($user['tipoPerfil'] ?? '');
        $rolId = intval($user['idRol'] ?? 0);
        $rolesPermitidos = [1, 3, 5, 6];
        $perfilesPermitidos = ['Administrador', 'Moderador', 'Universidad', 'Institución', 'Docente'];

        if (!in_array($rolId, $rolesPermitidos) && !in_array($perfil, $perfilesPermitidos)) {
            throw new DomainException("Acceso Denegado: Su cuenta ({$perfil}) no posee permisos administrativos.");
        }

        $token = JWTService::generarToken([
            'idUsuario'  => (int)$user['idUsuario'],
            'email'      => $user['email'],
            'tipoPerfil' => $user['tipoPerfil'],
            'idRol'      => (int)$user['idRol']
        ]);
        unset($user['password']);
        $user['token'] = $token;

        return $user;
    }

    /**
     * Nivel 1: Panorama Global Consolidado
     */
    public function obtenerPanoramaGlobal(): array {
        $kpis          = $this->repo->obtenerKpisGlobales();
        $universidades = $this->repo->obtenerRankingUniversidades();

        $conteoVerde = 0;
        $conteoAmarillo = 0;
        $conteoRojo = 0;
        $conteoSinDatos = 0;
        foreach ($universidades as $uni) {
            if ($uni['estado_semaforo'] === 'VERDE') $conteoVerde++;
            elseif ($uni['estado_semaforo'] === 'AMARILLO') $conteoAmarillo++;
            elseif ($uni['estado_semaforo'] === 'ROJO') $conteoRojo++;
            else $conteoSinDatos++;
        }

        return [
            "kpis" => $kpis,
            "distribucion_semaforo" => [
                "verde"     => $conteoVerde,
                "amarillo"  => $conteoAmarillo,
                "rojo"      => $conteoRojo,
                "sin_datos" => $conteoSinDatos
            ],
            "universidades" => $universidades
        ];
    }

    /**
     * Nivel 2: Drill-Down Detalle Institucional (Sedes y Carreras)
     */
    public function obtenerDesgloseUniversidad(int $idUniversidad): array {
        if ($idUniversidad <= 0) {
            throw new InvalidArgumentException("ID de universidad inválido.");
        }

        $universidad = $this->repo->obtenerResumenUniversidad($idUniversidad);
        if (!$universidad) {
            throw new RuntimeException("Universidad no encontrada.");
        }

        $sedesCarreras = $this->repo->obtenerSedesCarrerasUniversidad($idUniversidad);
        $sedes         = $this->repo->obtenerSedesResumenUniversidad($idUniversidad);

        return [
            "universidad"    => $universidad,
            "sedes"          => $sedes,
            "sedes_carreras" => $sedesCarreras
        ];
    }

    /**
     * Nivel 3: Drill-Down Detalle Carrera en Sede (Feedback Granular y Reseñas)
     */
    public function obtenerDetalleCarrera(int $idSede, int $idCarrera): array {
        if ($idSede <= 0 || $idCarrera <= 0) {
            throw new InvalidArgumentException("Parámetros de Sede y Carrera inválidos.");
        }

        $contexto = $this->repo->obtenerContextoCarrera($idSede, $idCarrera);
        if (!$contexto) {
            throw new RuntimeException("La oferta académica solicitada no existe.");
        }

        $publicaciones   = $this->repo->obtenerPublicacionesCarrera($idSede, $idCarrera);
        $pubIds          = array_column($publicaciones, 'idPublicacion');
        $todasRespuestas = $this->repo->obtenerRespuestasPorPublicaciones($pubIds);

        $respuestasPorPub = [];
        foreach ($todasRespuestas as $resp) {
            $respuestasPorPub[$resp['idPublicacion']][] = $resp;
        }

        foreach ($publicaciones as &$pub) {
            $pub['respuestas'] = $respuestasPorPub[$pub['idPublicacion']] ?? [];
        }

        return [
            "contexto"      => $contexto,
            "publicaciones" => $publicaciones
        ];
    }
}