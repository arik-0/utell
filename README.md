# U-Tell - Plataforma Universitaria

U-Tell es una plataforma interactiva para la comunidad universitaria donde postulantes, estudiantes, graduados, docentes e instituciones pueden compartir experiencias verificadas, resolver consultas académicas, conectar con pares y analizar indicadores de clima estudiantil.

## Arquitectura del Proyecto

El proyecto sigue una arquitectura cliente-servidor desacoplada en capas:
- **`app/`**: Frontend SPA desarrollado en **Vue 3** (Composition API), **Pinia**, **Vue Router** y **Vite**.
- **`api/`**: Backend API RESTful en **PHP 8+ / Slim 3** estructurado en N-Capas (`rutas/`, `middleware/`, `Services/`, `Facades/`, `Factories/`, `Strategies/`, `Repositories/`, `Models/`).
- **`database/`**: Scripts SQL unificados en MySQL/MariaDB (`utf8mb4`, motor InnoDB):
  - `database/01_schema.sql`: Esquema relacional completo, claves foráneas, índices y restricciones de integridad.
  - `database/02_seed.sql`: Datos semilla coherentes de prueba (roles, permisos, geografía, universidades, sedes, oferta académica, usuarios, publicaciones e interacciones).

---

## Requisitos Previos

- **XAMPP** (o servidor con PHP >= 8.0 y MySQL 8.0+ / MariaDB 10.4+).
- **Node.js** (v18 o superior) y **npm** (para el frontend).
- **Composer** (para las dependencias del backend PHP).

---

## Instalación y Configuración

### 1. Base de Datos
1. Abre phpMyAdmin o tu cliente MySQL preferido.
2. Crea una base de datos llamada `u-tell` con cotejamiento `utf8mb4_unicode_ci`.
3. Importa en orden:
   - `database/01_schema.sql` (estructura completa de tablas).
   - `database/02_seed.sql` (datos iniciales de prueba; contraseña de cuentas demo: `123456`).
   - Alternativamente, desde consola puedes ejecutar `php api/src/config/seed.php`.

### 2. Backend (API - PHP Slim 3)
1. Abre una terminal y dirígete a la carpeta `api/`:
   ```bash
   cd api
   composer install
   ```
2. Asegúrate de que Apache (XAMPP) esté corriendo sobre `htdocs/utell`. La API responderá en `http://localhost/utell/api/public`.

### 3. Frontend (Vue 3 + Vite)
1. Abre una terminal y dirígete a la carpeta `app/`:
   ```bash
   cd app
   npm install
   npm run dev
   ```
2. Abre la URL provista por Vite (usualmente `http://localhost:5173`).

---

## Estructura de Directorios

```text
utell/
├── api/                      # Backend REST API (PHP Slim 3)
│   ├── public/               # Front Controller (index.php), .htaccess y uploads
│   └── src/
│       ├── config/           # Conexión PDO (db.php), helpers HTTP/JWT y seed CLI
│       ├── middleware/       # Validación de tokens JWT (AuthMiddleware)
│       ├── rutas/            # Controladores HTTP por dominio
│       ├── Services/         # Lógica de negocio y reglas de dominio
│       ├── Facades/          # Orquestación de KPIs ejecutivos (DashboardFacade)
│       ├── Factories/        # Creación polimórfica (PublicacionFactory)
│       ├── Strategies/       # Algoritmos de feed (ParaTi / TuRed)
│       ├── Repositories/     # Persistencia PDO con sentencias preparadas
│       └── Models/           # Entidades del dominio académico y social
├── app/                      # Frontend SPA (Vue 3 + Pinia + Vite)
│   ├── src/
│   │   ├── components/       # Componentes reutilizables
│   │   ├── views/            # Pantallas principales (Feed, Mensajes, Dashboard, etc.)
│   │   ├── store/            # Estado global reactivo (Pinia)
│   │   ├── api.js            # Cliente Axios e interceptores JWT
│   │   └── router.js         # Definición de rutas y guardas de navegación
│   └── package.json
├── database/
│   ├── 01_schema.sql         # Esquema relacional unificado
│   └── 02_seed.sql           # Dataset inicial coherente
├── docs/                     # Informes complementarios del proyecto
├── DOCUMENTACION.md          # Resumen técnico de arquitectura y flujos
└── README.md                 # Guía rápida de instalación
```
