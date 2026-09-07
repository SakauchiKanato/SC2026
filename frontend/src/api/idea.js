import { apiGet, apiPost } from './client'

/**
 * 指定した地域のアイデア一覧を取得する（新着アイデア／過去のアイデア画面）
 * バックエンドの想定エンドポイント: GET /api/areas/{areaId}/ideas?status=...
 * @param {number|string} areaId
 * @param {{ status?: 'success' | 'failure' }} filters
 */
export function fetchIdeas(areaId, filters = {}) {
  const params = new URLSearchParams()
  if (filters.status) params.append('status', filters.status)

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
 * 指定した地域にアイデアを新規登録する（ログイン必須）
 * バックエンドの想定エンドポイント: POST /api/areas/{areaId}/ideas
 * NOTE: 投稿者（user_id）はフロントからは送らない。
 *       バックエンド側で認証トークンから取得する（クライアントの申告値を信用しない）。
 * @param {number|string} areaId
 * @param {{ title: string, status: 'success'|'failure', content: string, reason: string }} idea
 */
export function createIdea(areaId, idea) {
  return apiPost(`/areas/${areaId}/ideas`, {
    title: idea.title,
    status: idea.status,
    content: idea.content,
    reason: idea.reason,
  })
}
