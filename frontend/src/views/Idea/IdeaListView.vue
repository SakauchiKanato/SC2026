<!--
  地域に紐づくアイデア一覧。
  variant="recent" : 新着アイデア（PDF10枚目）＝ まだ企業・自治体の評価が付いていないアイデア
  variant="past"    : 過去のアイデア（PDF11枚目、達成／未達成バッジ付き）＝ 評価済みのアイデア
  どちらも実体は同じideasテーブルで、evaluated（評価済みかどうか）で絞り込んで出し分けている
  （アイデア登録時の発案者の自己申告は廃止し、企業・自治体が後から達成／未達成を評価する
   仕様になったため、新着＝評価待ち、過去＝評価が確定したもの、という位置づけにしている）。
-->
<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useIdeaStore } from '../../store/idea'

const props = defineProps({
  areaId: {
    type: [String, Number],
    required: true,
  },
})

const route = useRoute()
const isPast = computed(() => route.meta.variant === 'past')

const { ideas, isLoading, errorMessage, loadIdeas } = useIdeaStore()

function excerpt(text, max = 80) {
  if (!text) return ''
  return text.length > max ? `${text.slice(0, max)}…` : text
}

onMounted(() => {
  loadIdeas(props.areaId, { evaluated: isPast.value })
})
</script>

<template>
  <section class="mt-page">
    <header class="idea-page-header">
      <h1>{{ isPast ? '過去のアイデア' : '新着アイデア' }}</h1>
      <p class="idea-page-description">
        {{ isPast ? '企業・自治体による評価が確定したアイデアです！' : '評価待ちの、最近発案されたアイデアです！' }}
      </p>
    </header>

    <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
      {{ errorMessage }}
    </p>
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="ideas.length === 0" class="idea-empty-state">
      まだアイデアが登録されていません。
    </p>

    <div v-else class="mt-idea-grid">
      <article
        v-for="idea in ideas"
        :key="idea.id"
        class="mt-idea-card"
        :class="isPast ? 'mt-idea-card--past' : 'mt-idea-card--new'"
      >
        <div class="mt-idea-card__head">
          <div>
            <p class="mt-idea-card__label">施策</p>
            <h3 class="mt-idea-card__title">{{ idea.title }}</h3>
          </div>
          <span
            v-if="isPast"
            class="mt-status-pill"
            :class="idea.status === 'success' ? 'mt-status-pill--achieved' : 'mt-status-pill--not-achieved'"
          >
            {{ idea.status === 'success' ? '達成' : '未達成' }}
          </span>
        </div>

        <p class="mt-idea-card__excerpt">{{ excerpt(idea.content) }}</p>

        <div class="mt-idea-card__footer">
          <RouterLink :to="{ name: 'idea-detail', params: { id: idea.id } }">詳細をみる</RouterLink>
        </div>
      </article>
    </div>
  </section>
</template>
