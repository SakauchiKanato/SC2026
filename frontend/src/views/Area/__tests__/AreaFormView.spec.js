import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'

vi.mock('../../../api/area', () => ({
  createArea: vi.fn(),
}))

async function setup() {
  vi.resetModules()
  const areaApi = await import('../../../api/area')
  const { default: AreaFormView } = await import('../AreaFormView.vue')

  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/areas', name: 'area-list', component: { template: '<div />' } },
      { path: '/areas/new', name: 'area-new', component: AreaFormView },
      { path: '/areas/:id', name: 'area-detail', component: { template: '<div />' }, props: true },
    ],
  })
  router.push('/areas/new')
  await router.isReady()

  const wrapper = mount({ template: '<router-view />' }, { global: { plugins: [router] } })
  return { wrapper, router, areaApi }
}

beforeEach(() => {
  vi.clearAllMocks()
})

afterEach(() => {
  vi.useRealTimers()
})

describe('AreaFormView', () => {
  it('地域名（必須）が未入力だと送信ボタンは非活性。入力すると活性化する（他は任意項目）', async () => {
    const { wrapper } = await setup()
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()

    await wrapper.find('#name').setValue('東京都渋谷区')
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeUndefined()
  })

  it('課題点・問題点の文字数がリアルタイムで表示される', async () => {
    const { wrapper } = await setup()
    await wrapper.find('#challenges').setValue('あいうえお')
    expect(wrapper.text()).toContain('5 文字')
  })

  it('登録成功時: 完了表示を挟んでから登録した地域の詳細ページへ遷移する', async () => {
    vi.useFakeTimers()
    const { wrapper, router, areaApi } = await setup()
    areaApi.createArea.mockResolvedValue({ id: 42, name: '東京都渋谷区' })

    await wrapper.find('#name').setValue('東京都渋谷区')
    await wrapper.find('#challenges').setValue('空き店舗が増えている')
    await wrapper.find('#expected_future').setValue('商店街に活気が戻る')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('地域登録が完了しました')
    expect(wrapper.text()).toContain('「東京都渋谷区」の情報を登録しました')
    expect(router.currentRoute.value.name).toBe('area-new')

    await vi.advanceTimersByTimeAsync(1200)

    expect(router.currentRoute.value.name).toBe('area-detail')
    expect(router.currentRoute.value.params.id).toBe('42')
  })

  it('登録失敗時（422）: フィールドごとのエラーを表示し、完了表示にはしない', async () => {
    const { wrapper, areaApi } = await setup()
    const error = new Error('validation failed')
    error.status = 422
    error.errors = { name: '地域名は既に使用されています' }
    areaApi.createArea.mockRejectedValue(error)

    await wrapper.find('#name').setValue('東京都渋谷区')
    await wrapper.find('#challenges').setValue('空き店舗が増えている')
    await wrapper.find('#expected_future').setValue('商店街に活気が戻る')
    await wrapper.find('form').trigger('submit')
    await flushPromises()

    expect(wrapper.text()).toContain('地域名は既に使用されています')
    expect(wrapper.text()).not.toContain('地域登録が完了しました')
  })
})
