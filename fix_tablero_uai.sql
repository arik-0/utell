-- ══════════════════════════════════════════════════════════════════════════════
-- FIX TABLERO UAI & REPUTACIÓN INSTITUCIONAL (U-TELL)
-- Ejecutar este script en phpMyAdmin (Alwaysdata / Localhost) sobre la BD 'utell_db' o 'u-tell'
-- ══════════════════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Crear tabla 'experiencias' si no existe
CREATE TABLE IF NOT EXISTS `experiencias` (
  `idExperiencia` int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `idUniversidad` int(11) NOT NULL,
  `idSede` int(11) NOT NULL,
  `idCarrera` int(11) NOT NULL,
  `texto` text NOT NULL,
  `likes` int(11) DEFAULT 0,
  `puntuacionClima` int(11) NOT NULL DEFAULT 5,
  `puntuacionInfraestructura` int(11) NOT NULL DEFAULT 5,
  `puntuacionAcademica` int(11) NOT NULL DEFAULT 5,
  PRIMARY KEY (`idExperiencia`),
  KEY `fk_exp_usuario` (`idUsuario`),
  KEY `fk_exp_uni` (`idUniversidad`),
  KEY `fk_exp_sede` (`idSede`),
  KEY `fk_exp_carrera` (`idCarrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Crear tabla 'consultas' si no existe
CREATE TABLE IF NOT EXISTS `consultas` (
  `idConsulta` int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `idUniversidad` int(11) NOT NULL,
  `idSede` int(11) NOT NULL,
  `idCarrera` int(11) NOT NULL,
  `texto` text NOT NULL,
  `likes` int(11) DEFAULT 0,
  PRIMARY KEY (`idConsulta`),
  KEY `fk_con_usuario` (`idUsuario`),
  KEY `fk_con_uni` (`idUniversidad`),
  KEY `fk_con_sede` (`idSede`),
  KEY `fk_con_carrera` (`idCarrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Migrar / Sincronizar las publicaciones tipo 'Experiencia' existentes a la tabla 'experiencias'
INSERT IGNORE INTO `experiencias`
  (`idExperiencia`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `puntuacionClima`, `puntuacionInfraestructura`, `puntuacionAcademica`)
SELECT 
  p.idPublicacion, 
  p.idUsuario, 
  p.fecha, 
  p.hora,
  COALESCE(p.idUniversidad, 1),
  COALESCE(p.idSede, 1),
  COALESCE(p.idCarrera, 1),
  p.texto,
  COALESCE(p.likes, 0),
  5, 4, 5
FROM `publicaciones` p
WHERE p.tipoPublicacion = 'Experiencia';

-- 4. Migrar / Sincronizar las publicaciones tipo 'Consulta' existentes a la tabla 'consultas'
INSERT IGNORE INTO `consultas`
  (`idConsulta`, `idUsuario`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`)
SELECT 
  p.idPublicacion, 
  p.idUsuario, 
  p.fecha, 
  p.hora,
  COALESCE(p.idUniversidad, 1),
  COALESCE(p.idSede, 1),
  COALESCE(p.idCarrera, 1),
  p.texto,
  COALESCE(p.likes, 0)
FROM `publicaciones` p
WHERE p.tipoPublicacion = 'Consulta';

SET FOREIGN_KEY_CHECKS = 1;
