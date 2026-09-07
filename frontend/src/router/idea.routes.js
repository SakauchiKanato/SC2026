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
    // アイデアの登録はログイン必須（UX目的のガードで、真の保護は
    // バックエンド側のAuthMiddleware::requireUserId()で行っている）
    meta: { requiresAuth: true },
  },
]
