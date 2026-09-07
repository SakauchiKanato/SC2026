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
    // backend(IdeaController/AuthController/AreaController)は単一エラーを
    // {error: string} または {message: string}、バリデーションエラーを
    // {errors: string[]}（配列）または {errors: {field: string}}（オブジェクト）で返す。
    // どちらの形でも呼び出し側（各画面）が扱えるよう、messageに加えてstatus/errorsも
    // 例外オブジェクトに載せておく（api/area.jsが元々やっていたのと同じ形）。
    const message =
      body?.error ||
      body?.message ||
      (Array.isArray(body?.errors) ? body.errors.join(' / ') : null) ||
      `APIリクエストに失敗しました（status: ${response.status}）`
    const error = new Error(message)
    error.status = response.status
    error.errors = body?.errors ?? null
    throw error
  }

  return body
}

export function apiGet(path) {
  return request(path, { method: 'GET' })
}

export function apiPost(path, data) {
  return request(path, { method: 'POST', body: JSON.stringify(data) })
}

export function apiPut(path, data) {
  return request(path, { method: 'PUT', body: JSON.stringify(data) })
}

export function apiDelete(path) {
  return request(path, { method: 'DELETE' })
}
