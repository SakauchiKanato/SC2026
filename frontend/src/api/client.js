const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

// ログイン中はJWTをAuthorizationヘッダーに付与する。
// トークン自体はstore/auth.jsが保持し、ログイン/ログアウト時にここへ反映する
// （api/client.jsをフェッチ処理の一元窓口にし、各apiファイルがヘッダーを意識しなくて済むようにする）。
let authToken = null

export function setAuthToken(token) {
  authToken = token
}

export function clearAuthToken() {
  authToken = null
}

// api/area.js等、request()を経由せず独自にfetch()するモジュールから
// 現在のトークンを参照するためのgetter。
export function getAuthToken() {
  return authToken
}

/**
 * PHP側APIへの共通fetchラッパー。
 * ベースURLの付与と、エラー時のメッセージ整形をここに集約する。
 */
async function request(path, options = {}) {
  const headers = {
    'Content-Type': 'application/json',
    ...options.headers,
  }

  if (authToken) {
    headers['Authorization'] = `Bearer ${authToken}`
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    credentials: 'include',
    ...options,
    headers,
  })

  let body = null
  try {
    body = await response.json()
  } catch {
    // 204 No Contentなど、JSONを含まないレスポンスは無視する
    body = null
  }

  if (!response.ok) {
    // backend(IdeaController/AuthController)は単一エラーを{error: string}、
    // バリデーションエラーを{errors: string[]}で返す。どちらにも対応する。
    const message =
      body?.error ||
      (Array.isArray(body?.errors) ? body.errors.join(' / ') : null) ||
      `APIリクエストに失敗しました（status: ${response.status}）`
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
