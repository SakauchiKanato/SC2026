<script setup>
</script>

<template>
  <div class="app-shell">
    <header class="app-header">
      <h1 class="app-title">地域活性化アイデア共有アプリ</h1>
      <nav class="app-nav">
        <router-link to="/ideas">アイデアを見る</router-link>
        <router-link to="/ideas/new">アイデアを登録する</router-link>
        <router-link to="/areas">地域一覧</router-link>
        <router-link to="/areas/new">地域を登録する</router-link>
      </nav>
    </header>

    <main class="app-main">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
.app-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.app-header {
  padding: 16px 24px;
  border-bottom: 1px solid #e2e2e2;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.app-title {
  margin: 0;
  font-size: 1.1rem;
}

.app-nav {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}

.app-nav a {
  color: inherit;
  text-decoration: none;
  font-size: 0.9rem;
}

.app-nav a:hover {
  text-decoration: underline;
}

.app-nav a.router-link-active {
  font-weight: 700;
}

.app-main {
  flex: 1;
  width: 100%;
}
</style>
