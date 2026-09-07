/**
 * Area機能のルート定義（街タネUI）
 *
 * NOTE: frontend/src/router/index.js は他メンバーも触る共有ファイルのため、
 *       直接編集せずこのファイルをexportする形にしている。
 *
 * /areas/:id は発案者向け（閲覧＋アイデア登録導線）・企業/自治体向け（自分の
 * 登録した地域の編集）の両方を1つのコンポーネント(AreaDetailView)で兼ねる
 * （表示はログインユーザーがその地域の登録者かどうかで切り替える）。
 */
export default [
  {
    path: '/areas',
    name: 'area-list',
    component: () => import('@/views/Area/AreaListView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/areas/new',
    name: 'area-new',
    component: () => import('@/views/Area/AreaFormView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/areas/:id',
    name: 'area-detail',
    component: () => import('@/views/Area/AreaDetailView.vue'),
    props: true,
    meta: { requiresAuth: true },
  },
];
