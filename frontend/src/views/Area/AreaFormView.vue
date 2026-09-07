<template>
  <div class="area-form-view">
    <h2>{{ isEditMode ? '地域を編集' : '地域を新規登録' }}</h2>

    <p v-if="isLoading">読み込み中...</p>

    <form v-else @submit.prevent="handleSubmit">
      <div class="form-field">
        <label for="name">地域名</label>
        <input id="name" v-model="form.name" type="text" required maxlength="255" />
        <p v-if="errors.name" class="form-field__error">{{ errors.name }}</p>
      </div>

      <div class="form-field">
        <label for="address">住所（任意）</label>
        <input id="address" v-model="form.address" type="text" maxlength="255" />
        <p v-if="errors.address" class="form-field__error">{{ errors.address }}</p>
      </div>

      <div class="form-field form-field--row">
        <div>
          <label for="latitude">緯度（任意）</label>
          <input
            id="latitude"
            v-model="form.latitude"
            type="number"
            step="0.000001"
            min="-90"
            max="90"
          />
          <p v-if="errors.latitude" class="form-field__error">{{ errors.latitude }}</p>
        </div>
        <div>
          <label for="longitude">経度（任意）</label>
          <input
            id="longitude"
            v-model="form.longitude"
            type="number"
            step="0.000001"
            min="-180"
            max="180"
          />
          <p v-if="errors.longitude" class="form-field__error">{{ errors.longitude }}</p>
        </div>
      </div>

      <div class="form-field">
        <label>特色タグ（複数選択可・任意）</label>
        <div class="tag-selector">
          <label v-for="tag in availableTags" :key="tag.name" class="tag-selector__option">
            <input type="checkbox" :value="tag.name" v-model="form.tags" />
            {{ tag.name }}
          </label>
          <p v-if="availableTags.length === 0" class="tag-selector__empty">
            まだタグが登録されていません。下記から新しく追加してください
          </p>
        </div>
        <div class="tag-selector__new">
          <input
            v-model="newTagName"
            type="text"
            placeholder="新しいタグ名（例：温泉）"
            maxlength="50"
            @keydown.enter.prevent="addNewTag"
          />
          <button type="button" @click="addNewTag">＋ タグを追加</button>
        </div>
        <p v-if="errors.tags" class="form-field__error">{{ errors.tags }}</p>
      </div>

      <div class="form-field">
        <label for="features">特色（自由記述・任意）</label>
        <textarea id="features" v-model="form.features" rows="5"></textarea>
        <p v-if="errors.features" class="form-field__error">{{ errors.features }}</p>
      </div>

      <p v-if="submitError" class="area-form-view__error">{{ submitError }}</p>

      <div class="area-form-view__actions">
        <button type="submit" :disabled="isSubmitting">
          {{ isSubmitting ? '保存中...' : '保存' }}
        </button>
        <router-link to="/areas">キャンセル</router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { fetchArea, createArea, updateArea, fetchFeatureTags } from '@/api/area';

const route = useRoute();
const router = useRouter();

const isEditMode = computed(() => route.params.id !== undefined);

const form = reactive({
  name: '',
  features: '',
  latitude: '',
  longitude: '',
  address: '',
  tags: [], // 選択中のタグ名の配列
});

const availableTags = ref([]);
const newTagName = ref('');

const errors = ref({});
const isLoading = ref(false);
const isSubmitting = ref(false);
const submitError = ref('');

async function loadAvailableTags() {
  try {
    availableTags.value = await fetchFeatureTags();
  } catch (error) {
    // タグ一覧の取得に失敗しても、新規タグの自由入力は引き続き可能にしておく
    availableTags.value = [];
  }
}

function addNewTag() {
  const name = newTagName.value.trim();
  newTagName.value = '';

  if (name === '' || form.tags.includes(name)) {
    return;
  }

  form.tags.push(name);

  if (!availableTags.value.some((tag) => tag.name === name)) {
    availableTags.value.push({ id: null, name });
  }
}

async function loadArea() {
  isLoading.value = true;

  try {
    const area = await fetchArea(route.params.id);
    form.name = area.name;
    form.features = area.features ?? '';
    form.latitude = area.latitude ?? '';
    form.longitude = area.longitude ?? '';
    form.address = area.address ?? '';
    form.tags = area.tags.map((tag) => tag.name);
  } catch (error) {
    submitError.value = '地域データの取得に失敗しました';
  } finally {
    isLoading.value = false;
  }
}

async function handleSubmit() {
  isSubmitting.value = true;
  submitError.value = '';
  errors.value = {};

  const payload = {
    name: form.name,
    features: form.features === '' ? null : form.features,
    latitude: form.latitude === '' ? null : form.latitude,
    longitude: form.longitude === '' ? null : form.longitude,
    address: form.address === '' ? null : form.address,
    tags: form.tags,
  };

  try {
    if (isEditMode.value) {
      await updateArea(route.params.id, payload);
    } else {
      await createArea(payload);
    }
    router.push('/areas');
  } catch (error) {
    if (error.status === 401) {
      submitError.value = 'ログインが必要です';
    } else if (error.status === 403) {
      submitError.value = 'この地域を編集する権限がありません';
    } else if (error.status === 422 && error.errors) {
      errors.value = error.errors;
    } else {
      submitError.value = '保存に失敗しました';
    }
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  loadAvailableTags();
  if (isEditMode.value) {
    loadArea();
  }
});
</script>

<style scoped>
.form-field {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-field--row {
  flex-direction: row;
  gap: 16px;
}

.form-field__error {
  color: #c0392b;
  font-size: 0.85rem;
  margin: 0;
}

.tag-selector {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 8px;
}

.tag-selector__option {
  display: flex;
  align-items: center;
  gap: 4px;
  font-weight: normal;
}

.tag-selector__empty {
  color: #888;
  font-size: 0.85rem;
  margin: 0;
}

.tag-selector__new {
  display: flex;
  gap: 8px;
}

.area-form-view__error {
  color: #c0392b;
}

.area-form-view__actions {
  display: flex;
  gap: 12px;
  align-items: center;
}
</style>
