import { apiGet, apiPost } from './client'

/**
 * サインアップする
 * バックエンドの想定エンドポイント: POST /api/signup
 * 成功時は { data: { token, user } } が返る（自動ログイン）
 * @param {{ name: string, email: string, password: string, role: 'user'|'company' }} payload
 */
export function signup(payload) {
  return apiPost('/signup', {
    name: payload.name,
    email: payload.email,
    password: payload.password,
    role: payload.role,
  })
}

/**
 * ログインする
 * バックエンドの想定エンドポイント: POST /api/login
 * 成功時は { data: { token, user } } が返る
 *
 * roleは「どちらのログインページから送られたリクエストか」をバックエンドに伝えるための値。
 * アカウントの実際のroleと一致しない場合（例: 発案者アカウントで企業・自治体ログインページ
 * からログインしようとした場合）は403エラーになる（AuthController::login()参照）。
 * @param {{ email: string, password: string, role?: 'user'|'company' }} payload
 */
export function login(payload) {
  return apiPost('/login', {
    email: payload.email,
    password: payload.password,
    role: payload.role,
  })
}

/**
 * 現在ログイン中のユーザー情報を取得する（トークンの有効性チェックにも使う）
 * バックエンドの想定エンドポイント: GET /api/me
 */
export function fetchMe() {
  return apiGet('/me')
}
