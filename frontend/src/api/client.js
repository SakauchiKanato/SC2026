const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

/**
 * PHP側APIへの共通fetchラッパー。
 * ベースURLの付与と、エラー時のメッセージ整形をここに集約する。
 */
async function request(path, options = {}) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    credentials: 'include',
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  })

  let body = null
  try {
    body = await response.json()
  } catch {
    // 204 No Contentなど、JSONを含まないレスポンスは無視する
    body = null
  }

  if (!response.ok) {
    const message = body?.message || `APIリクエストに失敗しました（status: ${response.status}）`
    throw new Error(message)
  }

  return body
}

export function apiGet(path) {
  return request(path, { method: 'GET' })
}

export function apiPost(path, data) {
  return request(path, { method: 'POST', body: JSON.stringify(data) })
}