import { createRouter, createWebHistory } from 'vue-router'
import ideaRoutes from './idea.routes'
import areaRoutes from './area.routes'

const routes = [
  { path: '/', redirect: '/ideas' },
  ...ideaRoutes,
  ...areaRoutes,
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
