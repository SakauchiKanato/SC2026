import { createRouter, createWebHashHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/Auth/LoginView.vue'
import SignupView from '../views/Auth/SignupView.vue'
import LogoutView from '../views/Auth/LogoutView.vue'
import { useAuthStore } from '../store/auth'
import ideaRoutes from './idea.routes'
import areaRoutes from './area.routes'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  // guestOnly: ログイン済みのユーザーがログイン/サインアップ画面を開いたらホームへ流す
  {
    path: '/login/proposer',
    name: 'login-proposer',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: '/login/company',
    name: 'login-company',
    component: LoginView,
    meta: { guestOnly: true },
  },
  { path: '/signup', name: 'signup', component: SignupView, meta: { guestOnly: true } },
  // AppHeaderのログアウトボタンから遷移する完了画面（machitane-design/ProposerLogout.dc.html・
  // CompanyLogout.dc.htmlに対応）。ログアウト後に遷移するのでguestOnly。
  {
    path: '/logout/proposer',
    name: 'logout-proposer',
    component: LogoutView,
    meta: { guestOnly: true },
  },
  {
    path: '/logout/company',
    name: 'logout-company',
    component: LogoutView,
    meta: { guestOnly: true },
  },
  ...ideaRoutes,
  ...areaRoutes,
]

const router = createRouter({
  // hashモード（URL末尾に "#/" を挟む方式。例: https://.../SC2026/#/areas/1）。
  //
  // なぜhistoryモード（createWebHistory）から変更したか:
  //   history.pushState方式は、/areas/1のようなURLへの直接アクセス・
  //   リロード時に、サーバー側でindex.htmlへのフォールバック設定
  //   （frontend/public/.htaccessのFallbackResource・mod_rewrite）が
  //   効いている必要がある。本番サーバー（gms.gdl.jp）ではその設定を
  //   入れても直接アクセス時に素のApache 404が返る不具合が本番QAで
  //   確認された。原因はサーバー側のAllowOverride設定等の可能性が高いが、
  //   管理者権限で本番サーバーに入る手段が無いため、こちらからは
  //   確認・修正ができない（経緯はfrontend/public/.htaccessのコメント参照）。
  //
  //   hashモード（createWebHashHistory）は「#」より後ろの部分
  //   （例: #/areas/1）を一切サーバーに送信しないため、サーバーは
  //   常にindex.htmlだけを返せばよく、上記のサーバー設定に依存しない。
  //   トレードオフとして全URLの形が変わる（共有リンクやブラウザ履歴の
  //   見た目が変わる。SEO上も不利になりうる）。
  //
  //   サーバー側のAllowOverride設定を直せる場合は、このコミットを取り消す
  //   （createWebHistory(import.meta.env.BASE_URL)に戻す）ことでURL形式を
  //   元に戻せる。
  history: createWebHashHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to) => {
  const { isAuthenticated, user } = useAuthStore()

  if (to.meta.guestOnly && isAuthenticated.value) {
    return { name: 'home' }
  }

  // 書き込み・閲覧系の画面はログイン必須。未ログインでのアクセスは
  // トップページ（発案者/企業・自治体のログイン選択画面）へ戻す。
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    return { name: 'home' }
  }

  // ロール限定の画面（例：アイデア登録は発案者(user)専用）。
  // 該当しないロールでのアクセスはホームへ戻す（実際の認可はバックエンド側でも行う）。
  if (to.meta.requiresRole && user.value?.role !== to.meta.requiresRole) {
    return { name: 'home' }
  }

  return true
})

export default router
