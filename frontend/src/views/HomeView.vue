<!--
  トップページ。
  未ログイン時：PDF1枚目の「街タネ」ランディング（葉っぱ型ボタンで発案者/企業・自治体の
    ログインページに分岐）。
  ログイン時（発案者）：「おかえりなさい」＋現在アイデア募集中の地域一覧（PDF4枚目）。
  ログイン時（企業・自治体）：「おかえりなさい」＋登録されている地域一覧（自団体・他団体を問わず全件）。
    地域ごとに「詳細をみる（特色の編集）」「新着アイデア」「過去のアイデア」に進める
    （他団体が登録した地域でも、詳細ページで「企業・自治体が実現したいこと」掲示板への
    投稿ができるため、自団体が登録した地域だけに絞らず全件表示する。編集できるのは
    引き続きAreaDetailView.vue側のisOwnerチェックにより登録者本人のみ）。
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

// 企業・自治体アカウントでも、自団体が登録した地域だけでなく登録されている
// 地域を全件表示する。他団体の地域の詳細ページからも「企業・自治体が実現したいこと」
// 掲示板へ投稿できるため（AreaDetailView.vue参照）、閲覧自体は絞り込まない。
const displayedAreas = computed(() => areas.value)

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
      <MachitaneSprouts class="mt-landing__sprouts" />
      <p class="mt-landing__tagline">
        地域の特色・地理情報と、そこで生まれたアイデアの成功／失敗の記録を蓄積し、<br />
        似た土地で挑戦する人の道しるべになることを目指すアプリです。
      </p>
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

    <header v-else class="idea-page-header">
      <h1>おかえりなさい、{{ user?.name }} さん</h1>
      <p class="idea-page-description">現在、アイデア募集中の地域はこちら！</p>
    </header>

    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>
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
