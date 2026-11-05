-- ============================================================================
-- U-TELL — DATOS SEMILLA Y CALIBRACIÓN DEL TABLERO (02_seed.sql)
-- Ejecutar después de 01_schema.sql
-- Contraseña demo de todas las cuentas (bcrypt coste 10): 123456
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. GEOGRAFÍA
INSERT INTO `paises` (`idPais`, `nombre`) VALUES
(1, 'Argentina'), (2, 'Uruguay'), (3, 'Chile'),
(4, 'Colombia'),  (5, 'México'),  (6, 'España')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

INSERT INTO `provincias` (`idProvincia`, `idPais`, `nombre`) VALUES
(1, 1, 'Santa Fe'),
(2, 1, 'Buenos Aires'),
(3, 1, 'Ciudad Autónoma de Buenos Aires'),
(4, 1, 'Córdoba'),
(5, 1, 'Mendoza'),
(6, 1, 'Tucumán'),
(7, 2, 'Montevideo'),
(8, 3, 'Región Metropolitana')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `idPais` = VALUES(`idPais`);

INSERT INTO `ciudades` (`idCiudad`, `idProvincia`, `nombre`) VALUES
(1,  1, 'Rosario'),
(2,  1, 'Santa Fe Capital'),
(3,  2, 'La Plata'),
(4,  2, 'Mar del Plata'),
(5,  3, 'CABA'),
(6,  4, 'Córdoba Capital'),
(7,  5, 'Mendoza Capital'),
(8,  6, 'San Miguel de Tucumán'),
(9,  7, 'Montevideo'),
(10, 8, 'Santiago de Chile')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `idProvincia` = VALUES(`idProvincia`);

-- 2. ROLES Y PERMISOS (RBAC)
INSERT INTO `roles` (`idRol`, `nombreRol`) VALUES
(1, 'Administrador'),
(2, 'Estudiante'),
(3, 'Moderador'),
(4, 'Graduado'),
(5, 'Docente'),
(6, 'Institución')
ON DUPLICATE KEY UPDATE `nombreRol` = VALUES(`nombreRol`);

INSERT INTO `permisos` (`idPermiso`, `nombre`, `codigoAccion`) VALUES
(1,  'Crear Publicaciones',              'POST_CREATE'),
(2,  'Responder Consultas',              'POST_REPLY'),
(3,  'Calificar Instituciones',          'RATE_UNIVERSITY'),
(4,  'Moderar Contenido',                'MODERATE_CONTENT'),
(5,  'Gestionar Usuarios',               'MANAGE_USERS'),
(6,  'Enviar Mensajes Directos',         'SEND_DIRECT_MESSAGE'),
(7,  'Ver Métricas Institucionales',     'VIEW_INSTITUTION_METRICS'),
(8,  'Insignia Oficial Verificada',      'OFFICIAL_BADGE'),
(9,  'Eliminar Cualquier Publicación',   'DELETE_ANY_POST'),
(10, 'Administración Total del Sistema', 'ADMIN_FULL')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `codigoAccion` = VALUES(`codigoAccion`);

INSERT INTO `grupos_permiso` (`idGrupoPermiso`, `nombre`, `descripcion`) VALUES
(1, 'Administradores Generales',  'Acceso total y control de moderación institucional'),
(2, 'Moderadores de Comunidad',   'Capacidad de moderar, cerrar o dar de baja publicaciones inapropiadas'),
(3, 'Estudiantes Verificados',    'Alumnos con pertenencia institucional verificada'),
(4, 'Cuerpo Docente y Académico', 'Profesores, investigadores y representantes universitarios')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `descripcion` = VALUES(`descripcion`);

INSERT IGNORE INTO `grupos_permiso_permisos` (`idGrupoPermiso`, `idPermiso`) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),
(2,1),(2,2),(2,4),(2,6),(2,9),
(3,1),(3,2),(3,3),(3,6),
(4,1),(4,2),(4,3),(4,6),(4,7),(4,8);

-- 3. CATÁLOGO ACADÉMICO: UNIVERSIDADES, SEDES, CARRERAS Y OFERTA ACADÉMICA
INSERT INTO `universidades` (`idUniversidad`, `nombre`, `email`, `password`, `celular`, `estado`) VALUES
(1, 'Universidad Nacional de Rosario',        'contacto@unr.edu.ar',    '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 341 4802620',  'activo'),
(2, 'Universidad de Buenos Aires',            'info@rec.uba.ar',        '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 11 5285-5000', 'activo'),
(3, 'Universidad Tecnológica Nacional',       'rectorado@utn.edu.ar',   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 11 4867-7500', 'activo'),
(4, 'Universidad Nacional de La Plata',       'presidencia@unlp.edu.ar','$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 221 644-7000', 'activo'),
(5, 'Universidad Nacional de Córdoba',        'info@unc.edu.ar',        '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 351 535-3700', 'activo'),
(6, 'Universidad Argentina de la Empresa',    'informes@uade.edu.ar',   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 11 4000-7600', 'activo'),
(7, 'Instituto Tecnológico de Buenos Aires',  'informes@itba.edu.ar',   '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 11 3724-6900', 'activo'),
(8, 'Universidad Abierta Interamericana',     'contacto@uai.edu.ar',    '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', '+54 11 4342-7788', 'activo')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `estado` = VALUES(`estado`);

INSERT INTO `sedes` (`idSede`, `idUniversidad`, `idCiudad`, `nombre`, `descripcion`, `email`, `web`, `telefono`, `estado`) VALUES
(1,  1, 1, 'Sede Central - Rectorado UNR',                            'Gobierno y administración general UNR',                      'rectorado@unr.edu.ar',  'https://unr.edu.ar',        '+54 341 4802620',  'activo'),
(2,  1, 1, 'Facultad de Ciencias Médicas (Rosario)',                  'Hospital Provincial del Centenario y Ciencias de la Salud',  'medicina@unr.edu.ar',   'https://fcm.unr.edu.ar',    '+54 341 4804555',  'activo'),
(3,  1, 1, 'Facultad de Ciencias Exactas, Ingeniería y Agrimensura',  'FCEIA - Av. Pellegrini 250, Rosario',                        'fceia@unr.edu.ar',      'https://fceia.unr.edu.ar',  '+54 341 4802649',  'activo'),
(4,  2, 5, 'Facultad de Derecho UBA',                                 'Sede Av. Figueroa Alcorta 2263, Recoleta',                   'alumnos@derecho.uba.ar','https://derecho.uba.ar',    '+54 11 5287-6700', 'activo'),
(5,  2, 5, 'Facultad de Ciencias Económicas (FCE UBA)',               'Sede Av. Córdoba 2122, Balvanera',                           'fce@uba.ar',            'https://economicas.uba.ar', '+54 11 5285-6000', 'activo'),
(6,  2, 5, 'Facultad de Medicina (FMED UBA)',                         'Sede Paraguay 2155, Plaza Houssay',                          'fmed@uba.ar',           'https://fmed.uba.ar',       '+54 11 5285-2000', 'activo'),
(7,  3, 1, 'UTN Facultad Regional Rosario (FRRo)',                    'Sede Zeballos 1341, Rosario',                                'frro@frro.utn.edu.ar',  'https://frro.utn.edu.ar',   '+54 341 4480102',  'activo'),
(8,  3, 5, 'UTN Facultad Regional Buenos Aires (FRBA)',               'Sede Medrano 951 / Campus Lugano',                           'frba@frba.utn.edu.ar',  'https://frba.utn.edu.ar',   '+54 11 4867-7500', 'activo'),
(9,  4, 3, 'Facultad de Informática UNLP',                            'Calle 50 y Av. 120, Bosque Platense',                        'info@info.unlp.edu.ar', 'https://info.unlp.edu.ar',  '+54 221 427-7270', 'activo'),
(10, 5, 6, 'Ciudad Universitaria UNC',                                'Pabellones académicos Nueva Córdoba',                        'info@unc.edu.ar',       'https://unc.edu.ar',        '+54 351 535-3700', 'activo'),
(11, 6, 5, 'Campus Urbano UADE Monserrat',                            'Lima 775, Ciudad Autónoma de Buenos Aires',                  'contacto@uade.edu.ar',  'https://uade.edu.ar',       '+54 11 4000-7600', 'activo'),
(12, 7, 5, 'Sede Central ITBA Puerto Madero',                         'Distrito Tecnológico / Puerto Madero',                       'contacto@itba.edu.ar',  'https://itba.edu.ar',       '+54 11 3724-6900', 'activo'),
(13, 8, 1, 'UAI - Sede Rosario (Localización Roca / Pellegrini)',     'Facultad de Tecnología Informática, Medicina y Derecho',     'rosario@uai.edu.ar',    'https://uai.edu.ar',        '+54 341 440-8010', 'activo'),
(14, 8, 5, 'UAI - Sede Central Buenos Aires (San Telmo)',             'Rectorado y Facultad de Tecnología Informática y Diseño',    'buenosaires@uai.edu.ar','https://uai.edu.ar',        '+54 11 4342-7788', 'activo'),
(15, 2, 5, 'Facultad de Ingeniería UBA (FIUBA - Paseo Colón)',        'Sede Av. Paseo Colón 850, San Telmo',                        'contacto@fi.uba.ar',    'https://fi.uba.ar',         '+54 11 5285-0400', 'activo')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `descripcion` = VALUES(`descripcion`), `estado` = 'activo';

INSERT INTO `carreras` (`idCarrera`, `nombre`) VALUES
(1,  'Medicina'),
(2,  'Ingeniería en Sistemas de Información'),
(3,  'Licenciatura en Ciencias de la Computación'),
(4,  'Abogacía'),
(5,  'Arquitectura y Urbanismo'),
(6,  'Licenciatura en Psicología'),
(7,  'Licenciatura en Administración de Empresas'),
(8,  'Contador Público'),
(9,  'Bioquímica'),
(10, 'Diseño Gráfico')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

INSERT INTO `oferta_academica` (`idCarrera`, `idSede`, `fechaImplementacion`, `descripcion`, `email`, `web`, `telefono`) VALUES
(1, 2,  '1920-03-01', 'Carrera de Grado en Medicina - FCM UNR',                              'medicina@unr.edu.ar',           'https://fcm.unr.edu.ar',    '+54 341 4804555'),
(2, 3,  '1985-03-15', 'Ingeniería en Sistemas y Ciencias de la Computación - FCEIA UNR',     'sistemas@fceia.unr.edu.ar',     'https://fceia.unr.edu.ar',  '+54 341 4802649'),
(4, 4,  '1950-03-01', 'Carrera de Abogacía - UBA Derecho',                                   'alumnos@derecho.uba.ar',        'https://derecho.uba.ar',    '+54 11 5287-6700'),
(7, 5,  '1960-03-01', 'Licenciatura en Administración - FCE UBA',                            'administracion@economicas.uba.ar','https://economicas.uba.ar','+54 11 5285-6000'),
(1, 6,  '1900-03-01', 'Medicina General y Cirugía - FMED UBA',                               'medicina@fmed.uba.ar',          'https://fmed.uba.ar',       '+54 11 5285-2000'),
(2, 15, '1865-03-01', 'Ingeniería en Informática y Sistemas - FIUBA Paseo Colón',            'informatica@fi.uba.ar',         'https://fi.uba.ar',         '+54 11 5285-0400'),
(2, 7,  '1984-04-01', 'Ingeniería en Sistemas de Información - UTN FRRo',                    'sistemas@frro.utn.edu.ar',      'https://frro.utn.edu.ar',   '+54 341 4480102'),
(2, 8,  '1980-03-01', 'Ingeniería en Sistemas de Información - UTN FRBA',                    'sistemas@frba.utn.edu.ar',      'https://frba.utn.edu.ar',   '+54 11 4867-7500'),
(3, 9,  '1999-03-01', 'Licenciatura en Ciencias de la Computación - UNLP',                   'alumnos@info.unlp.edu.ar',      'https://info.unlp.edu.ar',  '+54 221 427-7270'),
(5, 10, '1955-03-01', 'Arquitectura y Urbanismo - FAUD UNC',                                 'faud@unc.edu.ar',               'https://faud.unc.edu.ar',   '+54 351 535-3700'),
(1, 10, '1920-03-01', 'Medicina - FCM UNC',                                                  'fcm@unc.edu.ar',                'https://fcm.unc.edu.ar',    '+54 351 535-3701'),
(3, 10, '1990-03-01', 'Licenciatura en Ciencias de la Computación - FAMAF UNC',              'famaf@unc.edu.ar',              'https://famaf.unc.edu.ar',  '+54 351 535-3702'),
(7, 11, '1990-03-01', 'Licenciatura en Administración de Empresas - UADE',                   'business@uade.edu.ar',          'https://uade.edu.ar',       '+54 11 4000-7600'),
(2, 12, '1975-03-01', 'Ingeniería en Informática - ITBA',                                    'ingenieria@itba.edu.ar',        'https://itba.edu.ar',       '+54 11 3724-6900'),
(2, 13, '1995-03-01', 'Ingeniería en Sistemas Informáticos - UAI Sede Rosario',              'sistemas.rosario@uai.edu.ar',   'https://uai.edu.ar',        '+54 341 440-8010'),
(1, 13, '1998-03-01', 'Carrera de Medicina - Hospital Universitario UAI Rosario',            'medicina.rosario@uai.edu.ar',   'https://uai.edu.ar',        '+54 341 440-8011'),
(4, 13, '1996-03-01', 'Abogacía y Práctica Profesional Supervisada - UAI Rosario',           'derecho.rosario@uai.edu.ar',    'https://uai.edu.ar',        '+54 341 440-8012'),
(2, 14, '1994-03-01', 'Ingeniería en Sistemas Informáticos - UAI Sede San Telmo',            'sistemas.ba@uai.edu.ar',        'https://uai.edu.ar',        '+54 11 4342-7788'),
(5, 14, '2000-03-01', 'Arquitectura y Urbanismo - UAI San Telmo',                            'arquitectura.ba@uai.edu.ar',    'https://uai.edu.ar',        '+54 11 4342-7789'),
(7, 14, '1996-03-01', 'Licenciatura en Administración de Empresas - UAI San Telmo',          'administracion.ba@uai.edu.ar',  'https://uai.edu.ar',        '+54 11 4342-7790')
ON DUPLICATE KEY UPDATE `descripcion` = VALUES(`descripcion`), `web` = VALUES(`web`);

-- 4. USUARIOS (Comunidad + Cuentas Institucionales / Administración)
INSERT INTO `usuarios`
  (`idUsuario`, `nombre`, `apellido`, `fNac`, `email`, `password`, `fotoPerfil`, `celular`, `tipoPerfil`, `descripcion`, `trayectoria`, `idCiudad`, `idUniversidad`, `idCarrera`, `idRol`, `estadoCuenta`)
VALUES
(3,  'Tamara',      'Ajun',       '1995-04-12', 'tamaraajun@hotmail.com',          '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&auto=format&fit=crop&q=80', '+54 341 555-0101', 'Docente',       'Docente e investigadora en Ciencias Médicas. Apasionada por la pedagogía universitaria.', 'Docente titular en UNR con 8 años de trayectoria.', 1, 1, 1, 5, 'activo'),
(4,  'Arik',        'Yelin',      '2001-08-20', 'arikayelin@gmail.com',            '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80', '+54 341 555-0102', 'Estudiante',    'Estudiante avanzado de Ingeniería en Sistemas Informáticos y desarrollador full-stack.', 'Trabajo de Diploma UAI Rosario.', 1, 8, 2, 2, 'activo'),
(5,  'Lucas',       'Benítez',    '2002-03-14', 'lucas.benitez@utn.edu.ar',        '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&auto=format&fit=crop&q=80', '+54 341 555-0103', 'Estudiante',    'Estudiante de Ingeniería en Sistemas en UTN Rosario. Entusiasta del software libre.', '3er año de Ingeniería en Sistemas.', 1, 3, 2, 2, 'activo'),
(6,  'Camila',      'Ríos',       '2000-11-28', 'camila.rios@uba.ar',              '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&auto=format&fit=crop&q=80', '+54 11 555-0104',  'Estudiante',    'Cursando Abogacía en la UBA. Ayudante de cátedra en Derecho Constitucional.', '4to año en la Facultad de Derecho UBA.', 5, 2, 4, 2, 'activo'),
(7,  'Martín',      'Gómez',      '1998-06-19', 'martin.gomez@itba.edu.ar',        '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&auto=format&fit=crop&q=80', '+54 11 555-0105',  'Graduado',      'Ingeniero en Informática por ITBA. Tech Lead en empresa de tecnología.', 'Graduado con honores en 2023.', 5, 7, 2, 4, 'activo'),
(8,  'Sofía',       'Fernández',  '2003-01-10', 'sofia.fernandez@info.unlp.edu.ar','$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=200&auto=format&fit=crop&q=80', '+54 221 555-0106', 'Estudiante',    'Estudiante de Ciencias de la Computación en UNLP. Apasionada por algoritmos y ML.', '2do año de Ciencias de la Computación.', 3, 4, 3, 2, 'activo'),
(9,  'Valentina',   'Rossi',      '2001-09-05', 'valentina.rossi@unc.edu.ar',      '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&auto=format&fit=crop&q=80', '+54 351 555-0107', 'Estudiante',    'Estudiando Arquitectura y Urbanismo en la UNC.', '3er año en Ciudad Universitaria Córdoba.', 6, 5, 5, 2, 'activo'),
(10, 'Nicolás',     'Castro',     '1999-12-22', 'nicolas.castro@uade.edu.ar',      '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=200&auto=format&fit=crop&q=80', '+54 11 555-0108',  'Estudiante',    'Cursando Administración de Empresas en UADE. Emprendedor en e-commerce.', 'Último año de Administración.', 5, 6, 7, 2, 'activo'),
(97, 'Moderación',  'U-Tell',     '1992-05-10', 'moderador@uai.edu.ar',            '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&auto=format&fit=crop&q=80', '+54 341 440-8010', 'Moderador',     'Equipo de Moderación Académica U-Tell.', 'Supervisión comunitaria.', 1, 8, 2, 3, 'activo'),
(98, 'Rectorado',   'UAI',        '1980-01-01', 'rectorado@uai.edu.ar',            '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1562774053-701939374585?w=200&auto=format&fit=crop&q=80', '+54 11 4342-7788', 'Universidad',   'Autoridad Institucional UAI.', 'Rectorado y Secretaría Académica UAI.', 5, 8, 2, 6, 'activo'),
(99, 'Administrador','UAI',       '1990-01-01', 'admin@uai.edu.ar',                '$2y$10$dGNt7MjZlkHC0aJ1TqxeAe2orHKZnaLuuW3hXFgq2.cAmhcCNoX3O', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80', '+54 11 4342-7788', 'Administrador', 'Administrador General U-Tell.', 'Gestión integral de la plataforma.', 5, 8, 2, 1, 'activo')
ON DUPLICATE KEY UPDATE `tipoPerfil` = VALUES(`tipoPerfil`), `idRol` = VALUES(`idRol`), `estadoCuenta` = 'activo';

INSERT INTO `inscripciones` (`idUsuario`, `idUniversidad`, `idSede`, `idCarrera`, `anioIngreso`, `estado`) VALUES
(3,  1, 2,  1, 2017, 'activo'),
(4,  8, 13, 2, 2022, 'activo'),
(5,  3, 7,  2, 2022, 'activo'),
(6,  2, 4,  4, 2020, 'activo'),
(7,  7, 12, 2, 2018, 'activo'),
(8,  4, 9,  3, 2023, 'activo'),
(9,  5, 10, 5, 2022, 'activo'),
(10, 6, 11, 7, 2020, 'activo'),
(97, 8, 13, 2, 2024, 'activo'),
(98, 8, 14, 2, 2020, 'activo'),
(99, 8, 14, 2, 2020, 'activo')
ON DUPLICATE KEY UPDATE `idUniversidad` = VALUES(`idUniversidad`), `idSede` = VALUES(`idSede`), `idCarrera` = VALUES(`idCarrera`);

INSERT IGNORE INTO `detalle_grupo_permiso` (`idUsuario`, `idGrupoPermiso`, `fechaAsignacion`) VALUES
(3,  4, '2026-08-24 20:12:25'),
(4,  3, '2026-08-24 20:12:25'),
(5,  3, '2026-08-24 20:12:25'),
(6,  3, '2026-08-24 20:12:25'),
(7,  3, '2026-08-24 20:12:25'),
(8,  3, '2026-08-24 20:12:25'),
(9,  3, '2026-08-24 20:12:25'),
(10, 3, '2026-08-24 20:12:25'),
(97, 2, '2026-08-24 20:12:25'),
(98, 4, '2026-08-24 20:12:25'),
(99, 1, '2026-08-24 20:12:25');

-- 5. PUBLICACIONES UNIFICADAS (Experiencias calibradas 1..5 y Consultas con puntajes NULL)
INSERT INTO `publicaciones`
  (`idPublicacion`, `idUsuario`, `tipoPublicacion`, `titulo`, `fecha`, `hora`, `idUniversidad`, `idSede`, `idCarrera`, `texto`, `likes`, `resuelta`, `puntuacionClima`, `puntuacionInfraestructura`, `puntuacionAcademica`, `estado`)
VALUES
(1,  3,  'Consulta',    'Inscripción a guardias clínicas en UNR',                     '2026-08-14', '01:21:27', 1, 2,  1, 'Estimados alumnos: ¿Alguien sabe la fecha límite para entregar la planilla de rotación hospitalaria de 5to año?', 1, 1, NULL, NULL, NULL, 'activo'),
(2,  3,  'Experiencia', 'Mi balance como docente en Medicina UNR',                    '2026-08-14', '03:59:57', 1, 2,  1, 'La dedicación y el compromiso clínico de los alumnos en el Hospital Centenario es de excelencia. Muy conforme con las prácticas integradas de este ciclo.', 1, 0, 4, 4, 4, 'activo'),
(3,  4,  'Consulta',    'Apuntes de Farmacología Médica 1',                           '2026-08-17', '22:27:47', 1, 2,  1, '¿Quién tiene el resumen actualizado con las modificaciones del nuevo protocolo de antibioticoterapia?', 1, 0, NULL, NULL, NULL, 'activo'),
(4,  5,  'Experiencia', 'Cursada de Sistemas Operativos en UTN Rosario',              '2026-08-18', '11:15:00', 3, 7,  2, 'Los laboratorios de Linux y los trabajos prácticos de concurrencia y memoria virtual son muy completos. Gran nivel de exigencia en los parciales.', 2, 0, 4, 3, 5, 'activo'),
(5,  5,  'Consulta',    'Electivas recomendadas para 4to año en UTN',                 '2026-08-19', '16:40:00', 3, 7,  2, '¿Qué opinan entre Inteligencia Artificial aplicada vs. Arquitectura de Software? ¿Cuál tiene mejor enfoque práctico?', 0, 0, NULL, NULL, NULL, 'activo'),
(6,  6,  'Experiencia', 'Prácticas profesionales en UBA Derecho',                     '2026-08-20', '09:30:00', 2, 4,  4, 'El patrocinio jurídico gratuito de la UBA te da una experiencia inigualable en litigio real antes de graduarte. Biblioteca y docentes de primer nivel.', 1, 0, 4, 4, 5, 'activo'),
(7,  7,  'Experiencia', 'Balance académico y salida laboral ITBA',                    '2026-08-21', '14:20:00', 7, 12, 2, 'La red de contactos, la infraestructura de laboratorios y los convenios con empresas internacionales son extraordinarios. Cursada muy intensa pero que rinde frutos inmediatos.', 1, 0, 5, 5, 5, 'activo'),
(8,  8,  'Experiencia', 'La comunidad en Informática UNLP',                           '2026-08-22', '10:05:00', 4, 9,  3, 'El clima estudiantil y el centro de estudiantes son súper colaborativos. Los profesores siempre tienen horarios de consulta disponibles para resolver dudas.', 0, 0, 4, 3, 4, 'activo'),
(9,  9,  'Consulta',    'Materiales para Taller de Maquetas en UNC',                  '2026-08-23', '15:10:00', 5, 10, 5, '¿Dónde consiguen cartón gris y madera balsa a buen precio cerca de Ciudad Universitaria?', 0, 0, NULL, NULL, NULL, 'activo'),
(10, 10, 'Experiencia', 'Cursada nocturna y networking en UADE',                      '2026-08-24', '12:00:00', 6, 11, 7, 'Muy buenas instalaciones y flexibilidad horaria para quienes trabajamos en horario comercial. Los docentes tienen experiencia real corporativa.', 0, 0, 4, 5, 3, 'activo'),
(18, 4,  'Experiencia', 'Cátedra de Bases de Datos Aplicada y Diploma en UAI Rosario','2026-09-15', '10:30:00', 8, 13, 2, 'Excelente nivel pedagógico en la Facultad de Tecnología Informática. Los proyectos sobre arquitectura en N-Capas y motores relacionales te preparan directo para el mercado laboral.', 2, 0, 5, 5, 5, 'activo'),
(19, 5,  'Experiencia', 'Prácticas clínicas y simulación médica en UAI',              '2026-09-18', '14:20:00', 8, 13, 1, 'El centro de simulación médica y el hospital universitario tienen equipamiento de alta tecnología. Docentes con enorme vocación hospitalaria.', 1, 0, 4, 5, 5, 'activo'),
(20, 7,  'Experiencia', 'Laboratorios informáticos y campus San Telmo UAI',           '2026-09-22', '16:45:00', 8, 14, 2, 'Las aulas de desarrollo cuentan con hardware moderno, buena conectividad y soporte continuo para trabajos prácticos en equipo. Clima sumamente ameno.', 1, 0, 5, 4, 5, 'activo'),
(21, 8,  'Consulta',    '¿Alguien cursó Arquitectura de Software en UAI San Telmo?',  '2026-09-25', '11:10:00', 8, 14, 2, 'Quisiera saber qué enfoque le dan a los patrones de diseño y capas desacopladas en el trabajo práctico integrador.', 0, 0, NULL, NULL, NULL, 'activo'),
(22, 9,  'Experiencia', 'Dificultades de infraestructura en Talleres de Arquitectura UNC','2026-09-08','09:15:00',5, 10, 5, 'Los profesores tienen buena voluntad pero las aulas de taller están superpobladas, falta ventilación y los tableros de dibujo están bastante deteriorados.', 1, 0, 3, 2, 3, 'activo'),
(23, 10, 'Experiencia', 'Burocracia y tiempos de gestión en Ciudad Universitaria UNC','2026-09-12', '15:40:00', 5, 10, 5, 'Los trámites administrativos demoran meses y la atención en secretaría es deficiente. A nivel académico es exigente pero el clima estudiantil se ve afectado por la falta de recursos.', 0, 0, 3, 3, 4, 'activo'),
(24, 6,  'Consulta',    '¿Dónde conseguir materiales para maquetas cerca de UNC?',    '2026-09-19', '18:20:00', 5, 10, 5, 'Busco librerías técnicas con precios accesibles para estudiantes en la zona de Nueva Córdoba.', 0, 1, NULL, NULL, NULL, 'activo'),
(25, 7,  'Experiencia', 'Pasantías internacionales e innovación en ITBA Puerto Madero','2026-09-14','11:00:00', 7, 12, 2, 'Los laboratorios de robótica y computación en la nube son excepcionales. Empresas líderes vienen directamente al campus a reclutar antes de graduarte.', 1, 0, 4, 5, 5, 'activo'),
(26, 6,  'Experiencia', 'Exigencia y rigor en FIUBA Paseo Colón',                     '2026-09-16', '17:30:00', 2, 15, 2, 'La matemática y la física en FIUBA son de un nivel superior. El edificio histórico impone respeto, aunque algunas aulas necesitan actualización de proyectores.', 1, 0, 4, 4, 5, 'activo'),
(27, 5,  'Experiencia', 'Vida universitaria y comunidad en UTN Buenos Aires (Medrano)','2026-09-20','19:15:00', 3, 8,  2, 'Excelente ambiente de compañerismo en las cursadas vespertinas. Los docentes trabajan en la industria y transmiten problemáticas reales de ingeniería.', 0, 0, 5, 4, 4, 'activo'),
(28, 8,  'Experiencia', 'Comunidad de Software Libre en Informática UNLP',            '2026-09-21', '13:00:00', 4, 9,  3, 'El centro de estudiantes y los grupos de investigación en algoritmos son súper activos. Falta inversión en refrigeración de aulas pero la calidad humana es inmejorable.', 0, 0, 5, 3, 5, 'activo'),
(29, 10, 'Experiencia', 'Edificio y tecnología en Campus Monserrat UADE',             '2026-09-23', '18:40:00', 6, 11, 7, 'Aulas híbridas impecables, estacionamiento, comedores y biblioteca digital de vanguardia. La exigencia académica es accesible si llevas la materia al día.', 0, 0, 4, 5, 4, 'activo')
ON DUPLICATE KEY UPDATE
  `idSede` = VALUES(`idSede`),
  `idCarrera` = VALUES(`idCarrera`),
  `puntuacionClima` = VALUES(`puntuacionClima`),
  `puntuacionInfraestructura` = VALUES(`puntuacionInfraestructura`),
  `puntuacionAcademica` = VALUES(`puntuacionAcademica`);

-- 6. RESPUESTAS Y DEBATE COMUNITARIO
INSERT INTO `respuestas` (`idRespuesta`, `idPublicacion`, `idUsuario`, `fecha`, `hora`, `texto`) VALUES
(1,  1,  4,  '2026-08-14', '09:15:00', 'Profe, el plazo fijado por secretaría académica vence este viernes 28 a las 18 hs por ventanilla.'),
(2,  1,  3,  '2026-08-14', '10:00:00', 'Excelente Arik, muchas gracias por confirmar la información.'),
(3,  3,  3,  '2026-08-17', '23:05:00', 'Te lo acabo de compartir en el campus virtual en la sección de material complementario.'),
(4,  5,  7,  '2026-08-19', '17:30:00', 'Arquitectura de Software te da bases clave para cualquier rol senior en la industria, súper recomendada.'),
(5,  5,  8,  '2026-08-19', '18:12:00', 'Coincido, además el docente que la dicta tiene casos de estudio reales muy dinámicos.'),
(13, 18, 3,  '2026-09-15', '12:00:00', '¡Totalmente de acuerdo! La práctica con casos de estudio reales en UAI marca la diferencia.'),
(14, 18, 5,  '2026-09-15', '14:30:00', 'Confirmo, el enfoque en normalización y optimización de consultas SQL te abre puertas rápido.'),
(15, 21, 4,  '2026-09-25', '14:20:00', 'El TP integrador se basa en arquitectura en N-Capas con controladores REST, servicios y repositorios PDO.'),
(16, 22, 6,  '2026-09-08', '11:10:00', 'Coincido plenamente, en invierno las aulas de taller de la UNC son heladas. Ojalá eleven el reclamo al decanato.'),
(17, 24, 10, '2026-09-19', '19:00:00', 'En la librería técnica frente al Pabellón México hacen 15% de descuento con libreta universitaria.')
ON DUPLICATE KEY UPDATE `texto` = VALUES(`texto`);

-- 7. FAVORITOS / LIKES (Consistentes con publicaciones.likes)
INSERT IGNORE INTO `favoritos` (`idFavorito`, `idUsuario`, `idPublicacion`, `fecha`, `hora`, `estado`) VALUES
(1,  4, 1,  '2026-08-14', '09:16:00', 'activo'),
(2,  4, 2,  '2026-08-14', '10:02:00', 'activo'),
(3,  3, 3,  '2026-08-17', '23:06:00', 'activo'),
(4,  4, 4,  '2026-08-18', '11:20:00', 'activo'),
(5,  5, 4,  '2026-08-18', '11:22:00', 'activo'),
(6,  4, 6,  '2026-08-20', '10:00:00', 'activo'),
(7,  4, 7,  '2026-08-21', '14:30:00', 'activo'),
(8,  3, 18, '2026-09-15', '11:00:00', 'activo'),
(9,  5, 18, '2026-09-15', '11:30:00', 'activo'),
(10, 4, 19, '2026-09-18', '15:00:00', 'activo'),
(11, 4, 20, '2026-09-22', '17:00:00', 'activo'),
(12, 8, 22, '2026-09-08', '10:00:00', 'activo'),
(13, 4, 25, '2026-09-14', '12:00:00', 'activo'),
(14, 5, 26, '2026-09-16', '18:00:00', 'activo');

-- 8. AMISTADES Y RED DE CONTACTOS
INSERT IGNORE INTO `amigos` (`idAmistad`, `idSolicitante`, `idReceptor`, `fechaInicio`, `estado`) VALUES
(1, 4, 3,  '2026-08-01', 'aceptada'),
(2, 4, 5,  '2026-08-05', 'aceptada'),
(3, 4, 8,  '2026-08-10', 'aceptada'),
(4, 6, 4,  '2026-08-15', 'aceptada'),
(5, 7, 4,  '2026-08-20', 'pendiente'),
(6, 9, 4,  '2026-08-22', 'pendiente'),
(7, 5, 8,  '2026-08-02', 'aceptada'),
(8, 6, 10, '2026-08-03', 'aceptada');

-- 9. MENSAJERÍA DIRECTA
INSERT INTO `mensajedirecto`
  (`idMensajeDirecto`, `idEmisor`, `idReceptor`, `fechaMensaje`, `horaMensaje`, `textoMensaje`, `leido`, `fechaLectura`)
VALUES
(1, 3, 4, '2026-08-23', '14:20:00', 'Hola Arik, ¿viste la actualización de fechas para las entregas de Trabajo de Diploma?', 1, '2026-08-23 14:21:00'),
(2, 4, 3, '2026-08-23', '14:22:15', 'Hola Profe, ¡sí! Ya dejé listos los diagramas y el modelo relacional.', 1, '2026-08-23 14:25:00'),
(3, 3, 4, '2026-08-24', '10:05:00', 'Excelente. Nos vemos mañana en la revisión técnica a las 18:00.', 0, NULL),
(4, 5, 4, '2026-08-24', '16:00:00', 'Arik, ¿tenés a mano el apunte de Sistemas que mencionaron en el feed?', 1, '2026-08-24 16:02:00'),
(5, 4, 5, '2026-08-24', '16:05:00', 'Dale Lucas, te paso el link directo al repositorio en un rato.', 1, '2026-08-24 16:10:00'),
(6, 8, 4, '2026-08-24', '18:30:00', '¡Hola! Vi tu experiencia sobre UAI Rosario, ¿qué tal la cursada de Ingeniería en Sistemas?', 0, NULL)
ON DUPLICATE KEY UPDATE `textoMensaje` = VALUES(`textoMensaje`);

-- 10. AUDITORÍA INICIAL
INSERT IGNORE INTO `log_auditoria` (`idLog`, `idUsuario`, `tablaAfectada`, `accion`, `valorOriginal`, `valorNuevo`, `detalle`, `fechaHora`, `ip`) VALUES
(1, 4,  'publicaciones',  'INSERT', NULL, 'idPublicacion: 18', 'Creación de experiencia académica UAI Rosario', '2026-09-15 10:30:00', '127.0.0.1'),
(2, 5,  'publicaciones',  'INSERT', NULL, 'idPublicacion: 4',  'Creación de experiencia académica UTN FRRo',    '2026-08-18 11:15:00', '127.0.0.1'),
(3, 6,  'publicaciones',  'INSERT', NULL, 'idPublicacion: 6',  'Creación de experiencia académica UBA Derecho', '2026-08-20 09:30:00', '127.0.0.1'),
(4, 4,  'mensajedirecto', 'INSERT', NULL, 'idMensajeDirecto: 5','Mensaje directo enviado a contacto activo',     '2026-08-24 16:05:00', '127.0.0.1'),
(5, 99, 'usuarios',       'SEED',   NULL, 'Inicialización RBAC','Aprovisionamiento de cuentas institucionales',  '2026-09-28 12:00:00', '127.0.0.1');

SET FOREIGN_KEY_CHECKS = 1;
