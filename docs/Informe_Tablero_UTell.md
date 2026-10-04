# UNIVERSIDAD ABIERTA INTERAMERICANA
## FACULTAD DE TECNOLOGÍA INFORMÁTICA
### CÁTEDRA DE BASES DE DATOS APLICADA

---

# TRABAJO FINAL CUATRIMESTRAL
## U-TELL: TABLERO DE CONTROL Y CALIDAD UNIVERSITARIA
*Sistema de Analítica Multinivel y Soporte a la Toma de Decisiones Académicas*

- **Integrantes:** Arik Yelin / [Completar con integrantes del grupo]
- **Número de Grupo:** [Completar Nº de grupo]
- **Repositorio GitHub:** [https://github.com/arik-0/utell](https://github.com/arik-0/utell)
- **Despliegue en Producción:** [https://utell-zeta.vercel.app/](https://utell-zeta.vercel.app/)
- **Fecha:** Octubre 2026 — Rosario / Buenos Aires, Argentina

---

## 1. Breve Explicación del Proyecto y Objetivo del Tablero

**U-Tell** es una plataforma web colaborativa y red comunitaria académica orientada a centralizar y democratizar la información sobre la vida universitaria en la República Argentina. Permite a estudiantes activos, aspirantes, graduados y docentes compartir consultas, formular recomendaciones y expresar experiencias evaluativas fundamentadas sobre las carreras e instituciones del país.

En el marco del Trabajo Cuatrimestral de Bases de Datos Aplicada, se desarrolló un **Tablero de Control Ejecutivo (Dashboard)** que procesa de manera agregada la información almacenada en el motor de base de datos relacional para transformar el volumen de interacciones y reseñas en indicadores significativos de gestión.

### 1.1 Objetivo del Tablero de Control
El objetivo primordial del tablero es brindar a las autoridades institucionales (Rectorado, Decanatos de Facultades, Direcciones de Carrera y Secretaría Académica) una herramienta analítica intuitiva y en tiempo real que facilite la toma de decisiones estratégicas mediante:

- **Monitoreo de Calidad:** Detectar con precisión la satisfacción estudiantil desagregada en tres dimensiones cardinales: *Rigor Académico*, *Clima Estudiantil* y *Calidad de Infraestructura*.
- **Comparación Territorial:** Identificar asimetrías de calidad o satisfacción entre distintas sedes geográficas de una misma casa de estudios.
- **Detección de Alarmas:** Señalizar mediante semaforización de tres estados las carreras o campus que requieren intervención inmediata de gestión o inversión.
- **Trazabilidad y Evidencia:** Acceder desde la panorámica nacional hasta el comentario o reseña puntual que motivó un determinado puntaje, mediante navegación multinivel (*Drill Down* y *Drill Up*).

### 1.2 Perfil del Usuario Destinatario
El usuario final del tablero no es el estudiante general, sino los roles de conducción académica e institucional. Por esta razón, el acceso está restringido exclusivamente a cuentas con credenciales autorizadas (Cuentas Institucionales/Rectorado, Administradores Generales, Moderadores y Cuerpo Docente), asegurando que la analítica sea aprovechada por los tomadores de decisiones.

---

## 2. Tecnologías y Herramientas Utilizadas

El proyecto fue concebido bajo una arquitectura cliente-servidor desacoplada mediante API RESTful, priorizando el rendimiento, la integridad relacional y la seguridad en el manejo de datos:

| Capa / Componente | Tecnología Elegida | Versión / Especificación | Fundamentación Técnica |
| :--- | :--- | :--- | :--- |
| **Base de Datos Relacional** | MariaDB / MySQL | 11.4.13-MariaDB / InnoDB | Motor transaccional con soporte nativo de claves foráneas, restricciones CHECK, integridad referencial y agregaciones analíticas optimizadas. |
| **Backend (API REST)** | PHP + Slim Framework 3 | PHP 8.4 | Desarrollo de controladores y repositorios orientados a objetos con conexión PDO, consultas preparadas contra inyección SQL y patrón Facade. |
| **Autenticación y Criptografía** | bcrypt + JSON Web Tokens (JWT) | Algoritmo 2y con coste 10 | Almacenamiento seguro de contraseñas mediante `password_hash` / `password_verify`. Emisión de tokens de sesión con expiración para el acceso a las rutas protegidas. |
| **Frontend (SPA)** | Vue 3 + Vite | Vue 3 (Composition API) | Single Page Application ágil y reactiva, con gestión de rutas dinámicas (Vue Router) y estado centralizado. |
| **Librería de Visualizaciones** | Chart.js | Chart.js v4 (Canvas) | Renderizado de gráficos interactivos de barras comparativas y donas de distribución en tiempo de ejecución. |
| **Alojamiento y Despliegue** | Vercel + alwaysdata | Producción en la nube | Frontend alojado en red CDN global (Vercel) y Backend con base de datos en servidor dedicado en alwaysdata.net. |

---

## 3. Entidades Representadas y su Fundamentación

La consigna establece la obligatoriedad de representar y fundamentar al menos cuatro entidades relevantes del sistema. En el Tablero de Control de U-Tell, las cuatro entidades nucleares seleccionadas y sus justificaciones son:

| Entidad | Pregunta de Negocio que Responde | Utilidad para la Toma de Decisiones |
| :--- | :--- | :--- |
| **1. Universidades (`universidades`)** | ¿Cuál es el posicionamiento institucional global y el volumen de actividad de cada universidad? | Permite al rectorado o a evaluadores externos comparar la reputación global, evaluar el nivel de compromiso de su comunidad y medir el índice general de satisfacción. |
| **2. Sedes (`sedes`)** | ¿Existe heterogeneidad entre los distintos campus o facultades de una misma universidad? | Esencial para la gestión operativa y edilicia. Una universidad puede ostentar alto rigor académico pero su sede regional puede tener una calificación crítica en infraestructura, orientando la asignación de obras y presupuesto. |
| **3. Carreras (`carreras`)** | ¿Qué programas académicos destacan por su calidad y cuáles presentan dificultades pedagógicas? | Permite al decanato y directores de carrera auditar el cuerpo docente, actualizar planes de estudio y atender problemáticas curriculares específicas reportadas por los estudiantes. |
| **4. Publicaciones (`publicaciones`)** | ¿Qué opiniones y puntuaciones cuantitativas respaldan los números del tablero? | Constituye el dato primario y granular (la evidencia empírica). Almacena las tres calificaciones (Clima, Infraestructura, Académica de 1 a 5) y el relato de la experiencia, dotando al indicador de respaldo cualitativo. |

*Entidades secundarias de soporte:* Además de las cuatro entidades principales, el modelo articula tablas relacionales de soporte indispensables: `usuarios` (autores y accesos administrativos), `oferta_academica` (tabla asociativa que modela la relación N:M entre sedes y carreras), `respuestas` (hilos comunitarios de debate), `favoritos` (registro de engagement/likes únicos), `ciudades` y `provincias` (análisis territorial).

---

## 4. Modelo Canónico de la Base de Datos

El esquema relacional fue normalizado en Tercera Forma Normal (3FN), garantizando la ausencia de redundancias anómalas y preservando la integridad referencial a través de claves foráneas con restricciones adecuadas.

> 📷 **ESPACIO RESERVADO PARA EL DIAGRAMA ENTIDAD-RELACIÓN (CANÓNICO)**  
> *(En el archivo .docx generado se encuentra el recuadro formateado para pegar la captura del DER exportado de phpMyAdmin Diseñador o MySQL Workbench).*

### 4.1 Diccionario del Esquema Relacional

| Tabla | Clave Primaria (PK) | Claves Foráneas (FK) | Descripción y Propósito |
| :--- | :--- | :--- | :--- |
| **`universidades`** | `idUniversidad` | — | Catálogo de casas de altos estudios con datos de contacto, correo institucional y clave encriptada. |
| **`sedes`** | `idSede` | `idUniversidad` (FK), `idCiudad` (FK) | Instalaciones físicas o campus donde se desarrollan las actividades académicas. |
| **`carreras`** | `idCarrera` | — | Nómina oficial de carreras de grado y pregrado disponibles. |
| **`oferta_academica`** | `(idCarrera, idSede)` [PK comp.] | `idCarrera` (FK), `idSede` (FK) | Tabla asociativa que formaliza qué carreras se imparten en qué sedes específicas, con fecha de implementación. |
| **`usuarios`** | `idUsuario` | `idCiudad` (FK), `idRol` (FK) | Cuentas de usuarios (estudiantes, docentes, directivos). Almacena password hasheada y tipoPerfil. |
| **`roles`** | `idRol` | — | Roles del sistema: Administrador (1), Estudiante (2), Moderador (3), Graduado (4), Docente (5), Institución (6). |
| **`publicaciones`** | `idPublicacion` | `idUsuario` (FK) | Entidad unificada de experiencias y consultas. Contiene texto, fecha, hora, likes y las tres columnas de puntuación (1 a 5). |
| **`respuestas`** | `idRespuesta` | `idPublicacion` (FK), `idUsuario` (FK) | Comentarios y réplicas a publicaciones existentes. |
| **`favoritos`** | `idFavorito` | `idUsuario` (FK), `idPublicacion` (FK) | Votos de agrado / likes, con restricción `UNIQUE(idUsuario, idPublicacion)`. |
| **`log_auditoria`** | `idLog` | `idUsuario` (FK) | Registro de trazabilidad y auditoría de eventos de moderación y bajas administrativas. |

### 4.2 Cardinalidades y Relaciones Estructurales
- **Universidades - Sedes (1:N):** Una universidad posee una o muchas sedes físicas. Una sede pertenece obligatoriamente a una única universidad.
- **Sedes - Carreras (N:M):** Una sede ofrece múltiples carreras, y una carrera puede dictarse en múltiples sedes y universidades. Esta relación se resuelve limpiamente mediante la tabla asociativa `oferta_academica`.
- **Usuarios - Publicaciones (1:N):** Un usuario puede ser autor de múltiples publicaciones, pero cada publicación pertenece a un único autor registrado.
- **Publicaciones - Contexto Académico:** Cada publicación se vincula opcionalmente con una Universidad, una Sede y una Carrera específicas, permitiendo indexar las opiniones al programa correspondiente.
- **Publicaciones - Respuestas / Likes (1:N):** Una publicación puede acumular cero, una o muchas respuestas e interacciones de favoritos.

---

## 5. Funcionamiento del Tablero de Control

### 5.1 Sistema de Autenticación (Login) y Seguridad
En cumplimiento riguroso de la consigna, el acceso al tablero se encuentra resguardado por un mecanismo de inicio de sesión que valida las credenciales directamente contra la base de datos:
1. **Captura de Credenciales:** El usuario ingresa su correo electrónico y contraseña en el formulario de acceso.
2. **Verificación Relacional:** El backend ejecuta una consulta PDO parametrizada sobre la tabla `usuarios` (o `universidades` en cuentas de rectorado) buscando el registro activo.
3. **Comprobación Criptográfica:** La contraseña se verifica utilizando la función nativa `password_verify($password, $hash)`, garantizando que las claves nunca viajen ni se comparen en texto plano.
4. **Control de Perfil (RBAC):** Se evalúa que el usuario autenticado posea un rol administrativo, de moderación, docente o institucional (`idRol` en 1, 3, 5, 6). Si un estudiante común intenta loguearse al tablero, el sistema deniega el acceso con código HTTP 403 Forbidden.
5. **Protección contra Fallas:** En caso de credenciales inválidas, el sistema rechaza la solicitud (HTTP 401) impidiendo la carga de cualquier indicador.
6. **Acceso Concedido:** Una vez autenticado exitosamente, el backend emite un token JWT firmado y el frontend habilita la visualización del Nivel 1 del tablero.

### 5.2 Desglose y Navegación Multinivel (Drill Down y Drill Up)

| Nivel de Profundidad | Alcance y Visualizaciones | Mecanismo de Desglose (*Drill Down*) | Mecanismo de Retorno (*Drill Up*) |
| :--- | :--- | :--- | :--- |
| **NIVEL 1: Panorama Global** | Tarjetas de KPIs globales (Total Universidades, Sedes, Carreras, Publicaciones, Satisfacción), Gráfico de barras comparativo de las 3 dimensiones, Leyenda explicativa de semáforo y Tabla de ranking general. | Hacer clic en una fila de universidad o en el botón "Explorar Sedes". | Nivel raíz (inicio). |
| **NIVEL 2: Desglose Institucional (Universidad)** | Foco en la universidad elegida. Cabecera institucional con datos de contacto, triad cards de promedios, Gráfico de dona con distribución de carreras por semáforo, y tabla interactiva de Sedes y Carreras. | Hacer clic en una carrera dictada en una sede específica. | Botón "Volver a Nivel 1" o clic en el breadcrumb "Nivel 1: Panorama Global". |
| **NIVEL 3: Detalle Granular (Carrera en Sede)** | Contexto específico del programa en el campus (ciudad, provincia, web, teléfono), Score consolidado de la carrera, Gráfico de barras de las 3 dimensiones en la sede, y listado completo de publicaciones y experiencias. | Nivel de máxima granularidad analítica. | Botón "Volver a Sedes (Nivel 2)" o migas de pan superiores. |

### 5.3 Indicador Visual y Criterio de Semaforización
Para permitir la rápida interpretación del estado de cada institución, sede y carrera, se diseñó un semáforo visual con tres estados definidos a partir de información cuantitativa real obtenida de las experiencias almacenadas:

| Color / Estado | Rango de Puntuación (1.00 a 5.00) | Interpretación y Estado de Gestión | Acción Recomendada |
| :--- | :--- | :--- | :--- |
| **🟢 VERDE** | Promedio General $\ge$ 4.50 | **Favorable / Excelente:** La comunidad estudiantil califica con excelencia la oferta académica, el clima y las instalaciones. | Mantener estándares de calidad y replicar buenas prácticas docentes en otros programas. |
| **🟡 AMARILLO** | Promedio entre 3.50 y 4.49 | **Advertencia / Regular:** Situación intermedia con fortalezas pero con aspectos específicos a mejorar (p. ej. equipamiento o tiempos de gestión). | Auditar los factores con menor puntaje e implementar planes de mejora a mediano plazo. |
| **🔴 ROJO** | Promedio General < 3.50 | **Crítico / Insatisfactorio:** Calificación desfavorable persistente en uno o más ejes de evaluación. | Intervención prioritaria inmediata por parte de decanato e inspección de instalaciones. |

$$\text{Score General} = \frac{\text{Promedio Académico} + \text{Promedio Clima} + \text{Promedio Infraestructura}}{3.00}$$

### 5.4 Indicadores y Visualizaciones Gráficas
- **KPIs Ejecutivos:** Cinco tarjetas con contadores agregados (Universidades, Sedes Territoriales, Carreras Ofertadas, Publicaciones Totales y Satisfacción Global).
- **Gráfico 1 (Nivel 1 - Barras Comparativas):** Compara el Rigor Académico (azul), Clima Estudiantil (ámbar) e Infraestructura (verde) para las universidades registradas.
- **Gráfico 2 (Nivel 2 - Dona de Distribución):** Ilustra la proporción de carreras de la universidad que se encuentran en estado Verde, Amarillo y Rojo.
- **Gráfico 3 (Nivel 3 - Barras de Ejes de Carrera):** Calificación promedio obtenida en cada dimensión específica para la carrera y sede analizadas.

### 5.5 Consultas SQL de Soporte a las Vistas
Las consultas SQL implementadas en `api/src/Facades/DashboardFacade.php` extraen y transforman los datos de forma directa:
- **Consulta Nivel 1 (Global):** Utiliza `COUNT(DISTINCT ...)` para contabilizar entidades únicas y `AVG(...)` con `CASE` condicionales para determinar el semáforo institucional.
- **Consulta Nivel 2 (Desglose Sede/Carrera):** Filtra por `idUniversidad` y agrupa por `idSede` e `idCarrera` sobre la tabla `oferta_academica` vinculada con `publicaciones` mediante `LEFT JOIN`.
- **Consulta Nivel 3 (Detalle Granular):** Obtiene la cabecera contextual de la carrera en el campus y luego lista las reseñas individuales con sus autores, likes (subconsulta sobre favoritos) y cantidad de respuestas.

---

## 6. Capturas de Pantalla del Sistema

> *(En el archivo .docx generado se encuentran los recuadros prediseñados listos para pegar cada imagen).*

1. **Captura 1:** Pantalla de Inicio de Sesión (Login al Tablero).
2. **Captura 2:** Validación de credenciales erróneas (Alerta 401).
3. **Captura 3:** Nivel 1 — Panorama Global (KPIs, Gráfico de barras, Leyenda y Ranking).
4. **Captura 4:** Nivel 2 — Desglose Institucional (Métricas, Gráfico de dona y tabla de sedes/carreras).
5. **Captura 5:** Nivel 3 — Detalle Granular de Carrera en Sede (Score, Gráfico de dimensiones y publicaciones).

---

## 7. Repositorio del Proyecto y Datos de Acceso

- **Repositorio GitHub:** [https://github.com/arik-0/utell](https://github.com/arik-0/utell)
- **Entorno en Producción:** [https://utell-zeta.vercel.app/dashboard](https://utell-zeta.vercel.app/dashboard)

### 7.1 Cuentas de Acceso al Tablero para Evaluación Docente

| Perfil / Rol | Correo Electrónico | Contraseña de Demostración | Permisos de Visualización |
| :--- | :--- | :--- | :--- |
| **Rectorado (UAI)** | `rectorado@uai.edu.ar` | `123456` | Acceso pleno a todos los niveles del tablero institucional y nacional. |
| **Administrador General** | `admin@uai.edu.ar` | `123456` | Acceso irrestricto al tablero, funciones de moderación y auditoría. |
| **Moderador de Comunidad** | `moderador@uai.edu.ar` | `123456` | Acceso analítico y facultades de moderación de contenido inapropiado. |
| **Cuerpo Docente** | `lucia.albarracin@uai.edu.ar` | `123456` | Acceso a métricas de calidad y valoraciones académicas. |
