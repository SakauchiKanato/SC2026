<!--
  トップページ。
  未ログイン時：machitane-design/Main.dc.html準拠のランディング（バッジ・見出し・
    3列特徴・芽アイコン装飾）。発案者/企業・自治体への導線は、新デザインの2列カードではなく
    既存の葉っぱ型ボタン(MachitaneDoubleLeafCta)を踏襲する（チームの意向）。
  ログイン時（発案者）：machitane-design/ProposerHome.dc.html準拠。「おかえりなさい」＋
    地域名検索ボックス＋現在アイデア募集中の地域一覧。
  ログイン時（企業・自治体）：machitane-design/CompanyHome.dc.html準拠。「おかえりなさい」＋
    登録されている地域一覧（自団体・他団体を問わず全件）。地域ごとに「詳細をみる（特色の編集）」
    「新着アイデア」「過去のアイデア」に進める（他団体が登録した地域でも、詳細ページで
    「企業・自治体が実現したいこと」掲示板への投稿ができるため、自団体が登録した地域だけに
    絞らず全件表示する。編集できるのは引き続きAreaDetailView.vue側のisOwnerチェックにより
    登録者本人のみ）。なお企業ホームの「すべて/募集中/募集終了」フィルタは、地域に募集状態を
    表すカラムが無く実装できないため見送っている。
-->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../store/auth'
import { fetchAreas } from '../api/area'
import AreaCard from '../components/Area/AreaCard.vue'
import MachitaneSprouts from '../components/Layout/MachitaneSprouts.vue'
import MachitaneDoubleLeafCta from '../components/Layout/MachitaneDoubleLeafCta.vue'

const { user, isAuthenticated } = useAuthStore()

const isCompany = computed(() => user.value?.role === 'company')

const areas = ref([])
const isLoading = ref(false)
const loadError = ref('')

// 発案者ホームの地域名検索（ProposerHome.dc.html準拠、クライアント側の部分一致）
const searchQuery = ref('')

// 企業・自治体アカウントでも、自団体が登録した地域だけでなく登録されている地域を全件表示する。
// 他団体の地域の詳細ページからも「企業・自治体が実現したいこと」掲示板へ投稿できるため
// （AreaDetailView.vue参照）、閲覧自体は絞り込まない。発案者は引き続き検索ボックスで絞り込む。
const displayedAreas = computed(() => {
  if (isCompany.value) {
    return areas.value
  }
  return areas.value.filter((area) => area.name.includes(searchQuery.value))
})

const isSearchEmpty = computed(
  () => !isCompany.value && searchQuery.value.length > 0 && displayedAreas.value.length === 0,
)

async function loadAreas() {
  isLoading.value = true
  loadError.value = ''
  try {
    // APIが予期しないレスポンス（Content-Typeがapplication/json以外等）を返した場合、
    // fetchAreas()がnullを返すことがある。その場合でもdisplayedAreasのfilter()で
    // 例外にならないよう、必ず配列にフォールバックする。
    areas.value = (await fetchAreas()) ?? []
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
    <div class="mt-landing__hero">
      <div class="mt-landing__eyebrow">REGIONAL IDEA PLATFORM</div>
      <h1 class="mt-landing__title">
        地域のアイデアに、<br />
        育つ場所を。
      </h1>
      <p class="mt-landing__tagline">
        地域の特色・地理情報と、そこで生まれたアイデアの成功／失敗の記録を蓄積し、<br />
        似た土地で挑戦する人の道しるべになることを目指すアプリです。
      </p>
    </div>

    <MachitaneSprouts />

    <div class="mt-landing__feature-grid">
      <div class="mt-landing__feature-card">
        <svg class="mt-landing__feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M4 20V10M12 20V4M20 20V13" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <div class="mt-landing__feature-text">地域の特色データを蓄積</div>
      </div>
      <div class="mt-landing__feature-card">
        <svg class="mt-landing__feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path
            d="M9 11l3 3 8-8M20 12v6a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h9"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
        <div class="mt-landing__feature-text">アイデアの成功・失敗を記録</div>
      </div>
      <div class="mt-landing__feature-card">
        <svg class="mt-landing__feature-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M12 2L4 6v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-4z" stroke-width="2" stroke-linejoin="round" />
        </svg>
        <div class="mt-landing__feature-text">似た地域への道しるべに</div>
      </div>
    </div>

    <MachitaneDoubleLeafCta class="mt-landing__cta" />
  </section>

  <section v-else class="mt-page">
    <div v-if="isCompany" class="mt-page__header">
      <div>
        <h1 class="mt-page__title">おかえりなさい、{{ user?.name }} さん</h1>
        <p class="idea-page-description">
          登録されている地域の一覧です。地域ごとに新着・過去のアイデアを確認したり、
          あなたの団体が実現したいことを投稿できます。
        </p>
      </div>
      <RouterLink :to="{ name: 'area-new' }" class="mt-pill mt-pill--tan">
        地域登録はこちらから→
      </RouterLink>
    </div>

    <template v-else>
      <header class="idea-page-header">
        <h1>おかえりなさい、{{ user?.name }} さん</h1>
        <p class="idea-page-description">現在、アイデア募集中の地域はこちらです。</p>
      </header>

      <div class="mt-searchbox">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <circle cx="11" cy="11" r="7" stroke-width="2" />
          <path d="M21 21l-4.3-4.3" stroke-width="2" stroke-linecap="round" />
        </svg>
        <input v-model="searchQuery" type="text" placeholder="地域名で検索（例：渋谷）" />
      </div>
    </template>

    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>
    <div v-else-if="isSearchEmpty" class="mt-search-empty">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
        <circle cx="11" cy="11" r="7" stroke-width="2" />
        <path d="M21 21l-4.3-4.3" stroke-width="2" stroke-linecap="round" />
      </svg>
      <p>「{{ searchQuery }}」に一致する地域が見つかりませんでした。</p>
    </div>
    <p v-else-if="displayedAreas.length === 0" class="idea-empty-state">
      まだ登録されている地域がありません。{{
        isCompany ? '「地域登録はこちらから→」から最初の地域を登録しましょう。' : ''
      }}
    </p>

    <div v-else class="mt-area-grid">
      <AreaCard
        v-for="area in displayedAreas"
        :key="area.id"
        :area="area"
        :mode="isCompany ? 'browse' : 'home'"
      />
    </div>
  </section>
</template>
