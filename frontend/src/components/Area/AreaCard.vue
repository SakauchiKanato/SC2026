<!--
  地域カード。
  mode="home"   : おかえりなさいホーム用（発案者、ProposerHome.dc.html準拠）。
                  名前＋「募集◯件」バッジ＋「詳細をみる→」。
  mode="browse" : アイデア募集地域一覧用（企業・自治体、CompanyHome.dc.html準拠）。
                  名前＋「募集◯件」＋「詳細をみる」＋「新着アイデア」「過去のアイデア」。

  NOTE: 以前はmode="home"にも新着・過去アイデアへのリンクを表示していたが
        （QA報告書 2026-09-08の指摘を受けた一時対応）、発案者が他の発案者の
        アイデアを見て新規投稿の内容が偏る（意見の集中がわかりにくくなる）ことを避けるため、
        新着・過去アイデアの閲覧は企業・自治体側（mode="browse"）専用に戻した。

  NOTE: ProposerHome.dc.htmlにはカード上部にタグ（例：「若者文化 × 交通結節点」）が
        あるが、チームの意向で今回は表示しないことにした。
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
    <template v-if="mode === 'home'">
      <h3 class="mt-area-card__name">{{ area.name }}</h3>

      <div class="mt-area-card__footer">
        <span class="mt-area-card__count-badge">募集 {{ area.ideas_count ?? 0 }}件</span>
        <RouterLink :to="{ name: 'area-detail', params: { id: area.id } }" class="mt-area-card__detail-link">
          詳細をみる
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
            <path d="M5 12h14M13 6l6 6-6 6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </RouterLink>
      </div>
    </template>

    <template v-else>
      <h3 class="mt-area-card__name">{{ area.name }}</h3>
      <p class="mt-area-card__meta">募集{{ area.ideas_count ?? 0 }}件</p>
      <RouterLink :to="{ name: 'area-detail', params: { id: area.id } }" class="mt-area-card__link">
        詳細をみる
      </RouterLink>

      <div class="mt-area-card__links">
        <RouterLink :to="{ name: 'idea-recent', params: { areaId: area.id } }">新着アイデア</RouterLink>
        <RouterLink :to="{ name: 'idea-past', params: { areaId: area.id } }">過去のアイデア</RouterLink>
      </div>
    </template>
  </div>
</template>
