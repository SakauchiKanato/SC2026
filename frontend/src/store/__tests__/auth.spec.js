import { describe, it, expect, vi, beforeEach } from 'vitest'

// store/auth.js はモジュールレベルでstateを1つだけ持つシングルトン構成（store/idea.jsと同じ方針）。
// テスト間で状態を持ち越さないよう、毎回 vi.resetModules() してから動的importし直す。
vi.mock('../../api/auth', () => ({
  signup: vi.fn(),
  login: vi.fn(),
  fetchMe: vi.fn(),
}))

async function freshStore() {
  vi.resetModules()
  const authApi = await import('../../api/auth')
  const { useAuthStore, TOKEN_STORAGE_KEY } = await import('../auth')
  return { authApi, useAuthStore, TOKEN_STORAGE_KEY }
}

beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
})

describe('useAuthStore', () => {
  it('login成功時: userが設定され、isAuthenticatedがtrueになる', async () => {
    const { authApi, useAuthStore } = await freshStore()
    const fakeUser = { id: 1, name: '山田太郎', email: 'taro@example.com', role: 'user' }
    authApi.login.mockResolvedValue({ data: { token: 'fake-jwt', user: fakeUser } })

    const store = useAuthStore()
    const result = await store.login({ email: 'taro@example.com', password: 'password123' })

    expect(authApi.login).toHaveBeenCalledWith({ email: 'taro@example.com', password: 'password123' })
    expect(result).toEqual(fakeUser)
    expect(store.user.value).toEqual(fakeUser)
    expect(store.isAuthenticated.value).toBe(true)
    expect(store.errorMessage.value).toBe('')
  })

  it('login失敗時: errorMessageが設定され、呼び出し元にエラーが伝播する', async () => {
    const { authApi, useAuthStore } = await freshStore()
    authApi.login.mockRejectedValue(new Error('メールアドレスまたはパスワードが正しくありません'))

    const store = useAuthStore()
    await expect(
      store.login({ email: 'taro@example.com', password: 'wrong-password' })
    ).rejects.toThrow('メールアドレスまたはパスワードが正しくありません')

    expect(store.isAuthenticated.value).toBe(false)
    expect(store.errorMessage.value).toBe('メールアドレスまたはパスワードが正しくありません')
  })

  it('signup成功時: 自動的にログイン状態になる（トークンが発行されるため）', async () => {
    const { authApi, useAuthStore } = await freshStore()
    const fakeUser = { id: 2, name: '鈴木花子', email: 'hanako@example.com', role: 'user' }
    authApi.signup.mockResolvedValue({ data: { token: 'fake-jwt', user: fakeUser } })

    const store = useAuthStore()
    await store.signup({ name: '鈴木花子', email: 'hanako@example.com', password: 'password123' })

    expect(store.isAuthenticated.value).toBe(true)
    expect(store.user.value).toEqual(fakeUser)
  })

  it('logout: userがnullになり、localStorageのトークンも消える', async () => {
    const { authApi, useAuthStore, TOKEN_STORAGE_KEY } = await freshStore()
    authApi.login.mockResolvedValue({
      data: { token: 'fake-jwt', user: { id: 1, name: '山田太郎' } },
    })

    const store = useAuthStore()
    await store.login({ email: 'taro@example.com', password: 'password123' })
    expect(localStorage.getItem(TOKEN_STORAGE_KEY)).toBe('fake-jwt')

    store.logout()

    expect(store.user.value).toBeNull()
    expect(store.isAuthenticated.value).toBe(false)
    expect(localStorage.getItem(TOKEN_STORAGE_KEY)).toBeNull()
  })

  it('restoreSession: トークンが無ければ何もせずisInitializedがtrueになる', async () => {
    const { authApi, useAuthStore } = await freshStore()

    const store = useAuthStore()
    await store.restoreSession()

    expect(authApi.fetchMe).not.toHaveBeenCalled()
    expect(store.isInitialized.value).toBe(true)
    expect(store.isAuthenticated.value).toBe(false)
  })

  it('restoreSession: 有効なトークンがあればユーザー情報を復元する', async () => {
    const { authApi, useAuthStore, TOKEN_STORAGE_KEY } = await freshStore()
    const fakeUser = { id: 1, name: '山田太郎' }
    localStorage.setItem(TOKEN_STORAGE_KEY, 'existing-token')
    authApi.fetchMe.mockResolvedValue({ data: fakeUser })

    const store = useAuthStore()
    await store.restoreSession()

    expect(authApi.fetchMe).toHaveBeenCalled()
    expect(store.user.value).toEqual(fakeUser)
    expect(store.isAuthenticated.value).toBe(true)
    expect(store.isInitialized.value).toBe(true)
  })

  it('restoreSession: トークンが無効/期限切れならログアウト状態に戻し、localStorageからも削除する', async () => {
    const { authApi, useAuthStore, TOKEN_STORAGE_KEY } = await freshStore()
    localStorage.setItem(TOKEN_STORAGE_KEY, 'expired-token')
    authApi.fetchMe.mockRejectedValue(new Error('トークンの有効期限が切れています'))

    const store = useAuthStore()
    await store.restoreSession()

    expect(store.user.value).toBeNull()
    expect(store.isAuthenticated.value).toBe(false)
    expect(localStorage.getItem(TOKEN_STORAGE_KEY)).toBeNull()
    expect(store.isInitialized.value).toBe(true)
  })
})
