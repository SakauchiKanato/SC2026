<!--
  新規登録ページ。/login/proposer, /login/company の「新規登録」リンクから
  role=user|companyが渡ってくる（machitane-design/ProposerSignup.dc.html・
  CompanySignup.dc.htmlに対応）。

  企業・自治体側は新デザインでは「団体名」「ご担当者名」の2項目だが、usersテーブルには
  name列しかないため、両ロールとも既存の単一「お名前」項目のまま扱う（Company機能は
  別メンバーが開発中で、テーブル変更はそちらと衝突する可能性があるため今回は行わない）。

  登録成功時はトークンが発行され自動的にログイン状態になるため、新デザインの
  「ログイン画面へ」ボタンではなく「ホームへ進む」ボタンにして、ログイン画面を
  経由せずそのままホームへ進めるようにしている。
-->
<script setup>
import { computed, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import SignupForm from '../../components/Auth/SignupForm.vue'
import { useAuthStore } from '../../store/auth'

const router = useRouter()
const route = useRoute()
const { errorMessage, isLoading, signup } = useAuthStore()

// /login/proposer, /login/company の「新規登録」リンクからrole=user|companyが渡ってくる
const defaultRole = computed(() => (route.query.role === 'company' ? 'company' : 'user'))
const isCompany = computed(() => defaultRole.value === 'company')
const title = computed(() => (isCompany.value ? '企業・自治体 新規登録' : '発案者 新規登録'))
const description = computed(() =>
  isCompany.value
    ? 'アカウントを作成して、地域のアイデア募集をはじめましょう'
    : 'アカウントを作成して、地域へのアイデア投稿をはじめましょう',
)

const signupSucceeded = ref(false)
const registeredName = ref('')

async function handleSubmit(payload) {
  try {
    const user = await signup(payload)
    registeredName.value = user.name
    signupSucceeded.value = true
  } catch {
    // エラー内容はstoreのerrorMessageで表示するため、ここでは何もしない
  }
}

function goHome() {
  router.push({ name: 'home' })
}
</script>

<template>
  <section class="mt-page auth-view">
    <template v-if="!signupSucceeded">
      <header class="auth-view__header">
        <h1 class="auth-view__title">{{ title }}</h1>
        <p class="auth-view__description">{{ description }}</p>
      </header>

      <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
        {{ errorMessage }}
      </p>

      <SignupForm :default-role="defaultRole" @submit="handleSubmit" />

      <p v-if="isLoading" class="idea-loading-indicator">登録中です…</p>

      <p class="auth-view__switch">
        すでにアカウントをお持ちですか？
        <RouterLink :to="{ name: 'login-proposer' }">発案者ログイン</RouterLink>
        /
        <RouterLink :to="{ name: 'login-company' }">企業・自治体ログイン</RouterLink>
      </p>
    </template>

    <div v-else class="auth-view__success">
      <div class="auth-view__success-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
          <path d="M5 13l4 4L19 7" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <h1 class="auth-view__title">登録が完了しました</h1>
      <p class="auth-view__description">
        「{{ registeredName }}」さんのアカウントを作成しました。<br />
        さっそくはじめましょう。
      </p>
      <button type="button" class="auth-view__success-cta" @click="goHome">ホームへ進む</button>
    </div>
  </section>
</template>
