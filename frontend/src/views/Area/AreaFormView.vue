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
        <label for="prefecture">都道府県</label>
        <input id="prefecture" v-model="form.prefecture" type="text" required maxlength="50" />
        <p v-if="errors.prefecture" class="form-field__error">{{ errors.prefecture }}</p>
      </div>

      <div class="form-field">
        <label for="city">市区町村</label>
        <input id="city" v-model="form.city" type="text" required maxlength="100" />
        <p v-if="errors.city" class="form-field__error">{{ errors.city }}</p>
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
        <label for="features">特色</label>
        <textarea id="features" v-model="form.features" required rows="5"></textarea>
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
import { fetchArea, createArea, updateArea } from '@/api/area';

const route = useRoute();
const router = useRouter();

const isEditMode = computed(() => route.params.id !== undefined);

const form = reactive({
  name: '',
  prefecture: '',
  city: '',
  latitude: '',
  longitude: '',
  features: '',
});

const errors = ref({});
const isLoading = ref(false);
const isSubmitting = ref(false);
const submitError = ref('');

async function loadArea() {
  isLoading.value = true;

  try {
    const area = await fetchArea(route.params.id);
    form.name = area.name;
    form.prefecture = area.prefecture;
    form.city = area.city;
    form.latitude = area.latitude ?? '';
    form.longitude = area.longitude ?? '';
    form.features = area.features;
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
    prefecture: form.prefecture,
    city: form.city,
    latitude: form.latitude === '' ? null : form.latitude,
    longitude: form.longitude === '' ? null : form.longitude,
    features: form.features,
  };

  try {
    if (isEditMode.value) {
      await updateArea(route.params.id, payload);
    } else {
      await createArea(payload);
    }
    router.push('/areas');
  } catch (error) {
    if (error.status === 422 && error.errors) {
      errors.value = error.errors;
    } else {
      submitError.value = '保存に失敗しました';
    }
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
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

.area-form-view__error {
  color: #c0392b;
}

.area-form-view__actions {
  display: flex;
  gap: 12px;
  align-items: center;
}
</style>
