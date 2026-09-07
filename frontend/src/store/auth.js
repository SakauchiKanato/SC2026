import { reactive, toRefs, computed } from 'vue'
import { signup as signupApi, login as loginApi, fetchMe as fetchMeApi } from '../api/auth'
import { setAuthToken, clearAuthToken } from '../api/client'

// exportしているのはテストからlocalStorageの読み書きを検証しやすくするため
export const TOKEN_STORAGE_KEY = 'sc2026_auth_token'

/**
 * 認証状態の管理。
 *
 * NOTE: store/idea.jsと同じ方針で、Piniaの導入有無が未確定のため
 *       Composition APIのreactiveのみで実装している（導入する場合はdefineStore()の中に
 *       このstate/actionsを移し替えるだけで置き換えられる構成）。
 *
 * NOTE(SECURITY): JWTはCookieではなくlocalStorageに保存する方式を採用している。
 *       httpOnly Cookie方式に比べXSS時の窃取リスクが高いトレードオフがあるため、
 *       将来的にセキュリティ要件が上がった場合はhttpOnly Cookie方式への切り替えを検討する。
 */
const state = reactive({
  user: null,
  isLoading: false,
  errorMessage: '',
  isInitialized: false, // 起動時のトークン検証(restoreSession)が完了したかどうか
})

function persistToken(token) {
  try {
    localStorage.setItem(TOKEN_STORAGE_KEY, token)
  } catch {
    // localStorageが使えない環境（プライベートモード等）でもアプリが落ちないようにする
  }
}

function readPersistedToken() {
  try {
    return localStorage.getItem(TOKEN_STORAGE_KEY)
  } catch {
    return null
  }
}

function clearPersistedToken() {
  try {
    localStorage.removeItem(TOKEN_STORAGE_KEY)
  } catch {
    // noop
  }
}

function setSession(token, user) {
  setAuthToken(token)
  persistToken(token)
  state.user = user
}

function clearSession() {
  clearAuthToken()
  clearPersistedToken()
  state.user = null
}

// アプリ起動時に一度だけ実行する。localStorageにトークンが残っていれば
// /me を呼んで有効性を確認し、ログイン状態を復元する。
let restoreSessionPromise = null
function restoreSession() {
  if (restoreSessionPromise) {
    return restoreSessionPromise
  }

  restoreSessionPromise = (async () => {
    const token = readPersistedToken()
    if (!token) {
      state.isInitialized = true
      return
    }

    setAuthToken(token)
    try {
      const response = await fetchMeApi()
      state.user = response.data
    } catch {
      // トークンが無効・期限切れの場合はログアウト状態に戻す
      clearSession()
    } finally {
      state.isInitialized = true
    }
  })()

  return restoreSessionPromise
}

export function useAuthStore() {
  async function signup(payload) {
    state.errorMessage = ''
    state.isLoading = true
    try {
      const response = await signupApi(payload)
      setSession(response.data.token, response.data.user)
      return response.data.user
    } catch (error) {
      state.errorMessage = error.message
      throw error
    } finally {
      state.isLoading = false
    }
  }

  async function login(payload) {
    state.errorMessage = ''
    state.isLoading = true
    try {
      const response = await loginApi(payload)
      setSession(response.data.token, response.data.user)
      return response.data.user
    } catch (error) {
      state.errorMessage = error.message
      throw error
    } finally {
      state.isLoading = false
    }
  }

  function logout() {
    clearSession()
  }

  return {
    ...toRefs(state),
    isAuthenticated: computed(() => state.user !== null),
    restoreSession,
    signup,
    login,
    logout,
  }
}
