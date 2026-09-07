<!--
  トップページ中央の「大きな双葉」型CTA。
  1枚のSVGで双葉（発案者側／企業・自治体側のリーフが根元でつながった形）と
  茎・土台を描き、各リーフの上に foreignObject でリンクを重ねることで、
  1つの大きな双葉としての見た目とクリック領域の両方を実現している。
-->
<script setup></script>

<template>
  <svg
    class="mt-leaf-graphic"
    viewBox="0 0 600 330"
    xmlns="http://www.w3.org/2000/svg"
    role="img"
    aria-label="発案者の方はこちら／企業・自治体の方はこちら"
  >
    <!-- 土台（土のマウンド） -->
    <ellipse cx="300" cy="308" rx="70" ry="16" class="mt-leaf-graphic__mound" />
    <!-- 茎 -->
    <path d="M300,250 L300,296" class="mt-leaf-graphic__stem" />

    <!-- 右側のリーフ（企業・自治体） -->
    <path
      class="mt-leaf-graphic__lobe"
      d="M300,250
         C310,110 410,50 580,70
         C534,183 422,255 300,250 Z"
    />
    <path class="mt-leaf-graphic__vein" d="M304,232 C370,175 440,130 545,95" />

    <!-- 左側のリーフ（発案者） -->
    <path
      class="mt-leaf-graphic__lobe"
      d="M300,250
         C290,110 190,50 20,70
         C66,183 178,255 300,250 Z"
    />
    <path class="mt-leaf-graphic__vein" d="M296,232 C230,175 160,130 55,95" />

    <!-- クリック領域（左：発案者） -->
    <foreignObject x="50" y="115" width="220" height="120">
      <div xmlns="http://www.w3.org/1999/xhtml" class="mt-leaf-graphic__slot">
        <RouterLink :to="{ name: 'login-proposer' }" class="mt-leaf-graphic__link">
          発案者の方<br />はこちら
        </RouterLink>
      </div>
    </foreignObject>

    <!-- クリック領域（右：企業・自治体） -->
    <foreignObject x="330" y="115" width="220" height="120">
      <div xmlns="http://www.w3.org/1999/xhtml" class="mt-leaf-graphic__slot">
        <RouterLink :to="{ name: 'login-company' }" class="mt-leaf-graphic__link">
          企業・自治体<br />の方はこちら
        </RouterLink>
      </div>
    </foreignObject>
  </svg>
</template>
