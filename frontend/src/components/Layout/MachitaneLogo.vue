<!--
  「街タネ」のロゴ。家と木のシルエット + アプリ名のテキストロゴ。
  PDFデザインのアイコンを簡略化したSVGで再現している。
-->
<script setup>
defineProps({
  linkTo: {
    type: String,
    default: '/',
  },
})
</script>

<template>
  <RouterLink :to="linkTo" class="mt-logo">
    <svg class="mt-logo__icon" viewBox="0 0 120 70" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <path d="M4 62 L4 30 L18 18 L32 30 L32 62 Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
      <path d="M32 62 L32 22 L52 6 L72 22 L72 62 Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
      <rect x="44" y="34" width="10" height="14" stroke="currentColor" stroke-width="3" />
      <rect x="58" y="34" width="10" height="14" stroke="currentColor" stroke-width="3" />
      <path d="M72 62 L72 28 L88 16 L100 28 L100 62 Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
      <rect x="80" y="38" width="8" height="10" stroke="currentColor" stroke-width="3" />
      <path d="M108 62 L108 40 M100 46 L108 38 L116 46 M102 54 L108 48 L114 54"
        stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M2 62 H100" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
    </svg>
    <span class="mt-logo__text">街タネ</span>
  </RouterLink>
</template>
