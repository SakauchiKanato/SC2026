import { apiGet, apiPost, apiPut } from './client'

/**
 * 指定した地域のアイデア一覧を取得する（新着アイデア／過去のアイデア画面）
 * バックエンドの想定エンドポイント: GET /api/areas/{areaId}/ideas?status=...&evaluated=...
 * @param {number|string} areaId
 * @param {{ status?: 'success' | 'failure', evaluated?: boolean }} filters
 */
export function fetchIdeas(areaId, filters = {}) {
  const params = new URLSearchParams()
  if (filters.status) params.append('status', filters.status)
  if (filters.evaluated !== undefined && filters.evaluated !== null) {
    params.append('evaluated', filters.evaluated ? '1' : '0')
  }

  const query = params.toString()
  return apiGet(`/areas/${areaId}/ideas${query ? `?${query}` : ''}`)
}

/**
 * アイデア詳細を取得する
 * バックエンドの想定エンドポイント: GET /api/ideas/{id}
 * @param {number|string} ideaId
 */
export function fetchIdea(ideaId) {
  return apiGet(`/ideas/${ideaId}`)
}

/**
 * 指定した地域にアイデアを新規登録する（ログイン必須・発案者アカウントのみ）
 * バックエンドの想定エンドポイント: POST /api/areas/{areaId}/ideas
 * NOTE: 投稿者（user_id）はフロントからは送らない。
 *       バックエンド側で認証トークンから取得する（クライアントの申告値を信用しない）。
 * NOTE: 結果（status）・理由（reason）は発案者の自己申告を廃止したため送らない。
 *       登録時点ではNULLで作成され、企業・自治体側がevaluateIdea()で後から評価する。
 * @param {number|string} areaId
 * @param {{ title: string, content: string }} idea
 */
export function createIdea(areaId, idea) {
  return apiPost(`/areas/${areaId}/ideas`, {
    title: idea.title,
    content: idea.content,
  })
}

/**
 * アイデアを評価する（達成／未達成）。ログイン必須・企業・自治体アカウントのみ、
 * かつそのアイデアが紐づく地域の登録者本人のみ実行できる。
 * バックエンドの想定エンドポイント: PUT /api/ideas/{id}/evaluate
 * @param {number|string} ideaId
 * @param {{ status: 'success'|'failure', reason: string }} evaluation
 */
export function evaluateIdea(ideaId, evaluation) {
  return apiPut(`/ideas/${ideaId}/evaluate`, {
    status: evaluation.status,
    reason: evaluation.reason,
  })
}
