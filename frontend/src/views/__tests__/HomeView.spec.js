import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'

// store/auth.jsと同じ方針で、api層をモックしてから動的importし直す（モジュールレベルの
// シングルトンstateをテスト間で持ち越さないため）。
vi.mock('../../api/auth', () => ({
  signup: vi.fn(),
  login: vi.fn(),
  fetchMe: vi.fn(),
}))
vi.mock('../../api/area', () => ({
  fetchAreas: vi.fn(),
}))

const AREAS = [
  { id: 1, user_id: 10, name: '東京都渋谷区', ideas_count: 3, tags: [] },
  { id: 2, user_id: 10, name: '東京都新宿区', ideas_count: 1, tags: [] },
  { id: 3, user_id: 99, name: '東京都港区', ideas_count: 0, tags: [] },
]

async function setup(role) {
  vi.resetModules()
  const authApi = await import('../../api/auth')
  const areaApi = await import('../../api/area')
  const { useAuthStore } = await import('../../store/auth')
  const { default: HomeView } = await import('../HomeView.vue')

  areaApi.fetchAreas.mockResolvedValue(AREAS)
  authApi.login.mockResolvedValue({
    data: { token: 'fake-jwt', user: { id: 10, name: 'テストユーザー', role } },
  })
  const store = useAuthStore()
  await store.login({ email: 'test@example.com', password: 'password123' })

  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'home', component: HomeView },
      { path: '/areas/new', name: 'area-new', component: { template: '<div />' } },
      { path: '/areas/:id', name: 'area-detail', component: { template: '<div />' } },
      { path: '/areas/:areaId/ideas/recent', name: 'idea-recent', component: { template: '<div />' } },
      { path: '/areas/:areaId/ideas/past', name: 'idea-past', component: { template: '<div />' } },
    ],
  })
  router.push('/')
  await router.isReady()

  const wrapper = mount({ template: '<router-view />' }, { global: { plugins: [router] } })
  await flushPromises()
  return { wrapper, areaApi }
}

beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
})

describe('HomeView（認証後）', () => {
  it('発案者(user): 検索ボックスで地域名を部分一致フィルタする', async () => {
    const { wrapper } = await setup('user')

    expect(wrapper.text()).toContain('東京都渋谷区')
    expect(wrapper.text()).toContain('東京都新宿区')
    expect(wrapper.text()).toContain('東京都港区')

    await wrapper.find('.mt-searchbox input').setValue('渋谷')
    await flushPromises()

    expect(wrapper.text()).toContain('東京都渋谷区')
    expect(wrapper.text()).not.toContain('東京都新宿区')
    expect(wrapper.text()).not.toContain('東京都港区')
  })

  it('発案者(user): 一致する地域が無いと専用の空状態メッセージを表示する', async () => {
    const { wrapper } = await setup('user')

    await wrapper.find('.mt-searchbox input').setValue('存在しない地域名')
    await flushPromises()

    expect(wrapper.text()).toContain('「存在しない地域名」に一致する地域が見つかりませんでした。')
  })

  it('企業・自治体(company): 検索ボックスは表示せず、自団体・他団体を問わず登録されている地域を全件表示する', async () => {
    const { wrapper } = await setup('company')

    expect(wrapper.find('.mt-searchbox').exists()).toBe(false)
    expect(wrapper.text()).toContain('東京都渋谷区')
    expect(wrapper.text()).toContain('東京都新宿区')
    // user_id=99の他団体の地域も、詳細ページの「実現したいこと」掲示板へ投稿できるため表示する
    expect(wrapper.text()).toContain('東京都港区')
  })
})
