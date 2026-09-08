import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import AreaCard from '../AreaCard.vue'

async function mountCard(props) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'home', component: { template: '<div />' } },
      { path: '/areas/:id', name: 'area-detail', component: { template: '<div />' } },
      { path: '/areas/:areaId/ideas/recent', name: 'idea-recent', component: { template: '<div />' } },
      { path: '/areas/:areaId/ideas/past', name: 'idea-past', component: { template: '<div />' } },
    ],
  })
  router.push('/')
  await router.isReady()
  return mount(AreaCard, { props, global: { plugins: [router] } })
}

describe('AreaCard', () => {
  it('mode="home": 名前・募集件数バッジ・詳細リンクを表示する', async () => {
    const wrapper = await mountCard({
      mode: 'home',
      area: { id: 1, name: '東京都渋谷区', ideas_count: 3 },
    })

    expect(wrapper.text()).toContain('東京都渋谷区')
    expect(wrapper.text()).toContain('募集 3件')
    expect(wrapper.text()).toContain('詳細をみる')
    // mode="home"では新着/過去アイデアへのリンクは表示しない
    expect(wrapper.text()).not.toContain('新着アイデア')
  })

  it('mode="browse": 「新着アイデア」「過去のアイデア」リンクを表示する', async () => {
    const wrapper = await mountCard({
      mode: 'browse',
      area: { id: 1, name: '東京都渋谷区', ideas_count: 2 },
    })

    expect(wrapper.text()).toContain('新着アイデア')
    expect(wrapper.text()).toContain('過去のアイデア')
    expect(wrapper.text()).toContain('募集2件')
  })
})
