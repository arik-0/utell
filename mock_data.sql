-- --------------------------------------------------------
-- Script de Datos de Prueba (Mock Data) para U-Tell
-- --------------------------------------------------------

-- Insertar Roles
INSERT INTO `roles` (`idRol`, `nombreRol`) VALUES
(1, 'Administrador'),
(2, 'Estudiante'),
(3, 'Profesor');

-- Insertar Países
INSERT INTO `paises` (`idPais`, `nombre`) VALUES
(1, 'Argentina'),
(2, 'Uruguay'),
(3, 'Chile');

-- Insertar Provincias
INSERT INTO `provincias` (`idProvincia`, `idPais`, `nombre`) VALUES
(1, 1, 'Buenos Aires'),
(2, 1, 'Córdoba'),
(3, 1, 'Santa Fe');

-- Insertar Ciudades
INSERT INTO `ciudades` (`idCiudad`, `idProvincia`, `nombre`) VALUES
(1, 1, 'La Plata'),
(2, 1, 'Mar del Plata'),
(3, 1, 'Tandil'),
(4, 2, 'Córdoba Capital'),
(5, 3, 'Rosario');

-- Insertar Usuarios (Contraseña por defecto: 123456 en MD5 o similar. Usaremos un hash dummy o texto plano según cómo lo maneje el sistema, pondremos un hash dummy simple para prueba)
INSERT INTO `usuarios` (`idUsuario`, `nombre`, `apellido`, `fNac`, `email`, `password`, `tipoPerfil`, `idCiudad`, `idRol`) VALUES
(1, 'Juan', 'Pérez', '2001-05-15', 'juan.perez@email.com', '$2y$10$M.iTnYeZpov/Ocdb5q68o.9nAO6Qnmvpom2YiN6cNZACtbqGhGj0W', 'Estudiante', 1, 2),
(2, 'María', 'Gómez', '1999-08-22', 'maria.gomez@email.com', '$2y$10$M.iTnYeZpov/Ocdb5q68o.9nAO6Qnmvpom2YiN6cNZACtbqGhGj0W', 'Estudiante', 2, 2),
(3, 'Carlos', 'López', '2000-11-10', 'carlos.lopez@email.com', '$2y$10$M.iTnYeZpov/Ocdb5q68o.9nAO6Qnmvpom2YiN6cNZACtbqGhGj0W', 'Estudiante', 4, 2);

-- Insertar Universidades
INSERT INTO `universidades` (`idUniversidad`, `nombre`, `email`, `password`, `estado`) VALUES
(1, 'Universidad Nacional de La Plata', 'info@unlp.edu.ar', 'passunlp', 'activo'),
(2, 'Universidad de Buenos Aires', 'info@uba.ar', 'passuba', 'activo'),
(3, 'Universidad Nacional de Córdoba', 'info@unc.edu.ar', 'passunc', 'activo');

-- Insertar Sedes
INSERT INTO `sedes` (`idSede`, `idUniversidad`, `idCiudad`, `nombre`, `password`, `estado`) VALUES
(1, 1, 1, 'Sede Central UNLP', 'sedepass1', 'activo'),
(2, 2, 1, 'Facultad de Ingeniería UBA', 'sedepass2', 'activo'),
(3, 3, 4, 'Ciudad Universitaria UNC', 'sedepass3', 'activo');

-- Insertar Carreras
INSERT INTO `carreras` (`idCarrera`, `nombre`) VALUES
(1, 'Ingeniería en Sistemas de Información'),
(2, 'Medicina'),
(3, 'Abogacía'),
(4, 'Arquitectura'),
(5, 'Licenciatura en Psicología');

-- Insertar Oferta Académica
INSERT INTO `oferta_academica` (`idCarrera`, `idSede`, `fechaImplementacion`) VALUES
(1, 1, '2010-03-01'),
(2, 1, '1990-03-01'),
(1, 2, '2015-04-01'),
(3, 3, '1980-03-01');

-- Insertar Consultas
INSERT INTO `consultas` (`idConsulta`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`) VALUES
(1, 1, '2026-07-10', '10:30:00', 1, 1, 1, '¿Alguien sabe si el plan de estudios de Ingeniería en Sistemas está actualizado a 2026?', 5),
(2, 2, '2026-07-12', '15:45:00', 3, 3, 3, '¿Es muy difícil conseguir vacante para rendir libre en primer año de Abogacía?', 12);

-- Insertar Respuestas a Consultas
INSERT INTO `respuestas` (`idRespuesta`, `idConsulta`, `idUsuario`, `fecha`, `hora`, `texto`) VALUES
(1, 1, 3, '2026-07-11', '09:00:00', 'Sí, lo actualizaron el año pasado, ahora tiene más materias de IA y Datos.'),
(2, 2, 1, '2026-07-13', '11:20:00', 'Depende del profesor, pero por lo general si te anotas con tiempo conseguís lugar.');

-- Insertar Experiencias
INSERT INTO `experiencias` (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionClima`, `puntuacionInfraestructura`, `puntuacionAcademica`) VALUES
(1, 3, '2026-06-20', '14:00:00', 1, 1, 1, 'Excelente carrera, los profesores son muy dedicados. Las aulas de laboratorio tienen máquinas nuevas.', 20, 5, 4, 5),
(2, 2, '2026-06-25', '16:30:00', 3, 3, 3, 'La experiencia es buena pero la burocracia para los trámites es un poco pesada.', 8, 4, 3, 4);

-- Insertar Amigos
INSERT INTO `amigos` (`idUsuario`, `idAmigo`) VALUES
(1, 2),
(2, 1),
(3, 1);
