<!-- アイデア詳細ページ（新着/過去のアイデア一覧の「詳細をみる」リンク先） -->
<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { fetchIdea } from '@/api/idea'
import { useIdeaStore } from '../../store/idea'
import { useAuthStore } from '../../store/auth'

const MAX_REASON_LENGTH = 1000

const props = defineProps({
  id: {
    type: [String, Number],
    required: true,
  },
})

const idea = ref(null)
const isLoading = ref(true)
const loadError = ref('')

const { evaluateIdea } = useIdeaStore()
const { user } = useAuthStore()

// このアイデアが紐づく地域を登録した企業・自治体アカウント本人だけが評価できる
// （AuthMiddleware::requireAuth()のroleと同じ判定をバックエンド側でも行っている。
//  フロント側の出し分けは利便性のためで、実際の認可はサーバー側で担保する）。
const canEvaluate = computed(() => {
  if (!user.value || !idea.value) return false
  return user.value.role === 'company' && Number(user.value.id) === Number(idea.value.area_owner_id)
})

const isEvaluated = computed(() => idea.value?.status === 'success' || idea.value?.status === 'failure')

const evaluationForm = reactive({
  status: 'success',
  reason: '',
})
const evaluationError = ref('')
const isSubmittingEvaluation = ref(false)

function formatDate(dateString) {
  if (!dateString) return ''
  return new Intl.DateTimeFormat('ja-JP', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(dateString))
}

async function loadIdea() {
  isLoading.value = true
  loadError.value = ''
  try {
    idea.value = await fetchIdea(props.id)
    evaluationForm.status = idea.value.status ?? 'success'
    evaluationForm.reason = idea.value.reason ?? ''
  } catch (error) {
    loadError.value = 'アイデアの取得に失敗しました'
  } finally {
    isLoading.value = false
  }
}

async function handleEvaluationSubmit() {
  evaluationError.value = ''

  if (!evaluationForm.reason.trim()) {
    evaluationError.value = '評価理由を入力してください'
    return
  }
  if (evaluationForm.reason.length > MAX_REASON_LENGTH) {
    evaluationError.value = `評価理由は${MAX_REASON_LENGTH}文字以内で入力してください`
    return
  }

  isSubmittingEvaluation.value = true
  try {
    idea.value = await evaluateIdea(props.id, {
      status: evaluationForm.status,
      reason: evaluationForm.reason.trim(),
    })
  } catch (error) {
    evaluationError.value = error.message || '評価の保存に失敗しました'
  } finally {
    isSubmittingEvaluation.value = false
  }
}

onMounted(loadIdea)
</script>

<template>
  <section class="mt-page idea-detail-view">
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>

    <template v-else-if="idea">
      <RouterLink :to="{ name: 'area-detail', params: { id: idea.area_id } }" class="idea-detail-view__back">
        ← {{ idea.area_name }}に戻る
      </RouterLink>

      <div class="mt-idea-card__head idea-detail-view__head">
        <h1>{{ idea.title }}</h1>
        <span
          v-if="isEvaluated"
          class="mt-status-pill"
          :class="idea.status === 'success' ? 'mt-status-pill--achieved' : 'mt-status-pill--not-achieved'"
        >
          {{ idea.status === 'success' ? '達成' : '未達成' }}
        </span>
        <span v-else class="mt-status-pill mt-status-pill--pending">評価待ち</span>
      </div>

      <p class="idea-detail-view__date">{{ formatDate(idea.created_at) }}</p>

      <p class="mt-section-label">アイデアの内容</p>
      <div class="mt-box">{{ idea.content }}</div>

      <template v-if="isEvaluated">
        <p class="mt-section-label">
          {{ idea.status === 'success' ? '達成した理由' : '未達成だった理由' }}
        </p>
        <div class="mt-box">{{ idea.reason }}</div>
      </template>
      <p v-else-if="!canEvaluate" class="idea-detail-view__pending-note">
        まだ企業・自治体による評価が付いていません。
      </p>

      <div v-if="canEvaluate" class="idea-detail-view__evaluation">
        <p class="mt-section-label">{{ isEvaluated ? '評価を変更する' : 'このアイデアを評価する' }}</p>

        <form class="idea-form" novalidate @submit.prevent="handleEvaluationSubmit">
          <div class="idea-form__field">
            <span class="idea-form__label">評価</span>
            <div class="idea-form__status-options">
              <label class="idea-form__status-option">
                <input v-model="evaluationForm.status" type="radio" name="evaluation-status" value="success" />
                達成
              </label>
              <label class="idea-form__status-option">
                <input v-model="evaluationForm.status" type="radio" name="evaluation-status" value="failure" />
                未達成
              </label>
            </div>
          </div>

          <div class="idea-form__field">
            <label for="evaluation-reason">
              {{ evaluationForm.status === 'success' ? '達成した理由' : '未達成だった理由' }}
            </label>
            <textarea
              id="evaluation-reason"
              v-model="evaluationForm.reason"
              rows="5"
              :maxlength="MAX_REASON_LENGTH"
              placeholder="評価の理由を書いてください"
            ></textarea>
            <p class="idea-form__char-count">{{ evaluationForm.reason.length }} / {{ MAX_REASON_LENGTH }}</p>
          </div>

          <p v-if="evaluationError" class="idea-banner idea-banner--error" role="alert">
            {{ evaluationError }}
          </p>

          <button type="submit" class="idea-button" :disabled="isSubmittingEvaluation">
            {{ isSubmittingEvaluation ? '保存中…' : '評価を保存する' }}
          </button>
        </form>
      </div>
    </template>
  </section>
</template>

<style scoped>
.idea-detail-view__back {
  display: inline-block;
  margin-bottom: var(--idea-spacing-md);
  color: var(--idea-color-text-muted);
}

.idea-detail-view__head {
  align-items: center;
}

.idea-detail-view__date {
  color: var(--idea-color-text-muted);
  font-size: 0.85rem;
  margin-top: -8px;
}

.idea-detail-view__pending-note {
  color: var(--idea-color-text-muted);
  font-size: 0.9rem;
}

.mt-status-pill--pending {
  background: var(--idea-color-border);
  color: var(--idea-color-text-muted);
}

.idea-detail-view__evaluation {
  margin-top: var(--idea-spacing-lg);
  padding-top: var(--idea-spacing-lg);
  border-top: 1px solid var(--idea-color-border);
}
</style>
