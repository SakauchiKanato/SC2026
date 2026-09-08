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

async function setup(role) {
  vi.resetModules()
  const authApi = await import('../../../api/auth')
  const { useAuthStore } = await import('../../../store/auth')
  const { default: AppHeader } = await import('../AppHeader.vue')

  authApi.login.mockResolvedValue({
    data: { token: 'fake-jwt', user: { id: 1, name: 'テストユーザー', role } },
  })
  const store = useAuthStore()
  await store.login({ email: 'test@example.com', password: 'password123' })

  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'home', component: { template: '<div>home</div>' } },
      { path: '/login/proposer', name: 'login-proposer', component: { template: '<div />' } },
      { path: '/login/company', name: 'login-company', component: { template: '<div />' } },
      { path: '/logout/proposer', name: 'logout-proposer', component: { template: '<div />' } },
      { path: '/logout/company', name: 'logout-company', component: { template: '<div />' } },
    ],
  })
  router.push('/')
  await router.isReady()

  const wrapper = mount(AppHeader, { global: { plugins: [router] } })
  return { store, router, wrapper }
}

beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
})

describe('AppHeader', () => {
  it('発案者(user)がログアウトすると、セッションを消してlogout-proposerへ遷移する', async () => {
    const { store, router, wrapper } = await setup('user')

    await wrapper.find('button.mt-pill--tan').trigger('click')
    await flushPromises()

    expect(store.user.value).toBeNull()
    expect(router.currentRoute.value.name).toBe('logout-proposer')
  })

  it('企業・自治体(company)がログアウトすると、logout-companyへ遷移する', async () => {
    const { store, router, wrapper } = await setup('company')

    await wrapper.find('button.mt-pill--tan').trigger('click')
    await flushPromises()

    expect(store.user.value).toBeNull()
    expect(router.currentRoute.value.name).toBe('logout-company')
  })
})
