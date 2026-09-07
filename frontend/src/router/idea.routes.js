/**
 * Idea機能のルート定義
 *
 * NOTE: frontend/src/router/index.js は他メンバーも触る共有ファイルのため、
 *       直接編集せずこのファイルをexportする形にしている（area.routes.jsと同じ方針）。
 */

export default [
  {
    path: '/ideas',
    name: 'idea-list',
    component: () => import('../views/Idea/IdeaListView.vue'),
  },
  {
    path: '/ideas/new',
    name: 'idea-new',
    component: () => import('../views/Idea/IdeaFormView.vue'),
  },
]
