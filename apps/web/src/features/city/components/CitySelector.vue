<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'

import { useCities } from '@/features/city/composables/useCities'
import { useCityStore } from '@/features/city/store/useCityStore'
import Button from '@/shared/ui/Button.vue'
import Select from '@/shared/ui/Select.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

type SelectorMode = 'default' | 'pill'

const props = withDefaults(
  defineProps<{
    mode?: SelectorMode
    label?: string
  }>(),
  {
    mode: 'default',
    label: 'City',
  },
)

const cityStore = useCityStore()
const { cities, isLoadingCities, citiesError, loadCities } = useCities()

const options = computed(() =>
  cities.value.map((city) => ({
    label: city.name,
    value: city.id,
  })),
)

const selectedCityValue = computed({
  get: () => (cityStore.selectedCityId !== null ? String(cityStore.selectedCityId) : ''),
  set: (value: string) => {
    const parsedValue = Number(value)
    cityStore.setSelectedCityId(Number.isInteger(parsedValue) && parsedValue > 0 ? parsedValue : null)
  },
})

watch(
  () => cities.value,
  (availableCities) => {
    const firstCity = availableCities[0]

    if (cityStore.selectedCityId === null && firstCity) {
      cityStore.setSelectedCityId(firstCity.id)
    }
  },
  { immediate: true },
)

onMounted(() => {
  void loadCities()
})

const wrapperClass = computed(() =>
  props.mode === 'pill' ? 'w-full min-w-[200px] sm:w-auto sm:min-w-[240px]' : 'w-full max-w-[270px]',
)
</script>

<template>
  <div :class="wrapperClass">
    <label v-if="props.mode === 'default'" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
      {{ props.label }}
    </label>

    <div v-if="isLoadingCities">
      <Skeleton height="2.5rem" :rounded="props.mode === 'pill' ? '9999px' : '0.75rem'" />
    </div>

    <div v-else-if="citiesError" class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
      <p class="text-xs text-slate-600">{{ citiesError }}</p>
      <Button variant="secondary" size="sm" @click="loadCities(true)">Retry</Button>
    </div>

    <Select
      v-else
      v-model="selectedCityValue"
      :options="options"
      placeholder="Select city"
      :variant="props.mode === 'pill' ? 'pill' : 'default'"
    />
  </div>
</template>
