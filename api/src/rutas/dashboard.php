<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * DASHBOARD API - Cátedra Bases de Datos Aplicada (UAI)
 * Endpoints multinivel (Nivel 1, Nivel 2, Nivel 3) y Autenticación con PDO
 * ══════════════════════════════════════════════════════════════════════════════
 */
require_once __DIR__ . '/../Services/JWTService.php';

// ─── 1. LOGIN DE ACCESO AL TABLERO (SOLO UNIVERSIDADES Y ADMINISTRADORES/MODERADORES) ──
$app->post('/dashboard/login', function (Request $request, Response $response) {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $request->getParsedBody() ?? $_POST ?? [];

    $email = trim($body['email'] ?? $body['usuario'] ?? '');
    $password = trim($body['password'] ?? $body['clave'] ?? '');

    if (empty($email) || empty($password)) {
        $response->getBody()->write(json_encode([
            "error" => true,
            "mensaje" => "Debe ingresar el correo electrónico y la contraseña."
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    try {
        $db = db::getInstance()->getConnection();
        $db->exec("SET NAMES utf8mb4");
        
        // 1.A: Buscar primero en la tabla de Usuarios
        $stmt = $db->prepare("
            SELECT u.idUsuario, u.nombre, u.apellido, u.email, u.password, u.tipoPerfil, u.fotoPerfil,
                   u.idRol, r.nombreRol, c.nombre AS ciudad
            FROM usuarios u
            LEFT JOIN roles r ON u.idRol = r.idRol
            LEFT JOIN ciudades c ON u.idCiudad = c.idCiudad
            WHERE u.email = :email
            LIMIT 1
        ");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 1.B: Si no existe en usuarios, verificar si es una cuenta institucional directa en la tabla 'universidades'
        if (!$user) {
            $stmtUni = $db->prepare("
                SELECT idUniversidad, nombre, email, password, celular, estado
                FROM universidades
                WHERE email = :email AND estado = 'activo'
                LIMIT 1
            ");
            $stmtUni->bindParam(':email', $email, PDO::PARAM_STR);
            $stmtUni->execute();
            $uni = $stmtUni->fetch(PDO::FETCH_ASSOC);

            if ($uni) {
                $hashUni = $uni['password'];
                $isValidUni = password_verify($password, $hashUni) || ($password === $hashUni);
                if ($isValidUni) {
                    $token = JWTService::generarToken([
                        'idUsuario' => $uni['idUniversidad'],
                        'email' => $uni['email'],
                        'tipoPerfil' => 'Universidad'
                    ]);
                    $user = [
                        'idUsuario' => $uni['idUniversidad'],
                        'nombre' => $uni['nombre'],
                        'apellido' => '(Institución)',
                        'email' => $uni['email'],
                        'tipoPerfil' => 'Universidad',
                        'nombreRol' => 'Institución',
                        'idRol' => 6,
                        'fotoPerfil' => 'https://images.unsplash.com/photo-1562774053-701939374585?w=200&auto=format&fit=crop&q=80',
                        'token' => $token
                    ];
                }
            }
        }

        if (!$user) {
            $response->getBody()->write(json_encode([
                "error" => true,
                "mensaje" => "No existe una cuenta registrada con ese correo electrónico."
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        // Si es un usuario estándar, validar contraseña con BCRYPT
        if (!isset($user['token'])) {
            $hash = $user['password'];
            $isValid = password_verify($password, $hash);
            if (!$isValid && $password === $hash) {
                $isValid = true;
            }

            if (!$isValid) {
                $response->getBody()->write(json_encode([
                    "error" => true,
                    "mensaje" => "Credenciales incorrectas. Verifique su contraseña."
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json; charset=utf-8');
            }

            // 1.C: VALIDACIÓN ESTRICTA DE ROLES AUTORIZADOS
            $perfil = trim($user['tipoPerfil'] ?? '');
            $rolId = intval($user['idRol'] ?? 0);
            $rolesPermitidos = [1, 3, 5, 6];
            $perfilesPermitidos = ['Administrador', 'Moderador', 'Universidad', 'Institución', 'Docente'];

            if (!in_array($rolId, $rolesPermitidos) && !in_array($perfil, $perfilesPermitidos)) {
                $response->getBody()->write(json_encode([
                    "error" => true,
                    "mensaje" => "Acceso Denegado: Su cuenta ({$perfil}) no posee permisos administrativos. El Tablero de Control está reservado exclusivamente para Universidades, Administradores y Moderadores/Docentes."
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json; charset=utf-8');
            }

            // Generar Token JWT
            $token = JWTService::generarToken([
                'idUsuario' => $user['idUsuario'],
                'email' => $user['email'],
                'tipoPerfil' => $user['tipoPerfil']
            ]);
            unset($user['password']);
            $user['token'] = $token;
        }

        $response->getBody()->write(json_encode([
            "error" => false,
            "mensaje" => "Autenticación exitosa al Tablero de Control.",
            "usuario" => $user
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');

    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            "error" => true,
            "mensaje" => "Error al procesar login: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
});

// ─── 2. NIVEL 1: VISTA GLOBAL / PANORÁMICA DE UNIVERSIDADES ──────────────────
$app->get('/dashboard/global', function (Request $request, Response $response) {
    try {
        $db = db::getInstance()->getConnection();
        $db->exec("SET NAMES utf8mb4");

        // 2.A: Métricas Globales Consolidadas (KPIs)
        $sqlKpis = "
            SELECT 
                COUNT(DISTINCT u.idUniversidad) AS total_universidades,
                COUNT(DISTINCT s.idSede) AS total_sedes,
                COUNT(DISTINCT c.idCarrera) AS total_carreras,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Experiencia' THEN p.idPublicacion END) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                COALESCE(ROUND(AVG(e.puntuacionAcademica), 2), 0) AS promedio_academico_global,
                COALESCE(ROUND(AVG(e.puntuacionClima), 2), 0) AS promedio_clima_global,
                COALESCE(ROUND(AVG(e.puntuacionInfraestructura), 2), 0) AS promedio_infraestructura_global,
                COALESCE(ROUND(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 2), 0) AS score_satisfaccion_global
            FROM universidades u
            LEFT JOIN sedes s ON u.idUniversidad = s.idUniversidad AND s.estado = 'activo'
            LEFT JOIN oferta_academica oa ON s.idSede = oa.idSede
            LEFT JOIN carreras c ON oa.idCarrera = c.idCarrera
            LEFT JOIN publicaciones p ON u.idUniversidad = p.idUniversidad
            LEFT JOIN experiencias e ON p.idPublicacion = e.idExperiencia
            WHERE u.estado = 'activo'
        ";
        $stmtKpis = $db->prepare($sqlKpis);
        $stmtKpis->execute();
        $kpis = $stmtKpis->fetch(PDO::FETCH_ASSOC);

        // 2.B: Lista Detallada de Universidades con Semaforización
        $sqlUniversidades = "
            SELECT 
                u.idUniversidad,
                u.nombre AS universidad,
                u.email,
                u.celular,
                COUNT(DISTINCT s.idSede) AS total_sedes,
                COUNT(DISTINCT oa.idCarrera) AS total_carreras,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT e.idExperiencia) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                COALESCE(ROUND(AVG(e.puntuacionAcademica), 2), 0) AS promedio_academico,
                COALESCE(ROUND(AVG(e.puntuacionClima), 2), 0) AS promedio_clima,
                COALESCE(ROUND(AVG(e.puntuacionInfraestructura), 2), 0) AS promedio_infraestructura,
                COALESCE(ROUND(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 2), 0) AS promedio_general,
                CASE 
                    WHEN COALESCE(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 0) >= 4.5 THEN 'VERDE'
                    WHEN COALESCE(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 0) >= 3.5 THEN 'AMARILLO'
                    ELSE 'ROJO'
                END AS estado_semaforo
            FROM universidades u
            LEFT JOIN sedes s ON u.idUniversidad = s.idUniversidad AND s.estado = 'activo'
            LEFT JOIN oferta_academica oa ON s.idSede = oa.idSede
            LEFT JOIN publicaciones p ON u.idUniversidad = p.idUniversidad
            LEFT JOIN experiencias e ON p.idPublicacion = e.idExperiencia
            WHERE u.estado = 'activo'
            GROUP BY u.idUniversidad, u.nombre, u.email, u.celular
            ORDER BY total_publicaciones DESC, promedio_general DESC, u.nombre ASC
        ";
        $stmtUni = $db->prepare($sqlUniversidades);
        $stmtUni->execute();
        $universidades = $stmtUni->fetchAll(PDO::FETCH_ASSOC);

        // 2.C: Distribución de Semaforización Global para Gráficos
        $conteoVerde = 0;
        $conteoAmarillo = 0;
        $conteoRojo = 0;
        foreach ($universidades as $uni) {
            if ($uni['estado_semaforo'] === 'VERDE') $conteoVerde++;
            elseif ($uni['estado_semaforo'] === 'AMARILLO') $conteoAmarillo++;
            else $conteoRojo++;
        }

        $response->getBody()->write(json_encode([
            "error" => false,
            "kpis" => $kpis,
            "distribucion_semaforo" => [
                "verde" => $conteoVerde,
                "amarillo" => $conteoAmarillo,
                "rojo" => $conteoRojo
            ],
            "universidades" => $universidades
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');

    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            "error" => true,
            "mensaje" => "Error al obtener vista global: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
});

// ─── 3. NIVEL 2: DRILL-DOWN UNIVERSIDAD (SEDES Y CARRERAS) ────────────────────
$app->get('/dashboard/universidad/{id}', function (Request $request, Response $response, array $args) {
    $idUniversidad = intval($args['id'] ?? 0);

    if ($idUniversidad <= 0) {
        $response->getBody()->write(json_encode(["error" => true, "mensaje" => "ID de universidad inválido."], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    try {
        $db = db::getInstance()->getConnection();
        $db->exec("SET NAMES utf8mb4");

        // 3.A: Resumen Contextual de la Universidad
        $sqlUni = "
            SELECT 
                u.idUniversidad,
                u.nombre AS universidad,
                u.email,
                u.celular,
                COUNT(DISTINCT s.idSede) AS total_sedes,
                COUNT(DISTINCT oa.idCarrera) AS total_carreras,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT e.idExperiencia) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                COALESCE(ROUND(AVG(e.puntuacionAcademica), 2), 0) AS promedio_academico,
                COALESCE(ROUND(AVG(e.puntuacionClima), 2), 0) AS promedio_clima,
                COALESCE(ROUND(AVG(e.puntuacionInfraestructura), 2), 0) AS promedio_infraestructura,
                COALESCE(ROUND(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 2), 0) AS promedio_general,
                CASE 
                    WHEN COALESCE(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 0) >= 4.5 THEN 'VERDE'
                    WHEN COALESCE(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 0) >= 3.5 THEN 'AMARILLO'
                    ELSE 'ROJO'
                END AS estado_semaforo
            FROM universidades u
            LEFT JOIN sedes s ON u.idUniversidad = s.idUniversidad AND s.estado = 'activo'
            LEFT JOIN oferta_academica oa ON s.idSede = oa.idSede
            LEFT JOIN publicaciones p ON u.idUniversidad = p.idUniversidad
            LEFT JOIN experiencias e ON p.idPublicacion = e.idExperiencia
            WHERE u.idUniversidad = :idUniversidad
            GROUP BY u.idUniversidad, u.nombre, u.email, u.celular
        ";
        $stmtUni = $db->prepare($sqlUni);
        $stmtUni->bindParam(':idUniversidad', $idUniversidad, PDO::PARAM_INT);
        $stmtUni->execute();
        $universidad = $stmtUni->fetch(PDO::FETCH_ASSOC);

        if (!$universidad) {
            $response->getBody()->write(json_encode(["error" => true, "mensaje" => "Universidad no encontrada."], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        // 3.B: Desglose de Sedes y Oferta Académica (Carreras) con Semaforización
        $sqlSedesCarreras = "
            SELECT 
                s.idSede,
                s.nombre AS sede,
                ci.nombre AS ciudad,
                pr.nombre AS provincia,
                c.idCarrera,
                c.nombre AS carrera,
                oa.descripcion AS descripcion_carrera,
                oa.web AS web_carrera,
                oa.telefono AS telefono_carrera,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT e.idExperiencia) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                COALESCE(ROUND(AVG(e.puntuacionAcademica), 2), 0) AS promedio_academico,
                COALESCE(ROUND(AVG(e.puntuacionClima), 2), 0) AS promedio_clima,
                COALESCE(ROUND(AVG(e.puntuacionInfraestructura), 2), 0) AS promedio_infraestructura,
                COALESCE(ROUND(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 2), 0) AS promedio_carrera,
                CASE 
                    WHEN COALESCE(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 0) >= 4.5 THEN 'VERDE'
                    WHEN COALESCE(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 0) >= 3.5 THEN 'AMARILLO'
                    ELSE 'ROJO'
                END AS estado_semaforo
            FROM sedes s
            JOIN oferta_academica oa ON s.idSede = oa.idSede
            JOIN carreras c ON oa.idCarrera = c.idCarrera
            LEFT JOIN ciudades ci ON s.idCiudad = ci.idCiudad
            LEFT JOIN provincias pr ON ci.idProvincia = pr.idProvincia
            LEFT JOIN publicaciones p ON (p.idSede = s.idSede AND p.idCarrera = c.idCarrera)
            LEFT JOIN experiencias e ON p.idPublicacion = e.idExperiencia
            WHERE s.idUniversidad = :idUniversidad
            GROUP BY s.idSede, s.nombre, ci.nombre, pr.nombre, c.idCarrera, c.nombre, oa.descripcion, oa.web, oa.telefono
            ORDER BY s.nombre ASC, promedio_carrera DESC, c.nombre ASC
        ";
        $stmtSC = $db->prepare($sqlSedesCarreras);
        $stmtSC->bindParam(':idUniversidad', $idUniversidad, PDO::PARAM_INT);
        $stmtSC->execute();
        $sedesCarreras = $stmtSC->fetchAll(PDO::FETCH_ASSOC);

        // 3.C: Lista de Sedes Únicas para Agrupación en el Frontend
        $sqlSedes = "
            SELECT 
                s.idSede,
                s.nombre AS sede,
                ci.nombre AS ciudad,
                pr.nombre AS provincia,
                s.email,
                s.telefono,
                s.web,
                COUNT(DISTINCT oa.idCarrera) AS total_carreras,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COALESCE(ROUND(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 2), 0) AS promedio_sede
            FROM sedes s
            LEFT JOIN ciudades ci ON s.idCiudad = ci.idCiudad
            LEFT JOIN provincias pr ON ci.idProvincia = pr.idProvincia
            LEFT JOIN oferta_academica oa ON s.idSede = oa.idSede
            LEFT JOIN publicaciones p ON s.idSede = p.idSede
            LEFT JOIN experiencias e ON p.idPublicacion = e.idExperiencia
            WHERE s.idUniversidad = :idUniversidad AND s.estado = 'activo'
            GROUP BY s.idSede, s.nombre, ci.nombre, pr.nombre, s.email, s.telefono, s.web
            ORDER BY s.nombre ASC
        ";
        $stmtSedes = $db->prepare($sqlSedes);
        $stmtSedes->bindParam(':idUniversidad', $idUniversidad, PDO::PARAM_INT);
        $stmtSedes->execute();
        $sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            "error" => false,
            "universidad" => $universidad,
            "sedes" => $sedes,
            "sedes_carreras" => $sedesCarreras
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');

    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            "error" => true,
            "mensaje" => "Error al obtener detalle de universidad: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
});

// ─── 4. NIVEL 3: DRILL-DOWN DETALLE CARRERA/SEDE (PUBLICACIONES E INTERACCIONES)
$app->get('/dashboard/carrera-detalle/{idSede}/{idCarrera}', function (Request $request, Response $response, array $args) {
    $idSede = intval($args['idSede'] ?? 0);
    $idCarrera = intval($args['idCarrera'] ?? 0);

    if ($idSede <= 0 || $idCarrera <= 0) {
        $response->getBody()->write(json_encode(["error" => true, "mensaje" => "Parámetros de Sede y Carrera inválidos."], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    try {
        $db = db::getInstance()->getConnection();
        $db->exec("SET NAMES utf8mb4");

        // 4.A: Resumen Contextual de la Carrera y Sede
        $sqlHeader = "
            SELECT 
                u.idUniversidad,
                u.nombre AS universidad,
                s.idSede,
                s.nombre AS sede,
                ci.nombre AS ciudad,
                pr.nombre AS provincia,
                c.idCarrera,
                c.nombre AS carrera,
                oa.descripcion,
                oa.web,
                oa.telefono,
                COUNT(DISTINCT p.idPublicacion) AS total_publicaciones,
                COUNT(DISTINCT e.idExperiencia) AS total_experiencias,
                COUNT(DISTINCT CASE WHEN p.tipoPublicacion = 'Consulta' THEN p.idPublicacion END) AS total_consultas,
                COALESCE(ROUND(AVG(e.puntuacionAcademica), 2), 0) AS promedio_academico,
                COALESCE(ROUND(AVG(e.puntuacionClima), 2), 0) AS promedio_clima,
                COALESCE(ROUND(AVG(e.puntuacionInfraestructura), 2), 0) AS promedio_infraestructura,
                COALESCE(ROUND(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 2), 0) AS promedio_general,
                CASE 
                    WHEN COALESCE(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 0) >= 4.5 THEN 'VERDE'
                    WHEN COALESCE(AVG((e.puntuacionAcademica + e.puntuacionClima + e.puntuacionInfraestructura) / 3.0), 0) >= 3.5 THEN 'AMARILLO'
                    ELSE 'ROJO'
                END AS estado_semaforo
            FROM sedes s
            JOIN universidades u ON s.idUniversidad = u.idUniversidad
            JOIN oferta_academica oa ON s.idSede = oa.idSede
            JOIN carreras c ON oa.idCarrera = c.idCarrera
            LEFT JOIN ciudades ci ON s.idCiudad = ci.idCiudad
            LEFT JOIN provincias pr ON ci.idProvincia = pr.idProvincia
            LEFT JOIN publicaciones p ON (p.idSede = s.idSede AND p.idCarrera = c.idCarrera)
            LEFT JOIN experiencias e ON p.idPublicacion = e.idExperiencia
            WHERE s.idSede = :idSede AND c.idCarrera = :idCarrera
            GROUP BY u.idUniversidad, u.nombre, s.idSede, s.nombre, ci.nombre, pr.nombre, c.idCarrera, c.nombre, oa.descripcion, oa.web, oa.telefono
        ";
        $stmtHead = $db->prepare($sqlHeader);
        $stmtHead->bindParam(':idSede', $idSede, PDO::PARAM_INT);
        $stmtHead->bindParam(':idCarrera', $idCarrera, PDO::PARAM_INT);
        $stmtHead->execute();
        $contexto = $stmtHead->fetch(PDO::FETCH_ASSOC);

        if (!$contexto) {
            $response->getBody()->write(json_encode(["error" => true, "mensaje" => "La oferta académica solicitada no existe."], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        // 4.B: Lista Granular de Publicaciones con Autores, Puntajes y Conteos de Interacción
        $sqlPublicaciones = "
            SELECT 
                p.idPublicacion,
                p.tipoPublicacion,
                p.titulo,
                p.texto,
                p.fecha,
                p.hora,
                p.likes,
                p.resuelta,
                usr.idUsuario,
                usr.nombre AS autor_nombre,
                usr.apellido AS autor_apellido,
                usr.email AS autor_email,
                usr.tipoPerfil AS autor_perfil,
                usr.fotoPerfil AS autor_foto,
                e.puntuacionClima,
                e.puntuacionInfraestructura,
                e.puntuacionAcademica,
                CASE 
                    WHEN p.tipoPublicacion = 'Experiencia' THEN ROUND((e.puntuacionClima + e.puntuacionInfraestructura + e.puntuacionAcademica) / 3.0, 2)
                    ELSE NULL
                END AS promedio_experiencia,
                COUNT(DISTINCT r.idRespuesta) AS total_respuestas,
                COUNT(DISTINCT f.idFavorito) AS total_favoritos
            FROM publicaciones p
            JOIN usuarios usr ON p.idUsuario = usr.idUsuario
            LEFT JOIN experiencias e ON p.idPublicacion = e.idExperiencia
            LEFT JOIN respuestas r ON p.idPublicacion = r.idPublicacion
            LEFT JOIN favoritos f ON p.idPublicacion = f.idPublicacion AND f.estado = 'activo'
            WHERE p.idSede = :idSede AND p.idCarrera = :idCarrera
            GROUP BY p.idPublicacion, p.tipoPublicacion, p.titulo, p.texto, p.fecha, p.hora, p.likes, p.resuelta,
                     usr.idUsuario, usr.nombre, usr.apellido, usr.email, usr.tipoPerfil, usr.fotoPerfil,
                     e.puntuacionClima, e.puntuacionInfraestructura, e.puntuacionAcademica
            ORDER BY p.fecha DESC, p.hora DESC
        ";
        $stmtPub = $db->prepare($sqlPublicaciones);
        $stmtPub->bindParam(':idSede', $idSede, PDO::PARAM_INT);
        $stmtPub->bindParam(':idCarrera', $idCarrera, PDO::PARAM_INT);
        $stmtPub->execute();
        $publicaciones = $stmtPub->fetchAll(PDO::FETCH_ASSOC);

        // 4.C: Respuestas Comunitarias vinculadas a estas publicaciones
        $sqlRespuestas = "
            SELECT 
                r.idRespuesta,
                r.idPublicacion,
                r.texto,
                r.fecha,
                r.hora,
                u.idUsuario AS autor_respuesta_id,
                u.nombre AS autor_respuesta_nombre,
                u.apellido AS autor_respuesta_apellido,
                u.fotoPerfil AS autor_respuesta_foto,
                u.tipoPerfil AS autor_respuesta_perfil
            FROM respuestas r
            JOIN usuarios u ON r.idUsuario = u.idUsuario
            JOIN publicaciones p ON r.idPublicacion = p.idPublicacion
            WHERE p.idSede = :idSede AND p.idCarrera = :idCarrera
            ORDER BY r.fecha ASC, r.hora ASC
        ";
        $stmtResp = $db->prepare($sqlRespuestas);
        $stmtResp->bindParam(':idSede', $idSede, PDO::PARAM_INT);
        $stmtResp->bindParam(':idCarrera', $idCarrera, PDO::PARAM_INT);
        $stmtResp->execute();
        $respuestas = $stmtResp->fetchAll(PDO::FETCH_ASSOC);

        // Mapear respuestas a sus publicaciones correspondientes
        $respuestasPorPublicacion = [];
        foreach ($respuestas as $resp) {
            $respuestasPorPublicacion[$resp['idPublicacion']][] = $resp;
        }

        foreach ($publicaciones as &$pub) {
            $pub['respuestas'] = $respuestasPorPublicacion[$pub['idPublicacion']] ?? [];
        }

        $response->getBody()->write(json_encode([
            "error" => false,
            "contexto" => $contexto,
            "publicaciones" => $publicaciones
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');

    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            "error" => true,
            "mensaje" => "Error al obtener detalle de carrera: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
});
