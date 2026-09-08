<!--
  ヒーロー見出し下に横一列で並ぶ芽アイコンの装飾（machitane-design/Main.dc.html準拠）。
  中央だけアクセントカラー（アンバー）にし、両端に近いものを半透明にすることで
  奥行きのあるリズムを出している。装飾目的のため aria-hidden="true"。
-->
<script setup>
const sprouts = [
  { size: 26, faint: false, amber: false },
  { size: 22, faint: true, amber: false },
  { size: 30, faint: false, amber: true },
  { size: 22, faint: true, amber: false },
  { size: 26, faint: false, amber: false },
]
</script>

<template>
  <div class="mt-sprout-row" aria-hidden="true">
    <svg
      v-for="(s, index) in sprouts"
      :key="index"
      :width="s.size"
      :height="Math.round(s.size * 1.3)"
      viewBox="0 0 26 34"
      fill="none"
      class="mt-sprout-row__icon"
      :class="{ 'mt-sprout-row__icon--faint': s.faint, 'mt-sprout-row__icon--amber': s.amber }"
    >
      <path d="M13 34V17" stroke-width="2.2" stroke-linecap="round" class="mt-sprout-row__stem" />
      <path d="M13 22C13 22 4 22 4 12C13 12 13 22 13 22Z" class="mt-sprout-row__leaf" />
      <path
        d="M13 17C13 17 22 17 22 8C13 8 13 17 13 17Z"
        class="mt-sprout-row__leaf mt-sprout-row__leaf--dark"
      />
    </svg>
  </div>
</template>
