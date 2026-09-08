/**
 * Idea機能のルート定義（街タネUI）
 *
 * NOTE: frontend/src/router/index.js は他メンバーも触る共有ファイルのため、
 *       直接編集せずこのファイルをexportする形にしている（area.routes.jsと同じ方針）。
 *
 * アイデアは特定の地域(areaId)に紐づく（発案者が地域ページからアイデア登録に
 * 進む導線になっているため）。「新着アイデア」「過去のアイデア」は同じ
 * IdeaListView.vueをmeta.variantで出し分けている。
 */
export default [
  {
    path: '/areas/:areaId/ideas/new',
    name: 'idea-new',
    component: () => import('../views/Idea/IdeaFormView.vue'),
    props: true,
    // アイデア登録は発案者（role=user）専用。企業・自治体アカウントはアイデアを
    // 評価する側であり、登録することはできない（router/index.jsのrequiresRoleガード参照）。
    meta: { requiresAuth: true, requiresRole: 'user' },
  },
  // 新着／過去のアイデア一覧は企業・自治体（role=company）専用。発案者が他の発案者の
  // アイデアを見て新規投稿の内容が偏る（意見の集中がわかりにくくなる）ことを避けるため、
  // 発案者の画面からはリンクを外した上で、直接URLアクセスもここでブロックする。
  {
    path: '/areas/:areaId/ideas/recent',
    name: 'idea-recent',
    component: () => import('../views/Idea/IdeaListView.vue'),
    props: true,
    meta: { requiresAuth: true, requiresRole: 'company', variant: 'recent' },
  },
  {
    path: '/areas/:areaId/ideas/past',
    name: 'idea-past',
    component: () => import('../views/Idea/IdeaListView.vue'),
    props: true,
    meta: { requiresAuth: true, requiresRole: 'company', variant: 'past' },
  },
  {
    path: '/ideas/:id',
    name: 'idea-detail',
    component: () => import('../views/Idea/IdeaDetailView.vue'),
    props: true,
    meta: { requiresAuth: true },
  },
]
