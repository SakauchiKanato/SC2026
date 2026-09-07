<script setup>
import { useAuthStore } from '../store/auth'

const { user, isAuthenticated } = useAuthStore()
</script>

<template>
  <section class="home-view">
    <header class="idea-page-header">
      <h1 v-if="isAuthenticated">おかえりなさい、{{ user?.name }} さん</h1>
      <h1 v-else>地域活性化アイデア共有アプリ</h1>
      <p class="idea-page-description">
        地域の特色・地理情報と、そこで生まれたアイデアの成功／失敗の記録を蓄積し、
        似た土地で挑戦する人の道しるべになることを目指すアプリです。
      </p>
    </header>

    <p v-if="!isAuthenticated" class="home-view__cta-hint">
      アイデアを登録・閲覧するには、まず新規登録またはログインしてください。
    </p>
  </section>
</template>

<style scoped>
.home-view {
  max-width: 640px;
  margin: 0 auto;
  padding: var(--idea-spacing-lg) var(--idea-spacing-md);
}

.home-view__cta-hint {
  color: var(--idea-color-text-muted);
}
</style>
