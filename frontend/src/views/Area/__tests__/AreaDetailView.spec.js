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
vi.mock('../../../api/area', () => ({
  fetchArea: vi.fn(),
  updateArea: vi.fn(),
  fetchAreaChallenges: vi.fn(),
  createAreaChallenge: vi.fn(),
  updateAreaChallenge: vi.fn(),
  deleteAreaChallenge: vi.fn(),
}))
vi.mock('../../../api/idea', () => ({
  fetchIdeas: vi.fn(),
  fetchIdea: vi.fn(),
  createIdea: vi.fn(),
  evaluateIdea: vi.fn(),
}))

const AREA = {
  id: 1,
  user_id: 20,
  name: '東京都渋谷区',
  address: null,
  population: null,
  day_night_population_ratio: null,
  average_age: null,
  main_industry: null,
  transit_access: null,
  other: null,
  ideas_count: 0,
}

// ログイン中のユーザー(id: 10)が登録した地域（オーナー向けテスト用）
const OWNED_AREA = { ...AREA, user_id: 10 }

// オーナー(id: 10)自身の投稿1件が既に掲示板にある状態
const OWN_CHALLENGE_ENTRY = {
  id: 500,
  area_id: 1,
  user_id: 10,
  user_name: '渋谷区役所',
  challenges: '若者世代の地域行事への参加率が低下している',
  expected_future: '若者世代が日常的に地域活動へ参加する仕組みができている',
}

async function setup(role, { area = AREA, challenges = [] } = {}) {
  vi.resetModules()
  const authApi = await import('../../../api/auth')
  const areaApi = await import('../../../api/area')
  const { useAuthStore } = await import('../../../store/auth')
  const { default: AreaDetailView } = await import('../AreaDetailView.vue')

  areaApi.fetchArea.mockResolvedValue(area)
  areaApi.fetchAreaChallenges.mockResolvedValue(challenges)
  authApi.login.mockResolvedValue({
    data: { token: 'fake-jwt', user: { id: 10, name: '渋谷区役所', role } },
  })
  const store = useAuthStore()
  await store.login({ email: 'test@example.com', password: 'password123' })

  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'home', component: { template: '<div />' } },
      { path: '/areas', name: 'area-list', component: { template: '<div />' } },
      { path: '/areas/:id', name: 'area-detail', component: AreaDetailView, props: true },
    ],
  })
  router.push('/areas/1')
  await router.isReady()

  const wrapper = mount({ template: '<router-view />' }, { global: { plugins: [router] } })
  await flushPromises()
  return { wrapper, router }
}

beforeEach(() => {
  localStorage.clear()
  vi.clearAllMocks()
})

describe('AreaDetailView（非オーナー・発案者）', () => {
  it('地域一覧に戻るリンクはhomeを指す（発案者にはarea-listが無いため）', async () => {
    const { wrapper } = await setup('user')
    expect(wrapper.get('.mt-back-link').attributes('href')).toBe('/')
  })

  it('初期表示はアイデア登録の案内のみで、フォームは閉じている', async () => {
    const { wrapper } = await setup('user')
    expect(wrapper.text()).toContain('この地域でアイデアを登録する')
    expect(wrapper.find('#idea-title').exists()).toBe(false)
  })

  it('「アイデア登録はこちらから」を押すとフォームが開き、送信すると完了表示に切り替わる', async () => {
    const { wrapper } = await setup('user')
    const ideaApi = await import('../../../api/idea')
    ideaApi.createIdea.mockResolvedValue({ id: 100, title: 'タイトル', content: '内容' })

    await wrapper.find('.area-detail-view__cta-btn').trigger('click')
    expect(wrapper.find('#idea-title').exists()).toBe(true)

    await wrapper.find('#idea-title').setValue('空き店舗を使った学生向けシェア工房')
    await wrapper.find('#idea-content').setValue('学生が主体となって空き店舗を活用するイベントを開く')
    await wrapper.find('.idea-form').trigger('submit')
    await flushPromises()

    expect(ideaApi.createIdea).toHaveBeenCalledWith(1, {
      title: '空き店舗を使った学生向けシェア工房',
      content: '学生が主体となって空き店舗を活用するイベントを開く',
    })
    expect(wrapper.text()).toContain('アイデアを登録しました')
    expect(wrapper.find('#idea-title').exists()).toBe(false)
  })
})

describe('AreaDetailView（企業・自治体、他社の地域を閲覧）', () => {
  it('地域一覧に戻るリンクはarea-listを指す', async () => {
    const { wrapper } = await setup('company')
    expect(wrapper.get('.mt-back-link').attributes('href')).toBe('/areas')
  })

  it('アイデア登録の導線は表示しない（企業・自治体はアイデアを登録できない）', async () => {
    const { wrapper } = await setup('company')
    expect(wrapper.text()).not.toContain('アイデア登録はこちらから')
  })
})

describe('AreaDetailView（オーナー本人）', () => {
  it('初期表示は読み取り専用の確認画面（編集フォームは表示しない）', async () => {
    const { wrapper } = await setup('company', {
      area: OWNED_AREA,
      challenges: [OWN_CHALLENGE_ENTRY],
    })
    expect(wrapper.text()).toContain('この地域の情報を編集しますか？')
    expect(wrapper.find('#edit-population').exists()).toBe(false)
    expect(wrapper.text()).toContain('渋谷区役所（あなたの団体）')
  })

  it('「編集する」を押すと編集画面に切り替わり、保存すると確認画面に戻る', async () => {
    const { wrapper } = await setup('company', { area: OWNED_AREA })
    const areaApi = await import('../../../api/area')
    areaApi.updateArea.mockResolvedValue({ ...OWNED_AREA, population: '約22.6万人' })

    await wrapper.find('.area-detail-view__cta-btn').trigger('click')
    expect(wrapper.text()).toContain('編集モード')
    expect(wrapper.find('#edit-population').exists()).toBe(true)

    await wrapper.find('#edit-population').setValue('約22.6万人')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(areaApi.updateArea).toHaveBeenCalledWith(
      '1',
      expect.objectContaining({ population: '約22.6万人' }),
    )
    // 保存後は編集モードを抜けて確認画面に戻る（編集フォームが消える）
    expect(wrapper.find('#edit-population').exists()).toBe(false)
    expect(wrapper.text()).toContain('約22.6万人')
  })

  it('掲示板に自団体の投稿を新規追加できる', async () => {
    const { wrapper } = await setup('company', { area: OWNED_AREA, challenges: [] })
    const areaApi = await import('../../../api/area')
    areaApi.createAreaChallenge.mockResolvedValue({
      id: 501,
      area_id: 1,
      user_id: 10,
      user_name: '渋谷区役所',
      challenges: '空き地が目立つ',
      expected_future: '賑わいを取り戻したい',
    })

    expect(wrapper.text()).toContain('まだ投稿がありません')

    await wrapper.find('.area-detail-view__add-button').trigger('click')
    await wrapper.find('#new-entry-challenges').setValue('空き地が目立つ')
    await wrapper.find('#new-entry-expected-future').setValue('賑わいを取り戻したい')
    await wrapper.find('.area-detail-view__cta-btn--sm').trigger('click')
    await flushPromises()

    expect(areaApi.createAreaChallenge).toHaveBeenCalledWith('1', {
      challenges: '空き地が目立つ',
      expected_future: '賑わいを取り戻したい',
    })
    expect(wrapper.text()).toContain('賑わいを取り戻したい')
    expect(wrapper.text()).not.toContain('まだ投稿がありません')
  })

  it('他団体の投稿には編集・削除ボタンを表示しない', async () => {
    const otherEntry = {
      id: 600,
      area_id: 1,
      user_id: 999,
      user_name: '民間デベロッパー',
      challenges: '遊休スペースが点在している',
      expected_future: '賑わいが生まれている',
    }
    const { wrapper } = await setup('company', { area: OWNED_AREA, challenges: [otherEntry] })

    expect(wrapper.text()).toContain('民間デベロッパー')
    expect(wrapper.text()).not.toContain('（あなたの団体）')
    expect(wrapper.find('.area-detail-view__icon-button').exists()).toBe(false)
  })
})
