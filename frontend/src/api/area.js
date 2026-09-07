/**
 * Area関連のAPIクライアント
 * バックエンド（生PHP）の /api/areas, /api/feature-tags エンドポイントを呼び出す
 *
 * NOTE(認証統一): 以前はここだけ独自のfetch実装で、Authorizationヘッダーを
 *       付けていなかった（PHPセッション前提だったため）。AreaControllerが
 *       JWT認証（AuthMiddleware::requireUserId()）に統一されたのに合わせて、
 *       api/client.jsの共通fetchラッパー（JWTを自動付与する）を使うように変更した。
 *       これをやらないと、ログイン中でも地域の登録・編集・削除が401になってしまう。
 */
import { apiGet, apiPost, apiPut, apiDelete } from './client'

export function fetchAreas() {
  return apiGet('/areas')
}

export function fetchArea(id) {
  return apiGet(`/areas/${id}`)
}

export function createArea(payload) {
  return apiPost('/areas', payload)
}

export function updateArea(id, payload) {
  return apiPut(`/areas/${id}`, payload)
}

export function deleteArea(id) {
  return apiDelete(`/areas/${id}`)
}

/**
 * 特色タグの選択肢一覧を取得する（登録・編集フォームのプルダウン用）
 * 誰かが新しいタグを登録すると、以後このAPIの結果に含まれるようになる
 */
export function fetchFeatureTags() {
  return apiGet('/feature-tags')
}
