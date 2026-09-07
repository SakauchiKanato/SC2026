<!--
  街タネ共通ヘッダー。
  ログイン中は、右上に「相手側のログインページへの切り替えリンク」+「ログアウト」を表示する。
  （PDFデザインの前提：「企業・自治体ログイン」ボタンが表示されるページは発案者向け、
   逆に「発案者ログイン」ボタンが表示されるページは企業・自治体向け＝現在ログイン中の
   ロールとは逆側のログインページへの切り替え導線として表示している）

  NOTE(BUGFIX): ログイン中に/login/*へ遷移しようとすると、router/index.jsの
  guestOnlyガードによって毎回homeへ差し戻されてしまい、このボタンを押しても
  ログインページに一切遷移できない不具合があった。
  「別ロールに切り替える」ボタンなので、遷移前に一度ログアウトしてから
  ログインページへ遷移するようにして解消する（RouterLinkではなくbuttonにしている）。
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

function handleSwitchLogin(routeName) {
  // 先にログアウトしないと、router.push後にguestOnlyガードでhomeへ戻されてしまう
  logout()
  router.push({ name: routeName })
}
</script>

<template>
  <header class="mt-header">
    <MachitaneLogo />

    <div v-if="isAuthenticated" class="mt-header__actions">
      <button
        v-if="user?.role === 'company'"
        type="button"
        class="mt-pill mt-pill--blue"
        @click="handleSwitchLogin('login-proposer')"
      >
        発案者ログイン
      </button>
      <button
        v-else
        type="button"
        class="mt-pill mt-pill--blue"
        @click="handleSwitchLogin('login-company')"
      >
        企業・自治体ログイン
      </button>

      <button type="button" class="mt-pill mt-pill--tan" @click="handleLogout">ログアウト</button>
    </div>
  </header>
</template>
