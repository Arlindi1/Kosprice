<script setup lang="ts">
import { computed } from 'vue'

import type { BasketTrendPoint } from '@/lib/types/api'
import LineChart from '@/shared/charts/LineChart.vue'
import Card from '@/shared/ui/Card.vue'
import EmptyState from '@/shared/ui/EmptyState.vue'
import SegmentedControl from '@/shared/ui/SegmentedControl.vue'
import Skeleton from '@/shared/ui/Skeleton.vue'

const props = withDefaults(
  defineProps<{
    trend: BasketTrendPoint[]
    isLoading: boolean
    error: string | null
    days: 14 | 30
  }>(),
  {
    error: null,
  },
)

const emit = defineEmits<{
  'update:days': [value: 14 | 30]
  retry: []
}>()

const trendDayOptions = [
  { label: '14d', value: 14 },
  { label: '30d', value: 30 },
]

const selectedDaysValue = computed({
  get: () => props.days,
  set: (value: string | number) => {
    if (value === 14 || value === 30) {
      emit('update:days', value)
    }
  },
})

const trendLabels = computed(() => props.trend.map((point) => point.recorded_at.slice(5)))
const trendDatasets = computed(() => [
  {
    label: 'City average basket',
    data: props.trend.map((point) => point.average_total_eur),
    borderColor: '#4338ca',
    backgroundColor: 'rgba(79, 70, 229, 0.16)',
    fill: true,
    pointRadius: 1.5,
  },
  {
    label: 'Cheapest market basket',
    data: props.trend.map((point) => point.min_total_eur),
    borderColor: '#64748b',
    fill: false,
    pointRadius: 1.25,
  },
])
</script>

<template>
  <Card subtitle="Secondary context: how basket affordability shifts over time in your selected city.">
    <template #header>
      <div class="space-y-2">
        <h3 class="text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl">Grocery Cost Trend</h3>
        <p class="text-body max-w-3xl">
          This helps explain whether a cheap product is part of a broader city-wide affordability change.
        </p>
      </div>
    </template>

    <template #actions>
      <SegmentedControl v-model="selectedDaysValue" :options="trendDayOptions" />
    </template>

    <div v-if="props.isLoading" class="status-wrap">
      <Skeleton height="19rem" />
    </div>
    <div v-else-if="props.error" class="status-wrap">
      <EmptyState title="Basket trend unavailable" :message="props.error" cta-label="Retry" @retry="emit('retry')" />
    </div>
    <div v-else-if="props.trend.length === 0" class="status-wrap">
      <EmptyState
        title="No basket trend data"
        message="Trend data has not been recorded for this city yet."
      />
    </div>
    <LineChart v-else :labels="trendLabels" :datasets="trendDatasets" :height="360" />
  </Card>
</template>
