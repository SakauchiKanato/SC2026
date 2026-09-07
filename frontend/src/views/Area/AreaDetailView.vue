<!--
  地域の特色詳細ページ。
  発案者（＝その地域の登録者ではないユーザー）が見る場合は閲覧のみ＋
  「アイデア登録はこちらから→」導線（PDF5/7枚目）。
  地域の登録者本人（企業・自治体側であることが多い想定）が見る場合は、
  「課題点・問題点」「期待する未来」を編集できる（PDF8枚目）。
  ※「ライフスタイルデータ」はまだ実データの持ち先が無いため、準備中のプレースホルダー表示。
-->
<template>
  <section class="mt-page area-detail-view">
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>

    <template v-else-if="area">
      <h1 class="mt-page__title">{{ area.name }}の特色</h1>

      <p class="mt-section-label">ライフスタイルデータ</p>
      <div class="mt-box mt-box--placeholder">データは準備中です</div>

      <p class="mt-section-label">企業・自治体が実現したいこと</p>

      <form v-if="isOwner" @submit.prevent="handleSave">
        <div class="mt-box-row">
          <div class="mt-box">
            <textarea v-model="editForm.challenges" placeholder="課題点・問題点"></textarea>
          </div>
          <div class="mt-box">
            <textarea v-model="editForm.expectedFuture" placeholder="期待する未来"></textarea>
          </div>
        </div>

        <p v-if="saveError" class="idea-banner idea-banner--error">{{ saveError }}</p>

        <div class="area-detail-view__actions">
          <button type="submit" class="mt-pill mt-pill--tan" :disabled="isSaving">
            {{ isSaving ? '保存中...' : '変更→' }}
          </button>
        </div>
      </form>

      <template v-else>
        <div class="mt-box-row">
          <div class="mt-box">{{ area.challenges || '（未記入）' }}</div>
          <div class="mt-box">{{ area.expected_future || '（未記入）' }}</div>
        </div>

        <div class="area-detail-view__actions">
          <RouterLink :to="{ name: 'idea-new', params: { areaId: area.id } }" class="mt-pill mt-pill--tan">
            アイデア登録はこちらから→
          </RouterLink>
        </div>
      </template>
    </template>
  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { fetchArea, updateArea } from '@/api/area'
import { useAuthStore } from '../../store/auth'

const props = defineProps({
  id: {
    type: [String, Number],
    required: true,
  },
})

const { user, isAuthenticated } = useAuthStore()

const area = ref(null)
const isLoading = ref(true)
const loadError = ref('')

const isSaving = ref(false)
const saveError = ref('')

const editForm = reactive({
  challenges: '',
  expectedFuture: '',
})

// PostgreSQL(PDO)からのuser_idは文字列で返るため、数値化して比較する
const isOwner = computed(
  () =>
    isAuthenticated.value &&
    area.value &&
    Number(user.value?.id) === Number(area.value.user_id)
)

async function loadArea() {
  isLoading.value = true
  loadError.value = ''
  try {
    area.value = await fetchArea(props.id)
    editForm.challenges = area.value.challenges ?? ''
    editForm.expectedFuture = area.value.expected_future ?? ''
  } catch (error) {
    loadError.value = '地域データの取得に失敗しました'
  } finally {
    isLoading.value = false
  }
}

async function handleSave() {
  isSaving.value = true
  saveError.value = ''

  const payload = {
    name: area.value.name,
    address: area.value.address,
    challenges: editForm.challenges,
    expected_future: editForm.expectedFuture,
    tags: (area.value.tags || []).map((tag) => tag.name),
  }

  try {
    area.value = await updateArea(props.id, payload)
  } catch (error) {
    if (error.status === 403) {
      saveError.value = 'この地域を編集する権限がありません'
    } else if (error.status === 422 && error.errors) {
      saveError.value = Object.values(error.errors).join(' / ')
    } else {
      saveError.value = '保存に失敗しました'
    }
  } finally {
    isSaving.value = false
  }
}

onMounted(loadArea)
</script>

<style scoped>
.area-detail-view__actions {
  margin-top: var(--idea-spacing-md);
  display: flex;
  justify-content: flex-end;
}
</style>
