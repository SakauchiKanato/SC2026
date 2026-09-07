<script setup>
import { useRouter, useRoute } from 'vue-router'
import LoginForm from '../../components/Auth/LoginForm.vue'
import { useAuthStore } from '../../store/auth'

const router = useRouter()
const route = useRoute()
const { errorMessage, isLoading, login } = useAuthStore()

async function handleSubmit(payload) {
  try {
    await login(payload)
    // requiresAuthなページへの未ログインアクセスからここに流れてきた場合は、
    // ログイン後に元居た画面へ戻す（router/index.jsのredirectクエリ参照）
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null
    router.push(redirect ?? { name: 'home' })
  } catch {
    // エラー内容はstoreのerrorMessageで表示するため、ここでは何もしない
  }
}
</script>

<template>
  <section class="auth-view">
    <header class="idea-page-header">
      <h1>ログイン</h1>
      <p class="idea-page-description">登録済みのメールアドレスとパスワードでログインしてください。</p>
    </header>

    <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
      {{ errorMessage }}
    </p>

    <LoginForm @submit="handleSubmit" />

    <p v-if="isLoading" class="idea-loading-indicator">ログイン中です…</p>

    <p class="auth-view__switch">
      アカウントをお持ちでないですか？
      <RouterLink to="/signup">新規登録</RouterLink>
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
