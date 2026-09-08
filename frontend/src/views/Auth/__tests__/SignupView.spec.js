import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'

// store/auth.jsと同じ方針で、api層をモックしてから動的importし直す（モジュールレベルの
// シングルトンstateをテスト間で持ち越さないため）。
vi.mock('../../../api/auth', () => ({
  signup: vi.fn(),
  login: vi.fn(),
  fetchMe: vi.fn(),
}))

async function agreeToTerms(wrapper) {
  await wrapper.find('input[type="checkbox"]').setValue(true)
}

async function setupRouter() {
  vi.resetModules()
  const authApi = await import('../../../api/auth')
  const { default: SignupView } = await import('../SignupView.vue')
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'home', component: { template: '<div>home</div>' } },
      { path: '/login/proposer', name: 'login-proposer', component: { template: '<div />' } },
      { path: '/login/company', name: 'login-company', component: { template: '<div />' } },
      { path: '/signup', name: 'signup', component: SignupView },
    ],
  })
  return { authApi, router }
}

beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
})

describe('SignupView', () => {
  it('登録成功時: 完了表示に切り替わり、ボタンでhomeへ進める', async () => {
    const { authApi, router } = await setupRouter()
    authApi.signup.mockResolvedValue({
      data: { token: 'fake-jwt', user: { id: 1, name: '山田太郎', role: 'user' } },
    })

    router.push('/signup')
    await router.isReady()

    const wrapper = mount({ template: '<router-view />' }, { global: { plugins: [router] } })

    await wrapper.find('#signup-name').setValue('山田太郎')
    await wrapper.find('#signup-email').setValue('taro@example.com')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('password123')
    await agreeToTerms(wrapper)
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('登録が完了しました')
    expect(wrapper.text()).toContain('山田太郎')
    expect(router.currentRoute.value.name).toBe('signup')

    await wrapper.find('.auth-view__success-cta').trigger('click')
    await flushPromises()

    expect(router.currentRoute.value.name).toBe('home')
  })

  it('登録失敗時: 完了表示に切り替わらず、エラーメッセージを表示する', async () => {
    const { authApi, router } = await setupRouter()
    authApi.signup.mockRejectedValue(new Error('このメールアドレスは既に使用されています'))

    router.push('/signup')
    await router.isReady()

    const wrapper = mount({ template: '<router-view />' }, { global: { plugins: [router] } })

    await wrapper.find('#signup-name').setValue('山田太郎')
    await wrapper.find('#signup-email').setValue('taro@example.com')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('password123')
    await agreeToTerms(wrapper)
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('このメールアドレスは既に使用されています')
    expect(wrapper.text()).not.toContain('登録が完了しました')
  })
})
