-- ============================================================
-- MIGRACIÓN 1/2 - PREPARAR (segura: no borra nada)
-- Ejecutar ANTES de desplegar el nuevo código del back.
-- Hacer backup (export desde phpMyAdmin) antes de correr.
-- ============================================================

-- 1) Puntuaciones dentro de publicaciones (NULL para Consultas)
ALTER TABLE `publicaciones`
  ADD COLUMN `puntuacionClima` TINYINT NULL DEFAULT NULL,
  ADD COLUMN `puntuacionInfraestructura` TINYINT NULL DEFAULT NULL,
  ADD COLUMN `puntuacionAcademica` TINYINT NULL DEFAULT NULL;

-- 2) Copiar puntuaciones desde experiencias (mismo id que la publicación)
UPDATE `publicaciones` p
JOIN `experiencias` e ON e.idExperiencia = p.idPublicacion
SET p.puntuacionClima = e.puntuacionClima,
    p.puntuacionInfraestructura = e.puntuacionInfraestructura,
    p.puntuacionAcademica = e.puntuacionAcademica
WHERE p.tipoPublicacion = 'Experiencia';

-- 3) Experiencias sin fila en experiencias: valor neutro para no romper promedios
UPDATE `publicaciones`
SET puntuacionClima = 5, puntuacionInfraestructura = 5, puntuacionAcademica = 5
WHERE tipoPublicacion = 'Experiencia' AND puntuacionClima IS NULL;

-- 4) Validar rango 1-5 (MariaDB 10.2+)
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `chk_pub_clima` CHECK (`puntuacionClima` IS NULL OR `puntuacionClima` BETWEEN 1 AND 5),
  ADD CONSTRAINT `chk_pub_infra` CHECK (`puntuacionInfraestructura` IS NULL OR `puntuacionInfraestructura` BETWEEN 1 AND 5),
  ADD CONSTRAINT `chk_pub_acad`  CHECK (`puntuacionAcademica` IS NULL OR `puntuacionAcademica` BETWEEN 1 AND 5);

-- 5) Un favorito/like por usuario y publicación
ALTER TABLE `favoritos`
  ADD UNIQUE KEY `uq_fav_usuario_pub` (`idUsuario`, `idPublicacion`);

-- 6) Índices de consulta frecuente
ALTER TABLE `publicaciones`
  ADD KEY `idx_pub_tipo_fecha` (`tipoPublicacion`, `fecha`),
  ADD KEY `idx_pub_uni_sede_carrera` (`idUniversidad`, `idSede`, `idCarrera`);
