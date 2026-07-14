-- Eliminar foreign keys que dependen de consultas
ALTER TABLE `favoritos` DROP FOREIGN KEY `fk_fav_consulta`;
ALTER TABLE `respuestas` DROP FOREIGN KEY `fk_resp_consulta`;

-- Renombrar columnas en favoritos y respuestas
ALTER TABLE `favoritos` CHANGE `idConsulta` `idPublicacion` int(11) NOT NULL;
ALTER TABLE `respuestas` CHANGE `idConsulta` `idPublicacion` int(11) NOT NULL;

-- Crear tabla publicaciones
CREATE TABLE IF NOT EXISTS `publicaciones` (
  `idPublicacion` int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario` int(11) NOT NULL,
  `tipoPublicacion` varchar(50) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `idUniversidad` int(11) DEFAULT NULL,
  `idSede` int(11) DEFAULT NULL,
  `idCarrera` int(11) DEFAULT NULL,
  `texto` text NOT NULL,
  `likes` int(11) DEFAULT 0,
  `resuelta` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`idPublicacion`),
  KEY `fk_pub_usuario` (`idUsuario`),
  CONSTRAINT `fk_pub_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mover datos si es necesario, o solo insertar nuevos datos de mock
INSERT INTO `publicaciones` (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`) VALUES
(1, 1, 'Consulta', 'Duda plan estudios', '2026-07-10', '10:30:00', 1, 1, 1, '¿Alguien sabe si el plan de estudios de Ingeniería en Sistemas está actualizado a 2026?', 5),
(2, 2, 'Consulta', 'Vacantes libres', '2026-07-12', '15:45:00', 3, 3, 3, '¿Es muy difícil conseguir vacante para rendir libre en primer año de Abogacía?', 12),
(3, 3, 'Experiencia', 'Excelente carrera', '2026-06-20', '14:00:00', 1, 1, 1, 'Excelente carrera, los profesores son muy dedicados. Las aulas de laboratorio tienen máquinas nuevas.', 20),
(4, 2, 'Experiencia', 'Burocracia', '2026-06-25', '16:30:00', 3, 3, 3, 'La experiencia es buena pero la burocracia para los trámites es un poco pesada.', 8);

-- Asegurarnos de que las respuestas apunten a algo válido
TRUNCATE TABLE `respuestas`;
INSERT INTO `respuestas` (`idRespuesta`, `idPublicacion`, `idUsuario`, `fecha`, `hora`, `texto`) VALUES
(1, 1, 3, '2026-07-11', '09:00:00', 'Sí, lo actualizaron el año pasado, ahora tiene más materias de IA y Datos.'),
(2, 2, 1, '2026-07-13', '11:20:00', 'Depende del profesor, pero por lo general si te anotas con tiempo conseguís lugar.');

-- Recrear foreign keys
ALTER TABLE `favoritos` ADD CONSTRAINT `fk_fav_publicacion` FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE;
ALTER TABLE `respuestas` ADD CONSTRAINT `fk_resp_publicacion` FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE;
