import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/Auth/LoginView.vue'
import SignupView from '../views/Auth/SignupView.vue'
import areaRoutes from './area.routes'
import ideaRoutes from './idea.routes'
import { useAuthStore } from '../store/auth'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  // guestOnly: ログイン済みのユーザーがログイン/サインアップ画面を開いたらホームへ流す
  { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true } },
  { path: '/signup', name: 'signup', component: SignupView, meta: { guestOnly: true } },

  // Area/Idea機能のルートはそれぞれのfeature側で定義されたものをここでspreadする
  // （area.routes.js / idea.routes.js のNOTE参照）
  ...areaRoutes,
  ...ideaRoutes,
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const { isAuthenticated } = useAuthStore()

  if (to.meta.guestOnly && isAuthenticated.value) {
    return { name: 'home' }
  }

  // 未ログインで登録・編集画面を直接開いた場合、バックエンド側で401になる前に
  // ログイン画面へ誘導する（UX目的のガードであり、真の保護はバックエンド側の
  // AuthMiddleware::requireUserId()で行っている＝クライアント側だけを信用しない）
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return { name: 'login' }
  }

  return true
})

export default router
