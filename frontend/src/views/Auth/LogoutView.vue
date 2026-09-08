<!--
  ログアウト完了ページ。/logout/proposer, /logout/company の2ルートから使われる
  （machitane-design/ProposerLogout.dc.html・CompanyLogout.dc.htmlに対応）。

  以前は「ログアウトボタン押下→即座にhomeへ遷移」だったが、新デザインに合わせて
  ログアウト完了の確認画面を挟むようにした。ログアウト自体はAppHeader.vueの
  handleLogout()内で既に完了しており、このページは完了後の案内表示のみを担う
  （ルート名からどちらのロールでログアウトしたかを判定し、再ログイン導線の
  ラベルを出し分ける）。
-->
<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isCompany = computed(() => route.name === 'logout-company')
const loginRouteName = computed(() => (isCompany.value ? 'login-company' : 'login-proposer'))
const loginLabel = computed(() => (isCompany.value ? '企業・自治体としてログイン' : '発案者としてログイン'))
</script>

<template>
  <section class="logout-view">
    <div class="logout-view__icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path
          d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </div>
    <h1 class="logout-view__title">ログアウトしました</h1>
    <p class="logout-view__description">
      ご利用ありがとうございました。<br />
      またのご利用をお待ちしております。
    </p>
    <RouterLink :to="{ name: loginRouteName }" class="logout-view__primary">{{ loginLabel }}</RouterLink>
    <RouterLink :to="{ name: 'home' }" class="logout-view__secondary">トップページに戻る</RouterLink>
  </section>
</template>
