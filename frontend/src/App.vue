<script setup>
import AppHeader from './components/Layout/AppHeader.vue'
</script>

<template>
  <div class="app-shell">
    <AppHeader />

    <main class="app-main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.app-main {
  min-height: calc(100vh - 65px);
}
</style>
