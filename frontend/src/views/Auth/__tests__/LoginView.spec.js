import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'

// store/auth.jsと同じ方針で、api層をモックしてから動的importし直す（モジュールレベルの
// シングルトンstateをテスト間で持ち越さないため）。
vi.mock('../../../api/auth', () => ({
  signup: vi.fn(),
  login: vi.fn(),
  fetchMe: vi.fn(),
}))

async function setupRouter() {
  vi.resetModules()
  const authApi = await import('../../../api/auth')
  const { default: LoginView } = await import('../LoginView.vue')
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'home', component: { template: '<div>home</div>' } },
      { path: '/login/proposer', name: 'login-proposer', component: LoginView },
      { path: '/login/company', name: 'login-company', component: LoginView },
      { path: '/signup', name: 'signup', component: { template: '<div>signup</div>' } },
    ],
  })
  return { authApi, router }
}

beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
})

afterEach(() => {
  vi.useRealTimers()
})

describe('LoginView', () => {
  it('ログイン成功時: 完了表示を挟んでからhomeへ遷移する', async () => {
    vi.useFakeTimers()
    const { authApi, router } = await setupRouter()
    authApi.login.mockResolvedValue({
      data: { token: 'fake-jwt', user: { id: 1, name: '山田太郎', role: 'user' } },
    })

    router.push('/login/proposer')
    await router.isReady()

    const wrapper = mount({ template: '<router-view />' }, { global: { plugins: [router] } })

    await wrapper.find('#login-email').setValue('taro@example.com')
    await wrapper.find('#login-password').setValue('password123')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('ログインしました')
    expect(wrapper.text()).toContain('アイデア募集中の地域をチェックしましょう')
    expect(router.currentRoute.value.name).toBe('login-proposer')

    await vi.advanceTimersByTimeAsync(900)

    expect(router.currentRoute.value.name).toBe('home')
  })

  it('ログイン失敗時: 完了表示に切り替わらず、エラーメッセージを表示する', async () => {
    const { authApi, router } = await setupRouter()
    authApi.login.mockRejectedValue(new Error('メールアドレスまたはパスワードが正しくありません'))

    router.push('/login/company')
    await router.isReady()

    const wrapper = mount({ template: '<router-view />' }, { global: { plugins: [router] } })

    await wrapper.find('#login-email').setValue('taro@example.com')
    await wrapper.find('#login-password').setValue('wrong-password')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('メールアドレスまたはパスワードが正しくありません')
    expect(wrapper.text()).not.toContain('ログインしました')
    expect(router.currentRoute.value.name).toBe('login-company')
  })
})
