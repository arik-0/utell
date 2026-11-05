# Documentación Técnica de U-Tell

## 1. Visión General
U-Tell es una plataforma interactiva destinada a la comunidad universitaria. Conecta postulantes, estudiantes, graduados, docentes e instituciones educativas para realizar consultas académicas, compartir experiencias estructuradas sobre carreras y sedes, comunicarse mediante mensajería directa y analizar métricas institucionales mediante un Tablero de Comando Ejecutivo.

El sistema implementa una arquitectura cliente-servidor desacoplada con separación estricta de responsabilidades en N-Capas.

---

## 2. Arquitectura del Sistema

### 2.1 Frontend (Aplicación Cliente)
Desarrollado en **Vue 3** (`<script setup>`), **Pinia**, **Vue Router 4**, **Axios** y **Chart.js**, empaquetado con **Vite**.

- **Ubicación:** Carpeta `/app`.
- **Enrutamiento (`src/router.js`):** Gestiona vistas públicas y protegidas con guardas por sesión y rol (Login, Registro, Muro Principal, Detalle de Publicación, Creación de Consultas/Experiencias, Mensajería Directa, Red de Contactos, Perfil de Usuario, Muros Institucionales y Tablero Ejecutivo).
- **Gestión de Estado (`src/store/`):** Stores reactivos en Pinia para sesión/JWT (`auth.js`), notificaciones visuales (`toast.js`) y tema claro/oscuro (`theme.js`).
- **Cliente HTTP (`src/api.js`):** Instancia centralizada de Axios que adjunta automáticamente `Authorization: Bearer <token>` y gestiona expiraciones `401 Unauthorized`.

### 2.2 Backend (API RESTful)
Construido sobre **PHP 8+** y **Slim Framework 3** bajo arquitectura N-Capas y principios SOLID.

- **Ubicación:** Carpeta `/api`.
- **Capas Internas:**
  - `public/index.php`: Front Controller, configuración CORS y registro modular de rutas.
  - `src/middleware/AuthMiddleware.php`: Validación criptográfica HS256 de tokens JWT e inyección de identidad en el request.
  - `src/rutas/`: Controladores livianos encargados de recibir peticiones HTTP, validar parámetros de entrada y delegar a Servicios/Fachadas.
  - `src/Services/`, `src/Facades/`, `src/Factories/`, `src/Strategies/`: Reglas de negocio, polimorfismo de publicaciones (*Factory*), curación de feeds (*Strategy*) e indicadores ejecutivos (*Facade*).
  - `src/Repositories/`: Acceso exclusivo a datos mediante PDO y *Prepared Statements*.
  - `src/Models/`: Entidades del modelo de dominio.

### 2.3 Base de Datos Relacional
Motor **MySQL / MariaDB** con codificación `utf8mb4` e integridad referencial InnoDB.
- **Scripts Unificados (`/database`):**
  - `database/01_schema.sql`: Definición íntegra del esquema (seguridad RBAC compuesta `roles`/`permisos`/`grupos_permisos`, geografía `paises`/`provincias`/`ciudades`, núcleo académico `universidades`/`sedes`/`carreras`/`oferta_academica`/`inscripciones`, herencia de tabla única `publicaciones`, interacciones `respuestas`/`favoritos`, grafo social `amigos`/`mensajedirecto` y trazabilidad `log_auditoria`).
  - `database/02_seed.sql`: Dataset coherente calibrado para pruebas funcionales y analítica institucional.

---

## 3. Flujos Principales

### 3.1 Autenticación y Autorización
1. El usuario envía credenciales desde `Login.vue` hacia `POST /auth/login` (o Google OAuth `POST /auth/google`).
2. `AuthService` verifica el hash bcrypt (`password_verify`), comprueba el estado de la cuenta, registra el evento en `log_auditoria` y emite un JWT firmado.
3. Las rutas protegidas verifican el token mediante `AuthMiddleware` y contrastan la identidad del token frente a los recursos solicitados.

### 3.2 Visualización y Creación de Publicaciones
1. `Feed.vue` consume los feeds personalizados (`ParaTiFeedStrategy` / `TuRedFeedStrategy`) desde `PublicacionService`.
2. Al publicar desde `CreateConsulta.vue` o `CreateExperiencia.vue`, `PublicacionFactory` instancia la entidad correspondiente (incluyendo puntajes multidimensionales de Clima, Infraestructura y Calidad Académica en Experiencias) y `PublicacionRepository` la persiste en `publicaciones`.

### 3.3 Tablero de Comando Ejecutivo
1. Usuarios con rol institucional o administrativo acceden a `/dashboard` (`DashboardView.vue`).
2. `DashboardFacade` calcula KPIs globales, semáforos estratégicos y desgloses jerárquicos (*Drill-Down* Universidad → Sede → Carrera → Publicaciones base).

---

## 4. Tecnologías Involucradas

- **Lenguajes:** JavaScript (ES2022+), HTML5, CSS3, PHP 8+, SQL.
- **Frameworks y Librerías:** Vue 3, Pinia, Vue Router, Chart.js, Axios, Slim Framework 3, `firebase/php-jwt`.
- **Herramientas:** Vite, Composer, npm, GitHub Actions (CI/CD).
- **Base de Datos:** MySQL / MariaDB.
