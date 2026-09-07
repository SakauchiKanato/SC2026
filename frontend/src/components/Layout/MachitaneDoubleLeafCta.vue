<!--
  トップページ中央の「大きな双葉」型CTA。
  1枚のSVG（装飾のみ、pointer-events:noneでクリックを透過させる）で双葉
  （発案者側／企業・自治体側のリーフが根元でつながった形）と茎・土台を描き、
  その上に本物のRouterLink（通常のHTML要素）を重ねて配置することで、
  1つの大きな双葉としての見た目とクリック領域の両方を実現している。

  NOTE(BUGFIX): 以前はSVGのforeignObject内にRouterLinkを置いていたが、
  ブラウザによってforeignObject内のクリックが正しく拾われないことがあるため、
  より確実な「SVGは装飾のみ・リンクは通常のHTML要素を絶対配置で重ねる」方式に変更した。
-->
<script setup></script>

<template>
  <div class="mt-landing__cta">
    <svg
      class="mt-leaf-graphic"
      viewBox="0 0 600 330"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
      focusable="false"
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
    </svg>

    <RouterLink
      :to="{ name: 'login-proposer' }"
      class="mt-leaf-graphic__hit mt-leaf-graphic__hit--left"
    >
      発案者の方<br />はこちら
    </RouterLink>
    <RouterLink
      :to="{ name: 'login-company' }"
      class="mt-leaf-graphic__hit mt-leaf-graphic__hit--right"
    >
      企業・自治体<br />の方はこちら
    </RouterLink>
  </div>
</template>
