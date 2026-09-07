<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { useAuthStore } from './store/auth'

const { user, isAuthenticated, logout } = useAuthStore()
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <RouterLink to="/" class="app-header__brand">地域活性化アイデア共有</RouterLink>

      <nav class="app-header__nav">
        <template v-if="isAuthenticated">
          <span class="app-header__user">{{ user?.name }} さん</span>
          <button type="button" class="idea-button idea-button--secondary" @click="logout">
            ログアウト
          </button>
        </template>
        <template v-else>
          <RouterLink to="/login" class="idea-button idea-button--secondary">ログイン</RouterLink>
          <RouterLink to="/signup" class="idea-button">新規登録</RouterLink>
        </template>
      </nav>
    </header>

    <main class="app-main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--idea-spacing-md) var(--idea-spacing-lg);
  border-bottom: 1px solid var(--idea-color-border);
  background: var(--idea-color-surface);
}

.app-header__brand {
  font-weight: 700;
  color: var(--idea-color-primary);
  text-decoration: none;
}

.app-header__nav {
  display: flex;
  align-items: center;
  gap: var(--idea-spacing-sm);
}

.app-header__user {
  color: var(--idea-color-text);
  font-size: 0.9rem;
  margin-right: var(--idea-spacing-sm);
}

.app-main {
  min-height: calc(100vh - 65px);
}
</style>
