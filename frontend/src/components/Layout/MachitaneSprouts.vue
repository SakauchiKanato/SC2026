<!--
  背景装飾用の「双葉」パーツを複数まとめて表示するコンポーネント。
  トップページのキャッチコピー周辺に、発芽の様子を表す小さな双葉を
  高さや向きを変えてランダムに配置している（PDFデザイン準拠）。
  装飾目的のため aria-hidden="true"、クリック判定も持たせない。
-->
<script setup>
// 配置パターン（x, y, スケール, 土台の有無）。
// 高さをばらけさせることで「発芽している途中」の賑やかさを表現する。
const sprouts = [
  { x: 20, y: 46, scale: 0.85, mound: true },
  { x: 130, y: 10, scale: 0.75, mound: false },
  { x: 235, y: 44, scale: 0.95, mound: true },
  { x: 350, y: 6, scale: 0.7, mound: false },
  { x: 460, y: 40, scale: 0.9, mound: true },
  { x: 575, y: 4, scale: 0.72, mound: false },
  { x: 685, y: 42, scale: 0.9, mound: true },
  { x: 800, y: 12, scale: 0.78, mound: false },
  { x: 905, y: 44, scale: 0.85, mound: true },
]
</script>

<template>
  <svg
    class="mt-sprout-bg"
    viewBox="0 0 960 110"
    preserveAspectRatio="none"
    aria-hidden="true"
    focusable="false"
  >
    <g
      v-for="(s, index) in sprouts"
      :key="index"
      :transform="`translate(${s.x}, ${s.y}) scale(${s.scale})`"
    >
      <ellipse v-if="s.mound" cx="20" cy="46" rx="15" ry="5" class="mt-sprout-bg__mound" />
      <path d="M20,46 L20,26" class="mt-sprout-bg__stem" />
      <path
        d="M20,30 C9,27 3,16 9,8 C15,3 22,9 20,22 Z"
        class="mt-sprout-bg__leaf"
      />
      <path
        d="M20,30 C31,27 37,16 31,8 C25,3 18,9 20,22 Z"
        class="mt-sprout-bg__leaf"
      />
    </g>
  </svg>
</template>
