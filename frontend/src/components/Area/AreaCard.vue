<!--
  地域カード（緑）。
  mode="home"   : おかえりなさいホーム用（発案者）。「募集◯件」＋「詳細をみる」のみ。
  mode="browse" : アイデア募集地域一覧用（企業・自治体）。「詳細をみる」＋「新着アイデア」「過去のアイデア」。

  NOTE: 以前はmode="home"にも新着・過去アイデアへのリンクを表示していたが
        （QA報告書 2026-09-08の指摘を受けた一時対応）、発案者が他の発案者の
        アイデアを見て新規投稿の内容が偏る（意見の集中がわかりにくくなる）ことを避けるため、
        新着・過去アイデアの閲覧は企業・自治体側（mode="browse"）専用に戻した。
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

    <template v-else>
      <p class="mt-area-card__meta">
        <RouterLink :to="{ name: 'area-detail', params: { id: area.id } }">詳細をみる</RouterLink>
      </p>

      <div class="mt-area-card__links">
        <RouterLink :to="{ name: 'idea-recent', params: { areaId: area.id } }">新着アイデア</RouterLink>
        <RouterLink :to="{ name: 'idea-past', params: { areaId: area.id } }">過去のアイデア</RouterLink>
      </div>
    </template>
  </div>
</template>
