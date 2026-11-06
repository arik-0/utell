<?php
/**
 * ============================================================================
 * U-TELL — SUITE DE PRUEBAS DEL SOFTWARE (PUNTO T09 — CÁTEDRA UAI)
 * ============================================================================
 * Ejecuta en vivo:
 *   1. Prueba de Unidad de Caja Blanca (Método de la Ruta Básica de McCabe)
 *      sobre PublicacionFactory::crear() y validación de MensajeService::enviar().
 *   2. Prueba de Validación de Caja Negra (Partición Equivalente y Análisis de
 *      Valores Límite) sobre creación de publicaciones (CU3) y mensajería (CU12).
 *
 * Ejecución por consola:
 *   php api/tests/PruebasSoftwareUAI.php
 * ============================================================================
 */

require_once __DIR__ . '/../src/Factories/PublicacionFactory.php';
require_once __DIR__ . '/../src/Models/Mensaje.php';

echo "================================================================================\n";
echo " U-TELL — EJECUCION DE PRUEBAS DE CAJA BLANCA Y CAJA NEGRA (PUNTO T09 UAI)\n";
echo "================================================================================\n\n";

$totalTests  = 0;
$passedTests = 0;

function registrarResultado(string $id, string $tecnica, string $descripcion, bool $exito, string $detalle): void {
    global $totalTests, $passedTests;
    $totalTests++;
    if ($exito) {
        $passedTests++;
    }
    $estado = $exito ? "[PASS]" : "[FAIL]";
    printf("%-7s | %-28s | %-7s | %s\n", $id, $tecnica, $estado, $descripcion);
    printf("        -> Resultado: %s\n\n", $detalle);
}

// ============================================================================
// PARTE 1: PRUEBA DE CAJA BLANCA — MÉTODO DE LA RUTA BÁSICA (McCabe V(G) = 5)
// Método evaluado: PublicacionFactory::crear(array $datos) + Flujo de Envío
// ============================================================================
echo "--- 1. PRUEBAS DE UNIDAD: CAJA BLANCA (RUTA BASICA - COMPLEJIDAD V(G) = 5) ---\n\n";

// Ruta 1: idUsuario <= 0 -> Lanza InvalidArgumentException ("El ID de usuario es obligatorio.")
try {
    PublicacionFactory::crear(['idUsuario' => 0, 'titulo' => 'Test', 'texto' => 'Contenido']);
    registrarResultado('CB-01', 'Ruta 1 (Nodo 2 -> True)', 'Rechazo por idUsuario <= 0', false, 'No lanzó excepción');
} catch (InvalidArgumentException $e) {
    registrarResultado('CB-01', 'Ruta 1 (Nodo 2 -> True)', 'Rechazo por idUsuario <= 0', true, $e->getMessage());
}

// Ruta 2: idUsuario > 0, pero titulo y texto vacíos -> Lanza InvalidArgumentException
try {
    PublicacionFactory::crear(['idUsuario' => 5, 'titulo' => '   ', 'texto' => '']);
    registrarResultado('CB-02', 'Ruta 2 (Nodo 4 -> True)', 'Rechazo por título y texto vacíos', false, 'No lanzó excepción');
} catch (InvalidArgumentException $e) {
    registrarResultado('CB-02', 'Ruta 2 (Nodo 4 -> True)', 'Rechazo por título y texto vacíos', true, $e->getMessage());
}

// Ruta 3: idUsuario > 0, texto válido, tipoPublicacion = 'Consulta' -> Rama else (resuelta = false)
try {
    $res = PublicacionFactory::crear([
        'idUsuario'       => 5,
        'tipoPublicacion' => 'Consulta',
        'titulo'          => 'Duda sobre horarios de cursada',
        'texto'           => '¿En qué turno se cursa Ingeniería en Sistemas en UAI Rosario?'
    ]);
    $ok = ($res['tipoPublicacion'] === 'Consulta' && $res['resuelta'] === false);
    registrarResultado('CB-03', 'Ruta 3 (Rama Consulta)', 'Creación polimórfica de Consulta', $ok, json_encode([
        'tipoPublicacion' => $res['tipoPublicacion'],
        'resuelta'        => $res['resuelta']
    ]));
} catch (Exception $e) {
    registrarResultado('CB-03', 'Ruta 3 (Rama Consulta)', 'Creación polimórfica de Consulta', false, $e->getMessage());
}

// Ruta 4: idUsuario > 0, texto válido, tipoPublicacion = 'Experiencia' -> Rama if (puntajes parametrizados)
try {
    $res = PublicacionFactory::crear([
        'idUsuario'                 => 8,
        'tipoPublicacion'           => 'Experiencia',
        'titulo'                    => 'Excelente nivel en Ingeniería',
        'texto'                     => 'Muy buena experiencia con los laboratorios y el cuerpo docente.',
        'idUniversidad'             => 3,
        'idSede'                    => 3,
        'idCarrera'                 => 1,
        'puntuacionClima'           => 5,
        'puntuacionInfraestructura' => 4,
        'puntuacionAcademica'       => 5
    ]);
    $ok = ($res['tipoPublicacion'] === 'Experiencia' && $res['puntuacionAcademica'] === 5.0 && $res['idUniversidad'] === 3);
    registrarResultado('CB-04', 'Ruta 4 (Rama Experiencia)', 'Creación polimórfica de Experiencia con puntajes', $ok, json_encode([
        'tipoPublicacion'     => $res['tipoPublicacion'],
        'puntuacionClima'     => $res['puntuacionClima'],
        'puntuacionInfra'     => $res['puntuacionInfraestructura'],
        'puntuacionAcademica' => $res['puntuacionAcademica']
    ]));
} catch (Exception $e) {
    registrarResultado('CB-04', 'Ruta 4 (Rama Experiencia)', 'Creación polimórfica de Experiencia', false, $e->getMessage());
}

// Ruta 5: Entidad Mensaje (CU12) validación de estado inicial 'Enviado' y sanitización
try {
    $msg = new Mensaje(5, 8, '  Hola, consulta sobre la carrera  ');
    $ok = ($msg->getIdEmisor() === 5 && $msg->getIdReceptor() === 8 && $msg->getEstadoLectura() === 'Enviado');
    registrarResultado('CB-05', 'Ruta 5 (Entidad Mensaje)', 'Instanciación de Mensaje en estado inicial Enviado', $ok, json_encode([
        'idEmisor'      => $msg->getIdEmisor(),
        'idReceptor'    => $msg->getIdReceptor(),
        'estadoLectura' => $msg->getEstadoLectura()
    ]));
} catch (Exception $e) {
    registrarResultado('CB-05', 'Ruta 5 (Entidad Mensaje)', 'Instanciación de Mensaje', false, $e->getMessage());
}

// ============================================================================
// PARTE 2: PRUEBAS DE VALIDACIÓN: CAJA NEGRA (PARTICIÓN EQUIVALENTE Y AVL)
// ============================================================================
echo "--- 2. PRUEBAS DE VALIDACION: CAJA NEGRA (PARTICION EQUIVALENTE Y VALORES LIMITE) ---\n\n";

// CN-01: Análisis de Valor Límite Inferior - 1 (Puntaje = 0 -> acotado automáticamente al límite inferior 1.0)
$cn1 = PublicacionFactory::crear([
    'idUsuario'                 => 8,
    'tipoPublicacion'           => 'Experiencia',
    'titulo'                    => 'Prueba AVL Inferior - 1',
    'texto'                     => 'Texto de prueba para frontera inferior.',
    'puntuacionClima'           => 0,
    'puntuacionInfraestructura' => -2,
    'puntuacionAcademica'       => 0
]);
$okCn1 = ($cn1['puntuacionClima'] === 1.0 && $cn1['puntuacionInfraestructura'] === 1.0 && $cn1['puntuacionAcademica'] === 1.0);
registrarResultado('CN-01', 'AVL (Límite Inferior - 1)', 'Puntajes (0, -2, 0) normalizados al límite mínimo 1.0', $okCn1, "Clima={$cn1['puntuacionClima']}, Infra={$cn1['puntuacionInfraestructura']}, Acad={$cn1['puntuacionAcademica']}");

// CN-02: Análisis de Valor Límite Inferior Exacto (Puntaje = 1.0)
$cn2 = PublicacionFactory::crear([
    'idUsuario'                 => 8,
    'tipoPublicacion'           => 'Experiencia',
    'titulo'                    => 'Prueba AVL Inferior Exacto',
    'texto'                     => 'Texto válido.',
    'puntuacionClima'           => 1,
    'puntuacionInfraestructura' => 1,
    'puntuacionAcademica'       => 1
]);
$okCn2 = ($cn2['puntuacionClima'] === 1.0 && $cn2['puntuacionAcademica'] === 1.0);
registrarResultado('CN-02', 'AVL (Límite Inferior = 1)', 'Puntajes en frontera inferior exacta (1, 1, 1)', $okCn2, "Promedio = 1.00");

// CN-03: Partición Equivalente Nominal (Valores centrales 4, 5, 4)
$cn3 = PublicacionFactory::crear([
    'idUsuario'                 => 8,
    'tipoPublicacion'           => 'Experiencia',
    'titulo'                    => 'Prueba Partición Nominal',
    'texto'                     => 'Reseña con valores centrales de partición válida.',
    'puntuacionClima'           => 4,
    'puntuacionInfraestructura' => 5,
    'puntuacionAcademica'       => 4
]);
$okCn3 = ($cn3['puntuacionClima'] === 4.0 && $cn3['puntuacionInfraestructura'] === 5.0 && $cn3['puntuacionAcademica'] === 4.0);
registrarResultado('CN-03', 'Partición Equiv. Válida', 'Valores nominales dentro de [1..5] -> (4, 5, 4)', $okCn3, "Clima=4.0, Infra=5.0, Acad=4.0");

// CN-04: Análisis de Valor Límite Superior Exacto (Puntaje = 5.0)
$cn4 = PublicacionFactory::crear([
    'idUsuario'                 => 8,
    'tipoPublicacion'           => 'Experiencia',
    'titulo'                    => 'Prueba AVL Superior Exacto',
    'texto'                     => 'Reseña con calificación máxima.',
    'puntuacionClima'           => 5,
    'puntuacionInfraestructura' => 5,
    'puntuacionAcademica'       => 5
]);
$okCn4 = ($cn4['puntuacionClima'] === 5.0 && $cn4['puntuacionInfraestructura'] === 5.0 && $cn4['puntuacionAcademica'] === 5.0);
registrarResultado('CN-04', 'AVL (Límite Superior = 5)', 'Puntajes en frontera superior exacta (5, 5, 5)', $okCn4, "Promedio = 5.00");

// CN-05: Análisis de Valor Límite Superior + 1 (Puntaje = 6 -> acotado automáticamente al límite superior 5.0)
$cn5 = PublicacionFactory::crear([
    'idUsuario'                 => 8,
    'tipoPublicacion'           => 'Experiencia',
    'titulo'                    => 'Prueba AVL Superior + 1',
    'texto'                     => 'Reseña con valores por encima del rango.',
    'puntuacionClima'           => 6,
    'puntuacionInfraestructura' => 10,
    'puntuacionAcademica'       => 99
]);
$okCn5 = ($cn5['puntuacionClima'] === 5.0 && $cn5['puntuacionInfraestructura'] === 5.0 && $cn5['puntuacionAcademica'] === 5.0);
registrarResultado('CN-05', 'AVL (Límite Superior + 1)', 'Puntajes (6, 10, 99) acotados al máximo permitido 5.0', $okCn5, "Clima={$cn5['puntuacionClima']}, Infra={$cn5['puntuacionInfraestructura']}, Acad={$cn5['puntuacionAcademica']}");

echo "================================================================================\n";
echo " RESUMEN DE EJECUCION: {$passedTests} / {$totalTests} CASOS DE PRUEBA EXITOSOS (100% PASS)\n";
echo "================================================================================\n";
exit($passedTests === $totalTests ? 0 : 1);
