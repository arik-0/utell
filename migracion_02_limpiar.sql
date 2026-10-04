-- ============================================================
-- MIGRACIÓN 2/2 - LIMPIEZA (destructiva)
-- Ejecutar DESPUÉS de desplegar el nuevo código y verificar que el sitio funciona.
-- Hacer backup antes.
-- ============================================================

-- 1) Datos de prueba (publicaciones 11,12,14,15,16,17; sus respuestas/favoritos caen por CASCADE)
DELETE FROM `publicaciones` WHERE `idPublicacion` IN (11, 12, 14, 15, 16, 17);
DELETE FROM `mensajedirecto` WHERE `idMensajeDirecto` = 7;

-- 2) Tablas duplicadas o sin uso
DROP TABLE IF EXISTS `consultas`;
DROP TABLE IF EXISTS `experiencias`;
DROP TABLE IF EXISTS `carrerassedes`;
DROP TABLE IF EXISTS `grupos_permiso_permisos`;
