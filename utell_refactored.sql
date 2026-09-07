-- ============================================================
-- U-Tell — Refactor Schema + Mock Data Completo
-- Cambios principales:
--   1. usuarios ya NO tiene idUniversidad ni idCarrera
--   2. Nueva tabla: inscripciones (relación académica usuario↔uni/carrera)
--   3. Nueva tabla: grupos_permiso_permisos (grupos↔permisos del sistema)
--   4. FKs correctas en detalle_grupo_permiso
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLAS BASE (sin dependencias)
-- ============================================================

DROP TABLE IF EXISTS `paises`;
CREATE TABLE `paises` (
  `idPais` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  PRIMARY KEY (`idPais`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `paises` VALUES
(1,'Argentina'),(2,'Uruguay'),(3,'Chile'),
(4,'Colombia'),(5,'México'),(6,'España');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `provincias`;
CREATE TABLE `provincias` (
  `idProvincia` int(11) NOT NULL AUTO_INCREMENT,
  `idPais`      int(11) NOT NULL,
  `nombre`      varchar(255) NOT NULL,
  PRIMARY KEY (`idProvincia`),
  KEY `fk_provincia_pais` (`idPais`),
  CONSTRAINT `fk_provincia_pais`
    FOREIGN KEY (`idPais`) REFERENCES `paises` (`idPais`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `provincias` VALUES
(1,1,'Santa Fe'),(2,1,'Buenos Aires'),(3,1,'Ciudad Autónoma de Buenos Aires'),
(4,1,'Córdoba'),(5,1,'Mendoza'),(6,1,'Tucumán'),
(7,2,'Montevideo'),(8,3,'Región Metropolitana');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `ciudades`;
CREATE TABLE `ciudades` (
  `idCiudad`    int(11) NOT NULL AUTO_INCREMENT,
  `idProvincia` int(11) NOT NULL,
  `nombre`      varchar(255) NOT NULL,
  PRIMARY KEY (`idCiudad`),
  KEY `fk_ciudad_provincia` (`idProvincia`),
  CONSTRAINT `fk_ciudad_provincia`
    FOREIGN KEY (`idProvincia`) REFERENCES `provincias` (`idProvincia`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ciudades` VALUES
(1,1,'Rosario'),(2,1,'Santa Fe Capital'),(3,2,'La Plata'),
(4,2,'Mar del Plata'),(5,3,'CABA'),(6,4,'Córdoba Capital'),
(7,5,'Mendoza Capital'),(8,6,'San Miguel de Tucumán'),
(9,7,'Montevideo'),(10,8,'Santiago de Chile');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `idRol`     int(11) NOT NULL AUTO_INCREMENT,
  `nombreRol` varchar(100) NOT NULL,
  PRIMARY KEY (`idRol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` VALUES
(1,'Administrador'),(2,'Estudiante'),(3,'Docente'),(4,'Graduado');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `carreras`;
CREATE TABLE `carreras` (
  `idCarrera` int(11) NOT NULL AUTO_INCREMENT,
  `nombre`    varchar(255) NOT NULL,
  PRIMARY KEY (`idCarrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `carreras` VALUES
(1,'Medicina'),(2,'Ingeniería en Sistemas de Información'),
(3,'Licenciatura en Ciencias de la Computación'),(4,'Abogacía'),
(5,'Arquitectura y Urbanismo'),(6,'Licenciatura en Psicología'),
(7,'Licenciatura en Administración de Empresas'),
(8,'Contador Público'),(9,'Bioquímica'),(10,'Diseño Gráfico');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `universidades`;
CREATE TABLE `universidades` (
  `idUniversidad` int(11) NOT NULL AUTO_INCREMENT,
  `nombre`  varchar(255) NOT NULL,
  `email`   varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `celular` varchar(255) DEFAULT NULL,
  `estado`  varchar(20) DEFAULT 'activo',
  PRIMARY KEY (`idUniversidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `universidades` VALUES
(1,'Universidad Nacional de Rosario','contacto@unr.edu.ar',
   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O','+54 341 4802620','activo'),
(2,'Universidad de Buenos Aires','info@rec.uba.ar',
   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O','+54 11 5285-5000','activo'),
(3,'Universidad Tecnológica Nacional','rectorado@utn.edu.ar',
   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O','+54 11 4867-7500','activo'),
(4,'Universidad Nacional de La Plata','presidencia@unlp.edu.ar',
   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O','+54 221 644-7000','activo'),
(5,'Universidad Nacional de Córdoba','info@unc.edu.ar',
   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O','+54 351 535-3700','activo'),
(6,'Universidad Argentina de la Empresa','informes@uade.edu.ar',
   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O','+54 11 4000-7600','activo'),
(7,'Instituto Tecnológico de Buenos Aires','informes@itba.edu.ar',
   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O','+54 11 3724-6900','activo'),
(8,'Universidad Abierta Interamericana','contacto@uai.edu.ar',
   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O','+54 11 4342-7788','activo');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `sedes`;
CREATE TABLE `sedes` (
  `idSede`        int(11) NOT NULL AUTO_INCREMENT,
  `idUniversidad` int(11) NOT NULL,
  `idCiudad`      int(11) NOT NULL,
  `nombre`        varchar(255) NOT NULL,
  `descripcion`   text DEFAULT NULL,
  `email`         varchar(255) DEFAULT NULL,
  `web`           varchar(255) DEFAULT NULL,
  `telefono`      varchar(255) DEFAULT NULL,
  `estado`        varchar(20) DEFAULT 'activo',
  PRIMARY KEY (`idSede`),
  KEY `fk_sede_uni`    (`idUniversidad`),
  KEY `fk_sede_ciudad` (`idCiudad`),
  CONSTRAINT `fk_sede_uni`
    FOREIGN KEY (`idUniversidad`) REFERENCES `universidades` (`idUniversidad`),
  CONSTRAINT `fk_sede_ciudad`
    FOREIGN KEY (`idCiudad`) REFERENCES `ciudades` (`idCiudad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sedes` (`idSede`,`idUniversidad`,`idCiudad`,`nombre`,`estado`) VALUES
(1,1,1,'Facultad de Ciencias Médicas UNR','activo'),
(2,1,1,'FCEIA UNR','activo'),
(3,1,1,'Facultad de Derecho UNR','activo'),
(4,2,5,'Facultad de Derecho UBA','activo'),
(5,2,5,'Facultad de Ciencias Económicas UBA','activo'),
(6,2,5,'Facultad de Medicina UBA','activo'),
(7,3,1,'UTN FRRo','activo'),
(8,3,5,'UTN FRBA','activo'),
(9,4,3,'Facultad de Informática UNLP','activo'),
(10,5,6,'Ciudad Universitaria UNC','activo'),
(11,6,5,'Sede Central UADE','activo'),
(12,7,5,'Sede Central ITBA','activo');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
  `idPermiso`    int(11) NOT NULL AUTO_INCREMENT,
  `nombre`       varchar(255) NOT NULL,
  `codigoAccion` varchar(255) NOT NULL,
  PRIMARY KEY (`idPermiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `permisos` VALUES
(1,'Crear Publicaciones','POST_CREATE'),
(2,'Responder Consultas','POST_REPLY'),
(3,'Calificar Instituciones','RATE_UNIVERSITY'),
(4,'Moderar Comentarios','MODERATE_COMMENT'),
(5,'Gestionar Usuarios','MANAGE_USERS'),
(6,'Enviar Mensajes Directos','SEND_DIRECT_MESSAGE');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `grupos_permiso`;
CREATE TABLE `grupos_permiso` (
  `idGrupoPermiso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre`         varchar(255) NOT NULL,
  `descripcion`    text DEFAULT NULL,
  PRIMARY KEY (`idGrupoPermiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `grupos_permiso` VALUES
(1,'Administradores Generales','Acceso total y control de moderación institucional'),
(2,'Moderadores de Comunidad','Capacidad de editar o cerrar publicaciones inapropiadas'),
(3,'Estudiantes Verificados','Alumnos con credencial y legajo verificado'),
(4,'Cuerpo Docente y Académico','Profesores e investigadores universitarios');

-- ============================================================
-- TABLA NUEVA: grupos_permiso_permisos
-- Vincula grupos con los permisos del sistema
-- ============================================================
DROP TABLE IF EXISTS `grupos_permiso_permisos`;
CREATE TABLE `grupos_permiso_permisos` (
  `idGrupoPermiso` int(11) NOT NULL,
  `idPermiso`      int(11) NOT NULL,
  PRIMARY KEY (`idGrupoPermiso`, `idPermiso`),
  KEY `fk_gpp_permiso` (`idPermiso`),
  CONSTRAINT `fk_gpp_grupo`
    FOREIGN KEY (`idGrupoPermiso`) REFERENCES `grupos_permiso` (`idGrupoPermiso`) ON DELETE CASCADE,
  CONSTRAINT `fk_gpp_permiso`
    FOREIGN KEY (`idPermiso`)      REFERENCES `permisos` (`idPermiso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `grupos_permiso_permisos` VALUES
-- Administradores: todos los permisos
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),
-- Moderadores: crear, responder, calificar, moderar, mensajes
(2,1),(2,2),(2,3),(2,4),(2,6),
-- Estudiantes Verificados: crear, responder, calificar, mensajes
(3,1),(3,2),(3,3),(3,6),
-- Cuerpo Docente: crear, responder, calificar, mensajes
(4,1),(4,2),(4,3),(4,6);

-- ============================================================
-- TABLA USUARIOS — REFACTORIZADA
-- Ya NO tiene idUniversidad ni idCarrera
-- Esos datos pasan a la tabla inscripciones
-- ============================================================
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `idUsuario`   int(11) NOT NULL AUTO_INCREMENT,
  `nombre`      varchar(255) NOT NULL,
  `apellido`    varchar(255) NOT NULL,
  `fNac`        date NOT NULL,
  `email`       varchar(255) NOT NULL,
  `password`    varchar(255) NOT NULL,
  `fotoPerfil`  varchar(255) DEFAULT NULL,
  `celular`     varchar(255) DEFAULT NULL,
  `tipoPerfil`  varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `trayectoria` text DEFAULT NULL,
  `idCiudad`    int(11) NOT NULL,
  `idRol`       int(11) NOT NULL DEFAULT 2,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_usuario_ciudad` (`idCiudad`),
  KEY `fk_usuario_rol`    (`idRol`),
  CONSTRAINT `fk_usuario_ciudad`
    FOREIGN KEY (`idCiudad`) REFERENCES `ciudades` (`idCiudad`),
  CONSTRAINT `fk_usuario_rol`
    FOREIGN KEY (`idRol`) REFERENCES `roles` (`idRol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Contraseña de todos: "123456" (hash bcrypt)
INSERT INTO `usuarios`
  (`idUsuario`,`nombre`,`apellido`,`fNac`,`email`,`password`,`fotoPerfil`,`celular`,`tipoPerfil`,`descripcion`,`trayectoria`,`idCiudad`,`idRol`)
VALUES
(3,'Tamara','Ajun','1995-04-12','tamaraajun@hotmail.com',
 '$2y$10$eJcHOfO0pdXH41mCywxE2OiiMzB80YD6OT8moRRUJZzZqyOqdRn7O',
 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&auto=format&fit=crop&q=80',
 '+54 341 555-0101','Docente','Docente e investigadora en Ciencias Médicas.',
 'Docente titular en UNR con 8 años de trayectoria.',1,3),
(4,'Arik','Yelin','2001-08-20','arikayelin@gmail.com',
 '$2y$10$RLtrsXNWWlnuh3RpzMxFnOTr/zMtYYL0DCa/J3voI6deTgjtHfgmO',
 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80',
 '+54 341 555-0102','Estudiante','Estudiante avanzado de Medicina.',
 '4to año de la carrera de Medicina en UNR.',1,2),
(5,'Lucas','Benítez','2002-03-14','lucas.benitez@utn.edu.ar',
 '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O',
 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&auto=format&fit=crop&q=80',
 '+54 341 555-0103','Estudiante','Desarrollador backend y entusiasta del software libre.',
 '3er año de Ingeniería en Sistemas.',1,2),
(6,'Camila','Ríos','2000-11-28','camila.rios@uba.ar',
 '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O',
 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&auto=format&fit=crop&q=80',
 '+54 11 555-0104','Estudiante','Ayudante de cátedra en Derecho Constitucional.',
 '4to año en la Facultad de Derecho UBA.',5,2),
(7,'Martín','Gómez','1998-06-19','martin.gomez@itba.edu.ar',
 '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O',
 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&auto=format&fit=crop&q=80',
 '+54 11 555-0105','Graduado','Tech Lead en empresa de tecnología.',
 'Graduado con honores en 2023.',5,4),
(8,'Sofía','Fernández','2003-01-10','sofia.fernandez@info.unlp.edu.ar',
 '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O',
 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=200&auto=format&fit=crop&q=80',
 '+54 221 555-0106','Estudiante','Apasionada por algoritmos y machine learning.',
 '2do año de Ciencias de la Computación.',3,2),
(9,'Valentina','Rossi','2001-09-05','valentina.rossi@unc.edu.ar',
 '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O',
 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&auto=format&fit=crop&q=80',
 '+54 351 555-0107','Estudiante','Estudiando Arquitectura y Urbanismo en la UNC.',
 '3er año en Ciudad Universitaria Córdoba.',6,2),
(10,'Nicolás','Castro','1999-12-22','nicolas.castro@uade.edu.ar',
 '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O',
 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=200&auto=format&fit=crop&q=80',
 '+54 11 555-0108','Estudiante','Emprendedor en e-commerce.',
 'Último año de Administración.',5,2);

-- ============================================================
-- TABLA NUEVA: inscripciones
-- Intermediaria entre usuarios y su relación académica
-- Lo que antes vivía como columnas directas en usuarios
-- ============================================================
DROP TABLE IF EXISTS `inscripciones`;
CREATE TABLE `inscripciones` (
  `idInscripcion`  int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`      int(11) NOT NULL,
  `idUniversidad`  int(11) DEFAULT NULL,
  `idSede`         int(11) DEFAULT NULL,
  `idCarrera`      int(11) DEFAULT NULL,
  `anioIngreso`    year    DEFAULT NULL,
  `estado`         varchar(20) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`idInscripcion`),
  KEY `fk_ins_usuario`     (`idUsuario`),
  KEY `fk_ins_universidad` (`idUniversidad`),
  KEY `fk_ins_sede`        (`idSede`),
  KEY `fk_ins_carrera`     (`idCarrera`),
  CONSTRAINT `fk_ins_usuario`
    FOREIGN KEY (`idUsuario`)     REFERENCES `usuarios`      (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_ins_universidad`
    FOREIGN KEY (`idUniversidad`) REFERENCES `universidades` (`idUniversidad`),
  CONSTRAINT `fk_ins_sede`
    FOREIGN KEY (`idSede`)        REFERENCES `sedes`         (`idSede`),
  CONSTRAINT `fk_ins_carrera`
    FOREIGN KEY (`idCarrera`)     REFERENCES `carreras`      (`idCarrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos académicos de cada usuario (antes estaban en columnas de usuarios)
INSERT INTO `inscripciones` (`idUsuario`,`idUniversidad`,`idSede`,`idCarrera`,`anioIngreso`,`estado`) VALUES
(3,  1,  1, 1, 2017, 'activo'),  -- Tamara: Medicina UNR
(4,  1,  1, 1, 2021, 'activo'),  -- Arik:   Medicina UNR
(5,  3,  7, 2, 2022, 'activo'),  -- Lucas:  Ing. Sistemas UTN FRRo
(6,  2,  4, 4, 2020, 'activo'),  -- Camila: Abogacía UBA
(7,  7, 12, 2, 2018, 'activo'),  -- Martín: Ing. Informática ITBA
(8,  4,  9, 3, 2023, 'activo'),  -- Sofía:  Cs. Computación UNLP
(9,  5, 10, 5, 2022, 'activo'),  -- Valentina: Arquitectura UNC
(10, 6, 11, 7, 2020, 'activo');  -- Nicolás: Administración UADE

-- ============================================================
-- MÓDULO DE SEGURIDAD: asignación usuario → grupo
-- ============================================================
DROP TABLE IF EXISTS `detalle_grupo_permiso`;
CREATE TABLE `detalle_grupo_permiso` (
  `idDetalle`      int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`      int(11) NOT NULL,
  `idGrupoPermiso` int(11) NOT NULL,
  `fechaAsignacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idDetalle`),
  KEY `fk_dgp_usuario` (`idUsuario`),
  KEY `fk_dgp_grupo`   (`idGrupoPermiso`),
  CONSTRAINT `fk_dgp_usuario`
    FOREIGN KEY (`idUsuario`)      REFERENCES `usuarios`       (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_dgp_grupo`
    FOREIGN KEY (`idGrupoPermiso`) REFERENCES `grupos_permiso` (`idGrupoPermiso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `detalle_grupo_permiso` (`idUsuario`,`idGrupoPermiso`,`fechaAsignacion`) VALUES
(3,  4, '2026-08-24 20:12:25'),  -- Tamara  → Cuerpo Docente
(4,  3, '2026-08-24 20:12:25'),  -- Arik    → Estudiantes Verificados
(5,  3, '2026-08-24 20:12:25'),  -- Lucas   → Estudiantes Verificados
(6,  3, '2026-08-24 20:12:25'),  -- Camila  → Estudiantes Verificados
(7,  1, '2026-08-24 20:12:25'),  -- Martín  → Administradores Generales
(8,  3, '2026-08-24 20:12:25'),  -- Sofía   → Estudiantes Verificados
(9,  3, '2026-08-24 20:12:25'),  -- Valentina → Estudiantes Verificados
(10, 3, '2026-08-24 20:12:25'); -- Nicolás  → Estudiantes Verificados

-- ============================================================
-- CONTENIDO: publicaciones, respuestas, etc.
-- ============================================================

DROP TABLE IF EXISTS `publicaciones`;
CREATE TABLE `publicaciones` (
  `idPublicacion`   int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`       int(11) NOT NULL,
  `tipoPublicacion` varchar(50) NOT NULL DEFAULT 'Consulta',
  `titulo`          varchar(255) DEFAULT NULL,
  `fecha`           date NOT NULL,
  `hora`            time NOT NULL,
  `idUniversidad`   int(11) DEFAULT NULL,
  `idSede`          int(11) DEFAULT NULL,
  `idCarrera`       int(11) DEFAULT NULL,
  `texto`           text NOT NULL,
  `likes`           int(11) DEFAULT 0,
  `resuelta`        tinyint(1) DEFAULT 0,
  PRIMARY KEY (`idPublicacion`),
  KEY `fk_pub_usuario` (`idUsuario`),
  CONSTRAINT `fk_pub_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `publicaciones`
  (`idPublicacion`,`idUsuario`,`tipoPublicacion`,`titulo`,`fecha`,`hora`,`idUniversidad`,`idSede`,`idCarrera`,`texto`,`likes`,`resuelta`)
VALUES
(1,3,'Consulta','Inscripción a guardias clínicas en UNR','2026-08-14','01:21:27',1,1,1,
 'Estimados alumnos: ¿Alguien sabe la fecha límite para entregar la planilla de rotación hospitalaria de 5to año?',4,1),
(2,3,'Experiencia','Mi balance como docente en Medicina UNR','2026-08-14','03:59:57',1,1,1,
 'La dedicación y el compromiso clínico de los alumnos en el Hospital Centenario es de excelencia.',8,0),
(3,4,'Consulta','Apuntes de Farmacología Médica 1','2026-08-17','22:27:47',1,1,1,
 '¿Quién tiene el resumen actualizado con las modificaciones del nuevo protocolo de antibioticoterapia?',3,0),
(4,5,'Experiencia','Cursada de Sistemas Operativos en UTN Rosario','2026-08-18','11:15:00',3,7,2,
 'Los laboratorios de Linux y los trabajos prácticos de concurrencia y memoria virtual son muy completos.',12,0),
(5,5,'Consulta','Electivas recomendadas para 4to año en UTN','2026-08-19','16:40:00',3,7,2,
 '¿Qué opinan entre Inteligencia Artificial aplicada vs. Arquitectura de Software?',6,0),
(6,6,'Experiencia','Prácticas profesionales en UBA Derecho','2026-08-20','09:30:00',2,4,4,
 'El patrocinio jurídico gratuito de la UBA te da una experiencia inigualable en litigio real.',15,0),
(7,7,'Experiencia','Balance académico y salida laboral ITBA','2026-08-21','14:20:00',7,12,2,
 'La red de contactos y los convenios con empresas internacionales son extraordinarios.',19,0),
(8,8,'Experiencia','La comunidad en Informática UNLP','2026-08-22','10:05:00',4,9,3,
 'El clima estudiantil y el centro de estudiantes son súper colaborativos.',9,0),
(9,9,'Consulta','Materiales para Taller de Maquetas en UNC','2026-08-23','15:10:00',5,10,5,
 '¿Dónde consiguen cartón gris y madera balsa a buen precio cerca de Ciudad Universitaria?',2,0),
(10,10,'Experiencia','Cursada nocturna y networking en UADE','2026-08-24','12:00:00',6,11,7,
 'Muy buenas instalaciones y flexibilidad horaria para quienes trabajamos en horario comercial.',7,0);

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `respuestas`;
CREATE TABLE `respuestas` (
  `idRespuesta`  int(11) NOT NULL AUTO_INCREMENT,
  `idPublicacion` int(11) NOT NULL,
  `idUsuario`    int(11) NOT NULL,
  `fecha`        date NOT NULL,
  `hora`         time NOT NULL,
  `texto`        text NOT NULL,
  PRIMARY KEY (`idRespuesta`),
  KEY `fk_resp_publicacion` (`idPublicacion`),
  KEY `fk_resp_usuario`     (`idUsuario`),
  CONSTRAINT `fk_resp_publicacion`
    FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE,
  CONSTRAINT `fk_resp_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `respuestas` (`idRespuesta`,`idPublicacion`,`idUsuario`,`fecha`,`hora`,`texto`) VALUES
(1,1,5,'2026-08-15','09:00:00','El plazo vence el 30 de agosto, lo publicaron en el SIU.'),
(2,3,4,'2026-08-18','11:20:00','Yo tengo el PDF actualizado, te lo mando por mensaje.'),
(3,5,7,'2026-08-20','08:30:00','IA aplicada tiene mejor salida laboral hoy en día, pero Arquitectura te da más base.'),
(4,9,6,'2026-08-24','10:00:00','En el Corralón del Estudiante sobre Av. Vélez Sársfield consiguen todo barato.'),
(5,1,3,'2026-08-16','14:15:00','El plazo real es el viernes 1ro de septiembre. El SIU tiene un error en la fecha.');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `amigos`;
CREATE TABLE `amigos` (
  `idAmistad`    int(11) NOT NULL AUTO_INCREMENT,
  `idSolicitante` int(11) NOT NULL,
  `idReceptor`   int(11) NOT NULL,
  `fechaInicio`  date NOT NULL,
  `estado`       varchar(20) NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`idAmistad`),
  KEY `idx_solicitante` (`idSolicitante`),
  KEY `idx_receptor`    (`idReceptor`),
  CONSTRAINT `fk_amigo_solicitante`
    FOREIGN KEY (`idSolicitante`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_amigo_receptor`
    FOREIGN KEY (`idReceptor`)    REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `amigos` VALUES
(1,4,3,'2026-08-01','aceptada'),(2,4,5,'2026-08-05','aceptada'),
(3,4,8,'2026-08-10','aceptada'),(4,6,4,'2026-08-15','aceptada'),
(5,7,4,'2026-08-20','pendiente'),(6,9,4,'2026-08-22','pendiente'),
(7,5,8,'2026-08-02','aceptada'),(8,6,10,'2026-08-03','aceptada');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE `favoritos` (
  `idFavorito`   int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`    int(11) NOT NULL,
  `idPublicacion` int(11) NOT NULL,
  `fecha`        date NOT NULL,
  `hora`         time NOT NULL,
  `estado`       varchar(20) DEFAULT 'activo',
  PRIMARY KEY (`idFavorito`),
  KEY `fk_fav_usuario`     (`idUsuario`),
  KEY `fk_fav_publicacion` (`idPublicacion`),
  CONSTRAINT `fk_fav_usuario`
    FOREIGN KEY (`idUsuario`)     REFERENCES `usuarios`      (`idUsuario`),
  CONSTRAINT `fk_fav_publicacion`
    FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `favoritos` VALUES
(1,4,1,'2026-08-14','09:16:00','activo'),(2,4,2,'2026-08-14','10:02:00','activo'),
(3,3,3,'2026-08-17','23:06:00','activo'),(4,4,4,'2026-08-18','11:20:00','activo'),
(5,5,4,'2026-08-18','11:22:00','activo'),(6,4,6,'2026-08-20','10:00:00','activo'),
(7,4,7,'2026-08-21','14:30:00','activo');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `mensajedirecto`;
CREATE TABLE `mensajedirecto` (
  `idMensajeDirecto` int(11) NOT NULL AUTO_INCREMENT,
  `idEmisor`         int(11) NOT NULL,
  `idReceptor`       int(11) NOT NULL,
  `texto`            text NOT NULL,
  `fecha`            date NOT NULL,
  `hora`             time NOT NULL,
  `leido`            tinyint(1) DEFAULT 0,
  PRIMARY KEY (`idMensajeDirecto`),
  KEY `fk_msg_emisor`   (`idEmisor`),
  KEY `fk_msg_receptor` (`idReceptor`),
  CONSTRAINT `fk_msg_emisor`
    FOREIGN KEY (`idEmisor`)   REFERENCES `usuarios` (`idUsuario`),
  CONSTRAINT `fk_msg_receptor`
    FOREIGN KEY (`idReceptor`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `mensajedirecto` VALUES
(1,4,3,'Hola Tamara, ¿sabés cuándo salen los resultados del parcial?','2026-08-15','10:05:00',1),
(2,3,4,'Hola Arik, deberían estar el lunes que viene.','2026-08-15','11:30:00',1),
(3,5,4,'Bro, ¿tenes el link del repo de la materia?','2026-08-16','19:00:00',1),
(4,4,5,'Sí, te lo mando ahora por acá.','2026-08-16','19:15:00',0),
(5,8,9,'Valen! ¿Vas al evento de la facultad el viernes?','2026-08-20','14:00:00',0),
(6,9,8,'Sí! nos juntamos en la entrada a las 18hs','2026-08-20','15:30:00',0);

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `oferta_academica`;
CREATE TABLE `oferta_academica` (
  `idCarrera`           int(11) NOT NULL,
  `idSede`              int(11) NOT NULL,
  `fechaImplementacion` date DEFAULT NULL,
  `descripcion`         text DEFAULT NULL,
  `email`               varchar(255) DEFAULT NULL,
  `web`                 varchar(255) DEFAULT NULL,
  `telefono`            varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idCarrera`,`idSede`),
  KEY `fk_oa_sede`    (`idSede`),
  CONSTRAINT `fk_oa_carrera`
    FOREIGN KEY (`idCarrera`) REFERENCES `carreras` (`idCarrera`) ON UPDATE CASCADE,
  CONSTRAINT `fk_oa_sede`
    FOREIGN KEY (`idSede`)    REFERENCES `sedes`    (`idSede`)    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `oferta_academica` (`idCarrera`,`idSede`,`fechaImplementacion`) VALUES
(1,1,'1920-03-01'),(1,6,'1900-03-01'),
(2,3,'1985-03-15'),(2,7,'1984-04-01'),(2,8,'1980-03-01'),(2,12,'1975-03-01'),
(3,9,'1999-03-01'),(4,4,'1950-03-01'),
(7,5,'1960-03-01'),(7,11,'1990-03-01');

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `carrerassedes`;
CREATE TABLE `carrerassedes` (
  `idCarrera`   int(11) NOT NULL,
  `idSede`      int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `email`       varchar(255) DEFAULT NULL,
  `web`         varchar(255) DEFAULT NULL,
  `telefono`    varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idCarrera`,`idSede`),
  KEY `fk_cs_sede` (`idSede`),
  CONSTRAINT `fk_cs_carrera`
    FOREIGN KEY (`idCarrera`) REFERENCES `carreras` (`idCarrera`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cs_sede`
    FOREIGN KEY (`idSede`)    REFERENCES `sedes`    (`idSede`)    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `log_auditoria`;
CREATE TABLE `log_auditoria` (
  `idLog`      int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`  int(11) NOT NULL,
  `accion`     varchar(255) NOT NULL,
  `detalle`    text DEFAULT NULL,
  `fechaHora`  datetime NOT NULL DEFAULT current_timestamp(),
  `ip`         varchar(45) DEFAULT NULL,
  PRIMARY KEY (`idLog`),
  KEY `fk_auditoria_usuario` (`idUsuario`),
  CONSTRAINT `fk_auditoria_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;
