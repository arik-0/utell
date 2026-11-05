-- ============================================================================
-- U-TELL — ESQUEMA RELACIONAL UNIFICADO (01_schema.sql)
-- Motor: MySQL 8.0+ / MariaDB 10.4+ (InnoDB, UTF-8 utf8mb4)
-- Consolida: u-tell.sql, utell_refactored.sql, migracion_01..03 y fix_tablero
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- 1. GEOGRAFÍA NORMALIZADA (3FN): PAÍSES -> PROVINCIAS -> CIUDADES
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `paises`;
CREATE TABLE `paises` (
  `idPais` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  PRIMARY KEY (`idPais`),
  UNIQUE KEY `uq_pais_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `provincias`;
CREATE TABLE `provincias` (
  `idProvincia` INT(11) NOT NULL AUTO_INCREMENT,
  `idPais`      INT(11) NOT NULL,
  `nombre`      VARCHAR(150) NOT NULL,
  PRIMARY KEY (`idProvincia`),
  KEY `fk_provincia_pais` (`idPais`),
  CONSTRAINT `fk_provincia_pais`
    FOREIGN KEY (`idPais`) REFERENCES `paises` (`idPais`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ciudades`;
CREATE TABLE `ciudades` (
  `idCiudad`    INT(11) NOT NULL AUTO_INCREMENT,
  `idProvincia` INT(11) NOT NULL,
  `nombre`      VARCHAR(150) NOT NULL,
  PRIMARY KEY (`idCiudad`),
  KEY `fk_ciudad_provincia` (`idProvincia`),
  CONSTRAINT `fk_ciudad_provincia`
    FOREIGN KEY (`idProvincia`) REFERENCES `provincias` (`idProvincia`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. SEGURIDAD Y RBAC: ROLES, PERMISOS Y GRUPOS DE PERMISO
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `idRol`     INT(11) NOT NULL AUTO_INCREMENT,
  `nombreRol` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`idRol`),
  UNIQUE KEY `uq_rol_nombre` (`nombreRol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
  `idPermiso`    INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`       VARCHAR(150) NOT NULL,
  `codigoAccion` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`idPermiso`),
  UNIQUE KEY `uq_permiso_codigo` (`codigoAccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grupos_permiso`;
CREATE TABLE `grupos_permiso` (
  `idGrupoPermiso` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`         VARCHAR(150) NOT NULL,
  `descripcion`    TEXT DEFAULT NULL,
  PRIMARY KEY (`idGrupoPermiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grupos_permiso_permisos`;
CREATE TABLE `grupos_permiso_permisos` (
  `idGrupoPermiso` INT(11) NOT NULL,
  `idPermiso`      INT(11) NOT NULL,
  PRIMARY KEY (`idGrupoPermiso`, `idPermiso`),
  KEY `fk_gpp_permiso` (`idPermiso`),
  CONSTRAINT `fk_gpp_grupo`
    FOREIGN KEY (`idGrupoPermiso`) REFERENCES `grupos_permiso` (`idGrupoPermiso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_gpp_permiso`
    FOREIGN KEY (`idPermiso`) REFERENCES `permisos` (`idPermiso`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. NÚCLEO ACADÉMICO: UNIVERSIDADES, SEDES, CARRERAS Y OFERTA ACADÉMICA
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `universidades`;
CREATE TABLE `universidades` (
  `idUniversidad` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`        VARCHAR(255) NOT NULL,
  `email`         VARCHAR(180) NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `celular`       VARCHAR(60) DEFAULT NULL,
  `estado`        VARCHAR(20) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`idUniversidad`),
  UNIQUE KEY `uq_universidad_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sedes`;
CREATE TABLE `sedes` (
  `idSede`        INT(11) NOT NULL AUTO_INCREMENT,
  `idUniversidad` INT(11) NOT NULL,
  `idCiudad`      INT(11) DEFAULT NULL,
  `nombre`        VARCHAR(255) NOT NULL,
  `descripcion`   TEXT DEFAULT NULL,
  `email`         VARCHAR(180) DEFAULT NULL,
  `web`           VARCHAR(255) DEFAULT NULL,
  `telefono`      VARCHAR(60) DEFAULT NULL,
  `estado`        VARCHAR(20) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`idSede`),
  KEY `fk_sede_universidad` (`idUniversidad`),
  KEY `fk_sede_ciudad`      (`idCiudad`),
  CONSTRAINT `fk_sede_universidad`
    FOREIGN KEY (`idUniversidad`) REFERENCES `universidades` (`idUniversidad`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sede_ciudad`
    FOREIGN KEY (`idCiudad`) REFERENCES `ciudades` (`idCiudad`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `carreras`;
CREATE TABLE `carreras` (
  `idCarrera` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`    VARCHAR(255) NOT NULL,
  PRIMARY KEY (`idCarrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `oferta_academica`;
CREATE TABLE `oferta_academica` (
  `idOfertaAcademica`   INT(11) NOT NULL AUTO_INCREMENT,
  `idCarrera`           INT(11) NOT NULL,
  `idSede`              INT(11) NOT NULL,
  `fechaImplementacion` DATE DEFAULT NULL,
  `descripcion`         TEXT DEFAULT NULL,
  `email`               VARCHAR(180) DEFAULT NULL,
  `web`                 VARCHAR(255) DEFAULT NULL,
  `telefono`            VARCHAR(60) DEFAULT NULL,
  PRIMARY KEY (`idOfertaAcademica`),
  UNIQUE KEY `uq_oa_carrera_sede` (`idCarrera`, `idSede`),
  KEY `fk_oa_sede` (`idSede`),
  CONSTRAINT `fk_oa_carrera`
    FOREIGN KEY (`idCarrera`) REFERENCES `carreras` (`idCarrera`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_oa_sede`
    FOREIGN KEY (`idSede`) REFERENCES `sedes` (`idSede`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. USUARIOS, PERTENENCIA ACADÉMICA (INSCRIPCIONES) Y ASIGNACIÓN DE PERMISOS
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `idUsuario`     INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`        VARCHAR(120) NOT NULL,
  `apellido`      VARCHAR(120) NOT NULL,
  `fNac`          DATE NOT NULL,
  `email`         VARCHAR(180) NOT NULL,
  `password`      VARCHAR(255) NOT NULL,
  `fotoPerfil`    VARCHAR(500) DEFAULT NULL,
  `celular`       VARCHAR(60) DEFAULT NULL,
  `tipoPerfil`    VARCHAR(50) NOT NULL DEFAULT 'Estudiante',
  `descripcion`   TEXT DEFAULT NULL,
  `trayectoria`   TEXT DEFAULT NULL,
  `idCiudad`      INT(11) NOT NULL,
  `idUniversidad` INT(11) DEFAULT NULL,
  `idCarrera`     INT(11) DEFAULT NULL,
  `idRol`         INT(11) NOT NULL DEFAULT 2,
  `estadoCuenta`  VARCHAR(30) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `uq_usuario_email`   (`email`),
  KEY `fk_usuario_ciudad`         (`idCiudad`),
  KEY `fk_usuario_universidad`    (`idUniversidad`),
  KEY `fk_usuario_carrera`        (`idCarrera`),
  KEY `fk_usuario_rol`            (`idRol`),
  CONSTRAINT `fk_usuario_ciudad`
    FOREIGN KEY (`idCiudad`) REFERENCES `ciudades` (`idCiudad`) ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_universidad`
    FOREIGN KEY (`idUniversidad`) REFERENCES `universidades` (`idUniversidad`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_carrera`
    FOREIGN KEY (`idCarrera`) REFERENCES `carreras` (`idCarrera`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_rol`
    FOREIGN KEY (`idRol`) REFERENCES `roles` (`idRol`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `inscripciones`;
CREATE TABLE `inscripciones` (
  `idInscripcion` INT(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`     INT(11) NOT NULL,
  `idUniversidad` INT(11) DEFAULT NULL,
  `idSede`        INT(11) DEFAULT NULL,
  `idCarrera`     INT(11) DEFAULT NULL,
  `anioIngreso`   INT(4) DEFAULT NULL,
  `estado`        VARCHAR(30) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`idInscripcion`),
  UNIQUE KEY `idx_usuario_inscripcion` (`idUsuario`),
  KEY `fk_ins_universidad` (`idUniversidad`),
  KEY `fk_ins_sede`        (`idSede`),
  KEY `fk_ins_carrera`     (`idCarrera`),
  CONSTRAINT `fk_ins_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ins_universidad`
    FOREIGN KEY (`idUniversidad`) REFERENCES `universidades` (`idUniversidad`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ins_sede`
    FOREIGN KEY (`idSede`) REFERENCES `sedes` (`idSede`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ins_carrera`
    FOREIGN KEY (`idCarrera`) REFERENCES `carreras` (`idCarrera`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `detalle_grupo_permiso`;
CREATE TABLE `detalle_grupo_permiso` (
  `idDetalle`       INT(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`       INT(11) NOT NULL,
  `idGrupoPermiso`  INT(11) NOT NULL,
  `fechaAsignacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idDetalle`),
  UNIQUE KEY `uq_dgp_usuario_grupo` (`idUsuario`, `idGrupoPermiso`),
  KEY `fk_dgp_grupo` (`idGrupoPermiso`),
  CONSTRAINT `fk_dgp_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dgp_grupo`
    FOREIGN KEY (`idGrupoPermiso`) REFERENCES `grupos_permiso` (`idGrupoPermiso`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. CONTENIDO COLABORATIVO: PUBLICACIONES, RESPUESTAS Y FAVORITOS (LIKES)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `publicaciones`;
CREATE TABLE `publicaciones` (
  `idPublicacion`             INT(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`                 INT(11) NOT NULL,
  `tipoPublicacion`           VARCHAR(50) NOT NULL DEFAULT 'Consulta',
  `titulo`                    VARCHAR(255) DEFAULT NULL,
  `fecha`                     DATE NOT NULL,
  `hora`                      TIME NOT NULL,
  `idUniversidad`             INT(11) DEFAULT NULL,
  `idSede`                    INT(11) DEFAULT NULL,
  `idCarrera`                 INT(11) DEFAULT NULL,
  `texto`                     TEXT NOT NULL,
  `likes`                     INT(11) NOT NULL DEFAULT 0,
  `resuelta`                  TINYINT(1) NOT NULL DEFAULT 0,
  `puntuacionClima`           TINYINT DEFAULT NULL,
  `puntuacionInfraestructura` TINYINT DEFAULT NULL,
  `puntuacionAcademica`       TINYINT DEFAULT NULL,
  `estado`                    VARCHAR(20) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`idPublicacion`),
  KEY `fk_pub_usuario`           (`idUsuario`),
  KEY `idx_pub_tipo_fecha`       (`tipoPublicacion`, `fecha`),
  KEY `idx_pub_uni_sede_carrera` (`idUniversidad`, `idSede`, `idCarrera`),
  KEY `fk_pub_sede`              (`idSede`),
  KEY `fk_pub_carrera`           (`idCarrera`),
  CONSTRAINT `fk_pub_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pub_universidad`
    FOREIGN KEY (`idUniversidad`) REFERENCES `universidades` (`idUniversidad`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pub_sede`
    FOREIGN KEY (`idSede`) REFERENCES `sedes` (`idSede`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pub_carrera`
    FOREIGN KEY (`idCarrera`) REFERENCES `carreras` (`idCarrera`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_pub_clima` CHECK (`puntuacionClima` IS NULL OR `puntuacionClima` BETWEEN 1 AND 5),
  CONSTRAINT `chk_pub_infra` CHECK (`puntuacionInfraestructura` IS NULL OR `puntuacionInfraestructura` BETWEEN 1 AND 5),
  CONSTRAINT `chk_pub_acad`  CHECK (`puntuacionAcademica` IS NULL OR `puntuacionAcademica` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `respuestas`;
CREATE TABLE `respuestas` (
  `idRespuesta`   INT(11) NOT NULL AUTO_INCREMENT,
  `idPublicacion` INT(11) NOT NULL,
  `idUsuario`     INT(11) NOT NULL,
  `fecha`         DATE NOT NULL,
  `hora`          TIME NOT NULL,
  `texto`         TEXT NOT NULL,
  PRIMARY KEY (`idRespuesta`),
  KEY `fk_resp_publicacion` (`idPublicacion`),
  KEY `fk_resp_usuario`     (`idUsuario`),
  CONSTRAINT `fk_resp_publicacion`
    FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resp_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE `favoritos` (
  `idFavorito`    INT(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`     INT(11) NOT NULL,
  `idPublicacion` INT(11) NOT NULL,
  `fecha`         DATE NOT NULL,
  `hora`          TIME NOT NULL,
  `estado`        VARCHAR(20) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`idFavorito`),
  UNIQUE KEY `uq_fav_usuario_pub` (`idUsuario`, `idPublicacion`),
  KEY `fk_fav_publicacion` (`idPublicacion`),
  CONSTRAINT `fk_fav_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fav_publicacion`
    FOREIGN KEY (`idPublicacion`) REFERENCES `publicaciones` (`idPublicacion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. RED SOCIAL Y MENSAJERÍA DIRECTA: AMIGOS Y MENSAJES
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `amigos`;
CREATE TABLE `amigos` (
  `idAmistad`     INT(11) NOT NULL AUTO_INCREMENT,
  `idSolicitante` INT(11) NOT NULL,
  `idReceptor`    INT(11) NOT NULL,
  `fechaInicio`   DATE NOT NULL,
  `estado`        VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`idAmistad`),
  UNIQUE KEY `uq_amigos_par` (`idSolicitante`, `idReceptor`),
  KEY `idx_receptor` (`idReceptor`),
  CONSTRAINT `fk_amigo_solicitante`
    FOREIGN KEY (`idSolicitante`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_amigo_receptor`
    FOREIGN KEY (`idReceptor`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `mensajedirecto`;
CREATE TABLE `mensajedirecto` (
  `idMensajeDirecto` INT(11) NOT NULL AUTO_INCREMENT,
  `idEmisor`         INT(11) NOT NULL,
  `idReceptor`       INT(11) NOT NULL,
  `fechaMensaje`     DATE NOT NULL,
  `horaMensaje`      TIME NOT NULL,
  `textoMensaje`     TEXT NOT NULL,
  `leido`            TINYINT(1) NOT NULL DEFAULT 0,
  `fechaLectura`     DATETIME DEFAULT NULL,
  `ocultoEmisor`     TINYINT(1) NOT NULL DEFAULT 0,
  `ocultoReceptor`   TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idMensajeDirecto`),
  KEY `idx_msg_emisor_receptor` (`idEmisor`, `idReceptor`),
  KEY `idx_msg_receptor_leido`  (`idReceptor`, `leido`),
  CONSTRAINT `fk_msg_emisor`
    FOREIGN KEY (`idEmisor`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_msg_receptor`
    FOREIGN KEY (`idReceptor`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. TRAZABILIDAD Y AUDITORÍA (RNF06)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `log_auditoria`;
CREATE TABLE `log_auditoria` (
  `idLog`         INT(11) NOT NULL AUTO_INCREMENT,
  `idUsuario`     INT(11) DEFAULT NULL,
  `tablaAfectada` VARCHAR(60) DEFAULT 'general',
  `accion`        VARCHAR(80) NOT NULL,
  `valorOriginal` TEXT DEFAULT NULL,
  `valorNuevo`    TEXT DEFAULT NULL,
  `detalle`       TEXT DEFAULT NULL,
  `fechaHora`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip`            VARCHAR(45) DEFAULT NULL,
  PRIMARY KEY (`idLog`),
  KEY `fk_auditoria_usuario` (`idUsuario`),
  CONSTRAINT `fk_auditoria_usuario`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpieza de tablas legadas redundantes si existían previamente
DROP TABLE IF EXISTS `consultas`;
DROP TABLE IF EXISTS `experiencias`;
DROP TABLE IF EXISTS `carrerassedes`;

SET FOREIGN_KEY_CHECKS = 1;
