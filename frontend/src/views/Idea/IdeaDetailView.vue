<!-- アイデア詳細ページ（新着/過去のアイデア一覧の「詳細をみる」リンク先） -->
<script setup>
import { ref, onMounted } from 'vue'
import { fetchIdea } from '@/api/idea'

const props = defineProps({
  id: {
    type: [String, Number],
    required: true,
  },
})

const idea = ref(null)
const isLoading = ref(true)
const loadError = ref('')

function formatDate(dateString) {
  if (!dateString) return ''
  return new Intl.DateTimeFormat('ja-JP', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(dateString))
}

async function loadIdea() {
  isLoading.value = true
  loadError.value = ''
  try {
    idea.value = await fetchIdea(props.id)
  } catch (error) {
    loadError.value = 'アイデアの取得に失敗しました'
  } finally {
    isLoading.value = false
  }
}

onMounted(loadIdea)
</script>

<template>
  <section class="mt-page idea-detail-view">
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>

    <template v-else-if="idea">
      <RouterLink :to="{ name: 'area-detail', params: { id: idea.area_id } }" class="idea-detail-view__back">
        ← {{ idea.area_name }}に戻る
      </RouterLink>

      <div class="mt-idea-card__head idea-detail-view__head">
        <h1>{{ idea.title }}</h1>
        <span
          class="mt-status-pill"
          :class="idea.status === 'success' ? 'mt-status-pill--achieved' : 'mt-status-pill--not-achieved'"
        >
          {{ idea.status === 'success' ? '達成' : '未達成' }}
        </span>
      </div>

      <p class="idea-detail-view__date">{{ formatDate(idea.created_at) }}</p>

      <p class="mt-section-label">アイデアの内容</p>
      <div class="mt-box">{{ idea.content }}</div>

      <p class="mt-section-label">
        {{ idea.status === 'success' ? '達成した理由' : '未達成だった理由' }}
      </p>
      <div class="mt-box">{{ idea.reason }}</div>
    </template>
  </section>
</template>

<style scoped>
.idea-detail-view__back {
  display: inline-block;
  margin-bottom: var(--idea-spacing-md);
  color: var(--idea-color-text-muted);
}

.idea-detail-view__head {
  align-items: center;
}

.idea-detail-view__date {
  color: var(--idea-color-text-muted);
  font-size: 0.85rem;
  margin-top: -8px;
}
</style>
