-- ============================================================
-- MIGRACIÓN 3 - COHERENCIA DE DATOS DEL TABLERO
-- Hace que los totales del nivel 1 coincidan con lo que se ve al bajar al nivel 3.
-- Ejecutar después de migracion_01_preparar.sql. Hacer backup antes.
-- ============================================================

-- 1) Medicina UNR: las publicaciones estaban en la sede 2 (FCEIA) y la carrera se dicta en la sede 1
UPDATE `publicaciones` SET `idSede` = 1 WHERE `idPublicacion` IN (1, 2, 3, 13);

-- 2) Ingeniería en FIUBA: no existía una sede para esa facultad (estaba cargada en Derecho UBA)
INSERT INTO `sedes` (`idUniversidad`, `idCiudad`, `nombre`, `descripcion`, `estado`)
VALUES (2, 5, 'Facultad de Ingeniería UBA (FIUBA)', 'Sede Paseo Colón', 'activo');

SET @idSedeFiuba = LAST_INSERT_ID();

INSERT INTO `oferta_academica` (`idCarrera`, `idSede`, `fechaImplementacion`, `descripcion`, `web`)
VALUES (2, @idSedeFiuba, '1865-03-01', 'Ingeniería en Sistemas de Información - FIUBA', 'https://www.fi.uba.ar');

UPDATE `publicaciones` SET `idSede` = @idSedeFiuba WHERE `idPublicacion` = 26;

-- 3) Control: no debe devolver filas (publicaciones sin oferta académica asociada)
SELECT p.idPublicacion, p.idSede, p.idCarrera
FROM `publicaciones` p
LEFT JOIN `oferta_academica` oa ON oa.idSede = p.idSede AND oa.idCarrera = p.idCarrera
WHERE p.idSede IS NOT NULL AND p.idCarrera IS NOT NULL AND oa.idSede IS NULL;
