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

const routes = [
  { path: '/',                   name: 'Login',             component: Login },
  { path: '/register',           name: 'Register',          component: Register },
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

// Auth guard
router.beforeEach((to) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.user) {
    return { name: 'Login' }
  }
})

export default router
