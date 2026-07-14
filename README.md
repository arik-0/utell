# U-Tell - Plataforma Universitaria

U-Tell es una plataforma interactiva para estudiantes universitarios, donde pueden compartir experiencias, realizar consultas sobre carreras y sedes, e interactuar con otros estudiantes.

## Arquitectura del Proyecto

El proyecto está dividido en dos partes principales:
- **`app/`**: Frontend desarrollado en **Vue 3** y **Vite**.
- **`api/`**: Backend desarrollado en PHP utilizando el microframework **Slim 3**.
- **Base de datos**: Relacional, utilizando MySQL/MariaDB (schema provisto en `u-tell.sql`).

---

## Requisitos Previos

- **XAMPP** (o cualquier servidor con PHP >= 8.0 y MySQL).
- **Node.js** (v16 o superior) y **npm** (para el frontend).
- **Composer** (para las dependencias del backend PHP).

---

## Instalación y Configuración

### 1. Base de Datos
1. Abre phpMyAdmin o tu cliente MySQL preferido.
2. Crea una base de datos llamada `u-tell`.
3. Importa el archivo `u-tell.sql` ubicado en la raíz de este proyecto.
4. (Opcional) Importa el archivo `mock_data.sql` si deseas tener datos de prueba (usuarios, universidades, carreras, publicaciones).

### 2. Backend (API - PHP Slim 3)
El backend sirve los endpoints de los cuales el frontend consume la información.
1. Abre una terminal y dirígete a la carpeta `api/`:
   ```bash
   cd api
   ```
2. Instala las dependencias de PHP usando Composer:
   ```bash
   composer install
   ```
3. Asegúrate de que el servidor Apache (en XAMPP) esté corriendo y apuntando correctamente a la carpeta `htdocs/utell`. La API estará disponible típicamente en `http://localhost/utell/api/public`.

### 3. Frontend (Vue 3 + Vite)
El frontend contiene la interfaz de usuario de la aplicación.
1. Abre una nueva terminal y dirígete a la carpeta `app/`:
   ```bash
   cd app
   ```
2. Instala las dependencias de Node:
   ```bash
   npm install
   ```
3. Inicia el servidor de desarrollo de Vite:
   ```bash
   npm run dev
   ```
4. Abre la URL que te proporciona Vite en tu navegador (usualmente `http://localhost:5173`).

---

## Estructura de Directorios

```text
utell/
├── api/                  # Código fuente del Backend (PHP Slim 3)
│   ├── public/           # Entrypoint (index.php) y archivos públicos
│   └── src/
│       ├── Models/       # Modelos de base de datos
│       ├── Repositories/ # Capa de acceso a datos
│       ├── Services/     # Lógica de negocio
│       ├── config/       # Configuración de BD y dependencias
│       └── rutas/        # Definición de rutas/endpoints de la API
├── app/                  # Código fuente del Frontend (Vue 3 + Vite)
│   ├── src/
│   │   ├── components/   # Componentes reutilizables de Vue
│   │   ├── views/        # Vistas de las páginas (Login, Feed, Profile, etc.)
│   │   ├── store/        # Manejo de estado (Pinia/Vuex)
│   │   └── router.js     # Configuración de rutas del frontend
│   └── package.json      # Dependencias del frontend
├── u-tell.sql            # Script de creación de la base de datos
├── mock_data.sql         # Script con datos de prueba
└── README.md             # Este archivo
```
