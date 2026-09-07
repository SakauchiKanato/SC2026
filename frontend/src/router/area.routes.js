/**
 * Area機能のルート定義
 *
 * NOTE: frontend/src/router/index.js は他メンバーも触る共有ファイルのため、
 *       直接編集せずこのファイルをexportする形にしている。
 *       index.js側で以下のようにspreadして取り込んでもらう想定：
 *
 *   import areaRoutes from './area.routes';
 *   const routes = [...areaRoutes, ...otherRoutes];
 */

export default [
  {
    path: '/areas',
    name: 'area-list',
    component: () => import('@/views/Area/AreaListView.vue'),
  },
  {
    path: '/areas/new',
    name: 'area-new',
    component: () => import('@/views/Area/AreaFormView.vue'),
    // 地域の登録・編集はログイン必須（UX目的のガードで、真の保護は
    // バックエンド側のAuthMiddleware::requireUserId()で行っている）
    meta: { requiresAuth: true },
  },
  {
    path: '/areas/:id/edit',
    name: 'area-edit',
    component: () => import('@/views/Area/AreaFormView.vue'),
    props: true,
    meta: { requiresAuth: true },
  },
];
