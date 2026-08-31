import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from './store/auth'

// Views
import Login              from './views/Login.vue'
import Register           from './views/Register.vue'
import Feed               from './views/Feed.vue'
import Profile            from './views/Profile.vue'
import CreateConsulta     from './views/CreateConsulta.vue'
import CreateExperiencia  from './views/CreateExperiencia.vue'
import PostDetail         from './views/PostDetail.vue'
import PublicacionesView  from './views/PublicacionesView.vue'
import MensajesView       from './views/MensajesView.vue'
import AmigosView         from './views/AmigosView.vue'
import UniversidadWall    from './views/UniversidadWall.vue'
import DashboardView      from './views/DashboardView.vue'

const routes = [
  { path: '/',                   name: 'Login',             component: Login },
  { path: '/register',           name: 'Register',          component: Register },
  { path: '/dashboard',          name: 'Dashboard',         component: DashboardView },
  { path: '/feed',               name: 'Feed',              component: Feed,               meta: { requiresAuth: true } },
  { path: '/profile',            name: 'Profile',           component: Profile,            meta: { requiresAuth: true } },
  { path: '/perfil/:id',         name: 'UserProfile',       component: Profile,            meta: { requiresAuth: true } },
  { path: '/usuario/:id',        name: 'UserProfileAlt',    component: Profile,            meta: { requiresAuth: true } },
  { path: '/amigos',             name: 'Amigos',            component: AmigosView,         meta: { requiresAuth: true } },
  { path: '/publicaciones',      name: 'Publicaciones',     component: PublicacionesView,   meta: { requiresAuth: true } },
  { path: '/mensajes',           name: 'Mensajes',          component: MensajesView,       meta: { requiresAuth: true } },
  { path: '/universidad/:id',    name: 'UniversidadWall',   component: UniversidadWall,    meta: { requiresAuth: true } },
  { path: '/create-consulta',    name: 'CreateConsulta',    component: CreateConsulta,    meta: { requiresAuth: true } },
  { path: '/create-experiencia', name: 'CreateExperiencia', component: CreateExperiencia, meta: { requiresAuth: true } },
  { path: '/post/:id',           name: 'PostDetail',        component: PostDetail,        meta: { requiresAuth: true } },
]


const router = createRouter({
  history: createWebHistory(),
  routes
})

// Auth & Role guard
router.beforeEach((to) => {
  const authStore = useAuthStore()

  // 1. Require authentication for protected app routes
  if (to.meta.requiresAuth && !authStore.user) {
    return { name: 'Login' }
  }

  // 2. Strict Role Protection for /dashboard:
  // If a student user is logged in and navigates to /dashboard, redirect to /feed
  if (to.path === '/dashboard' && authStore.user) {
    const rol = Number(authStore.user.idRol || 0)
    const perfil = authStore.user.tipoPerfil || ''
    const isStaffOrInstitution = [1, 3, 5, 6].includes(rol) || ['Administrador', 'Moderador', 'Universidad', 'Institución', 'Docente'].includes(perfil)
    if (!isStaffOrInstitution) {
      return { name: 'Feed' }
    }
  }
})

export default router

