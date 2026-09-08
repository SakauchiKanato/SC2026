import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'

vi.mock('../../../api/idea', () => ({
  fetchIdeas: vi.fn(),
  fetchIdea: vi.fn(),
  createIdea: vi.fn(),
  evaluateIdea: vi.fn(),
}))
vi.mock('../../../api/area', () => ({
  fetchArea: vi.fn(),
}))

const IDEAS = [
  { id: 1, title: '公園ライトアップ', content: '...', status: null, created_at: '2026-09-01T00:00:00Z' },
  { id: 2, title: '多言語観光アプリ', content: '...', status: null, created_at: '2026-09-03T00:00:00Z' },
  { id: 3, title: '空き店舗リノベ支援', content: '...', status: 'success', created_at: '2026-08-01T00:00:00Z' },
  { id: 4, title: '共通ポイントアプリ', content: '...', status: 'failure', created_at: '2026-07-01T00:00:00Z' },
]

async function setup(initialPath) {
  vi.resetModules()
  const ideaApi = await import('../../../api/idea')
  const areaApi = await import('../../../api/area')
  const { default: IdeaListView } = await import('../IdeaListView.vue')

  ideaApi.fetchIdeas.mockResolvedValue(IDEAS)
  areaApi.fetchArea.mockResolvedValue({ id: 1, name: '東京都渋谷区' })

  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/areas/:id', name: 'area-detail', component: { template: '<div />' } },
      { path: '/ideas/:id', name: 'idea-detail', component: { template: '<div />' } },
      {
        path: '/areas/:areaId/ideas/recent',
        name: 'idea-recent',
        component: IdeaListView,
        props: true,
        meta: { variant: 'recent' },
      },
      {
        path: '/areas/:areaId/ideas/past',
        name: 'idea-past',
        component: IdeaListView,
        props: true,
        meta: { variant: 'past' },
      },
    ],
  })
  router.push(initialPath)
  await router.isReady()

  const wrapper = mount({ template: '<router-view />' }, { global: { plugins: [router] } })
  await flushPromises()
  return { wrapper, router, ideaApi }
}

beforeEach(() => {
  vi.clearAllMocks()
})

describe('IdeaListView', () => {
  it('/ideas/recentで開くと新着タブがアクティブで、statusが未評価のアイデアのみ表示する', async () => {
    const { wrapper } = await setup('/areas/1/ideas/recent')

    expect(wrapper.get('.idea-list-view__tab--active').text()).toContain('新着アイデア')
    expect(wrapper.text()).toContain('公園ライトアップ')
    expect(wrapper.text()).toContain('多言語観光アプリ')
    expect(wrapper.text()).not.toContain('空き店舗リノベ支援')
    expect(wrapper.text()).toContain('新着アイデア （2）')
    expect(wrapper.text()).toContain('過去のアイデア （2）')

    const detailLinks = wrapper.findAll('.mt-idea-card--new a')
    expect(detailLinks).toHaveLength(2)
    expect(detailLinks[0].attributes('href')).toBe('/ideas/1')
  })

  it('/ideas/pastで開くと過去タブがアクティブで、達成/未達成バッジ付きで表示する', async () => {
    const { wrapper } = await setup('/areas/1/ideas/past')

    expect(wrapper.get('.idea-list-view__tab--active').text()).toContain('過去のアイデア')
    expect(wrapper.text()).toContain('空き店舗リノベ支援')
    expect(wrapper.text()).toContain('達成')
    expect(wrapper.text()).toContain('共通ポイントアプリ')
    expect(wrapper.text()).toContain('未達成')
    expect(wrapper.text()).not.toContain('公園ライトアップ')
  })

  it('タブをクリックすると、ルート遷移せずその場で表示が切り替わる', async () => {
    const { wrapper, router } = await setup('/areas/1/ideas/recent')

    const tabs = wrapper.findAll('.idea-list-view__tab')
    const pastTab = tabs.find((t) => t.text().includes('過去のアイデア'))
    await pastTab.trigger('click')

    expect(wrapper.text()).toContain('空き店舗リノベ支援')
    expect(router.currentRoute.value.name).toBe('idea-recent')
  })

  it('idea-recentからidea-pastへ直接遷移すると（コンポーネントが使い回されても）タブと一覧が更新される', async () => {
    const { wrapper, router } = await setup('/areas/1/ideas/recent')
    expect(wrapper.get('.idea-list-view__tab--active').text()).toContain('新着アイデア')

    await router.push('/areas/1/ideas/past')
    await flushPromises()

    expect(wrapper.get('.idea-list-view__tab--active').text()).toContain('過去のアイデア')
    expect(wrapper.text()).toContain('空き店舗リノベ支援')
    expect(wrapper.text()).toContain('達成')
  })

  it('見出しに地域名を表示し、戻るリンクは地域詳細ページを指す', async () => {
    const { wrapper } = await setup('/areas/1/ideas/recent')

    expect(wrapper.get('h1').text()).toBe('東京都渋谷区のアイデア')
    expect(wrapper.get('.mt-back-link').attributes('href')).toBe('/areas/1')
  })
})
