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
    meta: { requiresAuth: true },
  },
  {
    path: '/areas/:areaId/ideas/recent',
    name: 'idea-recent',
    component: () => import('../views/Idea/IdeaListView.vue'),
    props: true,
    meta: { requiresAuth: true, variant: 'recent' },
  },
  {
    path: '/areas/:areaId/ideas/past',
    name: 'idea-past',
    component: () => import('../views/Idea/IdeaListView.vue'),
    props: true,
    meta: { requiresAuth: true, variant: 'past' },
  },
  {
    path: '/ideas/:id',
    name: 'idea-detail',
    component: () => import('../views/Idea/IdeaDetailView.vue'),
    props: true,
    meta: { requiresAuth: true },
  },
]
