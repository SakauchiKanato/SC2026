import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import SignupForm from '../SignupForm.vue'

describe('SignupForm', () => {
  it('空のまま送信すると必須項目のエラーが表示され、submitは発火しない', async () => {
    const wrapper = mount(SignupForm)
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.text()).toContain('お名前を入力してください')
    expect(wrapper.text()).toContain('メールアドレスを入力してください')
    expect(wrapper.text()).toContain('パスワードを入力してください')
    expect(wrapper.text()).toContain('確認用のパスワードを入力してください')
    expect(wrapper.emitted('submit')).toBeUndefined()
  })

  it('メールアドレスの形式が不正だとエラーになる', async () => {
    const wrapper = mount(SignupForm)
    await wrapper.find('#signup-name').setValue('山田太郎')
    await wrapper.find('#signup-email').setValue('not-an-email')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('password123')
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.text()).toContain('メールアドレスの形式が正しくありません')
    expect(wrapper.emitted('submit')).toBeUndefined()
  })

  it('パスワードが8文字未満だとエラーになる', async () => {
    const wrapper = mount(SignupForm)
    await wrapper.find('#signup-name').setValue('山田太郎')
    await wrapper.find('#signup-email').setValue('taro@example.com')
    await wrapper.find('#signup-password').setValue('short')
    await wrapper.find('#signup-password-confirmation').setValue('short')
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.text()).toContain('8文字以上')
    expect(wrapper.emitted('submit')).toBeUndefined()
  })

  it('確認用パスワードが一致しないとエラーになる', async () => {
    const wrapper = mount(SignupForm)
    await wrapper.find('#signup-name').setValue('山田太郎')
    await wrapper.find('#signup-email').setValue('taro@example.com')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('different-password')
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.text()).toContain('パスワードが一致しません')
    expect(wrapper.emitted('submit')).toBeUndefined()
  })

  it('全項目が正しい場合、前後の空白を除いたpayloadでsubmitを発火する（role未選択時はデフォルトでuser）', async () => {
    const wrapper = mount(SignupForm)
    await wrapper.find('#signup-name').setValue('  山田太郎  ')
    await wrapper.find('#signup-email').setValue('  taro@example.com  ')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('password123')
    await wrapper.find('form').trigger('submit.prevent')

    const submitted = wrapper.emitted('submit')
    expect(submitted).toHaveLength(1)
    expect(submitted[0][0]).toEqual({
      name: '山田太郎',
      email: 'taro@example.com',
      password: 'password123',
      role: 'user',
    })
  })

  it('「企業として登録する」を選ぶとroleが company でsubmitされる', async () => {
    const wrapper = mount(SignupForm)
    await wrapper.find('#signup-name').setValue('株式会社サンプル')
    await wrapper.find('#signup-email').setValue('company@example.com')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('password123')
    await wrapper.find('input[type="radio"][value="company"]').setValue()
    await wrapper.find('form').trigger('submit.prevent')

    const submitted = wrapper.emitted('submit')
    expect(submitted).toHaveLength(1)
    expect(submitted[0][0].role).toBe('company')
  })
})
