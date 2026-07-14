# Documentación Técnica de U-Tell

## 1. Visión General
U-Tell es una plataforma interactiva destinada a la comunidad universitaria. Su principal objetivo es conectar a estudiantes de diversas instituciones para que puedan realizar consultas, compartir experiencias sobre sus carreras y universidades, y crear una red de contactos académicos.

El sistema se diseñó con una arquitectura cliente-servidor separando el Frontend y el Backend para permitir mayor escalabilidad y facilidad de mantenimiento.

---

## 2. Arquitectura del Sistema

### 2.1 Frontend (Aplicación Cliente)
El cliente fue desarrollado utilizando **Vue 3** (Composition API con `<script setup>`) y empaquetado mediante **Vite**, lo cual ofrece un entorno de desarrollo extremadamente rápido y compilaciones optimizadas.

- **Ubicación:** Carpeta `/app`.
- **Enrutamiento:** Se utiliza `vue-router` para manejar las vistas (SPA - Single Page Application). En el archivo `src/router.js` se definen rutas para vistas como Login, Registro, Feed Principal (Muro), Creación de Consultas/Experiencias, Detalles de Publicaciones y Perfil de Usuario.
- **Gestión de Estado:** (Opcional/Según implementación) Normalmente se puede encontrar soporte para `Pinia` o `Vuex` dentro de `src/store` para manejar el estado global (ej. datos del usuario autenticado).
- **Componentes:** La carpeta `src/components/` aloja los elementos visuales reutilizables (tarjetas de post, barras de navegación, botones, etc.), mientras que `src/views/` contiene las pantallas principales.

### 2.2 Backend (API REST)
El servidor expone una API REST construida con el microframework **Slim 3** en PHP. Su objetivo es procesar las peticiones del Frontend, validar datos y realizar operaciones contra la base de datos MySQL.

- **Ubicación:** Carpeta `/api`.
- **Estructura Interna:**
  - `public/index.php`: Es el punto de entrada de todas las peticiones HTTP (Front Controller). Se encarga de instanciar la aplicación Slim y cargar las rutas.
  - `src/rutas/`: Contiene archivos separados por dominio de negocio (ej. `usuarios.php`, `publicaciones.php`, `auth.php`, `instituciones.php`). Cada archivo define los endpoints (GET, POST, PUT, DELETE).
  - `src/Models/` y `src/Repositories/`: Encargados de la lógica de negocio y del mapeo objeto-relacional o consultas directas mediante PDO para la interacción segura con la base de datos.
  - `src/config/`: Contiene la configuración de conexión a la base de datos, habitualmente retornando una instancia de PDO.

### 2.3 Base de Datos (Relacional)
El motor de base de datos utilizado es **MySQL / MariaDB**. El diseño relacional incluye entidades complejas para manejar todo el ecosistema universitario.
- **Entidades Principales:**
  - `usuarios`, `roles`: Gestión de acceso y perfiles.
  - `universidades`, `sedes`, `carreras`, `oferta_academica`: Representan el núcleo académico de la plataforma.
  - `consultas`, `respuestas`, `experiencias`: Elementos principales de interacción social, donde los usuarios generan el contenido (Posts).
  - `amigos`, `mensajedirecto`: Módulo de interacción social directa (Red Social).
  - `ciudades`, `provincias`, `paises`: Normalización geográfica para las sedes y usuarios.
- El archivo `u-tell.sql` contiene la estructura, y `mock_data.sql` inicializa la base de datos con registros de prueba.

---

## 3. Flujos Principales de la Aplicación

### 3.1 Autenticación
1. El usuario ingresa credenciales en `Login.vue`.
2. Se envía un `POST /api/auth` al backend.
3. El archivo `auth.php` verifica el hash de la contraseña y, si es correcto, genera un token (JWT) o establece una sesión, devolviendo la información del perfil y el rol.

### 3.2 Visualización del Feed
1. Al acceder a `Feed.vue`, el componente dispara una petición `GET /api/publicaciones` (o similar).
2. Slim Framework recibe la petición y, desde `publicaciones.php`, consulta a la base de datos las últimas *consultas* y *experiencias*.
3. El frontend recibe un arreglo en formato JSON y renderiza los componentes de tarjeta mostrando el autor, texto y "likes" de cada publicación.

### 3.3 Creación de una Consulta
1. El usuario va a `CreateConsulta.vue`.
2. Selecciona la Universidad, Sede y Carrera (datos cargados vía endpoints `GET` de `instituciones.php`).
3. Envía el formulario (`POST`). El backend registra la nueva consulta asociándola al ID del usuario activo.

---

## 4. Tecnologías Involucradas

- **Lenguajes:** JavaScript, HTML5, CSS3, PHP, SQL.
- **Frameworks:** Vue.js 3, Slim Framework 3.
- **Herramientas de Build:** Vite, Composer, npm.
- **Base de Datos:** MySQL / MariaDB (XAMPP).

---

Este documento sirve como resumen técnico. Para ver el código en detalle, revisar los directorios `app/src` y `api/src`.
