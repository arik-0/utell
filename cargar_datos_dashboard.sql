-- ══════════════════════════════════════════════════════════════════════════════
-- CARGA DE DATOS DEMO & VARIACIÓN DEL TABLERO UAI (U-TELL)
-- Ejecutar en phpMyAdmin (Alwaysdata) sobre la base de datos 'utell_db'
-- ══════════════════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. SEDES PARA UAI (idUniversidad = 8)
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO `sedes` (`idSede`, `idUniversidad`, `idCiudad`, `nombre`, `descripcion`, `email`, `web`, `telefono`, `estado`) VALUES
(13, 8, 1, 'UAI - Sede Rosario (Campus Pellegrini)', 'Facultad de Tecnología Informática, Medicina y Ciencias Jurídicas', 'rosario@uai.edu.ar', 'https://uai.edu.ar', '+54 341 440-8010', 'activo'),
(14, 8, 5, 'UAI - Sede Central Buenos Aires (San Telmo)', 'Rectorado y Facultad de Tecnología Informática y Arquitectura', 'buenosaires@uai.edu.ar', 'https://uai.edu.ar', '+54 11 4342-7788', 'activo')
ON DUPLICATE KEY UPDATE 
  `nombre` = VALUES(`nombre`),
  `idUniversidad` = VALUES(`idUniversidad`),
  `estado` = 'activo';

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. OFERTA ACADÉMICA (Vincular Sedes con Carreras para UAI y UNC)
-- ─────────────────────────────────────────────────────────────────────────────
-- UAI Rosario (Sede 13)
INSERT INTO `oferta_academica` (`idCarrera`, `idSede`, `fechaImplementacion`, `descripcion`, `email`, `web`, `telefono`) VALUES
(2, 13, '1995-03-01', 'Ingeniería en Sistemas Informáticos - Cátedra de Bases de Datos Aplicada', 'sistemas.rosario@uai.edu.ar', 'https://uai.edu.ar', '+54 341 440-8010'),
(1, 13, '1998-03-01', 'Carrera de Medicina - Hospital Universitario UAI', 'medicina.rosario@uai.edu.ar', 'https://uai.edu.ar', '+54 341 440-8011'),
(4, 13, '1996-03-01', 'Abogacía y Práctica Profesional Supervisada', 'derecho.rosario@uai.edu.ar', 'https://uai.edu.ar', '+54 341 440-8012'),
-- UAI Buenos Aires (Sede 14)
(2, 14, '1994-03-01', 'Ingeniería en Sistemas Informáticos - Sede San Telmo', 'sistemas.ba@uai.edu.ar', 'https://uai.edu.ar', '+54 11 4342-7788'),
(5, 14, '2000-03-01', 'Arquitectura y Urbanismo - Talleres de Diseño', 'arquitectura.ba@uai.edu.ar', 'https://uai.edu.ar', '+54 11 4342-7789'),
(7, 14, '1996-03-01', 'Licenciatura en Administración de Empresas', 'administracion.ba@uai.edu.ar', 'https://uai.edu.ar', '+54 11 4342-7790'),
-- UNC Córdoba (Sede 10)
(5, 10, '1955-03-01', 'Arquitectura y Urbanismo - FAUD UNC', 'faud@unc.edu.ar', 'https://faud.unc.edu.ar', '+54 351 535-3700'),
(1, 10, '1920-03-01', 'Medicina - FCM UNC', 'fcm@unc.edu.ar', 'https://fcm.unc.edu.ar', '+54 351 535-3701'),
(3, 10, '1990-03-01', 'Licenciatura en Ciencias de la Computación - FAMAF', 'famaf@unc.edu.ar', 'https://famaf.unc.edu.ar', '+54 351 535-3702')
ON DUPLICATE KEY UPDATE `descripcion` = VALUES(`descripcion`);

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. CALIBRACIÓN DE EXPERIENCIAS EXISTENTES (Para dar variedad real)
-- ─────────────────────────────────────────────────────────────────────────────
-- ID 2 (UNR Medicina) -> Rigor 4, Clima 4, Infra 4 (Promedio 4.00)
UPDATE `experiencias` SET `puntuacionAcademica` = 4, `puntuacionClima` = 4, `puntuacionInfraestructura` = 4 WHERE `idExperiencia` = 2;
-- ID 4 (UTN Sistemas) -> Rigor 5, Clima 4, Infra 3 (Promedio 4.00)
UPDATE `experiencias` SET `puntuacionAcademica` = 5, `puntuacionClima` = 4, `puntuacionInfraestructura` = 3 WHERE `idExperiencia` = 4;
-- ID 6 (UBA Derecho) -> Rigor 5, Clima 4, Infra 4 (Promedio 4.33)
UPDATE `experiencias` SET `puntuacionAcademica` = 5, `puntuacionClima` = 4, `puntuacionInfraestructura` = 4 WHERE `idExperiencia` = 6;
-- ID 7 (ITBA Sistemas) -> Rigor 5, Clima 5, Infra 5 (Promedio 5.00)
UPDATE `experiencias` SET `puntuacionAcademica` = 5, `puntuacionClima` = 5, `puntuacionInfraestructura` = 5 WHERE `idExperiencia` = 7;
-- ID 8 (UNLP Informática) -> Rigor 4, Clima 4, Infra 3 (Promedio 3.67)
UPDATE `experiencias` SET `puntuacionAcademica` = 4, `puntuacionClima` = 4, `puntuacionInfraestructura` = 3 WHERE `idExperiencia` = 8;
-- ID 10 (UADE Admin) -> Rigor 3, Clima 4, Infra 5 (Promedio 4.00)
UPDATE `experiencias` SET `puntuacionAcademica` = 3, `puntuacionClima` = 4, `puntuacionInfraestructura` = 5 WHERE `idExperiencia` = 10;
-- ID 12 (UAI Sistemas) -> Rigor 5, Clima 5, Infra 5 (Promedio 5.00)
UPDATE `experiencias` SET `puntuacionAcademica` = 5, `puntuacionClima` = 5, `puntuacionInfraestructura` = 5, `idSede` = 13, `idCarrera` = 2 WHERE `idExperiencia` = 12;
UPDATE `publicaciones` SET `idSede` = 13, `idCarrera` = 2 WHERE `idPublicacion` = 12;
-- ID 16 (UNR) -> Rigor 4, Clima 4, Infra 3 (Promedio 3.67)
UPDATE `experiencias` SET `puntuacionAcademica` = 4, `puntuacionClima` = 4, `puntuacionInfraestructura` = 3 WHERE `idExperiencia` = 16;

-- Corregir sedes en publicaciones existentes para que se visualicen en drill-down
UPDATE `publicaciones` SET `idSede` = 2, `idCarrera` = 1 WHERE `idPublicacion` IN (1, 2, 3, 13);
UPDATE `publicaciones` SET `idSede` = 7, `idCarrera` = 2 WHERE `idPublicacion` IN (4, 5);
UPDATE `publicaciones` SET `idSede` = 4, `idCarrera` = 4 WHERE `idPublicacion` = 6;
UPDATE `publicaciones` SET `idSede` = 12, `idCarrera` = 2 WHERE `idPublicacion` = 7;
UPDATE `publicaciones` SET `idSede` = 9, `idCarrera` = 3 WHERE `idPublicacion` = 8;
UPDATE `publicaciones` SET `idSede` = 10, `idCarrera` = 5 WHERE `idPublicacion` = 9;
UPDATE `publicaciones` SET `idSede` = 11, `idCarrera` = 7 WHERE `idPublicacion` = 10;
UPDATE `publicaciones` SET `idSede` = 4, `idCarrera` = 2 WHERE `idPublicacion` = 15;

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. NUEVAS PUBLICACIONES VARIADAS (Consultas y Experiencias Multidimensionales)
-- ─────────────────────────────────────────────────────────────────────────────

-- ─── UAI (Universidad Abierta Interamericana - id 8) ─────────────────────────
INSERT IGNORE INTO `publicaciones` (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`) VALUES
(18, 4, 'Experiencia', 'Cátedra de Bases de Datos Aplicada en UAI Rosario', '2026-09-15', '10:30:00', 8, 13, 2, 
'Excelente nivel pedagógico en la cátedra de Bases de Datos. Los proyectos de desarrollo sobre arquitectura web moderna y motores relacionales te preparan directo para el mercado laboral.', 24, 0),

(19, 5, 'Experiencia', 'Prácticas clínicas y simulación médica en UAI', '2026-09-18', '14:20:00', 8, 13, 1, 
'El centro de simulación médica y el hospital universitario tienen equipamiento de alta tecnología. Docentes con enorme vocación hospitalaria.', 18, 0),

(20, 7, 'Experiencia', 'Laboratorios informáticos y campus San Telmo UAI', '2026-09-22', '16:45:00', 8, 14, 2, 
'Las aulas de desarrollo cuentan con hardware moderno, buena conectividad y soporte continuo para trabajos prácticos en equipo. Clima sumamente ameno.', 15, 0),

(21, 8, 'Consulta', '¿Alguien cursó Arquitectura de Software en UAI San Telmo?', '2026-09-25', '11:10:00', 8, 14, 2, 
'Quisiera saber qué enfoque le dan a los patrones de diseño y microservicios en el trabajo práctico integrador.', 6, 0);

-- Experiencias de UAI en tabla 'experiencias' (Altos puntajes -> VERDE 4.83)
INSERT INTO `experiencias` (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionAcademica`, `puntuacionClima`, `puntuacionInfraestructura`) VALUES
(18, 4, '2026-09-15', '10:30:00', 8, 13, 2, 'Excelente nivel pedagógico en la cátedra de Bases de Datos. Los proyectos de desarrollo sobre arquitectura web moderna y motores relacionales te preparan directo para el mercado laboral.', 24, 5, 5, 5),
(19, 5, '2026-09-18', '14:20:00', 8, 13, 1, 'El centro de simulación médica y el hospital universitario tienen equipamiento de alta tecnología. Docentes con enorme vocación hospitalaria.', 18, 5, 4, 5),
(20, 7, '2026-09-22', '16:45:00', 8, 14, 2, 'Las aulas de desarrollo cuentan con hardware moderno, buena conectividad y soporte continuo para trabajos prácticos en equipo. Clima sumamente ameno.', 15, 5, 5, 4)
ON DUPLICATE KEY UPDATE `puntuacionAcademica` = VALUES(`puntuacionAcademica`), `puntuacionClima` = VALUES(`puntuacionClima`), `puntuacionInfraestructura` = VALUES(`puntuacionInfraestructura`);

INSERT INTO `consultas` (`idConsulta`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`) VALUES
(21, 8, '2026-09-25', '11:10:00', 8, 14, 2, 'Quisiera saber qué enfoque le dan a los patrones de diseño y microservicios en el trabajo práctico integrador.', 6)
ON DUPLICATE KEY UPDATE `texto` = VALUES(`texto`);

-- ─── UNC (Universidad Nacional de Córdoba - id 5) -> Puntuaciones Bajas -> ROJO (3.00) ───
INSERT IGNORE INTO `publicaciones` (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`) VALUES
(22, 9, 'Experiencia', 'Dificultades de infraestructura en Talleres de Arquitectura UNC', '2026-09-08', '09:15:00', 5, 10, 5, 
'Los profesores tienen buena voluntad pero las aulas de taller están superpobladas, falta ventilación y los tableros de dibujo están bastante deteriorados.', 14, 0),

(23, 10, 'Experiencia', 'Burocracia y tiempos de gestión en Ciudad Universitaria UNC', '2026-09-12', '15:40:00', 5, 10, 5, 
'Los trámites administrativos demoran meses y la atención en secretaría es deficiente. A nivel académico es exigente pero el clima estudiantil se ve afectado por la falta de recursos.', 9, 0),

(24, 11, 'Consulta', '¿Dónde conseguir materiales para maquetas cerca de UNC?', '2026-09-19', '18:20:00', 5, 10, 5, 
'Busco librerías técnicas con precios accesibles para estudiantes en la zona de Nueva Córdoba.', 4, 1);

INSERT INTO `experiencias` (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionAcademica`, `puntuacionClima`, `puntuacionInfraestructura`) VALUES
(22, 9, '2026-09-08', '09:15:00', 5, 10, 5, 'Los profesores tienen buena voluntad pero las aulas de taller están superpobladas, falta ventilación y los tableros de dibujo están bastante deteriorados.', 14, 3, 3, 2),
(23, 10, '2026-09-12', '15:40:00', 5, 10, 5, 'Los trámites administrativos demoran meses y la atención en secretaría es deficiente. A nivel académico es exigente pero el clima estudiantil se ve afectado por la falta de recursos.', 9, 4, 3, 3)
ON DUPLICATE KEY UPDATE `puntuacionAcademica` = VALUES(`puntuacionAcademica`), `puntuacionClima` = VALUES(`puntuacionClima`), `puntuacionInfraestructura` = VALUES(`puntuacionInfraestructura`);

INSERT INTO `consultas` (`idConsulta`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`) VALUES
(24, 11, '2026-09-19', '18:20:00', 5, 10, 5, 'Busco librerías técnicas con precios accesibles para estudiantes en la zona de Nueva Córdoba.', 4)
ON DUPLICATE KEY UPDATE `texto` = VALUES(`texto`);

-- ─── ITBA (id 7) -> Máximo Rigor e Infraestructura -> VERDE (4.83) ────────────
INSERT IGNORE INTO `publicaciones` (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`) VALUES
(25, 7, 'Experiencia', 'Pasantías internacionales e innovación en ITBA Puerto Madero', '2026-09-14', '11:00:00', 7, 12, 2, 
'Los laboratorios de robótica y computación en la nube son excepcionales. Empresas líderes vienen directamente al campus a reclutar antes de graduarte.', 31, 0);

INSERT INTO `experiencias` (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionAcademica`, `puntuacionClima`, `puntuacionInfraestructura`) VALUES
(25, 7, '2026-09-14', '11:00:00', 7, 12, 2, 'Los laboratorios de robótica y computación en la nube son excepcionales. Empresas líderes vienen directamente al campus a reclutar antes de graduarte.', 31, 5, 4, 5)
ON DUPLICATE KEY UPDATE `puntuacionAcademica` = VALUES(`puntuacionAcademica`), `puntuacionClima` = VALUES(`puntuacionClima`), `puntuacionInfraestructura` = VALUES(`puntuacionInfraestructura`);

-- ─── UBA (id 2) -> Rigor Muy Alto, Clima Bueno, Infra Regular -> VERDE (4.50) ─
INSERT IGNORE INTO `publicaciones` (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`) VALUES
(26, 6, 'Experiencia', 'Exigencia y rigor en FIUBA Paseo Colón', '2026-09-16', '17:30:00', 2, 4, 2, 
'La matemática y la física en FIUBA son de un nivel superior. El edificio histórico impone respeto, aunque algunas aulas necesitan actualización de proyectores.', 22, 0);

INSERT INTO `experiencias` (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionAcademica`, `puntuacionClima`, `puntuacionInfraestructura`) VALUES
(26, 6, '2026-09-16', '17:30:00', 2, 4, 2, 'La matemática y la física en FIUBA son de un nivel superior. El edificio histórico impone respeto, aunque algunas aulas necesitan actualización de proyectores.', 22, 5, 4, 4)
ON DUPLICATE KEY UPDATE `puntuacionAcademica` = VALUES(`puntuacionAcademica`), `puntuacionClima` = VALUES(`puntuacionClima`), `puntuacionInfraestructura` = VALUES(`puntuacionInfraestructura`);

-- ─── UTN (id 3) -> AMARILLO (4.22) ───────────────────────────────────────────
INSERT IGNORE INTO `publicaciones` (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`) VALUES
(27, 5, 'Experiencia', 'Vida universitaria y comunidad en UTN Buenos Aires (Medrano)', '2026-09-20', '19:15:00', 3, 8, 2, 
'Excelente ambiente de compañerismo en las cursadas vespertinas. Los docentes trabajan en la industria y transmiten problemáticas reales de ingeniería.', 16, 0);

INSERT INTO `experiencias` (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionAcademica`, `puntuacionClima`, `puntuacionInfraestructura`) VALUES
(27, 5, '2026-09-20', '19:15:00', 3, 8, 2, 'Excelente ambiente de compañerismo en las cursadas vespertinas. Los docentes trabajan en la industria y transmiten problemáticas reales de ingeniería.', 16, 4, 5, 4)
ON DUPLICATE KEY UPDATE `puntuacionAcademica` = VALUES(`puntuacionAcademica`), `puntuacionClima` = VALUES(`puntuacionClima`), `puntuacionInfraestructura` = VALUES(`puntuacionInfraestructura`);

-- ─── UNLP (id 4) -> Clima Excelente, Infra Regular -> AMARILLO (4.00) ─────────
INSERT IGNORE INTO `publicaciones` (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`) VALUES
(28, 8, 'Experiencia', 'Comunidad de Software Libre en Informática UNLP', '2026-09-21', '13:00:00', 4, 9, 3, 
'El centro de estudiantes y los grupos de investigación en algoritmos son súper activos. Falta inversión en refrigeración de aulas pero la calidad humana es inmejorable.', 19, 0);

INSERT INTO `experiencias` (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionAcademica`, `puntuacionClima`, `puntuacionInfraestructura`) VALUES
(28, 8, '2026-09-21', '13:00:00', 4, 9, 3, 'El centro de estudiantes y los grupos de investigación en algoritmos son súper activos. Falta inversión en refrigeración de aulas pero la calidad humana es inmejorable.', 19, 5, 5, 3)
ON DUPLICATE KEY UPDATE `puntuacionAcademica` = VALUES(`puntuacionAcademica`), `puntuacionClima` = VALUES(`puntuacionClima`), `puntuacionInfraestructura` = VALUES(`puntuacionInfraestructura`);

-- ─── UADE (id 6) -> Infraestructura Máxima, Rigor Medio -> AMARILLO (4.17) ───
INSERT IGNORE INTO `publicaciones` (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`) VALUES
(29, 10, 'Experiencia', 'Edificio y tecnología en Campus Monserrat UADE', '2026-09-23', '18:40:00', 6, 11, 7, 
'Aulas híbridas impecables, estacionamiento, comedores y biblioteca digital de vanguardia. La exigencia académica es accesible si llevas la materia al día.', 21, 0);

INSERT INTO `experiencias` (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionAcademica`, `puntuacionClima`, `puntuacionInfraestructura`) VALUES
(29, 10, '2026-09-23', '18:40:00', 6, 11, 7, 'Aulas híbridas impecables, estacionamiento, comedores y biblioteca digital de vanguardia. La exigencia académica es accesible si llevas la materia al día.', 21, 4, 4, 5)
ON DUPLICATE KEY UPDATE `puntuacionAcademica` = VALUES(`puntuacionAcademica`), `puntuacionClima` = VALUES(`puntuacionClima`), `puntuacionInfraestructura` = VALUES(`puntuacionInfraestructura`);

-- ─────────────────────────────────────────────────────────────────────────────
-- 5. RESPUESTAS Y DIÁLOGOS DE FEEDBACK (Para dar vida al Nivel 3)
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO `respuestas` (`idRespuesta`, `idPublicacion`, `idUsuario`, `fecha`, `hora`, `texto`) VALUES
(13, 18, 3, '2026-09-15', '12:00:00', '¡Totalmente de acuerdo! La práctica con casos de estudio empresariales en UAI marca la diferencia.'),
(14, 18, 5, '2026-09-15', '14:30:00', 'Confirmo, el enfoque en normalización y optimización de consultas SQL te abre puertas rápido.'),
(15, 21, 4, '2026-09-25', '14:20:00', 'El TP integrador se basa en clean architecture con controladores REST y capas de servicio desacopladas.'),
(16, 22, 11, '2026-09-08', '11:10:00', 'Coincido plenamente, en invierno las aulas de taller de la UNC son heladas. Ojalá eleven el reclamo al decanato.'),
(17, 24, 9, '2026-09-19', '19:00:00', 'En la librería técnica frente al Pabellón México hacen 15% de descuento con libreta universitaria.')
ON DUPLICATE KEY UPDATE `texto` = VALUES(`texto`);

SET FOREIGN_KEY_CHECKS = 1;
