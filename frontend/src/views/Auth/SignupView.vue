<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import SignupForm from '../../components/Auth/SignupForm.vue'
import { useAuthStore } from '../../store/auth'

const router = useRouter()
const { errorMessage, isLoading, signup } = useAuthStore()

async function handleSubmit(payload) {
  try {
    await signup(payload)
    // サインアップ成功時はトークンが発行され自動的にログイン状態になるため、
    // ログイン画面を挟まずそのままホームへ遷移する。
    router.push({ name: 'home' })
  } catch {
    // エラー内容はstoreのerrorMessageで表示するため、ここでは何もしない
  }
}
</script>

<template>
  <section class="auth-view">
    <header class="idea-page-header">
      <h1>新規登録</h1>
      <p class="idea-page-description">
        アカウントを作成して、地域活性化のアイデアを登録・閲覧しましょう。
      </p>
    </header>

    <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
      {{ errorMessage }}
    </p>

    <SignupForm @submit="handleSubmit" />

    <p v-if="isLoading" class="idea-loading-indicator">登録中です…</p>

    <p class="auth-view__switch">
      すでにアカウントをお持ちですか？
      <RouterLink to="/login">ログイン</RouterLink>
    </p>
  </section>
</template>

<style scoped>
.auth-view {
  max-width: 480px;
  margin: 0 auto;
  padding: var(--idea-spacing-lg) var(--idea-spacing-md);
}

.auth-view__switch {
  margin-top: var(--idea-spacing-md);
  font-size: 0.9rem;
  color: var(--idea-color-text-muted);
}
</style>
