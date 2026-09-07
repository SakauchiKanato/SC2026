import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/Auth/LoginView.vue'
import SignupView from '../views/Auth/SignupView.vue'
import { useAuthStore } from '../store/auth'
import ideaRoutes from './idea.routes'
import areaRoutes from './area.routes'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  // guestOnly: ログイン済みのユーザーがログイン/サインアップ画面を開いたらホームへ流す
  { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true } },
  { path: '/signup', name: 'signup', component: SignupView, meta: { guestOnly: true } },
  ...ideaRoutes,
  ...areaRoutes,
]

const router = createRouter({
  // import.meta.env.BASE_URLはビルド時の--baseオプションと連動する
  // サブフォルダ配信（例: /~user/project/）でもURLがずれないようにするため必須。
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to) => {
  const { isAuthenticated } = useAuthStore()

  if (to.meta.guestOnly && isAuthenticated.value) {
    return { name: 'home' }
  }

  // 書き込み系の画面（アイデア・地域の新規登録／編集）はログイン必須。
  // 未ログインのままアクセスされた場合はログイン画面へ流し、
  // ログイン後に元の画面へ戻れるようredirectをクエリに残す。
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  return true
})

export default router
