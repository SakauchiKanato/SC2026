import { reactive, toRefs } from 'vue'
import {
  fetchIdeas as fetchIdeasApi,
  fetchIdea as fetchIdeaApi,
  createIdea as createIdeaApi,
} from '../api/idea'

/**
 * アイデア機能の状態管理。
 *
 * NOTE: Piniaの導入有無がプロジェクトとして未確定のため、
 *       ライブラリを勝手に追加せずComposition APIのreactiveのみで実装している。
 *       Piniaを導入済み・導入する場合は、このstate/actionsの中身を
 *       defineStore()の中に移し替えるだけで置き換えられる構成にしてある。
 */
const state = reactive({
  ideas: [],
  currentIdea: null,
  isLoading: false,
  errorMessage: '',
})

export function useIdeaStore() {
  async function loadIdeas(filters = {}) {
    state.isLoading = true
    state.errorMessage = ''
    try {
      state.ideas = await fetchIdeasApi(filters)
    } catch (error) {
      state.errorMessage = error.message
    } finally {
      state.isLoading = false
    }
  }

  async function loadIdea(ideaId) {
    state.isLoading = true
    state.errorMessage = ''
    try {
      state.currentIdea = await fetchIdeaApi(ideaId)
    } catch (error) {
      state.errorMessage = error.message
    } finally {
      state.isLoading = false
    }
  }

  async function registerIdea(idea) {
    state.errorMessage = ''
    const created = await createIdeaApi(idea)
    state.ideas = [created, ...state.ideas]
    return created
  }

  return {
    ...toRefs(state),
    loadIdeas,
    loadIdea,
    registerIdea,
  }
}