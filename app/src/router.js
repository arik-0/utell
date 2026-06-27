import { createRouter, createWebHistory } from 'vue-router'

// Views
import Login from './views/Login.vue'
import Register from './views/Register.vue'
import Feed from './views/Feed.vue'
import Profile from './views/Profile.vue'
import CreateConsulta from './views/CreateConsulta.vue'
import CreateExperiencia from './views/CreateExperiencia.vue'
import PostDetail from './views/PostDetail.vue'

const routes = [
  { path: '/', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },
  { path: '/feed', name: 'Feed', component: Feed },
  { path: '/profile', name: 'Profile', component: Profile },
  { path: '/create-consulta', name: 'CreateConsulta', component: CreateConsulta },
  { path: '/create-experiencia', name: 'CreateExperiencia', component: CreateExperiencia },
  { path: '/post/:id', name: 'PostDetail', component: PostDetail },
  // add others later
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
