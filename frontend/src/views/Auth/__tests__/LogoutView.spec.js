import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import LogoutView from '../LogoutView.vue'

async function mountAt(path) {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: '/', name: 'home', component: { template: '<div />' } },
      { path: '/login/proposer', name: 'login-proposer', component: { template: '<div />' } },
      { path: '/login/company', name: 'login-company', component: { template: '<div />' } },
      { path: '/logout/proposer', name: 'logout-proposer', component: LogoutView },
      { path: '/logout/company', name: 'logout-company', component: LogoutView },
    ],
  })
  router.push(path)
  await router.isReady()
  return mount({ template: '<router-view />' }, { global: { plugins: [router] } })
}

describe('LogoutView', () => {
  it('/logout/proposer: 発案者としてログインへの導線を表示する', async () => {
    const wrapper = await mountAt('/logout/proposer')

    expect(wrapper.text()).toContain('ログアウトしました')
    expect(wrapper.text()).toContain('発案者としてログイン')
    expect(wrapper.get('.logout-view__primary').attributes('href')).toBe('/login/proposer')
  })

  it('/logout/company: 企業・自治体としてログインへの導線を表示する', async () => {
    const wrapper = await mountAt('/logout/company')

    expect(wrapper.text()).toContain('企業・自治体としてログイン')
    expect(wrapper.get('.logout-view__primary').attributes('href')).toBe('/login/company')
  })
})
