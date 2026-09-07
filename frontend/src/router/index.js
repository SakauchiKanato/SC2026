import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/Auth/LoginView.vue'
import SignupView from '../views/Auth/SignupView.vue'
import { useAuthStore } from '../store/auth'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  // guestOnly: ログイン済みのユーザーがログイン/サインアップ画面を開いたらホームへ流す
  { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true } },
  { path: '/signup', name: 'signup', component: SignupView, meta: { guestOnly: true } },
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

  return true
})

export default router
