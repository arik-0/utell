-- --------------------------------------------------------
-- Script de Datos de Prueba (Mock Data) para U-Tell
-- CORREGIDO para el schema real de u-tell.sql
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Países (idPais, nombre)
-- --------------------------------------------------------
INSERT IGNORE INTO `paises` (`idPais`, `nombre`) VALUES
(1, 'Argentina'),
(2, 'Uruguay'),
(3, 'Chile');

-- --------------------------------------------------------
-- Provincias (idProvincia, idPais, nombre)
-- --------------------------------------------------------
INSERT IGNORE INTO `provincias` (`idProvincia`, `idPais`, `nombre`) VALUES
(1, 1, 'Santa Fe'),
(2, 1, 'Buenos Aires'),
(3, 1, 'Ciudad Autónoma de Buenos Aires');

-- --------------------------------------------------------
-- Ciudades (idCiudad, idProvincia, nombre)
-- --------------------------------------------------------
INSERT IGNORE INTO `ciudades` (`idCiudad`, `idProvincia`, `nombre`) VALUES
(1, 1, 'Rosario'),
(2, 1, 'Santa Fe Capital'),
(3, 2, 'La Plata'),
(4, 2, 'Mar del Plata'),
(5, 3, 'CABA');

-- --------------------------------------------------------
-- Roles (idRol, nombreRol)
-- La tabla "roles" SÍ existe — la FK en usuarios la referencia
-- --------------------------------------------------------
INSERT IGNORE INTO `roles` (`idRol`, `nombreRol`) VALUES
(1, 'SuperAdmin'),
(2, 'Estudiante'),
(3, 'Docente'),
(4, 'Moderador'),
(5, 'Universidad');

-- --------------------------------------------------------
-- Universidades (idUniversidad, nombre, email, password, celular, estado)
-- --------------------------------------------------------
INSERT IGNORE INTO `universidades` (`idUniversidad`, `nombre`, `email`, `password`, `celular`, `estado`) VALUES
(1, 'Universidad Nacional de La Plata', 'info@unlp.edu.ar', '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 221 644-7000', 'activo'),
(2, 'Universidad de Buenos Aires',      'info@rec.uba.ar',  '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 11 5285-5000', 'activo'),
(3, 'Universidad Nacional de Córdoba',  'info@unc.edu.ar',  '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 351 535-3700', 'activo');

-- --------------------------------------------------------
-- Sedes (idSede, idUniversidad, idCiudad, nombre, estado)
-- ❌ NO tiene columna "password"
-- --------------------------------------------------------
INSERT IGNORE INTO `sedes` (`idSede`, `idUniversidad`, `idCiudad`, `nombre`, `estado`) VALUES
(1, 1, 3, 'Sede Central UNLP',          'activo'),
(2, 2, 5, 'Facultad de Ingeniería UBA', 'activo'),
(3, 3, 6, 'Ciudad Universitaria UNC',   'activo');
-- idCiudad=6 (Córdoba Capital) ya existe si importaste u-tell.sql primero.
-- Si no, agregá: INSERT IGNORE INTO ciudades (idCiudad,idProvincia,nombre) VALUES (6,2,'Córdoba Capital');

-- --------------------------------------------------------
-- Carreras (idCarrera, nombre)
-- --------------------------------------------------------
INSERT IGNORE INTO `carreras` (`idCarrera`, `nombre`) VALUES
(1, 'Medicina'),
(2, 'Ingeniería en Sistemas de Información'),
(3, 'Licenciatura en Ciencias de la Computación'),
(4, 'Abogacía'),
(5, 'Arquitectura y Urbanismo');

-- --------------------------------------------------------
-- Oferta Académica (idCarrera, idSede, fechaImplementacion, ...)
-- --------------------------------------------------------
INSERT IGNORE INTO `oferta_academica` (`idCarrera`, `idSede`, `fechaImplementacion`) VALUES
(1, 1, '2010-03-01'),
(2, 1, '1990-03-01'),
(2, 2, '2015-04-01'),
(4, 3, '1980-03-01');

-- --------------------------------------------------------
-- Usuarios
-- Columnas reales: idUsuario, nombre, apellido, fNac, email, password,
--   fotoPerfil, celular, tipoPerfil, descripcion, trayectoria,
--   idCiudad, idUniversidad, idCarrera, idRol
-- IDs desde 11 para no chocar con los del u-tell.sql original
-- Contraseña "123456" con bcrypt
-- --------------------------------------------------------
INSERT IGNORE INTO `usuarios` (`idUsuario`, `nombre`, `apellido`, `fNac`, `email`, `password`, `tipoPerfil`, `idCiudad`, `idUniversidad`, `idCarrera`, `idRol`) VALUES
(11, 'Juan',   'Pérez', '2001-05-15', 'juan.perez@email.com',   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'Estudiante', 3, 1, 2, 2),
(12, 'María',  'Gómez', '1999-08-22', 'maria.gomez@email.com',  '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'Estudiante', 4, 2, 4, 2),
(13, 'Carlos', 'López', '2000-11-10', 'carlos.lopez@email.com', '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'Estudiante', 5, 3, 2, 2);

-- --------------------------------------------------------
-- Publicaciones
-- ❌ "consultas" y "experiencias" NO son tablas separadas
-- ✅ Existe UNA tabla "publicaciones" con tipoPublicacion = 'Consulta' | 'Experiencia'
-- Columnas: idPublicacion, idUsuario, tipoPublicacion, titulo, fecha, hora,
--           idUniversidad, idSede, idCarrera, texto, likes, resuelta
-- --------------------------------------------------------

-- Consultas
INSERT IGNORE INTO `publicaciones`
  (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`)
VALUES
(11, 11, 'Consulta', 'Plan de estudios actualizado',   '2026-07-10', '10:30:00', 1, 1, 2,
 '¿Alguien sabe si el plan de estudios de Ingeniería en Sistemas está actualizado a 2026?', 5, 0),
(12, 12, 'Consulta', 'Inscripción libre primer año',    '2026-07-12', '15:45:00', 3, 3, 4,
 '¿Es muy difícil conseguir vacante para rendir libre en primer año de Abogacía?', 12, 0);

-- Experiencias
INSERT IGNORE INTO `publicaciones`
  (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`)
VALUES
(13, 13, 'Experiencia', 'Excelente carrera de Sistemas', '2026-06-20', '14:00:00', 1, 1, 2,
 'Excelente carrera, los profesores son muy dedicados. Las aulas de laboratorio tienen máquinas nuevas.', 20, 0),
(14, 12, 'Experiencia', 'Mi experiencia en Abogacía UNC', '2026-06-25', '16:30:00', 3, 3, 4,
 'La experiencia es buena pero la burocracia para los trámites es un poco pesada.', 8, 0);

-- --------------------------------------------------------
-- Respuestas
-- ❌ La FK es idPublicacion, NO idConsulta
-- Columnas reales: idRespuesta, idPublicacion, idUsuario, fecha, hora, texto
-- --------------------------------------------------------
INSERT IGNORE INTO `respuestas` (`idRespuesta`, `idPublicacion`, `idUsuario`, `fecha`, `hora`, `texto`) VALUES
(11, 11, 13, '2026-07-11', '09:00:00', 'Sí, lo actualizaron el año pasado, ahora tiene más materias de IA y Datos.'),
(12, 12, 11, '2026-07-13', '11:20:00', 'Depende del profesor, pero por lo general si te anotas con tiempo conseguís lugar.');

-- --------------------------------------------------------
-- Amigos
-- ❌ Las columnas NO son (idUsuario, idAmigo)
-- ✅ Son: idAmistad (AUTO), idSolicitante, idReceptor, fechaInicio, estado
-- --------------------------------------------------------
INSERT IGNORE INTO `amigos` (`idSolicitante`, `idReceptor`, `fechaInicio`, `estado`) VALUES
(11, 12, '2026-07-20', 'aceptada'),
(11, 13, '2026-07-21', 'aceptada'),
(13, 11, '2026-07-22', 'pendiente');

SET FOREIGN_KEY_CHECKS=1;
