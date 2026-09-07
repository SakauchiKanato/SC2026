<script setup>
import { onMounted, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { fetchAreas } from '../../api/area'

const MAX_TITLE_LENGTH = 60
const MAX_CONTENT_LENGTH = 1000
const MAX_REASON_LENGTH = 1000

const emit = defineEmits(['submit'])

const form = reactive({
  title: '',
  // NOTE(area_id): 自由入力の地域名だと、存在しない地域でも登録できてしまうため、
  //       登録済みAreaの一覧から選ぶ方式にした（area_idをバックエンドへ送る）。
  areaId: '',
  status: 'success',
  content: '',
  reason: '',
})

const errors = reactive({
  title: '',
  areaId: '',
  content: '',
  reason: '',
})

const areas = ref([])
const isLoadingAreas = ref(true)
const areasLoadError = ref('')

async function loadAreas() {
  isLoadingAreas.value = true
  areasLoadError.value = ''
  try {
    areas.value = await fetchAreas()
  } catch {
    areasLoadError.value = '地域一覧の取得に失敗しました。時間をおいて再度お試しください。'
  } finally {
    isLoadingAreas.value = false
  }
}

onMounted(loadAreas)

function validate() {
  errors.title = form.title.trim()
    ? form.title.length > MAX_TITLE_LENGTH
      ? `タイトルは${MAX_TITLE_LENGTH}文字以内で入力してください`
      : ''
    : 'タイトルを入力してください'

  errors.areaId = form.areaId ? '' : '地域を選択してください（先に地域の登録が必要です）'

  errors.content = form.content.trim()
    ? form.content.length > MAX_CONTENT_LENGTH
      ? `内容は${MAX_CONTENT_LENGTH}文字以内で入力してください`
      : ''
    : 'アイデアの内容を入力してください'

  errors.reason = form.reason.trim()
    ? form.reason.length > MAX_REASON_LENGTH
      ? `理由は${MAX_REASON_LENGTH}文字以内で入力してください`
      : ''
    : '理由を入力してください'

  return !errors.title && !errors.areaId && !errors.content && !errors.reason
}

function handleSubmit() {
  if (!validate()) return
  emit('submit', { ...form })
}

function resetForm() {
  form.title = ''
  form.areaId = ''
  form.status = 'success'
  form.content = ''
  form.reason = ''
  errors.title = ''
  errors.areaId = ''
  errors.content = ''
  errors.reason = ''
}

defineExpose({ resetForm })
</script>

<template>
  <form class="idea-form" novalidate @submit.prevent="handleSubmit">
    <div class="idea-form__field">
      <label for="idea-title">タイトル</label>
      <input
        id="idea-title"
        v-model="form.title"
        type="text"
        :maxlength="MAX_TITLE_LENGTH"
        placeholder="例）廃校を活用した週末マルシェ"
        :aria-invalid="Boolean(errors.title)"
      />
      <p v-if="errors.title" class="idea-form__error" role="alert">{{ errors.title }}</p>
    </div>

    <div class="idea-form__field">
      <label for="idea-area">地域</label>
      <select
        id="idea-area"
        v-model="form.areaId"
        :disabled="isLoadingAreas"
        :aria-invalid="Boolean(errors.areaId)"
      >
        <option value="" disabled>地域を選択してください</option>
        <option v-for="area in areas" :key="area.id" :value="area.id">
          {{ area.name }}
        </option>
      </select>
      <p v-if="isLoadingAreas" class="idea-form__hint">地域一覧を読み込み中です…</p>
      <p v-else-if="areasLoadError" class="idea-form__error" role="alert">{{ areasLoadError }}</p>
      <p v-else-if="areas.length === 0" class="idea-form__hint">
        登録されている地域がありません。先に
        <RouterLink to="/areas/new">地域の登録</RouterLink>
        が必要です。
      </p>
      <p v-if="errors.areaId" class="idea-form__error" role="alert">{{ errors.areaId }}</p>
    </div>

    <div class="idea-form__field">
      <span class="idea-form__label">結果</span>
      <div class="idea-form__status-options">
        <label class="idea-form__status-option">
          <input v-model="form.status" type="radio" name="idea-status" value="success" />
          成功
        </label>
        <label class="idea-form__status-option">
          <input v-model="form.status" type="radio" name="idea-status" value="failure" />
          失敗
        </label>
      </div>
    </div>

    <div class="idea-form__field">
      <label for="idea-content">アイデアの内容</label>
      <textarea
        id="idea-content"
        v-model="form.content"
        rows="5"
        :maxlength="MAX_CONTENT_LENGTH"
        placeholder="どんな取り組みだったかを具体的に書いてください"
        :aria-invalid="Boolean(errors.content)"
      ></textarea>
      <p class="idea-form__char-count">{{ form.content.length }} / {{ MAX_CONTENT_LENGTH }}</p>
      <p v-if="errors.content" class="idea-form__error" role="alert">{{ errors.content }}</p>
    </div>

    <div class="idea-form__field">
      <label for="idea-reason">{{ form.status === 'success' ? '成功した理由' : '失敗した理由' }}</label>
      <textarea
        id="idea-reason"
        v-model="form.reason"
        rows="5"
        :maxlength="MAX_REASON_LENGTH"
        placeholder="うまくいった／いかなかった要因を書いてください"
        :aria-invalid="Boolean(errors.reason)"
      ></textarea>
      <p class="idea-form__char-count">{{ form.reason.length }} / {{ MAX_REASON_LENGTH }}</p>
      <p v-if="errors.reason" class="idea-form__error" role="alert">{{ errors.reason }}</p>
    </div>

    <button type="submit" class="idea-button">アイデアを登録する</button>
  </form>
</template>

<style scoped>
.idea-form {
  display: flex;
  flex-direction: column;
  gap: var(--idea-spacing-md);
  max-width: 560px;
}

.idea-form__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.idea-form__field label,
.idea-form__label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--idea-color-text);
}

.idea-form__field input[type='text'],
.idea-form__field textarea,
.idea-form__field select {
  border: 1px solid var(--idea-color-border);
  border-radius: var(--idea-radius);
  padding: 10px 12px;
  font-size: 0.95rem;
  font-family: inherit;
  color: var(--idea-color-text);
  background: var(--idea-color-surface);
}

.idea-form__field input[type='text']:focus,
.idea-form__field textarea:focus,
.idea-form__field select:focus {
  outline: 2px solid var(--idea-color-accent);
  outline-offset: 1px;
}

.idea-form__field input[aria-invalid='true'],
.idea-form__field textarea[aria-invalid='true'],
.idea-form__field select[aria-invalid='true'] {
  border-color: var(--idea-color-danger);
}

.idea-form__status-options {
  display: flex;
  gap: var(--idea-spacing-md);
}

.idea-form__status-option {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 400;
}

.idea-form__char-count {
  align-self: flex-end;
  margin: 0;
  font-size: 0.75rem;
  color: var(--idea-color-text-muted);
}

.idea-form__error {
  margin: 0;
  font-size: 0.8rem;
  color: var(--idea-color-danger);
}

.idea-form__hint {
  margin: 0;
  font-size: 0.8rem;
  color: var(--idea-color-text-muted);
}
</style>
