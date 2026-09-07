import { createRouter, createWebHistory } from 'vue-router'
import ideaRoutes from './idea.routes'
import areaRoutes from './area.routes'

const routes = [
  { path: '/', redirect: '/ideas' },
  ...ideaRoutes,
  ...areaRoutes,
]

const router = createRouter({
  // import.meta.env.BASE_URLはビルド時の--baseオプションと連動する。
  // サブフォルダ配信（例: /~user/project/）でもURLがずれないようにするため必須。
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
