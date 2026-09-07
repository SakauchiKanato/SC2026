<!--
  トップページ。
  未ログイン時：PDF1枚目の「街タネ」ランディング（葉っぱ型ボタンで発案者/企業・自治体の
    ログインページに分岐）。
  ログイン時：「おかえりなさい」＋現在アイデア募集中の地域一覧（PDF4枚目）。
-->
<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../store/auth'
import { fetchAreas } from '../api/area'
import AreaCard from '../components/Area/AreaCard.vue'

const { user, isAuthenticated } = useAuthStore()

const areas = ref([])
const isLoading = ref(false)
const loadError = ref('')

async function loadAreas() {
  isLoading.value = true
  loadError.value = ''
  try {
    areas.value = await fetchAreas()
  } catch (error) {
    loadError.value = '地域一覧の取得に失敗しました'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  if (isAuthenticated.value) {
    loadAreas()
  }
})
</script>

<template>
  <section v-if="!isAuthenticated" class="mt-landing">
    <p class="mt-landing__tagline">
      地域の特色・地理情報と、そこで生まれたアイデアの成功／失敗の記録を蓄積し、<br />
      似た土地で挑戦する人の道しるべになることを目指すアプリです。
    </p>

    <div class="mt-leaf-row">
      <RouterLink :to="{ name: 'login-proposer' }" class="mt-leaf mt-leaf--left">
        発案者の方<br />はこちら
      </RouterLink>
      <RouterLink :to="{ name: 'login-company' }" class="mt-leaf mt-leaf--right">
        企業・自治体<br />の方はこちら
      </RouterLink>
    </div>
    <div class="mt-leaf-stem"></div>
  </section>

  <section v-else class="mt-page">
    <header class="idea-page-header">
      <h1>おかえりなさい、{{ user?.name }} さん</h1>
      <p class="idea-page-description">現在、アイデア募集中の地域はこちら！</p>
    </header>

    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>
    <p v-else-if="areas.length === 0" class="idea-empty-state">
      まだ登録されている地域がありません。
    </p>

    <div v-else class="mt-area-grid">
      <AreaCard v-for="area in areas" :key="area.id" :area="area" mode="home" />
    </div>
  </section>
</template>
