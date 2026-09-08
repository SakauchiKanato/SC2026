<!--
  ログインページ。
  発案者ログイン(/login/proposer)と企業・自治体ログイン(/login/company)の
  2つのルートから使われる（machitane-design/ProposerLogin.dc.html・CompanyLogin.dc.htmlに
  対応）。API自体は共通（POST /login）で、ロールはアカウントに紐づいた実際の値がそのまま使われる。

  ログイン成功時は新デザイン通り一旦「ログインしました」の完了表示を挟んでからhomeへ遷移する
  （LOGIN_SUCCESS_DISPLAY_MS経過後に自動遷移）。

  NOTE: ログイン画面ではメールアドレスの存在有無を推測されないよう、形式チェックはあえて
        行わない方針（LoginForm.vue参照）。新デザインの形式チェック機能はこの方針を優先し、
        今回は追加していない。
-->
<script setup>
import { computed, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import LoginForm from '../../components/Auth/LoginForm.vue'
import { useAuthStore } from '../../store/auth'

const LOGIN_SUCCESS_DISPLAY_MS = 900

const router = useRouter()
const route = useRoute()
const { errorMessage, isLoading, login } = useAuthStore()

const loginSucceeded = ref(false)

const isCompany = computed(() => route.name === 'login-company')
const title = computed(() => (isCompany.value ? '企業・自治体ログイン' : '発案者ログイン'))
const description = computed(() =>
  isCompany.value ? '地域データの確認とアイデア募集はこちらから' : '地域へのアイデア投稿はこちらから',
)
const successMessage = computed(() =>
  isCompany.value
    ? '担当地域のアイデア募集状況を確認しましょう。'
    : 'おかえりなさい。アイデア募集中の地域をチェックしましょう。',
)
const otherLoginRoute = computed(() => (isCompany.value ? 'login-proposer' : 'login-company'))
const otherLoginLabel = computed(() => (isCompany.value ? '発案者の方はこちら' : '企業・自治体の方はこちら'))
const signupRole = computed(() => (isCompany.value ? 'company' : 'user'))

async function handleSubmit(payload) {
  try {
    await login(payload)
    loginSucceeded.value = true
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null
    setTimeout(() => {
      router.push(redirect ?? { name: 'home' })
    }, LOGIN_SUCCESS_DISPLAY_MS)
  } catch {
    // エラー内容はstoreのerrorMessageで表示するため、ここでは何もしない
  }
}
</script>

<template>
  <section class="mt-page auth-view">
    <template v-if="!loginSucceeded">
      <header class="auth-view__header">
        <h1 class="auth-view__title">{{ title }}</h1>
        <p class="auth-view__description">{{ description }}</p>
      </header>

      <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
        {{ errorMessage }}
      </p>

      <LoginForm :expected-role="isCompany ? 'company' : 'user'" @submit="handleSubmit" />

      <p v-if="isLoading" class="idea-loading-indicator">ログイン中です…</p>

      <p class="auth-view__switch">
        アカウントをお持ちでないですか？
        <RouterLink :to="{ name: 'signup', query: { role: signupRole } }">新規登録</RouterLink>
      </p>
      <p class="auth-view__switch">
        <RouterLink :to="{ name: otherLoginRoute }">{{ otherLoginLabel }}</RouterLink>
      </p>
    </template>

    <div v-else class="auth-view__success">
      <div class="auth-view__success-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
          <path d="M5 13l4 4L19 7" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <h1 class="auth-view__title">ログインしました</h1>
      <p class="auth-view__description">{{ successMessage }}</p>
    </div>
  </section>
</template>

<style scoped>
.auth-view {
  max-width: 480px;
}

.auth-view__header {
  text-align: center;
  margin-bottom: var(--idea-spacing-lg);
}

.auth-view__title {
  font-family: 'Zen Maru Gothic', 'Noto Sans JP', sans-serif;
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0 0 8px;
  color: var(--ink);
}

.auth-view__description {
  margin: 0;
  font-size: 0.85rem;
  color: var(--ink-soft);
}

.auth-view__switch {
  text-align: center;
  margin-top: var(--idea-spacing-md);
  font-size: 0.9rem;
  color: var(--idea-color-text-muted);
}

.auth-view__success {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 0;
}

.auth-view__success-icon {
  width: 56px;
  height: 56px;
  border-radius: var(--mt-radius-pill);
  background: var(--surface-sage);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  stroke: var(--brand-900);
}
</style>
