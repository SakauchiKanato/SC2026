<!--
  地域カード（緑）。
  mode="home"   : おかえりなさいホーム用。「募集◯件」＋「詳細をみる」＋「新着アイデア」「過去のアイデア」
  mode="browse" : アイデア募集地域一覧用。「詳細をみる」＋「新着アイデア」「過去のアイデア」

  NOTE: 以前はmode="home"（発案者のホーム画面）に新着・過去アイデアへのリンクが無く、
        発案者がアイデア一覧（IdeaListView）に辿り着く手段が実質存在しなかった
        （QA報告書 2026-09-08で指摘）。ホーム画面でも一覧を見られるよう、
        「新着アイデア」「過去のアイデア」のリンクをbrowseモードと共通化した。
-->
<script setup>
defineProps({
  area: {
    type: Object,
    required: true,
  },
  mode: {
    type: String,
    default: 'browse', // 'home' | 'browse'
  },
})
</script>

<template>
  <div class="mt-area-card">
    <h3 class="mt-area-card__name">{{ area.name }}</h3>

    <p v-if="mode === 'home'" class="mt-area-card__meta">
      募集{{ area.ideas_count ?? 0 }}件
      <br />
      <RouterLink :to="{ name: 'area-detail', params: { id: area.id } }">詳細をみる</RouterLink>
    </p>

    <p v-else class="mt-area-card__meta">
      <RouterLink :to="{ name: 'area-detail', params: { id: area.id } }">詳細をみる</RouterLink>
    </p>

    <div class="mt-area-card__links">
      <RouterLink :to="{ name: 'idea-recent', params: { areaId: area.id } }">新着アイデア</RouterLink>
      <RouterLink :to="{ name: 'idea-past', params: { areaId: area.id } }">過去のアイデア</RouterLink>
    </div>
  </div>
</template>
