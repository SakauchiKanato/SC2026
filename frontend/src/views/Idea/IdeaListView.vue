<script setup>
import { onMounted, reactive } from 'vue'
import '../../assets/idea-theme.css'
import IdeaCard from '../../components/Idea/IdeaCard.vue'
import { useIdeaStore } from '../../store/idea'

const { ideas, isLoading, errorMessage, loadIdeas } = useIdeaStore()

const filters = reactive({
  areaName: '',
  status: '',
})

function applyFilters() {
  loadIdeas({
    areaName: filters.areaName || undefined,
    status: filters.status || undefined,
  })
}

function resetFilters() {
  filters.areaName = ''
  filters.status = ''
  loadIdeas()
}

onMounted(() => {
  loadIdeas()
})
</script>

<template>
  <section class="idea-list-view">
    <header class="idea-page-header">
      <h1>アイデアを見る</h1>
      <p class="idea-page-description">
        これまでに登録された地域活性化アイデアの成功例・失敗例です。
      </p>
    </header>

    <form class="idea-filter-bar" @submit.prevent="applyFilters">
      <input v-model="filters.areaName" type="text" placeholder="地域名で絞り込む" />
      <select v-model="filters.status">
        <option value="">すべて</option>
        <option value="success">成功のみ</option>
        <option value="failure">失敗のみ</option>
      </select>
      <button type="submit" class="idea-button">絞り込む</button>
      <button type="button" class="idea-button idea-button--secondary" @click="resetFilters">
        リセット
      </button>
    </form>

    <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
      {{ errorMessage }}
    </p>

    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>

    <p v-else-if="ideas.length === 0" class="idea-empty-state">
      該当するアイデアがまだありません。最初のアイデアを登録してみましょう。
    </p>

    <div v-else class="idea-list">
      <IdeaCard v-for="idea in ideas" :key="idea.id" :idea="idea" />
    </div>
  </section>
</template>

<style scoped>
.idea-list-view {
  max-width: 800px;
  margin: 0 auto;
  padding: var(--idea-spacing-lg) var(--idea-spacing-md);
}

.idea-filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: var(--idea-spacing-sm);
  margin-bottom: var(--idea-spacing-lg);
}

.idea-filter-bar input[type='text'],
.idea-filter-bar select {
  border: 1px solid var(--idea-color-border);
  border-radius: var(--idea-radius);
  padding: 8px 12px;
  font-size: 0.9rem;
  font-family: inherit;
  background: var(--idea-color-surface);
  color: var(--idea-color-text);
}

.idea-list {
  display: flex;
  flex-direction: column;
  gap: var(--idea-spacing-md);
}
</style>