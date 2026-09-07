import { apiGet, apiPost } from './client'

/**
 * アイデア一覧を取得する
 * バックエンドの想定エンドポイント: GET /api/ideas?area_id=...&area_name=...&status=...
 * @param {{ areaId?: number|string, areaName?: string, status?: 'success' | 'failure' }} filters
 */
export function fetchIdeas(filters = {}) {
  const params = new URLSearchParams()
  if (filters.areaId) params.append('area_id', filters.areaId)
  if (filters.areaName) params.append('area_name', filters.areaName)
  if (filters.status) params.append('status', filters.status)

  const query = params.toString()
  return apiGet(`/ideas${query ? `?${query}` : ''}`)
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
 * アイデアを新規登録する
 * バックエンドの想定エンドポイント: POST /api/ideas
 * NOTE: 投稿者（user_id）はフロントからは送らない。
 *       バックエンド側で認証トークンから取得すること（クライアントの申告値を信用しない）。
 * NOTE(area_id): 地域は自由入力ではなく、登録済みAreaの中からarea_idで指定する
 *       （存在しない地域名でも登録できてしまう不具合の修正）。
 * @param {{ title: string, areaId: number|string, status: 'success'|'failure', content: string, reason: string }} idea
 */
export function createIdea(idea) {
  return apiPost('/ideas', {
    title: idea.title,
    area_id: idea.areaId,
    status: idea.status,
    content: idea.content,
    reason: idea.reason,
  })
}
