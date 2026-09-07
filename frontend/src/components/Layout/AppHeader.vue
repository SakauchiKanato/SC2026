<!--
  街タネ共通ヘッダー。
  ログイン中は、右上に「相手側のログインページへの切り替えリンク」+「ログアウト」を表示する。
  （PDFデザインの前提：「企業・自治体ログイン」ボタンが表示されるページは発案者向け、
   逆に「発案者ログイン」ボタンが表示されるページは企業・自治体向け＝現在ログイン中の
   ロールとは逆側のログインページへの切り替え導線として表示している）
-->
<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/auth'
import MachitaneLogo from './MachitaneLogo.vue'

const router = useRouter()
const { user, isAuthenticated, logout } = useAuthStore()

function handleLogout() {
  logout()
  router.push({ name: 'home' })
}
</script>

<template>
  <header class="mt-header">
    <MachitaneLogo />

    <div v-if="isAuthenticated" class="mt-header__actions">
      <RouterLink
        v-if="user?.role === 'company'"
        :to="{ name: 'login-proposer' }"
        class="mt-pill mt-pill--blue"
      >
        発案者ログイン
      </RouterLink>
      <RouterLink v-else :to="{ name: 'login-company' }" class="mt-pill mt-pill--blue">
        企業・自治体ログイン
      </RouterLink>

      <button type="button" class="mt-pill mt-pill--tan" @click="handleLogout">ログアウト</button>
    </div>
  </header>
</template>
