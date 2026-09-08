<!--
  トップページ。
  未ログイン時：machitane-design/Main.dc.html準拠のランディング（バッジ・見出し・
    3列特徴・芽アイコン装飾）。発案者/企業・自治体への導線は、新デザインの2列カードではなく
    既存の葉っぱ型ボタン(MachitaneDoubleLeafCta)を踏襲する（チームの意向）。
  ログイン時（発案者）：「おかえりなさい」＋現在アイデア募集中の地域一覧（PDF4枚目）。
  ログイン時（企業・自治体）：「おかえりなさい」＋自分が登録した地域一覧。
    地域ごとに「詳細をみる（特色の編集）」「新着アイデア」「過去のアイデア」に進める
    （企業・自治体は複数の地域を登録できる想定のため、発案者向けの「地域一覧」画面と
    同じ並び・同じAreaCard(mode="browse")を使い、表示対象だけ自分の地域に絞っている）。
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

// 企業・自治体は「自分が登録した地域」だけを表示する。
// PostgreSQL(PDO)からのuser_idは文字列で返るため、AreaDetailView.vueのisOwnerと
// 同じ方針で数値化して比較する。
const displayedAreas = computed(() => {
  if (!isCompany.value) {
    return areas.value
  }
  return areas.value.filter((area) => Number(area.user_id) === Number(user.value?.id))
})

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
        <p class="idea-page-description">あなたが登録した地域です。地域ごとに新着・過去のアイデアを確認できます。</p>
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
    <p v-else-if="displayedAreas.length === 0 && isCompany" class="idea-empty-state">
      まだ地域を登録していません。「地域登録はこちらから→」から最初の地域を登録しましょう。
    </p>
    <p v-else-if="displayedAreas.length === 0" class="idea-empty-state">
      まだ登録されている地域がありません。
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
