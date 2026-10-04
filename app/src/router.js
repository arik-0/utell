import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from './store/auth'

// Route definitions with dynamic imports (lazy loading)
const routes = [
  { path: '/',                   name: 'Login',             component: () => import('./views/Login.vue') },
  { path: '/register',           name: 'Register',          component: () => import('./views/Register.vue') },
  { path: '/dashboard',          name: 'Dashboard',         component: () => import('./views/DashboardView.vue') },
  { path: '/feed',               name: 'Feed',              component: () => import('./views/Feed.vue'),               meta: { requiresAuth: true } },
  { path: '/profile',            name: 'Profile',           component: () => import('./views/Profile.vue'),            meta: { requiresAuth: true } },
  { path: '/perfil/:id',         name: 'UserProfile',       component: () => import('./views/Profile.vue'),            meta: { requiresAuth: true } },
  { path: '/usuario/:id',        name: 'UserProfileAlt',    component: () => import('./views/Profile.vue'),            meta: { requiresAuth: true } },
  { path: '/amigos',             name: 'Amigos',            component: () => import('./views/AmigosView.vue'),         meta: { requiresAuth: true } },
  { path: '/publicaciones',      name: 'Publicaciones',     component: () => import('./views/PublicacionesView.vue'),   meta: { requiresAuth: true } },
  { path: '/mensajes',           name: 'Mensajes',          component: () => import('./views/MensajesView.vue'),       meta: { requiresAuth: true } },
  { path: '/universidad/:id',    name: 'UniversidadWall',   component: () => import('./views/UniversidadWall.vue'),    meta: { requiresAuth: true } },
  { path: '/create-consulta',    name: 'CreateConsulta',    component: () => import('./views/CreateConsulta.vue'),    meta: { requiresAuth: true } },
  { path: '/create-experiencia', name: 'CreateExperiencia', component: () => import('./views/CreateExperiencia.vue'), meta: { requiresAuth: true } },
  { path: '/post/:id',           name: 'PostDetail',        component: () => import('./views/PostDetail.vue'),        meta: { requiresAuth: true } },
]


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    ...routes,
    { path: '/:pathMatch(.*)*', name: 'NotFound', redirect: '/' }
  ]
})

// Auth & Role guard
router.beforeEach((to) => {
  const authStore = useAuthStore()

  // 1. Si ya está autenticado e intenta ir al login ('/'), redirigir según su rol
  if (to.path === '/' && authStore.user) {
    const rol = Number(authStore.user.idRol || 0)
    const perfil = authStore.user.tipoPerfil || ''
    const isStaffOrInstitution = [1, 3, 5, 6].includes(rol) || ['Administrador', 'Moderador', 'Universidad', 'Institución', 'Docente'].includes(perfil)
    return isStaffOrInstitution ? { path: '/dashboard' } : { path: '/feed' }
  }

  // 2. Rutas protegidas que requieren autenticación
  if (to.meta.requiresAuth && !authStore.user) {
    return { name: 'Login' }
  }

  // 3. Protección estricta para /dashboard:
  if (to.path === '/dashboard') {
    if (!authStore.user) {
      return { name: 'Login' }
    }
    const rol = Number(authStore.user.idRol || 0)
    const perfil = authStore.user.tipoPerfil || ''
    const isStaffOrInstitution = [1, 3, 5, 6].includes(rol) || ['Administrador', 'Moderador', 'Universidad', 'Institución', 'Docente'].includes(perfil)
    if (!isStaffOrInstitution) {
      return { name: 'Feed' }
    }
  }
})

export default router

