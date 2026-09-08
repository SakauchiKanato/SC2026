<!--
  地域の特色詳細ページ。
  発案者（＝その地域の登録者ではないユーザー）が見る場合は閲覧のみ＋
  「アイデア登録はこちらから→」導線（PDF5/7枚目）。
  地域の登録者本人（企業・自治体側であることが多い想定）が見る場合は、
  「ライフスタイルデータ」「課題点・問題点」「期待する未来」を編集できる（PDF8/9枚目）。
-->
<template>
  <section class="mt-page area-detail-view">
    <p v-if="isLoading" class="idea-loading-indicator">読み込み中です…</p>
    <p v-else-if="loadError" class="idea-banner idea-banner--error">{{ loadError }}</p>

    <template v-else-if="area">
      <h1 class="mt-page__title">{{ area.name }}の特色</h1>

      <p class="mt-section-label">ライフスタイルデータ</p>

      <form v-if="isOwner" @submit.prevent="handleSave">
        <div class="mt-form-field">
          <label for="edit-address">住所</label>
          <input
            id="edit-address"
            v-model="editForm.address"
            type="text"
            maxlength="255"
            placeholder="例：東京都渋谷区宇田川町1-1"
          />
        </div>

        <div class="mt-form-row">
          <div class="mt-form-field">
            <label for="edit-population">人口</label>
            <input
              id="edit-population"
              v-model="editForm.population"
              type="text"
              maxlength="255"
              placeholder="例：約22.6万人"
            />
          </div>
          <div class="mt-form-field">
            <label for="edit-day-night-population-ratio">昼夜人口比率</label>
            <input
              id="edit-day-night-population-ratio"
              v-model="editForm.dayNightPopulationRatio"
              type="text"
              maxlength="255"
              placeholder="例：約230%"
            />
          </div>
        </div>

        <div class="mt-form-row">
          <div class="mt-form-field">
            <label for="edit-average-age">平均年齢</label>
            <input
              id="edit-average-age"
              v-model="editForm.averageAge"
              type="text"
              maxlength="255"
              placeholder="例：38.4歳"
            />
          </div>
          <div class="mt-form-field">
            <label for="edit-main-industry">主要産業</label>
            <input
              id="edit-main-industry"
              v-model="editForm.mainIndustry"
              type="text"
              maxlength="255"
              placeholder="例：商業・サービス業 / IT"
            />
          </div>
        </div>

        <div class="mt-form-field">
          <label for="edit-transit-access">交通アクセス</label>
          <input
            id="edit-transit-access"
            v-model="editForm.transitAccess"
            type="text"
            maxlength="255"
            placeholder="例：JR山手線・私鉄5路線が乗り入れる広域ターミナル"
          />
        </div>

        <p class="mt-section-label">企業・自治体が実現したいこと</p>
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
        <div class="mt-box-row area-detail-view__lifestyle-grid">
          <div class="mt-box">
            <p class="area-detail-view__lifestyle-label">住所</p>
            <p>{{ area.address || '（未記入）' }}</p>
          </div>
          <div class="mt-box">
            <p class="area-detail-view__lifestyle-label">人口</p>
            <p>{{ area.population || '（未記入）' }}</p>
          </div>
          <div class="mt-box">
            <p class="area-detail-view__lifestyle-label">昼夜人口比率</p>
            <p>{{ area.day_night_population_ratio || '（未記入）' }}</p>
          </div>
          <div class="mt-box">
            <p class="area-detail-view__lifestyle-label">平均年齢</p>
            <p>{{ area.average_age || '（未記入）' }}</p>
          </div>
          <div class="mt-box">
            <p class="area-detail-view__lifestyle-label">主要産業</p>
            <p>{{ area.main_industry || '（未記入）' }}</p>
          </div>
          <div class="mt-box">
            <p class="area-detail-view__lifestyle-label">交通アクセス</p>
            <p>{{ area.transit_access || '（未記入）' }}</p>
          </div>
        </div>

        <p class="mt-section-label">企業・自治体が実現したいこと</p>
        <div class="mt-box-row">
          <div class="mt-box">{{ area.challenges || '（未記入）' }}</div>
          <div class="mt-box">{{ area.expected_future || '（未記入）' }}</div>
        </div>

        <!-- アイデア登録は発案者（role=user）専用。企業・自治体が他社の地域を見ている場合は表示しない -->
        <div v-if="user?.role === 'user'" class="area-detail-view__actions">
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
  address: '',
  population: '',
  dayNightPopulationRatio: '',
  averageAge: '',
  mainIndustry: '',
  transitAccess: '',
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
    editForm.address = area.value.address ?? ''
    editForm.population = area.value.population ?? ''
    editForm.dayNightPopulationRatio = area.value.day_night_population_ratio ?? ''
    editForm.averageAge = area.value.average_age ?? ''
    editForm.mainIndustry = area.value.main_industry ?? ''
    editForm.transitAccess = area.value.transit_access ?? ''
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
    address: editForm.address === '' ? null : editForm.address,
    population: editForm.population === '' ? null : editForm.population,
    day_night_population_ratio:
      editForm.dayNightPopulationRatio === '' ? null : editForm.dayNightPopulationRatio,
    average_age: editForm.averageAge === '' ? null : editForm.averageAge,
    main_industry: editForm.mainIndustry === '' ? null : editForm.mainIndustry,
    transit_access: editForm.transitAccess === '' ? null : editForm.transitAccess,
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

.area-detail-view__lifestyle-grid {
  grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 640px) {
  .area-detail-view__lifestyle-grid {
    grid-template-columns: 1fr;
  }
}

.area-detail-view__lifestyle-label {
  font-weight: 700;
  margin: 0 0 4px;
}
</style>
