import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import SignupForm from '../SignupForm.vue'

// 利用規約同意チェックボックスを入れてから送信するヘルパー（同意していないと送信できない）
async function agreeToTerms(wrapper) {
  await wrapper.find('input[type="checkbox"]').setValue(true)
}

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
    await agreeToTerms(wrapper)
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
    await agreeToTerms(wrapper)
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
    await agreeToTerms(wrapper)
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.text()).toContain('パスワードが一致しません')
    expect(wrapper.emitted('submit')).toBeUndefined()
  })

  it('利用規約に同意していないと、他の項目が正しくてもsubmitは発火しない', async () => {
    const wrapper = mount(SignupForm)
    await wrapper.find('#signup-name').setValue('山田太郎')
    await wrapper.find('#signup-email').setValue('taro@example.com')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('password123')
    await wrapper.find('form').trigger('submit.prevent')

    expect(wrapper.emitted('submit')).toBeUndefined()
    expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('全項目が正しく同意済みの場合、前後の空白を除いたpayloadでsubmitを発火する（デフォルトはuser）', async () => {
    const wrapper = mount(SignupForm)
    await wrapper.find('#signup-name').setValue('  山田太郎  ')
    await wrapper.find('#signup-email').setValue('  taro@example.com  ')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('password123')
    await agreeToTerms(wrapper)
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

  it('defaultRole="company"のとき、roleがcompanyでsubmitされる', async () => {
    const wrapper = mount(SignupForm, { props: { defaultRole: 'company' } })
    await wrapper.find('#signup-name').setValue('山田花子')
    await wrapper.find('#signup-email').setValue('company@example.com')
    await wrapper.find('#signup-password').setValue('password123')
    await wrapper.find('#signup-password-confirmation').setValue('password123')
    await agreeToTerms(wrapper)
    await wrapper.find('form').trigger('submit.prevent')

    const submitted = wrapper.emitted('submit')
    expect(submitted).toHaveLength(1)
    expect(submitted[0][0].role).toBe('company')
  })
})
