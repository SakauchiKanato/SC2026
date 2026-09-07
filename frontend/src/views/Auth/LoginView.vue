<!--
  ログインページ。
  発案者ログイン(/login/proposer)と企業・自治体ログイン(/login/company)の
  2つのルートから使われる（PDFデザインの「発案者ログインページ」
  「企業・自治体ログインページ」に対応）。API自体は共通（POST /login）で、
  ロールはアカウントに紐づいた実際の値がそのまま使われる。
-->
<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import LoginForm from '../../components/Auth/LoginForm.vue'
import { useAuthStore } from '../../store/auth'

const router = useRouter()
const route = useRoute()
const { errorMessage, isLoading, login } = useAuthStore()

const isCompany = computed(() => route.name === 'login-company')
const title = computed(() => (isCompany.value ? '企業・自治体ログイン' : '発案者ログイン'))
const otherLoginRoute = computed(() => (isCompany.value ? 'login-proposer' : 'login-company'))
const otherLoginLabel = computed(() => (isCompany.value ? '発案者の方はこちら' : '企業・自治体の方はこちら'))
const signupRole = computed(() => (isCompany.value ? 'company' : 'user'))

async function handleSubmit(payload) {
  try {
    await login(payload)
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null
    router.push(redirect ?? { name: 'home' })
  } catch {
    // エラー内容はstoreのerrorMessageで表示するため、ここでは何もしない
  }
}
</script>

<template>
  <section class="mt-page auth-view">
    <header class="idea-page-header">
      <h1>{{ title }}</h1>
      <p class="idea-page-description">登録済みのメールアドレスとパスワードでログインしてください。</p>
    </header>

    <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
      {{ errorMessage }}
    </p>

    <LoginForm @submit="handleSubmit" />

    <p v-if="isLoading" class="idea-loading-indicator">ログイン中です…</p>

    <p class="auth-view__switch">
      アカウントをお持ちでないですか？
      <RouterLink :to="{ name: 'signup', query: { role: signupRole } }">新規登録</RouterLink>
    </p>
    <p class="auth-view__switch">
      <RouterLink :to="{ name: otherLoginRoute }">{{ otherLoginLabel }}</RouterLink>
    </p>
  </section>
</template>

<style scoped>
.auth-view {
  max-width: 480px;
}

.auth-view__switch {
  margin-top: var(--idea-spacing-md);
  font-size: 0.9rem;
  color: var(--idea-color-text-muted);
}
</style>
