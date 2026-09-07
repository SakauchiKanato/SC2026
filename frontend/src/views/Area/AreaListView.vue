<template>
  <section class="mt-page">
    <div class="mt-page__header">
      <h1 class="mt-page__title">アイデア募集地域一覧</h1>
      <RouterLink :to="{ name: 'area-new' }" class="mt-pill mt-pill--tan">
        地域登録はこちらから→
      </RouterLink>
    </div>

    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>
    <p v-else-if="areas.length === 0" class="idea-empty-state">
      登録されている地域がありません
    </p>

    <div v-else class="mt-area-grid">
      <AreaCard v-for="area in areas" :key="area.id" :area="area" mode="browse" />
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AreaCard from '@/components/Area/AreaCard.vue'
import { fetchAreas } from '@/api/area'

const areas = ref([])
const isLoading = ref(true)
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

onMounted(loadAreas)
</script>
