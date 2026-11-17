# Guía de Estudio y Defensa Final — U-TELL (Ingeniería de Software / Trabajo de Diploma)

> [!TIP]
> **Cómo usar esta guía:** Está organizada exactamente en el orden en que el **Ing. Pablo Audoglio** y el tribunal evalúan la defensa oral: **1) Guion de presentación (12 min)**, **2) Resumen teórico-práctico de cada punto de la rúbrica**, **3) Las 15 preguntas clásicas de examen con tu respuesta exacta** y **4) El recorrido paso a paso para la Demo en vivo**.

---

## 1. Guion para tu Exposición Oral (Estructura de 12 a 15 minutos)

### Minuto 0 a 2 — El Problema, la Motivación y el Objetivo (Puntos G00, G01, G02)
* **Qué decir:**
  * *"Buenas tardes. Mi proyecto es **U-Tell (UT)**, una plataforma colaborativa de orientación y experiencia universitaria enfocada inicialmente en las universidades de Rosario."*
  * **Motivación (¿Por qué?):** *"Hoy un chico de 5.º año de secundaria elige qué y dónde estudiar basándose en folletos publicitarios o comentarios sueltos en redes sociales sin verificar. Esa asimetría de información genera frustración vocacional y una alta tasa de deserción en el primer año."*
  * **Objetivo General:** *"Desarrollar e implementar una plataforma que centralice experiencias validadas de estudiantes y docentes y habilite el contacto directo con los aspirantes para mejorar su toma de decisiones y reducir la deserción temprana."*

### Minuto 2 a 5 — Gestión del Proyecto: Métricas y Riesgos (Punto G05)
* **Métricas (UCP):**
  * *"Para estimar el esfuerzo utilicé **Puntos de Casos de Uso (UCP)** sobre los 13 Casos de Uso y 4 actores del sistema. Obtuve un peso de actores sin ajustar $\text{UAW} = 11$ y de casos de uso $\text{UUCW} = 150$ ($\text{UUCP} = 161$). Aplicando el factor técnico $\text{TCF} = 1.125$ (por arquitectura desacoplada, seguridad JWT y rendimiento) y el ambiental $\text{ECF} = 0.725$, el tamaño funcional resultó en **$131.32\text{ UCP}$**, equivalentes a **$420\text{ horas hombre}$** distribuidas en 5 fases."*
* **Gestión de Riesgos:**
  * *"Evalué los riesgos por $\text{Probabilidad} \times \text{Impacto}$ en escala de 1 a 25, fijando la **Línea Base en $ER \ge 9$**. Los 4 riesgos sobre la línea base fueron: 1) Inyección SQL/vulneración de endpoints ($ER=15$), 2) Falsa identidad académica en reseñas ($ER=12$), 3) Desfase de esquema de BD en despliegues ($ER=12$) y 4) Spam en mensajería privada ($ER=9$). Para cada uno definí planes de Prevención, Minimización, Contingencia e Indicadores de Monitoreo."*

### Minuto 5 a 8 — Las 2 Iteraciones CORE y el Diseño UML (Punto N00)
* **Iteración 1 (Ingeniería de Software) — Publicaciones Polimórficas (`CU3`, `CU4`, `CU7`):**
  * *"Es el corazón de U-Tell. Discrimina polimórficamente entre una **Consulta** (pregunta abierta que puede publicar cualquier aspirante) y una **Experiencia** (reseña restringida a Estudiantes y Docentes verificados, que exige calificar del 1 al 5 **Rigor Académico**, **Infraestructura** y **Clima**)."*
* **Iteración 2 (Trabajo de Diploma) — Mensajería Privada (`CU12`, `CU13`):**
  * *"Cierra el circuito de contacto directo. Incorpora la regla de negocio de **Vínculo Habilitante**: para evitar spam, solo podés enviar mensajes si existe una **Amistad activa (`CU11`)** o si sos un **Postulante** consultando a un **Estudiante/Docente validado**. Además, modela la máquina de estados del mensaje (`Enviado` $\rightarrow$ `Entregado` $\rightarrow$ `Leído`) y el borrado unilateral («Eliminar para mí»)."*

### Minuto 8 a 11 — Arquitectura N-Capas y los 3 Patrones GoF (Punto T01)
* **Arquitectura:** *"Cliente Web **SPA en Vue.js 3** totalmente desacoplado que consume vía HTTPS/JSON una **API REST en PHP 8 Slim** estructurada en N-Capas: Rutas/Controladores + Middlewares $\rightarrow$ Capa de Servicios (Lógica de negocio) $\rightarrow$ Capa de Dominio (Modelos encapsulados) $\rightarrow$ Capa de Acceso a Datos (Repositorios con PDO Prepared Statements) $\rightarrow$ MySQL InnoDB."*
* **Los 3 Patrones GoF implementados en código:**
  1. **Creacional:** **`Factory Method`** en [`PublicacionFactory`](file:///c:/xampp/htdocs/utell/api/src/Factories/PublicacionFactory.php) (instancia y normaliza `Experiencia` vs. `Consulta`) + **`Singleton`** en [`db::getInstance()`](file:///c:/xampp/htdocs/utell/api/src/config/db.php) (única conexión PDO).
  2. **Estructural:** **`Facade`** en [`DashboardFacade`](file:///c:/xampp/htdocs/utell/api/src/Facades/DashboardFacade.php) (unifica las consultas complejas de los 3 niveles de *Drill-Down* del Tablero BI) + **`Composite`** en [`GrupoPermiso`](file:///c:/xampp/htdocs/utell/api/src/Models/GrupoPermiso.php) / [`Permiso`](file:///c:/xampp/htdocs/utell/api/src/Models/Permiso.php) (seguridad RBAC).
  3. **De Comportamiento:** **`Strategy`** en [`IFeedStrategy`](file:///c:/xampp/htdocs/utell/api/src/Strategies/IFeedStrategy.php) ([`ParaTiFeedStrategy`](file:///c:/xampp/htdocs/utell/api/src/Strategies/ParaTiFeedStrategy.php) por afinidad académica vs. [`TuRedFeedStrategy`](file:///c:/xampp/htdocs/utell/api/src/Strategies/TuRedFeedStrategy.php) por red de amigos).

### Minuto 11 a 15 — Seguridad, Auditoría, Tablero BI, Resguardo y Pruebas (T02–T09 + Demo)
* Mostrar en vivo el **Tablero BI (`DashboardView.vue`)** con sus 3 niveles de *Drill-Down* y semáforo institucional, y el **Panel de Seguridad y Auditoría (`ModeracionView.vue`)** con sus 5 pestañas (*Publicaciones*, *Usuarios y Claves*, *Grupos y Permisos*, *Auditoría T06* y *Resguardo T07*).

---

## 2. Machete Conceptual por Punto de la Cátedra (Lo que tenés que saber distinguir)

### A. Diferencias Clave en los Diagramas UML (Correcciones típicas de Audoglio)
| Artefacto | Qué muestra | Regla de Oro de la Cátedra |
| :--- | :--- | :--- |
| **Diagrama de Actividad** | El flujo de trabajo del proceso de negocio paso a paso. | Debe hacerse **con calles (*swimlanes*)** separando quién ejecuta cada acción (Usuario, Interfaz SPA, API/Servicios, Base de Datos), nunca como bloques sueltos. |
| **Diagrama de Transición de Estados** | Los estados por los que pasa **una sola entidad** a lo largo de su vida (ej. `Mensaje`: *Enviado* $\rightarrow$ *Entregado* $\rightarrow$ *Leído* $\rightarrow$ *Oculto*). | Las flechas son **eventos/transiciones** y debe incluir **notas con las reglas de negocio** que gobiernan el cambio de estado. |
| **Diagrama de Casos de Uso** | Interacción funcional entre Actores y el Sistema. | No atomizar acciones triviales. **`<<include>>`** = paso obligatorio reutilizable (la flecha apunta al caso incluido, ej. *Validar Autenticación*, *Registrar Auditoría*). **`<<extend>>`** = comportamiento opcional/condicional (la flecha apunta desde la extensión hacia el caso base, ej. *Modificar Perfil* extiende a *Gestionar Perfil*). |
| **Diagrama de Dominio Actualizado** | Las clases conceptuales del problema y cómo se asocian. | **¡PROHIBIDO poner métodos y prohibido poner `idForaneo` como atributo!** En lugar de poner `idUniversidad: int` dentro de `Publicacion`, se dibuja la línea de asociación hacia la clase `Universidad` con su multiplicidad (`*` a `1`). |
| **Diagrama de Clases de Diseño** | La estructura orientada a objetos real del software. | **Lleva atributos (con visibilidad `-`, `#`, `+`) y lleva métodos.** Muestra la herencia (`Persona` $\triangleleft-$ `Usuario`; `Publicacion` abstracta $\triangleleft-$ `Experiencia` y `Consulta`) y las clases `Service` y `Repository`. |
| **Diagrama de Robustez (ICONIX)** | El puente entre el Análisis (Qué) y el Diseño (Cómo). | Conecta los 4 estereotipos: **Actor** $\rightarrow$ **Boundary** (Vistas `.vue`) $\rightarrow$ **Control** (Rutas/Servicios `.php`) $\rightarrow$ **Entity** (Modelos/Tablas). Regla: un Actor solo habla con un Boundary; una Entity solo habla con un Control. |
| **DER (Entidad-Relación)** | El modelo físico relacional implementado en MySQL. | Acá **sí** van las tablas físicas, las claves primarias (`PK`), las claves foráneas (`FK`) y la cardinalidad pata de gallo (`1:N`, `N:M` resueltas con tablas intermedias como `detalle_grupo_permiso` y `oferta_academica`). |

---

### B. Los 3 Patrones GoF de U-Tell Explicados en 30 Segundos Cada Uno

1. **Patrón Creacional — `Factory Method` ([`PublicacionFactory`](file:///c:/xampp/htdocs/utell/api/src/Factories/PublicacionFactory.php)):**
   * **Problema que resuelve:** Evitar que `PublicacionService` tenga lógica acoplada para validar e instanciar distintos tipos de publicaciones (`Experiencia` vs. `Consulta`).
   * **Cómo funciona en U-Tell:** `PublicacionFactory::crear($datos)` recibe el payload crudo, verifica las invariantes comunes (`idUsuario`, `titulo`/`texto`) y bifurca la parametrización: si es `Experiencia`, normaliza y acota los tres puntajes (`puntuacionClima`, `puntuacionInfraestructura`, `puntuacionAcademica`) en el rango $[1.0..5.0]$; si es `Consulta`, inicializa `resuelta = false`.
   * *(Bonus Creacional)*: **`Singleton`** en [`db::getInstance()`](file:///c:/xampp/htdocs/utell/api/src/config/db.php) con constructor `private` para reutilizar una única conexión PDO por request.

2. **Patrón Estructural — `Facade` ([`DashboardFacade`](file:///c:/xampp/htdocs/utell/api/src/Facades/DashboardFacade.php)):**
   * **Problema que resuelve:** El Tablero Ejecutivo BI necesita cruzar 6 tablas (`universidades`, `sedes`, `oferta_academica`, `carreras`, `publicaciones`, `respuestas`), calcular promedios ponderados, clasificar semáforos (`VERDE`, `AMARILLO`, `ROJO`) y armar 3 niveles de *Drill-Down*. Si el controlador o el frontend llamaran a cada repositorio por separado, habría un alto acoplamiento.
   * **Cómo funciona en U-Tell:** `DashboardFacade` expone 3 métodos simples de alto nivel (`obtenerPanoramaGlobal()`, `obtenerDesgloseUniversidad($idUni)`, `obtenerDetalleCarrera($idSede, $idCarrera)`) que encapsulan toda la complejidad analítica y devuelven el JSON listo para Chart.js.
   * *(Bonus Estructural)*: **`Composite`** en [`GrupoPermiso`](file:///c:/xampp/htdocs/utell/api/src/Models/GrupoPermiso.php) y [`Permiso`](file:///c:/xampp/htdocs/utell/api/src/Models/Permiso.php), tratando de forma uniforme permisos individuales (hojas) y grupos de permisos (compuestos).

3. **Patrón de Comportamiento — `Strategy` ([`IFeedStrategy`](file:///c:/xampp/htdocs/utell/api/src/Strategies/IFeedStrategy.php)):**
   * **Problema que resuelve:** El muro principal (CU4) debe poder cambiar su algoritmo de recomendación dinámicamente sin llenar `PublicacionService` de `if/else` gigantes.
   * **Cómo funciona en U-Tell:** Define la interfaz `IFeedStrategy` con el método `obtenerFeed(int $idUsuario): array`. Tiene dos estrategias concretas: [`ParaTiFeedStrategy`](file:///c:/xampp/htdocs/utell/api/src/Strategies/ParaTiFeedStrategy.php) (ordena por afinidad académica y relevancia general) y [`TuRedFeedStrategy`](file:///c:/xampp/htdocs/utell/api/src/Strategies/TuRedFeedStrategy.php) (filtra exclusivamente publicaciones de amigos activos en la tabla `amigos`). `PublicacionService::obtenerFeedSegunEstrategia(IFeedStrategy $strategy, $idUsuario)` delega la ejecución polimórficamente.

---

### C. Pruebas de Caja Blanca y Caja Negra (Punto T09 — Explicación Matemática)

#### 1. Caja Blanca (Método de la Ruta Básica de McCabe)
* **Qué evalúa:** La estructura interna del código (todas las sentencias y todas las decisiones lógicas `if/else` al menos una vez).
* **Cómo calculaste la Complejidad Ciclomática $V(G)$:**
  En el grafo de flujo de validación de mensajería/publicación tenemos **$N = 11$ nodos**, **$E = 14$ aristas** y **$P_n = 4$ nodos predicado** (decisiones `if`).
  Las 3 fórmulas de McCabe dan siempre el mismo número:
  1. $V(G) = E - N + 2P = 14 - 11 + 2(1) = \mathbf{5}$
  2. $V(G) = \text{Nodos Predicado} + 1 = 4 + 1 = \mathbf{5}$
  3. $V(G) = \text{Regiones del grafo} = \mathbf{5}$
* **Qué significa $V(G) = 5$:** Que existen exactamente **5 caminos linealmente independientes** (caminos que introducen al menos una arista nueva) y por lo tanto se necesitan como mínimo **5 casos de prueba (`CB-01` a `CB-05`)** para lograr el $100\%$ de cobertura de ramas. Además, como $V(G) = 5 \le 10$, el método es altamente mantenible, cohesivo y de bajo riesgo según el estándar de McCabe.

#### 2. Caja Negra (Partición Equivalente + Análisis de Valores Límite)
* **Qué evalúa:** Las entradas y salidas según la especificación funcional, sin mirar el código interno.
* **Partición Equivalente:** Divide el dominio de entrada en clases válidas e inválidas (ej. para los puntajes de una `Experiencia` cuyo rango válido es $[1..5]$: Partición Inválida Inferior $< 1$, Partición Válida $[1..5]$, Partición Inválida Superior $> 5$).
* **Análisis de Valores Límite (AVL):** Como la mayoría de los bugs ocurren en las fronteras de las particiones, se prueban los bordes exactos y sus adyacentes: **$0$** ($\text{mín} - 1$), **$1$** ($\text{mín}$), **$5$** ($\text{máx}$) y **$6$** ($\text{máx} + 1$).

---

## 3. Las 15 Preguntas Clásicas del Tribunal (y cómo responderlas con solvencia)

1. **«¿Por qué elegiste una arquitectura SPA + API REST en N-Capas en lugar de un monolito PHP tradicional con vistas mezcladas?»**
   > *Respuesta:* Por el principio de **Separación de Responsabilidades (*Separation of Concerns*)** y escalabilidad. Al desacoplar la presentación en Vue.js 3 de la lógica de negocio en PHP Slim, el frontend solo maneja estado visual y experiencia de usuario reactiva, mientras que la API REST centraliza las reglas de negocio, la seguridad JWT y el acceso a datos por PDO. Si mañana queremos sumar una app móvil nativa en Android/iOS, consume exactamente los mismos endpoints REST sin tocar una sola línea del backend.

2. **«¿Cómo prevenís la Inyección SQL (SQLi) y el robo de sesiones en U-Tell?»**
   > *Respuesta:* En tres niveles:
   > 1) **Acceso a Datos:** La única capa que toca MySQL es `Repositories`, y el $100\%$ de las consultas usan **PDO Prepared Statements** (`$this->db->prepare()` con parámetros tipados `:id`), lo que separa el árbol sintáctico SQL de los datos del usuario.
   > 2) **Contraseñas:** Jamás se guardan en texto plano; se hashean con **Bcrypt (`password_hash` con `PASSWORD_BCRYPT`)** y se validan con `password_verify`.
   > 3) **Sesiones y Autorización:** Usamos tokens **JWT firmados con HMAC-SHA256 (`JWTService`)** verificados en cada ruta protegida por `AuthMiddleware`, sumado a la validación de permisos RBAC en `SeguridadService`.

3. **«¿Por qué en tu base de datos las publicaciones y los usuarios no se borran con `DELETE FROM` cuando un usuario elimina su cuenta o un moderador baja un post?»**
   > *Respuesta:* Porque aplicamos **Baja Lógica (*Soft Delete*)** mediante las columnas `estadoCuenta = 'inactivo'` en `usuarios` y `estado = 'eliminado'` en `publicaciones`. Si hiciéramos un borrado físico (`DELETE`), destruiríamos la integridad referencial histórica, alteraríamos los promedios estadísticos del Tablero Ejecutivo BI y perderíamos la evidencia forense en `log_auditoria`.

4. **«¿Cómo resolviste en la Iteración 2 el requisito de que un usuario pueda borrar un mensaje privado sin que se le borre al otro participante?»**
   > *Respuesta:* Como el mensaje es un único registro compartido entre emisor y receptor en la tabla `mensajedirecto`, implementé **banderas de visibilidad unilateral** (`ocultoEmisor` y `ocultoReceptor`). Cuando un usuario ejecuta `DELETE /mensajes/{id}/visibilidad` («Eliminar para mí»), `MensajeRepository::ocultarMensaje()` evalúa si el actor es el emisor o el receptor y activa únicamente su flag (`ocultoEmisor = 1` o `ocultoReceptor = 1`). Así el mensaje desaparece de su chat pero se preserva intacto para el interlocutor y para auditoría.

5. **«¿Qué es la regla del Vínculo Habilitante en el Caso de Uso 12 (Enviar Mensaje)?»**
   > *Respuesta:* Es una regla de negocio de privacidad para evitar que U-Tell se convierta en una red con spam, sin perder su propósito de orientación vocacional. `AmistadRepository::verificarVinculoHabilitante()` autoriza el envío de un mensaje privado solo en dos casos: **a)** si entre ambos usuarios existe una amistad en estado `aceptada` (CU11), o **b)** si la conversación involucra un perfil con rol de **Postulante/Aspirante** consultando con fines académicos. Si no se cumple, devuelve `HTTP 403` y la interfaz ofrece enviar la solicitud de amistad con un clic.

6. **«¿Cuál es la diferencia entre Prevención, Minimización y Contingencia en tu matriz de Gestión de Riesgos?»**
   > *Respuesta:*
   > * **Prevención** actúa *antes* de que el riesgo ocurra para **reducir su Probabilidad** (ej. exigir rol verificado `Estudiante`/`Docente` para publicar una `Experiencia`).
   > * **Minimización (Mitigación)** busca **reducir el Impacto** en caso de que falle la prevención (ej. contar con moderación comunitaria y filtros).
   > * **Contingencia** es el plan reactivo que se ejecuta **después de que el riesgo ya ocurrió** (ej. ejecutar la baja lógica del post apócrifo y degradar el rol del infractor desde el panel de Moderación, dejando rastro en `log_auditoria`).

7. **«Explicame cómo cumple tu sistema el Punto T06 de Auditoría (Trazabilidad, Login-Logout y Reportes).»**
   > *Respuesta:*
   > 1) **Trazabilidad de transformaciones:** Cuando un Administrador cambia el rol de un usuario (`PUT /usuarios/{id}/rol`), bloquea una cuenta, modifica un grupo de permisos o modera una publicación, `SeguridadRepository` consulta primero el estado actual en la BD, lo serializa en JSON dentro de la columna **`valorOriginal`**, ejecuta el cambio y guarda el nuevo estado JSON en **`valorNuevo`** junto con el `idUsuario` actor, la `fechaHora` y la `ip`.
   > 2) **Login-Logout:** Tanto `POST /login` (exitoso o fallido) como `POST /logout` registran el evento en `log_auditoria`.
   > 3) **Reporte:**En el Panel de Moderación (`/moderacion`, pestaña *Auditoría T06*) se visualiza la bitácora en vivo con filtros por categoría y la comparativa *Antes $\rightarrow$ Después*.

8. **«¿Cómo aporta valor para la toma de decisiones tu Tablero de Reportes (Punto A02)?»**
   > *Respuesta:* No es un listado plano, sino un **Tablero Ejecutivo de Inteligencia de Negocios (BI)** con navegación **Drill-Down en 3 niveles** y **semaforización automática**:
   > * **Nivel 1 (Macro):** Cruza todas las universidades activas, calcula el índice global de satisfacción y les asigna un semáforo (`VERDE` $\ge 4.5$, `AMARILLO` $\ge 3.5$, `ROJO` $< 3.5$) comparando Rigor Académico, Clima e Infraestructura en gráficos Chart.js.
   > * **Nivel 2 (Meso):** Al hacer clic en una universidad, desglosa sus **Sedes y Carreras** para detectar qué carrera puntual está traccionando hacia arriba o hacia abajo el promedio institucional.
   > * **Nivel 3 (Micro):** Al hacer clic en una carrera, muestra las **publicaciones y reseñas granulares** que justifican ese puntaje, permitiendo a un decano o a un aspirante tomar decisiones basadas en evidencia trazable.

9. **«¿Por qué elegiste la política de resguardo GFS con `--single-transaction` y hash `SHA-256` en el Punto T07?»**
   > *Respuesta:* Porque todas las tablas de U-Tell utilizan el motor transaccional **InnoDB**. Al ejecutar el volcado con consistencia transaccional, el backup toma una instantánea consistente sin bloquear la lectura ni la escritura de los usuarios conectados. Además, generamos una firma criptográfica **`SHA-256`** sobre el archivo `.sql`: antes de restaurar un backup ante una contingencia, verificamos el hash para garantizar que el archivo no sufrió corrupción ni adulteración. Incluso instrumentamos un generador de Backup SQL + SHA-256 en 1 clic dentro del panel de Administración (`/moderacion`).

10. **«¿Cómo modelaste la herencia de Usuarios y de Publicaciones en la base de datos vs. en las clases PHP?»**
    > *Respuesta:* En la **Capa de Dominio (PHP)** tenemos herencia pura: `Usuario` extiende de `Persona` (reutilizando `nombre`, `apellido`, `fNac`), y `Experiencia` y `Consulta` extienden de la clase abstracta `Publicacion`. En la **Capa de Persistencia (MySQL)** aplicamos el patrón de mapeo objeto-relacional **Single Table Inheritance (*Tabla Única por Jerarquía*)** sobre la tabla `publicaciones` discriminada por la columna `tipoPublicacion` (`'Experiencia'` vs. `'Consulta'`). Esto evita `JOINs` costosos al listar el feed mezclado y simplifica las claves foráneas de `respuestas` y `favoritos`.

---

## 4. Recorrido Sugerido para la Demo en Vivo (5 Minutos sin Fallos)

Tené abiertas dos pestañas con **`https://utell.alwaysdata.net`** (o tu entorno local si no hubiera internet en el aula):

1. **Paso 1 — Login y Recuperación de Clave (`Puntos T02/T03`):**
   * En `/login`, hacé clic en **«¿Olvidaste tu contraseña?»** para mostrar que está implementado el modal de recuperación con validación de fecha de nacimiento y auditoría.
   * Iniciá sesión con tu cuenta de Administrador.
2. **Paso 2 — Iteración 1: Feed Dinámico (`Strategy`), Muro de Universidad y Crear Publicación (`Factory Method`):**
   * En `/feed`, alterná entre las pestañas **«Para Ti»** y **«Tu Red»** (mencioná que por detrás ejecuta `ParaTiFeedStrategy` y `TuRedFeedStrategy`).
   * Entrá al muro de una universidad (ej. `/universidad/1` o `/universidad/3`) para mostrar las sedes, carreras y reseñas filtradas.
3. **Paso 3 — Iteración 2: Mensajería Privada y Vínculo Habilitante (`CU12 / CU13`):**
   * Entrá a **«Mensajes»** (`/mensajes`), enviá un mensaje en vivo (mostrando el estado de lectura) y mostrá el botón de papelera **«Eliminar para mí»** (borrado unilateral `DELETE /mensajes/{id}/visibilidad`).
4. **Paso 4 — Tablero Ejecutivo BI con 3 Niveles de Drill-Down (`Punto A02` + `DashboardFacade`):**
   * Entrá a **«Tablero BI»** (`/dashboard`).
   * Mostrá los KPIs globales y gráficos Chart.js (**Nivel 1**).
   * Hacé clic en una universidad para bajar al desglose de **Sedes y Carreras** (**Nivel 2**).
   * Hacé clic en una carrera para ver las **Evidencias y Reseñas granulares** (**Nivel 3**).
5. **Paso 5 — Panel de Seguridad, Grupos RBAC, Auditoría Forense y Resguardo (`Puntos T02, T03, T06, T07`):**
   * Entrá a **«Moderación»** (`/moderacion`) desde la barra lateral:
     * **Pestaña 1 (*Publicaciones*):** Ocultá o reactivá un post en vivo.
     * **Pestaña 2 (*Usuarios y Claves*):** Cambiale el rol a un usuario o usá **«🔑 Reset Clave»**.
     * **Pestaña 3 (*Grupos y Permisos*):** Mostrá los grupos RBAC (`Composite`) y abrí el modal **«Editar Grupo y Permisos»** con su checklist de permisos atómicos.
     * **Pestaña 4 (*Auditoría T06*):** Mostrá cómo quedaron registrados tus clics recién hechos con la columna **Trazabilidad (Antes $\rightarrow$ Después)**, fecha/hora e IP.
     * **Pestaña 5 (*Resguardo T07*):** Presioná el botón **«⬇ Generar y Descargar Backup SQL + SHA-256»** para que vean descargarse el `.sql` en vivo y aparecer en pantalla el hash `SHA-256` y el conteo de registros por tabla.
