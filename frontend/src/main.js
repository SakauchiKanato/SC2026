import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './store/auth'
// :root のデザイントークン・共通クラス（idea-button, idea-banner等）はアプリ全体で使うため、
// 各画面で個別にimportするのではなくここで一度だけ読み込む。
import './assets/idea-theme.css'

// ルーターのナビゲーションガードがログイン状態を正しく判定できるよう、
// 最初の画面を描画する前にlocalStorageのトークンを検証しておく。
await useAuthStore().restoreSession()

const app = createApp(App)

app.use(router)
app.mount('#app')
