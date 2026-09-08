<!--
  地域に紐づくアイデア一覧（machitane-design/IdeasList.dc.html準拠）。
  ルートは既存の/areas/:areaId/ideas/recent・/areas/:areaId/ideas/pastのままだが、
  新デザインでは画面内のタブ切替になっているため、両方のアイデアを一度に取得し
  （evaluatedフィルタなしでfetchIdeas）、ステータスで新着／過去に振り分けて
  タブ（activeTab）で表示を切り替える。タブ切替はこのページ内の表示切替のみで、
  ルート遷移は行わない（URLは開いたときのルートのまま）。

  NOTE: idea-recentとidea-pastは同じコンポーネントを使うため、（AreaCard.vueの
  mode="browse"のように）片方から直接もう片方のリンクを踏むとVue Routerが
  コンポーネントを再マウントせず使い回すことがある。そのままだとactiveTabの初期値や
  アイデアの再取得が行われないため、route.fullPathをwatchしてルートが変わるたびに
  タブと一覧を明示的に更新している。

  新着アイデア＝status未評価（評価待ち）、過去のアイデア＝status評価済み（達成／未達成）。
  アイデア登録時の発案者の自己申告は廃止し、企業・自治体が後から達成／未達成を評価する
  仕様になったため、新着＝評価待ち、過去＝評価が確定したもの、という位置づけにしている。

  NOTE: IdeasList.dc.htmlの新着カードには「提案者：{{author}}」があるが、
        ideasテーブルには投稿者の表示名を返す仕組みが無い（user_idのみ）ため、
        日付のみを表示している。
-->
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useIdeaStore } from '../../store/idea'
import { fetchArea } from '@/api/area'

const props = defineProps({
  areaId: {
    type: [String, Number],
    required: true,
  },
})

const route = useRoute()
const { ideas, isLoading, errorMessage, loadIdeas } = useIdeaStore()

const area = ref(null)
const activeTab = ref(route.meta.variant === 'past' ? 'past' : 'new')

const newIdeas = computed(() => ideas.value.filter((idea) => idea.status === null))
const pastIdeas = computed(() => ideas.value.filter((idea) => idea.status !== null))

function formatDate(dateString) {
  if (!dateString) return ''
  return new Intl.DateTimeFormat('ja-JP', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(dateString))
}

async function load() {
  loadIdeas(props.areaId)
  try {
    area.value = await fetchArea(props.areaId)
  } catch {
    // 見出しの地域名が出せないだけなので、一覧自体の表示は継続する
  }
}

onMounted(load)

// idea-recent⇔idea-past間、または別のareaIdへの遷移でコンポーネントが再マウントされない
// 場合に備えて、ルートが変わるたびにタブの初期状態と一覧を明示的に更新する
watch(
  () => route.fullPath,
  () => {
    activeTab.value = route.meta.variant === 'past' ? 'past' : 'new'
    load()
  },
)
</script>

<template>
  <section class="mt-page idea-list-view">
    <RouterLink :to="{ name: 'area-detail', params: { id: areaId } }" class="mt-back-link">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
        <path d="M19 12H5M11 18l-6-6 6-6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      地域の詳細に戻る
    </RouterLink>

    <header class="idea-page-header">
      <h1>{{ area ? `${area.name}のアイデア` : 'アイデア一覧' }}</h1>
      <p class="idea-page-description">寄せられたアイデアと、これまで実行された施策の記録です。</p>
    </header>

    <div class="idea-list-view__tabs">
      <button
        type="button"
        class="idea-list-view__tab"
        :class="{ 'idea-list-view__tab--active': activeTab === 'new' }"
        @click="activeTab = 'new'"
      >
        新着アイデア <span class="idea-list-view__tab-count">（{{ newIdeas.length }}）</span>
      </button>
      <button
        type="button"
        class="idea-list-view__tab"
        :class="{ 'idea-list-view__tab--active': activeTab === 'past' }"
        @click="activeTab = 'past'"
      >
        過去のアイデア <span class="idea-list-view__tab-count">（{{ pastIdeas.length }}）</span>
      </button>
    </div>

    <p v-if="errorMessage" class="idea-banner idea-banner--error" role="alert">
      {{ errorMessage }}
    </p>
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>

    <template v-else-if="activeTab === 'new'">
      <p class="idea-list-view__tab-note">最近発案されたアイデアです。</p>
      <p v-if="newIdeas.length === 0" class="idea-empty-state">まだアイデアが登録されていません。</p>
      <div v-else class="mt-idea-grid">
        <article v-for="idea in newIdeas" :key="idea.id" class="mt-idea-card mt-idea-card--new">
          <h3 class="mt-idea-card__title">{{ idea.title }}</h3>
          <div class="idea-list-view__new-meta">
            <span>{{ formatDate(idea.created_at) }}</span>
            <RouterLink :to="{ name: 'idea-detail', params: { id: idea.id } }">詳細をみる →</RouterLink>
          </div>
        </article>
      </div>
    </template>

    <template v-else>
      <p class="idea-list-view__tab-note">今までに実行されてきたアイデアです。</p>
      <p v-if="pastIdeas.length === 0" class="idea-empty-state">まだ評価済みのアイデアがありません。</p>
      <div v-else class="mt-idea-grid">
        <article v-for="idea in pastIdeas" :key="idea.id" class="mt-idea-card mt-idea-card--past">
          <div class="mt-idea-card__head">
            <p class="mt-idea-card__label">施策</p>
            <span
              class="mt-status-pill"
              :class="idea.status === 'success' ? 'mt-status-pill--achieved' : 'mt-status-pill--not-achieved'"
            >
              {{ idea.status === 'success' ? '達成' : '未達成' }}
            </span>
          </div>
          <h3 class="mt-idea-card__title">{{ idea.title }}</h3>
          <div class="mt-idea-card__footer">
            <RouterLink :to="{ name: 'idea-detail', params: { id: idea.id } }">詳細をみる →</RouterLink>
          </div>
        </article>
      </div>
    </template>
  </section>
</template>

<style scoped>
.idea-list-view__tabs {
  display: flex;
  align-items: center;
  gap: 4px;
  border-bottom: 1px solid var(--line);
  margin-bottom: var(--idea-spacing-lg);
}

.idea-list-view__tab {
  padding: 14px 22px;
  margin-bottom: -1px;
  border: none;
  border-bottom: 2.5px solid transparent;
  background: transparent;
  font-family: inherit;
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--ink-faint);
  cursor: pointer;
}

.idea-list-view__tab--active {
  color: var(--ink);
  border-bottom-color: var(--brand-700);
}

.idea-list-view__tab-count {
  color: var(--ink-faint);
  font-weight: 600;
}

.idea-list-view__tab-note {
  margin: -8px 0 var(--idea-spacing-md);
  font-size: 0.85rem;
  color: var(--ink-faint);
}

.idea-list-view__new-meta {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--idea-spacing-sm);
  font-size: 0.78rem;
  color: var(--ink-soft);
}

.idea-list-view__new-meta a {
  flex-shrink: 0;
  color: var(--brand-900);
  font-weight: 700;
}
</style>
