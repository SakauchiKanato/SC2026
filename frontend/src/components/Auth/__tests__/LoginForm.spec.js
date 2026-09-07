import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import LoginForm from '../LoginForm.vue'

describe('LoginForm', () => {
  it('空のまま送信すると必須項目のエラーが表示され、submitは発火しない', async () => {
    const wrapper = mount(LoginForm)
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.text()).toContain('メールアドレスを入力してください')
    expect(wrapper.text()).toContain('パスワードを入力してください')
    expect(wrapper.emitted('submit')).toBeUndefined()
  })

  it('メールアドレスの形式チェックはしない（存在有無を推測させないため、未入力かどうかだけ見る）', async () => {
    const wrapper = mount(LoginForm)
    await wrapper.find('#login-email').setValue('not-an-email')
    await wrapper.find('#login-password').setValue('whatever-password')
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.text()).not.toContain('形式が正しくありません')
    expect(wrapper.emitted('submit')).toHaveLength(1)
  })

  it('両方入力されていれば前後の空白を除いたpayloadでsubmitを発火する', async () => {
    const wrapper = mount(LoginForm)
    await wrapper.find('#login-email').setValue('  taro@example.com  ')
    await wrapper.find('#login-password').setValue('password123')
    await wrapper.find('form').trigger('submit.prevent')

    const submitted = wrapper.emitted('submit')
    expect(submitted).toHaveLength(1)
    expect(submitted[0][0]).toEqual({
      email: 'taro@example.com',
      password: 'password123',
    })
  })
})
